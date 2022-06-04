<?php
/**
 * Defines the business rule that determines if a competitor id is participating on a ladder.
 *
 * @link       https://www.tournamatch.com
 * @since      3.15.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines  the business rule that determines if a competitor id is participating on a ladder.
 *
 * @since      3.15.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Must_Participate_On_Ladder implements Business_Rule {

	/**
	 * Ladder id to check.
	 *
	 * @var integer $ladder_id
	 *
	 * @since 3.15.0
	 */
	private $ladder_id;

	/**
	 * Competitor id to check.
	 *
	 * @var integer $competitor_id
	 *
	 * @since 3.15.0
	 */
	private $competitor_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $ladder_id Ladder id to check.
	 * @param int $competitor_id Competitor id to check.
	 *
	 * @since 3.15.0
	 */
	public function __construct( $ladder_id, $competitor_id ) {
		$this->ladder_id     = $ladder_id;
		$this->competitor_id = $competitor_id;
	}

	/**
	 * Evaluates whether the competitor is participating on a ladder.
	 *
	 * @since 3.15.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `ladder_id` = %d AND `competitor_id` = %d", $this->ladder_id, $this->competitor_id ) );

		return ( '1' === $row_count );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.15.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return __( 'This competitor is not participating on the ladder.', 'tournamatch' );
	}
}
