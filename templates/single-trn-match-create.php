<?php
/**
 * The template that displays the create action for a single match.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( trn_route( 'matches.single.create' ) ) );
	exit;
}

global $wpdb;

$user_id = get_current_user_id();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$ladder_id = isset( $_REQUEST['ladder_id'] ) ? intval( $_REQUEST['ladder_id'] ) : 0;

$ladder  = trn_get_ladder( $ladder_id );
$user_id = get_current_user_id();

// Verify this user is a member of this ladder.
$competitor = trn_get_user_ladder( $user_id, $ladder_id );
if ( is_null( $competitor ) ) {
	wp_safe_redirect( trn_route( 'ladders.single', array( 'id' => $ladder_id ) ) );
	exit;
}

$scheduled_matches = trn_get_scheduled_ladder_matches( $user_id, $ladder_id );
$opponents         = trn_get_ladder_competitors( $ladder_id );

if ( 'players' === $ladder->competitor_type ) {
	$opponents = array_filter(
		$opponents,
		function( $opponent ) use ( $user_id ) {
			return ( intval( $opponent->competitor_id ) !== intval( $user_id ) );
		}
	);
} else {
	$my_teams  = trn_get_user_ladder_teams( get_current_user_id(), $ladder_id );
	$my_teams  = array_column( $my_teams, 'team_id' );
	$opponents = array_filter(
		$opponents,
		function( $opponent ) use ( $my_teams ) {
			return ( ! in_array( $opponent->competitor_id, $my_teams, true ) );
		}
	);
}

$args = array(
	'competition_id'   => $ladder_id,
	'competition_type' => 'Ladder',
	'competition_name' => $ladder->name,
	'competition_slug' => 'ladder_id',
	'opponents'        => $opponents,
	'competitor_id'    => $competitor->competitor_id,
	'competitor_type'  => $ladder->competitor_type,
	'uses_draws'       => trn_get_option( 'uses_draws' ),
);

if ( 'teams' === $ladder->competitor_type ) {
	$args['my_teams'] = trn_get_user_ladder_teams( get_current_user_id(), $ladder_id );
}

get_header();

trn_get_header();

?>
<h1 class="trn-mb-4"><?php esc_html_e( 'Report Results', 'tournamatch' ); ?></h1>
<section>
	<h4><?php esc_html_e( 'Scheduled Matches', 'tournamatch' ); ?></h4>
	<?php trn_get_template_part( 'partials/scheduled-matches-table', '', array( 'scheduled_matches' => $scheduled_matches ) ); ?>
</section>
<?php if ( trn_get_option( 'open_play_enabled' ) ) : ?>
<section>
	<h4><?php esc_html_e( 'New Match', 'tournamatch' ); ?></h4>
	<?php match_form( $args ); ?>
</section>
<?php endif; ?>
<?php

trn_get_footer();

get_footer();
