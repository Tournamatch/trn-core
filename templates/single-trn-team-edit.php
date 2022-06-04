<?php
/**
 * The template that displays the edit action for a single team.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$team_id = intval( get_query_var( 'id' ) );

$team = trn_get_team( $team_id );
if ( is_null( $team ) ) {
	wp_safe_redirect( trn_route( 'teams.archive' ) );
	exit;
}

$can_edit = false;
if ( current_user_can( 'manage_tournamatch' ) && ( 0 !== $team_id ) ) {
	$can_edit = true;
} elseif ( is_user_logged_in() ) {
	$team_owner = trn_get_team_owner( $team_id );
	$can_edit   = ( intval( $team_owner->id ) === get_current_user_id() );
}

if ( ! $can_edit ) {
	wp_safe_redirect( trn_route( 'teams.archive' ) );
	exit;
}

$team_fields      = apply_filters( 'trn_team_fields', array() );
$team_icon_fields = apply_filters( 'trn_team_icon_fields', array() );

get_header();

trn_get_header();

?>
	<h1 class="mb-4"><?php esc_html_e( 'Edit Team Profile', 'tournamatch' ); ?></h1>
	<form id="trn-edit-team-profile-form" action="#" method="post" enctype="multipart/form-data" data-team-id="<?php echo intval( $team_id ); ?>">
		<div class="form-group row">
			<label for="name" class="control-label col-sm-3"><?php esc_html_e( 'Team Name', 'tournamatch' ); ?></label>
			<div class="col-sm-3">
				<input type="text" id="name" name="name" class="form-control" value="<?php echo esc_html( $team->name ); ?>" required>
			</div>
		</div>
		<div class="form-group row">
			<label for="tag" class="control-label col-sm-3"><?php esc_html_e( 'Team Tag', 'tournamatch' ); ?></label>
			<div class="col-sm-1">
				<input type='text' id="tag" name='tag' value='<?php echo esc_html( $team->tag ); ?>' class="form-control" maxlength='5' style="width: 110%">
			</div>
		</div>
		<div class="form-group row">
			<label for="flag" class="control-label col-sm-3"><?php esc_html_e( 'Flag', 'tournamatch' ); ?></label>
			<div class="col-sm-4">
				<select id="flag" name="flag" class="form-control">
					<option value="blank.gif" <?php echo ( 'blank.gif' === $team->flag ) ? 'selected' : ''; ?>><?php esc_html_e( 'No Flag', 'tournamatch' ); ?></option>
					<?php foreach ( trn_get_flag_list() as $flag => $flag_name ) : ?>
						<option value="<?php echo esc_html( $flag ); ?>" <?php echo ( $flag === $team->flag ) ? 'selected' : ''; ?>><?php echo esc_html( $flag_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php foreach ( $team_icon_fields as $field_id => $field_data ) : ?>
			<div class="form-group row">
				<label for="<?php echo esc_html( $field_id ); ?>" class="control-label col-sm-3"><?php echo esc_html( $field_data['display_name'] ); ?></label>
				<div class="col-sm-4">
					<input class="form-control" type="<?php echo esc_html( $field_data['input_type'] ); ?>" id="<?php echo esc_html( $field_id ); ?>" name="<?php echo esc_html( $field_id ); ?>" value="<?php echo esc_html( get_post_meta( $team->post_id, $field_id, true ) ); ?>">
				</div>
			</div>
		<?php endforeach; ?>
		<?php foreach ( $team_fields as $field_id => $field_data ) : ?>
			<div class="form-group row">
				<label for="<?php echo esc_html( $field_id ); ?>" class="control-label col-sm-3"><?php echo esc_html( $field_data['display_name'] ); ?></label>
				<div class="col-sm-4">
					<input class="form-control" type="<?php echo esc_html( $field_data['input_type'] ); ?>" id="<?php echo esc_html( $field_id ); ?>" name="<?php echo esc_html( $field_id ); ?>" value="<?php echo esc_html( get_post_meta( $team->post_id, $field_id, true ) ); ?>">
				</div>
			</div>
		<?php endforeach; ?>
		<div class="form-group row">
			<label for="avatar" class="control-label col-sm-3"><?php esc_html_e( 'Picture', 'tournamatch' ); ?></label>
			<div class="col-sm-9">
				<input type='file' id="avatar" name="avatar" value='<?php echo esc_url( $team->avatar ); ?>' class="form-control-file">
				<small class="form-text text-muted"><?php esc_html_e( 'Only choose file if you wish to change your avatar.', 'tournamatch' ); ?></small>
				<?php trn_display_avatar( $team->team_id, 'teams', $team->avatar ); ?>
			</div>
		</div>
		<div class="form-group row">
			<div class="offset-sm-3 col-sm-4">
				<input id="trn-save-button" type="submit" class="btn btn-primary" value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>">
			</div>
		</div>
	</form>
	<div id="trn-update-response"></div>
<?php

$options = [
	'api_url'            => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'         => wp_create_nonce( 'wp_rest' ),
	'avatar_upload_path' => trn_upload_url() . '/images/avatars/',
	'language'           => array(
		'failure'         => esc_html__( 'Error', 'tournamatch' ),
		'failure_message' => esc_html__( 'Could not update team profile.', 'tournamatch' ),
		'success'         => esc_html__( 'Success', 'tournamatch' ),
		'success_message' => esc_html__( 'Team profile updated.', 'tournamatch' ),
	),
];

wp_register_script( 'update-team-profile', plugins_url( '../dist/js/update-team-profile.js', __FILE__ ), array( 'tournamatch' ), '3.16.0', true );
wp_localize_script( 'update-team-profile', 'trn_team_profile_options', $options );
wp_enqueue_script( 'update-team-profile' );

trn_get_footer();

get_footer();
