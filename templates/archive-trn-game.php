<?php
/**
 * The template that displays an archive of games.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

trn_get_header();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$games           = trn_get_games_with_competition_counts( isset( $_GET['platform'] ) ? sanitize_text_field( wp_unslash( $_GET['platform'] ) ) : null );
$image_directory = trn_upload_url() . '/images';

?>
<div class="trn-row" id="games">
	<?php
	foreach ( $games as $game ) :
		$src = $image_directory . '/games/blank.gif';

		if ( isset( $game->thumbnail_id ) && ( 0 < $game->thumbnail_id ) ) {
			$src = wp_get_attachment_image_src( $game->thumbnail_id );
			if ( is_array( $src ) ) {
				$src = $src[0];
			}
		} elseif ( isset( $game->thumbnail ) && ( 0 < strlen( $game->thumbnail ) ) ) {
			$src = $image_directory . '/games/' . $game->thumbnail;
		}

		?>
	<div class="trn-col-lg-4 trn-col-sm-6">
		<div class="trn-item-wrapper">
			<div class="trn-item-group">
				<div class="trn-item-thumbnail">
					<img src="<?php echo esc_attr( $src ); ?>" alt="<?php echo esc_html( $game->name ); ?>">
				</div>
				<div class="trn-item-info">
					<span class="trn-item-title"><?php echo esc_html( $game->name ); ?></span>
					<span class="trn-item-meta">
						<?php
						echo sprintf(
							wp_kses_post(
								/* translators: A Hyperlinked number of ladders; '<a href="">5 Ladders</a>' */
								__( '<a href="%1$s">%2$d Ladders</a>', 'tournamatch' )
							),
							esc_url( trn_route( 'ladders.archive', array( 'game_id' => $game->game_id ) ) ),
							intval( $game->ladders )
						)
						?>
					</span>
					<span class="trn-item-meta">
						<?php
						echo sprintf(
							wp_kses_post(
								/* translators: A Hyperlinked number of tournaments; '<a href="">3</a> Tournaments' */
								__( '<a href="%1$s">%2$d Tournaments</a>', 'tournamatch' )
							),
							esc_url( trn_route( 'tournaments.archive', array( 'game_id' => $game->game_id ) ) ),
							intval( $game->tournaments )
						)
						?>
					</span>
				</div>
			</div>
			<ul class="trn-item-list">
				<li class="trn-item-list-item platform">
					<?php echo esc_html( $game->platform ); ?>
				</li>
			</ul>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<?php

trn_get_footer();

get_footer();
