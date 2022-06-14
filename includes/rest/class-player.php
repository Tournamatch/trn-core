<?php
/**
 * Manages Tournamatch REST endpoint for players.
 *
 * @link       https://www.tournamatch.com
 * @since      3.15.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Rest;

use Tournamatch\Rules\Unique_Player_Name;
use Tournamatch\Rules\Password_Must_Match;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for players.
 *
 * @since      3.15.0
 * @since      3.19.0 Refactored to support native WordPress REST behavior.
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Player extends Controller {

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
	 * @since 3.16.0 Added PATCH for player with id.
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/players/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/players/(?P<id>\d+)/challenges',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_player_challenges' ),
				'permission_callback' => '__return_true',
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
			'/players/(?P<id>\d+)/teams',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_player_teams' ),
				'permission_callback' => '__return_true',
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
			'/players/(?P<id>\d+)',
			array(
				'args' => array(
					'id' => array(
						'required' => true,
						'type'     => 'integer',
						'minimum'  => 1,
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Checks if a given request has access to read players.
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
	 * Retrieves a collection of players.
	 *
	 * @since 3.19.0
	 * @since 3.21.0 Updated to follow get_items WordPress REST API pattern.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $wpdb;

		$total_data = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_players_profiles`" );

		$sql = "
SELECT 
  `p`.`user_id`, 
  `p`.`display_name`, 
  `p`.`location`, 
  `p`.`flag`, 
  `p`.`wins`, 
  `p`.`losses`, 
  `p`.`draws`, 
  `p`.`profile`, 
  `p`.`avatar`, 
  (SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams_members` AS `tm` WHERE `tm`.`user_id` = `p`.`user_id`) AS `teams`, 
  `u`.`user_registered` AS `joined_date` 
FROM `{$wpdb->prefix}trn_players_profiles` AS `p` 
  LEFT JOIN `{$wpdb->users}` AS `u` ON `p`.`user_id` = `u`.`ID` 
WHERE 1 = 1 ";

		if ( ! empty( $request['search'] ) ) {
			$sql .= $wpdb->prepare( ' AND `p`.`display_name` LIKE %s', '%' . $wpdb->esc_like( $request['search'] ) . '%' );
		}

		if ( ! empty( $request['name'] ) ) {
			$sql .= $wpdb->prepare( ' AND `p`.`display_name` = %s', $request['name'] );
		}

		$wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_filtered = $wpdb->num_rows;

		if ( ! empty( $request['orderby'] ) ) {
			$columns  = array( 'display_name', 'joined_date', 'locations', 'teams' );
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

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$players = array();

		foreach ( $results as $result ) {
			$data      = $this->prepare_item_for_response( $result, $request );
			$players[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $players );

		$response->header( 'X-WP-Total', intval( $total_data ) );
		$response->header( 'X-WP-TotalPages', 1 );
		$response->header( 'TRN-Draw', intval( $request['draw'] ) );
		$response->header( 'TRN-Filtered', intval( $total_filtered ) );

		return $response;
	}

	/**
	 * Check if a given request has access to get a player.
	 *
	 * @since 3.21.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves a single player.
	 *
	 * @since 3.21.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		global $wpdb;

		$player = $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  `p`.`user_id`, 
  `p`.`display_name`, 
  `p`.`location`, 
  `p`.`flag`, 
  `p`.`wins`, 
  `p`.`losses`, 
  `p`.`draws`, 
  `p`.`profile`, 
  `p`.`avatar`, 
  (SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams_members` AS `tm` WHERE `tm`.`user_id` = `p`.`user_id`) AS `teams`, 
  `u`.`user_registered` AS `joined_date` 
FROM `{$wpdb->prefix}trn_players_profiles` AS `p` 
  LEFT JOIN `{$wpdb->users}` AS `u` ON `p`.`user_id` = `u`.`ID` 
WHERE `user_id` = %d
",
				$request['id']
			)
		);

		$item = $this->prepare_item_for_response( $player, $request );

		$response = rest_ensure_response( $item );

		return $response;
	}

	/**
	 * Handles returning player challenges.
	 *
	 * @since 3.15.0
	 *
	 * @param \WP_REST_Request $request Contains data for the REST request.
	 *
	 * @return array|object
	 */
	public function get_player_challenges( \WP_REST_Request $request ) {
		global $wpdb;

		$user_id      = get_current_user_id();
		$player_teams = $wpdb->get_results( $wpdb->prepare( "SELECT team_id FROM {$wpdb->prefix}trn_teams_members WHERE user_id = %d", $user_id ) );
		$player_teams = array_column( $player_teams, 'team_id' );

		if ( 0 === count( $player_teams ) ) {
			$challenges = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT 
				  c.*, 
				  l.name, 
				  pcr.display_name AS challenger_name, 
				  pce.display_name AS challengee_name
				FROM `{$wpdb->prefix}trn_challenges` AS c 
				  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS l ON l.ladder_id = c.ladder_id 
				  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS pcr ON pcr.user_id = c.challenger_id AND l.competitor_type = 'players'
				  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS pce ON pce.user_id = c.challengee_id AND l.competitor_type = 'players'
				WHERE 
				  c.accepted_state = 'pending' AND
			      (l.competitor_type = 'players' AND (c.challenger_id = %d OR c.challengee_id = %d)) 
			",
					$user_id,
					$user_id
				)
			);
		} else {
			$challenges = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT 
				  c.*, 
				  l.name, 
				  IF(pcr.display_name IS NULL, tcr.name, pcr.display_name) AS challenger_name, 
				  IF(pce.display_name IS NULL, tce.name, pce.display_name) AS challengee_name
				FROM `{$wpdb->prefix}trn_challenges` AS c 
				  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS l ON l.ladder_id = c.ladder_id 
				  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS pcr ON pcr.user_id = c.challenger_id AND l.competitor_type = 'players'
				  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS pce ON pce.user_id = c.challengee_id AND l.competitor_type = 'players'
				  LEFT JOIN `{$wpdb->prefix}trn_teams` AS tcr ON tcr.team_id = c.challenger_id AND l.competitor_type = 'teams'
				  LEFT JOIN `{$wpdb->prefix}trn_teams` AS tce ON tce.team_id = c.challengee_id AND l.competitor_type = 'teams'			  
				WHERE 
				  c.accepted_state = 'pending' AND
				    (
					 (l.competitor_type = 'players' AND (c.challenger_id = %d OR c.challengee_id = %d)) OR 
					 (l.competitor_type = 'teams' AND (c.challenger_id IN (SELECT team_id FROM {$wpdb->prefix}trn_teams_members WHERE user_id = %d) OR c.challengee_id IN (SELECT team_id FROM {$wpdb->prefix}trn_teams_members WHERE user_id = %d)))
					)
			",
					$user_id,
					$user_id,
					$user_id,
					$user_id
				)
			);
		}

		return $challenges;
	}

	/**
	 * Retrieves a collection of player's teams.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_player_teams( $request ) {
		global $wpdb;

		$limit = ( isset( $request['per_page'] ) && is_numeric( $request['per_page'] ) ) ? $request['per_page'] : 10;
		$start = ( isset( $request['page'] ) && is_numeric( $request['per_page'] ) ) ? $request['page'] : 1;
		$start = min( 0, $start - 1 );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT `t`.`team_id` AS `team_id`, `t`.`name` FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` WHERE `tm`.`user_id` = %d ORDER BY `name` ASC LIMIT %d, %d", $request['id'], $start, $limit ) );

		$response = rest_ensure_response( $results );

		return $response;
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
		return ( get_current_user_id() === absint( $request->get_param( 'id' ) ) ) || current_user_can( 'manage_tournamatch' );
	}

	/**
	 * Updates a single player profile.
	 *
	 * @since 3.16.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function update_item( $request ) {
		global $wpdb;

		$user_id = $request->get_param( 'id' );
		$player  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $user_id ) );

		$rules = array( new Unique_Player_Name( $request->get_param( 'display_name' ), $user_id ) );

		$new_password     = $request->get_param( 'new_password' );
		$confirm_password = $request->get_param( 'confirm_password' );

		if ( isset( $new_password ) ) {
			array_push( $rules, new Password_Must_Match( $new_password, $confirm_password ) );
		}

		$this->verify_business_rules( $rules );

		if ( 0 < strlen( trim( $new_password ) ) ) {
			wp_update_user(
				array(
					'ID'        => $user_id,
					'user_pass' => $new_password,
				)
			);
		}

		$data   = array();
		$fields = array( 'display_name', 'location', 'flag', 'profile' );

		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				if ( (string) $player->$field !== (string) $request->get_param( $field ) ) {
					$data[ $field ] = $request->get_param( $field );
					$player->$field = $request->get_param( $field );
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
						if ( strlen( $player->avatar ) > 0 ) {
							if ( file_exists( $player->avatar ) ) {
								unlink( $player->avatar );
							} elseif ( file_exists( $avatar_directory . $player->avatar ) ) {
								unlink( $avatar_directory . $player->avatar );
							}
						}

						$data['avatar'] = $new_pic;
						$player->avatar = $new_pic;
					}
				}
			}
		}

		if ( 0 < count( $data ) ) {
			$wpdb->update( $wpdb->prefix . 'trn_players_profiles', $data, array( 'user_id' => $user_id ) );
		}

		return rest_ensure_response( $player );
	}

	/**
	 * Prepares a single player item for response.
	 *
	 * @since 3.19.0
	 *
	 * @param Object           $player    Player object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $player, $request ) {

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'user_id', $fields ) ) {
			$data['user_id'] = (int) $player->user_id;
		}

		if ( rest_is_field_included( 'display_name', $fields ) ) {
			$data['name'] = $player->display_name;
		}

		if ( rest_is_field_included( 'joined_date', $fields ) ) {
			$joined_date         = get_user_by( 'id', $player->user_id )->data->user_registered;
			$data['joined_date'] = array(
				'raw'      => $joined_date,
				'rendered' => date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $joined_date ) ) ),
			);
		}

		if ( rest_is_field_included( 'location', $fields ) ) {
			$data['location'] = $player->location;
		}

		if ( rest_is_field_included( 'flag', $fields ) ) {
			$data['flag'] = $player->flag;
		}

		if ( rest_is_field_included( 'wins', $fields ) ) {
			$data['wins'] = (int) $player->wins;
		}

		if ( rest_is_field_included( 'losses', $fields ) ) {
			$data['losses'] = (int) $player->losses;
		}

		if ( rest_is_field_included( 'draws', $fields ) ) {
			$data['draws'] = (int) $player->draws;
		}

		if ( rest_is_field_included( 'profile', $fields ) ) {
			$data['profile'] = $player->profile;
		}

		if ( rest_is_field_included( 'avatar', $fields ) ) {
			$data['avatar'] = $player->avatar;
		}

		if ( rest_is_field_included( 'teams', $fields ) ) {
			$data['teams'] = $player->teams;
		}

		$icon_fields = apply_filters( 'trn_player_icon_fields', array() );
		foreach ( $icon_fields as $social_icon => $social_icon_data ) {
			if ( rest_is_field_included( "trn_$social_icon", $fields ) ) {
				$data[ "trn_$social_icon" ] = get_user_meta( $player->user_id, "trn_$social_icon", true );
			}
		}

		$player_fields = apply_filters( 'trn_player_fields', array() );
		foreach ( $player_fields as $field_id => $field_data ) {
			if ( rest_is_field_included( "trn_$field_id", $fields ) ) {
				$data[ "trn_$field_id" ] = get_user_meta( $player->user_id, "trn_$field_id", true );
			}
		}

		if ( rest_is_field_included( 'link', $fields ) ) {
			$data['link'] = trn_route( 'players.single', array( 'id' => $player->user_id ) );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Retrieves the players schema, conforming to JSON Schema.
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
			'user_id'      => array(
				'description' => esc_html__( 'The user id for the player.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'display_name' => array(
				'description' => esc_html__( 'The display name for the player.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
				'required'    => true,
			),
			'joined_date'  => array(
				'description' => esc_html__( 'The date the player registered on the website.', 'tournamatch' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
				'properties'  => array(
					'raw'      => array(
						'description' => esc_html__( 'The date the player registered on the website, as it exists in the database.', 'tournamatch' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'view', 'edit', 'embed' ),
					),
					'rendered' => array(
						'description' => esc_html__( 'The date the player registered on the website, transformed for display.', 'tournamatch' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
				),
			),
			'location'     => array(
				'description' => esc_html__( 'The location for the player.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'flag'         => array(
				'description' => esc_html__( 'The country flag for the player.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'wins'         => array(
				'description' => esc_html__( 'The number of individual wins for the player.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'losses'       => array(
				'description' => esc_html__( 'The number of individual losses for the player.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'draws'        => array(
				'description' => esc_html__( 'The number of individual draws for the player.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'profile'      => array(
				'description' => esc_html__( 'The long text bio for the player.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'avatar'       => array(
				'description' => esc_html__( 'The avatar for the player.', 'tournamatch' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'teams'        => array(
				'description' => esc_html__( 'The number of teams the player is a member of.', 'tournamatch' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'link'         => array(
				'description' => esc_html__( 'URL to the player.' ),
				'type'        => 'string',
				'format'      => 'uri',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
		);

		$icon_fields = apply_filters( 'trn_player_icon_fields', array() );
		foreach ( $icon_fields as $social_icon => $social_icon_data ) {
			$properties[ "trn_$social_icon" ] = array(
				/* translators: The name of a field. */
				'description' => sprintf( esc_html__( 'The %s field for the player.', 'tournamatch' ), $social_icon ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			);
		}

		$player_fields = apply_filters( 'trn_player_fields', array() );
		foreach ( $player_fields as $field_id => $field_data ) {
			$properties[ "trn_$field_id" ] = array(
				/* translators: The name of a field. */
				'description' => sprintf( esc_html__( 'The %s field for the player.', 'tournamatch' ), $field_id ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			);
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'players',
			'type'       => 'object',
			'properties' => $properties,
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

}

new Player();
