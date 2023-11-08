<?php
/**
 * Manages Tournamatch REST endpoint for tournament competitors.
 *
 * @link  https://www.tournamatch.com
 * @since 3.25.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\One_Competitor_Per_Tournament;
use Tournamatch\Rules\Requires_Minimum_Members;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for tournament competitors.
 *
 * @since 3.25.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Tournament_Competitor extends Controller {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.22.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.25.0
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/tournament-competitors/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'tournament_id' => array(
							'description' => esc_html__( 'Unique identifier for the tournament competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'player_id'     => array(
							'description' => esc_html__( 'Unique identifier for the tournament competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'team_id'       => array(
							'description' => esc_html__( 'Unique identifier for the tournament competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
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
	}

	/**
	 * Check if a given request has access to register a tournament competitor.
	 *
	 * @since 3.28.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return is_user_logged_in();
	}

	/**
	 * Creates a single tournament registration item.
	 *
	 * @since 3.28.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $request['tournament_id'] ) );
		if ( ! $tournament ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Tournament does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$rules = array(
			new One_Competitor_Per_Tournament( $request['tournament_id'], get_current_user_id() ),
		);

		if ( ( '1' === trn_get_option( 'enforce_team_minimum' ) ) && ( 'teams' === $tournament->competitor_type ) ) {
			$rules[] = new Requires_Minimum_Members( $request['competitor_id'], $request['tournament_id'], 'tournament' );
		}

		$rules = apply_filters( 'trn_rest_create_tournament_competitor_rules', $rules, $request );

		$this->verify_business_rules( $rules );

		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}trn_tournaments_entries (`tournament_entry_id`, `tournament_id`, `competitor_id`, `competitor_type`, `joined_date`, `seed`) VALUES (NULL, %d, %d, %s, UTC_TIMESTAMP(), NULL)", $request['tournament_id'], $request['competitor_id'], $request['competitor_type'] ) );

		$competitor = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_entry_id` = %d", $wpdb->insert_id ) );

		/**
		 * Fires when a tournament competitor has been created (a tournament registration).
		 */
		do_action( 'trn_rest_tournament_competitor_created', $competitor );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $competitor, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
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

		$total_data = "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE 1 = 1 ";
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
	 * Prepares links for the request.
	 *
	 * @since 3.25.0
	 *
	 * @param Object $competitor Tournament competitor object.
	 *
	 * @return array Links for the given ladder competitor.
	 */
	protected function prepare_links( $competitor ) {
		$base = "{$this->namespace}/tournament-competitors";

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
	 * Retrieves the tournament competitor schema, conforming to JSON Schema.
	 *
	 * @since 3.25.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'tournament-competitor',
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
					'trn-subtype' => 'datetime',
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
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Tournament_Competitor();
