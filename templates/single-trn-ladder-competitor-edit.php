<?php
/**
 * The template for displaying the edit action for a single ladder competitor.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$ladder_entry_id = get_query_var( 'id' );

global $wpdb;

$competitor = trn_get_ladder_competitor( $ladder_entry_id );

if ( is_null( $competitor ) ) {
	wp_safe_redirect( trn_route( 'ladders.archive' ) );
	exit;
}

$ladder = trn_get_ladder( $competitor['ladder_id'] );

$points          = $competitor['points'];
$wins            = $competitor['wins'];
$losses          = $competitor['losses'];
$draws           = $competitor['draws'];
$streak          = $competitor['streak'];
$competitor_name = $competitor['competitor_name'];

get_header();

trn_get_header();

?>
	<h1 class="mb-4"><?php esc_html_e( 'Edit Competitor', 'tournamatch' ); ?></h1>
	<div id="trn-update-response"></div>
	<form method="post" id="trn-edit-competitor-form"
			data-ladder-competitor-id="<?php echo intval( $ladder_entry_id ); ?>">
		<div class="form-group row">
			<label class="col-sm-3 col-form-label"><?php esc_html_e( 'Competitor', 'tournamatch' ); ?>:</label>
			<div class="col-sm-2">
				<p class="form-control-static"><?php echo esc_html( $competitor_name ); ?></p>
			</div>
		</div>
		<div class="form-group row">
			<label for="points" class="col-sm-3 col-form-label"><?php esc_html_e( 'Points', 'tournamatch' ); ?>:</label>
			<div class="col-sm-2">
				<input type="text" class="form-control" id="points" name="points"
						value="<?php echo intval( $points ); ?>"/>
			</div>
		</div>
		<div class="form-group row">
			<label for="wins" class="col-sm-3 col-form-label"><?php esc_html_e( 'Wins', 'tournamatch' ); ?>:</label>
			<div class="col-sm-2">
				<input type="text" class="form-control" id="wins" name="wins"
						value="<?php echo intval( $wins ); ?>"/>
			</div>
		</div>
		<div class="form-group row">
			<label for="losses" class="col-sm-3 col-form-label"><?php esc_html_e( 'Losses', 'tournamatch' ); ?>:</label>
			<div class="col-sm-2">
				<input type="text" class="form-control" id="losses" name="losses"
						value="<?php echo intval( $losses ); ?>"/>
			</div>
		</div>
		<div class="form-group row">
			<label for="draws" class="col-sm-3 col-form-label"><?php esc_html_e( 'Draws', 'tournamatch' ); ?>:</label>
			<div class="col-sm-2">
				<input type="text" class="form-control" id="draws" name="draws"
						value="<?php echo intval( $draws ); ?>"/>
			</div>
		</div>
		<div class="form-group row">
			<label for="streak" class="col-sm-3 col-form-label"><?php esc_html_e( 'Streak', 'tournamatch' ); ?>:</label>
			<div class="col-sm-2">
				<input type="text" class="form-control" id="streak" name="streak"
						value="<?php echo esc_html( $streak ); ?>"/>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-6">
				<input type="submit" value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>"
						class="btn btn-primary"/>
			</div>
		</div>
	</form>
<?php

$options = array(
	'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce' => wp_create_nonce( 'wp_rest' ),
	'language'   => array(
		'failure'         => esc_html__( 'Error', 'tournamatch' ),
		'success'         => esc_html__( 'Success', 'tournamatch' ),
		'success_message' => esc_html__( 'The competitor has been updated.', 'tournamatch' ),
		'failure_message' => esc_html__( 'Could not update the competitor.', 'tournamatch' ),
	),
);

wp_register_script( 'trn-edit-competitor', plugins_url( '../dist/js/edit-competitor.js', __FILE__ ), array( 'tournamatch' ), '3.23.0', true );
wp_localize_script( 'trn-edit-competitor', 'trn_edit_competitor_options', $options );
wp_enqueue_script( 'trn-edit-competitor' );

trn_get_footer();

get_footer();
