<?php
/**
 * Manages Tournamatch REST endpoint for team ranks.
 *
 * @link       https://www.tournamatch.com
 * @since      3.17.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\Cannot_Move_Default_Rank;
use Tournamatch\Rules\Cannot_Move_Owner_Rank;
use Tournamatch\Rules\Cannot_Remove_Default_Rank;
use Tournamatch\Rules\Cannot_Remove_Owner_Rank;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for team ranks.
 *
 * @since      3.17.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Team_Rank extends Controller {
	/*
	 * REST API notes for future changes.
	 *
	 * GET    /team-rank/
	 * GET    /team-rank/$id (get information about a single team-rank)
	 * POST   /team-rank/    (create team-rank)
	 * PATCH  /team-rank/$id (update a team rank name, max, or position)
	 * DELETE /team-rank/$id (remove team-rank)
	 */

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
			'/team-ranks/',
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
			'/team-ranks/(?P<id>[\d]+)',
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

		register_rest_route(
			$this->namespace,
			'/team-ranks/(?P<id>[\d]+)/move',
			array(
				'args'   => array(
					'id'    => array(
						'description' => esc_html__( 'Unique identifier for the object.' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'after' => array(
						'description' => esc_html__( 'Unique identifier of the item after which this item should be inserted.' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'move_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Check if a given request has access to get a team rank.
	 *
	 * @since 3.25.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		global $wpdb;

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` AS `tr` WHERE `tr`.`team_rank_id` = %d", $request->get_param( 'id' ) ) );
		if ( ! $team_rank ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team rank does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Retrieves a single team rank item.
	 *
	 * @since 3.25.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		global $wpdb;

		$ladder   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` AS `tr` WHERE `tr`.`team_rank_id` = %d", $request->get_param( 'id' ) ) );
		$data     = $this->prepare_item_for_response( $ladder, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Checks if a given request has access to read team ranks.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves a collection of team-ranks.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $wpdb;

		$team_ranks_result = $wpdb->get_results( "SELECT `team_rank_id`, `title`, `max`, `weight`, `team_id` FROM `{$wpdb->prefix}trn_teams_ranks` ORDER BY `weight` ASC" );

		$team_ranks = array();

		foreach ( $team_ranks_result as $team_rank ) {
			$data         = $this->prepare_item_for_response( $team_rank, $request );
			$team_ranks[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $team_ranks );

		$response->header( 'X-WP-Total', count( $team_ranks ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}

	/**
	 * Check if a given request has access to create a team rank.
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
	 * Creates a single team rank item.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$lowest_rank = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_id` = '-1' ORDER BY `weight` DESC LIMIT 1" );
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_ranks` SET `weight` = %d WHERE `team_rank_id` = %d", ( $lowest_rank->weight + 1 ), $lowest_rank->team_rank_id ) );
		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_teams_ranks` VALUES (NULL, %s, %d, %d)", $request->get_param( 'title' ), $request->get_param( 'max' ), $lowest_rank->weight ) );

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $wpdb->insert_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $team_rank, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Check if a given request has access to update a team rank.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		global $wpdb;

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $request['id'] ) );
		if ( ! $team_rank ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team rank does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single team rank item.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $request['id'] ) );
		if ( ! $team_rank ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team rank does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$schema = $this->get_item_schema();

		$data = array();

		if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
			$data['title'] = substr( trim( $request['title'] ), 0, 25 );
		}
		if ( ! empty( $schema['properties']['max'] ) && isset( $request['max'] ) ) {
			$data['max'] = $request['max'];
		}

		if ( 0 < count( $data ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_teams_ranks', $data, array( 'team_rank_id' => $request['id'] ) );
		}

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $team_rank->team_rank_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $team_rank, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Changes the order of an item.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function move_item( $request ) {
		global $wpdb;

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $request['id'] ) );
		if ( ! $team_rank ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team rank does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$after_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $request['after'] ) );
		if ( ! $after_rank ) {
			/* translators: An integer indicating an id in the database. */
			return new \WP_Error( 'rest_custom_error', sprintf( esc_html__( 'Team rank with id %s does not exist.', 'tournamatch' ), $request['after'] ), array( 'status' => 404 ) );
		}

		$this->verify_business_rules(
			array(
				new Cannot_Move_Owner_Rank( $team_rank->team_rank_id ),
				new Cannot_Move_Default_Rank( $team_rank->team_rank_id ),
				new Cannot_Move_Default_Rank( $after_rank->team_rank_id ),
			)
		);

		$old_weight = intval( $team_rank->weight );
		$new_weight = ( intval( $after_rank->weight ) < $old_weight ) ? intval( $after_rank->weight ) + 1 : intval( $after_rank->weight );
		if ( $new_weight !== $old_weight ) {
			$wpdb->update( $wpdb->prefix . 'trn_teams_ranks', array( 'weight' => $new_weight ), array( 'team_rank_id' => $team_rank->team_rank_id ) );

			// Update the rank of the remaining items.
			if ( $new_weight < $old_weight ) {
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_ranks` SET `weight` = `weight` + 1 WHERE `team_rank_id` != %d AND `weight` > %d AND `weight` < %d", $team_rank->team_rank_id, $after_rank->weight, $old_weight ) );
			} else {
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_ranks` SET `weight` = `weight` - 1 WHERE `team_rank_id` != %d AND `weight` > %d AND `weight` <= %d", $team_rank->team_rank_id, $old_weight, $after_rank->weight ) );
			}
		}

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $team_rank->team_rank_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $team_rank, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to delete a team rank.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		global $wpdb;

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $request['id'] ) );
		if ( ! $team_rank ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team rank does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Deletes a single team rank.
	 *
	 * @since 3.17.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		global $wpdb;

		$team_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $request['id'] ) );
		if ( ! $team_rank ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team rank does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$this->verify_business_rules(
			array(
				new Cannot_Remove_Owner_Rank( $team_rank->team_rank_id ),
				new Cannot_Remove_Default_Rank( $team_rank->team_rank_id ),
			)
		);

		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $team_rank->team_rank_id ) );
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_ranks` SET `weight` = `weight` - 1 WHERE `weight` > %d", $team_rank->weight ) );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The team rank was deleted.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}


	/**
	 * Prepares a single team rank item for response.
	 *
	 * @since 3.17.0
	 *
	 * @param Object           $team_rank Team Rank object.
	 * @param \WP_REST_Request $request   Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $team_rank, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'team_rank_id', $fields ) ) {
			$data['team_rank_id'] = (int) $team_rank->team_rank_id;
		}

		if ( rest_is_field_included( 'title', $fields ) ) {
			$data['title'] = $team_rank->title;
		}

		if ( rest_is_field_included( 'max', $fields ) ) {
			$data['max'] = (int) $team_rank->max;
		}

		if ( rest_is_field_included( 'weight', $fields ) ) {
			$data['weight'] = (int) $team_rank->weight;
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Retrieves the team-ranks schema, conforming to JSON Schema.
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
			'title'      => 'team-rank',
			'type'       => 'object',
			'properties' => array(
				'team_rank_id' => array(
					'description' => esc_html__( 'The id for the team rank.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'title'        => array(
					'description' => esc_html__( 'The title for the team rank.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'max'          => array(
					'description' => esc_html__( 'The max allowed competitors for the team rank.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'weight'       => array(
					'description' => esc_html__( 'The ordinal rank (weight) for the team rank.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Team_Rank();
