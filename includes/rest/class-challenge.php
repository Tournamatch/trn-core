<?php
/**
 * Manages Tournamatch REST endpoint for challenges.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\Can_Create_Ladder_Challenges;
use Tournamatch\Rules\Cannot_Challenge_Self;
use Tournamatch\Rules\Direct_Challenge_Requires_Enabled;
use Tournamatch\Rules\Must_Participate_On_Ladder;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for challenges.
 *
 * @since      3.11.0
 * @since      3.21.0 Updated to use WordPress API class.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Challenge extends Controller {

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
	 * @since 3.15.0 Added accept and decline end points.
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/challenges/',
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
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/challenges/(?P<id>\d+)',
			array(
				'args' => array(
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
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete' ),
					'permission_callback' => array( $this, 'can_delete_challenge' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/challenges/(?P<id>\d+)/accept',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'accept' ),
				'permission_callback' => array( $this, 'can_accept_challenge' ),
				'args'                => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the challenge.' ),
						'type'        => 'integer',
						'required'    => true,
						'minimum'     => 1,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/challenges/(?P<id>\d+)/decline',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'decline' ),
				'permission_callback' => array( $this, 'can_decline_challenge' ),
				'args'                => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the challenge.' ),
						'type'        => 'integer',
						'required'    => true,
						'minimum'     => 1,
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to create a challenge.
	 *
	 * @since 3.20.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['ladder_id'] ) );

		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 422 ) );
		}

		if ( ! current_user_can( 'manage_tournamatch' ) ) {
			// must be a part of the ladder.
			$authorization_rule = new Can_Create_Ladder_Challenges( $request['ladder_id'], get_current_user_id() );

			return $authorization_rule->passes();
		}

		return true;
	}

	/**
	 * Creates a single challenge item.
	 *
	 * @since 3.20.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$user_id        = get_current_user_id();
		$ladder         = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['ladder_id'] ) );
		$challenge_type = ( '0' === $request['challengee_id'] ) ? 'blind' : 'direct';

		$rules = array(
			new Cannot_Challenge_Self( $request['ladder_id'], $request['challengee_id'], $user_id ),
			new Direct_Challenge_Requires_Enabled( $request['ladder_id'], $request['challengee_id'] ),
		);
		$this->verify_business_rules( $rules );

		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_challenges` (`challenge_id`, `ladder_id`, `challenge_type`, `challenger_id`, `challengee_id`, `match_time`) VALUES (NULL, %d, %s, %d, %d, %s)", $request['ladder_id'], $challenge_type, $request['challenger_id'], $request['challengee_id'], $request['match_time'] ) );

		$challenge = $wpdb->get_row( $wpdb->prepare( "SELECT `{$wpdb->prefix}trn_challenges`.*, `{$wpdb->prefix}trn_ladders`.`competitor_type` FROM `{$wpdb->prefix}trn_challenges` LEFT JOIN `{$wpdb->prefix}trn_ladders` ON `{$wpdb->prefix}trn_ladders`.`ladder_id` = `{$wpdb->prefix}trn_challenges`.`ladder_id` WHERE `{$wpdb->prefix}trn_challenges`.`challenge_id` = %d", $wpdb->insert_id ) );

		if ( 'direct' === $challenge_type ) {
			$email_details = get_challenge_email_data( $challenge->challenge_id );
			if ( 'players' === $ladder->competitor_type ) {
				$challenger_link = trn_route( 'players.single', array( 'id' => $challenge->challenger_id ) );
			} else {
				$challenger_link = trn_route( 'teams.single', array( 'id' => $challenge->challenger_id ) );
			}

			$data = [
				'ladder_link'     => trn_route( 'ladders.single', array( 'id' => $challenge->ladder_id ) ),
				'ladder_name'     => $ladder->name,
				'challenger_link' => $challenger_link,
				'challenger'      => $email_details->challenger,
				'challenge_date'  => $email_details->challenge_date,
				'challenge_link'  => trn_route( 'challenges.single', array( 'id' => $challenge->challenge_id ) ),
			];

			do_action(
				'trn_notify_challenge_received',
				array(
					'email' => $email_details->challengee_email,
					'name'  => $email_details->challengee,
				),
				esc_html__( 'Challenge Received', 'tournamatch' ),
				$data
			);
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $challenge, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Handles deleting a challenge.
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

		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_challenges` WHERE `challenge_id` = %d LIMIT 1", $id ) );

		return new \WP_REST_Response(
			array(
				'message' => __( 'The challenge was deleted.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Determines whether the current user may delete a challenge.
	 *
	 * @since 3.11.0
	 * @since 3.15.0 Refactored to reuse permissions method.
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return bool True with permission, false otherwise.
	 */
	public function can_delete_challenge( \WP_REST_Request $request ) {
		$id      = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		return trn_can_delete_challenge( $user_id, $id );
	}

	/**
	 * Check if a given request has access to get a challenge.
	 *
	 * @since 3.27.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		global $wpdb;

		$challenge = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_challenges` WHERE `challenge_id` = %d", $request->get_param( 'id' ) ) );
		if ( ! $challenge ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Challenge does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Retrieves a single challenge item.
	 *
	 * @since 3.27.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		global $wpdb;

		$challenge = $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  `c`.*,
  `l`.`competitor_type`,
  `l`.`name` AS `ladder`,
  IFNULL(`t1`.`name`, `p1`.`display_name`) AS `challenger`,
  IFNULL(`t2`.`name`, `p2`.`display_name`) AS `challengee`
FROM `{$wpdb->prefix}trn_challenges` AS `c`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `c`.`ladder_id` = `l`.`ladder_id`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p1` ON `c`.`challenger_id` = `p1`.`user_id` AND `l`.`competitor_type` = 1
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p2` ON `c`.`challengee_id` = `p2`.`user_id` AND `l`.`competitor_type` = 1
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t1` ON `c`.`challenger_id` = `t1`.`team_id` AND `l`.`competitor_type` = 3
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t2` ON `c`.`challengee_id` = `t2`.`team_id` AND `l`.`competitor_type` = 3  
WHERE `challenge_id` = %d",
				$request->get_param( 'id' )
			)
		);
		$data      = $this->prepare_item_for_response( $challenge, $request );
		$response  = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Evaluates whether a user has permission to retrieve challenge items.
	 *
	 * @since 3.27.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves many challenge items.
	 *
	 * @since 3.27.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wpdb;

		$total_data = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_challenges` WHERE ((`accepted_state` = 'accepted' ) OR (`match_time` > UTC_TIMESTAMP() AND `accepted_state` = 'pending'))" );

		$sql = $wpdb->prepare(
			"
SELECT 
  `c`.*,
  `l`.`competitor_type`,
  `l`.`name` AS `ladder`,
  IFNULL(`t1`.`name`, `p1`.`display_name`) AS `challenger`,
  IFNULL(`t2`.`name`, `p2`.`display_name`) AS `challengee`
FROM `{$wpdb->prefix}trn_challenges` AS `c`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `c`.`ladder_id` = `l`.`ladder_id`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p1` ON `c`.`challenger_id` = `p1`.`user_id` AND `l`.`competitor_type` = 1
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p2` ON `c`.`challengee_id` = `p2`.`user_id` AND `l`.`competitor_type` = 1
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t1` ON `c`.`challenger_id` = `t1`.`team_id` AND `l`.`competitor_type` = 3
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t2` ON `c`.`challengee_id` = `t2`.`team_id` AND `l`.`competitor_type` = 3  
WHERE ((`c`.`accepted_state` = %s ) OR (`c`.`match_time` > UTC_TIMESTAMP() AND `c`.`accepted_state` = %s))",
			'accepted',
			'pending'
		);

		if ( ! empty( $request['search'] ) ) {
			$sql .= $wpdb->prepare( ' AND (`l`.`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `p1`.`display_name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `p2`.`display_name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `t1`.`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `t2`.`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `c`.`accepted_state` LIKE %s)', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
		}

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filtered = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns  = array( 'ladder', 'challenger', 'challengee', 'match_time', 'accepted_state' );
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

		$challenges = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$items = array();

		foreach ( $challenges as $challenge ) {
			$data    = $this->prepare_item_for_response( $challenge, $request );
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
	 * Handles accepting a challenge.
	 *
	 * @since 3.15.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function accept( \WP_REST_Request $request ) {
		global $wpdb;

		$params           = $request->get_params();
		$id               = $params['id'];
		$user_id          = get_current_user_id();
		$challenge        = $wpdb->get_row( $wpdb->prepare( "SELECT c.*, l.competitor_type FROM `{$wpdb->prefix}trn_challenges` AS c LEFT JOIN `{$wpdb->prefix}trn_ladders` AS l ON c.ladder_id = l.ladder_id WHERE challenge_id = %d", $id ) );
		$competition_type = $challenge->competitor_type;
		$response_data    = array();

		if ( 'blind' === $challenge->challenge_type ) {
			if ( 'players' === $challenge->competitor_type ) {
				$challenge->challengee_id = $user_id;
			} else {
				$teams                    = $wpdb->get_row( $wpdb->prepare( "SELECT le.competitor_id AS competitor_id FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS tm ON le.competitor_id = tm.team_id WHERE tm.user_id = %d AND le.ladder_id = %d", $user_id, $challenge->ladder_id ) );
				$challenge->challengee_id = $teams->competitor_id;
			}
		}

		// to accept, challengee and challenger must be competing on ladder.
		$this->verify_business_rules(
			array(
				new Must_Participate_On_Ladder( $challenge->ladder_id, $challenge->challenger_id ),
				new Must_Participate_On_Ladder( $challenge->ladder_id, $challenge->challengee_id ),
			)
		);

		// create scheduled match from challenge.
		$insert = array(
			'competition_id'      => $challenge->ladder_id,
			'competition_type'    => 'ladders',
			'one_competitor_id'   => $challenge->challenger_id,
			'one_competitor_type' => $competition_type,
			'one_ip'              => '',
			'one_result'          => '',
			'one_comment'         => '',
			'two_competitor_id'   => $challenge->challengee_id,
			'two_competitor_type' => $competition_type,
			'two_ip'              => '',
			'two_result'          => '',
			'two_comment'         => '',
			'match_date'          => $challenge->match_time,
			'match_status'        => 'scheduled',
		);
		$wpdb->insert( $wpdb->prefix . 'trn_matches', $insert );
		$match_id = $wpdb->insert_id;

		// update challenge in db.
		if ( 'direct' === $challenge->challenge_type ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}trn_challenges SET match_id = %d, accepted_state = 'accepted', accepted_at = UTC_TIMESTAMP() WHERE challenge_id = %d", $match_id, $id ) );
		} else {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}trn_challenges SET challengee_id = %d, match_id = %d, accepted_state='accepted', accepted_at = UTC_TIMESTAMP() WHERE challenge_id = %d", $challenge->challengee_id, $match_id, $id ) );
		}

		// Send accept email here to challenger.
		$email_details = get_challenge_email_data( $id );

		if ( 'players' === $email_details->competitor_type ) {
			$challengee_link = trn_route( 'players.single', [ 'id' => $email_details->challengee_id ] );
		} else {
			$challengee_link = trn_route( 'teams.single', [ 'id' => $email_details->challengee_id ] );
		}

		$data = [
			'opponent_link'  => $challengee_link,
			'opponent'       => $email_details->challengee,
			'challenge_link' => trn_route( 'challenges.single', [ 'id' => $challenge->challenge_id ] ),
		];

		do_action(
			'trn_notify_challenge_accepted',
			[
				'email' => $email_details->challenger_email,
				'name'  => $email_details->challenger,
			],
			esc_html__( 'Challenge Accepted', 'tournamatch' ),
			$data
		);

		if ( 'blind' === $challenge->challenge_type ) {
			$response_data = array_merge(
				$response_data,
				array(
					'challenge' => array(
						'challenger_id'   => $email_details->challenger_id,
						'challenger_name' => $email_details->challenger,
						'challengee_id'   => $email_details->challengee_id,
						'challengee_name' => $email_details->challengee,
						'status'          => 'accepted',
					),
				)
			);
		}

		$response_data = array_merge( $response_data, array( 'status' => 200 ) );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The challenge was accepted.', 'tournamatch' ),
				'data'    => $response_data,
			),
			200
		);
	}

	/**
	 * Determines whether the current user may accept a challenge.
	 *
	 * @since 3.15.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return bool True with permission, false otherwise.
	 */
	public function can_accept_challenge( \WP_REST_Request $request ) {
		$id      = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		return trn_can_accept_challenge( $user_id, $id );
	}

	/**
	 * Handles declining a challenge.
	 *
	 * @since 3.15.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function decline( \WP_REST_Request $request ) {
		global $wpdb;

		$params = $request->get_params();
		$id     = $params['id'];

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}trn_challenges SET accepted_state='declined', accepted_at = UTC_TIMESTAMP() WHERE challenge_id = %d", $id ) );

		// Send decline email here to challenger.
		$email_details = get_challenge_email_data( $id );
		if ( 'players' === $email_details->competitor_type ) {
			$opponent_link = trn_route( 'players.single', [ 'id' => $email_details->challengee_id ] );
		} else {
			$opponent_link = trn_route( 'teams.single', [ 'id' => $email_details->challengee_id ] );
		}

		$data = [
			'opponent_link' => $opponent_link,
			'opponent'      => $email_details->challengee,
		];

		do_action(
			'trn_notify_challenge_declined',
			[
				'email' => $email_details->challenger_email,
				'name'  => $email_details->challenger,
			],
			esc_html__( 'Challenge Declined', 'tournamatch' ),
			$data
		);

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The challenge was declined.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Determines whether the current user may decline a challenge.
	 *
	 * @since 3.15.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return bool True with permission, false otherwise.
	 */
	public function can_decline_challenge( \WP_REST_Request $request ) {
		$id      = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		return trn_can_decline_challenge( $user_id, $id );
	}

	/**
	 * Prepares a single challenge item for response.
	 *
	 * @since 3.15.0
	 *
	 * @param Object           $challenge Challenge object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $challenge, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'challenge_id', $fields ) ) {
			$data['challenge_id'] = (int) $challenge->challenge_id;
		}

		if ( rest_is_field_included( 'ladder_id', $fields ) ) {
			$data['ladder_id'] = (int) $challenge->ladder_id;
		}

		if ( rest_is_field_included( 'challenge_type', $fields ) ) {
			$data['challenge_type'] = $challenge->challenge_type;
		}

		if ( rest_is_field_included( 'challenger_id', $fields ) ) {
			$data['challenger_id'] = (int) $challenge->challenger_id;
		}

		if ( rest_is_field_included( 'challenger_type', $fields ) ) {
			$data['challenger_type'] = $challenge->competitor_type;
		}

		if ( rest_is_field_included( 'challengee_id', $fields ) ) {
			$data['challengee_id'] = (int) $challenge->challengee_id;
		}

		if ( rest_is_field_included( 'challengee_type', $fields ) ) {
			$data['challengee_type'] = $challenge->competitor_type;
		}

		if ( rest_is_field_included( 'match_time', $fields ) ) {
			$data['match_time'] = array(
				'raw'      => $challenge->match_time,
				'rendered' => date( get_option( 'date_format' ), strtotime( $challenge->match_time ) ),
			);
		}

		if ( rest_is_field_included( 'accepted_state', $fields ) ) {
			$data['accepted_state'] = $challenge->accepted_state;
		}

		if ( rest_is_field_included( 'accepted_at', $fields ) ) {
			$data['accepted_at'] = array(
				'raw'      => $challenge->accepted_at,
				'rendered' => date( get_option( 'date_format' ), strtotime( $challenge->accepted_at ) ),
			);
		}

		if ( rest_is_field_included( 'match_id', $fields ) ) {
			$data['match_id'] = (int) $challenge->match_id;
		}

		if ( rest_is_field_included( 'expires_at', $fields ) ) {
			$data['expires_at'] = array(
				'raw'      => $challenge->expires_at,
				'rendered' => date( get_option( 'date_format' ), strtotime( $challenge->expires_at ) ),
			);
		}

		if ( rest_is_field_included( 'link', $fields ) ) {
			$data['link'] = trn_route( 'challenges.single', array( 'id' => $challenge->challenge_id ) );
		}

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $challenge );
		$response->add_links( $links );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 3.21.0
	 *
	 * @param Object $challenge Challenge object.
	 * @return array Links for the given challenge.
	 */
	protected function prepare_links( $challenge ) {
		global $wpdb;

		$base = "{$this->namespace}/challenges";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $challenge->challenge_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		$links['ladder'] = array(
			'href'       => rest_url( "{$this->namespace}/ladders/{$challenge->ladder_id}" ),
			'embeddable' => true,
		);

		$competitor_type = $wpdb->get_var( $wpdb->prepare( "SELECT `competitor_type` FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $challenge->ladder_id ) );

		$links['challenger'] = array(
			'href'       => rest_url( "{$this->namespace}/{$competitor_type}/{$challenge->challenger_id}" ),
			'embeddable' => true,
		);
		$links['challengee'] = array(
			'href'       => rest_url( "{$this->namespace}/{$competitor_type}/{$challenge->challengee_id}" ),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Retrieves the challenge schema, conforming to JSON Schema.
	 *
	 * @since 3.21.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'challenges',
			'type'       => 'object',
			'properties' => array(
				'challenge_id'    => array(
					'description' => esc_html__( 'The id for the challenge.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'ladder_id'       => array(
					'description' => esc_html__( 'The ladder id for the challenge.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'challenge_type'  => array(
					'description' => esc_html__( 'The type of challenge.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'direct', 'blind' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'challenger_id'   => array(
					'description' => esc_html__( 'The competitor id creating/sending the challenge.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'challenger_type' => array(
					'description' => esc_html__( 'The competitor type of the challenger.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'players', 'teams' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'challengee_id'   => array(
					'description' => esc_html__( 'The competitor id receiving/accepting this challenge.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'challengee_type' => array(
					'description' => esc_html__( 'The competitor type of the challengee.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'players', 'teams' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'match_time'      => array(
					'description' => esc_html__( 'The match time for the challenge.', 'tournamatch' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'Match time for the challenge, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'Match time for the object, transformed for display.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'accepted_state'  => array(
					'description' => esc_html__( 'The accepted state for the challenge.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'pending', 'accepted', 'declined', 'reported' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'pending',
				),
				'accepted_at'     => array(
					'description' => esc_html__( 'The accepted at time for the challenge.', 'tournamatch' ),
					'type'        => array( 'object', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'Accepted at time for the challenge, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'Accepted at time for the object, transformed for display.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'match_id'        => array(
					'description' => esc_html__( 'The match id for the challenge.', 'tournamatch' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'expires_at'      => array(
					'description' => esc_html__( 'The expiration at time for the challenge.', 'tournamatch' ),
					'type'        => array( 'object', 'null' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'Expiration at time for the challenge, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'Expiration at time for the object, transformed for display.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'link'            => array(
					'description' => esc_html__( 'URL to the challenge.' ),
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

new Challenge();
