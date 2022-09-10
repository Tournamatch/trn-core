<?php
/**
 * The template that displays an archive of ladders.
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

$ladders = trn_get_ladders( $game_id, $platform );

get_header();

trn_get_header();

?>
	<h1 class="trn-mb-4"><?php esc_html_e( 'Ladders', 'tournamatch' ); ?></h1>
	<div class="trn-row" id="ladders">
		<?php
		foreach ( $ladders as $ladder ) :
			$ladder = trn_the_ladder( $ladder );
			?>
			<div class="trn-col-sm-6">
				<div class="trn-item-wrapper" onclick="window.location.href = '<?php trn_esc_route_e( 'ladders.single', array( 'id' => $ladder->ladder_id ) ); ?>'">
					<div class="trn-item-group">
						<div class="trn-item-thumbnail">
							<?php trn_game_thumbnail( $ladder ); ?>
						</div>
						<div class="trn-item-info" style="float: left; margin-left: 10px">
							<span class="trn-item-title"><?php echo esc_html( $ladder->name ); ?></span>
						</div>
					</div>
					<ul class="trn-item-list">
						<li class="trn-item-list-item members">
							<?php /* translators: number of competitors. */ ?>
							<?php echo sprintf( esc_html( _n( '%s Competitor', '%s Competitors', 8, 'tournamatch' ) ), 8 ); ?>
						</li>
						<li class="trn-item-list-item ranking">
							<?php echo esc_html( $ladder->ranking_mode_label ); ?>
						</li>
						<li class="trn-item-list-item competitor-type">
							<?php if ( 'players' === $ladder->competitor_type ) : ?>
								<?php esc_html_e( 'Singles', 'tournamatch' ); ?>
							<?php else : ?>
								<?php /* translators: Opponent name vs opponent name. */ ?>
								<?php echo sprintf( esc_html__( 'Teams (%1$d vs %1$d)', 'tournamatch' ), intval( $ladder->team_size ) ); ?>
							<?php endif; ?>
						</li>
					</ul>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php

trn_get_footer();

get_footer();
