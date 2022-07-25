<?php
/**
 * Manages Tournamatch REST endpoint for ladders.
 *
 * @link  https://www.tournamatch.com
 * @since 3.19.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\Cannot_Change_Ladder_Competition;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for ladders.
 *
 * @since 3.19.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Ladder extends Controller {


	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.19.0
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/ladders/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/ladders/(?P<id>\d+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the object.' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Check if a given request has access to get a ladder.
	 *
	 * @since 3.21.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request->get_param( 'id' ) ) );
		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		if ( 'hidden' === $ladder->visibility ) {
			return current_user_can( 'manage_tournamatch' );
		}

		return true;
	}

	/**
	 * Retrieves a single ladder item.
	 *
	 * @since 3.21.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		global $wpdb;

		$ladder   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request->get_param( 'id' ) ) );
		$data     = $this->prepare_item_for_response( $ladder, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Check if a given request has access to get ladders.
	 *
	 * @since 3.21.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves a collection of ladders.
	 *
	 * @since 3.21.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $wpdb;

		$params = $request->get_params();

		$sql = "SELECT * FROM `{$wpdb->prefix}trn_ladders` AS `l` WHERE 1 = 1";

		if ( isset( $params['status'] ) && ( in_array( $params['status'], array( 'active', 'inactive' ), true ) ) ) {
			$sql .= $wpdb->prepare( ' AND `l`.`status` = %s', $params['status'] );
		}

		if ( isset( $params['visibility'] ) && ( in_array( $params['visibility'], array( 'visible', 'hidden' ), true ) ) ) {
			$sql .= $wpdb->prepare( ' AND `l`.`visibility` = %s', $params['visibility'] );
		}

		$sql .= ' ORDER BY `l`.`ladder_id` ASC';

		$ladders = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$ladder_items = array();

		foreach ( $ladders as $ladder ) {
			$data           = $this->prepare_item_for_response( $ladder, $request );
			$ladder_items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $ladder_items );

		$response->header( 'X-WP-Total', count( $ladder_items ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}

	/**
	 * Check if a given request has access to create a ladder.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Creates a single ladder item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$prepared_post = (array) $this->prepare_item_for_database( $request );

		$wpdb->insert( $wpdb->prefix . 'trn_ladders', $prepared_post );

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $wpdb->insert_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $ladder, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Check if a given request has access to update a ladder.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['id'] ) );
		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single ladder item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['id'] ) );
		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$rules = array();

		if ( isset( $request['competitor_type'] ) ) {
			$rules[] = new Cannot_Change_Ladder_Competition( $request['id'], $request['competitor_type'] );
		}

		// Verify business rules.
		if ( 0 < count( $rules ) ) {
			$this->verify_business_rules( $rules );
		}

		$prepared_post = (array) $this->prepare_item_for_database( $request );

		if ( 0 < count( $prepared_post ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_ladders', $prepared_post, array( 'ladder_id' => $request['id'] ) );
		}

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $ladder->ladder_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $ladder, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 4.1.0
	 *
	 * @param object $ladder Ladder object.
	 * @return array Links for the given ladder.
	 */
	protected function prepare_links( $ladder ) {

		$base = "{$this->namespace}/ladders";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $ladder->ladder_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		return $links;
	}

	/**
	 * Retrieves the ladder schema, conforming to JSON Schema.
	 *
	 * @since 3.19.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ladders',
			'type'       => 'object',
			'properties' => array(
				'ladder_id'         => array(
					'description' => esc_html__( 'The id for the ladder.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'name'              => array(
					'description' => esc_html__( 'The display name for the ladder.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'game_id'           => array(
					'description' => esc_html__( 'The corresponding game for the ladder.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 0,
				),
				'competitor_type'   => array(
					'description' => esc_html__( 'Indicates whether the ladder is a singles or teams competition.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'players', 'teams' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'players',
				),
				'team_size'         => array(
					'description' => esc_html__( 'The number of players per team if the ladder is a teams competition.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 2,
					'minimum'     => 2,
				),
				'win_points'        => array(
					'description' => esc_html__( 'The number of points awarded for a win.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 3,
				),
				'loss_points'       => array(
					'description' => esc_html__( 'The number of points awarded for a loss.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 1,
				),
				'draw_points'       => array(
					'description' => esc_html__( 'The number of points awarded for a draw.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 2,
				),
				'direct_challenges' => array(
					'description' => esc_html__( 'Indicates whether ladder competitors may directly challenge another competitor.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'enabled', 'disabled' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'enabled',
				),
				'rules'             => array(
					'description' => esc_html__( 'The rules for the ladder.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'visibility'        => array(
					'description' => esc_html__( 'Indicates whether the ladder is visible to non-admins.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'visible', 'hidden' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'visible',
				),
				'status'            => array(
					'description' => esc_html__( 'Indicates whether competitors may join, report matches, or manage challenges for the ladder.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'active', 'inactive' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'active',
				),
				'link'              => array(
					'description' => esc_html__( 'URL to the ladder.' ),
					'type'        => 'string',
					'trn-subtype' => 'callable',
					'trn-get'     => function( $ladder ) {
						return trn_route( 'ladders.single', array( 'id' => $ladder->ladder_id ) );
					},
					'format'      => 'uri',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Ladder();
