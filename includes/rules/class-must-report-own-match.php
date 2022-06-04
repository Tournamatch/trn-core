<?php
/**
 * Defines the business rule requiring a player or team to report their own match result only.
 *
 * @link       https://www.tournamatch.com
 * @since      3.28.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule requiring a player or team to report their own match result only.
 *
 * @since      3.28.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Must_Report_Own_Match implements Business_Rule {

	/**
	 * Match to check.
	 *
	 * @var Object $match
	 *
	 * @since 3.28.0
	 */
	private $match;

	/**
	 * Web Request
	 *
	 * @var \WP_REST_Request $request Full data about the request.
	 *
	 * @since 3.28.0
	 */
	private $request;

	/**
	 * Initializes this business rule.
	 *
	 * @param Object           $match Match to check.
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @since 3.28.0
	 */
	public function __construct( $match, $request ) {
		$this->match   = $match;
		$this->request = $request;
	}

	/**
	 * Evaluates whether a competitor is reporting their own match.
	 *
	 * @since 3.28.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$side = isset( $this->request['one_result'] ) ? 'one_competitor_id' : 'two_competitor_id';

		if ( 'players' === $this->match->one_competitor_type ) {
			return get_current_user_id() === intval( $this->match->$side );
		} else {
			$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d", get_current_user_id() ) );
			$teams = array_column( $teams, 'team_id' );

			return in_array( $this->match->$side, $teams, true );
		}
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.28.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'You may not report a result for another competitor.', 'tournamatch' );
	}
}
