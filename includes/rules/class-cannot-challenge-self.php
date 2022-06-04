<?php
/**
 * Defines the business rule that determines if a competitor is attempting to challenge self.
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
 * Defines the business rule that determines if a competitor is attempting to challenge self.
 *
 * @since      3.20.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Cannot_Challenge_Self implements Business_Rule {

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
	 * Challengee id to check.
	 *
	 * @var integer $challengee_id
	 *
	 * @since 3.20.0
	 */
	private $challengee_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $ladder_id Ladder id to check.
	 * @param int $user_id User id to check.
	 * @param int $challengee_id Challengee id to check.
	 *
	 * @since 3.20.0
	 */
	public function __construct( $ladder_id, $user_id, $challengee_id ) {
		$this->ladder_id     = $ladder_id;
		$this->user_id       = $user_id;
		$this->challengee_id = $challengee_id;
	}

	/**
	 * Evaluates whether the user is attempting to challenge self.
	 *
	 * @since 3.20.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $this->ladder_id ) );

		if ( 'players' === $ladder->competitor_type ) {
			$competitor_id = $this->user_id;
		} else {
			$competitor_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `le`.`competitor_id` FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `le`.`competitor_id` = `tm`.`team_id` WHERE `le`.`ladder_id` = %d AND `tm`.`team_rank_id` = %d AND `tm`.`user_id` = %d", $this->ladder_id, 1, $this->user_id ) );
		}

		return ( $this->challengee_id !== $competitor_id );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.20.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'User may not challenge self.', 'tournamatch' );
	}
}
