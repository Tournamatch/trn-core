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

$ladders         = trn_get_ladders( $game_id, $platform );
$is_admin        = current_user_can( 'manage_tournamatch' );
$image_directory = trn_upload_url() . '/images';

$my_ladders = array();
if ( is_user_logged_in() ) {
	$my_ladders = array_column( trn_get_user_ladders( get_current_user_id() ), 'ladder_id' );
}

get_header();

trn_get_header();

?>
	<h1 class="mb-4"><?php esc_html_e( 'Ladders', 'tournamatch' ); ?></h1>
	<div class="row" id="ladders">
		<?php
		foreach ( $ladders as $ladder ) :
			?>
			<div class="col-sm-6">
				<div class="item-wrapper">
					<div class="item-thumbnail">
						<a href="<?php trn_esc_route_e( 'ladders.single', array( 'id' => $ladder->ladder_id ) ); ?>"
							title="<?php esc_html_e( 'View Ladder', 'tournamatch' ); ?>">
							<img src="<?php echo ! is_null( $ladder->game_id ) ? esc_url( $image_directory . '/games/' . $ladder->thumbnail ) : esc_url( $image_directory . '/games/blank.gif' ); ?>"
								alt="<?php echo esc_html( $ladder->game ); ?>">
						</a>
					</div>
					<div class="item-info" style="float: left; margin-left: 10px">
						<span class="item-title"><?php echo esc_html( $ladder->name ); ?></span>
						<span class="item-meta"><?php esc_html_e( 'Points', 'tournamatch' ); ?></span>
						<span class="item-meta">
						<?php
						/* translators: Integer number of competitors */
							printf( esc_html__( '%d Competitors', 'tournamatch' ), intval( $ladder->competitors ) )
						?>
							</span>

					</div>
					<ul class="list-unstyled text-center" style="float: right">
						<li class="list-ite" style="margin-bottom: 10px"><a
									href="<?php trn_esc_route_e( 'ladders.single', array( 'id' => intval( $ladder->ladder_id ) ) ); ?>"
									class="btn btn-sm btn-primary"><?php esc_html_e( 'Info', 'tournamatch' ); ?></a></li>
						<?php if ( 'active' === $ladder->status ) : ?>
							<?php if ( in_array( (string) $ladder->ladder_id, $my_ladders, true ) ) : ?>
								<li class="list-inline-ite" style="margin-bottom: 10px"><a
											href="<?php trn_esc_route_e( 'matches.single.create', array( 'ladder_id' => $ladder->ladder_id ) ); ?>"
											class="btn btn-sm btn-primary"><?php esc_html_e( 'Report', 'tournamatch' ); ?></a>
								</li>
							<?php else : ?>
								<li class="list-inline-ite" style="margin-bottom: 10px"><a
											id="trn-ladder-<?php echo intval( $ladder->ladder_id ); ?>-join"
											href="<?php trn_esc_route_e( 'ladders.single.join', array( 'id' => $ladder->ladder_id ) ); ?>"
											class="btn btn-sm btn-primary"><?php esc_html_e( 'Join', 'tournamatch' ); ?></a>
								</li>
							<?php endif; ?>
						<?php endif ?>
						<li style=""><a
									href="<?php trn_esc_route_e( 'ladders.single', array( 'id' => intval( $ladder->ladder_id ) ) ); ?>"
									class="btn btn-sm btn-primary"><?php esc_html_e( 'View', 'tournamatch' ); ?></a></li>
					</ul>
					<div class="clearfix"></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php

trn_get_footer();

get_footer();
