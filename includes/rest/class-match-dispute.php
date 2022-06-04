<?php
/**
 * Manages Tournamatch REST endpoint for match disputes.
 *
 * @link  https://www.tournamatch.com
 * @since 3.19.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Rest;

// Exit if accessed directly.
use Tournamatch\Rules\Can_Dispute_Match;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Tournamatch REST endpoint for match disputes.
 *
 * @since 3.19.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Match_Dispute extends Controller {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Add REST endpoints.
	 *
	 * @since 3.19.0
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/match-disputes/',
			array(
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
	 * Check if a given request has access to create a match dispute.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		global $wpdb;

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $request->get_param( 'id' ) ) );

		if ( 'ladders' === $match->competition_type ) {
			$competition = $wpdb->get_var( $wpdb->prepare( "SELECT `competitor_type` FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $match->competition_id ) );
		} else {
			$competition = $wpdb->get_var( $wpdb->prepare( "SELECT `competitor_type` FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $match->competition_id ) );
		}

		$disputer_side = ( strlen( $match->one_result ) > 0 ) ? 'two_competitor_id' : 'one_competitor_id';

		if ( 'players' === $competition ) {
			return ( get_current_user_id() === (int) $match->$disputer_side );
		} else {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`team_id` = `t`.`team_id` WHERE `t`.`team_id` = %d AND `tm`.`user_id` = %d", $match->$disputer_side, get_current_user_id() ) );

			return ( 0 < $count );
		}
	}

	/**
	 * Creates a single match dispute item.
	 *
	 * @since 3.19.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool|\WP_REST_Response
	 */
	public function create_item( $request ) {
		global $wpdb;

		// business logic. can only dispute a match that is reported.
		$this->verify_business_rules(
			array(
				new Can_Dispute_Match( $request->get_param( 'id' ) ),
			)
		);

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $request->get_param( 'id' ) ) );

		if ( strlen( $match->one_result ) > 0 ) {
			$reporter_side = 'one_competitor_id';
			$disputer_side = 'two_competitor_id';

			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `two_result` = %s, `match_status` = %s WHERE `match_id` = %d", 'wrong', 'disputed', $request->get_param( 'id' ) ) );
		} else {
			$reporter_side = 'two_competitor_id';
			$disputer_side = 'one_competitor_id';

			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `one_result` = %s, `match_status` = %s WHERE `match_id` = %d", 'wrong', 'disputed', $request->get_param( 'id' ) ) );
		}

		if ( 'ladders' === $match->competition_type ) {
			$competition = $wpdb->get_var( $wpdb->prepare( "SELECT `competitor_type` FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $match->competition_id ) );
			$subject     = esc_html__( 'Ladder Result Disputed', 'tournamatch' );
		} else {
			$competition = $wpdb->get_var( $wpdb->prepare( "SELECT `competitor_type` FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $match->competition_id ) );
			$subject     = esc_html__( 'Tournament Result Disputed', 'tournamatch' );
		}

		if ( 'players' === $competition ) {
			$disputer_link                   = trn_route( 'players.single', [ 'id' => $match->$disputer_side ] );
			list($disputer, $disputer_email) = $wpdb->get_row( $wpdb->prepare( "SELECT `p`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $match->$disputer_side ), ARRAY_N );
			list($reporter, $reporter_email) = $wpdb->get_row( $wpdb->prepare( "SELECT `p`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $match->$reporter_side ), ARRAY_N );
			$reporter_link                   = trn_route( 'players.single', [ 'id' => $match->$reporter_side ] );
		} else {
			$disputer_link                   = trn_route( 'teams.single', [ 'id' => $match->$disputer_side ] );
			list($disputer, $disputer_email) = $wpdb->get_row( $wpdb->prepare( "SELECT `p`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `p`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `p`.`user_id` WHERE `tm`.`team_rank_id` = 1 AND `tm`.`team_id` = %d", $match->$disputer_side ), ARRAY_N );
			list($reporter, $reporter_email) = $wpdb->get_row( $wpdb->prepare( "SELECT `p`.`display_name`, `u`.`user_email` AS `email` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->users}` AS `u` ON `u`.`ID` = `p`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `p`.`user_id` WHERE `tm`.`team_rank_id` = 1 AND `tm`.`team_id` = %d", $match->$reporter_side ), ARRAY_N );
			$reporter_link                   = trn_route( 'teams.single', [ 'id' => $match->$reporter_side ] );
		}

		// Send email to the original user that reported the match.
		$data = [
			'disputer_link' => $disputer_link,
			'disputer'      => $disputer,
			'results_link'  => trn_route( 'matches.single', array( 'id' => $match->match_id ) ),
		];

		do_action(
			'trn_notify_match_disputed',
			[
				'email' => $reporter_email,
				'name'  => $reporter,
			],
			$subject,
			$data
		);

		// Send an email to the admin.
		$data = [
			'disputer_link' => $disputer_link,
			'disputer'      => $disputer,
			'disputee_link' => $reporter_link,
			'disputee'      => $reporter,
			'disputes_link' => trn_route( "admin.{$match->competition_type}.matches", array( 'status' => 'disputed' ) ),
		];

		do_action( 'trn_notify_admin_match_disputed', trn_get_option( 'admin_email' ), $subject, $data );

		return new \WP_REST_Response(
			array(
				'message' => esc_html__( 'The match has been disputed.', 'tournamatch' ),
				'data'    => array(
					'status' => 201,
				),
			),
			201
		);
	}

}

new Match_Dispute();
