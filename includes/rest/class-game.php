<?php
/**
 * Manages Tournamatch REST endpoint for games.
 *
 * @link  https://www.tournamatch.com
 * @since 3.18.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for games.
 *
 * @since 3.18.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Game extends Controller {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.18.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.18.0
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/games/',
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
			'/games/(?P<id>[\d]+)',
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
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::DELETABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Checks if a given request has access to read a game.
	 *
	 * @since 3.27.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		global $wpdb;

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $request['id'] ) );
		if ( ! $game ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Game does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Retrieves a single game.
	 *
	 * @since 3.27.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		global $wpdb;

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $request['id'] ) );
		if ( ! $game ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Game does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$game = $this->prepare_item_for_response( $game, $request );

		$response = rest_ensure_response( $game );

		return $response;
	}

	/**
	 * Checks if a given request has access to read games.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves a collection of games.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $wpdb;

		$games = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}trn_games` ORDER BY `game_id` ASC" );

		$game_items = array();

		foreach ( $games as $game ) {
			$data         = $this->prepare_item_for_response( $game, $request );
			$game_items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $game_items );

		$response->header( 'X-WP-Total', count( $game_items ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}

	/**
	 * Check if a given request has access to create a game.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Creates a single game item.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_games` (`game_id`, `name`, `thumbnail_id`, `banner_id`, `platform`) VALUES (NULL, %s, %d, %d, %s)", $request->get_param( 'name' ), $request->get_param( 'thumbnail_id' ), $request->get_param( 'banner_id' ), $request->get_param( 'platform' ) ) );

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $wpdb->insert_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $game, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Check if a given request has access to update a game.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		global $wpdb;

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $request['id'] ) );
		if ( ! $game ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Game does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single game item.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $request['id'] ) );
		if ( ! $game ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Game does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$schema = $this->get_item_schema();

		$data = array();

		if ( ! empty( $schema['properties']['name'] ) && isset( $request['name'] ) ) {
			$data['name'] = $request['name'];
		}
		if ( ! empty( $schema['properties']['platform'] ) && isset( $request['platform'] ) ) {
			$data['platform'] = $request['platform'];
		}
		if ( ! empty( $schema['properties']['thumbnail_id'] ) && isset( $request['thumbnail_id'] ) ) {
			$data['thumbnail_id'] = $request['thumbnail_id'];
		}
		if ( ! empty( $schema['properties']['banner_id'] ) && isset( $request['banner_id'] ) ) {
			$data['banner_id'] = $request['banner_id'];
		}

		if ( 0 < count( $data ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_games', $data, array( 'game_id' => $request['id'] ) );
		}

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $game->game_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $game, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to delete a tournament registration.
	 *
	 * @since 3.18.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		global $wpdb;

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $request['id'] ) );
		if ( ! $game ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Game does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Deletes a single game.
	 *
	 * @since 3.18.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		global $wpdb;

		$game = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $request['id'] ) );
		if ( ! $game ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Game does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_games` WHERE `game_id` = %d", $game->game_id ) );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The game was deleted.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Retrieves the games schema, conforming to JSON Schema.
	 *
	 * @since 3.18.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'games',
			'type'       => 'object',
			'properties' => array(
				'game_id'      => array(
					'description' => esc_html__( 'The id for the game.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'name'         => array(
					'description' => esc_html__( 'The display name for the game.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'platform'     => array(
					'description' => esc_html__( 'The platform for the game.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'thumbnail_id' => array(
					'description' => esc_html__( 'The image thumbnail id for the game.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 0,
				),
				'thumbnail'    => array(
					'description' => esc_html__( 'The image thumbnail for the game.', 'tournamatch' ),
					'type'        => 'string',
					'trn-subtype' => 'callable',
					'trn-get'     => function( $game ) {
						return trn_get_rest_image_property( $game->thumbnail_id );
					},
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'banner_id'    => array(
					'description' => esc_html__( 'The image banner id for the game.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 0,
				),
				'banner'       => array(
					'description' => esc_html__( 'The image banner for the game.', 'tournamatch' ),
					'type'        => 'string',
					'trn-subtype' => 'callable',
					'trn-get'     => function( $game ) {
						return trn_get_rest_image_property( $game->banner_id, 'full' );
					},
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Game();
