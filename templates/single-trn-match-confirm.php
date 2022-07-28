<?php
/**
 * The template that displays the confirm action for a single match.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$match_id = get_query_var( 'id' );

if ( ! trn_can_confirm_match( get_current_user_id(), $match_id ) ) {
	wp_safe_redirect( trn_route( 'matches.single', array( 'id' => $match_id ) ) );
	exit;
}

$match = trn_get_match( $match_id );

if ( is_null( $match ) || ( 'reported' !== $match->match_status ) ) {
	wp_safe_redirect( trn_route( 'report.page' ) );
	exit;
}

if ( 'ladders' === $match->competition_type ) {
	$competition      = trn_get_ladder( $match->competition_id );
	$competition_type = esc_html__( 'Ladder', 'tournamatch' );
} else {
	$competition      = trn_get_tournament( $match->competition_id );
	$competition_type = esc_html__( 'Tournament', 'tournamatch' );
}

if ( 'players' === $competition->competitor_type ) {
	$one_competitor = trn_get_player( $match->one_competitor_id );
	$two_competitor = trn_get_player( $match->two_competitor_id );
} else {
	$one_competitor = trn_get_team( $match->one_competitor_id );
	$two_competitor = trn_get_team( $match->two_competitor_id );
}

if ( ( 'won' === $match->one_result ) || ( 'lost' === $match->two_result ) ) {
	$winner = $one_competitor->name;
	$loser  = $two_competitor->name;
} else {
	$winner = $two_competitor->name;
	$loser  = $one_competitor->name;
}

get_header();

trn_get_header();

?>
	<h4><?php esc_html_e( 'Confirm Match', 'tournamatch' ); ?></h4>
	<form id="trn-confirm-match-form" action="#" method="post" enctype="multipart/form-data" >
		<div class="trn-form-group trn-row">
			<label for="competition_name" class="trn-col-sm-3"><?php echo esc_html( $competition_type ); ?></label>
			<div class="trn-col-sm-4">
				<p class="trn-form-control-static"><?php echo esc_html( $competition->name ); ?></p>
			</div>
		</div>
		<?php if ( in_array( 'draw', array( $match->one_result, $match->two_result ), true ) ) : ?>
			<div class="trn-form-group trn-row">
				<label for="result" class="trn-col-sm-3"><?php esc_html_e( 'Result', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-6">
					<p class="trn-form-control-static"><?php esc_html_e( 'Draw', 'tournamatch' ); ?></p>
				</div>
			</div>
		<?php else : ?>
			<div class="trn-form-group trn-row">
				<label for="result" class="trn-col-sm-3"><?php esc_html_e( 'Winner', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-6">
					<p class="trn-form-control-static"><?php echo esc_html( $winner ); ?></p>
				</div>
			</div>
			<div class="trn-form-group trn-row">
				<label for="result" class="trn-col-sm-3"><?php esc_html_e( 'Loser', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-6">
					<p class="trn-form-control-static"><?php echo esc_html( $loser ); ?></p>
				</div>
			</div>
		<?php endif; ?>
		<div class="trn-form-group trn-row">
			<label for="comment" class="trn-col-sm-3"><?php esc_html_e( 'Comment', 'tournamatch' ); ?>:</label>
			<div class="trn-col-sm-6">
				<textarea class="trn-form-control" id="comment" name='comment' cols='30' trn-rows='5'></textarea>
			</div>
		</div>
		<div class="trn-form-group trn-row">
			<div class="offset-sm-3 trn-col-sm-9">
				<input type='hidden' name='id' value='<?php intval( $match_id ); ?>'>
				<input type='submit' id="trn-confirm-match-button" class="trn-button" value='<?php esc_html_e( 'Confirm', 'tournamatch' ); ?>'>
			</div>
		</div>
	</form>
<?php

if ( in_array( 'draw', array( $match->one_result, $match->two_result ), true ) ) {
	$result_to_confirm = 'draw';
} elseif ( in_array( 'won', array( $match->one_result, $match->two_result ), true ) ) {
	$result_to_confirm = 'lost';
} else {
	$result_to_confirm = 'won';
}

$options = array(
	'api_url'           => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'        => wp_create_nonce( 'wp_rest' ),
	'redirect_link'     => trn_route( 'report.page' ),
	'side_to_confirm'   => ( 0 < strlen( $match->one_result ) ) ? 'two' : 'one',
	'result_to_confirm' => $result_to_confirm,
	'match_id'          => $match->match_id,
	'language'          => array(
		'success' => esc_html__( 'Error', 'tournamatch' ),
		'failure' => esc_html__( 'Error', 'tournamatch' ),
	),
);

wp_register_script( 'trn-confirm-match', plugins_url( '../dist/js/confirm-match.js', __FILE__ ), array( 'tournamatch' ), '3.19.0', true );
wp_localize_script( 'trn-confirm-match', 'trn_confirm_match_options', $options );
wp_enqueue_script( 'trn-confirm-match' );

trn_get_footer();

get_footer();
