<?php
/**
 * The template that displays challenge details.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$challenge_id = get_query_var( 'id' );

$challenge = trn_get_challenge( $challenge_id );

if ( is_null( $challenge ) ) {
	wp_safe_redirect( trn_route( 'challenges.archive' ) );
	exit;
}

if ( ( 'direct' === $challenge->challenge_type ) || ( 'accepted' === $challenge->accepted_state ) ) {
	$challenge->can_see_challenger = true;
} else {
	if ( 'players' === $challenge->competitor_type ) {
		$challenge->can_see_challenger = ( absint( $challenge->challenger_id ) === get_current_user_id() );
	} else {
		$teams                         = array_column( trn_get_user_teams( get_current_user_id() ), 'id' );
		$challenge->can_see_challenger = in_array( $challenge->challenger_id, $teams, true );
	}
}

get_header();

trn_get_header();
?>
<dl>
	<dt><?php esc_html_e( 'Ladder', 'tournamatch' ); ?>:</dt>
	<dd><?php echo esc_html( $challenge->ladder_name ); ?></dd>
	<dt><?php esc_html_e( 'Match Time', 'tournamatch' ); ?>:</dt>
	<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $challenge->match_time ) ) ) ); ?></dd>
	<dt><?php esc_html_e( 'Challenger', 'tournamatch' ); ?>:</dt>
	<dd id="trn-challenge-challenger">
		<?php
		if ( $challenge->can_see_challenger ) {
			echo esc_html( $challenge->challenger_name );
		} else {
			esc_html_e( '(hidden)', 'tournamatch' );
		}
		?>
	</dd>
	<dt><?php esc_html_e( 'Challengee', 'tournamatch' ); ?>:</dt>
	<dd id="trn-challenge-challengee">
		<?php
		if ( is_null( $challenge->challengee_id ) ) {
			esc_html_e( '(open)', 'tournamatch' );
		} else {
			echo esc_html( $challenge->challengee_name );
		}
		?>
	</dd>
	<dt><?php esc_html_e( 'Status', 'tournamatch' ); ?>:</dt>
	<dd id="trn-challenge-status"><?php echo esc_html( ucwords( $challenge->accepted_state ) ); ?></dd>
</dl>
<div id="trn-challenge-success-response"></div>
<div id="trn-challenge-failure-response"></div>
<div class="trn-text-center">
	<?php
	if ( trn_can_accept_challenge( get_current_user_id(), $challenge->challenge_id ) ) {
		echo do_shortcode( '[trn-accept-challenge-button id="' . intval( $challenge->challenge_id ) . '"]' );
	}
	if ( trn_can_decline_challenge( get_current_user_id(), $challenge->challenge_id ) ) {
		echo do_shortcode( '[trn-decline-challenge-button id="' . intval( $challenge->challenge_id ) . '"]' );
	}
	if ( trn_can_delete_challenge( get_current_user_id(), $challenge->challenge_id ) ) {
		echo do_shortcode( '[trn-delete-challenge-button id="' . intval( $challenge->challenge_id ) . '"]' );
	}
	?>
</div>

<?php

$options = array(
	'api_url'             => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'          => wp_create_nonce( 'wp_rest' ),
	'challenge_list_link' => trn_route( 'challenges.archive' ),
	'language'            => array(
		'failure'         => esc_html__( 'Error', 'tournamatch' ),
		'success'         => esc_html__( 'Success', 'tournamatch' ),
		'accepted'        => esc_html__( 'Accepted', 'tournamatch' ),
		'acceptedMessage' => esc_html__( 'The challenge was accepted.', 'tournamatch' ),
		'declined'        => esc_html__( 'Declined', 'tournamatch' ),
		'declinedMessage' => esc_html__( 'The challenge was declined.', 'tournamatch' ),
	),
);
wp_register_script( 'trn_single_challenge', plugins_url( '../dist/js/single-challenge.js', __FILE__ ), array( 'tournamatch' ), '3.27.0', true );
wp_localize_script( 'trn_single_challenge', 'trn_single_challenge_options', $options );
wp_enqueue_script( 'trn_single_challenge' );

trn_get_footer();

get_footer();
