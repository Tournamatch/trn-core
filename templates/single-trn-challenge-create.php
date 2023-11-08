<?php
/**
 * The template that displays the create action for a single challenge.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if access directly.
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( trn_route( 'challenges.single.create' ) ) );
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$ladder_id = isset( $_REQUEST['ladder_id'] ) ? intval( $_REQUEST['ladder_id'] ) : 0;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$challengee_id = isset( $_REQUEST['challengee_id'] ) ? intval( $_REQUEST['challengee_id'] ) : 0;

$ladders = trn_get_user_ladders_with_challenges( get_current_user_id() );

if ( array_key_exists( $ladder_id, $ladders ) ) {
	$ladder = $ladders[ $ladder_id ];
} else {
	$ladder    = null;
	$ladder_id = 0;
}

get_header();

trn_get_header();

?>
	<h1 class="trn-mb-4"><?php esc_html_e( 'Create Challenge', 'tournamatch' ); ?></h1>
	<?php if ( 0 < count( $ladders ) ) : ?>
		<form id="trn-create-challenge-form" class="form-horizontal" action="#" method="post">
			<div class="trn-form-group">
				<label class="trn-col-sm-3" for="ladder_id"><?php esc_html_e( 'Ladder', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-4">
					<?php if ( isset( $ladder ) ) : ?>
						<p class="trn-form-control-static"><?php echo esc_html( $ladder->name ); ?></p>
						<input type="hidden" name="ladder_id" value="<?php echo intval( $ladder_id ); ?>">
					<?php else : ?>
						<select id="ladder_id" name="ladder_id" class="trn-form-control">
							<?php foreach ( $ladders as $ladder_option ) : ?>
								<option value="<?php echo intval( $ladder_option->id ); ?>"><?php echo esc_html( $ladder_option->name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</div>
			</div>
			<div class="trn-form-group d-none" id="trn-challenge-form-challenger-group">
				<label for="trn-challenge-form-challenger" class="trn-col-sm-3"><?php esc_html_e( 'Challenger', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-4" id="trn-challenge-form-challenger">
				</div>
			</div>
			<div class="trn-form-group d-none" id="trn-challenge-form-challengee-group">
				<label for="trn-challenge-form-challengee" class="trn-col-sm-3"><?php esc_html_e( 'Challengee', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-4" id="trn-challenge-form-challengee">
				</div>
			</div>
			<div class="trn-form-group d-none" id="trn-challenge-form-match-time-group">
				<label for="match_time_field"class="trn-col-sm-3"><?php esc_html_e( 'Match Time', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-4">
					<input id="match_time_field" name="match_time_field" type="datetime-local" required class="trn-form-control" autocomplete="off" disabled>
					<input id="match_time" name="match_time" type="hidden" >
				</div>
			</div>
			<?php if ( trn_is_plugin_active( 'trn-mycred' ) ) : ?>
				<div class="trn-form-group d-none" id="trn-challenge-form-mycred-wager-group">
					<label for="mycred_wager_amount" class="trn-col-sm-3"><?php esc_html_e( 'Wager', 'tournamatch' ); ?>:</label>
					<div class="trn-col-sm-4">
						<input id="mycred_wager_amount" name="mycred_wager_amount" type="number" required class="trn-form-control" autocomplete="off" disabled min="0">
					</div>
				</div>
			<?php endif; ?>
			<div class="trn-form-group">
				<div class="trn-col-sm-offset-3 trn-col-sm-9">
					<div id="trn-create-challenge-form-response"></div>
					<input type="submit" class="trn-button" id="trn-challenge-button" value="<?php esc_html_e( 'Challenge', 'tournamatch' ); ?>">
				</div>
			</div>
		</form>
	<?php else : ?>
		<p><?php esc_html_e( 'You are not participating on any ladders with challenges enabled.', 'tournamatch' ); ?></p>
	<?php endif; ?>
<?php

if ( 0 < count( $ladders ) ) {
	$options = array(
		'api_url'       => site_url( 'wp-json/tournamatch/v1/' ),
		'rest_nonce'    => wp_create_nonce( 'wp_rest' ),
		'ladder_id'     => $ladder_id,
		'challengee_id' => $challengee_id,
		'ladders'       => $ladders,
		'ladder'        => $ladder,
		'language'      => array(
			'failure'              => esc_html__( 'Error', 'tournamatch' ),
			'success'              => esc_html__( 'Success', 'tournamatch' ),
			'no_competitors_exist' => esc_html__( 'No competitors exist to challenge.', 'tournamatch' ),
		),
	);

	wp_register_script( 'trn-create-challenge', plugins_url( '../dist/js/create-challenge-form.js', __FILE__ ), array( 'tournamatch' ), '4.3.1', true );
	wp_localize_script( 'trn-create-challenge', 'trn_create_challenge_form_options', $options );
	wp_enqueue_script( 'trn-create-challenge' );
}

trn_get_footer();

get_footer();
