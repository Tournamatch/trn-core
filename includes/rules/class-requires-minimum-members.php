<?php
/**
 * Defines the business rule that determines if a team has the minimum number of competitors.
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
 * Defines the business rule that determines if a team is locked.
 *
 * @since      3.28.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Requires_Minimum_Members implements Business_Rule {

	/**
	 * Team id to check.
	 *
	 * @var integer $team_id
	 *
	 * @since 3.28.0
	 */
	private $team_id;

	/**
	 * Competition id to check.
	 *
	 * @var integer $competition_id
	 *
	 * @since 3.28.0
	 */
	private $competition_id;

	/**
	 * Competition type to check.
	 *
	 * @var string $competition_type
	 *
	 * @since 3.28.0
	 */
	private $competition_type;

	/**
	 * The minimum number of members required for the competition.
	 *
	 * @var integer $competition_id
	 *
	 * @since 3.28.0
	 */
	private $minimum_required;

	/**
	 * Initializes this business rule.
	 *
	 * @param int    $team_id Team id to check.
	 * @param int    $competition_id Competition id to check.
	 * @param string $competition_type Competition type to check.
	 *
	 * @since 3.28.0
	 */
	public function __construct( $team_id, $competition_id, $competition_type ) {
		$this->team_id          = $team_id;
		$this->competition_id   = $competition_id;
		$this->competition_type = $competition_type;
	}

	/**
	 * Evaluates whether the team roster has the minimum number of members.
	 *
	 * @since 3.28.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		if ( 'ladder' === $this->competition_type ) {
			$this->minimum_required = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `l`.`team_size` FROM `{$wpdb->prefix}trn_ladders` AS `l` WHERE `l`.`ladder_id` = %d", $this->competition_id ) );
		} else {
			$this->minimum_required = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `t`.`team_size` FROM `{$wpdb->prefix}trn_tournaments` AS `t` WHERE `t`.`tournament_id` = %d", $this->competition_id ) );
		}

		$member_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `t`.`members` FROM `{$wpdb->prefix}trn_teams` AS `t` WHERE `t`.`team_id` = %d", $this->team_id ) );

		return ( $this->minimum_required <= $member_count );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.28.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		if ( 'ladder' === $this->competition_type ) {
			/* translators: An integer. */
			return sprintf( esc_html__( 'This ladder requires %d team members to join.', 'tournamatch' ), $this->minimum_required );
		} else {
			/* translators: An integer. */
			return sprintf( esc_html__( 'This tournament requires %d team members to register.', 'tournamatch' ), $this->minimum_required );
		}
	}
}
