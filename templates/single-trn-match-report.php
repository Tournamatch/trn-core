<?php
/**
 * The template that displays the report action for a single match.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$match_id = get_query_var( 'id' );

if ( ! trn_can_report_match( get_current_user_id(), $match_id ) ) {
	wp_safe_redirect( trn_route( 'matches.single', array( 'id' => $match_id ) ) );
	exit;
}

$user_id = get_current_user_id();
$match   = trn_get_match( $match_id );

// competition data.
if ( 'ladders' === $match->competition_type ) {
	$competition      = trn_get_ladder( $match->competition_id );
	$competition_type = 'Ladder';
	$competition_slug = 'ladder_id';
} else {
	$competition      = trn_get_tournament( $match->competition_id );
	$competition_type = 'Tournament';
	$competition_slug = 'tournament_id';
}

// user and opponent data.
if ( 'players' === $competition->competitor_type ) {
	$competitor_id = $user_id;
	$opponent_id   = ( intval( $user_id ) === intval( $match->one_competitor_id ) ) ? $match->two_competitor_id : $match->one_competitor_id;
	$opponent      = trn_get_player( $opponent_id );
} else {
	if ( 'ladders' === $match->competition_type ) {
		$my_teams = trn_get_user_ladder_teams( $user_id, $match->competition_id );
	} else {
		$my_teams = trn_get_user_tournament_teams( $user_id, $match->competition_id );
	}
	$my_teams      = array_column( $my_teams, 'team_id' );
	$competitor_id = in_array( $match->one_competitor_id, $my_teams, true ) ? $match->one_competitor_id : $match->two_competitor_id;
	$opponent_id   = ( $competitor_id === $match->one_competitor_id ) ? $match->two_competitor_id : $match->one_competitor_id;
	$opponent      = trn_get_team( $opponent_id );
}

$opponent = array(
	'competitor_id'   => $opponent_id,
	'competitor_name' => $opponent->name,
);

$args = array(
	'competition_id'    => $match->competition_id,
	'competition_type'  => $competition_type,
	'competition_name'  => $competition->name,
	'competition_slug'  => $competition_slug,
	'opponents'         => $opponent,
	'competitor_id'     => $competitor_id,
	'competitor_type'   => $competition->competitor_type,
	'uses_draws'        => trn_get_option( 'uses_draws' ),
	'match_id'          => $match_id,
	'one_competitor_id' => $match->one_competitor_id,
	'mode'              => 'save',
);

if ( 'teams' === $competition->competitor_type ) {
	if ( 'ladders' === $match->competition_type ) {
		$args['my_teams'] = trn_get_user_ladder_teams( $user_id, $match->competition_id );
	} else {
		$args['my_teams'] = trn_get_user_tournament_teams( $user_id, $match->competition_id );
	}
}


get_header();

trn_get_header();

match_form( $args );

trn_get_footer();

get_footer();
