<?php
/**
 * Manages ladder admin pages.
 *
 * @link  https://www.tournamatch.com
 * @since 4.0.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Manages ladder admin pages.
 *
 * @since 4.0.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Ladder {

	/**
	 * Initializes the ladder admin components.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'setup_menu' ) );

		$post_hooks = array();

		array_walk(
			$post_hooks,
			function( $callable, $action ) {
				add_action( "admin_post_$action", $callable );
			}
		);

		add_action( 'load-toplevel_page_trn-ladders', array( $this, 'pre_headers' ) );
	}

	/**
	 * Initialize the menu for ladder screens.
	 *
	 * @since 4.0.0
	 */
	public function setup_menu() {
		/*
		 * Icons are sourced from here: https://github.com/encharm/Font-Awesome-SVG-PNG/tree/master/black/svg
		 */

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$icon = base64_encode( '<svg width="20" height="20" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path fill="#9ca2a7" d="M381 1620q0 80-54.5 126t-135.5 46q-106 0-172-66l57-88q49 45 106 45 29 0 50.5-14.5t21.5-42.5q0-64-105-56l-26-56q8-10 32.5-43.5t42.5-54 37-38.5v-1q-16 0-48.5 1t-48.5 1v53h-106v-152h333v88l-95 115q51 12 81 49t30 88zm2-627v159h-362q-6-36-6-54 0-51 23.5-93t56.5-68 66-47.5 56.5-43.5 23.5-45q0-25-14.5-38.5t-39.5-13.5q-46 0-81 58l-85-59q24-51 71.5-79.5t105.5-28.5q73 0 123 41.5t50 112.5q0 50-34 91.5t-75 64.5-75.5 50.5-35.5 52.5h127v-60h105zm1409 319v192q0 13-9.5 22.5t-22.5 9.5h-1216q-13 0-22.5-9.5t-9.5-22.5v-192q0-14 9-23t23-9h1216q13 0 22.5 9.5t9.5 22.5zm-1408-899v99h-335v-99h107q0-41 .5-121.5t.5-121.5v-12h-2q-8 17-50 54l-71-76 136-127h106v404h108zm1408 387v192q0 13-9.5 22.5t-22.5 9.5h-1216q-13 0-22.5-9.5t-9.5-22.5v-192q0-14 9-23t23-9h1216q13 0 22.5 9.5t9.5 22.5zm0-512v192q0 13-9.5 22.5t-22.5 9.5h-1216q-13 0-22.5-9.5t-9.5-22.5v-192q0-13 9.5-22.5t22.5-9.5h1216q13 0 22.5 9.5t9.5 22.5z"/></svg>' );

		add_menu_page(
			esc_html__( 'Ladders', 'tournamatch' ),
			esc_html__( 'Ladders', 'tournamatch' ),
			'manage_tournamatch',
			'trn-ladders',
			array( $this, 'ladders' ),
			'data:image/svg+xml;base64,' . $icon
		);
		add_submenu_page(
			'trn-ladders',
			esc_html__( 'All Ladders', 'tournamatch' ),
			esc_html__( 'All Ladders', 'tournamatch' ),
			'manage_tournamatch',
			'trn-ladders',
			array( $this, 'ladders' )
		);
		add_submenu_page(
			'trn-ladders',
			esc_html__( 'Add New', 'tournamatch' ),
			esc_html__( 'Add New', 'tournamatch' ),
			'manage_tournamatch',
			'trn-ladders-new',
			array( $this, 'create' )
		);
	}

	/**
	 * Displays the ladder screens.
	 *
	 * @since 4.0.0
	 */
	public function ladders() {
		global $wpdb;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'dashboard';

		switch ( $action ) {
			case 'delete':
				check_admin_referer( 'tournamatch-bulk-ladders' );

				$id      = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$message = '<p class="notice notice-error">' . esc_html__( 'Are you sure you want to delete this ladder? All match history and competitor participation history for this ladder will also be deleted. Consider making this ladder inactive if you want to end future participation while preserving match history.', 'tournamatch' ) . '<br><br>' . esc_html__( 'Delete?', 'tournamatch' ) . ' <a href="' . trn_route(
					'admin.ladders.delete-confirm',
					array(
						'id'       => $id,
						'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-ladders' ),
					)
				) . '">' . esc_html__( 'Yes', 'tournamatch' ) . '</a> ' . esc_html__( 'or', 'tournamatch' ) . ' <a href="' . trn_route( 'admin.ladders' ) . '">' . esc_html__( 'No', 'tournamatch' ) . '</a></p>';
				trn_admin_message( esc_html__( 'Delete Ladder', 'tournamatch' ), $message );
				break;

			case 'edit':
				check_admin_referer( 'tournamatch-bulk-ladders' );

				$ladder_id    = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$ladder       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $ladder_id ) );
				$participants = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `ladder_id` = %d", $ladder_id ) );

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline">
						<?php esc_html_e( 'Edit Ladder', 'tournamatch' ); ?>
					</h1>
					<?php if ( $participants > 0 ) : ?>
						<div class="notice notice-info">
							<p><?php esc_html_e( 'Numerous fields are disabled for any ladder that already has participants.', 'tournamatch' ); ?></p>
						</div>
					<?php endif; ?>
					<?php $this->form( $ladder, $participants, 'update' ); ?>
				</div>
				<?php
				break;

			default:
				$list_table = new \Tournamatch_Ladder_List_Table();
				$list_table->prepare_items();

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Ladders', 'tournamatch' ); ?></h1>
					<?php
					echo ' <a href="' . esc_url( trn_route( 'admin.ladders.create' ) ) . '" class="page-title-action">' . esc_html__( 'Add New', 'tournamatch' ) . '</a>';

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

					<form method="get" id="trn_ladders_list_table_filter">
						<?php
						$list_table->views();
						$list_table->search_box( esc_html__( 'Search Ladders', 'tournamatch' ), 'trn_search_ladders_input' );
						?>
						<input type="hidden" name="page" value="ladders"/>
						<?php
						$list_table->display();
						?>
					</form>
				</div>
				<?php
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
			case 'clone':
				check_admin_referer( 'tournamatch-bulk-ladders' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;

				$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $id ), ARRAY_A );

				unset( $ladder['ladder_id'] );
				$ladder['name']         = 'Copy of ' . $ladder['name'];
				$ladder['created_date'] = date( 'Y-m-d H:i:s' );

				$wpdb->insert( $wpdb->prefix . 'trn_ladders', $ladder );
				$id = $wpdb->insert_id;

				wp_safe_redirect(
					trn_route(
						'admin.ladders.edit',
						array(
							'id'       => $id,
							'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-ladders' ),
						)
					)
				);
				break;

			case 'delete-confirm':
				check_admin_referer( 'tournamatch-bulk-ladders' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;

				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_matches` WHERE competition_id = %d AND competition_type = 'ladders'", $id ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_challenges` WHERE ladder_id = %d", $id ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_ladders_entries` WHERE ladder_id = %d", $id ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_ladders` WHERE ladder_id = %d LIMIT 1", $id ) );

				wp_safe_redirect( trn_route( 'admin.ladders' ) );
				break;
		}
	}

	/**
	 * Handles the create new ladder page.
	 *
	 * @since 4.0.0
	 */
	public function create() {
		?>
		<div class="wrap">
			<h1 class="class-heading-inline">
				<?php esc_html_e( 'Add New Ladder', 'tournamatch' ); ?>
			</h1>
		<?php $this->form(); ?>
		</div>
		<?php
	}

	/**
	 * Displays the admin ladder form.
	 *
	 * @since 4.0.0
	 *
	 * @param object $ladder The ladder.
	 * @param array  $participants An array of ladder participants.
	 * @param string $form_state Indicates if we are creating or updating a ladder.
	 */
	private function form( $ladder = null, $participants = [], $form_state = 'create' ) {
		global $wpdb;

		$current_games = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}trn_games`" );

		?>
		<style type="text/css">
			#trn-ladder-form .form-field input, #trn-ladder-form .form-field select {
				width: 25em;
			}
			@media screen and (max-width: 782px) {
				#trn-ladder-form .form-field input, #trn-ladder-form .form-field select {
					width: 100%;
				}
			}
		</style>
		<form id="trn-ladder-form" action="#" method="post" enctype="multipart/form-data" >
			<h2 class="title"><?php esc_html_e( 'General Ladder Info', 'tournamatch' ); ?></h2>
			<div id="trn-admin-manage-ladder-response"></div>
			<table class="form-table" role="presentation">
				<tr class="form-field form-required">
					<th scope="row">
						<label for="name"><?php esc_html_e( 'Name', 'tournamatch' ); ?> <span class="description"><?php esc_html_e( '(required)', 'tournamatch' ); ?></span></label>
					</th>
					<td>
						<input type="text" id="name" name="name" value="<?php echo isset( $ladder->name ) ? esc_html( $ladder->name ) : ''; ?>" required/>
						<p class="description"><?php esc_html_e( 'The name displayed to users for the ladder.', 'tournamatch' ); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="game_id"><?php esc_html_e( 'Game', 'tournamatch' ); ?>:</label>
					</th>
					<td>
						<?php if ( count( $current_games ) > 0 ) : ?>
							<select name='game_id' id='game_id'>
								<option value='0' <?php echo ( isset( $ladder->game_id ) && ( '0' === $ladder->game_id ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'None', 'tournamatch' ); ?></option>
								<?php foreach ( $current_games as $game ) : ?>
									<option value="<?php echo intval( $game->game_id ); ?>" <?php echo ( isset( $ladder->game_id ) && ( $ladder->game_id === $game->game_id ) ) ? 'selected' : ''; ?>><?php echo esc_html( $game->name ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Choose corresponding game (e.g. Madden, AoE, etc.)', 'tournamatch' ); ?></p>
						<?php else : ?>
							<?php /* translators: Opening and closing anchor tags. */ ?>
							<p><?php esc_html_e( 'There are no Game Types made.', 'tournamatch' ); ?> <?php echo sprintf( esc_html__( 'Click %1$s here %2$s if you want to create one or you may proceed without one.', 'tournamatch' ), "<a href='" . esc_url( trn_route( 'admin.games' ) ) . "'>", '</a>' ); ?> *</p>
							<input type='hidden' id="game_id" name='game_id' value='0'>
						<?php endif; ?>
					</td>
				</tr>
				<tr class="form-field trn-points-form-group">
					<th scope="row">
						<label for="win_points"><?php esc_html_e( 'Win', 'tournamatch' ); ?>:</label>
					</th>
					<td>
						<input type="number" id="win_points" name="win_points" value="<?php echo isset( $ladder->win_points ) ? intval( $ladder->win_points ) : '3'; ?>">
					</td>
				</tr>
				<tr class="form-field trn-points-form-group">
					<th scope="row">
						<label for="loss_points"><?php esc_html_e( 'Loss', 'tournamatch' ); ?>:</label>
					</th>
					<td>
						<input type="number" id="loss_points" name="loss_points" value="<?php echo isset( $ladder->loss_points ) ? intval( $ladder->loss_points ) : '1'; ?>">
					</td>
				</tr>
				<tr class="form-field trn-points-form-group">
					<th scope="row">
						<label for="draw_points"><?php esc_html_e( 'Tie', 'tournamatch' ); ?>:</label>
					</th>
					<td>
						<input type="number" id="draw_points" name="draw_points" value="<?php echo isset( $ladder->draw_points ) ? intval( $ladder->draw_points ) : '2'; ?>">
						<p class="description"><?php esc_html_e( 'Points awarded for wins, losses and draws.', 'tournamatch' ); ?></p>
					</td>
				</tr>
			</table>
			<h2 class="title"><?php esc_html_e( 'Match Settings', 'tournamatch' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr class="form-field">
					<th scope="row">
						<label for="competitor_type"><?php esc_html_e( 'Competition', 'tournamatch' ); ?>:</label>
					</th>
					<td>
						<select id="competitor_type" name="competitor_type" class="form-control" <?php echo ( ( 'update' === $form_state ) && ( $participants > 0 ) ) ? 'disabled' : ''; ?>>
							<option value="players" <?php echo ( isset( $ladder->competitor_type ) && ( 'players' === $ladder->competitor_type ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Singles', 'tournamatch' ); ?></option>
							<option value="teams" <?php echo ( isset( $ladder->competitor_type ) && ( 'teams' === $ladder->competitor_type ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Teams', 'tournamatch' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Player vs player or team vs team.', 'tournamatch' ); ?></p>
					</td>
				</tr>
				<tr class="form-field" id="team_size_group">
					<th scope="row">
						<label for="team_size"><?php esc_html_e( 'Players per Team', 'tournamatch' ); ?>:</label>
					</th>
					<td>
						<input type='number' id='team_size' name='team_size' value="<?php echo isset( $ladder->team_size ) ? intval( $ladder->team_size ) : 2; ?>">
						<p class="description"><?php esc_html_e( 'Number of players per team.', 'tournamatch' ); ?></p>
					</td>
				</tr>
			</table>
			<h2 class="title"><?php esc_html_e( 'Challenge Settings', 'tournamatch' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php if ( ! get_option( 'tournamatch_options' )['open_play_enabled'] ) : ?>
					<div class="notice notice-warning">
						<?php /* translators: Opening and closing HTML anchor tags. */ ?>
						<p><?php printf( esc_html__( '%1$s Attention! %2$s Open play is disabled. At least one of the below must be enabled or competitors won\'t be able to report any results.', 'tournamatch' ), '<strong>', '</strong>' ); ?></p>
					</div>
				<?php endif; ?>
				<tr class="form-field">
					<th scope="row">
						<label for="direct_challenges"><?php esc_html_e( 'Direct Challenges:', 'tournamatch' ); ?></label>
					</th>
					<td>
						<select id="direct_challenges" name="direct_challenges">
							<option value='enabled'  <?php echo ( isset( $ladder->direct_challenges ) && ( 'enabled' === $ladder->direct_challenges ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Enabled', 'tournamatch' ); ?></option>
							<option value='disabled' <?php echo ( isset( $ladder->direct_challenges ) && ( 'disabled' === $ladder->direct_challenges ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Disabled', 'tournamatch' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Enable or disable direct challenges (challenger directly to challengee).', 'tournamatch' ); ?></p>
					</td>
				</tr>
			</table>
			<h2 class="title"><?php esc_html_e( 'Descriptions and Rules', 'tournamatch' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr class="form-field">
					<th scope="row">
						<label for="rules"><?php esc_html_e( 'Rules', 'tournamatch' ); ?>:</label>
					</th>
					<td>
						<textarea id='rules' name='rules' rows="10"><?php echo isset( $ladder->rules ) ? wp_kses_post( stripslashes( $ladder->rules ) ) : ''; ?></textarea>
						<p class="description"><?php esc_html_e( 'The rules for the tournament. HTML is allowed.', 'tournamatch' ); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="visibility"><?php esc_html_e( 'Visibility:', 'tournamatch' ); ?></label>
					</th>
					<td>
						<select id="visibility" name="visibility">
							<option value='visible' <?php echo ( isset( $ladder->visibility ) && ( 'visible' === $ladder->visibility ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Visible', 'tournamatch' ); ?></option>
							<option value='hidden' <?php echo ( isset( $ladder->visibility ) && ( 'hidden' === $ladder->visibility ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Hidden', 'tournamatch' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Toggle display of this ladder outside Admin.', 'tournamatch' ); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Active:', 'tournamatch' ); ?></label>
					</th>
					<td>
						<select id="status" name="status">
							<option value='active' <?php echo ( isset( $ladder->status ) && ( 'active' === $ladder->status ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Yes', 'tournamatch' ); ?></option>
							<option value='inactive' <?php echo ( isset( $ladder->status ) && ( 'inactive' === $ladder->status ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'No', 'tournamatch' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Toggle activity such as reporting, confirming, joining, etc.', 'tournamatch' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type='submit' id='create-ladder-button' value='<?php echo ( ( 'create' === $form_state ) ? esc_html__( 'Create Ladder', 'tournamatch' ) : esc_html__( 'Save Ladder', 'tournamatch' ) ); ?>' class="button button-primary">
			</p>
		</form>

		<?php

		$options = array(
			'api_url'    => isset( $ladder ) ? site_url( "wp-json/tournamatch/v1/ladders/{$ladder->ladder_id}" ) : site_url( 'wp-json/tournamatch/v1/ladders/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'language'   => array(
				'failure'         => esc_html__( 'Error', 'tournamatch' ),
				'success'         => esc_html__( 'Success', 'tournamatch' ),
				'success_message' => esc_html__( 'The ladder has been updated.', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn-ladder-form', plugins_url( '../dist/js/ladder-form.js', __FILE__ ), array( 'tournamatch' ), '3.24.0', true );
		wp_localize_script( 'trn-ladder-form', 'trn_admin_ladder_form_options', $options );
		wp_enqueue_script( 'trn-ladder-form' );
	}
}

new Ladder();
