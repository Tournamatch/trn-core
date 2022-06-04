<?php
/**
 * Defines the business rule that prohibits removing the rank of the team default rank.
 *
 * @link       https://www.tournamatch.com
 * @since      3.17.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule that prohibits removing the rank of the team default rank.
 *
 * @since      3.17.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Cannot_Remove_Default_Rank implements Business_Rule {

	/**
	 * Rank id to check.
	 *
	 * @var integer $team_rank_id
	 *
	 * @since 3.17.0
	 */
	private $team_rank_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $rank_id Rank id to check.
	 *
	 * @since 3.17.0
	 */
	public function __construct( $rank_id ) {
		$this->team_rank_id = $rank_id;
	}

	/**
	 * Evaluates whether the team rank may be moved.
	 *
	 * @since 3.17.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$default_rank_id = $wpdb->get_var( "SELECT `team_rank_id` FROM `{$wpdb->prefix}trn_teams_ranks` ORDER BY weight DESC LIMIT 1" );

		return ( $default_rank_id !== $this->team_rank_id );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.17.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'You may not remove the rank of the team default rank.', 'tournamatch' );
	}
}
