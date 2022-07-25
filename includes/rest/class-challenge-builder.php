<?php
/**
 * Manages Tournamatch REST endpoint for a challenge builder.
 *
 * @link  https://www.tournamatch.com
 * @since 3.21.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\Can_Create_Ladder_Challenges;
use Tournamatch\Rules\Ladder_Challenges_Enabled;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for a challenge builder.
 *
 * @since 3.21.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Challenge_Builder extends Controller {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.21.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.21.0
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/challenge-builder/(?P<ladder_id>[\d]+)',
			array(
				'args'   => array(
					'ladder_id' => array(
						'description' => esc_html__( 'Ladder id for the challenge.' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Checks if a given request has access to create a challenge.
	 *
	 * @since 3.21.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $request['ladder_id'] ) );
		if ( ! $ladder ) {
			return new \WP_Error( 'rest_custom_error', esc_html__( 'Ladder does not exist.', 'tournamatch' ), array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Retrieves a single challenge builder item.
	 *
	 * @since 3.21.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		global $wpdb;

		$user_id     = get_current_user_id();
		$ladder_id   = $request['ladder_id'];
		$ladder      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $ladder_id ) );
		$competitors = array();

		$rules = array(
			new Ladder_Challenges_Enabled( $ladder_id ),
			new Can_Create_Ladder_Challenges( $ladder_id, $user_id ),
		);

		$this->verify_business_rules( $rules );

		if ( 'players' === $ladder->competitor_type ) {
			$challenger  = $wpdb->get_row( $wpdb->prepare( "SELECT `p`.`display_name` AS `competitor_name`, `p`.`user_id` AS `competitor_id` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `p`.`user_id` = `le`.`competitor_id` WHERE `p`.`user_id` = %d AND `le`.`ladder_id` = %d LIMIT 1", $user_id, $ladder_id ) );
			$challengers = array( $challenger );

			if ( 'enabled' === $ladder->direct_challenges ) {
				$competitors = $wpdb->get_results( $wpdb->prepare( "SELECT `p`.`display_name` AS `competitor_name`, `p`.`user_id` AS `competitor_id` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `p`.`user_id` = `le`.`competitor_id` WHERE `p`.`user_id` != %d AND `le`.`ladder_id` = %d ORDER BY `p`.`display_name` ASC", $user_id, $ladder_id ) );
			}
		} else {
			$challengers = $wpdb->get_results( $wpdb->prepare( "SELECT `t`.`name` AS `competitor_name`, `t`.`team_id` AS `competitor_id` FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `t`.`team_id` = `le`.`competitor_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`team_id` = `t`.`team_id` WHERE `tm`.`team_rank_id` = %d AND `tm`.`user_id` = %d AND `le`.`ladder_id` = %d", 1, $user_id, $ladder_id ) );

			if ( 'enabled' === $ladder->direct_challenges ) {
				$competitors = $wpdb->get_results( $wpdb->prepare( "SELECT `t`.`name` AS `competitor_name`, `t`.`team_id` AS `competitor_id` FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `t`.`team_id` = `le`.`competitor_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`team_id` = `t`.`team_id` WHERE `tm`.`team_rank_id` = %d AND `tm`.`user_id` != %d AND `le`.`ladder_id` = %d ORDER BY `t`.`name` ASC", 1, $user_id, $ladder_id ) );
			}
		}

		$response = array(
			'ladder_id'   => $ladder_id,
			'challenger'  => $challengers,
			'competitors' => $competitors,
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Retrieves the challenge builder schema, conforming to JSON Schema.
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
			'title'      => 'challenge-builder',
			'type'       => 'object',
			'properties' => array(
				'ladder_id'   => array(
					'description' => esc_html__( 'The ladder id for the challenge.', 'tournamatch' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'challenger'  => array(
					'description' => esc_html__( 'The competitor for the challenge.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'competitors' => array(
					'description' => esc_html__( 'The competitors for the challenge.', 'tournamatch' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
			),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}

new Challenge_Builder();
