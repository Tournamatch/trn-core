<?php
/**
 * Defines the business rule that determines if ladder challenges are enabled.
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
 * Defines the business rule that determines if ladder challenges are enabled.
 *
 * @since      3.20.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Ladder_Challenges_Enabled implements Business_Rule {

	/**
	 * Ladder id to check.
	 *
	 * @var integer $ladder_id
	 *
	 * @since 3.20.0
	 */
	private $ladder_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $ladder_id Ladder id to check.
	 *
	 * @since 3.20.0
	 */
	public function __construct( $ladder_id ) {
		$this->ladder_id = $ladder_id;
	}

	/**
	 * Evaluates whether challenges are enabled.
	 *
	 * @since 3.20.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $this->ladder_id ) );

		if ( 'disabled' === $ladder->direct_challenges ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.20.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'Challenges are not enabled for this ladder.', 'tournamatch' );
	}
}
