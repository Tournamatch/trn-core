<?php
/**
 * Manages Tournamatch REST endpoint for bulk tournament registration.
 *
 * @link       https://www.tournamatch.com
 * @since      3.17.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\One_Competitor_Per_Tournament;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for bulk tournament registration.
 *
 * @since      3.17.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Tournament_Registration_List extends Controller {


	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.17.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.17.0
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/tournament-registration-list/',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check if a given request has access to create
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Creates tournament registrations from a list of items.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$tournament_id = $request->get_param( 'tournament_id' );
		$competitors   = $request->get_param( 'competitors' );
		$tournament    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $tournament_id ) );

		if ( 'players' === $tournament->competitor_type ) {
			// This is a singles tournament. Just verify each user id is registered.
			array_walk(
				$competitors,
				function( &$competitor ) use ( $request, $tournament ) {
					global $wpdb;

					$competitor['user_id'] = email_exists( $competitor['text'] );

					if ( $competitor['user_id'] ) {
						$rules = array(
							new One_Competitor_Per_Tournament( $tournament->tournament_id, $competitor['user_id'] ),
						);

						$rules = apply_filters(
							'trn_rest_create_tournament_competitor_rules',
							$rules,
							array(
								'tournament_id' => $tournament->tournament_id,
								'competitor_id' => $competitor['user_id'],
							)
						);

						$passes = array_reduce(
							$rules,
							function( $carry, $rule ) {
								return $carry && $rule->passes();
							},
							true
						);

						if ( $passes ) {
							$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_tournaments_entries` VALUES (NULL, %d, %d, 'players', UTC_TIMESTAMP(), NULL)", $tournament->tournament_id, $competitor['user_id'] ) );
						}
						$competitor['result'] = 'registered';
					} else {
						$competitor['result'] = 'unknown';
					}
				}
			);
		} else {
			// This is a teams tournament. Find each team each user is a member of. Verify one of those teams is registered. If not, find first team to use.
			array_walk(
				$competitors,
				function( &$competitor ) use ( $wpdb, $tournament_id ) {
					$competitor['user_id'] = email_exists( $competitor['text'] );

					if ( $competitor['user_id'] ) {
						$found = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_id` = %d AND `competitor_id` IN (SELECT DISTINCT team_id FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d )", $tournament_id, $competitor['user_id'] ) );
						if ( '0' === $found ) {
							$team_id = $wpdb->get_var( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d ORDER BY `team_member_id` LIMIT 1", $competitor['user_id'] ) );
							if ( ! is_null( $team_id ) ) {
								$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_tournaments_entries` VALUES (NULL, %d, %d, 'teams', UTC_TIMESTAMP(), NULL)", $tournament_id, $team_id ) );
								$competitor['result'] = 'registered';
							} else {
								$competitor['result'] = 'unknown';
							}
						} else {
							$competitor['result'] = 'registered';
						}
					} else {
						$competitor['result'] = 'unknown';
					}
				}
			);
		}
		$request->set_param( 'context', 'edit' );

		$data = array(
			'tournament_id' => $tournament_id,
			'competitors'   => $competitors,
		);

		$response = $this->prepare_item_for_response( $data, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Prepares a single tournament registration list for response.
	 *
	 * @since 3.17.0
	 *
	 * @param Object           $registrations Tournamatch registration list.
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $registrations, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'tournament_id', $fields ) ) {
			$data['tournament_id'] = (int) $registrations['tournament_id'];
		}

		if ( rest_is_field_included( 'competitors', $fields ) ) {
			array_walk(
				$registrations['competitors'],
				function( &$competitor ) use ( $fields ) {
					$data = array();

					if ( rest_is_field_included( 'competitors.text', $fields ) ) {
						$data['text'] = $competitor['text'];
					}

					if ( rest_is_field_included( 'competitors.type', $fields ) ) {
						$data['type'] = $competitor['type'];
					}

					if ( rest_is_field_included( 'competitors.result', $fields ) ) {
						$data['result'] = ( 'registered' === $competitor['result'] ) ? 'registered' : 'unknown';
					}

					$competitor = $data;
				}
			);

			$data['competitors'] = $registrations['competitors'];
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Retrieves the tournament registration list schema, conforming to JSON Schema.
	 *
	 * @since  3.17.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'tournament-registration-list',
			'type'       => 'object',
			'properties' => array(
				'tournament_id' => array(
					'description' => esc_html__( 'The id of the tournament to register the given competitors.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'competitors'   => array(
					'description' => esc_html__( 'The list of competitors to register.', 'tournamatch' ),
					'type'        => 'array',
					'items'       => array(
						'text'   => array(
							'description' => esc_html__( 'The value corresponding to the user to register for the tournament.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'required'    => true,
						),
						'type'   => array(
							'description' => esc_html__( 'The type of data for this tournament registration corresponding to the text field. Only \'email\' is currently accepted.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'required'    => true,
						),
						'result' => array(
							'description' => esc_html__( 'The result of the tournament registration for this item. Either \'registered\' or \'unknown\'.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Tournament_Registration_List();
