<?php
/**
 * Manages Tournamatch REST endpoint for team members.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\Must_Promote_Before_Leaving;
use Tournamatch\Rules\One_User_Per_Team;
use Tournamatch\Rules\Team_Rank_Maxed;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for team members.
 *
 * @since      3.8.0
 * @since      3.19.0 Refactored to implement Controller class.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Team_Member extends Controller {

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
	 * @since 3.25.0 Replaced the /leave and /drop endpoints with DELETE /$id.
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/team-members/',
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
			'/team-members/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the object.' ),
						'type'        => 'integer',
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
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::DELETABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Checks if a given request has access to update a team member.
	 *
	 * @since 3.25.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		global $wpdb;

		$team_member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_member_id` = %d", $request['id'] ) );
		if ( ! $team_member ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team member does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$team_owner = $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT `tm`.* 
FROM `{$wpdb->prefix}trn_teams_members` AS `tm`
  LEFT JOIN `{$wpdb->prefix}trn_teams_ranks` AS `tr` ON `tr`.`team_rank_id` = `tm`.`team_rank_id`
WHERE `tm`.`team_id` = %d 
  AND `tr`.`weight` = %d",
				$team_member->team_id,
				1
			)
		);

		// The owner may edit the rank, and the admin may edit anything.
		return ( get_current_user_id() === (int) $team_owner->user_id ) || current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single team member item.
	 *
	 * @since 3.25.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$team_member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_member_id` = %d", $request['id'] ) );
		if ( ! $team_member ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team member does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		// Verify business rules.
		$this->verify_business_rules(
			array(
				new Team_Rank_Maxed( $team_member->team_id, $request['team_rank_id'] ),
			)
		);

		// Prevent over POSTing by team owner.
		if ( ! current_user_can( 'manage_tournamatch' ) ) {
			unset( $request['wins'] );
			unset( $request['losses'] );
			unset( $request['draws'] );
		}

		$schema = $this->get_item_schema();

		$data = array();

		if ( ! empty( $schema['properties']['team_rank_id'] ) && isset( $request['team_rank_id'] ) ) {
			/*
			In the team ranks table, the ranks defined with team_id = -1 are the global rank definitions. This is to eventually
			accommodate teams creating their own rank.
			*/
			$new_rank = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_ranks` WHERE `team_rank_id` = %d", $request['team_rank_id'] ) );

			if ( '1' === $new_rank->weight ) {
				// Promoting another member to team owner also means demoting the owner.
				$old_owner_new_rank_id = $team_member->team_rank_id;

				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_members` SET `team_rank_id` = %d WHERE `team_id` = %d AND `team_rank_id` = %d", $old_owner_new_rank_id, $team_member->team_id, $request['team_rank_id'] ) );
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_members` SET `team_rank_id` = %d WHERE `team_member_id` = %d", $request['team_rank_id'], $team_member->team_member_id ) );
			} else {
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_members` SET `team_rank_id` = %d WHERE `team_member_id` = %d", $request['team_rank_id'], $team_member->team_member_id ) );
			}
		}
		if ( ! empty( $schema['properties']['wins'] ) && isset( $request['wins'] ) ) {
			$data['wins'] = $request['wins'];
		}
		if ( ! empty( $schema['properties']['wins'] ) && isset( $request['losses'] ) ) {
			$data['losses'] = $request['wins'];
		}
		if ( ! empty( $schema['properties']['draws'] ) && isset( $request['draws'] ) ) {
			$data['draws'] = $request['draws'];
		}

		if ( 0 < count( $data ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_teams_members', $data, array( 'id' => $request['id'] ) );
		}

		$team_member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_member_id` = %d", $team_member->team_member_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $team_member, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to read team-members.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Handles returning team members.
	 *
	 * @since 3.8.0
	 * @since 3.19.0 Renamed from get_all to get_items.
	 * @since 3.25.0 Updated to support player filter, data tables response, and add links.
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array|object
	 */
	public function get_items( $request ) {
		global $wpdb;

		$player_id = isset( $request['player_id'] ) ? intval( $request['player_id'] ) : null;
		$team_id   = isset( $request['team_id'] ) ? intval( $request['team_id'] ) : null;

		$total_data = "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams_members` AS `tm` WHERE 1 = 1 ";
		if ( ! is_null( $player_id ) ) {
			$total_data .= $wpdb->prepare( 'AND `tm`.`user_id` = %d ', $player_id );
		}
		if ( ! is_null( $team_id ) ) {
			$total_data .= $wpdb->prepare( 'AND `tm`.`team_id` = %d ', $team_id );
		}
		$total_data = $wpdb->get_var( $total_data ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$sql = "
SELECT 
  `tm`.*,
  `tr`.`title` AS `title`,
  `t`.`name` AS `name`,
  `p`.`display_name` AS `player`
FROM `{$wpdb->prefix}trn_teams_members` AS `tm`
  LEFT JOIN `{$wpdb->prefix}trn_teams_ranks` AS `tr` ON `tm`.`team_rank_id` = `tr`.`team_rank_id`
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t` ON `t`.`team_id` = `tm`.`team_id`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p` ON `p`.`user_id` = `tm`.`user_id`
WHERE 1 = 1 ";

		if ( ! is_null( $player_id ) ) {
			$sql .= $wpdb->prepare( 'AND `tm`.`user_id` = %d ', $player_id );
		}
		if ( ! is_null( $team_id ) ) {
			$sql .= $wpdb->prepare( 'AND `tm`.`team_id` = %d ', $team_id );
		}

		if ( ! empty( $request['search'] ) ) {
			$sql .= $wpdb->prepare( ' AND (`t`.`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `joined_date` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `tr`.`title` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `members` LIKE %s)', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
		}

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filter = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns  = array(
				'name'        => 'name',
				'player'      => 'player',
				'title'       => 'weight',
				'joined_date' => 'joined_date',
				'members'     => 'members',
			);
			$order_by = explode( '.', $request['orderby'] );

			if ( ( 2 === count( $order_by ) && in_array( $order_by[0], array_keys( $columns ), true ) ) ) {
				$direction = ( 'desc' === $order_by[1] ) ? 'desc' : 'asc';
				$column    = $columns[ $order_by[0] ];

				$sql .= " ORDER BY `$column` $direction";
			}
		} else {
			$sql .= ' ORDER BY `name` DESC';
		}

		if ( isset( $request['per_page'] ) && ( '-1' !== $request['per_page'] ) ) {
			$length = $request['per_page'] ?: 10;
			$start  = $request['page'] ? ( $request['page'] * $length ) : 0;
			$sql   .= $wpdb->prepare( ' LIMIT %d, %d', $start, $length );
		}

		$teams = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$items = array();
		foreach ( $teams as $team ) {
			$data    = $this->prepare_item_for_response( $team, $request );
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
	 * Check if a given request has access to create a team-member.
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
	 * Creates a single game item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item( $request ) {
		global $wpdb;

		$team_id = $request->get_param( 'team_id' );
		$user_id = $request->get_param( 'user_id' );

		$this->verify_business_rules(
			array(
				new One_User_Per_Team( $team_id, $user_id ),
			)
		);

		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_teams_members` (`team_member_id`, `team_id`, `user_id`, `joined_date`, `team_rank_id`) VALUES (NULL, %d, %d, UTC_TIMESTAMP(), 2)", $team_id, $user_id ) );

		$team_member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_member_id` = %d", $wpdb->insert_id ) );

		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET `members` = `members` + 1 WHERE `team_id` = %d", $team_id ) );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $team_member, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Checks if a given request has access to delete a team member.
	 *
	 * @since 3.25.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		global $wpdb;

		$team_member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_member_id` = %d", $request['id'] ) );
		if ( ! $team_member ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team member does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		$team_owner = $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT `tm`.* 
FROM `{$wpdb->prefix}trn_teams_members` AS `tm` 
  LEFT JOIN `{$wpdb->prefix}trn_teams_ranks` AS `tr` ON `tr`.`team_rank_id` = `tm`.`team_rank_id`
WHERE `tm`.`team_id` = %d 
  AND `tr`.`weight` = %d",
				$team_member->team_id,
				1
			)
		);

		// The user may leave a team, the owner may drop a member, and the admin may drop a member.
		return ( get_current_user_id() === (int) $team_owner->user_id ) || ( get_current_user_id() === (int) $team_member->user_id ) || current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Deletes a single team member.
	 *
	 * @since 3.25.0
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		global $wpdb;

		$team_member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_member_id` = %d", $request['id'] ) );
		if ( ! $team_member ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Team member does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		// Verify business rules.
		$this->verify_business_rules(
			array(
				new Must_Promote_Before_Leaving( $team_member->team_id, $team_member->user_id ),
			)
		);

		$members = $wpdb->get_var( $wpdb->prepare( "SELECT `members` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $team_member->team_id ) );

		if ( '1' === $members ) {
			trn_deleted_team( $team_member->team_id );
		} else {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET `members` = `members` - 1 WHERE `team_id` = %d", $team_member->team_id ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `user_id` = %d", $team_member->team_id, $team_member->user_id ) );
		}

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The team member was deleted.', 'tournamatch' ),
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
	 * @since 3.25.0
	 *
	 * @param Object $competitor Team member object.
	 *
	 * @return array Links for the given team member.
	 */
	protected function prepare_links( $competitor ) {
		$base = "{$this->namespace}/team-members";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $competitor->team_member_id ),
			),
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

		$links['rank'] = array(
			'href'       => rest_url( "{$this->namespace}/team-ranks/{$competitor->team_rank_id}" ),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Retrieves the team-member schema, conforming to JSON Schema.
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
			'title'      => 'team-members',
			'type'       => 'object',
			'properties' => array(
				'team_member_id' => array(
					'description' => esc_html__( 'The id for the team member.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'team_id'        => array(
					'description' => esc_html__( 'The team id for the team member.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'user_id'        => array(
					'description' => esc_html__( 'The user id for the team member.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'joined_date'    => array(
					'description' => esc_html__( 'The datetime the player joined the team for the team member.', 'tournamatch' ),
					'type'        => 'object',
					'trn-subtype' => 'datetime',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => esc_html__( 'The datetime the joined the team for the team member, as it exists in the database.', 'tournamatch' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'rendered' => array(
							'description' => esc_html__( 'The datetime the joined the team for the team member, transformed for display.', 'tournamatch' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'wins'           => array(
					'description' => esc_html__( 'The total wins the team member.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'losses'         => array(
					'description' => esc_html__( 'The total losses the team member.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'draws'          => array(
					'description' => esc_html__( 'The total draws the team member.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'team_rank_id'   => array(
					'description' => esc_html__( 'The rank id for the team member.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

}

new Team_Member();
