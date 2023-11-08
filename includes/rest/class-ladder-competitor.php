<?php
/**
 * Manages Tournamatch REST endpoint for ladder competitors.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\Can_Promote_Ladder_Competitor;
use Tournamatch\Rules\One_Competitor_Per_Ladder;
use Tournamatch\Rules\Requires_Minimum_Members;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for ladder competitors.
 *
 * @since      3.11.0
 * @since      3.23.0 Updated to use WordPress API class.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Ladder_Competitor extends Controller {

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
			'/ladder-competitors/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'ladder_id' => array(
							'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'player_id' => array(
							'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'team_id'   => array(
							'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
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

		register_rest_route(
			$this->namespace,
			'/ladder-competitors/(?P<id>\d+)/promote',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'promote' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_tournamatch' );
				},
				'args'                => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
						'required'    => true,
						'type'        => 'integer',
						'minimum'     => 1,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/ladder-competitors/(?P<id>\d+)',
			array(
				'args' => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the ladder competitor.' ),
						'required'    => true,
						'type'        => 'integer',
						'minimum'     => 1,
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete' ),
					'permission_callback' => function ( \WP_REST_Request $request ) {
						global $wpdb;

						if ( $request->has_param( 'admin' ) && current_user_can( 'manage_tournamatch' ) ) {
							return true;
						} else {
							if ( '1' === trn_get_option( 'can_leave_ladder' ) ) {
								$competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `le`.`competitor_id`, `l`.`competitor_type` FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `le`.`ladder_id` = `l`.`ladder_id` WHERE `le`.`ladder_entry_id` = %d", $request->get_param( 'id' ) ) );
								if ( 'players' === $competitor->competitor_type ) {
									return ( intval( $competitor->competitor_id ) === get_current_user_id() );
								} else {
									$team_owner = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `team_rank_id` = 1", $competitor->competitor_id ) );

									return ( intval( $team_owner->user_id ) === get_current_user_id() );
								}
							}

							return false;
						}
					},
				),
			)
		);
	}

	/**
	 * Check if a given request has access to create a ladder competitor.
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
	 * Creates a single ladder competitor item.
	 *
	 * @since 3.28.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['ladder_id'] ) );
		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		if ( $request->has_param( 'admin' ) && current_user_can( 'manage_tournamatch' ) ) {
			$rules = array();
		} else {
			if ( ! trn_is_user_or_team_owner( $request['competitor_id'], $request['competitor_type'] ) ) {
				return new \WP_Error( 'rest_custom_error', esc_html__( 'Insufficient privileges.', 'tournamatch' ), array( 'status' => 403 ) );
			}

			$rules = array(
				new One_Competitor_Per_Ladder( $ladder->ladder_id, get_current_user_id() ),
			);

			$enforce_team_minimum = trn_get_option( 'enforce_team_minimum' );
			if ( ( 1 === (int) $enforce_team_minimum ) && ( 'teams' === $ladder->competitor_type ) ) {
				$rules[] = new Requires_Minimum_Members( $request['competitor_id'], $request['ladder_id'], 'ladder' );
			}
		}

		$rules = apply_filters( 'trn_rest_create_ladder_competitor_rules', $rules, $request );

		$this->verify_business_rules( $rules );

		$prepared_post = (array) $this->prepare_item_for_database( $request );

		if ( 0 < count( $prepared_post ) ) {
			$wpdb->insert( $wpdb->prefix . 'trn_ladders_entries', $prepared_post );
		}

		$competitor = $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  `le1`.*,
  (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`) AS `games_played`, 
  CASE 
    WHEN (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`) = 0 THEN '0.000' 
    ELSE FORMAT((`le1`.`wins` / (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`)), 3) 
    END AS `win_percent`, 
  (UNIX_TIMESTAMP() - `time`) AS `idle_unix_timestamp`,
  (SELECT COUNT(*) + 1 FROM `{$wpdb->prefix}trn_ladders_entries` AS `le2` WHERE `le2`.`ladder_id` = `le1`.`ladder_id` AND `le2`.`points` > `le1`.`points`) AS `rank`   
FROM `{$wpdb->prefix}trn_ladders_entries` AS `le1`
WHERE `le1`.`ladder_entry_id` = %d",
				$wpdb->insert_id
			)
		);

		/**
		 * Fires when a ladder competitor has been created (a ladder registration).
		 */
		do_action( 'trn_rest_ladder_competitor_created', $competitor );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $competitor, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Check if a given request has access to get a collection of ladder competitors.
	 *
	 * @since 3.23.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}


	/**
	 * Retrieves a collection of ladder competitors.
	 *
	 * @since 3.23.0
	 * @since 3.25.0 Added support for player and team id filters.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $wpdb;

		$ladder_id = isset( $request['ladder_id'] ) ? intval( $request['ladder_id'] ) : null;
		$player_id = isset( $request['player_id'] ) ? intval( $request['player_id'] ) : null;
		$team_id   = isset( $request['team_id'] ) ? intval( $request['team_id'] ) : null;

		$total_data = "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` WHERE 1 = 1 ";
		if ( ! is_null( $ladder_id ) ) {
			$total_data .= $wpdb->prepare( 'AND `ladder_id` = %d ', $ladder_id );
		}
		if ( ! is_null( $player_id ) ) {
			$total_data .= $wpdb->prepare( "AND ((`competitor_type` = %s AND `competitor_id` = %d) OR (`competitor_type` = %s AND `competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d))) ", 'players', $player_id, 'teams', $player_id );
		}
		if ( ! is_null( $team_id ) ) {
			$total_data .= $wpdb->prepare( 'AND `competitor_type` = %s AND `competitor_id` = %d ', 'teams', $team_id );
		}

		$total_data = $wpdb->get_var( $total_data ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		/*
		 * CAST goals_for and goals_against before math because both are unsigned. Functions using unsigned arguments
		 * return unsigned results which breaks for expected negative value in goals_delta.
		 */
		$sql = "
SELECT 
  `le1`.*, 
  (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`) AS `games_played`, 
  CASE 
    WHEN (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`) = 0 THEN '0.000' 
    ELSE FORMAT((`le1`.`wins` / (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`)), 3) 
    END AS `win_percent`, 
  (UNIX_TIMESTAMP() - `time`) AS `idle_unix_timestamp`,
  CASE `le1`.`competitor_type`
    WHEN 'players' THEN `p`.`display_name`
    ELSE `t`.`name`
    END AS `name`,
  (SELECT COUNT(*) + 1 FROM `{$wpdb->prefix}trn_ladders_entries` AS `le2` WHERE `le2`.`ladder_id` = `le1`.`ladder_id` AND `le2`.`points` > `le1`.`points`) AS `rank`     
FROM `{$wpdb->prefix}trn_ladders_entries` AS `le1`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `le1`.`ladder_id`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p` ON `le1`.`competitor_id` = `p`.`user_id` AND `le1`.`competitor_type` = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t` ON `le1`.`competitor_id` = `t`.`team_id` AND `le1`.`competitor_type` = 'teams'
WHERE 1 = 1 ";

		if ( ! is_null( $ladder_id ) ) {
			$sql .= $wpdb->prepare( 'AND `le1`.`ladder_id` = %d ', $ladder_id );
		}
		if ( ! is_null( $player_id ) ) {
			$sql .= $wpdb->prepare( "AND ((`le1`.`competitor_type` = %s AND `competitor_id` = %d) OR (`le1`.`competitor_type` = %s AND `competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)))", 'players', $player_id, 'teams', $player_id );
		}
		if ( ! is_null( $team_id ) ) {
			$sql .= $wpdb->prepare( 'AND `le1`.`competitor_type` = %s AND `competitor_id` = %d ', 'teams', $team_id );
		}

		if ( ! empty( $request['search'] ) ) {

			// Rating is ladder mode 2. Position is ladder mode 3. Points is ladder mode 1.
			$sql .= $wpdb->prepare( ' AND (`points` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `p`.`display_name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `t`.`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `le1`.`wins` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `le1`.`losses` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `le1`.`draws` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `streak` LIKE %s)', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
		}

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filtered = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns = array(
				'name'         => 'name',
				'points'       => 'points',
				'wins'         => 'wins',
				'losses'       => 'losses',
				'draws'        => 'draws',
				'streak'       => 'streak',
				'win_percent'  => 'win_percent',
				'idle'         => 'idle_unix_timestamp',
				'games_played' => 'games_played',
			);

			/**
			 * Filters an array of orderable columns of ladder competitors.
			 *
			 * @since 4.3.0
			 *
			 * @param array $columns An array of columns.
			 */
			$columns = apply_filters( 'trn_filter_ladder_competitor_orderable_columns', $columns );

			$order_by = explode( '.', $request['orderby'] );

			if ( ( 2 === count( $order_by ) && in_array( $order_by[0], array_keys( $columns ), true ) ) ) {
				$direction = ( 'desc' === $order_by[1] ) ? 'desc' : 'asc';
				$column    = $columns[ $order_by[0] ];

				$sql .= " ORDER BY `$column` $direction";
			}
		} else {
			$sql .= ' ORDER BY `le1`.`joined_date` DESC';
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
	 * Check if a given request has access to update the ladder competitor.
	 *
	 * @since 3.23.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single ladder competitor.
	 *
	 * @since 3.23.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$ladder_entry_id = $request->get_param( 'id' );
		$competitor      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `ladder_entry_id` = %d", $ladder_entry_id ) );
		if ( ! $competitor ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder competitor does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$prepared_post = (array) $this->prepare_item_for_database( $request );

		if ( 0 < count( $prepared_post ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_ladders_entries', $prepared_post, array( 'ladder_entry_id' => $ladder_entry_id ) );
		}

		$competitor = $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  `le1`.*,
  (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`) AS `games_played`, 
  CASE 
    WHEN (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`) = 0 THEN '0.000' 
    ELSE FORMAT((`le1`.`wins` / (`le1`.`wins` + `le1`.`losses` + `le1`.`draws`)), 3) 
    END AS `win_percent`, 
  (UNIX_TIMESTAMP() - `time`) AS `idle_unix_timestamp`,
  (SELECT COUNT(*) + 1 FROM `{$wpdb->prefix}trn_ladders_entries` AS `le2` WHERE `le2`.`ladder_id` = `le1`.`ladder_id` AND `le2`.`points` > `le1`.`points`) AS `rank`   
FROM `{$wpdb->prefix}trn_ladders_entries` AS `le1`
WHERE `le1`.`ladder_entry_id` = %d",
				$ladder_entry_id
			)
		);

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $competitor, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Handles promoting a competitor on the given ladder.
	 *
	 * @since 3.28.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function promote( \WP_REST_Request $request ) {
		global $wpdb;

		$params = $request->get_params();
		$id     = $params['id'];

		// Verify business rules.
		$this->verify_business_rules(
			array(
				new Can_Promote_Ladder_Competitor( $id ),
			)
		);

		$up_row   = $wpdb->get_row( $wpdb->prepare( "SELECT ladder_entry_id AS id, position AS position, ladder_id AS ladder_id FROM `{$wpdb->prefix}trn_ladders_entries` WHERE ladder_entry_id = %d", $id ) );
		$down_row = $wpdb->get_row( $wpdb->prepare( "SELECT ladder_entry_id AS id, position AS position FROM `{$wpdb->prefix}trn_ladders_entries` WHERE ladder_id = %d AND position = %d LIMIT 1", $up_row->ladder_id, ( $up_row->position - 1 ) ) );

		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_ladders_entries` SET position = %d WHERE ladder_entry_id = %d", ( $up_row->position - 1 ), $up_row->id ) );
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_ladders_entries` SET position = %d WHERE ladder_entry_id = %d", ( $down_row->position + 1 ), $down_row->id ) );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The ladder competitor was promoted.', 'tournamatch' ),
				'data'    => array(
					'status' => 303,
				),
			),
			303
		);
	}

	/**
	 * Handles deleting a ladder competitor.
	 *
	 * @since 3.11.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete( \WP_REST_Request $request ) {
		global $wpdb;

		$params = $request->get_params();
		$id     = $params['id'];

		$competitor = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` WHERE `le`.`ladder_entry_id` = %d", $id ) );

		// Delete entry, challenges, pending match results, and petitions.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `ladder_entry_id` = %d LIMIT 1", $id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_challenges` WHERE `ladder_id` = %d AND (`challenger_id` = %d OR `challengee_id` = %d) AND `accepted_state` = %s", $competitor->ladder_id, $competitor->competitor_id, $competitor->competitor_id, 'pending' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_matches` WHERE `competition_id` = %d AND `competition_type` = %s AND (`one_competitor_id` = %d OR `two_competitor_id` = %d) AND `match_status` != %s", $competitor->ladder_id, 'ladders', $competitor->competitor_id, $competitor->competitor_id, 'confirmed' ) );

		/**
		 * Fires when a ladder competitor has been removed (a tournament registration).
		 */
		do_action( 'trn_rest_ladder_competitor_deleted', $competitor );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The ladder competitor was removed.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 3.23.0
	 * @since 3.25.0 Added ladder relation.
	 *
	 * @param Object $competitor Ladder competitor object.
	 *
	 * @return array Links for the given ladder competitor.
	 */
	protected function prepare_links( $competitor ) {
		$base = "{$this->namespace}/ladder-competitors";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $competitor->ladder_entry_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		$links['competitor'] = array(
			'href'       => rest_url( "{$this->namespace}/{$competitor->competitor_type}/{$competitor->competitor_id}" ),
			'embeddable' => true,
		);

		$links['ladder'] = array(
			'href'       => rest_url( "{$this->namespace}/ladders/{$competitor->ladder_id}" ),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Retrieves the ladder competitor schema, conforming to JSON Schema.
	 *
	 * @since 3.23.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$properties = array(
			'ladder_entry_id' => array(
				'description' => esc_html__( 'The id for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'ladder_id'       => array(
				'description' => esc_html__( 'The ladder id for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'required'    => true,
			),
			'competitor_id'   => array(
				'description' => esc_html__( 'The competitor id for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'required'    => true,
			),
			'competitor_type' => array(
				'description' => esc_html__( 'The competitor type for the ladder competitor.', 'tournamatch' ),
				'type'        => 'string',
				'enum'        => array( 'players', 'teams' ),
				'context'     => array( 'view', 'edit', 'embed' ),
				'required'    => true,
			),
			'joined_date'     => array(
				'description' => esc_html__( 'The datetime the ladder competitor joined the ladder.', 'tournamatch' ),
				'type'        => 'object',
				'trn-subtype' => 'datetime',
				'context'     => array( 'view', 'edit', 'embed' ),
				'properties'  => array(
					'raw'      => array(
						'description' => esc_html__( 'The datetime the ladder competitor joined the ladder, as it exists in the database.', 'tournamatch' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'view', 'edit', 'embed' ),
					),
					'rendered' => array(
						'description' => esc_html__( 'The datetime the ladder competitor joined the ladder, transformed for display.', 'tournamatch' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
				),
			),
			'points'          => array(
				'description' => esc_html__( 'The number of points earned according to Points ranking for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'default'     => 0,
			),
			'rank'            => array(
				'description' => esc_html__( 'The numeric rank for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'wins'            => array(
				'description' => esc_html__( 'The number of wins for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'default'     => 0,
			),
			'losses'          => array(
				'description' => esc_html__( 'The number of losses for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'default'     => 0,
			),
			'draws'           => array(
				'description' => esc_html__( 'The number of draws for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'default'     => 0,
			),
			'games_played'    => array(
				'description' => esc_html__( 'The total number of games played for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'win_percent'     => array(
				'description' => esc_html__( 'The win percentage for the ladder competitor.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'streak'          => array(
				'description' => esc_html__( 'The number of consecutive wins for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'default'     => 0,
			),
			'best_streak'     => array(
				'description' => esc_html__( 'The most number of consecutive wins ever for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'default'     => 0,
			),
			'worst_streak'    => array(
				'description' => esc_html__( 'The the worst number of consecutive losses ever for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'default'     => 0,
			),
			'days_idle'       => array(
				'description' => esc_html__( 'The total number of days since the last match for the ladder competitor.', 'tournamatch' ),
				'type'        => 'integer',
				'trn-subtype' => 'callable',
				'trn-get'     => function( $competitor ) {
					if ( 0 < strlen( $competitor->time ) ) {
						$date_time_1 = new \DateTime( '@0' );
						$date_time_2 = new \DateTime( '@' . $competitor->idle_unix_timestamp );

						return (int) $date_time_1->diff( $date_time_2 )->format( '%a' );
					} else {
						return '';
					}
				},
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
		);

		if ( current_user_can( 'manage_tournamatch' ) ) {
			$properties['edit_link'] = array(
				'description' => esc_html__( 'URL to edit the competitor.' ),
				'type'        => 'string',
				'trn-subtype' => 'callable',
				'trn-get'     => function( $competitor ) {
					return trn_route( 'ladder-competitors.single.edit', array( 'id' => $competitor->ladder_entry_id ) );
				},
				'format'      => 'uri',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			);
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ladder-competitors',
			'type'       => 'object',
			'properties' => $properties,
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Ladder_Competitor();
