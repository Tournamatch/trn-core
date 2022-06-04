<?php
/**
 * Defines the business rule requiring a team name not be empty.
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
 * Defines the business rule requiring a team name not be empty.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Team_Name_Required implements Business_Rule {

	/**
	 * Team name to check.
	 *
	 * @var string $team_name
	 *
	 * @since 3.8.0
	 */
	private $team_name;

	/**
	 * Initializes this business rule.
	 *
	 * @param string $team_name Team name to check.
	 *
	 * @since 3.8.0
	 */
	public function __construct( $team_name ) {
		$this->team_name = $team_name;
	}

	/**
	 * Evaluates whether the team name is empty.
	 *
	 * @since 3.8.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		return ( 0 < strlen( $this->team_name ) );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.8.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return __( 'Team name is required.', 'tournamatch' );
	}
}
