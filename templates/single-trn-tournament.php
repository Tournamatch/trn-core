<?php
/**
 * The template that displays a single tournament.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$tournament_id   = get_query_var( 'id' );
$image_directory = trn_upload_url() . '/images';

global $wpdb;

$tournament = trn_get_tournament( $tournament_id );

if ( is_null( $tournament ) ) {
	wp_safe_redirect( trn_route( 'tournaments.archive' ) );
	exit;
}

$my_tournaments      = array_column( trn_get_user_tournaments( get_current_user_id() ), 'tournament_id' );
$register_conditions = trn_get_tournament_register_conditions( $tournament->tournament_id, get_current_user_id() );
$competitors         = trn_get_tournament_competitors( $tournament_id );
$registered          = trn_get_registered_competitors( $tournament_id );

get_header();

trn_get_header();

?>
<div class="trn-competition-header"<?php trn_header_banner_style( $tournament->banner_id, $tournament->game_id ); ?>>
	<h1 class="trn-competition-name"><?php echo esc_html( $tournament->name ); ?></h1>
	<span class="trn-competition-game"><?php echo esc_html( $tournament->game_name ); ?></span>
	<span class="trn-competition-actions">
	<?php if ( is_user_logged_in() ) : ?>

	<?php endif; ?>
	</span>
	<ul class="trn-competition-list">
		<li class="trn-competition-list-item joined">
			<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $tournament->start_date ) ) ) ); ?>
		</li>
		<li class="trn-competition-list-item members">
			<?php echo intval( $tournament->competitors ); ?>/<?php echo ( $tournament->bracket_size > 0 ) ? intval( $tournament->bracket_size ) : '&infin;'; ?>
		</li>
		<li class="trn-competition-list-item info">
			<?php echo esc_html( ucwords( str_replace( '_', ' ', $tournament->status ) ) ); ?>
		</li>
		<li class="trn-competition-list-item format">
			<?php esc_html_e( 'Single Elimination', 'tournamatch' ); ?>
		</li>
		<li class="trn-competition-list-item competitor-type">
			<?php if ( 'players' === $tournament->competitor_type ) : ?>
				<?php esc_html_e( 'Singles', 'tournamatch' ); ?>
			<?php else : ?>
				<?php /* translators: Opponent name vs opponent name. */ ?>
				<?php echo sprintf( esc_html__( 'Teams (%1$d vs %1$d)', 'tournamatch' ), intval( $tournament->team_size ) ); ?>
			<?php endif; ?>
		</li>
	</ul>
</div>
<div id="trn-unregister-response"></div>
<?php

$views = array(
	'rules'      => array(
		'heading' => __( 'Rules', 'tournamatch' ),
		'content' => function( $tournament ) {
			if ( strlen( $tournament->rules ) > 0 ) {
				echo wp_kses_post( stripslashes( $tournament->rules ) );
			} else {
				echo '<p class="trn-text-center">';
				esc_html_e( 'No rules to display.', 'tournamatch' );
				echo '</p>';
			}
		},
	),
	'matches'    => array(
		'heading' => __( 'Matches', 'tournamatch' ),
		'content' => function( $tournament ) {
			echo do_shortcode( '[trn-tournament-matches-list-table tournament_id="' . intval( $tournament->tournament_id ) . '"]' );
		},
	),
	'registered' => array(
		'heading' => __( 'Registered', 'tournamatch' ),
		'content' => function( $tournament ) use ( $registered ) {
			?>
			<style type="text/css">
				.trn-tournament-registered-item {
					width: 200px;
					height: 50px;
					padding: 10px;
					margin: 10px;
					display: inline-table;
				}
				.trn-tournament-registered-item-avatar > img {
					height: 48px;
					width: 48px;
					float: left;
					margin-right: 5px;
					border-radius: 2px;
				}
			</style>
			<div class="d-flex flex-row flex-wrap" id="trn-tournament-registered">
				<?php foreach ( $registered as $competitor ) : ?>
					<div class="trn-tournament-registered-item shadow-sm rounded">
						<span class="trn-tournament-registered-item-avatar"><?php trn_display_avatar( $competitor->competitor_id, $competitor->competitor_type, $competitor->avatar ); ?></span>
						<a href="<?php trn_esc_route_e( "{$competitor->competitor_type}.single", array( 'id' => $competitor->competitor_id ) ); ?>"><?php echo esc_html( $competitor->competitor_name ); ?></a>
						<?php if ( current_user_can( 'manage_tournamatch' ) && in_array( $tournament->status, [ 'created', 'open' ], true ) ) : ?>
							&nbsp; <a style="float: right" href="
							<?php
							trn_esc_route_e(
								'admin.tournaments.remove-entry',
								array(
									'tournament_entry_id' => $competitor->tournament_entry_id,
									'_wpnonce'            => wp_create_nonce( 'tournamatch-remove-tournament-entry' ),
								)
							);
							?>
						"><i class="fa fa-times" aria-hidden="true"></i></a>
						<?php endif; ?>
						<?php if ( 'teams' === $tournament->competitor_type ) : ?>
							<br><small><?php echo intval( $competitor->members ); ?>/<?php echo intval( $tournament->team_size ); ?></small>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php
		},
	),
);

if ( in_array( $tournament->status, [ 'in_progress', 'complete' ], true ) ) {
	$views = array_merge(
		array(
			'brackets' => array(
				'heading' => __( 'Brackets', 'tournamatch' ),
				'content' => function ( $tournament ) {
					echo do_shortcode( '[trn-brackets tournament_id="' . intval( $tournament->tournament_id ) . '"]' );
				},
			),
		),
		$views
	);
}

if ( ( 'in_progress' === $tournament->status ) && in_array( $tournament->tournament_id, $my_tournaments, true ) ) {
	$views = array_merge(
		$views,
		array(
			'report' => array(
				'heading' => __( 'Report', 'tournamatch' ),
				'href'    => trn_route( 'report.page' ),
			),
		)
	);
}

if ( $register_conditions['can_register'] ) {
	$views = array_merge(
		$views,
		array(
			'register' => array(
				'heading' => __( 'Sign Up', 'tournamatch' ),
				'href'    => trn_route( 'tournaments.single.register', array( 'id' => $tournament->tournament_id ) ),
			),
		)
	);
}

if ( $register_conditions['can_unregister'] ) {
	$views = array_merge(
		$views,
		array(
			'unregister' => array(
				'heading' => function( $tournament ) use ( $register_conditions ) {
					?>
					<a class="trn-nav-link trn-tournament-unregister-button" href="#" data-tournament-registration-id="<?php echo intval( $register_conditions['id'] ); ?>" id="tournament-<?php echo intval( $tournament->tournament_id ); ?>-unregister-link"><?php esc_html_e( 'Unregister', 'tournamatch' ); ?></a>
					<?php
				},
			),
		)
	);

	$options = array(
		'api_url'     => site_url( 'wp-json/tournamatch/v1/' ),
		'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
		'refresh_url' => trn_route( 'tournaments.single.registered', array( 'id' => $tournament->tournament_id ) ),
		'language'    => array(
			'failure'         => esc_html__( 'Error', 'tournamatch' ),
			'success'         => esc_html__( 'Success', 'tournamatch' ),
			'failure_message' => esc_html__( 'Unable to unregister from this tournament at this time.', 'tournamatch' ),
		),
	);

	wp_register_script( 'tournament-unregister', plugins_url( '../dist/js/tournament-unregister.js', __FILE__ ), array( 'tournamatch' ), '3.24.0', true );
	wp_localize_script( 'tournament-unregister', 'trn_tournament_unregister_options', $options );
	wp_enqueue_script( 'tournament-unregister' );
}

/**
 * Filters an array of views for the single tournament template page.
 *
 * @since 4.1.0
 *
 * @param array $views {
 *          An associative array of tabbed views.
 *
 *          @param string|callable $heading The content or callable content of the header tab.
 *          @param string $href The url of the header tab.
 *          @param string|callable $content The content or callable content of the tabbed page.
 *      }
 * @param stdClass $tournament The data context item we are rendering a page for.
 */
$views = apply_filters( 'trn_single_tournament_views', $views, $tournament );

trn_single_template_tab_views( $views, $tournament );

trn_get_footer();

get_footer();
