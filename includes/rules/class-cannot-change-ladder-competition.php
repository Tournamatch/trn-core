<?php
/**
 * Defines the business rule that prohibits changing a ladder's competition setting after participants have registered.
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
 * Defines the business rule that prohibits changing a ladder's competition setting after participants have registered.
 *
 * @since      3.19.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Cannot_Change_Ladder_Competition implements Business_Rule {

	/**
	 * Ladder id to check.
	 *
	 * @var integer $ladder_id
	 *
	 * @since 3.19.0
	 */
	private $ladder_id;

	/**
	 * New value given on ladder edit.
	 *
	 * @var integer $new_value
	 *
	 * @since 3.19.0
	 */
	private $new_value;

	/**
	 * Initializes this business rule.
	 *
	 * @param int $ladder_id Ladder id to check.
	 * @param int $new_value New value to evaluate.
	 *
	 * @since 3.19.0
	 */
	public function __construct( $ladder_id, $new_value ) {
		$this->ladder_id = $ladder_id;
		$this->new_value = $new_value;
	}

	/**
	 * Evaluates whether the ladder competition may change.
	 *
	 * @since 3.19.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$participants = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `ladder_id` = %d", $this->ladder_id ) );

		if ( 0 === $participants ) {
			return true;
		} else {
			$old_value = $wpdb->get_var( $wpdb->prepare( "SELECT `competitor_type` FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $this->ladder_id ) );

			return ( $this->new_value === $old_value );
		}
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.19.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'You cannot change the ladder competition after participants have registered.', 'tournamatch' );
	}
}
