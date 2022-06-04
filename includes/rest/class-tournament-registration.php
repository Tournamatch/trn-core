<?php
/**
 * Manages Tournamatch REST endpoint for tournament registrations.
 *
 * @link  https://www.tournamatch.com
 * @since 3.17.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\One_Competitor_Per_Tournament;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for tournament registrations.
 *
 * @since 3.17.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Tournament_Registration extends Controller {
	/*
	* REST API notes for future changes.
	*
	* GET    /tournament-registration/
	* GET    /tournament-registration/$id (get information about a single tournament-registration)
	* POST   /tournament-registration/    (create tournament-registration)
	* PATCH  /tournament-registration/$id (update a tournament-registration)
	* DELETE /tournament-registration/$id (remove tournament-registration)
	*/

	/**
	 * Tournament registration business rules.
	 *
	 * @since 3.17.0
	 *
	 * @var array $registration_conditions Array of rules.
	 */
	private $registration_conditions;

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
	 * @since 3.19.0 Added POST endpoint
	 * @since 3.25.0 Added GET many endpoint.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/tournament-registrations/',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'tournament_id' => array(
							'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'player_id'     => array(
							'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'team_id'       => array(
							'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/tournament-registrations/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the object.' ),
						'type'        => 'integer',
					),
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
	 * Check if a given request has access to get a collection of tournament competitors.
	 *
	 * @since 3.25.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}


	/**
	 * Retrieves a collection of tournament competitors.
	 *
	 * @since 3.25.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $wpdb;

		$tournament_id = isset( $request['tournament_id'] ) ? intval( $request['tournament_id'] ) : null;
		$player_id     = isset( $request['player_id'] ) ? intval( $request['player_id'] ) : null;
		$team_id       = isset( $request['team_id'] ) ? intval( $request['team_id'] ) : null;

		$total_data = "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments_entries` AS `te` WHERE 1 = 1 ";
		if ( ! is_null( $tournament_id ) ) {
			$total_data .= $wpdb->prepare( 'AND `tournament_id` = %d ', $tournament_id );
		}
		if ( ! is_null( $player_id ) ) {
			$total_data .= $wpdb->prepare( "AND ((`competitor_type` = %s AND `competitor_id` = %d) OR (`competitor_type` = %s AND `competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d))) ", 'players', $player_id, 'teams', $player_id );
		}
		if ( ! is_null( $team_id ) ) {
			$total_data .= $wpdb->prepare( 'AND `competitor_type` = %s AND `competitor_id` = %d ', 'teams', $team_id );
		}
		$total_data = $wpdb->get_var( $total_data ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$sql = "
SELECT 
  `te`.*, 
  CASE `te`.`competitor_type`
    WHEN 'players' THEN `p`.`display_name`
    ELSE `t`.`name`
    END `name` 
FROM `{$wpdb->prefix}trn_tournaments_entries` AS `te`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p` ON `te`.`competitor_id` = `p`.`user_id` AND `te`.`competitor_type` = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t` ON `te`.`competitor_id` = `t`.`team_id` AND `te`.`competitor_type` = 'teams'
WHERE 1 = 1 ";

		if ( ! is_null( $tournament_id ) ) {
			$sql .= $wpdb->prepare( 'AND `tournament_id` = %d ', $tournament_id );
		}
		if ( ! is_null( $player_id ) ) {
			$sql .= $wpdb->prepare( "AND ((`competitor_type` = %s AND `competitor_id` = %d) OR (`competitor_type` = %s AND `competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)))", 'players', $player_id, 'teams', $player_id );
		}
		if ( ! is_null( $team_id ) ) {
			$sql .= $wpdb->prepare( 'AND `competitor_type` = %s AND `competitor_id` = %d ', 'teams', $team_id );
		}

		if ( ! empty( $request['search'] ) ) {
			$sql .= $wpdb->prepare( ' AND (`p`.`display_name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `t`.`name` LIKE %s)', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
		}

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filtered = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns  = array(
				'name'        => 'name',
				'joined_date' => 'joined_date',
			);
			$order_by = explode( '.', $request['orderby'] );

			if ( ( 2 === count( $order_by ) && in_array( $order_by[0], array_keys( $columns ), true ) ) ) {
				$direction = ( 'desc' === $order_by[1] ) ? 'desc' : 'asc';
				$column    = $columns[ $order_by[0] ];

				$sql .= " ORDER BY `$column` $direction";
			}
		} else {
			$sql .= ' ORDER BY `te`.`joined_date` DESC';
		}

		if ( isset( $request['per_page'] ) && ( '-1' !== $request['per_page'] ) ) {
			$length = $request['per_page'] ?: 10;
			$start  = $request['page'] ? ( $request['page'] * $length ) : 0;
			$sql   .= $wpdb->prepare( ' LIMIT %d, %d', $start, $length );
		}

		$competitors = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$items = array();
		foreach ( $competitors as $competitor ) {
			$data    = $this->prepare_item_for_response( $competitor, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $items );

		$response->header( 'X-WP-Total', intval( $total_data ) );
		$response->header( 'X-WP-TotalPages', 1 );
		$response->header( 'TRN-Draw', intval( $request['draw'] ) );
		$response->header( 'TRN-Filtered', intval( $total_filtered ) );

		return $response;
	}

	/**
	 * Check if a given request has access to create a tournament registration.
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
	 * Creates a single tournament registration.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		// one competitor per event.
		$this->verify_business_rules(
			array(
				new One_Competitor_Per_Tournament( $request->get_param( 'tournament_id' ), $request->get_param( 'competitor_id' ) ),
			)
		);

		$data = array(
			'tournament_id'   => $request->get_param( 'tournament_id' ),
			'competitor_id'   => $request->get_param( 'competitor_id' ),
			'competitor_type' => $request->get_param( 'competitor_type' ),
			'joined_date'     => $wpdb->get_var( 'SELECT UTC_TIMESTAMP()' ),
		);

		$wpdb->insert( $wpdb->prefix . 'trn_tournaments_entries', $data );

		$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_entry_id` = %d", $wpdb->insert_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $registration, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Checks if a given request has access to delete a tournament registration.
	 *
	 * @since 3.17.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		global $wpdb;

		$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_entry_id` = %d", $request['id'] ) );
		if ( ! $registration ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Tournament registration does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$this->registration_conditions = trn_get_tournament_register_conditions( $registration->tournament_id, get_current_user_id() );

		return ( $this->registration_conditions['can_unregister'] );
	}

	/**
	 * Deletes a single tournament registration.
	 *
	 * @since 3.17.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		global $wpdb;

		$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_entry_id` = %d", $request['id'] ) );
		if ( ! $registration ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Tournament registration does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_entry_id` = %d", $registration->tournament_entry_id ) );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The tournament registration was deleted.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}


	/**
	 * Prepares a single tournament registration item for response.
	 *
	 * @since 3.17.0
	 *
	 * @param Object           $tournament_registration Tournament registration object.
	 * @param \WP_REST_Request $request                 Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $tournament_registration, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'tournament_entry_id', $fields ) ) {
			$data['tournament_entry_id'] = (int) $tournament_registration->tournament_entry_id;
		}

		if ( rest_is_field_included( 'tournament_id', $fields ) ) {
			$data['tournament_id'] = (int) $tournament_registration->tournament_id;
		}

		if ( rest_is_field_included( 'competitor_id', $fields ) ) {
			$data['competitor_id'] = (int) $tournament_registration->competitor_id;
		}

		if ( rest_is_field_included( 'competitor_type', $fields ) ) {
			$data['competitor_type'] = $tournament_registration->competitor_type;
		}

		if ( rest_is_field_included( 'joined_date', $fields ) ) {
			$data['joined_date'] = array(
				'raw'      => $tournament_registration->joined_date,
				'rendered' => date( get_option( 'date_format' ), strtotime( $tournament_registration->joined_date ) ),
			);
		}

		if ( rest_is_field_included( 'can_unregister', $fields ) ) {
			// Admins, single competitors, and team owners may unregister if the tournament has not started.
			$data['can_unregister'] = false;        }

		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $tournament_registration );
		$response->add_links( $links );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 3.25.0
	 *
	 * @param Object $competitor Tournament competitor object.
	 *
	 * @return array Links for the given ladder competitor.
	 */
	protected function prepare_links( $competitor ) {
		$base = "{$this->namespace}/tournament-registrations";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $competitor->tournament_entry_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		$links['competitor'] = array(
			'href'       => rest_url( "{$this->namespace}/{$competitor->competitor_type}/{$competitor->competitor_id}" ),
			'embeddable' => true,
		);

		$links['tournament'] = array(
			'href'       => rest_url( "{$this->namespace}/tournaments/{$competitor->tournament_id}" ),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Retrieves the tournament registration schema, conforming to JSON Schema.
	 *
	 * @since 3.17.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'tournament-registration',
			'type'       => 'object',
			'properties' => array(
				'tournament_entry_id' => array(
					'description' => esc_html__( 'The id for the tournament registration.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'tournament_id'       => array(
					'description' => esc_html__( 'The id for the tournament.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'competitor_id'       => array(
					'description' => esc_html__( 'The id for the competitor.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'competitor_type'     => array(
					'description' => esc_html__( 'The type of competitor registering.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'players', 'teams' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'joined_date'         => array(
					'description' => esc_html__( 'The datetime the tournament competitor registered for the tournament.', 'tournamatch' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'The datetime the tournament competitor registered for the tournament, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'The datetime the tournament competitor joined the tournament, transformed for display.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'can_unregister'      => array(
					'description' => esc_html__( 'Indicates whether the authenticated user may unregister for the tournament.', 'tournamatch' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Tournament_Registration();
