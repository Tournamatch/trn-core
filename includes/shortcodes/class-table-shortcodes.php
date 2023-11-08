<?php
/**
 * Defines Tournamatch table list shortcodes.
 *
 * @link       https://www.tournamatch.com
 * @since      3.25.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Shortcodes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines Tournamatch table list shortcodes.
 *
 * @since      3.25.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Table_Shortcodes {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.25.0
	 */
	public function __construct() {
		add_shortcode( 'trn-teams-list-table', array( $this, 'teams_list_table' ) );
		add_shortcode( 'trn-players-list-table', array( $this, 'players_list_table' ) );
		add_shortcode( 'trn-challenges-list-table', array( $this, 'challenges_list_table' ) );
		add_shortcode( 'trn-matches-list-table', array( $this, 'matches_list_table' ) );
		add_shortcode( 'trn-ladder-matches-list-table', array( $this, 'ladder_matches_list_table' ) );
		add_shortcode( 'trn-ladder-standings-list-table', array( $this, 'ladder_standings_list_table' ) );
		add_shortcode( 'trn-tournament-matches-list-table', array( $this, 'tournament_matches_list_table' ) );
		add_shortcode( 'trn-competitor-ladders-list-table', array( $this, 'competitor_ladders_list_table' ) );
		add_shortcode( 'trn-competitor-tournaments-list-table', array( $this, 'competitor_tournaments_list_table' ) );
		add_shortcode( 'trn-player-teams-list-table', array( $this, 'player_teams_list_table' ) );
		add_shortcode( 'trn-competitor-match-list-table', array( $this, 'competitor_match_list_table' ) );
		add_shortcode( 'trn-team-roster-table', array( $this, 'team_roster_table' ) );

	}

	/**
	 * Shortcode to create the teams list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function teams_list_table( $atts = [], $content = null, $tag = '' ) {

		$html  = '<div id="trn-delete-team-response"></div>';
		$html .= '<table class="trn-table trn-table-striped" id="trn-teams-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-teams-table-name">' . esc_html__( 'Name', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-teams-table-created">' . esc_html__( 'Created', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-teams-table-members">' . esc_html__( 'Members', 'tournamatch' ) . '</th>';

		if ( current_user_can( 'manage_tournamatch' ) ) {
			$html .= '<th class="trn-teams-table-admin">' . esc_html__( 'Admin', 'tournamatch' ) . '</th>';
		}

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No teams to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ teams', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 teams', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total teams)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ teams', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No teams to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'language'        => array(
				'failure'        => esc_html__( 'Error', 'tournamatch' ),
				'success'        => esc_html__( 'Success', 'tournamatch' ),
				'delete_message' => esc_html__( 'The team was deleted.', 'tournamatch' ),
				'delete_team'    => esc_html__( 'Delete Team', 'tournamatch' ),
				'delete_confirm' => esc_html__( 'Are you sure you want to delete team &quot;{0}&quot;?', 'tournamatch' ),
			),
			'user_capability' => current_user_can( 'manage_tournamatch' ),
		);

		wp_register_script( 'trn-teams-list-table', plugins_url( '../../dist/js/teams.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '4.3.5', true );
		wp_localize_script( 'trn-teams-list-table', 'trn_teams_list_table_options', $options );
		wp_enqueue_script( 'trn-teams-list-table' );

		return $html;
	}

	/**
	 * Shortcode to create the players list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function players_list_table( $atts = [], $content = null, $tag = '' ) {

		$html  = '<table class="trn-table trn-table-striped trn-players-table" id="trn_players_list_table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-players-table-name">' . esc_html__( 'Name', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-players-table-joined">' . esc_html__( 'Joined', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-players-table-location">' . esc_html__( 'Location', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-players-table-teams">' . esc_html__( 'Teams', 'tournamatch' ) . '</th>';

		if ( current_user_can( 'manage_tournamatch' ) ) {
			$html .= '<th class="trn-players-table-admin">' . esc_html__( 'Admin', 'tournamatch' ) . '</th>';
		}

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No data available in table', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ players', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 players', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total players)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ players', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No players to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'language'        => array(
				'failure'     => esc_html__( 'Error', 'tournamatch' ),
				'success'     => esc_html__( 'Success', 'tournamatch' ),
				'edit_player' => esc_html__( 'Edit Player', 'tournamatch' ),
			),
			'user_capability' => current_user_can( 'manage_tournamatch' ),
		);

		wp_register_script( 'players', plugins_url( '../../dist/js/players.js', __FILE__ ), array( 'jquery', 'tournamatch', 'datatables' ), '3.21.1', true );
		wp_localize_script( 'players', 'trn_table_options', $options );
		wp_enqueue_script( 'players' );

		return $html;
	}


	/**
	 * Shortcode to create the challenges list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function challenges_list_table( $atts = [], $content = null, $tag = '' ) {

		$html  = '<div id="trn-delete-challenge-response"></div>';
		$html .= '<table class="trn-table trn-table-striped trn-challenges-table" id="trn-challenge-list-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-challenges-table-ladder">' . esc_html__( 'Ladder', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-challenges-table-challenger">' . esc_html__( 'Challenger', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-challenges-table-challengee">' . esc_html__( 'Challengee', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-challenges-table-match-time">' . esc_html__( 'Match Time', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-challenges-table-status">' . esc_html__( 'Status', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-challenges-table-actions"></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No data available in table', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ challenges', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 challenges', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total challenges)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ challenges', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No challenges to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'language'        => array(
				'failure'          => esc_html__( 'Error', 'tournamatch' ),
				'success'          => esc_html__( 'Success', 'tournamatch' ),
				'delete_message'   => esc_html__( 'The Challenge was deleted.', 'tournamatch' ),
				'delete_challenge' => esc_html__( 'Delete Challenge', 'tournamatch' ),
				'delete_confirm'   => esc_html__( 'Are you sure you want to delete Challenge &quot;{0}&quot;?', 'tournamatch' ),
				'open'             => esc_html__( '(open)', 'tournamatch' ),
				'hidden'           => esc_html__( '(hidden)', 'tournamatch' ),
			),
			'user_capability' => current_user_can( 'manage_tournamatch' ),
			'is_admin'        => current_user_can( 'manage_tournamatch' ),
		);

		wp_register_script( 'challenges', plugins_url( '../../dist/js/challenges.js', __FILE__ ), array( 'jquery', 'datatables', 'tournamatch' ), '4.3.5', true );
		wp_localize_script( 'challenges', 'trn_end_scripts', $options );
		wp_enqueue_script( 'challenges' );

		return $html;
	}

	/**
	 * Shortcode to create the matches list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function matches_list_table( $atts = [], $content = null, $tag = '' ) {

		$html  = '<table class="trn-table trn-table-striped trn-matches-table" id="match-list-table" style="width:100%">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-matches-table-event">' . esc_html__( 'Event', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-matches-table-name">' . esc_html__( 'Name', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-matches-table-result">' . esc_html__( 'Result', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-matches-table-date">' . esc_html__( 'Match Date', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-matches-table-actions"> </th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No data available in table', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ matches', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 matches', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total matches)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ matches', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No matches to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$nonce   = wp_create_nonce( 'tournamatch-bulk-matches' );
		$options = array(
			'redirect_link'   => trn_route( 'matches.archive' ),
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'language'        => array(
				'failure'            => esc_html__( 'Error', 'tournamatch' ),
				'success'            => esc_html__( 'Success', 'tournamatch' ),
				'delete_message'     => esc_html__( 'The Matches was deleted.', 'tournamatch' ),
				'delete_match'       => esc_html__( 'Delete Match', 'tournamatch' ),
				'delete_confirm'     => esc_html__( 'Are you sure you want to delete match &quot;{0}&quot;?', 'tournamatch' ),
				'details'            => esc_html__( 'Details', 'tournamatch' ),
				'view_match_details' => esc_html__( 'View Match Details', 'tournamatch' ),
				'edit_match'         => esc_html__( 'Edit Match', 'tournamatch' ),
				'view_brackets'      => esc_html__( 'View brackets to edit tournament results.', 'tournamatch' ),
			),
			'user_capability' => current_user_can( 'manage_tournamatch' ),
			'ladder_edit'     => site_url( "wp-admin/admin.php?page=trn-ladders-matches&action=edit-match&_wpnonce={$nonce}&id=" ),
		);

		wp_enqueue_script( 'trn-delete-match' );
		wp_register_script( 'trn-match-list', plugins_url( '../../dist/js/match-list.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '4.3.5', true );
		wp_localize_script( 'trn-match-list', 'trn_match_list_options', $options );
		wp_enqueue_script( 'trn-match-list' );

		return $html;
	}

	/**
	 * Shortcode to create the ladder matches list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function ladder_matches_list_table( $atts = [], $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['ladder_id'] ) ) {
			return '';
		}

		$lid = intval( $atts['ladder_id'] );

		$html  = '<table class="trn-table trn-table-striped trn-ladder-matches-table" id="trn-ladder-matches-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-ladder-matches-table-result">' . esc_html__( 'Result', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-matches-table-date">' . esc_html__( 'Match Date', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-matches-table-link"></th>';

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No ladder matches to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ ladder matches', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 ladder matches', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total ladder matches)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ ladder matches', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No ladder matches to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'redirect_link'   => trn_route( 'ladders.single.matches', array( 'id' => $lid ) ),
			'table_language'  => $table_language,
			'language'        => array(
				'failure'            => esc_html__( 'Error', 'tournamatch' ),
				'success'            => esc_html__( 'Success', 'tournamatch' ),
				'delete_message'     => esc_html__( 'The ladder matches was deleted.', 'tournamatch' ),
				'delete_match'       => esc_html__( 'Delete Ladder match', 'tournamatch' ),
				'delete_confirm'     => esc_html__( 'Are you sure you want to delete ladder match &quot;{0}&quot;?', 'tournamatch' ),
				'details'            => esc_html__( 'Details', 'tournamatch' ),
				'view_match_details' => esc_html__( 'View Match Details', 'tournamatch' ),
				'edit_match'         => esc_html__( 'Edit Match', 'tournamatch' ),
			),
			'user_capability' => current_user_can( 'manage_tournamatch' ),
			'ladder_id'       => $lid,
			'ladder_edit'     => site_url( 'wp-admin/admin.php?page=ladder-matches&action=edit-match&id=' ),
		);

		wp_enqueue_script( 'trn-delete-match' );
		wp_register_script(
			'trn-ladder-matches',
			plugins_url( '../../dist/js/ladder-matches.js', __FILE__ ),
			array(
				'tournamatch',
				'jquery',
				'datatables',
			),
			'4.3.5',
			true
		);
		wp_localize_script( 'trn-ladder-matches', 'trn_ladder_matches_options', $options );
		wp_enqueue_script( 'trn-ladder-matches' );

		return $html;
	}

	/**
	 * Shortcode to create the ladder standings list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function ladder_standings_list_table( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['ladder_id'] ) ) {
			return '';
		}

		$ladder_id = intval( $atts['ladder_id'] );

		$ladder = trn_get_ladder( $ladder_id );
		$ladder = trn_the_ladder( $ladder );

		$uses_draws    = ( '1' === trn_get_option( 'uses_draws' ) );
		$can_challenge = is_user_logged_in() && ( 'enabled' === $ladder->direct_challenges );

		$html  = '<div id="trn-remove-competitor-response"></div>';
		$html .= '<div id="trn-promote-competitor-response"></div>';
		$html .= '<table class="trn-table trn-table-striped trn-ladder-standings-table" id="ladder-standings-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-ladder-standings-table-number"></th>';
		$html .= '<th class="trn-ladder-standings-table-name">' . esc_html__( 'Name', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-standings-table-rating">' . esc_html( $ladder->ranking_mode_label ) . '</th>';
		$html .= '<th class="trn-ladder-standings-table-games-played">' . esc_html__( 'GP', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-standings-table-wins">' . esc_html__( 'W', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-standings-table-losses">' . esc_html__( 'L', 'tournamatch' ) . '</th>';
		if ( $uses_draws ) {
			$html .= '<th class="trn-ladder-standings-table-draws">' . esc_html__( 'D', 'tournamatch' ) . '</th>';
		}
		$html .= '<th class="trn-ladder-standings-table-win-percent">' . esc_html__( 'W%', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-standings-table-streak">' . esc_html__( 'Streak', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-standings-table-idle">' . esc_html__( 'Idle', 'tournamatch' ) . '</th>';

		if ( $can_challenge || current_user_can( 'manage_tournamatch' ) ) {
			$html .= '<th class="trn-ladder-standings-table-actions">' . esc_html__( 'Actions', 'tournamatch' ) . '</th>';
		}

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No competitors to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ competitors', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 competitors', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total competitors)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ competitors', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No competitors to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'language'        => array(
				'failure'                => esc_html__( 'Error', 'tournamatch' ),
				'success'                => esc_html__( 'Success', 'tournamatch' ),
				'remove_link_title'      => esc_html__( 'Remove this competitor from the ladder.', 'tournamatch' ),
				'confirm_delete_title'   => esc_html__( 'Remove Competitor', 'tournamatch' ),
				'confirm_delete_message' => esc_html__( 'Are you sure you want to remove competitor &quot;{0}&quot; from this ladder?', 'tournamatch' ),
				'edit_link_title'        => esc_html__( 'Edit competitor\'s ladder statistics.', 'tournamatch' ),
				'challenge_link_title'   => esc_html__( 'Challenge this competitor to a match.', 'tournamatch' ),
				'promote_link_title'     => esc_html__( 'Move this competitor up one rank.', 'tournamatch' ),
			),
			'ladder_id'       => intval( $ladder->ladder_id ),
			'default_target'  => $ladder->ranking_mode_field,
			'uses_draws'      => ( '1' === trn_get_option( 'uses_draws' ) ),
			'can_challenge'   => $can_challenge,
			'current_user_id' => get_current_user_id(),
			'is_admin'        => current_user_can( 'manage_tournamatch' ),
			'flag_path'       => plugins_url( 'tournamatch' ) . '/dist/images/flags/',
			'challenge_url'   => site_url( trn_get_route_roots()['challenges'] . '/create?ladder_id=' . $ladder->ladder_id . '&challengee_id=' ),
			'can_promote'     => false,
		);

		wp_register_script(
			'standings',
			plugins_url( '../../dist/js/standings.js', __FILE__ ),
			array(
				'tournamatch',
				'jquery',
				'datatables',
			),
			'4.3.5',
			true
		);
		wp_localize_script( 'standings', 'trn_ladder_standings_options', $options );
		wp_enqueue_script( 'standings' );

		return $html;

	}

	/**
	 * Shortcode to create the tournament matches list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function tournament_matches_list_table( $atts = [], $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['tournament_id'] ) ) {
			return '';
		}

		$tournament_id = intval( $atts['tournament_id'] );

		$html  = '<table class="trn-table trn-table-striped trn-tournament-matches-table" id="trn-tournament-matches-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-tournament-matches-table-competitors">' . esc_html__( 'Competitors', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-tournament-matches-table-result">' . esc_html__( 'Result', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-tournament-matches-table-date">' . esc_html__( 'Match Date', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-tournament-matches-table-link"></th>';

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No tournament matches to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ tournament matches', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 tournament matches', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total tournament matches)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ tournament matches', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No tournament matches to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'        => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'     => wp_create_nonce( 'wp_rest' ),
			'redirect_link'  => trn_route( 'tournaments.single.matches', array( 'id' => $tournament_id ) ),
			'table_language' => $table_language,
			'undecided'      => trn_get_option( 'tournament_undecided_display' ),
			'language'       => array(
				'failure'            => esc_html__( 'Error', 'tournamatch' ),
				'success'            => esc_html__( 'Success', 'tournamatch' ),
				'details'            => esc_html__( 'Details', 'tournamatch' ),
				'view_match_details' => esc_html__( 'View Match Details', 'tournamatch' ),
			),
			'tournament_id'  => $tournament_id,
		);

		wp_register_script( 'trn-tournament-matches', plugins_url( '../../dist/js/tournament-matches.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '3.25.0', true );
		wp_localize_script( 'trn-tournament-matches', 'trn_tournament_matches_options', $options );
		wp_enqueue_script( 'trn-tournament-matches' );

		return $html;
	}

	/**
	 * Shortcode to create the competitor ladders list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function competitor_ladders_list_table( $atts = [], $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['competitor_id'] ) ) {
			return '';
		}
		if ( empty( $atts['competitor_type'] ) ) {
			return '';
		}
		$competitor_id   = isset( $atts['competitor_id'] ) ? intval( $atts['competitor_id'] ) : 0;
		$competitor_type = isset( $atts['competitor_type'] ) ? sanitize_text_field( $atts['competitor_type'] ) : '';

		if ( ( 0 === $competitor_id ) || ( ! in_array( $competitor_type, array( 'players', 'teams' ), true ) ) ) {
			return '';
		}

		$uses_draws = ( '1' === trn_get_option( 'uses_draws' ) );

		$html  = '<table class="trn-table trn-table-striped trn-ladder-competitions-table" id="trn-ladder-competitions-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-ladder-competitions-table-name">' . esc_html__( 'Ladder', 'tournamatch' ) . '</th>';
		if ( 'players' === $competitor_type ) {
			$html .= '<th class="trn-ladder-competitions-table-team">' . esc_html__( 'Team', 'tournamatch' ) . '</th>';
		}
		$html .= '<th class="trn-ladder-competitions-table-joined">' . esc_html__( 'Joined', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-competitions-table-position">' . esc_html__( 'Position', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-competitions-table-wins">' . esc_html__( 'W', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-competitions-table-losses">' . esc_html__( 'L', 'tournamatch' ) . '</th>';
		if ( $uses_draws ) {
			$html .= '<th class="trn-ladder-competitions-table-draws">' . esc_html__( 'D', 'tournamatch' ) . '</th>';
		}
		$html .= '<th class="trn-ladder-competitions-table-win-percent">' . esc_html__( 'W%', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-competitions-table-streak">' . esc_html__( 'Streak', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-ladder-competitions-table-idle">' . esc_html__( 'Idle', 'tournamatch' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No ladders to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ ladders', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 ladders', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total ladders)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ ladders', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No ladders to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'uses_draws'      => $uses_draws,
			'competitor_id'   => $competitor_id,
			'competitor_type' => $competitor_type,
			'slug'            => ( 'players' === $competitor_type ) ? 'player_id' : 'team_id',
		);

		wp_register_script( 'trn-competitor-ladders', plugins_url( '../../dist/js/competitor-ladders.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '3.25.0', true );
		wp_localize_script( 'trn-competitor-ladders', 'trn_competitor_ladders_options', $options );
		wp_enqueue_script( 'trn-competitor-ladders' );

		return $html;
	}

	/**
	 * Shortcode to create the competitor tournaments list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function competitor_tournaments_list_table( $atts = [], $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['competitor_id'] ) ) {
			return '';
		}
		if ( empty( $atts['competitor_type'] ) ) {
			return '';
		}
		$competitor_id   = isset( $atts['competitor_id'] ) ? intval( $atts['competitor_id'] ) : 0;
		$competitor_type = isset( $atts['competitor_type'] ) ? sanitize_text_field( $atts['competitor_type'] ) : '';

		if ( ( 0 === $competitor_id ) || ( ! in_array( $competitor_type, array( 'players', 'teams' ), true ) ) ) {
			return '';
		}

		$html  = '<table class="trn-table trn-table-striped trn-tournament-competitions-table" id="trn-tournament-competitions-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-tournament-competitions-table-name">' . esc_html__( 'Tournament', 'tournamatch' ) . '</th>';
		if ( 'players' === $competitor_type ) {
			$html .= '<th class="trn-tournament-competitions-table-team">' . esc_html__( 'Team', 'tournamatch' ) . '</th>';
		}
		$html .= '<th class="trn-tournament-competitions-table-joined">' . esc_html__( 'Joined', 'tournamatch' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No tournaments to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ tournaments', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 tournaments', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total tournaments)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ tournaments', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No tournaments to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'competitor_id'   => $competitor_id,
			'competitor_type' => $competitor_type,
			'slug'            => ( 'players' === $competitor_type ) ? 'player_id' : 'team_id',
		);

		wp_register_script( 'trn-competitor-tournaments', plugins_url( '../../dist/js/competitor-tournaments.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '3.25.0', true );
		wp_localize_script( 'trn-competitor-tournaments', 'trn_competitor_tournaments_options', $options );
		wp_enqueue_script( 'trn-competitor-tournaments' );

		return $html;
	}

	/**
	 * Shortcode to create the player teams list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function player_teams_list_table( $atts = [], $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		if ( empty( $atts['player_id'] ) ) {
			return '';
		}
		$player_id = intval( $atts['player_id'] );

		if ( 0 === $player_id ) {
			return '';
		}

		$html  = '<table class="trn-table trn-table-striped trn-player-team-table" id="trn-player-teams-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-player-team-table-name">' . esc_html__( 'Name', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-player-team-table-rank">' . esc_html__( 'Rank', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-player-team-table-joined">' . esc_html__( 'Joined', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-player-team-table-members">' . esc_html__( 'Members', 'tournamatch' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No teams to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ teams', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 teams', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total teams)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ teams', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No teams to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'        => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'     => wp_create_nonce( 'wp_rest' ),
			'table_language' => $table_language,
			'player_id'      => $player_id,
		);

		wp_register_script( 'trn-player-teams-list-table', plugins_url( '../../dist/js/player-teams.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '3.25.0', true );
		wp_localize_script( 'trn-player-teams-list-table', 'trn_player_teams_list_table_options', $options );
		wp_enqueue_script( 'trn-player-teams-list-table' );

		return $html;
	}

	/**
	 * Shortcode to create the competitor match list table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function competitor_match_list_table( $atts = [], $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['competitor_id'] ) ) {
			return '';
		}
		if ( empty( $atts['competitor_type'] ) ) {
			return '';
		}
		$competitor_id   = isset( $atts['competitor_id'] ) ? intval( $atts['competitor_id'] ) : 0;
		$competitor_type = isset( $atts['competitor_type'] ) ? sanitize_text_field( $atts['competitor_type'] ) : '';

		if ( ( 0 === $competitor_id ) || ( ! in_array( $competitor_type, array( 'players', 'teams' ), true ) ) ) {
			return '';
		}
		$html  = '<table class="trn-table trn-table-striped trn-match-history-table" id="trn-competitor-match-list-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-match-history-event">' . esc_html__( 'Event', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-match-history-name">' . esc_html__( 'Name', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-match-history-result">' . esc_html__( 'Result', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-match-history-date">' . esc_html__( 'Date', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-match-history-details"></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No matches to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ matches', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 matches', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total matches)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ matches', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No matches to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'competitor_type' => $competitor_type,
			'competitor_id'   => $competitor_id,
		);

		wp_register_script( 'trn-competitor-match-list-table', plugins_url( '../../dist/js/competitor-match-list.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '3.25.0', true );
		wp_localize_script( 'trn-competitor-match-list-table', 'trn_competitor_match_list_table_options', $options );
		wp_enqueue_script( 'trn-competitor-match-list-table' );

		return $html;
	}

	/**
	 * Shortcode to create the team roster table.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function team_roster_table( $atts = [], $content = null, $tag = '' ) {
		global $wpdb;

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['team_id'] ) ) {
			return '';
		}

		$team_id = isset( $atts['team_id'] ) ? intval( $atts['team_id'] ) : 0;

		if ( 0 === $team_id ) {
			return '';
		}

		$html  = '<div id="trn-team-roster-response"></div>';
		$html .= '<table class="trn-table trn-table-striped trn-team-roster-table" id="trn-team-roster-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="trn-team-roster-name">' . esc_html__( 'Name', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-team-roster-title">' . esc_html__( 'Title', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-team-roster-joined">' . esc_html__( 'Joined', 'tournamatch' ) . '</th>';
		$html .= '<th class="trn-team-roster-options">' . esc_html__( 'Options', 'tournamatch' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody></tbody>';
		$html .= '</table>';

		$table_language = array(
			'sEmptyTable'     => esc_html__( 'No members to display.', 'tournamatch' ),
			'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ members', 'tournamatch' ),
			'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 members', 'tournamatch' ),
			'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total members)', 'tournamatch' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => ',',
			'sLengthMenu'     => esc_html__( 'Show _MENU_ members', 'tournamatch' ),
			'sLoadingRecords' => esc_html__( 'Loading...', 'tournamatch' ),
			'sProcessing'     => esc_html__( 'Processing...', 'tournamatch' ),
			'sSearch'         => esc_html__( 'Search:', 'tournamatch' ),
			'sZeroRecords'    => esc_html__( 'No members to display.', 'tournamatch' ),
			'oPaginate'       => [
				'sFirst'    => esc_html__( 'First', 'tournamatch' ),
				'sLast'     => esc_html__( 'Last', 'tournamatch' ),
				'sNext'     => esc_html__( 'Next', 'tournamatch' ),
				'sPrevious' => esc_html__( 'Previous', 'tournamatch' ),
			],
			'oAria'           => [
				'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'tournamatch' ),
				'sSortDescending' => esc_html__( ': activate to sort column descending', 'tournamatch' ),
			],
		);

		$team_owner      = $wpdb->get_row( $wpdb->prepare( "SELECT `user_id` AS `user_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `team_rank_id` = %d", $team_id, 1 ), ARRAY_A );
		$can_edit_roster = current_user_can( 'manage_tournamatch' ) || ( intval( $team_owner['user_id'] ) === get_current_user_id() );
		$ranks           = $wpdb->get_results( "SELECT `team_rank_id`, `title`, `weight` FROM `{$wpdb->prefix}trn_teams_ranks` ORDER BY `weight` ASC", ARRAY_A );

		$options = array(
			'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
			'table_language'  => $table_language,
			'language'        => array(
				'drop_player'       => esc_html__( 'Drop Player', 'tournamatch' ),
				'drop_team_member'  => esc_html__( 'Drop Team Member', 'tournamatch' ),
				'drop_confirm'      => esc_html__( 'Are you sure you want to remove \'{0}\' from the team?', 'tournamatch' ),
				'failure'           => esc_html__( 'Error', 'tournamatch' ),
				'confirm_new_owner' => esc_html__( 'Are you sure you want to promote another player to team owner?', 'tournamatch' ),
			),
			'team_id'         => $team_id,
			'uses_draws'      => ( '1' === trn_get_option( 'uses_draws' ) ),
			'ranks'           => $ranks,
			'can_edit_roster' => $can_edit_roster,
			'flag_directory'  => plugins_url( 'tournamatch' ) . '/dist/images/flags/',
		);

		wp_register_script( 'trn-team-roster-table', plugins_url( '../../dist/js/team-roster-table.js', __FILE__ ), array( 'tournamatch', 'jquery', 'datatables' ), '4.3.5', true );
		wp_localize_script( 'trn-team-roster-table', 'trn_team_roster_table_options', $options );
		wp_enqueue_script( 'trn-team-roster-table' );

		return $html;
	}
}

new Table_Shortcodes();
