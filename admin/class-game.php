<?php
/**
 * Manages admin game pages.
 *
 * @link  https://www.tournamatch.com
 * @since 4.0.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Manages admin game pages.
 *
 * @since 4.0.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Game {

	/**
	 * Initializes the game admin components.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'setup_menu' ) );

		add_action( 'load-toplevel_page_trn-games', array( $this, 'pre_headers' ) );
	}

	/**
	 * Initialize the menu for game screens.
	 *
	 * @since 4.0.0
	 */
	public function setup_menu() {
		/*
		 * Icons are sourced from here: https://github.com/encharm/Font-Awesome-SVG-PNG/tree/master/black/svg
		 */

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$icon = base64_encode( '<svg width="20" height="20" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path fill="#9ca2a7" d="M896 1088v-128q0-14-9-23t-23-9h-192v-192q0-14-9-23t-23-9h-128q-14 0-23 9t-9 23v192h-192q-14 0-23 9t-9 23v128q0 14 9 23t23 9h192v192q0 14 9 23t23 9h128q14 0 23-9t9-23v-192h192q14 0 23-9t9-23zm576 64q0-53-37.5-90.5t-90.5-37.5-90.5 37.5-37.5 90.5 37.5 90.5 90.5 37.5 90.5-37.5 37.5-90.5zm256-256q0-53-37.5-90.5t-90.5-37.5-90.5 37.5-37.5 90.5 37.5 90.5 90.5 37.5 90.5-37.5 37.5-90.5zm256 128q0 212-150 362t-362 150q-192 0-338-128h-220q-146 128-338 128-212 0-362-150t-150-362 150-362 362-150h896q212 0 362 150t150 362z"/></svg>' );

		add_menu_page(
			esc_html__( 'Games', 'tournamatch' ),
			esc_html__( 'Games', 'tournamatch' ),
			'manage_tournamatch',
			'trn-games',
			array( $this, 'games' ),
			'data:image/svg+xml;base64,' . $icon
		);
	}

	/**
	 * Displays the game screens.
	 *
	 * @since 4.0.0
	 */
	public function games() {
		global $wpdb;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'dashboard';

		switch ( $action ) {
			case 'delete':
				check_admin_referer( 'tournamatch-bulk-games' );

				$game_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;

				$message = '<p class="notice notice-error">' . esc_html__( 'Are you sure you want to delete this game? ', 'tournamatch' ) . '<br><br>' . esc_html__( 'Delete?', 'tournamatch' ) . ' <a href="' . esc_url(
					trn_route(
						'admin.games.delete-confirm',
						array(
							'id'       => $game_id,
							'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-games' ),
						)
					)
				) . '">' . esc_html__( 'Yes', 'tournamatch' ) . '</a> ' . esc_html__( 'or', 'tournamatch' ) . ' <a href="' . esc_url( trn_route( 'admin.games' ) ) . '">' . esc_html__( 'No', 'tournamatch' ) . '</a></p>';
				trn_admin_message( esc_html__( 'Delete Game', 'tournamatch' ), $message );
				break;

			case 'edit':
				check_admin_referer( 'tournamatch-bulk-games' );

				$game_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$game    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $game_id ) );
				?>
				<style type="text/css">
					#trn-edit-game-form .form-field input, #trn-edit-game-form .form-field select {
						width: 25em;
					}

					@media screen and (max-width: 782px) {
						#trn-edit-game-form .form-field input, #trn-edit-game-form .form-field select {
							width: 100%;
						}
					}
				</style>
				<div class="wrap">
					<h1 class="wp-heading-inline">
						<?php esc_html_e( 'Edit Game', 'tournamatch' ); ?>
					</h1>
					<form method="post" action="#" id="trn-edit-game-form">
						<div id="trn-edit-game-response"></div>
						<table class="form-table" role="presentation">
							<tr class="form-field form-required term-name-wrap">
								<th scope="row">
									<label for="trn-game-name"><?php esc_html_e( 'Name', 'tournamatch' ); ?> <span
												class="description"><?php esc_html_e( '(required)', 'tournamatch' ); ?></span></label>
								</th>
								<td>
									<input type="text" id="trn-game-name" name="trn-game-name" required
											aria-required="true"
											value="<?php echo esc_html( $game->name ); ?>">
									<p class="description"><?php esc_html_e( 'The title of the game as it appears on your site.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field form-required term-name-wrap">
								<th scope="row">
									<label for="trn-game-platform"><?php esc_html_e( 'Platform', 'tournamatch' ); ?></label>
								</th>
								<td>
									<input type="text" id="trn-game-platform" name="trn-game-platform"
											value="<?php echo esc_html( $game->platform ); ?>">
									<p class="description"><?php esc_html_e( 'The platform of the game. Useful for organizing games by platform in the front end menu.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field form-required term-name-wrap">
								<th scope="row">
									<label for="trn-game-thumbnail-button"><?php esc_html_e( 'Game Thumbnail', 'tournamatch' ); ?></label>
								</th>
								<td>
									<?php
									trn_display_media_input(
										array(
											'id'    => 'trn-game-thumbnail',
											'value' => $game->thumbnail_id,
										)
									);
									?>
									<p class="description"><?php esc_html_e( 'The game thumbnail is displayed on the games list page and also when no event specific thumbnail is selected.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field form-required term-name-wrap">
								<th scope="row">
									<label for="trn-game-banner-button"><?php esc_html_e( 'Game Banner', 'tournamatch' ); ?></label>
								</th>
								<td>
									<?php
									trn_display_media_input(
										array(
											'id'    => 'trn-game-banner',
											'value' => $game->banner_id,
											'width' => 400,
										)
									);
									?>
									<p class="description"><?php esc_html_e( 'The game banner is displayed when no event specific thumbnail is selected.', 'tournamatch' ); ?></p>
								</td>
							</tr>
						</table>
						<div class="trn-edit-game-actions" style="margin-top: 20px;">
							<input type="submit" id="trn-update-game-button" class="button button-primary"
									value="<?php esc_html_e( 'Update', 'tournamatch' ); ?>">
							<span id="delete-link">
								<a class="delete" href="
								<?php
								trn_esc_route_e(
									'admin.games.delete',
									array(
										'id'       => $game->game_id,
										'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-games' ),
									)
								);
								?>
														"><?php esc_html_e( 'Delete', 'tournamatch' ); ?></a>
							</span>
						</div>
					</form>
				</div>
				<?php

				$options = [
					'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
					'id'         => $game_id,
					'language'   => array(
						'failure'        => esc_html__( 'Error', 'tournamatch' ),
						'success'        => esc_html__( 'Success', 'tournamatch' ),
						'update_message' => esc_html__( 'The game was updated.', 'tournamatch' ),
					),
				];

				wp_register_script( 'trn-admin-edit-game', plugins_url( '../dist/js/edit-game.js', __FILE__ ), array( 'tournamatch' ), '3.24.0', true );
				wp_localize_script( 'trn-admin-edit-game', 'trn_edit_game_options', $options );
				wp_enqueue_script( 'trn-admin-edit-game' );
				break;

			default:
				$list_table = new \Tournamatch_Game_List_Table();
				$list_table->prepare_items();
				?>
				<style type="text/css">
					td.trn-admin-game-thumbnail-warning {
						border: 1px solid #ffeeba;
						background-color: #fff3cd;
						color: #856404;
						font-weight: bold;
					}
					.d-none {
						display: none;
					}
				</style>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Games', 'tournamatch' ); ?></h1>
					<?php

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$search_text = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

					if ( 0 < strlen( $search_text ) ) {
						echo '<span class="subtitle">';
						printf(
							/* translators: %s: Search query. */
							esc_html__( 'Search results for: %s', 'tournamatch' ),
							'<strong>' . esc_attr( $search_text ) . '</strong>'
						);
						echo '</span>';
					}
					?>
					<hr class="wp-header-end">
					<div id="trn-game-thumbnail-warning" class="d-none">
						<p class="notice notice-warning">
							<strong><?php esc_html_e( 'Action Required!', 'tournamatch' ); ?></strong>
							<?php esc_html_e( 'Games exist below with a thumbnail set to a string (highlighted). You should edit these games and re-select the thumbnail. Any game with a thumbnail set to a string (the highlighted ones) will stop working in 4.4.', 'tournamatch' ); ?>
						</p>
					</div>
					<div id="col-container">
						<div id="col-left">
							<div class="col-wrap">
								<div class="form-wrap">
									<h2>
										<?php esc_html_e( 'Add New Game', 'tournamatch' ); ?>
									</h2>
									<div id="trn-create-game-response"></div>
									<form method="post" action="<?php trn_esc_route_e( 'admin.games' ); ?>"
											id="trn-new-game-form">
										<div class="form-field form-required">
											<label for="trn-game-name"><?php esc_html_e( 'Name', 'tournamatch' ); ?></label>
											<input type="text" id="trn-game-name" name="trn-game-name" required>
											<p><?php esc_html_e( 'The title of the game as it appears on your site.', 'tournamatch' ); ?></p>
										</div>
										<div class="form-field form-required">
											<label for="trn-game-platform"><?php esc_html_e( 'Platform', 'tournamatch' ); ?></label>
											<input type="text" id="trn-game-platform" name="trn-game-platform">
											<p><?php esc_html_e( 'The platform of the game. Useful for organizing games by platform in the front end menu.', 'tournamatch' ); ?></p>
										</div>
										<div class="form-field form-required">
											<label for="trn-game-thumbnail-button"><?php esc_html_e( 'Game Thumbnail', 'tournamatch' ); ?></label>
											<?php trn_display_media_input( array( 'id' => 'trn-game-thumbnail' ) ); ?>
											<p class="description"><?php esc_html_e( 'The game thumbnail is displayed on the games list page and also when no event specific thumbnail is selected. Size Recommendation: 100 x 100 pixels.', 'tournamatch' ); ?></p>
										</div>
										<div class="form-field form-required">
											<label for="trn-game-banner-button"><?php esc_html_e( 'Game Banner', 'tournamatch' ); ?></label>
											<?php
											trn_display_media_input(
												array(
													'id' => 'trn-game-banner',
													'width' => 400,
												)
											);
											?>
											<p class="description"><?php esc_html_e( 'The game banner is displayed when no event specific banner is selected. Size Recommendations: An image with ratio 4-1 (width-height); the larger the better.', 'tournamatch' ); ?></p>
										</div>
										<p class="submit">
											<input id="trn-create-game-button" type="submit"
													class="button button-primary"
													value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>">
										</p>
									</form>
								</div>
							</div>
						</div>
						<div id="col-right">
							<div id="trn-games-list-response"></div>
							<form method="get" id="trn_games_list_table_filter">
								<?php
								$list_table->views();
								$list_table->search_box( esc_html__( 'Search Games', 'tournamatch' ), 'trn_search_games_input' );
								?>
								<input type="hidden" name="page" value="games"/>
								<?php
								$list_table->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<?php
				$options = [
					'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
					'language'   => array(
						'failure'                => esc_html__( 'Error', 'tournamatch' ),
						'success'                => esc_html__( 'Success', 'tournamatch' ),
						'success_message'        => esc_html__( 'New game {0} has been created.', 'tournamatch' ),
						'delete_message'         => esc_html__( 'The game was deleted.', 'tournamatch' ),
						'upload_success_message' => esc_html__( 'Game image has been uploaded.', 'tournamatch' ),
					),
				];

				wp_register_script( 'trn-admin-manage-games', plugins_url( '../dist/js/games.js', __FILE__ ), array( 'tournamatch' ), '3.25.0', true );
				wp_localize_script( 'trn-admin-manage-games', 'trn_manage_games_options', $options );
				wp_enqueue_script( 'trn-admin-manage-games' );
				break;
		}
	}

	/**
	 * Handles pages that must be processed before any headers are sent.
	 *
	 * @since 4.0.0
	 */
	public function pre_headers() {
		global $wpdb;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'dashboard';

		switch ( $action ) {
			case 'delete-confirm':
				check_admin_referer( 'tournamatch-bulk-games' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;

				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $id ) );

				wp_safe_redirect( trn_route( 'admin.games' ) );
				break;
		}
	}
}

new Game();
