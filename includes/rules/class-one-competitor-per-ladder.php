<?php
/**
 * Defines the business rule limiting one competitor per ladder.
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
 * Defines the business rule limiting one competitor per ladder.
 *
 * @since      3.28.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class One_Competitor_Per_Ladder implements Business_Rule {

	/**
	 * Competitor id to check.
	 *
	 * @var integer $competitor_id
	 *
	 * @since 3.28.0
	 */
	private $competitor_id;

	/**
	 * Ladder id to check.
	 *
	 * @var integer $ladder_id
	 *
	 * @since 3.28.0
	 */
	private $ladder_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $ladder_id Ladder id to check.
	 * @param int $competitor_id Competitor id to check.
	 *
	 * @since 3.28.0
	 */
	public function __construct( $ladder_id, $competitor_id ) {
		$this->ladder_id     = $ladder_id;
		$this->competitor_id = $competitor_id;
	}

	/**
	 * Evaluates whether the given competitor is already participating on a ladder.
	 *
	 * @since 3.28.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $this->ladder_id ) );

		if ( 'players' === $ladder->competitor_type ) {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `competitor_id` = %d AND `ladder_id` = %d", $this->competitor_id, $this->ladder_id ) );
		} else {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`team_id` = `le`.`competitor_id` WHERE `le`.`ladder_id` = %d AND `tm`.`user_id` = %d", $this->ladder_id, $this->competitor_id ) );
		}

		return ( '0' === $exists );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.28.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'Only one competitor per ladder is allowed.', 'tournamatch' );
	}
}
