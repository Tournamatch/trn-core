<?php
/**
 * Base class for Tournamatch REST Controller endpoints.
 *
 * @link       https://www.tournamatch.com
 * @since      3.16.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\Business_Rule;
use WP_REST_Controller;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base class for Tournamatch REST endpoints.
 *
 * @since      3.16.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Controller extends WP_REST_Controller {

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
	 * @since 3.16.0
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

	/**
	 * Prepares a single data item for create or update.
	 *
	 * @since 4.1.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \stdClass Object or WP_Error.
	 */
	public function prepare_item_for_database( $request ) {
		$prepared_post = new \stdClass();

		$schema = $this->get_item_schema();

		foreach ( $schema['properties'] as $field => $meta ) {
			if ( isset( $meta['readonly'] ) && $meta['readonly'] ) {
				continue;
			}

			if ( isset( $request[ $field ] ) ) {
				$prepared_post->$field = $request[ $field ];
			} elseif ( ! isset( $request['id'] ) && isset( $meta['default'] ) ) {
				$prepared_post->$field = $meta['default'];
			}
		}

		return $prepared_post;
	}

	/**
	 * Returns the formatted value of a field given a field type.
	 *
	 * @since 4.1.0
	 *
	 * @param object $data_context The data context item.
	 * @param string $field The name of the field to return.
	 * @param string $field_type The field type.
	 *
	 * @return mixed The formatted value.
	 */
	protected function get_field_for_response( $data_context, $field, $field_type ) {
		switch ( $field_type ) {
			case 'datetime':
				return array(
					'raw'      => $data_context->$field,
					'rendered' => date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $data_context->$field ) ) ),
				);
			case 'boolean':
				return (bool) $data_context->$field;
			case 'integer':
				return (int) $data_context->$field;
			case 'string':
			default:
				return $data_context->$field;
		}
	}

	/**
	 * Prepares a single item for response.
	 *
	 * @since 4.1.0
	 *
	 * @param Object           $data_context    Database object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $data_context, $request ) {

		$fields = $this->get_fields_for_response( $request );
		$schema = $this->get_item_schema();

		// Base fields for every post.
		$data = array();

		foreach ( $fields as $field ) {
			if ( rest_is_field_included( $field, $fields ) ) {
				if ( isset( $schema['properties'][ $field ]['trn-subtype'] ) && ( 'callable' === $schema['properties'][ $field ]['trn-subtype'] ) ) {
					if ( isset( $schema['properties'][ $field ]['trn-get'] ) && is_callable( $schema['properties'][ $field ]['trn-get'] ) ) {
						$data[ $field ] = call_user_func( $schema['properties'][ $field ]['trn-get'], $data_context );
						continue;
					}
				}

				if ( ! isset( $data_context->$field ) ) {
					continue;
				}

				$field_type = isset( $schema['properties'][ $field ]['type'] ) ? $schema['properties'][ $field ]['type'] : 'string';

				if ( is_array( $field_type ) ) {
					$field_type = rest_get_best_type_for_value( $data_context->$field, $field_type );
				}

				if ( 'object' === $field_type && isset( $schema['properties'][ $field ]['trn-subtype'] ) ) {
					$field_type = $schema['properties'][ $field ]['trn-subtype'];
				}

				$data[ $field ] = $this->get_field_for_response( $data_context, $field, $field_type );
			}
		}

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		if ( method_exists( $this, 'prepare_links' ) ) {
			$links = $this->prepare_links( $data_context );
			$response->add_links( $links );
		}

		return $response;
	}
}
