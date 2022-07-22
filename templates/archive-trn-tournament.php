<?php
/**
 * The template that displays an archive of tournaments.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$game_id = isset( $_GET['game_id'] ) ? intval( $_GET['game_id'] ) : null;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$platform = isset( $_GET['platform'] ) ? sanitize_text_field( wp_unslash( $_GET['platform'] ) ) : null;

$image_directory = trn_upload_url() . '/images';

$tournaments = trn_get_tournaments( $game_id, $platform );

$is_admin = current_user_can( 'manage_tournamatch' );

$my_tournaments = array();
if ( is_user_logged_in() ) {
	$my_tournaments = array_column( trn_get_user_tournaments( get_current_user_id() ), 'tournament_id' );
}

get_header();

trn_get_header();
?>
<h1 class="trn-mb-4"><?php esc_html_e( 'Tournaments', 'tournamatch' ); ?></h1>

<!-- Tab navigation -->
<ul class="trn-nav trn-mb-sm flex-column flex-sm-row">
	<li role="presentation" class="trn-nav-item flex-sm" aria-controls="all" data-filter="all"><a class="trn-nav-link trn-nav-active" href="#"><span><?php esc_html_e( 'All', 'tournamatch' ); ?></span></a></li>
	<li role="presentation" class="trn-nav-item flex-sm" aria-controls="upcoming" data-filter="upcoming"><a class="trn-nav-link" href="#"><span><?php esc_html_e( 'Upcoming', 'tournamatch' ); ?></span></a></li>
	<li role="presentation" class="trn-nav-item flex-sm" aria-controls="in_progress" data-filter="in_progress"><a class="trn-nav-link" href="#"><span><?php esc_html_e( 'In Progress', 'tournamatch' ); ?></span></a></li>
	<li role="presentation" class="trn-nav-item flex-sm" aria-controls="complete" data-filter="complete"><a class="trn-nav-link" href="#"><span><?php esc_html_e( 'Finished', 'tournamatch' ); ?></span></a></li>
</ul>

<div class="trn-row">
	<?php
	foreach ( $tournaments as $tournament ) :

		$tournament->id = $tournament->tournament_id;

		if ( in_array( $tournament->status, [ 'created', 'open' ], true ) ) {
			$filter = 'upcoming';
		} else {
			$filter = $tournament->status;
		}

		// Can current user sign up?
		$can_register = false;
		if ( is_user_logged_in() ) {
			if ( ! in_array( (string) $tournament->tournament_id, $my_tournaments, true ) && in_array( $tournament->status, [ 'open' ], true ) ) {
				$can_register = ( intval( $tournament->bracket_size ) === 0 ) || ( $tournament->competitors < $tournament->bracket_size );
			}
		}

		?>
		<div class="trn-col-sm-6 tournament" data-filter="<?php echo esc_html( $filter ); ?>" id="trn-tournament-<?php echo intval( $tournament->id ); ?>-details">
			<div class="trn-item-wrapper">
				<div class="trn-item-thumbnail">
					<a href="<?php trn_esc_route_e( 'tournaments.single.rules', array( 'id' => intval( $tournament->id ) ) ); ?>" title="<?php esc_html_e( 'View Tournament', 'tournamatch' ); ?>">
						<img src="<?php echo esc_url( $image_directory ); ?>/games/<?php echo is_null( $tournament->game_id ) ? 'blank.gif' : esc_html( $tournament->game_thumbnail ); ?>" alt="<?php echo esc_html( $tournament->game_name ); ?>">
					</a>
				</div>
				<div class="trn-item-info">
					<span class="trn-item-title"><?php echo esc_html( $tournament->name ); ?></span>
					<span class="trn-item-meta"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $tournament->start_date ) ) ) ); ?></span>
					<span class="trn-item-meta"><?php esc_html_e( 'One loss', 'tournamatch' ); ?></span>
					<span class="trn-item-meta"><a href="<?php trn_esc_route_e( 'tournaments.single.registered', array( 'id' => intval( $tournament->id ) ) ); ?>"><?php echo intval( $tournament->competitors ); ?></a>/<?php echo ( intval( $tournament->bracket_size ) > 0 ) ? intval( $tournament->bracket_size ) : '&infin;'; ?></span>
					<ul class="trn-list-inline">
						<li class="trn-list-inline-item"><a href="<?php trn_esc_route_e( 'tournaments.single.rules', array( 'id' => intval( $tournament->id ) ) ); ?>" class="trn-button trn-button-sm"><?php esc_html_e( 'Info', 'tournamatch' ); ?></a></li>
						<?php if ( $can_register ) : ?>
							<li class="trn-list-inline-item"><a href="<?php trn_esc_route_e( 'tournaments.single.register', array( 'id' => $tournament->id ) ); ?>" class="trn-button trn-button-sm" id="tournament-<?php echo intval( $tournament->id ); ?>-register-link"><?php esc_html_e( 'Sign up', 'tournamatch' ); ?></a></li>
						<?php endif; ?>
						<?php if ( in_array( $tournament->status, [ 'in_progress', 'complete' ], true ) ) : ?>
							<li class="trn-list-inline-item"><a href="<?php trn_esc_route_e( 'tournaments.single.brackets', array( 'id' => intval( $tournament->id ) ) ); ?>" class="trn-button trn-button-sm"><?php esc_html_e( 'Brackets', 'tournamatch' ); ?></a></li>
						<?php endif; ?>
						<?php if ( ( 'in_progress' === $tournament->status ) && in_array( (string) $tournament->id, $my_tournaments, true ) ) : ?>
							<?php /* TODO: In the future, report button should auto take you to the next match or the my-match screen. */ ?>
							<li class="trn-list-inline-item"><a href="<?php trn_esc_route_e( 'report.page' ); ?>" class="trn-button trn-button-sm"><?php esc_html_e( 'Report', 'tournamatch' ); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
				<div class="trn-clearfix"></div>
			</div>
		</div>
	<?php endforeach ?>
</div>
<?php

wp_register_script( 'tournaments', plugins_url( '../dist/js/tournaments.js', __FILE__ ), array(), '3.25.0', true );
wp_enqueue_script( 'tournaments' );

trn_get_footer();

get_footer();
