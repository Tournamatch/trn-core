<?php
/**
 * Defines the business rule limiting one team per user.
 *
 * @link       https://www.tournamatch.com
 * @since      3.10.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule limiting one team per user.
 *
 * @since      3.10.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class One_Team_Per_User implements Business_Rule {

	/**
	 * User id to check.
	 *
	 * @var integer $user_id
	 *
	 * @since 3.10.0
	 */
	private $user_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $user_id User id to check.
	 *
	 * @since 3.8.0
	 */
	public function __construct( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * Evaluates whether the given user is already a member of a team.
	 *
	 * @since 3.10.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}trn_teams_members WHERE user_id = %d", $this->user_id ) );

		return ( '0' === $row_count );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.10.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return __( 'Users are limited to one team.', 'tournamatch' );
	}
}
