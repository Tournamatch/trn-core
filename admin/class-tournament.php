<?php
/**
 * Manages tournament admin pages.
 *
 * @link  https://www.tournamatch.com
 * @since 4.0.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournament admin pages.
 *
 * @since 4.0.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Tournament {

	/**
	 * Initializes the tournament admin components.
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

		add_action( 'load-toplevel_page_trn-tournaments', array( $this, 'pre_headers' ) );
	}

	/**
	 * Initialize the menu for tournament screens.
	 *
	 * @since 4.0.0
	 */
	public function setup_menu() {
		/*
		 * Icons are sourced from here: https://github.com/encharm/Font-Awesome-SVG-PNG/tree/master/black/svg
		 */

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$icon = base64_encode( '<svg width="20" height="20" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path fill="#9ca2a7" d="M522 883q-74-162-74-371h-256v96q0 78 94.5 162t235.5 113zm1078-275v-96h-256q0 209-74 371 141-29 235.5-113t94.5-162zm128-128v128q0 71-41.5 143t-112 130-173 97.5-215.5 44.5q-42 54-95 95-38 34-52.5 72.5t-14.5 89.5q0 54 30.5 91t97.5 37q75 0 133.5 45.5t58.5 114.5v64q0 14-9 23t-23 9h-832q-14 0-23-9t-9-23v-64q0-69 58.5-114.5t133.5-45.5q67 0 97.5-37t30.5-91q0-51-14.5-89.5t-52.5-72.5q-53-41-95-95-113-5-215.5-44.5t-173-97.5-112-130-41.5-143v-128q0-40 28-68t68-28h288v-96q0-66 47-113t113-47h576q66 0 113 47t47 113v96h288q40 0 68 28t28 68z"/></svg>' );

		add_menu_page(
			esc_html__( 'Tournaments', 'tournamatch' ),
			esc_html__( 'Tournaments', 'tournamatch' ),
			'manage_tournamatch',
			'trn-tournaments',
			array( $this, 'tournaments' ),
			'data:image/svg+xml;base64,' . $icon
		);
		add_submenu_page(
			'trn-tournaments',
			esc_html__( 'All Tournaments', 'tournamatch' ),
			esc_html__( 'All Tournaments', 'tournamatch' ),
			'manage_tournamatch',
			'trn-tournaments',
			array( $this, 'tournaments' )
		);
		add_submenu_page(
			'trn-tournaments',
			esc_html__( 'Add New', 'tournamatch' ),
			esc_html__( 'Add New', 'tournamatch' ),
			'manage_tournamatch',
			'trn-tournaments-new',
			array( $this, 'create' )
		);
	}

	/**
	 * Displays the tournament screens.
	 *
	 * @since 4.0.0
	 */
	public function tournaments() {
		global $wpdb;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'dashboard';

		switch ( $action ) {
			case 'delete':
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

				$message = '<p class="notice notice-error">' . esc_html__( 'Are you sure you want to delete this tournament? All match history and competitor participation history for this event will also be deleted. Consider making this tournament inactive if you want to end future participation while preserving match history.', 'tournamatch' ) . '<br><br>' . esc_html__( 'Delete? ', 'tournamatch' ) . '<a href="' . trn_route(
					'admin.tournaments.delete-confirm',
					array(
						'id'       => $id,
						'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-tournaments' ),
					)
				) . '">' . esc_html__( 'Yes', 'tournamatch' ) . '</a> ' . esc_html__( 'or', 'tournamatch' ) . ' <a href="' . trn_route( 'admin.tournaments' ) . '">' . esc_html__( 'No', 'tournamatch' ) . '</a></p>';
				trn_admin_message( esc_html__( 'Delete Tournament', 'tournamatch' ), $message );
				break;

			case 'reset':
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

				$message = '<p class="notice notice-error">' . esc_html__( 'Are you sure you want to reset this tournament? All match history and competitor participation history for this event will also be reset.', 'tournamatch' ) . '<br><br><a href="' . trn_route(
					'admin.tournaments.reset-confirm',
					array(
						'id'       => $id,
						'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-tournaments' ),
					)
				) . '">' . esc_html__( 'Confirm', 'tournamatch' ) . '</a> ' . esc_html__( 'or', 'tournamatch' ) . ' <a href="' . trn_route( 'admin.tournaments' ) . '">' . esc_html__( 'Nevermind', 'tournamatch' ) . '</a></p>';
				trn_admin_message( esc_html__( 'Reset Tournament', 'tournamatch' ), $message );
				break;

			case 'edit':
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id           = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$tournament   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $id ) );
				$participants = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_id` = %d", $id ) );

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline">
						<?php esc_html_e( 'Edit Tournament', 'tournamatch' ); ?>
					</h1>
					<?php if ( $participants >= 0 ) : ?>
						<div class="notice notice-info">
							<p><?php esc_html_e( 'You may not change the \'Competition\' mode for any tournament that already has already accepted registrations.', 'tournamatch' ); ?></p>
							<p><?php esc_html_e( 'Numerous fields are disabled for any tournament that already has already started.', 'tournamatch' ); ?></p>
						</div>
					<?php endif; ?>
					<?php $this->form( $tournament, $participants, 'update' ); ?>
				</div>
				<?php
				break;

			case 'registration':
				include __TRNPATH . 'admin/tournament-registrations.php';
				break;

			default:
				$list_table = new \Tournamatch_Tournament_List_Table();
				$list_table->prepare_items();

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Tournaments', 'tournamatch' ); ?></h1>
					<?php
					echo ' <a href="' . esc_url( trn_route( 'admin.tournaments.create' ) ) . '" class="page-title-action">' . esc_html__( 'Add New', 'tournamatch' ) . '</a>';

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
					<div class="notice notice-info">
						<p><?php esc_html_e( 'Tournaments require at least 4 people to start.', 'tournamatch' ); ?></p>
					</div>
					<form method="get" id="trn_tournaments_list_table_filter">
						<?php
						$list_table->views();
						$list_table->search_box( esc_html__( 'Search Tournaments', 'tournamatch' ), 'trn_search_tournaments_input' );
						?>
						<input type="hidden" name="page" value="trn-tournaments"/>
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
	 * Handles the create new tournament page.
	 *
	 * @since 4.0.0
	 */
	public function create() {
		?>
		<div class="wrap">
			<h1 class="class-heading-inline">
				<?php esc_html_e( 'Add New Tournament', 'tournamatch' ); ?>
			</h1>
		<?php $this->form(); ?>
		</div>
		<?php
	}

	/**
	 * Displays the admin tournament form.
	 *
	 * @since 4.0.0
	 *
	 * @param object $tournament The tournament.
	 * @param array  $participants An array of tournament participants.
	 * @param string $form_state Indicates if we are creating or updating a tournament.
	 */
	private function form( $tournament = null, $participants = [], $form_state = 'create' ) {
		global $wpdb;

		$has_started   = ( isset( $tournament ) && ( 'in_progress' === $tournament->status ) );
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
				'content' => __( 'General Tournament Info', 'tournamatch' ),
				'fields'  => array(
					'name'         => array(
						'id'          => 'name',
						'label'       => __( 'Name', 'tournamatch' ),
						'required'    => true,
						'type'        => 'text',
						'description' => __( 'The name displayed to users for the tournament.', 'tournamatch' ),
						'value'       => isset( $tournament->name ) ? $tournament->name : '',
					),
					'game_id'      => array(
						'id'          => 'game_id',
						'label'       => __( 'Game', 'tournamatch' ),
						'type'        => 'select',
						'description' => __( 'Choose corresponding game (e.g. Madden, AoE, etc.)', 'tournamatch' ),
						'value'       => isset( $tournament->game_id ) ? intval( $tournament->game_id ) : 0,
						'options'     => $game_options,
					),
					'bracket_size' => array(
						'id'          => 'bracket_size',
						'label'       => __( 'Bracket Size', 'tournamatch' ),
						'type'        => 'select',
						'description' => __( 'Select the number of competitors for the tournament.', 'tournamatch' ),
						'disabled'    => isset( $tournament->status ) && ( ! in_array( $tournament->status, array( 'created', 'open' ), true ) ),
						'value'       => isset( $tournament->bracket_size ) ? intval( $tournament->bracket_size ) : 4,
						'options'     => array( 4, 8, 16, 32, 64, 128, 256 ),
					),
				),
			),
			array(
				'id'      => 'datetime',
				'content' => __( 'Date and Time Settings', 'tournamatch' ),
				'fields'  => array(
					'start_date' => array(
						'id'          => 'start_date',
						'label'       => __( 'Start Date and Time', 'tournamatch' ),
						'description' => __( 'The date and time the tournament is scheduled to start.', 'tournamatch' ),
						'type'        => 'datetime-local',
						'required'    => true,
						'value'       => isset( $tournament->start_date ) ? $tournament->start_date : '',
						'disabled'    => isset( $tournament->status ) && ( ! in_array( $tournament->status, array( 'created', 'open' ), true ) ),
					),
				),
			),
			array(
				'id'      => 'match',
				'content' => __( 'Match Settings', 'tournamatch' ),
				'fields'  => array(
					'competitor_type' => array(
						'id'          => 'competitor_type',
						'label'       => __( 'Competition', 'tournamatch' ),
						'type'        => 'select',
						'description' => __( 'Player vs player or team vs team.', 'tournamatch' ),
						'value'       => isset( $tournament->competitor_type ) ? $tournament->competitor_type : 'players',
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
					'team_size'       => array(
						'id'          => 'team_size',
						'label'       => __( 'Players per Team', 'tournamatch' ),
						'type'        => 'number',
						'description' => __( 'Number of players per team.', 'tournamatch' ),
						'value'       => isset( $tournament->team_size ) ? intval( $tournament->team_size ) : 2,
					),
				),
			),
			array(
				'id'      => 'other',
				'content' => __( 'Other Settings', 'tournamatch' ),
				'fields'  => array(
					'rules'      => array(
						'id'          => 'rules',
						'label'       => __( 'Rules', 'tournamatch' ),
						'description' => __( 'The rules for the tournament. HTML is allowed.', 'tournamatch' ),
						'type'        => 'textarea',
						'value'       => isset( $tournament->rules ) ? $tournament->rules : '',
					),
					'visibility' => array(
						'id'          => 'visibility',
						'label'       => __( 'Visibility', 'tournamatch' ),
						'description' => __( 'Toggle display of this tournament outside Admin.', 'tournamatch' ),
						'type'        => 'select',
						'value'       => isset( $tournament->visibility ) ? $tournament->visibility : 'visible',
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
				),
			),
		);

		$form = array(
			'id'       => 'trn_tournament_form',
			'sections' => $sections,
			'submit'   => array(
				'id'      => 'trn-save-button',
				'content' => ( 'create' === $form_state ) ? __( 'Create Tournament', 'tournamatch' ) : __( 'Save Changes', 'tournamatch' ),
			),
		);
		?>
		<style type="text/css">
			#trn_tournament_form .form-field input, #trn_tournament_form .form-field select {
				width: 25em;
			}
			@media screen and (max-width: 782px) {
				#trn_tournament_form .form-field input, #trn_tournament_form .form-field select {
					width: 100%;
				}
			}
		</style>
		<div id="trn-admin-manage-tournament-response"></div>
		<?php

		trn_admin_form( $form, $tournament );

		$tournament_id = isset( $tournament ) ? $tournament->tournament_id : 0;

		$options = array(
			'api_url'      => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'   => wp_create_nonce( 'wp_rest' ),
			'redirect_url' => trn_route( 'admin.tournaments' ),
			'has_started'  => $has_started,
			'language'     => array(
				'failure'         => esc_html__( 'Error', 'tournamatch' ),
				'success'         => esc_html__( 'Success', 'tournamatch' ),
				'success_message' => esc_html__( 'The tournament has been updated.', 'tournamatch' ),
			),
		);

		if ( 0 < $tournament_id ) {
			$options['id'] = $tournament_id;
		}

		wp_register_script( 'trn-tournament-form', plugins_url( '../dist/js/tournament-form.js', __FILE__ ), array( 'tournamatch' ), '3.27.0', true );
		wp_localize_script( 'trn-tournament-form', 'trn_admin_tournament_form_options', $options );
		wp_enqueue_script( 'trn-tournament-form' );
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
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

				$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $id ), ARRAY_A );

				unset( $tournament['tournament_id'] );
				$tournament['name']   = 'Copy of ' . $tournament['name'];
				$tournament['status'] = 'open';

				$wpdb->insert( $wpdb->prefix . 'trn_tournaments', $tournament );

				$id = $wpdb->insert_id;

				wp_safe_redirect(
					trn_route(
						'admin.tournaments.edit',
						array(
							'id'       => $id,
							'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-tournaments' ),
						)
					)
				);
				break;

			case 'start':
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

				$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $id ) );
				if ( in_array( $tournament->status, [ 'created', 'open' ], true ) ) {
					initialize_tournament( $id );
				}

				wp_safe_redirect( trn_route( 'admin.tournaments' ) );
				break;

			case 'finish':
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

				if ( 0 !== $id ) {
					$wpdb->update( $wpdb->prefix . 'trn_tournaments', array( 'status' => 'complete' ), array( 'tournament_id' => $id ) );
				}

				wp_safe_redirect( trn_route( 'admin.tournaments' ) );
				break;

			case 'reset-confirm':
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_matches WHERE competition_id = %d AND competition_type = 'tournaments'", $id ) );
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}trn_tournaments SET `status` = 'open' WHERE tournament_id = %d", $id ) );

				wp_safe_redirect( trn_route( 'admin.tournaments' ) );
				break;

			case 'delete-confirm':
				check_admin_referer( 'tournamatch-bulk-tournaments' );

				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_matches WHERE competition_id = %d AND competition_type = 'tournaments'", $id ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_tournaments_entries WHERE tournament_id = %d", $id ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_tournaments WHERE tournament_id = %d LIMIT 1", $id ) );

				wp_safe_redirect( trn_route( 'admin.tournaments' ) );
				break;

			case 'remove-entry':
				check_admin_referer( 'tournamatch-remove-tournament-entry' );

				if ( current_user_can( 'manage_tournamatch' ) ) {
					$id = isset( $_REQUEST['tournament_entry_id'] ) ? intval( $_REQUEST['tournament_entry_id'] ) : 0;
					if ( 0 !== intval( $id ) ) {
						$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_entry_id` = %d", $id ) );

						$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_entry_id` = %d LIMIT 1", $id ) );

						/**
						 * Fires when a tournament competitor has been removed (a tournament registration).
						 */
						do_action( 'trn_rest_tournament_registration_deleted', $registration );

						wp_safe_redirect( trn_route( 'tournaments.single.registered', [ 'id' => $registration->tournament_id ] ) );
						exit;
					}
				}
				wp_safe_redirect( trn_route( 'tournaments.archive' ) );
				break;
		}
	}
}

new Tournament();
