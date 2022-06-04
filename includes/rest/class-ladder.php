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

		$data = array(
			'name'              => $request->get_param( 'name' ),
			'game_id'           => $request->get_param( 'game_id' ),
			'competitor_type'   => $request->get_param( 'competitor_type' ),
			'team_size'         => $request->get_param( 'team_size' ),
			'win_points'        => $request->get_param( 'win_points' ),
			'loss_points'       => $request->get_param( 'loss_points' ),
			'draw_points'       => $request->get_param( 'draw_points' ),
			'direct_challenges' => $request->get_param( 'direct_challenges' ),
			'rules'             => $request->get_param( 'rules' ),
			'visibility'        => $request->get_param( 'visibility' ),
			'status'            => $request->get_param( 'status' ),
		);

		$wpdb->insert( $wpdb->prefix . 'trn_ladders', $data );

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

		$schema = $this->get_item_schema();

		$data = array();

		if ( ! empty( $schema['properties']['name'] ) && isset( $request['name'] ) ) {
			$data['name'] = $request['name'];
		}
		if ( ! empty( $schema['properties']['game_id'] ) && isset( $request['game_id'] ) ) {
			$data['game_id'] = $request['game_id'];
		}
		if ( ! empty( $schema['properties']['competitor_type'] ) && isset( $request['competitor_type'] ) ) {
			$data['competitor_type'] = $request['competitor_type'];
		}
		if ( ! empty( $schema['properties']['team_size'] ) && isset( $request['team_size'] ) ) {
			$data['team_size'] = $request['team_size'];
		}
		if ( ! empty( $schema['properties']['win_points'] ) && isset( $request['win_points'] ) ) {
			$data['win_points'] = $request['win_points'];
		}
		if ( ! empty( $schema['properties']['loss_points'] ) && isset( $request['loss_points'] ) ) {
			$data['loss_points'] = $request['loss_points'];
		}
		if ( ! empty( $schema['properties']['draw_points'] ) && isset( $request['draw_points'] ) ) {
			$data['draw_points'] = $request['draw_points'];
		}
		if ( ! empty( $schema['properties']['direct_challenges'] ) && isset( $request['direct_challenges'] ) ) {
			$data['direct_challenges'] = $request['direct_challenges'];
		}
		if ( ! empty( $schema['properties']['rules'] ) && isset( $request['rules'] ) ) {
			$data['rules'] = $request['rules'];
		}
		if ( ! empty( $schema['properties']['visibility'] ) && isset( $request['visibility'] ) ) {
			$data['visibility'] = $request['visibility'];
		}
		if ( ! empty( $schema['properties']['status'] ) && isset( $request['status'] ) ) {
			$data['status'] = $request['status'];
		}

		if ( 0 < count( $data ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_ladders', $data, array( 'ladder_id' => $request['id'] ) );
		}

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $ladder->ladder_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $ladder, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Prepares a single ladder item for response.
	 *
	 * @since 3.19.0
	 *
	 * @param Object           $ladder  Ladder object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $ladder, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'ladder_id', $fields ) ) {
			$data['ladder_id'] = (int) $ladder->ladder_id;
		}

		if ( rest_is_field_included( 'name', $fields ) ) {
			$data['name'] = $ladder->name;
		}

		if ( rest_is_field_included( 'game_id', $fields ) ) {
			$data['game_id'] = (int) $ladder->game_id;
		}

		if ( rest_is_field_included( 'competitor_type', $fields ) ) {
			$data['competitor_type'] = $ladder->competitor_type;
		}

		if ( rest_is_field_included( 'team_size', $fields ) ) {
			$data['team_size'] = (int) $ladder->team_size;
		}

		if ( rest_is_field_included( 'win_points', $fields ) ) {
			$data['win_points'] = (int) $ladder->win_points;
		}

		if ( rest_is_field_included( 'loss_points', $fields ) ) {
			$data['loss_points'] = (int) $ladder->loss_points;
		}

		if ( rest_is_field_included( 'draw_points', $fields ) ) {
			$data['draw_points'] = (int) $ladder->draw_points;
		}

		if ( rest_is_field_included( 'direct_challenges', $fields ) ) {
			$data['direct_challenges'] = ( 'enabled' === $ladder->direct_challenges ) ? 'enabled' : 'disabled';
		}

		if ( rest_is_field_included( 'rules', $fields ) ) {
			$data['rules'] = $ladder->rules;
		}

		if ( rest_is_field_included( 'visibility', $fields ) ) {
			$data['visibility'] = ( 'visible' === $ladder->visibility ) ? 'visible' : 'hidden';
		}

		if ( rest_is_field_included( 'status', $fields ) ) {
			$data['status'] = ( 'active' === $ladder->status ) ? 'active' : 'inactive';
		}

		if ( rest_is_field_included( 'link', $fields ) ) {
			$data['link'] = trn_route( 'ladders.single', array( 'id' => $ladder->ladder_id ) );
		}

		return rest_ensure_response( $data );
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
