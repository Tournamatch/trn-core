<?php
/**
 * Manages Tournamatch REST endpoint for teams.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\One_Team_Per_User;
use Tournamatch\Rules\Team_Name_Required;
use Tournamatch\Rules\Unique_Team_Name;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for teams.
 *
 * @since      3.8.0
 * @since      3.21.0 Updated to use WordPress API class.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Team extends Controller {

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
	 * @since 3.11.0 Added the DELETE endpoint.
	 * @since 3.16.0 Added the PATCH endpoint.
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/teams/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/teams/(?P<id>\d+)',
			array(
				'args' => array(
					'id' => array(
						'description' => esc_html__( 'Unique identifier for the team.' ),
						'type'        => 'integer',
						'required'    => true,
						'minimum'     => 1,
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => function () {
						return true;
					},
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_tournamatch' );
					},
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
			)
		);
	}

	/**
	 * Retrieves a single Team item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		global $wpdb;

		$team = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $request['id'] ) );

		$item = $this->prepare_item_for_response( $team, $request );

		$response = rest_ensure_response( $item );

		return $response;
	}

	/**
	 * Handles deleting a team.
	 *
	 * @since 3.11.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete( \WP_REST_Request $request ) {
		$params  = $request->get_params();
		$team_id = $params['id'];

		trn_deleted_team( $team_id );

		return new \WP_REST_Response(
			array(
				'message' => __( 'The team was deleted.', 'tournamatch' ),
				'data'    => array(
					'status' => 204,
				),
			),
			204
		);
	}

	/**
	 * Evaluates whether the user has permission to get team items.
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
	 * Retrieves a collection of teams.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $wpdb;

		$total_data = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams`" );

		$sql = "SELECT * FROM `{$wpdb->prefix}trn_teams` WHERE 1 = 1";
		if ( ! empty( $request['search'] ) ) {
			$sql .= $wpdb->prepare( ' AND (`name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `joined_date` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
			$sql .= $wpdb->prepare( ' OR `members` LIKE %s)', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
		}

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filtered = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns  = array( 'name', 'joined_date', 'members' );
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
		$response->header( 'TRN-Filtered', intval( $total_filtered ) );

		return $response;
	}


	/**
	 * Check if a given request has access to create a team.
	 *
	 * @since 3.18.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return is_user_logged_in();
	}

	/**

	 * Handles creating a new team request.
	 *
	 * @since 3.8.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function create( \WP_REST_Request $request ) {
		global $wpdb;

		$params    = $request->get_params();
		$team_name = $params['name'];
		$team_tag  = $params['tag'];
		$user_id   = isset( $params['user_id'] ) ? $params['user_id'] : get_current_user_id();

		// Verify authorization.

		$rules = array(
			new Team_Name_Required( $team_name ),
			new Unique_Team_Name( $team_name ),
		);

		$options = get_option( 'tournamatch_options' );
		if ( is_array( $options ) && isset( $options['one_team_per_player'] ) && ( '1' === $options['one_team_per_player'] ) ) {
			$rules[] = new One_Team_Per_User( $user_id );
		}

		// Verify business rules.
		$this->verify_business_rules( $rules );

		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_teams` (`team_id`, `tag`, `name`, `flag`, `joined_date`, `members`) VALUES (NULL, %s, %s, 'blank.gif', UTC_TIMESTAMP(), 1)", $team_tag, $team_name ) );
		$team_id = $wpdb->insert_id;
		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_teams_members` (`team_member_id`, `team_id`, `user_id`, `joined_date`, `team_rank_id`) VALUES (NULL, %d, %d, UTC_TIMESTAMP(), 1)", $team_id, $user_id ) );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The team was created', 'tournamatch' ),
				'data'    => array(
					'status'        => 200,
					'redirect_link' => trn_route( 'teams.single', array( 'id' => $team_id ) ),
					'team_id'       => $team_id,
				),
			),
			200
		);
	}

	/**
	 * Check if a given request has access to update items.
	 *
	 * @since 3.16.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		global $wpdb;

		$team = $wpdb->get_row( $wpdb->prepare( "SELECT team_id AS team_id FROM {$wpdb->prefix}trn_teams_members WHERE `team_id` = %d AND `user_id` = %d AND `team_rank_id` = 1", $request->get_param( 'id' ), get_current_user_id() ) );

		return ( ! is_null( $team ) ) || current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single team profile.
	 *
	 * @since 3.16.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$team_id = $request->get_param( 'id' );
		$team    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $team_id ) );

		$this->verify_business_rules(
			[
				new Unique_Team_Name( $request->get_param( 'name' ), $team_id ),
			]
		);

		$data   = array();
		$fields = array( 'tag', 'name', 'flag' );

		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				if ( (string) $team->$field !== $request->get_param( $field ) ) {
					$data[ $field ] = $request->get_param( $field );
					$team->$field   = $request->get_param( $field );
				}
			}
		}

		$files = $request->get_file_params();
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $file ) {
				$file_pieces   = explode( '.', $file['name'] );
				$pic_extension = end( $file_pieces );
				if ( in_array( $pic_extension, trn_get_option( 'allowed_extensions' ), true ) ) {
					$avatar_directory = trn_upload_dir() . '/images/avatars/';
					$new_pic          = uniqid() . '.' . $pic_extension;

					// move this to permanent location.
					if ( move_uploaded_file( $file['tmp_name'], $avatar_directory . $new_pic ) ) {
						// remove the old file.
						if ( strlen( $team->avatar ) > 0 ) {
							if ( file_exists( $team->avatar ) ) {
								unlink( $team->avatar );
							} elseif ( file_exists( $avatar_directory . $team->avatar ) ) {
								unlink( $avatar_directory . $team->avatar );
							}
						}

						$data['avatar'] = $new_pic;
						$team->avatar   = $new_pic;
					}
				}
			}
		}

		if ( 0 < count( $data ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_teams', $data, array( 'team_id' => $team_id ) );
		}

		return rest_ensure_response( $team );
	}


	/**
	 * Prepares a single team item for response.
	 *
	 * @since 3.21.0
	 *
	 * @param Object           $team Team object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $team, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'team_id', $fields ) ) {
			$data['team_id'] = (int) $team->team_id;
		}

		if ( rest_is_field_included( 'name', $fields ) ) {
			$data['name'] = $team->name;
		}

		if ( rest_is_field_included( 'joined_date', $fields ) ) {
			$data['joined_date'] = array(
				'raw'      => $team->joined_date,
				'rendered' => date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $team->joined_date ) ) ),
			);
		}

		if ( rest_is_field_included( 'avatar', $fields ) ) {
			$data['avatar'] = $team->avatar;
		}

		if ( rest_is_field_included( 'flag', $fields ) ) {
			$data['flag'] = $team->flag;
		}

		if ( rest_is_field_included( 'wins', $fields ) ) {
			$data['wins'] = (int) $team->wins;
		}

		if ( rest_is_field_included( 'losses', $fields ) ) {
			$data['losses'] = (int) $team->losses;
		}

		if ( rest_is_field_included( 'draws', $fields ) ) {
			$data['draws'] = (int) $team->draws;
		}

		if ( rest_is_field_included( 'members', $fields ) ) {
			$data['members'] = (int) $team->members;
		}

		if ( rest_is_field_included( 'link', $fields ) ) {
			$data['link'] = trn_route( 'teams.single', array( 'id' => $team->team_id ) );
		}

		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $team );
		$response->add_links( $links );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 3.21.0
	 *
	 * @param Object $team Team object.
	 *
	 * @return array Links for the given team.
	 */
	protected function prepare_links( $team ) {
		$base = "{$this->namespace}/teams";

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $team->team_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		return $links;
	}

	/**
	 * Retrieves the team schema, conforming to JSON Schema.
	 *
	 * @since 3.19.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$properties = array(
			'team_id'     => array(
				'description' => esc_html__( 'The id for the team.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'tag'         => array(
				'description' => esc_html__( 'The display tag for the team.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
				'required'    => true,
			),
			'name'        => array(
				'description' => esc_html__( 'The display name for the team.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
				'required'    => true,
			),
			'flag'        => array(
				'description' => esc_html__( 'The display flag for the team.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'joined_date' => array(
				'description' => esc_html__( 'The datetime the team was created.', 'tournamatch' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit', 'embed' ),
				'properties'  => array(
					'raw'      => array(
						'description' => esc_html__( 'Joined Date for the team, as it exists in the database.', 'tournamatch' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'view', 'edit', 'embed' ),
					),
					'rendered' => array(
						'description' => esc_html__( 'Joined Date for the object, transformed for display.', 'tournamatch' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
				),
			),
			'avatar'      => array(
				'description' => esc_html__( 'The avatar for the teams.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'wins'        => array(
				'description' => esc_html__( 'The number of win match for a team.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'losses'      => array(
				'description' => esc_html__( 'The number of loss match for team.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'draws'       => array(
				'description' => esc_html__( 'The number of draws for a team.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'members'     => array(
				'description' => esc_html__( 'The number of members of a team.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'link'        => array(
				'description' => esc_html__( 'URL to the team.' ),
				'type'        => 'string',
				'format'      => 'uri',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
		);

		$icon_fields = apply_filters( 'trn_team_icon_fields', array() );
		foreach ( $icon_fields as $field_id => $field_data ) {
			$properties[ $field_id ] = array(
				/* translators: The name of the field. */
				'description' => sprintf( esc_html__( 'The %s field for the team.', 'tournamatch' ), $field_id ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			);
		}

		$team_fields = apply_filters( 'trn_team_fields', array() );
		foreach ( $team_fields as $field_id => $field_data ) {
			$properties[ $field_id ] = array(
				/* translators: The name of the field. */
				'description' => sprintf( esc_html__( 'The %s field for the team.', 'tournamatch' ), $field_id ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			);
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'teams',
			'type'       => 'object',
			'properties' => $properties,
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

}

new Team();
