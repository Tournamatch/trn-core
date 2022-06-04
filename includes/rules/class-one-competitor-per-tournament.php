<?php
/**
 * Defines the business rule limiting one competitor per tournament.
 *
 * @link       https://www.tournamatch.com
 * @since      3.19.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule limiting one competitor per tournament.
 *
 * @since      3.19.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class One_Competitor_Per_Tournament implements Business_Rule {

	/**
	 * Competitor id to check.
	 *
	 * @var integer $competitor_id
	 *
	 * @since 3.19.0
	 */
	private $competitor_id;

	/**
	 * Competition id to check.
	 *
	 * @var integer $competition_id
	 *
	 * @since 3.19.0
	 */
	private $competition_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $competition_id Competition id to check.
	 * @param int $competitor_id Competitor id to check.
	 *
	 * @since 3.19.0
	 */
	public function __construct( $competition_id, $competitor_id ) {
		$this->competition_id = $competition_id;
		$this->competitor_id  = $competitor_id;
	}

	/**
	 * Evaluates whether the given competitor is already registered for the given tournament.
	 *
	 * @since 3.19.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_id` = %d AND `competitor_id` = %d", $this->competition_id, $this->competitor_id ) );

		return ( '0' === $row_count );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.19.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'Only one competitor per tournament is allowed.', 'tournamatch' );
	}
}
