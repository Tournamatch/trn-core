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
		$game_options  = array_map(
			function( $game ) {
				return array(
					'value'   => intval( $game->game_id ),
					'content' => $game->name,
				);
			},
			$current_games
		);

		$sections = array(
			array(
				'id'      => 'general',
				'content' => __( 'General Ladder Info', 'tournamatch' ),
				'fields'  => array(
					array(
						'id'          => 'name',
						'label'       => __( 'Name', 'tournamatch' ),
						'required'    => true,
						'type'        => 'text',
						'description' => __( 'The name displayed to users for the ladder.', 'tournamatch' ),
						'value'       => isset( $ladder->name ) ? $ladder->name : '',
					),
					array(
						'id'          => 'game_id',
						'label'       => __( 'Game', 'tournamatch' ),
						'type'        => 'select',
						'description' => __( 'Choose corresponding game (e.g. Madden, AoE, etc.)', 'tournamatch' ),
						'value'       => isset( $ladder->game_id ) ? intval( $ladder->game_id ) : 0,
						'options'     => $game_options,
					),
					array(
						'id'    => 'win_points',
						'label' => __( 'Win', 'tournamatch' ),
						'type'  => 'number',
						'value' => isset( $ladder->win_points ) ? intval( $ladder->win_points ) : 3,
					),
					array(
						'id'    => 'loss_points',
						'label' => __( 'Loss', 'tournamatch' ),
						'type'  => 'number',
						'value' => isset( $ladder->loss_points ) ? intval( $ladder->loss_points ) : 1,
					),
					array(
						'id'          => 'draw_points',
						'label'       => __( 'Draw', 'tournamatch' ),
						'description' => __( 'Points awarded for wins, losses and draws.', 'tournamatch' ),
						'type'        => 'number',
						'value'       => isset( $ladder->draw_points ) ? intval( $ladder->draw_points ) : 2,
					),
				),
			),
			array(
				'id'      => 'match',
				'content' => __( 'Match Settings', 'tournamatch' ),
				'fields'  => array(
					array(
						'id'          => 'competitor_type',
						'label'       => __( 'Competition', 'tournamatch' ),
						'type'        => 'select',
						'description' => __( 'Player vs player or team vs team.', 'tournamatch' ),
						'value'       => isset( $ladder->competitor_type ) ? $ladder->competitor_type : 'players',
						'disabled'    => ( 'update' === $form_state ) && ( $participants > 0 ),
						'options'     => array(
							array(
								'value'   => 'players',
								'content' => __( 'Singles', 'tournamatch' ),
							),
							array(
								'value'   => 'teams',
								'content' => __( 'Teams', 'tournamatch' ),
							),
						),
					),
					array(
						'id'          => 'team_size',
						'label'       => __( 'Players per Team', 'tournamatch' ),
						'type'        => 'number',
						'description' => __( 'Number of players per team.', 'tournamatch' ),
						'value'       => isset( $ladder->team_size ) ? intval( $ladder->team_size ) : 2,
					),
				),
			),
			array(
				'id'      => 'challenge',
				'content' => __( 'Challenge Settings', 'tournamatch' ),
				'fields'  => array(
					array(
						'id'          => 'direct_challenges',
						'label'       => __( 'Direct Challenges', 'tournamatch' ),
						'type'        => 'select',
						'description' => __( 'Enable or disable direct challenges (challenger directly to challengee).', 'tournamatch' ),
						'value'       => isset( $ladder->direct_challenges ) ? $ladder->direct_challenges : 'enabled',
						'options'     => array(
							array(
								'value'   => 'enabled',
								'content' => __( 'Enabled', 'tournamatch' ),
							),
							array(
								'value'   => 'disabled',
								'content' => __( 'Disabled', 'tournamatch' ),
							),
						),
					),
				),
			),
			array(
				'id'      => 'other',
				'content' => __( 'Other Settings', 'tournamatch' ),
				'fields'  => array(
					array(
						'id'          => 'rules',
						'label'       => __( 'Rules', 'tournamatch' ),
						'description' => __( 'The rules for the ladder. HTML is allowed.', 'tournamatch' ),
						'type'        => 'textarea',
						'value'       => isset( $ladder->rules ) ? $ladder->rules : '',
					),
					array(
						'id'          => 'visibility',
						'label'       => __( 'Visibility', 'tournamatch' ),
						'description' => __( 'Toggle display of this ladder outside Admin.', 'tournamatch' ),
						'type'        => 'select',
						'value'       => isset( $ladder->visibility ) ? $ladder->visibility : 'visible',
						'options'     => array(
							array(
								'value'   => 'visible',
								'content' => __( 'Visible', 'tournamatch' ),
							),
							array(
								'value'   => 'hidden',
								'content' => __( 'Hidden', 'tournamatch' ),
							),
						),
					),
					array(
						'id'          => 'active',
						'label'       => __( 'Active', 'tournamatch' ),
						'description' => __( 'Toggle activity such as reporting, confirming, joining, etc.', 'tournamatch' ),
						'type'        => 'select',
						'value'       => isset( $ladder->active ) ? $ladder->active : 'active',
						'options'     => array(
							array(
								'value'   => 'active',
								'content' => __( 'Active', 'tournamatch' ),
							),
							array(
								'value'   => 'inactive',
								'content' => __( 'Inactive', 'tournamatch' ),
							),
						),
					),
				),
			),
		);

		$form = array(
			'id'       => 'trn-ladder-form',
			'sections' => $sections,
			'submit'   => array(
				'id'      => 'create-ladder-button',
				'content' => ( 'create' === $form_state ) ? __( 'Create Ladder', 'tournamatch' ) : __( 'Save Changes', 'tournamatch' ),
			),
		);

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
		<div id="trn-admin-manage-ladder-response"></div>
		<?php

		trn_admin_form( $form, $ladder );

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
