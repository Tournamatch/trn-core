<?php
/**
 * Defines the business rule permitting what matches may be disputed.
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
 * Defines the business rule permitting what matches may be disputed.
 *
 * @since      3.19.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Can_Dispute_Match implements Business_Rule {

	/**
	 * Match id to check.
	 *
	 * @var integer $match_id
	 *
	 * @since 3.19.0
	 */
	private $match_id;


	/**
	 * Initializes this business rule.
	 *
	 * @param int $match_id Match id to check.
	 *
	 * @since 3.19.0
	 */
	public function __construct( $match_id ) {
		$this->match_id = $match_id;
	}

	/**
	 * Evaluates whether the given user may dispute a match result.
	 *
	 * @since 3.19.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$match_status = $wpdb->get_var( $wpdb->prepare( "SELECT `match_status` FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $this->match_id ) );

		return ( 'reported' === $match_status );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.19.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'Only reported matches not yet confirmed may be disputed.', 'tournamatch' );
	}
}
