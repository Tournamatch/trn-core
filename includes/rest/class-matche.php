<?php
/**
 * Manages Tournamatch REST endpoint for matches.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\Must_Participate_On_Ladder;
use Tournamatch\Rules\Must_Report_Own_Match;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for matches.
 *
 * @since      3.11.0
 * @since      3.19.0 Updated to use WordPress API class.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Matche extends Controller {

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
			'/matches/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
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
			'/matches/(?P<id>\d+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the object.' ),
						'type'        => 'integer',
						'required'    => true,
						'minimum'     => 1,
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete' ),
					'permission_callback' => array( $this, 'can_delete_match' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/matches/(?P<id>\d+)/clear',
			array(
				'args'   => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the object.' ),
						'type'        => 'integer',
						'required'    => true,
						'minimum'     => 1,
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'clear_match' ),
					'permission_callback' => array( $this, 'clear_match_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Evaluates whether a user has permission to retrieve a single match item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves a single match item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wpdb;

		$competition_id   = isset( $request['competition_id'] ) ? intval( $request['competition_id'] ) : null;
		$competition_type = isset( $request['competition_type'] ) ? sanitize_text_field( $request['competition_type'] ) : null;
		$competition_type = in_array(
			$competition_type,
			array(
				'ladders',
				'tournaments',
			),
			true
		) ? $competition_type : null;

		$competitor_id   = isset( $request['competitor_id'] ) ? intval( $request['competitor_id'] ) : null;
		$competitor_type = isset( $request['competitor_type'] ) ? sanitize_text_field( $request['competitor_type'] ) : null;
		$competitor_type = in_array( $competitor_type, array( 'players', 'teams' ), true ) ? $competitor_type : null;

		// Competition id requires competition type.
		if ( ! is_null( $competition_id ) && is_null( $competition_type ) ) {
			$competition_id = null;
		}

		// Competitor id requires competitor type.
		if ( ! is_null( $competitor_id ) && is_null( $competitor_type ) ) {
			$competitor_id = null;
		}

		$total_data = $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` WHERE `match_status` != %s", 'tournament_bye' );
		if ( ! is_null( $competition_type ) ) {
			$total_data .= $wpdb->prepare( ' AND `competition_type` = %s', $competition_type );
			if ( ! is_null( $competition_id ) ) {
				$total_data .= $wpdb->prepare( ' AND `competition_id` = %d', $competition_id );
			}
		}
		if ( ! is_null( $competitor_id ) ) {
			if ( 'players' === $competitor_type ) {
				$total_data .= $wpdb->prepare( " AND (((`one_competitor_id` = %d OR `two_competitor_id` = %d) AND `one_competitor_type` = %s) OR (`one_competitor_type` = %s AND ((`one_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)) OR (`two_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)))))", $competitor_id, $competitor_id, 'players', 'teams', $competitor_id, $competitor_id );
			} else {
				$total_data .= $wpdb->prepare( ' AND (`one_competitor_id` = %d OR `two_competitor_id` = %d) AND `one_competitor_type` = %s', $competitor_id, $competitor_id, 'teams' );
			}
		}
		$total_data = $wpdb->get_var( $total_data ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$sql = $wpdb->prepare(
			"
SELECT 
  `m`.*, 
  CASE `m`.`competition_type`
    WHEN 'ladders' THEN `l`.`name`
    ELSE `t`.`name`
    END `name`,  
  CASE `m`.`competition_type`
    WHEN 'ladders' THEN `l`.`competitor_type`
    ELSE `t`.`competitor_type`
    END `competitor_type`
FROM `{$wpdb->prefix}trn_matches` AS `m`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `m`.`competition_id` AND `m`.`competition_type` = 'ladders'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `t`.`tournament_id` = `m`.`competition_id` AND `m`.`competition_type` = 'tournaments'
WHERE  `m`.`match_status` != %s",
			'tournament_bye'
		);

		if ( ! is_null( $competition_type ) ) {
			$sql .= $wpdb->prepare( ' AND `m`.`competition_type` = %s', $competition_type );
			if ( ! is_null( $competition_id ) ) {
				$sql .= $wpdb->prepare( ' AND `m`.`competition_id` = %d', $competition_id );
			}
		}

		if ( ! is_null( $competitor_id ) ) {
			if ( 'players' === $competitor_type ) {
				$sql .= $wpdb->prepare( " AND (((`one_competitor_id` = %d OR `two_competitor_id` = %d) AND `one_competitor_type` = %s) OR (`one_competitor_type` = %s AND ((`one_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)) OR (`two_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)))))", $competitor_id, $competitor_id, 'players', 'teams', $competitor_id, $competitor_id );
			} else {
				$sql .= $wpdb->prepare( ' AND (`one_competitor_id` = %d OR `two_competitor_id` = %d) AND `one_competitor_type` = %s', $competitor_id, $competitor_id, 'teams' );
			}
		}

		if ( ! empty( $request['search'] ) ) {
			$sql .= $wpdb->prepare( ' AND (`m`.`competition_type` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `l`.`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `t`.`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `m`.`match_date` LIKE %s)', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
		}

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filter = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns  = array( 'competition_type', 'name', 'match_date' );
			$order_by = explode( '.', $request['orderby'] );

			if ( ( 2 === count( $order_by ) && in_array( $order_by[0], $columns, true ) ) ) {
				$direction = ( 'desc' === $order_by[1] ) ? 'desc' : 'asc';

				$sql .= " ORDER BY `$order_by[0]` $direction";
			}
		}

		if ( isset( $request['per_page'] ) && ( '-1' !== $request['per_page'] ) ) {
			$length = $request['per_page'] ?: 10;
			$start  = $request['page'] ? ( $request['page'] * $length ) : 0;
			$sql   .= $wpdb->prepare( ' LIMIT %d, %d', $start, $length );
		}

		$matches = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$items = array();

		foreach ( $matches as $match ) {
			$data    = $this->prepare_item_for_response( $match, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $items );

		$response->header( 'X-WP-Total', intval( $total_data ) );
		$response->header( 'X-WP-TotalPages', 1 );
		$response->header( 'TRN-Draw', intval( $request['draw'] ) );
		$response->header( 'TRN-Filtered', intval( $total_filter ) );

		return $response;
	}

	/**
	 * Check if a given request has access to create a match.
	 *
	 * @since 3.28.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		global $wpdb;

		// Can only create ladder matches via API at this time.
		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['competition_id'] ) );
		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		if ( 'players' === $ladder->competitor_type ) {
			return is_user_logged_in();
		} else {
			$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `tm`.`team_id` FROM `{$wpdb->prefix}trn_teams_members` AS `tm` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `le`.`competitor_id` = `tm`.`team_id` WHERE `tm`.`user_id` = %d AND `le`.`ladder_id` = %d", get_current_user_id(), $request['competition_id'] ) );

			return ( 0 < count( $teams ) );
		}
	}

	/**
	 * Creates a single match item.
	 *
	 * @since 3.28.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		// Can only create ladder matches via API at this time.
		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['competition_id'] ) );
		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		// Business rules.
		$rules = array(
			new Must_Participate_On_Ladder( $request['competition_id'], $request['one_competitor_id'] ),
			new Must_Participate_On_Ladder( $request['competition_id'], $request['two_competitor_id'] ),
		);

		$this->verify_business_rules( $rules );

		$competitor_type = $ladder->competitor_type;

		$ip           = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$time         = time();
		$confirm_hash = md5(
			$ladder->ladder_id
			. $request->get_param( 'one_competitor_id' )
			. $ip
			. $request->get_param( 'one_result' )
			. $request->get_param( 'one_comment' )
			. $request->get_param( 'two_competitor_id' )
			. $time
		);
		$confirm_hash = '1a' . substr( $confirm_hash, 2 );

		$data = array(
			'competition_id'      => $request->get_param( 'competition_id' ),
			'competition_type'    => 'ladders',
			'spot'                => null,
			'one_competitor_id'   => $request->get_param( 'one_competitor_id' ),
			'one_competitor_type' => $competitor_type,
			'one_ip'              => $ip,
			'one_result'          => $request->get_param( 'one_result' ),
			'one_comment'         => $request->get_param( 'one_comment' ),
			'two_competitor_id'   => $request->get_param( 'two_competitor_id' ),
			'two_competitor_type' => $competitor_type,
			'two_ip'              => '',
			'two_result'          => '',
			'two_comment'         => '',
			'match_date'          => $wpdb->get_var( 'SELECT UTC_TIMESTAMP()' ),
			'match_status'        => 'reported',
			'confirm_hash'        => $confirm_hash,
		);

		$wpdb->insert( $wpdb->prefix . 'trn_matches', $data );

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $wpdb->insert_id ) );

		if ( ! is_null( $match ) ) {
			if ( isset( $request['challenge_id'] ) && ( 0 < $request['challenge_id'] ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_challenges` SET `accepted_state` = %s, `match_id` = %d WHERE `challenge_id` = %d", 'reported', $match->match_id, $request['challenge_id'] ) );
			}

			if ( 'players' === $ladder->competitor_type ) {
				$reporter_name                          = $wpdb->get_var( $wpdb->prepare( "SELECT `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` WHERE `p`.`user_id` = %d", $request->get_param( 'one_competitor_id' ) ), ARRAY_N );
				list( $opponent_name, $opponent_email ) = $wpdb->get_row( $wpdb->prepare( "SELECT `p`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $request->get_param( 'two_competitor_id' ) ), ARRAY_N );
				$reporter_link                          = trn_route( 'players.single', [ 'id' => $request->get_param( 'one_competitor_id' ) ] );
			} else {
				$reporter_name                          = $wpdb->get_var( $wpdb->prepare( "SELECT `pp`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `pp` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `pp`.`user_id` WHERE `tm`.`team_id` = %d AND `tm`.`team_rank_id` = %d", $request->get_param( 'one_competitor_id' ), 1 ), ARRAY_N );
				list( $opponent_name, $opponent_email ) = $wpdb->get_row( $wpdb->prepare( "SELECT `pp`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `pp` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `pp`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `pp`.`user_id` WHERE `tm`.`team_id` = %d AND `tm`.`team_rank_id` = %d", $request->get_param( 'two_competitor_id' ), 1 ), ARRAY_N );
				$reporter_link                          = trn_route( 'teams.single', [ 'id' => $request->get_param( 'one_competitor_id' ) ] );
			}

			if ( 'won' === $request->get_param( 'one_result' ) ) {
				$result = esc_html__( 'you lost', 'tournamatch' );
			} elseif ( 'lost' === $request->get_param( 'one_result' ) ) {
				$result = esc_html__( 'you won', 'tournamatch' );
			} else {
				$result = esc_html__( 'a draw', 'tournamatch' );
			}

			$data = [
				'opponent_link'    => $reporter_link,
				'opponent'         => $reporter_name,
				'competition_link' => trn_route( 'ladders.single', [ 'id' => $ladder->ladder_id ] ),
				'competition_name' => $ladder->name,
				'results_link'     => trn_route( 'report.page' ),
				'result'           => $result,
				'competition_type' => 'ladder',
				'confirm_link'     => trn_route(
					'confirm-email-result',
					[
						'match_id'         => $match->match_id,
						'reference_id'     => $match->confirm_hash,
						'competition_type' => 'ladder',
					]
				),
			];

			do_action(
				'trn_notify_match_reported',
				[
					'email' => $opponent_email,
					'name'  => $opponent_name,
				],
				esc_html__( 'Confirm Ladder Result', 'tournamatch' ),
				$data
			);
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $match, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Check if a given request has access to clear a match.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function clear_match_permissions_check( $request ) {
		global $wpdb;

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $request['id'] ) );
		if ( ! $match ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Match does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Clears a single match item.
	 *
	 * @since 3.28.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function clear_match( $request ) {
		global $wpdb;

		$update = array(
			'one_result'   => '',
			'one_ip'       => '',
			'one_comment'  => '',
			'two_result'   => '',
			'two_ip'       => '',
			'two_comment'  => '',
			'match_status' => 'scheduled',
		);

		$wpdb->update( $wpdb->prefix . 'trn_matches', $update, array( 'match_id' => $request['id'] ) );

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $request['id'] ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $match, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Check if a given request has access to update a match.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		global $wpdb;

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $request['id'] ) );
		if ( ! $match ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Match does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		if ( current_user_can( 'manage_tournamatch' ) ) {
			return true;
		} elseif ( 'scheduled' === $match->match_status ) {
			if ( 'players' === $match->one_competitor_type ) {
				return in_array(
					(string) get_current_user_id(),
					array(
						$match->one_competitor_id,
						$match->two_competitor_id,
					),
					true
				);
			} else {
				$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d", get_current_user_id() ) );
				$teams = array_column( $teams, 'team_id' );

				return in_array( $match->one_competitor_id, $teams, true ) || in_array( $match->two_competitor_id, $teams, true );
			}
		} elseif ( 'reported' === $match->match_status ) {

			if ( 0 < strlen( $match->one_result ) ) {
				$to_confirm = 'two_competitor_id';
			} else {
				$to_confirm = 'one_competitor_id';
			}

			if ( in_array( 'players', array( $match->one_competitor_type, $match->two_competitor_type ), true ) ) {
				return ( get_current_user_id() === intval( $match->$to_confirm ) );
			} else {
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `user_id` = %d", $match->$to_confirm, get_current_user_id() ) );

				return ( '1' === $count );
			}
		} else {
			return false;
		}
	}

	/**
	 * Updates a single match item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $request['id'] ) );

		if ( 'scheduled' === $match->match_status ) {

			$this->verify_business_rules(
				array(
					new Must_Report_Own_Match( $match, $request ),
				)
			);

			$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			if ( isset( $request['one_result'] ) ) {
				$update = array(
					'one_result'  => $request['one_result'],
					'one_comment' => $request['one_comment'],
					'one_ip'      => $ip_address,
				);

				$competitor_id   = $match->one_competitor_id;
				$opponent_id     = $match->two_competitor_id;
				$reported_result = $request['one_result'];
			} else {
				$update = array(
					'two_result'  => $request['two_result'],
					'two_comment' => $request['two_comment'],
					'two_ip'      => $ip_address,
				);

				$competitor_id   = $match->two_competitor_id;
				$opponent_id     = $match->one_competitor_id;
				$reported_result = $request['two_result'];
			}

			$update['confirm_hash'] = md5( $match->match_id . wp_rand() );
			$update['confirm_hash'] = '1a' . substr( $update['confirm_hash'], 2 );
			$update['match_status'] = 'reported';

			$wpdb->update( $wpdb->prefix . 'trn_matches', $update, array( 'match_id' => $request['id'] ) );

			$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match->match_id ) );

			// Send email.
			if ( 'players' === $match->one_competitor_type ) {
				list( $opponent_name, $opponent_email ) = $wpdb->get_row( $wpdb->prepare( "SELECT `p`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $opponent_id ), ARRAY_N );
				$reporter_link                          = trn_route( 'players.single', [ 'id' => $competitor_id ] );
				$competitor_name                        = $wpdb->get_var( $wpdb->prepare( "SELECT `display_name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $competitor_id ) );
			} else {
				list( $opponent_name, $opponent_email ) = $wpdb->get_row( $wpdb->prepare( "SELECT `pp`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `pp` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `pp`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `pp`.`user_id` WHERE `tm`.`team_id` = %d AND `tm`.`team_rank_id` = %d", $opponent_id, 1 ), ARRAY_N );
				$reporter_link                          = trn_route( 'teams.single', [ 'id' => $competitor_id ] );
				$competitor_name                        = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $competitor_id ) );
			}

			if ( 'won' === $reported_result ) {
				$result = esc_html__( 'you lost', 'tournamatch' );
			} elseif ( 'lost' === $reported_result ) {
				$result = esc_html__( 'you won', 'tournamatch' );
			} else {
				$result = esc_html__( 'a draw', 'tournamatch' );
			}

			if ( 'ladders' === $match->competition_type ) {
				$competition      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $match->competition_id ), ARRAY_A );
				$competition_link = trn_route( 'ladders.single', [ 'id' => $match->competition_id ] );
				$competition_type = 'ladder';
				$subject          = esc_html__( 'Confirm Ladder Result', 'tournamatch' );
			} else {
				$competition      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $match->competition_id ), ARRAY_A );
				$competition_link = trn_route( 'tournaments.single', [ 'id' => $match->competition_id ] );
				$competition_type = 'tournament';
				$subject          = esc_html__( 'Confirm Tournament Result', 'tournamatch' );
			}

			$data = [
				'opponent_link'    => $reporter_link,
				'opponent'         => $competitor_name,
				'competition_link' => $competition_link,
				'competition_name' => $competition['name'],
				'results_link'     => trn_route( 'report.page' ),
				'result'           => $result,
				'competition_type' => $competition_type,
				'confirm_link'     => trn_route(
					'confirm-email-result',
					[
						'match_id'         => $match->match_id,
						'reference_id'     => $match->confirm_hash,
						'competition_type' => $competition_type,
					]
				),
			];

			do_action(
				'trn_notify_match_reported',
				[
					'email' => $opponent_email,
					'name'  => $opponent_name,
				],
				$subject,
				$data
			);
		} else {
			// determine the id of the winner.
			if ( ( 'won' === $match->one_result ) || ( 'lost' === $match->two_result ) ) {
				$winner_id = $match->one_competitor_id;
				$loser_id  = $match->two_competitor_id;
			} elseif ( ( 'draw' === $match->one_result ) || ( 'draw' === $match->two_result ) ) {
				$winner_id = $match->one_competitor_id;
				$loser_id  = $match->two_competitor_id;
			} else {
				$winner_id = $match->two_competitor_id;
				$loser_id  = $match->one_competitor_id;
			}

			// determine the result of the winner.
			if ( ( 'won' === $match->one_result ) || ( 'won' === $match->two_result ) ) {
				$confirm_result = 'lost';
			} elseif ( ( 'draw' === $match->one_result ) || ( 'draw' === $match->two_result ) ) {
				$confirm_result = 'draw';
			} else {
				$confirm_result = 'won';
			}

			$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			if ( 0 === strlen( $match->one_result ) ) {
				$update = array(
					'one_result'  => $confirm_result,
					'one_comment' => $request['one_comment'],
					'one_ip'      => $ip_address,
				);
			} else {
				$update = array(
					'two_result'  => $confirm_result,
					'two_comment' => $request['two_comment'],
					'two_ip'      => $ip_address,
				);
			}

			$update['match_status'] = 'confirmed';

			$wpdb->update( $wpdb->prefix . 'trn_matches', $update, array( 'match_id' => $request['id'] ) );

			$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match->match_id ) );

			if ( 'ladders' === $match->competition_type ) {
				update_ladder(
					$match->competition_id,
					array(
						$match->one_competitor_id => $match->one_result,
						$match->two_competitor_id => $match->two_result,
					)
				);
			} else {
				update_tournament(
					$match->competition_id,
					array(
						'match_id'  => $match->match_id,
						'winner_id' => $winner_id,
					)
				);
			}

			// Update career results.
			if ( 'draw' === $confirm_result ) {
				update_career_draws( $winner_id, $match->one_competitor_type );
				update_career_draws( $loser_id, $match->one_competitor_type );
			} else {
				update_career_wins( $winner_id, $match->one_competitor_type );
				update_career_losses( $loser_id, $match->one_competitor_type );
			}
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $match, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Handles deleting a match.
	 *
	 * @since 3.11.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete( \WP_REST_Request $request ) {
		global $wpdb;

		$params   = $request->get_params();
		$match_id = $params['id'];
		$match    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}trn_matches WHERE match_id = %d", $match_id ) );

		if ( 'ladders' === $match->competition_type ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_matches WHERE match_id = %d LIMIT 1", $match_id ) );
		} else {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}trn_matches SET one_result = %s, one_ip = %s, two_result = %s, two_ip = %s, match_status = %s WHERE match_id = %d", '', '', '', '', 'scheduled', $match_id ) );
		}

		return new \WP_REST_Response(
			array(
				'message' => __( 'The match was deleted.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Determines whether the current user may delete a match.
	 *
	 * @since 3.11.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return bool True with permission, false otherwise.
	 */
	public function can_delete_match( \WP_REST_Request $request ) {
		global $wpdb;

		if ( current_user_can( 'manage_tournamatch' ) ) {
			return true;
		}

		$params   = $request->get_params();
		$match_id = $params['id'];

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}trn_matches WHERE match_id = %d", $match_id ) );
		if ( 'players' === $match->one_competitor_type ) {
			if ( 'ladders' === $match->competition_type ) {
				return ( get_current_user_id() === intval( $match->one_competitor_id ) );
			} else {
				return (
					( ( get_current_user_id() === intval( $match->one_competitor_id ) ) && ( strlen( $match->one_result ) > 0 ) ) ||
					( ( get_current_user_id() === intval( $match->two_competitor_id ) ) && ( strlen( $match->two_result ) > 0 ) )
					) && ( 'reported' === $match->match_status );
			}
		} else {
			$teams     = array();
			$team_rows = $wpdb->get_results( $wpdb->prepare( "SELECT team_id FROM {$wpdb->prefix}trn_teams_members WHERE user_id = %d", get_current_user_id() ) );
			foreach ( $team_rows as $team_row ) {
				$teams[] = intval( $team_row->team_id );
			}
			if ( 'ladders' === $match->competition_type ) {
				return in_array( intval( $match->one_competitor_id ), $teams, true );
			} else {
				return ( 'reported' === $match->match_status ) && ( in_array( intval( $match->one_competitor_id ), $teams, true ) || in_array( intval( $match->two_competitor_id ), $teams, true ) );
			}
		}
	}

	/**
	 * Prepares a single match item for response.
	 *
	 * @since 3.19.0
	 *
	 * @param Object           $match Match object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $match, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'match_id', $fields ) ) {
			$data['match_id'] = (int) $match->match_id;
		}

		if ( rest_is_field_included( 'competition_id', $fields ) ) {
			$data['competition_id'] = (int) $match->competition_id;
		}

		if ( rest_is_field_included( 'competition_type', $fields ) ) {
			$data['competition_type'] = $match->competition_type;
		}

		if ( rest_is_field_included( 'spot', $fields ) ) {
			$data['spot'] = (int) $match->spot;
		}

		if ( rest_is_field_included( 'one_competitor_id', $fields ) ) {
			$data['one_competitor_id'] = (int) $match->one_competitor_id;
		}

		if ( rest_is_field_included( 'one_competitor_type', $fields ) ) {
			$data['one_competitor_type'] = $match->one_competitor_type;
		}

		if ( current_user_can( 'manage_tournamatch' ) ) {
			if ( rest_is_field_included( 'one_ip', $fields ) ) {
				$data['one_ip'] = $match->one_ip;
			}
		}

		if ( rest_is_field_included( 'one_result', $fields ) ) {
			$data['one_result'] = $match->one_result;
		}

		if ( rest_is_field_included( 'one_comment', $fields ) ) {
			$data['one_comment'] = $match->one_comment;
		}

		if ( rest_is_field_included( 'two_competitor_id', $fields ) ) {
			$data['two_competitor_id'] = (int) $match->two_competitor_id;
		}

		if ( rest_is_field_included( 'two_competitor_type', $fields ) ) {
			$data['two_competitor_type'] = $match->two_competitor_type;
		}

		if ( current_user_can( 'manage_tournamatch' ) ) {
			if ( rest_is_field_included( 'two_ip', $fields ) ) {
				$data['two_ip'] = $match->two_ip;
			}
		}

		if ( rest_is_field_included( 'two_result', $fields ) ) {
			$data['two_result'] = $match->two_result;
		}

		if ( rest_is_field_included( 'two_comment', $fields ) ) {
			$data['two_comment'] = $match->two_comment;
		}

		if ( rest_is_field_included( 'match_date', $fields ) ) {
			$data['match_date'] = array(
				'raw'      => $match->match_date,
				'rendered' => ( '0000-00-00 00:00:00' === $match->match_date ) ? '&nbsp;' : date( get_option( 'date_format' ), strtotime( $match->match_date ) ),
			);
		}

		if ( rest_is_field_included( 'match_status', $fields ) ) {
			$data['match_status'] = $match->match_status;
		}

		if ( rest_is_field_included( 'match_result', $fields ) ) {
			$data['match_result'] = get_match_result_text( $match );
		}

		if ( rest_is_field_included( 'link', $fields ) ) {
			$data['link'] = trn_route( 'matches.single', array( 'id' => $match->match_id ) );
		}

		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $match );
		$response->add_links( $links );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 3.21.0
	 *
	 * @param Object $match Match object.
	 *
	 * @return array Links for the given match.
	 */
	protected function prepare_links( $match ) {
		$base = "{$this->namespace}/matches";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $match->match_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		$links['competition'] = array(
			'href'       => rest_url( "{$this->namespace}/{$match->competition_type}/{$match->competition_id}" ),
			'embeddable' => true,
		);

		$links['one_competitor'] = array(
			'href'       => rest_url( "{$this->namespace}/{$match->one_competitor_type}/{$match->one_competitor_id}" ),
			'embeddable' => true,
		);
		$links['two_competitor'] = array(
			'href'       => rest_url( "{$this->namespace}/{$match->two_competitor_type}/{$match->two_competitor_id}" ),
			'embeddable' => true,
		);

		return $links;
	}


	/**
	 * Retrieves the match schema, conforming to JSON Schema.
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
			'title'      => 'match',
			'type'       => 'object',
			'properties' => array(
				'match_id'            => array(
					'description' => esc_html__( 'The id for the match.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'competition_id'      => array(
					'description' => esc_html__( 'The id for the competition.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'competition_type'    => array(
					'description' => esc_html__( 'The type of competitor registering.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'ladders', 'tournaments' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'spot'                => array(
					'description' => esc_html__( 'Tournament bracket spot for the match. Unused for ladder matches.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'one_competitor_id'   => array(
					'description' => esc_html__( 'Competitor one id for the match.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'one_competitor_type' => array(
					'description' => esc_html__( 'Competitor one type for the match.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'players', 'teams' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'one_ip'              => array(
					'description' => esc_html__( 'Competitor one ip address for the match.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'one_result'          => array(
					'description' => esc_html__( 'Competitor one result for the match.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'won', 'lost', 'draw' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'one_comment'         => array(
					'description' => esc_html__( 'Competitor one comment for the match.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'two_competitor_id'   => array(
					'description' => esc_html__( 'Competitor two id for the match.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'two_competitor_type' => array(
					'description' => esc_html__( 'Competitor two type for the match.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'players', 'teams' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'two_ip'              => array(
					'description' => esc_html__( 'Competitor two ip address for the match.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'two_result'          => array(
					'description' => esc_html__( 'Competitor two result for the match.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'two_comment'         => array(
					'description' => esc_html__( 'Competitor two comment for the match.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'match_date'          => array(
					'description' => esc_html__( 'Date and time for the match.', 'tournamatch' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'Date and time for the match, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'Date and time for the object, transformed for display.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'match_status'        => array(
					'description' => esc_html__( 'Current status for the match.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array(
						'scheduled',
						'reported',
						'confirmed',
						'disputed',
						'tournament_bye',
						'undetermined',
					),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'scheduled',
				),
				'match_result'        => array(
					'description' => esc_html__( 'Match result.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => '',
				),
				'link'                => array(
					'description' => esc_html__( 'URL to the match.' ),
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

new Matche();
