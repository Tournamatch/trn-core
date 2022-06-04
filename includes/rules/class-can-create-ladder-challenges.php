<?php
/**
 * Defines the business rule that determines if a competitor can create ladder challenges.
 *
 * @link       https://www.tournamatch.com
 * @since      3.20.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule that determines if a competitor can create ladder challenges.
 *
 * @since      3.20.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Can_Create_Ladder_Challenges implements Business_Rule {

	/**
	 * Ladder id to check.
	 *
	 * @var integer $ladder_id
	 *
	 * @since 3.20.0
	 */
	private $ladder_id;

	/**
	 * User id to check.
	 *
	 * @var integer $user_id
	 *
	 * @since 3.20.0
	 */
	private $user_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $ladder_id Ladder id to check.
	 * @param int $user_id User id to check.
	 *
	 * @since 3.20.0
	 */
	public function __construct( $ladder_id, $user_id ) {
		$this->ladder_id = $ladder_id;
		$this->user_id   = $user_id;
	}

	/**
	 * Evaluates whether the user can create challenges for the ladder.
	 *
	 * @since 3.20.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $this->ladder_id ) );

		if ( 'players' === $ladder->competitor_type ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` WHERE `le`.`ladder_id` = %d AND `le`.`competitor_id` = %d", $this->ladder_id, $this->user_id ) );

			return ( '1' === $count );
		} else {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `le`.`competitor_id` = `tm`.`team_id` WHERE `le`.`ladder_id` = %d AND `tm`.`team_rank_id` = %d AND `tm`.`user_id` = %d", $this->ladder_id, 1, $this->user_id ) );

			return ( 1 <= intval( $count ) );
		}
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.20.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'This user cannot create challenges for this ladder.', 'tournamatch' );
	}
}
