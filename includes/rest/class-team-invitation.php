<?php
/**
 * Manages Tournamatch REST endpoint for team invitations.
 *
 * @link  https://www.tournamatch.com
 * @since 3.8.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\Business_Rule;
use Tournamatch\Rules\One_Team_Per_User;
use Tournamatch\Rules\One_User_Per_Team;
use Tournamatch\Rules\Team_Not_Maxed;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for team invitations.
 *
 * @since 3.8.0
 * @since 4.0.0 This now extends the Controller class.
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Team_Invitation extends Controller {


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
	 * @since 3.10.0 Updated route arguments to use REST validation types.
	 * @since 3.13.0 Added accept and decline endpoints.
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/team-invitations/',
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
					'permission_callback' => array( $this, 'is_team_captain' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/team-invitations/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete' ),
				'permission_callback' => function( $request ) {
					global $wpdb;

					$team_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE `team_member_invitation_id` = %d", $request->get_param( 'id' ) ) );
					$team_captain = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `user_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `team_rank_id` = %d", $team_id, 1 ) );

					return ( is_user_logged_in() && ( get_current_user_id() === $team_captain ) );
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
			'/team-invitations/(?P<id>\d+)/accept',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'accept' ),
				'permission_callback' => array( $this, 'is_user_invited' ),
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
			'/team-invitations/(?P<id>\d+)/decline',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'decline' ),
				'permission_callback' => array( $this, 'is_user_invited' ),
				'args'                => array(
					'id' => array(
						'required' => true,
						'type'     => 'integer',
						'minimum'  => 1,
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

		$team_captain = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `user_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `team_rank_id` = %d", $request['team_id'], 1 ) );

		return ( get_current_user_id() === $team_captain );
	}

	/**
	 * Returns true if the current user is the user invited to join the team; false otherwise.
	 *
	 * @since 3.23.0
	 *
	 * @param Object $request The request object.
	 *
	 * @return bool
	 */
	public function is_user_invited( $request ) {
		global $wpdb;

		$user_invited = (int) $wpdb->get_var( $wpdb->prepare( "SELECT `user_id` FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE `team_member_invitation_id` = %d", $request['id'] ) );

		return ( get_current_user_id() === $user_invited );
	}

	/**
	 * Handles creating a new team invitation.
	 *
	 * @since 3.8.0
	 * @since 3.10.0 Added support for creating an invitation directly to a user.
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function create( \WP_REST_Request $request ) {
		global $wpdb;

		$params  = $request->get_params();
		$type    = $params['invitation_type'];
		$email   = isset( $params['email'] ) ? $params['email'] : null;
		$user_id = isset( $params['user_id'] ) ? $params['user_id'] : null;
		$team_id = $params['team_id'];

		// Setup business rules.
		$rules = array(
			new Team_Not_Maxed( $team_id ),
		);

		if ( 'user' === $type ) {
			$rules[] = new One_User_Per_Team( $team_id, $user_id );

			if ( '1' === trn_get_option( 'one_team_per_player' ) ) {
				$rules[] = new One_Team_Per_User( $user_id );
			}
		}

		// Verify business rules.
		$this->verify_business_rules( $rules );

		$accept_hash = md5( $team_id . time() );
		$accept_hash = '0a' . substr( $accept_hash, 2 );

		if ( 'user' === $type ) {
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}trn_teams_members_invitations (team_member_invitation_id, team_id, invitation_type, user_id, invited_at, accept_hash) VALUES (NULL, %d, %s, %d, UTC_TIMESTAMP(), %s)", $team_id, 'user', $user_id, $accept_hash ) );

			// Get email address of user.
			$user       = $wpdb->get_row( $wpdb->prepare( "SELECT u.user_email AS email, p.display_name AS `name` FROM {$wpdb->prefix}trn_players_profiles AS p LEFT JOIN " . $wpdb->users . ' AS u ON u.ID = p.user_id WHERE p.user_id = %d', $user_id ) );
			$message_to = $user->name;
			$email      = $user->email;
		} else {
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}trn_teams_members_invitations (team_member_invitation_id, team_id, invitation_type, user_email, invited_at, accept_hash) VALUES (NULL, %d, %s, %s, UTC_TIMESTAMP(), %s)", $team_id, 'email', $email, $accept_hash ) );
			$message_to = sanitize_email( $email );
		}

		$team_name = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM {$wpdb->prefix}trn_teams WHERE team_id = %d", $team_id ) );
		$data      = [
			'team_link'   => trn_route( 'teams.single', [ 'id' => $team_id ] ),
			'team_name'   => $team_name,
			'accept_link' => trn_route( 'magic.accept-team-invitation', [ 'join_code' => $accept_hash ] ),
		];

		do_action( 'trn_notify_membership_invited', $email, __( 'Team Membership Invitation', 'tournamatch' ), $data );

		return new \WP_REST_Response(
			array(
				/* translators: The next message prints an email address or user name to where this message was sent. */
				'message' => sprintf( esc_html__( 'The invitation was sent to %s successfully!', 'tournamatch' ), $message_to ),
				'data'    => array(
					'status' => 200,
				),
			),
			200
		);
	}

	/**
	 * Handles accepting an invitation to join a team by a user.
	 *
	 * @since 3.13.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array
	 */
	public function accept( \WP_REST_Request $request ) {
		global $wpdb;

		$params        = $request->get_params();
		$invitation_id = $params['invitation_id'];

		$team_invitation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}trn_teams_members_invitations WHERE team_member_invitation_id = %d", $invitation_id ) );
		$team_id         = $team_invitation->team_id;
		$user_id         = $team_invitation->user_id;

		$rules = array(
			new One_User_Per_Team( $team_id, $user_id ),
			new Team_Not_Maxed( $team_id ),
		);

		if ( '1' === trn_get_option( 'one_team_per_player' ) ) {
			array_splice( $rules, 1, 0, array( new One_Team_Per_User( $user_id ) ) );
		}

		// Verify business rules.
		$this->verify_business_rules( $rules );

		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET members = members + 1 WHERE team_id = %d", $team_id ) );
		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_teams_members` (team_member_id, team_id, user_id, joined_date, `team_rank_id`) VALUES (NULL, %d, %d, UTC_TIMESTAMP(), 2)", $team_id, $user_id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE team_member_invitation_id = %d LIMIT 1", $invitation_id ) );

		$team_name = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE team_id = %d", $team_id ) );
		$captain   = $wpdb->get_row( $wpdb->prepare( "SELECT u.user_email AS email, p.display_name FROM {$wpdb->users} AS u LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS p ON u.ID = p.user_id LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS tm ON p.user_id = tm.user_id AND tm.`team_rank_id` = 1 WHERE tm.team_id = %d", $team_id ) );
		$user_name = $wpdb->get_var( $wpdb->prepare( "SELECT display_name FROM `{$wpdb->prefix}trn_players_profiles` WHERE user_id = %d", $user_id ) );

		$data = [
			'team_link' => trn_route( 'teams.single', [ 'id' => $team_id ] ),
			'team_name' => $team_name,
			'user_link' => trn_route( 'players.single', [ 'id' => $user_id ] ),
			'user_name' => $user_name,
		];

		do_action(
			'trn_notify_membership_invitation_accepted',
			[
				'email' => $captain->email,
				'name'  => $captain->display_name,
			],
			__( 'Team Membership Invitation Accepted', 'tournamatch' ),
			$data
		);

		return array(
			'message' => __( 'The invitation has been accepted and you have been added to the team roster.', 'tournamatch' ),
		);
	}

	/**
	 * Handles declining & removing an invitation to join a team by a user.
	 *
	 * @since 3.13.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array
	 */
	public function decline( \WP_REST_Request $request ) {
		global $wpdb;

		$params        = $request->get_params();
		$invitation_id = $params['invitation_id'];

		$invitation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE team_member_invitation_id = %d", $invitation_id ) );
		$team_name  = $wpdb->get_var( $wpdb->prepare( "SELECT `{$wpdb->prefix}name` FROM `{$wpdb->prefix}trn_teams` WHERE team_id = %d", $invitation->team_id ) );
		$user_name  = $wpdb->get_var( $wpdb->prepare( "SELECT display_name FROM `{$wpdb->prefix}trn_players_profiles` WHERE user_id = %d", $invitation->user_id ) );
		$captain    = $wpdb->get_row( $wpdb->prepare( "SELECT u.user_email AS email, p.display_name FROM {$wpdb->users} AS u LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS p ON u.ID = p.user_id LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS tm ON p.user_id = tm.user_id AND tm.`team_rank_id` = 1 WHERE tm.team_id = %d", $invitation->team_id ) );

		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE team_member_invitation_id = %d LIMIT 1", $invitation_id ) );

		$data = [
			'team_link' => trn_route( 'teams.single', [ 'id' => $invitation->team_id ] ),
			'team_name' => $team_name,
			'user_link' => trn_route( 'players.single', [ 'id' => $invitation->user_id ] ),
			'user_name' => $user_name,
		];

		do_action(
			'trn_notify_membership_invitation_declined',
			[
				'email' => $captain->email,
				'name'  => $captain->display_name,
			],
			__( 'Team Membership Invitation Declined', 'tournamatch' ),
			$data
		);

		return array(
			'message' => __( 'The invitation has been declined.', 'tournamatch' ),
		);
	}

	/**
	 * Handles deleting a team invitation to join a team.
	 *
	 * @since 3.8.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete( \WP_REST_Request $request ) {
		global $wpdb;

		$params        = $request->get_params();
		$invitation_id = $params['id'];

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}trn_teams_members_invitations WHERE team_member_invitation_id = %d LIMIT 1", $invitation_id ) );

		return new \WP_REST_Response(
			array(
				'message' => __( 'The invitation has been removed.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Handles returning team invitations.
	 *
	 * @since 3.8.0
	 * @since 3.10.0 Added support for returning user name for invitations to users.
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array|object
	 */
	public function get_all( \WP_REST_Request $request ) {
		global $wpdb;

		$params = $request->get_params();

		if ( isset( $params['team_id'] ) ) {
			$invitations = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT tmi.*, u.display_name AS name FROM {$wpdb->prefix}trn_teams_members_invitations AS tmi LEFT JOIN {$wpdb->prefix}trn_players_profiles AS u ON u.user_id = tmi.user_id WHERE team_id = %d",
					$params['team_id']
				)
			);
		} elseif ( isset( $params['user_id'] ) ) {
			$invitations = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT tmi.*, u.display_name AS user_name, t.name AS team_name FROM {$wpdb->prefix}trn_teams_members_invitations AS tmi LEFT JOIN {$wpdb->prefix}trn_players_profiles AS u ON u.user_id = tmi.user_id LEFT JOIN {$wpdb->prefix}trn_teams AS t ON t.team_id = tmi.team_id WHERE tmi.user_id = %d",
					$params['user_id']
				)
			);
		} else {
			$invitations = $wpdb->get_results( "SELECT tmi.*, u.display_name AS name FROM {$wpdb->prefix}trn_teams_members_invitations AS tmi LEFT JOIN {$wpdb->prefix}trn_players_profiles AS u ON u.user_id = tmi.user_id" );
		}

		$items = array();
		foreach ( $invitations as $invitation ) {
			$data    = $this->prepare_item_for_response( $invitation, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $items );

		return $response;
	}

	/**
	 * Prepares links for the invitation.
	 *
	 * @since 4.0.0
	 *
	 * @param Object $competitor Team invitation object.
	 *
	 * @return array Links for the given team invitation.
	 */
	protected function prepare_links( $competitor ) {
		$base = "{$this->namespace}/team-invitations";

		$links = array(
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		$links['team'] = array(
			'href'       => rest_url( "{$this->namespace}/teams/{$competitor->team_id}" ),
			'embeddable' => true,
		);

		if ( isset( $competitor->user_id ) ) {
			$links['player'] = array(
				'href'       => rest_url( "{$this->namespace}/players/{$competitor->user_id}" ),
				'embeddable' => true,
			);
		}

		return $links;
	}

	/**
	 * Retrieves the team-invitations schema, conforming to JSON Schema.
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
			'title'      => 'team-invitations',
			'type'       => 'object',
			'properties' => array(
				'team_member_invitation_id' => array(
					'description' => esc_html__( 'The id for the team invitation.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'team_id'                   => array(
					'description' => esc_html__( 'The team id for the team invitation.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'invitation_type'           => array(
					'description' => esc_html__( 'Indicates whether the invitation is via the user or email address for the team invitation.', 'tournamatch' ),
					'type'        => 'string',
					'enum'        => array( 'user', 'email' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => 'user',
				),
				'user_id'                   => array(
					'description' => esc_html__( 'The user id for the team invitation.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'user_email'                => array(
					'description' => esc_html__( 'The user email for the team invitation.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'invited_at'                => array(
					'description' => esc_html__( 'The datetime the team request was created for the team invitation.', 'tournamatch' ),
					'type'        => 'object',
					'trn-subtype' => 'datetime',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'The datetime the team invitation was created for the team invitation, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'The datetime the team invitation was created for the team invitation, transformed for display.', 'tournamatch' ),
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

new Team_Invitation();
