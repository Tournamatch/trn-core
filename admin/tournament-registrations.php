<?php
/**
 * Handles the admin tournament registration page.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $wpdb;

check_admin_referer( 'tournamatch-bulk-tournaments' );

$tournament_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
if ( is_null( $tournament_id ) ) {
	echo '<meta http-equiv="refresh" content="0;url=' . esc_url( trn_route( 'admin.tournaments' ) ) . '">';
	exit;
}

$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $tournament_id ) );

?>
	<style type="text/css">
		#trn-tournament-register-form .form-field input, #trn-tournament-register-form .form-field select {
			width: 25em;
		}
		@media screen and (max-width: 782px) {
			#trn-tournament-register-form .form-field input, #trn-tournament-register-form .form-field select {
				width: 100%;
			}
		}

		.trn-auto-complete {
			/*the container must be positioned relative:*/
			position: relative;
			display: inline-block; }

		.trn-auto-complete-items {
			position: absolute;
			border: 1px solid #ced4da;
			border-radius: 0.25rem;
			z-index: 99;
			/*position the auto complete items to be the same width as the container:*/
			top: 100%;
			left: 0;
			right: 0; }

		.trn-auto-complete-items div {
			padding: 10px;
			cursor: pointer;
			background-color: #fff; }

		.trn-auto-complete-items div:hover {
			/*when hovering an item:*/
			background-color: #e9e9e9; }

		.trn-auto-complete-active {
			/*when navigating through the items using the arrow keys:*/
			background-color: DodgerBlue !important;
			color: #ffffff; }
	</style>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Tournament Registration', 'tournamatch' ); ?></h1>
		<hr class="wp-header-end">

		<div id="trn-tournament-register-response"></div>
		<form id="trn-tournament-register-form" autocomplete="off">
			<table class="form-table" role="presentation">
				<tr class="form-field">
					<th scope="row">
						<label for="tournament_name"><?php esc_html_e( 'Tournament', 'tournamatch' ); ?></label>
					</th>
					<td>
						<p><?php echo esc_html( $tournament->name ); ?></p>
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row">
						<label for="competitor_id"><?php esc_html_e( 'Player Name', 'tournamatch' ); ?></label>
					</th>
					<td class="trn-auto-complete">
						<input type="text" id="competitor_id" name="competitor_id" class="form-control" required>
					</td>
				</tr>
			<?php if ( 'teams' === $tournament->competitor_type ) : ?>
				<tr class="form-field form-required">
					<th scope="row">
						<label for="new_or_existing"><?php esc_html_e( 'New or Existing Team?', 'tournamatch' ); ?></label>
					</th>
					<td>
						<select id="new_or_existing" name="new_or_existing">
							<option value="new"><?php esc_html_e( 'New Team', 'tournamatch' ); ?></option>
							<option value="existing"><?php esc_html_e( 'Existing Team', 'tournamatch' ); ?></option>
						</select>
					</td>
				</tr>
				<tr class="form-field" id="existing_row">
					<th scope="row">
						<label for="existing_team"><?php esc_html_e( 'Select Existing Team', 'tournamatch' ); ?></label>
					</th>
					<td>
						<select id="existing_team" name="existing_team"></select>
					</td>
				</tr>
				<tr class="form-field" id="tag_row">
					<th scope="row">
						<label for="team_tag"><?php esc_html_e( 'Team Tag', 'tournamatch' ); ?></label>
					</th>
					<td>
						<input type="text" id="team_tag" name="team_tag" maxlength="5" required>
					</td>
				</tr>
				<tr class="form-field" id="name_row">
					<th scope="row">
						<label for="team_name"><?php esc_html_e( 'Team Name', 'tournamatch' ); ?></label>
					</th>
					<td>
						<input type="text" id="team_name" name="team_name" required>
					</td>
				</tr>
			<?php endif; ?>
			</table>
			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Register', 'tournamatch' ); ?></button>
			</p>
		</form>
	</div>
<?php

$options = [
	'api_url'       => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'    => wp_create_nonce( 'wp_rest' ),
	'tournament_id' => $tournament_id,
	'competition'   => ( 'players' === $tournament->competitor_type ) ? 'players' : 'teams',
	'language'      => array(
		'failure'         => esc_html__( 'Error', 'tournamatch' ),
		'success'         => esc_html__( 'Success', 'tournamatch' ),
		'success_message' => esc_html__( 'Competitor was added to the tournament.', 'tournamatch' ),
		'no_competitor'   => esc_html__( 'Competitor was not found.', 'tournamatch' ),
		'result'          => esc_html__( 'Success', 'tournamatch' ),
		'result_message'  => esc_html__( 'Competitors remaining in the Email Addresses input could not be found. Competitors on the right have been registered.', 'tournamatch' ),
		'zero_teams'      => esc_html__( 'No existing teams', 'tournamatch' ),
	),
];

wp_register_script( 'trn-tournament-registration', plugins_url( '../dist/js/tournament-registration.js', __FILE__ ), array( 'tournamatch' ), '3.24.0', true );
wp_localize_script( 'trn-tournament-registration', 'trn_tournament_registration_options', $options );
wp_enqueue_script( 'trn-tournament-registration' );

