<?php
/**
 * Defines the business rule requiring a player new password must be matched.
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
 * Defines the business rule requiring a player new password must be matched.
 *
 * @since      3.16.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Password_Must_Match implements Business_Rule {

	/**
	 * New Password to check.
	 *
	 * @var string $new_password
	 *
	 * @since 3.16.0
	 */
	private $new_password;

	/**
	 * Confirm Password to check.
	 *
	 * @var string $confirm_password
	 *
	 * @since 3.16.0
	 */
	private $confirm_password;

	/**
	 * Password_Must_Match constructor.
	 *
	 * @since 3.16.0
	 *
	 * @param string $new_password New password.
	 * @param string $confirm_password New password.
	 */
	public function __construct( $new_password, $confirm_password ) {
		$this->new_password     = $new_password;
		$this->confirm_password = $confirm_password;
	}

	/**
	 * Evaluates whether the user password matched or not.
	 *
	 * @since 3.16.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		if ( $this->new_password !== $this->confirm_password ) {
			return false;
		}
		return ( $this->new_password === $this->confirm_password );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.16.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		return esc_html__( 'Password does not match. Please check again and input properly.', 'tournamatch' );
	}
}
