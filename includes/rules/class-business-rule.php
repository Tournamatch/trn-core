<?php
/**
 * Defines abstract implementation of business rules.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the abstract implementation of a business rule.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
interface Business_Rule {

	/**
	 * Evaluates the conditions for this business rule.
	 *
	 * @since 3.8.0
	 *
	 * @return boolean True if the conditions for this rule pass, false otherwise.
	 */
	public function passes();

	/**
	 * Returns a message explaining why this rule fails.
	 *
	 * @since 3.8.0
	 *
	 * @return string A message explaining why this rule fails.
	 */
	public function failure_message();
}
