<?php
/**
 * Defines the business rule requiring a player name to be unique.
 *
 * @link       https://www.tournamatch.com
 * @since      3.16.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business rule requiring a player name to be unique.
 *
 * @since      3.16.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Unique_Player_Name implements Business_Rule {

	/**
	 * Player name to check.
	 *
	 * @var string $player_name
	 *
	 * @since 3.16.0
	 */
	private $player_name;

	/**
	 * User id of player to check.
	 *
	 * @var integer
	 *
	 * @since 3.16.0
	 */
	private $user_id;

	/**
	 * Initializes this business rule.
	 *
	 * @param string  $player_name Player name to check.
	 * @param integer $user_id User id of player to check.
	 *
	 * @since 3.16.0
	 */
	public function __construct( $player_name, $user_id ) {
		$this->player_name = $player_name;
		$this->user_id     = $user_id;
	}

	/**
	 * Evaluates whether the player name is unique.
	 *
	 * @since 3.16.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		global $wpdb;

		$row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_players_profiles` WHERE display_name = %s AND user_id <> %d", $this->player_name, $this->user_id ) );

		return ( '0' === $row_count );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.16.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'That name is already chosen. Please choose a different display name.', 'tournamatch' );
	}
}
