<?php
/**
 * Defines the business rule that determines if a team has the max number of members.
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
 * Defines the business rule that determines if a team has the max number of members.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Team_Not_Maxed implements Business_Rule {

	/**
	 * Team id to check.
	 *
	 * @var integer $team_id
	 *
	 * @since 3.8.0
	 */
	private $team_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $team_id Team id to check.
	 *
	 * @since 3.8.0
	 */
	public function __construct( $team_id ) {
		$this->team_id = $team_id;
	}

	/**
	 * Evaluates whether the team has the max number of members.
	 *
	 * @since 3.8.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` AS le LEFT JOIN `{$wpdb->prefix}trn_ladders` AS l ON le.ladder_id = l.ladder_id LEFT JOIN `{$wpdb->prefix}trn_teams` AS t ON t.team_id = le.competitor_id WHERE t.members >= l.team_size AND le.competitor_type = 'teams' AND t.team_id = %d", $this->team_id ) );

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
		return __( 'This team is participating on a ladder with a max member count. You may not join until the team drops a current member.', 'tournamatch' );
	}
}
