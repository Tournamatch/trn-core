<?php
/**
 * Manages Tournamatch REST endpoint for team requests.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\One_Team_Per_User;
use Tournamatch\Rules\One_Team_Request_Per_User;
use Tournamatch\Rules\One_User_Per_Team;
use Tournamatch\Rules\Team_Not_Maxed;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for team requests.
 *
 * @since      3.8.0
 * @since      4.0.0 This now extends the Controller class.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Team_Request extends Controller {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.8.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.8.0
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/team-requests/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_all' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'team_id' => array(
							'required' => false,
							'type'     => 'integer',
							'minimum'  => 1,
						),
						'user_id' => array(
							'required' => false,
							'type'     => 'integer',
							'minimum'  => 1,
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create' ),
					'permission_callback' => function( $request ) {
						return ( is_user_logged_in() && ( get_current_user_id() === (int) $request->get_param( 'user_id' ) ) );
					},
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/team-requests/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete' ),
				'permission_callback' => function( $request ) {
					global $wpdb;

					$user_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `user_id` FROM `{$wpdb->prefix}trn_teams_members_requests` WHERE `team_member_request_id` = %d", $request['id'] ) );

					return ( get_current_user_id() === $user_id );
				},

				'args'                => array(
					'id' => array(
						'required' => true,
						'type'     => 'integer',
						'minimum'  => 1,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/team-requests/(?P<id>\d+)/accept',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'accept' ),
				'permission_callback' => array( $this, 'is_team_captain' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param ) && ( 0 < (int) $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/team-requests/(?P<id>\d+)/decline',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'decline' ),
				'permission_callback' => array( $this, 'is_team_captain' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param ) && ( 0 < (int) $param );
						},
					),
				),
			)
		);

	}

	/**
	 * Returns true if the current user is the team captain; false otherwise.
	 *
	 * @since 3.23.0
	 *
	 * @param Object $request The request object.
	 *
	 * @return bool
	 */
	public function is_team_captain( $request ) {
		global $wpdb;

		$team_captain = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `tm`.`user_id` FROM `{$wpdb->prefix}trn_teams_members` AS `tm` LEFT JOIN `{$wpdb->prefix}trn_teams_members_requests` AS `tmr` ON `tm`.`team_id` = `tmr`.`team_id` WHERE `tmr`.`team_member_request_id` = %d AND `tm`.`team_rank_id` = %d", $request['id'], 1 ) );

		return ( get_current_user_id() === $team_captain );
	}

	/**
	 * Handles accepting a user request to join a team.
	 *
	 * @since 3.8.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array
	 */
	public function accept( \WP_REST_Request $request ) {
		global $wpdb;

		$request_id   = $request->get_param( 'request_id' );
		$team_request = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members_requests` WHERE `team_member_request_id` = %d", $request_id ) );
		$team_id      = $team_request->team_id;
		$user_id      = $team_request->user_id;

		$rules = array(
			new One_User_Per_Team( $team_id, $user_id ),
			new Team_Not_Maxed( $team_id ),
		);

		if ( trn_get_option( 'one_team_per_player' ) ) {
			array_splice( $rules, 1, 0, array( new One_Team_Per_User( $user_id ) ) );
		}

		// Verify business rules.
		$this->verify_business_rules( $rules );

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}trn_teams SET members = members + 1 WHERE team_id = %d", $team_id ) );
		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}trn_teams_members (team_member_id, team_id, user_id, joined_date, `team_rank_id`) VALUES (NULL, %d, %d, UTC_TIMESTAMP(), 2)", $team_id, $user_id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_teams_members_requests WHERE team_member_request_id = %d LIMIT 1", $request_id ) );

		$team_name       = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM {$wpdb->prefix}trn_teams WHERE team_id = %d", $team_request->team_id ) );
		$user_requesting = $wpdb->get_row( $wpdb->prepare( "SELECT u.user_email AS email, p.display_name FROM {$wpdb->prefix}trn_players_profiles AS p LEFT JOIN {$wpdb->users} AS u ON u.ID = p.user_id WHERE p.user_id = %d", $team_request->user_id ) );

		$data = [
			'team_link' => trn_route( 'teams.single', [ 'id' => $team_id ] ),
			'team_name' => $team_name,
		];

		do_action(
			'trn_notify_membership_request_accepted',
			[
				'email' => $user_requesting->email,
				'name'  => $user_requesting->display_name,
			],
			__( 'Team Membership Request Accepted', 'tournamatch' ),
			$data
		);

		return array(
			'message' => __( 'The request has been accepted and the team roster is updated.', 'tournamatch' ),
		);
	}

	/**
	 * Handles declining & removing a user request to join a team.
	 *
	 * @since 3.8.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array
	 */
	public function decline( \WP_REST_Request $request ) {
		global $wpdb;

		$request_id      = $request->get_param( 'request_id' );
		$team_request    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}trn_teams_members_requests WHERE team_member_request_id = %d", $request_id ) );
		$team_name       = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM {$wpdb->prefix}trn_teams WHERE team_id = %d", $team_request->team_id ) );
		$user_requesting = $wpdb->get_row( $wpdb->prepare( "SELECT u.user_email AS email, p.display_name FROM {$wpdb->prefix}trn_players_profiles AS p LEFT JOIN {$wpdb->users} AS u ON u.ID = p.user_id WHERE p.user_id = %d", $team_request->user_id ) );

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_teams_members_requests WHERE team_member_request_id = %d LIMIT 1", $request_id ) );

		$data = [
			'team_link' => trn_route( 'teams.single', [ 'id' => $team_request->team_id ] ),
			'team_name' => $team_name,
		];

		do_action(
			'trn_notify_membership_request_declined',
			[
				'email' => $user_requesting->email,
				'name'  => $user_requesting->display_name,
			],
			__( 'Team Membership Request Declined', 'tournamatch' ),
			$data
		);

		return array(
			'message' => __( 'The request has been removed.', 'tournamatch' ),
		);
	}

	/**
	 * Handles creating a new team request.
	 *
	 * @since 3.8.0
	 * @since 4.0.0 Changed response payload.
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function create( \WP_REST_Request $request ) {
		global $wpdb;

		$team_id = $request->get_param( 'team_id' );
		$user_id = $request->get_param( 'user_id' );

		// you can't join if you are already a member.
		// you have already requested to join this team.
		// team has max members.
		$rules = array(
			new One_User_Per_Team( $team_id, $user_id ),
			new One_Team_Request_Per_User( $team_id, $user_id ),
			new Team_Not_Maxed( $team_id ),
		);

		if ( trn_get_option( 'one_team_per_player' ) ) {
			array_splice( $rules, 1, 0, array( new One_Team_Per_User( $user_id ) ) );
		}

		$this->verify_business_rules( $rules );

		$result = $wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_teams_members_requests` (`team_member_request_id`, `team_id`, `user_id`, `requested_at`) VALUES (NULL, %d, %d, UTC_TIMESTAMP())", $team_id, $user_id ) );
		if ( ! $result ) {
			return new \WP_REST_Response(
				array(
					'message' => esc_html__( 'An error occurred attempting to record your request to join this team.', 'tournamatch' ),
					'data'    => array(
						'status' => 504,
					),
				),
				504
			);
		}

		$request_id      = $wpdb->insert_id;
		$team_captain    = $wpdb->get_row( $wpdb->prepare( "SELECT u.user_email AS email, p.display_name FROM {$wpdb->prefix}trn_teams_members AS tm LEFT JOIN {$wpdb->prefix}trn_players_profiles AS p ON tm.user_id = p.user_id LEFT JOIN {$wpdb->users} AS u ON u.ID = tm.user_id WHERE tm.team_id = %d AND tm.`team_rank_id` = 1 ORDER BY tm.joined_date LIMIT 1", $team_id ) );
		$team            = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}trn_teams WHERE team_id = %d", $team_id ) );
		$user_requesting = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}trn_players_profiles WHERE user_id = %d", $user_id ) );

		$data = [
			'user_link'    => trn_route( 'players.single', [ 'id' => $user_id ] ),
			'display_name' => $user_requesting->display_name,
			'team_link'    => trn_route( 'teams.single', [ 'id' => $team_id ] ),
			'team_name'    => $team->name,
		];

		do_action(
			'trn_notify_membership_requested',
			[
				'email' => $team_captain->email,
				'name'  => $team_captain->display_name,
			],
			__( 'Team Membership Request Received', 'tournamatch' ),
			$data
		);

		$team_request = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members_requests` WHERE `team_member_request_id` = %d", $request_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $team_request, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Handles returning team request.
	 *
	 * @since 3.8.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array|object
	 */
	public function get_all( \WP_REST_Request $request ) {
		global $wpdb;

		$params = $request->get_params();

		if ( isset( $params['team_id'] ) ) {
			$team_requests = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT r.*, pp.display_name AS display_name FROM {$wpdb->prefix}trn_teams_members_requests AS r LEFT JOIN {$wpdb->prefix}trn_players_profiles AS pp ON pp.user_id = r.user_id WHERE r.team_id = %d",
					$params['team_id']
				)
			);
		} elseif ( isset( $params['user_id'] ) ) {
			$team_requests = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT r.*, pp.display_name AS display_name, t.name AS team_name FROM {$wpdb->prefix}trn_teams_members_requests AS r LEFT JOIN {$wpdb->prefix}trn_players_profiles AS pp ON pp.user_id = r.user_id LEFT JOIN `{$wpdb->prefix}trn_teams` AS t ON t.team_id = r.team_id WHERE r.user_id = %d",
					$params['user_id']
				)
			);
		} else {
			$team_requests = $wpdb->get_results( "SELECT r.*, pp.display_name AS display_name FROM {$wpdb->prefix}trn_teams_members_requests AS r LEFT JOIN {$wpdb->prefix}trn_players_profiles AS pp ON pp.user_id = r.user_id" );
		}

		$items = array();
		foreach ( $team_requests as $team_request ) {
			$data    = $this->prepare_item_for_response( $team_request, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $items );

		return $response;
	}

	/**
	 * Handles deleting a team request to join a team.
	 *
	 * @since 3.15.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete( \WP_REST_Request $request ) {
		global $wpdb;

		$params     = $request->get_params();
		$request_id = $params['id'];

		// Verify authorization.

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_teams_members_requests WHERE team_member_request_id = %d LIMIT 1", $request_id ) );

		return new \WP_REST_Response(
			array(
				'message' => __( 'The request has been removed.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Prepares a single team-request item for response.
	 *
	 * @since 4.0.0
	 *
	 * @param Object           $team_request    Team request object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $team_request, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'team_member_request_id', $fields ) ) {
			$data['team_member_request_id'] = (int) $team_request->team_member_request_id;
		}

		if ( rest_is_field_included( 'team_id', $fields ) ) {
			$data['team_id'] = (int) $team_request->team_id;
		}

		if ( rest_is_field_included( 'user_id', $fields ) ) {
			$data['user_id'] = (int) $team_request->user_id;
		}

		if ( rest_is_field_included( 'requested_at', $fields ) ) {
			$data['requested_at'] = array(
				'raw'      => $team_request->requested_at,
				'rendered' => date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $team_request->requested_at ) ) ),
			);
		}

		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $team_request );
		$response->add_links( $links );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 4.0.0
	 *
	 * @param Object $competitor Team request object.
	 *
	 * @return array Links for the given team request.
	 */
	protected function prepare_links( $competitor ) {
		$base = "{$this->namespace}/team-requests";

		$links = array(
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		$links['team'] = array(
			'href'       => rest_url( "{$this->namespace}/teams/{$competitor->team_id}" ),
			'embeddable' => true,
		);

		$links['player'] = array(
			'href'       => rest_url( "{$this->namespace}/players/{$competitor->user_id}" ),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Retrieves the team-request schema, conforming to JSON Schema.
	 *
	 * @since 4.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'team-requests',
			'type'       => 'object',
			'properties' => array(
				'team_member_request_id' => array(
					'description' => esc_html__( 'The id for the team request.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'team_id'                => array(
					'description' => esc_html__( 'The team id for the team request.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'user_id'                => array(
					'description' => esc_html__( 'The user id for the team request.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'requested_at'           => array(
					'description' => esc_html__( 'The datetime the team request was created for the team request.', 'tournamatch' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'The datetime the team request was created for the team request, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'The datetime the team request was created for the team request, transformed for display.', 'tournamatch' ),
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

new Team_Request();
