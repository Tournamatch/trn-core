<?php
/**
 * Defines Tournamatch shortcodes.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Shortcodes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines Tournamatch shortcodes.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Shortcodes {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.8.0
	 * @since 3.13.0 Added my team invitations list shortcode.
	 * @since 3.15.0 Added my team requests list shortcode.
	 */
	public function __construct() {
		add_shortcode( 'trn-team-requests-list', array( $this, 'team_requests_list' ) );
		add_shortcode( 'trn-team-invitations-list', array( $this, 'team_invitations_list' ) );
		add_shortcode( 'trn-email-team-invitation-form', array( $this, 'email_team_invitation_form' ) );
		add_shortcode( 'trn-ladder-registration-button', array( $this, 'ladder_registration_button' ) );
		add_shortcode( 'trn-tournament-registration-button', array( $this, 'tournament_registration_button' ) );
		add_shortcode( 'trn-invite-player-to-team', array( $this, 'invite_player' ) );
		add_shortcode( 'trn-my-team-invitations-list', array( $this, 'my_team_invitations_list' ) );
		add_shortcode( 'trn-my-team-requests-list', array( $this, 'my_team_requests_list' ) );
		add_shortcode( 'trn-upcoming-tournaments', array( $this, 'upcoming_tournaments' ) );
		add_shortcode( 'trn-dispute-match-button', array( $this, 'dispute_match_button' ) );
		add_shortcode( 'trn-career-record', array( $this, 'career_record' ) );
		add_shortcode( 'trn-brackets', array( $this, 'brackets' ) );
	}

	/**
	 * Shortcode to create a section that displays your requests to join other teams.
	 *
	 * @since 3.15.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function my_team_requests_list( $atts = [], $content = null, $tag = '' ) {

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'user_id'    => get_current_user_id(),
			'language'   => array(
				'zero_requests' => __( 'No requests to display.', 'tournamatch' ),
				'error'         => __( 'An error occurred.', 'tournamatch' ),
				'failure'       => __( 'Error', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_my_team_requests_list', plugins_url( '../../dist/js/my-team-requests-list.js', __FILE__ ), array( 'tournamatch' ), '3.15.0', true );
		wp_localize_script( 'trn_my_team_requests_list', 'trn_my_team_requests_list_options', $options );
		wp_enqueue_script( 'trn_my_team_requests_list' );

		$html  = '<section id="trn-my-team-requests-section">';
		$html .= '<div id="trn-my-team-requests-response"></div>';
		$html .= '<p class="trn-text-center">' . esc_html__( 'Loading team requests...', 'tournamatch' ) . '</p>';
		$html .= '</section>';

		return $html;
	}

	/**
	 * Shortcode to create a section that displays teams that have requested your membership.
	 *
	 * @since 3.13.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function my_team_invitations_list( $atts = [], $content = null, $tag = '' ) {

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'user_id'    => get_current_user_id(),
			'language'   => array(
				'zero_invitations' => __( 'No invitations to display.', 'tournamatch' ),
				'error'            => __( 'An error occurred.', 'tournamatch' ),
				'failure'          => __( 'Error', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_my_team_invitations_list', plugins_url( '../../dist/js/my-team-invitations-list.js', __FILE__ ), array( 'tournamatch' ), '3.13.0', true );
		wp_localize_script( 'trn_my_team_invitations_list', 'trn_my_team_invitations_list_options', $options );
		wp_enqueue_script( 'trn_my_team_invitations_list' );

		$html  = '<section id="trn-my-team-invitations-section">';
		$html .= '<div id="trn-my-team-invitations-response"></div>';
		$html .= '<p class="trn-text-center">' . esc_html__( 'Loading team invitations...', 'tournamatch' ) . '</p>';
		$html .= '</section>';

		return $html;
	}

	/**
	 * Shortcode to create a section that displays team join requests.
	 *
	 * @since 3.8.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function team_requests_list( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['team_id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'team_id'    => intval( $atts['team_id'] ),
			'language'   => array(
				'zero_requests' => __( 'No requests to display.', 'tournamatch' ),
				'error'         => __( 'An error occurred.', 'tournamatch' ),
				'failure'       => __( 'Error', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_team_requests_list', plugins_url( '../../dist/js/team-requests-list.js', __FILE__ ), array( 'tournamatch' ), '3.8.0', true );
		wp_localize_script( 'trn_team_requests_list', 'trn_team_requests_list_options', $options );
		wp_enqueue_script( 'trn_team_requests_list' );

		$html  = '<section id="trn-team-requests-section">';
		$html .= '<div id="trn-team-requests-response"></div>';
		$html .= '<h4 class="trn-text-center" id="trn-team-requests-section-header">' . __( 'Requests', 'tournamatch' ) . '</h4>';
		$html .= '<p class="trn-text-center">' . __( 'Loading team requests...', 'tournamatch' ) . '</p>';
		$html .= '</section>';

		return $html;
	}

	/**
	 * Shortcode to create a section that displays team join invitations.
	 *
	 * @since 3.8.0
	 * @since 3.10.0 Added player profile link to localized options.
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function team_invitations_list( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['team_id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'team_id'    => intval( $atts['team_id'] ),
			'language'   => array(
				'zero_invitations' => __( 'No invitations to display.', 'tournamatch' ),
				'error'            => __( 'An error occurred.', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_team_invitations_list', plugins_url( '../../dist/js/team-invitations-list.js', __FILE__ ), array( 'tournamatch' ), '3.8.0', true );
		wp_localize_script( 'trn_team_invitations_list', 'trn_team_invitations_list_options', $options );
		wp_enqueue_script( 'trn_team_invitations_list' );

		$html  = '<section id="trn-team-invitations-section">';
		$html .= '<h4 class="trn-text-center" id="trn-team-invitations-section-header">' . __( 'Invitations', 'tournamatch' ) . '</h4>';
		$html .= '<p class="trn-text-center">' . __( 'Loading team invitations...', 'tournamatch' ) . '</p>';
		$html .= '</section>';

		return $html;
	}

	/**
	 * Shortcode to create a section that displays a form to email an invitations to join a team.
	 *
	 * @since 3.8.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function email_team_invitation_form( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['team_id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'team_id'    => intval( $atts['team_id'] ),
			'language'   => array(
				'error'          => __( 'An error occurred.', 'tournamatch' ),
				'email_required' => __( 'A valid email address is required.', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_email_team_invitation_form', plugins_url( '../../dist/js/email-team-invitation-form.js', __FILE__ ), array( 'tournamatch' ), '3.8.0', true );
		wp_localize_script( 'trn_email_team_invitation_form', 'trn_email_team_invitation_form_options', $options );
		wp_enqueue_script( 'trn_email_team_invitation_form' );

		$html  = '<section id="trn-email-team-invitation-section">';
		$html .= '<h4 class="trn-text-center" id="trn-email-team-invitation-section-header">' . __( 'Invite', 'tournamatch' ) . '</h4>';
		$html .= '<form class="form-horizontal" id="trn-email-team-invitation-form" novalidate>';
		$html .= '	<div class="trn-form-group">';
		$html .= '		<label class="control-label trn-col-md-3" for="trn-email-invite-address">' . __( 'Email', 'tournamatch' ) . '</label>';
		$html .= '		<div class="trn-col-md-9">';
		$html .= '			<input type="email" class="trn-form-control" name="trn-email-invite-address" id="trn-email-invite-address" required>';
		$html .= '			<div class="trn-invalid-feedback" id="trn-email-invite-address-error">' . __( 'A valid email address is required.', 'tournamatch' ) . '</div>';
		$html .= '		</div>';
		$html .= '	</div>';
		$html .= '	<div class="trn-form-group">';
		$html .= '		<div class="trn-col-md-offset-3 trn-col-md-9">';
		$html .= '			<button class="trn-button trn-button-secondary trn-button-sm" type="submit">' . __( 'Send Invitation', 'tournamatch' ) . '</button>';
		$html .= '		</div>';
		$html .= '	</div>';
		$html .= '</form>';
		$html .= '</section>';

		return $html;
	}

	/**
	 * Shortcode to create a ladder registration button.
	 *
	 * @since 3.10.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function ladder_registration_button( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		return '<a class="trn-button" href="' . trn_route( 'ladders.single.join', array( 'id' => $atts['id'] ) ) . '">' . esc_html__( 'Join', 'tournamatch' ) . '</a>';
	}

	/**
	 * Shortcode to create a tournament registration button.
	 *
	 * @since 3.10.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function tournament_registration_button( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		return '<a class="trn-button" href="' . trn_route( 'tournaments.single.register', array( 'id' => $atts['id'] ) ) . '">' . esc_html__( 'Register', 'tournamatch' ) . '</a>';
	}

	/**
	 * Shortcode to create a list of upcoming tournaments.
	 *
	 * @since 3.13.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function upcoming_tournaments( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['game_id'] ) ) {
			return '';
		}

		$atts['paginate'] = empty( $atts['paginate'] ) ? 3 : $atts['paginate'];

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'game_id'    => intval( $atts['game_id'] ),
			'paginate'   => intval( $atts['paginate'] ),
			'language'   => array(
				'success'              => __( 'Success', 'tournamatch' ),
				'failure'              => __( 'Error', 'tournamatch' ),
				'error'                => __( 'An error occurred.', 'tournamatch' ),
				'zero_tournaments'     => __( 'Zero tournaments to display.', 'tournamatch' ),
				'more_info'            => __( 'Info', 'tournamatch' ),
				'register'             => __( 'Register', 'tournamatch' ),
				'view_tournament_info' => __( 'View tournament information.', 'tournamatch' ),
				'current_seeding'      => __( 'Current Seeding', 'tournamatch' ),
				'one_loss'             => __( 'One loss', 'tournamatch' ),
				'double_elimination'   => __( 'Double elimination', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_upcoming_tournament_list_shortcode', plugins_url( '../../dist/js/upcoming-tournament-list.js', __FILE__ ), array( 'tournamatch' ), '3.13.0', true );
		wp_localize_script( 'trn_upcoming_tournament_list_shortcode', 'trn_upcoming_tournament_list_options', $options );
		wp_enqueue_script( 'trn_upcoming_tournament_list_shortcode' );

		$html  = '<div id="trn-tournament-list-shortcode">';
		$html .= '<p>' . esc_html__( 'Loading upcoming tournaments...', 'tournamatch' ) . '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Shortcode to create an invitation to team drop down.
	 *
	 * @since 3.13.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function invite_player( $atts = [], $content = null, $tag = '' ) {
		global $wpdb;

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['user_id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'user_id'    => intval( $atts['user_id'] ),
			'language'   => array(
				'success' => __( 'Success', 'tournamatch' ),
				'failure' => __( 'Error', 'tournamatch' ),
				'error'   => __( 'An error occurred.', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_invite_player_to_team', plugins_url( '../../dist/js/invite-player-to-team.js', __FILE__ ), array( 'tournamatch' ), '3.10.0', true );
		wp_localize_script( 'trn_invite_player_to_team', 'trn_invite_player_to_team_options', $options );
		wp_enqueue_script( 'trn_invite_player_to_team' );

		$user_teams = $wpdb->get_results( $wpdb->prepare( "SELECT t.team_id AS id, t.name AS name FROM {$wpdb->prefix}trn_teams AS t LEFT JOIN {$wpdb->prefix}trn_teams_members AS tm ON t.team_id = tm.team_id WHERE tm.`team_rank_id` = 1 AND tm.user_id = %d", get_current_user_id() ) );

		$html  = '<div class="trn-button-group">';
		$html .= '  <button class="trn-button trn-button-secondary trn-button-sm trn-dropdown-toggle" type="button" aria-haspopup="true" aria-expanded="false" id="trn-invite-dropdown">' . __( 'Invite to...', 'tournamatch' ) . '</button>';
		$html .= '  <div class="trn-dropdown-menu" aria-labelledby="trn-invite-dropdown">';

		if ( 0 === count( $user_teams ) ) {
			$html .= '    <button class="trn-dropdown-item" type="button"><em>' . __( 'No team available.', 'tournamatch' ) . '</em></button>';
		} else {
			foreach ( $user_teams as $team ) {
				$html .= '    <button class="trn-dropdown-item trn-invite-player-to-team" type="button" data-team-id="' . intval( $team->id ) . '">' . esc_html( $team->name ) . '</button>';
			}
		}
		$html .= '  </div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Shortcode to create a dispute match button.
	 *
	 * @since 3.19.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function dispute_match_button( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		);

		wp_register_script( 'trn_dispute_match_button', plugins_url( '../../dist/js/dispute-match-button.js', __FILE__ ), array( 'tournamatch' ), '3.19.0', true );
		wp_localize_script( 'trn_dispute_match_button', 'trn_dispute_match_button_options', $options );
		wp_enqueue_script( 'trn_dispute_match_button' );

		return '<a class="trn-button trn-button-sm trn-button-danger trn-dispute-match-button" href="#" data-match-id="' . intval( $atts['id'] ) . '">' . esc_html__( 'Dispute', 'tournamatch' ) . '</a>';
	}

	/**
	 * Shortcode to create a dispute match button.
	 *
	 * @since 3.25.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function career_record( $atts = [], $content = null, $tag = '' ) {
		global $wpdb;

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

		if ( 'players' === $competitor_type ) {
			$career_record = $wpdb->get_row( $wpdb->prepare( "SELECT `wins` AS `wins`, `losses` AS `losses`, `draws` AS `draws` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $competitor_id ), ARRAY_A );
		} else {
			$career_record = $wpdb->get_row( $wpdb->prepare( "SELECT `wins` AS `wins`, `losses` AS `losses`, `draws` AS `draws` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $competitor_id ), ARRAY_A );
		}

		$uses_draws = ( '1' === trn_get_option( 'uses_draws' ) );

		if ( ! $uses_draws ) {
			unset( $career_record['draws'] );
			/* translators: An integer dash another integer representing wins and losses. */
			$career_record_format = esc_html__( '%1$d - %2$d', 'tournamatch' );

			/* translators: An integer dash another integer representing wins and losses. */
			$singles_record_format = esc_html__( '(%1$d - %2$d in singles)', 'tournamatch' );
		} else {
			/* translators: An integer dash another integer dash another integer representing wins and losses and draws. */
			$career_record_format = esc_html__( '%1$d - %2$d - %3$d', 'tournamatch' );

			/* translators: An integer dash another integer dash another integer representing wins and losses and draws. */
			$singles_record_format = esc_html__( '(%1$d - %2$d - %3$d in singles)', 'tournamatch' );
		}

		if ( 'players' === $competitor_type ) {
			$singles_record           = $career_record;
			$team_record              = $wpdb->get_row( $wpdb->prepare( "SELECT SUM(`wins`) AS `wins`, SUM(`losses`) AS `losses`, SUM(`draws`) AS `draws` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d", $competitor_id ), ARRAY_A );
			$career_record['wins']   += $team_record['wins'];
			$career_record['losses'] += $team_record['losses'];
			if ( $uses_draws ) {
				$career_record['draws'] += $team_record['draws'];
			}

			$html  = '<span id="career_record">' . vsprintf( $career_record_format, $career_record ) . '</span>';
			$html .= ' <span id="singles-record">' . vsprintf( $singles_record_format, $singles_record ) . '</span>';
		} else {
			$html = '<span id="career_record">' . vsprintf( $career_record_format, $career_record ) . '</span>';
		}

		return $html;
	}

	/**
	 * Shortcode to create the tournament brackets output.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function brackets( $attributes = [], $content = null, $tag = '' ) {
		global $wpdb;

		$attributes = array_change_key_case( (array) $attributes, CASE_LOWER );

		if ( empty( $attributes['tournament_id'] ) ) {
			return '';
		}

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $attributes['tournament_id'] ) );

		if ( is_null( $tournament ) ) {
			return '';
		}

		if ( ! in_array( $tournament->status, array( 'in_progress', 'complete' ), true ) ) {
			return '<p class="trn-text-center">' . esc_html__( 'The tournament has not started.', 'tournamatch' ) . '</p>';
		}

		$round_language = array(
			0 => esc_html__( 'Round 1', 'tournamatch' ),
			1 => esc_html__( 'Round 2', 'tournamatch' ),
			2 => esc_html__( 'Round 3', 'tournamatch' ),
			3 => esc_html__( 'Round 4', 'tournamatch' ),
			4 => esc_html__( 'Round 5', 'tournamatch' ),
			5 => esc_html__( 'Quarter-Finals', 'tournamatch' ),
			6 => esc_html__( 'Semi-Finals', 'tournamatch' ),
			7 => esc_html__( 'Finals', 'tournamatch' ),
			8 => esc_html__( 'Winner', 'tournamatch' ),
		);

		$total_rounds = log( $tournament->bracket_size, 2 );

		if ( 7 >= $total_rounds ) {
			unset( $round_language[4] );
		}
		if ( 6 >= $total_rounds ) {
			unset( $round_language[3] );
		}
		if ( 5 >= $total_rounds ) {
			unset( $round_language[5] );
		}
		if ( 4 >= $total_rounds ) {
			unset( $round_language[2] );
		}
		if ( 3 >= $total_rounds ) {
			unset( $round_language[6] );
		}
		if ( 2 >= $total_rounds ) {
			unset( $round_language[1] );
		}

		$round_language = array_values( $round_language );

		$options = array(
			'rest_nonce'       => wp_create_nonce( 'wp_rest' ),
			'replace_nonce'    => wp_create_nonce( 'tournament-replace-competitor' ),
			'replace_url'      => trn_route(
				'tournaments.single.replace',
				array(
					'id'            => '{TOURNAMENT_ID}',
					'match_id'      => '{MATCH_ID}',
					'competitor_id' => '{COMPETITOR_ID}',
					'_wpnonce'      => '{NONCE}',
				)
			),
			'advance_nonce'    => wp_create_nonce( 'tournamatch-bulk-matches' ),
			'advance_url'      => trn_route(
				'admin.tournaments.advance-match',
				array(
					'id'        => '{ID}',
					'winner_id' => '{WINNER_ID}',
					'_wpnonce'  => '{NONCE}',
				)
			),
			'site_url'         => site_url(),
			'can_edit_matches' => current_user_can( 'manage_tournamatch' ),
			'language'         => array(
				'error'   => esc_html__( 'An error occurred.', 'tournamatch' ),
				'rounds'  => $round_language,
				'clear'   => esc_html__( 'Clear', 'tournamatch' ),
				'advance' => esc_html__( 'Advance {NAME}', 'tournamatch' ),
				'replace' => esc_html__( 'Replace {NAME}', 'tournamatch' ),
				'winner'  => esc_html__( 'Winner', 'tournamatch' ),
			),
			'undecided'        => trn_get_option( 'tournament_undecided_display' ),
		);

		wp_register_style( 'trn-tournament-brackets-style', plugins_url( '../../dist/css/brackets.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_style( 'trn-tournament-brackets-style' );

		wp_register_script( 'trn-tournament-brackets', plugins_url( '../../dist/js/brackets.js', __FILE__ ), array(), '1.0.0', true );
		wp_localize_script( 'trn-tournament-brackets', 'trn_brackets_options', $options );
		wp_enqueue_script( 'trn-tournament-brackets' );

		$html  = sprintf( '<div id="tournamatch-%d" class="trn-brackets" data-tournament-id="%d" data-tournament-size="%d">', $attributes['tournament_id'], $attributes['tournament_id'], $tournament->bracket_size );
		$html .= '<p class="trn-text-center">' . esc_html__( 'Loading brackets...', 'tournamatch' ) . '</p>';
		$html .= '</div>';

		return $html;
	}
}

new Shortcodes();
