<?php
/**
 * Defines the business rule requiring a team name to be unique.
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
 * Defines the business rule requiring a team name to be unique.
 *
 * @since 3.8.0
 * @since 3.16.0 Modified to accept a team id parameter for renaming.
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Unique_Team_Name implements Business_Rule {

	/**
	 * Team name to check.
	 *
	 * @var string $team_name
	 *
	 * @since 3.8.0
	 */
	private $team_name;

	/**
	 * Team id to check.
	 *
	 * @var string $team_id
	 *
	 * @since 3.16.0
	 */
	private $team_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param string  $team_name Team name to check.
	 * @param integer $team_id   Team id to check.
	 *
	 * @since 3.8.0
	 */
	public function __construct( $team_name, $team_id = 0 ) {
		$this->team_name = $team_name;
		$this->team_id   = $team_id;
	}

	/**
	 * Evaluates whether the team name is unique.
	 *
	 * @since 3.8.0
	 * @since 3.16.0 Also considers team id for team renaming.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		if ( 0 === $this->team_id ) {
			$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}trn_teams WHERE name = %s", $this->team_name ) );
		} else {
			$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}trn_teams WHERE name = %s AND team_id <> %d", $this->team_name, $this->team_id ) );
		}

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
		return __( 'Team name already exists.', 'tournamatch' );
	}
}
