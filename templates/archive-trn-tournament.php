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
<ul class="trn-nav trn-mb-sm flex-column flex-sm-row tournament-filter">
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
			<div class="trn-item-wrapper" onclick="window.location.href = '<?php trn_esc_route_e( 'tournaments.single', array( 'id' => intval( $tournament->id ) ) ); ?>'">
				<div class="trn-item-group">
					<div class="trn-item-thumbnail">
						<?php trn_game_thumbnail( $tournament ); ?>
					</div>
					<div class="trn-item-info">
						<span class="trn-item-title"><?php echo esc_html( $tournament->name ); ?></span>
						<span class="trn-item-meta"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $tournament->start_date ) ) ) ); ?></span>
					</div>
				</div>
				<ul class="trn-item-list">
					<li class="trn-item-list-item members">
						<?php echo intval( $tournament->competitors ); ?>/<?php echo ( $tournament->bracket_size > 0 ) ? intval( $tournament->bracket_size ) : '&infin;'; ?>
					</li>
					<li class="trn-item-list-item info">
						<?php echo esc_html( ucwords( str_replace( '_', ' ', $tournament->status ) ) ); ?>
					</li>
					<li class="trn-item-list-item competitor-type">
						<?php if ( 'players' === $tournament->competitor_type ) : ?>
							<?php esc_html_e( 'Singles', 'tournamatch' ); ?>
						<?php else : ?>
							<?php /* translators: Opponent name vs opponent name. */ ?>
							<?php echo sprintf( esc_html__( 'Teams (%1$d vs %1$d)', 'tournamatch' ), intval( $tournament->team_size ) ); ?>
						<?php endif; ?>
					</li>
					<?php if ( trn_is_plugin_active( 'trn-mycred' ) ) : ?>
						<?php if ( 0 < intval( $tournament->mycred_entry_fee ) ) : ?>
							<li class="trn-item-list-item entry-fee">
								<?php echo intval( $tournament->mycred_entry_fee ); ?>
							</li>
						<?php endif; ?>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	<?php endforeach ?>
</div>
<?php

wp_register_script( 'tournaments', plugins_url( '../dist/js/tournaments.js', __FILE__ ), array(), '3.25.0', true );
wp_enqueue_script( 'tournaments' );

trn_get_footer();

get_footer();
