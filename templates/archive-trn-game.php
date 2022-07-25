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
	<?php foreach ( $games as $game ) : ?>
	<div class="trn-col-lg-4 trn-col-sm-6">
		<div class="trn-item-wrapper">
			<div class="trn-item-thumbnail">
				<img src="<?php echo esc_html( $image_directory . '/games/' . $game->thumbnail ); ?>" alt="<?php echo esc_html( $game->name ); ?>">
			</div>
			<div class="trn-item-info">
				<span class="trn-item-title"><?php echo esc_html( $game->name ); ?></span>
				<span class="trn-item-meta"><?php echo esc_html( $game->platform ); ?></span>
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
			<div class="trn-clearfix"></div>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<?php

trn_get_footer();

get_footer();
