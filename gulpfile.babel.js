// https://css-tricks.com/gulp-for-wordpress-creating-the-tasks/
import { src, dest, watch, series, parallel } from 'gulp';
import yargs from 'yargs';
import sass from 'gulp-sass';
import cleanCss from 'gulp-clean-css';
import gulpif from 'gulp-if';
import postcss from 'gulp-postcss';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'autoprefixer';
import imagemin from 'gulp-imagemin';
import del from 'del';
import webpack from 'webpack-stream';
import named from 'vinyl-named';
import info from './package.json';
import wpPot from 'gulp-wp-pot'
import zip from 'gulp-zip';

const PRODUCTION = yargs.argv.prod;

export const styles = () => {
  return src(['src/scss/brackets.scss', 'src/scss/components.scss', 'src/scss/online-statistics-widget.scss', 'src/scss/trn.datatable.bootstrap4.css', 'src/scss/trn.bootstrap.4.3.1.css', 'src/scss/fontawesome.5.14.0.css'])
    .pipe(gulpif(!PRODUCTION, sourcemaps.init()))
    .pipe(sass().on('error', sass.logError))
    .pipe(gulpif(PRODUCTION, postcss([autoprefixer])))
    .pipe(gulpif(PRODUCTION, cleanCss({compatibility:'ie8'})))
    .pipe(gulpif(!PRODUCTION, sourcemaps.write()))
    .pipe(dest('dist/css'));
};

export const images = () => {
    return src('src/images/**/*.{jpg,jpeg,png,svg,gif}')
        .pipe(gulpif(PRODUCTION, imagemin()))
        .pipe(dest('dist/images'));
};

export const copy = () => {
    return src(['src/**/*','!src/{images,js,scss}','!src/{images,js,scss}/**/*'])
        .pipe(dest('dist'));
};

export const clean = () => {
    return del(['dist']);
};

export const scripts = () => {
    return src(['src/js/**/*.js'])
        .pipe(named())
        .pipe(webpack({
            module: {
                rules: [
                    {
                        test: /\.js$/,
                        use: {
                            loader: 'babel-loader',
                            options: {
                                presets: []
                            }
                        }
                    }
                ]
            },
            mode: PRODUCTION ? 'production' : 'development',
            devtool: !PRODUCTION ? 'inline-source-map' : false,
            output: {
                filename: '[name].js'
            },
            // externals: {
            //   jquery: 'jQuery'
            // },
        }))
        .pipe(dest('dist/js'));
};

export const pot = () => {
    return src(["**/*.php", "!bundled{,/**}"])
        .pipe(
            wpPot({
                domain: "tournamatch",
                package: info.name
            })
        )
        .pipe(dest(`languages/${info.name}.pot`));
};

export const watchForChanges = () => {
    watch('src/scss/**/*.scss', styles);
    watch('src/images/**/*.{jpg,jpeg,png,svg,gif}', images);
    watch(['src/**/*','!src/{images,js,scss}','!src/{images,js,scss}/**/*'], copy);
    watch('src/js/**/*.js', scripts);
};

export const compress = () => {
    return src([
        "**/*",
        "!node_modules{,/**}",
        "!bundled{,/**}",
        "!src{,/**}",
        "!.babelrc",
        "!.gitignore",
        "!gulpfile.babel.js",
        "!package.json",
        "!package-lock.json",
        "!*.bat",
		"!tournamatch.dev.php",
		"!phpcsrules.xml",
		"!README.md",
    ])
    .pipe(zip(`${info.name}.zip`))
    .pipe(dest('bundled'));
};

export const dev = series(clean, parallel(styles, images, copy, scripts), watchForChanges);
export const build = series(clean, parallel(styles, images, copy, scripts), pot, compress);
export default dev;