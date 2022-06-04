<?php
/**
 * Defines the business rule that determines if a team rank has the max number of members.
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
 * Defines the business rule that determines if a team rank has the max number of members.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Team_Rank_Maxed implements Business_Rule {

	/**
	 * Team id to check.
	 *
	 * @var integer $team_id
	 *
	 * @since 3.8.0
	 */
	private $team_id;

	/**
	 * Rank id to check.
	 *
	 * @var integer $team_rank_id
	 *
	 * @since 3.8.0
	 */
	private $team_rank_id;

	/**
	 * The max number of members for the given rank.
	 *
	 * @var integer $max
	 *
	 * @since 3.8.0
	 */
	private $max;

	/**
	 * The title of the given rank.
	 *
	 * @var string $rank_title
	 *
	 * @since 3.8.0
	 */
	private $rank_title;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $team_id Team id to check.
	 * @param int $rank_id Rank id to check.
	 *
	 * @since 3.8.0
	 */
	public function __construct( $team_id, $rank_id ) {
		$this->team_id      = $team_id;
		$this->team_rank_id = $rank_id;
	}

	/**
	 * Evaluates whether the team has the max number of members with a given team rank.
	 *
	 * @since 3.8.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$rank    = $wpdb->get_row( $wpdb->prepare( "SELECT `max`, `title`, `weight` FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $this->team_rank_id ) );
		$members = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `team_rank_id` = %d", $this->team_id, $this->team_rank_id ) );

		$this->rank_title = $rank->title;
		$this->max        = intval( $rank->max );
		$members          = intval( $members );

		// The lowest rank of any team has a max member count of to indicate unlimited members.
		return ( ( '1' === $rank->weight ) || ( 0 > $this->max ) || ( $members < $this->max ) );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.8.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		/* translators: The first value is a numerical number and the second value is the rank title. */
		return sprintf( __( 'The limit for that rank is %1$s and this team all ready has that many %2$s.', 'tournamatch' ), $this->max, $this->rank_title );
	}
}
