<?php
/**
 * Defines the business rule that prohibits changing given tournament field if a tournament has started.
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
 * Defines the business rule that prohibits changing given tournament field if a tournament has started.
 *
 * @since      3.19.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Cannot_Change_Tournament_Field implements Business_Rule {

	/**
	 * Name of field to evaluate.
	 *
	 * @var string $field
	 *
	 * @since 3.19.0
	 */
	private $field;

	/**
	 * Existing value of the field to evaluate.
	 *
	 * @var mixed $existing_value
	 *
	 * @since 3.19.0
	 */
	private $existing_value;

	/**
	 * Value given in the request.
	 *
	 * @var mixed Value given in the request.
	 *
	 * @since 3.19.0
	 */
	private $request_value;

	/**
	 * Initializes this business rule.
	 *
	 * @param string $field Field name to evaluate.
	 * @param mixed  $existing_value Existing value of field to evaluate.
	 * @param mixed  $request_value Value given in the request.
	 *
	 * @since 3.19.0
	 */
	public function __construct( $field, $existing_value, $request_value ) {
		$this->field          = $field;
		$this->existing_value = $existing_value;
		$this->request_value  = $request_value;
	}

	/**
	 * Evaluates whether the tournament field has changed.
	 *
	 * @since 3.19.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function passes() {
		return ( (string) $this->existing_value === (string) $this->request_value );
	}

	/**
	 * Returns a message to display on failure.
	 *
	 * @since 3.19.0
	 *
	 * @return string Failure message.
	 */
	public function failure_message() {
		/* translators: The name of the field. */
		return sprintf( esc_html__( 'You cannot change field \'%s\' after the tournament has started.', 'tournamatch' ), $this->field );
	}
}
