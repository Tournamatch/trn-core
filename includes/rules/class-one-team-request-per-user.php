<?php
/**
 * Defines the business rule limiting one join team request per user.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule limiting one join team request per user.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class One_Team_Request_Per_User implements Business_Rule {

	/**
	 * Team id to check.
	 *
	 * @var integer $team_id
	 *
	 * @since 3.8.0
	 */
	private $team_id;

	/**
	 * User id to check.
	 *
	 * @var integer $user_id
	 *
	 * @since 3.8.0
	 */
	private $user_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $team_id Team id to check.
	 * @param int $user_id User id to check.
	 *
	 * @since 3.8.0
	 */
	public function __construct( $team_id, $user_id ) {
		$this->team_id = $team_id;
		$this->user_id = $user_id;
	}

	/**
	 * Evaluates whether the given user has already requested to join the given team.
	 *
	 * @since 3.8.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}trn_teams_members_requests WHERE team_id = %d AND user_id = %d", $this->team_id, $this->user_id ) );

		return ( '0' === $row_count );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.8.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return __( 'You have already requested to join this team. The team manager must now accept your request.', 'tournamatch' );
	}
}
