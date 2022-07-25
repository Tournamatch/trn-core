<?php
/**
 * The template that displays the create action for a single team.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( trn_route( 'teams.single.create' ) ) );
	exit;
}

get_header();

trn_get_header();

$user_id = get_current_user_id();
if ( '1' === trn_get_option( 'one_team_per_player' ) ) {
	$limit_rule = new Tournamatch\Rules\One_Team_Per_User( $user_id );
	$can_create = $limit_rule->passes();
} else {
	$can_create = true;
}

?>
	<h1 class="trn-mb-4"><?php esc_html_e( 'Create Team', 'touranmatch' ); ?></h1>
<?php

if ( ! $can_create ) :
	?>
	<p><?php esc_html_e( 'You may not create another team until you leave your current team.', 'tournamatch' ); ?></p>
<?php else : ?>
	<form class="form-horizontal needs-validation" id="trn-create-team-form" novalidate>
		<div class="trn-form-group">
			<label for="trn-team-name" class="trn-col-sm-3"><?php esc_html_e( 'Team Name', 'tournamatch' ); ?>:</label>
			<div class="trn-col-sm-3">
				<input type="text" id="trn-team-name" name="trn-team-name" class="trn-form-control" maxlength="25" required>
				<div class="trn-invalid-feedback" id="trn-team-name-error"><?php esc_html_e( 'Team name is required.', 'tournamatch' ); ?></div>
			</div>
		</div>
		<div class="trn-form-group">
			<label for="trn-team-tag" class="trn-col-sm-3"><?php esc_html_e( 'Team Tag', 'tournamatch' ); ?>:</label>
			<div class="trn-col-sm-3">
				<input type="text" id="trn-team-tag" name="trn-team-tag" class="trn-form-control" maxlength="5" required>
				<div class="trn-invalid-feedback" id="trn-team-tag-error"><?php esc_html_e( 'Team tag is required.', 'tournamatch' ); ?></div>
			</div>
		</div>
		<div class="trn-form-group">
			<div class="trn-col-sm-offset-3 trn-col-sm-6">
				<button type="submit" class="trn-button" id="trn-create-team-button"><?php esc_html_e( 'Create Team', 'tournamatch' ); ?></button>
			</div>
		</div>
	</form>
	<?php

	$options = array(
		'api_url'                     => site_url( 'wp-json/tournamatch/v1/teams/' ),
		'rest_nonce'                  => wp_create_nonce( 'wp_rest' ),
		'team_name_required_message'  => esc_html__( 'Team name is required.', 'tournamatch' ),
		'team_name_duplicate_message' => esc_html__( 'Team name already exists', 'tournamatch' ),
	);
	wp_register_script( 'trn_create_team', plugins_url( '../dist/js/create-team.js', __FILE__ ), array( 'tournamatch' ), '3.21.1', true );
	wp_localize_script( 'trn_create_team', 'trn_create_team_options', $options );
	wp_enqueue_script( 'trn_create_team' );

endif;

trn_get_footer();

get_footer();
