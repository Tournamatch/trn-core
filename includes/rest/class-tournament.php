<?php
/**
 * Manages Tournamatch REST endpoint for tournaments.
 *
 * @link       https://www.tournamatch.com
 * @since      3.13.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\Cannot_Change_Tournament_Field;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for tournaments.
 *
 * @since      3.13.0
 * @since      3.19.0 Refactored to implement Controller class.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Tournament extends Controller {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.11.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.11.0
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/tournaments/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'game_id' => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
						'start'   => array(
							'type'    => 'integer',
							'minimum' => 0,
						),
						'length'  => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
						'status'  => array(
							'type' => 'string',
						),
					),
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
			'/tournaments/(?P<id>\d+)',
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
					'permission_callback' => '__return_true',
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Retrieves a single tournament item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		global $wpdb;

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $request['id'] ) );

		$item = $this->prepare_item_for_response( $tournament, $request );

		$response = rest_ensure_response( $item );

		return $response;
	}

	/**
	 * Check if a given request has access to create a tournament.
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
	 * Creates a single tournament item.
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
			'name'            => $request->get_param( 'name' ),
			'game_id'         => $request->get_param( 'game_id' ),
			'start_date'      => $request->get_param( 'start_date' ),
			'competitor_type' => $request->get_param( 'competitor_type' ),
			'team_size'       => $request->get_param( 'team_size' ),
			'bracket_size'    => $request->get_param( 'bracket_size' ),
			'games'           => 0,
			'rules'           => $request->get_param( 'rules' ),
			'visibility'      => $request->get_param( 'visibility' ),
			'status'          => $request->get_param( 'status' ),
		);

		$wpdb->insert( $wpdb->prefix . 'trn_tournaments', $data );

		$insert_id = $wpdb->insert_id;

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $insert_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $tournament, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Check if a given request has access to update a tournament.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		global $wpdb;

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $request['id'] ) );
		if ( ! $tournament ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Tournament does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single tournament item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $request['id'] ) );
		if ( ! $tournament ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Tournament does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		// Verify business rules.
		if ( ! in_array( $tournament->status, array( 'created', 'open' ), true ) ) {
			$this->verify_business_rules(
				array(
					new Cannot_Change_Tournament_Field( 'start_date', $tournament->start_date, $request['start_date'] ),
					new Cannot_Change_Tournament_Field( 'bracket_size', $tournament->bracket_size, $request['bracket_size'] ),
				)
			);
		}

		$schema = $this->get_item_schema();

		$data = array();

		if ( ! empty( $schema['properties']['name'] ) && isset( $request['name'] ) ) {
			$data['name'] = $request['name'];
		}
		if ( ! empty( $schema['properties']['game_id'] ) && isset( $request['game_id'] ) ) {
			$data['game_id'] = $request['game_id'];
		}
		if ( ! empty( $schema['properties']['start_date'] ) && isset( $request['start_date'] ) ) {
			$data['start_date'] = $request['start_date'];
		}
		if ( ! empty( $schema['properties']['competitor_type'] ) && isset( $request['competitor_type'] ) ) {
			$data['competitor_type'] = $request['competitor_type'];
		}
		if ( ! empty( $schema['properties']['team_size'] ) && isset( $request['team_size'] ) ) {
			$data['team_size'] = $request['team_size'];
		}
		if ( ! empty( $schema['properties']['bracket_size'] ) && isset( $request['bracket_size'] ) ) {
			$data['bracket_size'] = $request['bracket_size'];
		}
		if ( ! empty( $schema['properties']['starting_size'] ) && isset( $request['starting_size'] ) ) {
			$data['starting_size'] = $request['starting_size'];
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
			$wpdb->update( $wpdb->prefix . 'trn_tournaments', $data, array( 'tournament_id' => $request['id'] ) );
		}

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $tournament->tournament_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $tournament, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Retrieves a collection of tournaments.
	 *
	 * @since 3.13.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array|object
	 */
	public function get_items( $request ) {
		global $wpdb;

		$params = $request->get_params();

		$total_data = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments`" );

		$sql = $wpdb->prepare( "SELECT `t`.`tournament_id` AS `id`, `t`.*, `g`.`thumbnail`, `g`.`platform` AS `platform`, `g`.`name` AS `game`, COUNT(`te`.`tournament_entry_id`) AS `competitors` FROM `{$wpdb->prefix}trn_tournaments` AS `t` LEFT JOIN `{$wpdb->prefix}trn_games` AS `g` ON `t`.`game_id` = `g`.`game_id` LEFT JOIN `{$wpdb->prefix}trn_tournaments_entries` AS `te` ON `te`.`tournament_id` = `t`.`tournament_id` WHERE `t`.`visibility` = %s", 'visible' );

		if ( isset( $params['game_id'] ) ) {
			$sql .= $wpdb->prepare( '  AND `t`.`game_id` = ' . intval( $params['game_id'] ) );
		}

		if ( isset( $params['platform'] ) ) {
			$sql .= $wpdb->prepare( '  AND `g`.`platform` = \'' . esc_sql( $params['platform'] ) . '\'' );
		}

		if ( isset( $params['status'] ) ) {
			$sql .= $wpdb->prepare( '  AND `t`.`status` = \'' . esc_sql( $params['status'] ) . '\'' );
		}

		$sql .= ' GROUP BY `t`.`tournament_id`';
		$sql .= ' ORDER BY `t`.`start_date` ASC';

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filtered = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns  = array( 'name', 'competitors', 'platform' );
			$order_by = explode( '.', $request['orderby'] );

			if ( ( 2 === count( $order_by ) && in_array( $order_by[0], $columns, true ) ) ) {
				$direction = ( 'desc' === $order_by[1] ) ? 'desc' : 'asc';

				$sql .= " ORDER BY `$order_by[0]` $direction";
			}
		}

		if ( isset( $request['per_page'] ) && ( '-1' !== $request['per_page'] ) ) {
			$length = $request['per_page'] ?: 10;
			$start  = isset( $request['page'] ) ? ( $request['page'] * $length ) : 0;
			$sql   .= $wpdb->prepare( ' LIMIT %d, %d', $start, $length );
		}

		$tournaments = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$items = array();

		foreach ( $tournaments as $tournament ) {
			$data    = $this->prepare_item_for_response( $tournament, $request );
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
	 * Prepares a single tournament item for response.
	 *
	 * @since 3.19.0
	 *
	 * @param Object           $tournament    Tournament object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $tournament, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'tournament_id', $fields ) ) {
			$data['tournament_id'] = (int) $tournament->tournament_id;
		}

		if ( rest_is_field_included( 'name', $fields ) ) {
			$data['name'] = $tournament->name;
		}

		if ( rest_is_field_included( 'game_id', $fields ) ) {
			$data['game_id'] = (int) $tournament->game_id;
		}

		if ( rest_is_field_included( 'start_date', $fields ) ) {
			$data['start_date'] = $tournament->start_date;
		}

		if ( rest_is_field_included( 'competitor_type', $fields ) ) {
			$data['competitor_type'] = $tournament->competitor_type;
		}

		if ( rest_is_field_included( 'team_size', $fields ) ) {
			$data['team_size'] = (int) $tournament->team_size;
		}

		if ( rest_is_field_included( 'bracket_size', $fields ) ) {
			$data['bracket_size'] = (int) $tournament->bracket_size;
		}

		if ( rest_is_field_included( 'started_size', $fields ) ) {
			$data['started_size'] = (int) $tournament->started_size;
		}

		if ( rest_is_field_included( 'rules', $fields ) ) {
			$data['rules'] = $tournament->rules;
		}

		if ( rest_is_field_included( 'visibility', $fields ) ) {
			$data['visibility'] = ( 'visible' === $tournament->visibility ) ? 'visible' : 'hidden';
		}

		if ( rest_is_field_included( 'status', $fields ) ) {
			$data['status'] = $tournament->status;
		}

		if ( rest_is_field_included( 'link', $fields ) ) {
			$data['link'] = trn_route( 'tournaments.single', array( 'id' => $tournament->tournament_id ) );
		}

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $tournament );
		$response->add_links( $links );

		return $response;
	}


	/**
	 * Prepares links for the request.
	 *
	 * @since 3.21.0
	 *
	 * @param object $tournament Tournament object.
	 * @return array Links for the given tournament.
	 */
	protected function prepare_links( $tournament ) {

		$base = "{$this->namespace}/tournaments";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $tournament->tournament_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		$links['link']          = array(
			'href'       => rest_url( "{$this->namespace}/tournaments/?&mode=rules&tournid={$tournament->tournament_id}" ),
			'embeddable' => true,
		);
		$links['register_link'] = array(
			'href'       => rest_url( "{$this->namespace}/tournaments/?&mode=register&tournid={$tournament->tournament_id}" ),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Retrieves the tournament schema, conforming to JSON Schema.
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
			'title'      => 'tournaments',
			'type'       => 'object',
			'properties' => array(
				'tournament_id'   => array(
					'description' => esc_html__( 'The id for the tournament.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'name'            => array(
					'description' => esc_html__( 'The display name for the tournament.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'game_id'         => array(
					'description' => esc_html__( 'The corresponding game for the tournament.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 0,
				),
				'start_date'      => array(
					'description' => esc_html__( 'The datetime the tournament is scheduled to start.', 'tournamatch' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'competitor_type' => array(
					'description' => esc_html__( 'Indicates whether the tournament is a singles or teams competition.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'players', 'teams' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'players',
				),
				'team_size'       => array(
					'description' => esc_html__( 'The number of players per team if the tournament is a teams competition.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 2,
					'minimum'     => 2,
				),
				'bracket_size'    => array(
					'description' => esc_html__( 'The number of competitors for this tournament. A value of zero indicates unlimited competitors may register', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 0,
				),
				'started_size'    => array(
					'description' => esc_html__( 'Indicates the number of competitors seeded when the tournament started', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => null,
				),
				'rules'           => array(
					'description' => esc_html__( 'The rules for the tournament.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'visibility'      => array(
					'description' => esc_html__( 'Indicates whether the tournament is visible to non-admins.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'visible', 'hidden' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'visible',
				),
				'status'          => array(
					'description' => esc_html__( 'Indicates whether competitors may register, check in, and manage match reports.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'created', 'open', 'in_progress', 'complete' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'open',
				),
				'link'            => array(
					'description' => esc_html__( 'URL to the tournament.' ),
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

new Tournament();
