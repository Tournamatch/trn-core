<?php
/**
 * Defines the business rule that requires a team to have an exact number of members.
 *
 * @link       https://www.tournamatch.com
 * @since      4.5.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule that requires a team to have an exact number of members.
 *
 * @since      4.5.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Exact_Team_Size_Required implements Business_Rule {

	/**
	 * Team id to check.
	 *
	 * @var integer $team_id
	 *
	 * @since 4.5.0
	 */
	private $team_id;

	/**
	 * The exact team size required.
	 *
	 * @var integer $team_size
	 *
	 * @since 4.5.0
	 */
	private $team_size;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $team_id Team id to check.
	 * @param int $team_size Exact team size required.
	 *
	 * @since 4.5.0
	 */
	public function __construct( $team_id, $team_size ) {
		$this->team_id   = $team_id;
		$this->team_size = $team_size;
	}

	/**
	 * Evaluates whether a team to has the exact number of members required.
	 *
	 * @since 4.5.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$members = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d", $this->team_id ) );

		return intval( $members ) === intval( $this->team_size );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 4.5.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		/* translators: An integer number of team members. */
		return sprintf( __( 'This competition requires exactly %1$s team members.', 'tournamatch' ), $this->team_size );
	}
}
