<?php
/**
 * Base class for Tournamatch REST endpoints.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\Business_Rule;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base class for Tournamatch REST endpoints.
 *
 * @since      3.8.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Rest {

	/**
	 * Namespace for all Tournamatch REST routes
	 *
	 * @var string
	 */
	protected $namespace = 'tournamatch/v1';

	/**
	 * Enumerates the collection and evaluates each BusinessRule.
	 *
	 * @param Business_Rule[] $rules Collection of rules to evaluate.
	 *
	 * @since 3.8.0
	 */
	protected function verify_business_rules( $rules ) {
		array_walk(
			$rules,
			function( $rule ) {
				if ( ! $rule->passes() ) {
					$data = array(
						'message' => esc_html( $rule->failure_message() ),
						'data'    => array(
							'status' => 403,
						),
					);

					wp_send_json( $data, 403 );
				}
			}
		);
	}

}
