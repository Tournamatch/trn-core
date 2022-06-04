<?php
/**
 * Manages Tournamatch REST endpoint for game images.
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
 * Manages Tournamatch REST endpoint for game images.
 *
 * @since 3.18.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Game_Image extends Controller {

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
			'/game-images/',
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

	}

	/**
	 * Checks if a given request has access to read game images.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Retrieves a collection of game images.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$images = trn_get_files_of_type( trn_upload_dir() . '/images/games/', array( 'gif', 'jpg', 'png', 'jpeg' ) );

		$response = rest_ensure_response( $images );

		$response->header( 'X-WP-Total', count( $images ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}

	/**
	 * Check if a given request has access to create a game image.
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
	 * Creates a single game image item.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool|\WP_REST_Response
	 */
	public function create_item( $request ) {
		$files = $request->get_file_params();
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $file ) {
				$result = upload_image( trn_upload_dir() . '/images/games/', array( 'png', 'jpg', 'jpeg', 'gif' ), $file );
				if ( true !== $result ) {
					return new \WP_REST_Response(
						array(
							'message' => $result,
							'data'    => array(
								'status' => 403,
							),
						),
						403
					);
				}
			}
		}

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'Game image uploaded successfully.', 'tournamatch' ),
				'data'    => array(
					'status' => 201,
				),
			),
			201
		);
	}

}

new Game_Image();
