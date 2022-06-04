<?php
/**
 * Defines the business rule requiring a team owner to promote another member before leaving.
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
 * Defines the business rule requiring a team owner to promote another member before leaving.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Must_Promote_Before_Leaving implements Business_Rule {

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
	 * Evaluates whether a team member leaving is a team owner and the team contains more than one member.
	 *
	 * @since 3.8.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$members = $wpdb->get_var( $wpdb->prepare( "SELECT members FROM {$wpdb->prefix}trn_teams WHERE team_id = %d", $this->team_id ) );
		$members = intval( $members );
		$rank    = $wpdb->get_var( $wpdb->prepare( "SELECT `team_rank_id` FROM {$wpdb->prefix}trn_teams_members WHERE team_id = %d AND user_id = %d", $this->team_id, $this->user_id ) );

		if ( ( '1' === $rank ) && ( 1 < $members ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.8.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return __( 'Please update the rank of another member to Leader before leaving.', 'tournamatch' );
	}
}
