<?php
/**
 * Defines the business rule that determines if a competitor can create a direct challenge.
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
 * Defines the business rule that determines if a competitor can create a direct challenge.
 *
 * @since      3.20.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Direct_Challenge_Requires_Enabled implements Business_Rule {

	/**
	 * Ladder id to check.
	 *
	 * @var integer $ladder_id
	 *
	 * @since 3.20.0
	 */
	private $ladder_id;

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
	 * @param int $challengee_id Challengee id to check.
	 *
	 * @since 3.20.0
	 */
	public function __construct( $ladder_id, $challengee_id ) {
		$this->ladder_id     = $ladder_id;
		$this->challengee_id = $challengee_id;
	}

	/**
	 * Evaluates whether the user is attempting to create a direct challenge.
	 *
	 * @since 3.20.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		if ( 0 === $this->challengee_id ) {
			$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $this->ladder_id ) );

			return ( 'enabled' === $ladder->direct_challenges );
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
		return esc_html__( 'Direct challenges are not enabled for this ladder.', 'tournamatch' );
	}
}
