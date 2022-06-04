<?php
/**
 * The template that displays the edit action for a single player.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$user_id = get_query_var( 'id', null );
if ( is_user_logged_in() && is_null( $user_id ) ) {
	$user_id = get_current_user_id();
} elseif ( ! current_user_can( 'manage_tournamatch' ) ) {
	wp_safe_redirect( trn_route( 'players.archive' ) );
	exit;
}

$player = trn_get_player( $user_id );
if ( is_null( $player ) ) {
	wp_safe_redirect( trn_route( 'players.archive' ) );
	exit;
}

$player_fields = apply_filters( 'trn_player_fields', array() );
$icon_fields   = apply_filters( 'trn_player_icon_fields', array() );

get_header();

trn_get_header();

?>
	<h1 class="mb-4"><?php esc_html_e( 'Edit Profile', 'tournamatch' ); ?></h1>
	<form action="#" method="post" enctype="multipart/form-data" id="trn-edit-player-profile-form" data-player-id="<?php echo intval( $user_id ); ?>">
		<div class="form-group row">
			<label for="display_name" class="control-label col-sm-3"><?php esc_html_e( 'Display Name', 'tournamatch' ); ?></label>
			<div class="col-sm-4">
				<input class="form-control" type="text" id="display_name" name="display_name" value="<?php echo esc_html( $player->display_name ); ?>">
			</div>
		</div>
		<div class="form-group row">
			<label for="location" class="control-label col-sm-3"><?php esc_html_e( 'Location', 'tournamatch' ); ?></label>
			<div class="col-sm-4">
				<input class="form-control" type="text" id="location" name="location" value="<?php echo esc_html( $player->location ); ?>">
			</div>
		</div>
		<div class="form-group row">
			<label for="flag" class="control-label col-sm-3"><?php esc_html_e( 'Country Flag', 'tournamatch' ); ?></label>
			<div class="col-sm-4">
				<select id="flag" name="flag" class="form-control">
					<option value="blank.gif" <?php echo ( 'blank.gif' === $player->flag ) ? 'selected' : ''; ?>><?php esc_html_e( 'No Flag', 'tournamatch' ); ?></option>
					<?php foreach ( trn_get_flag_list() as $flag => $flag_title ) : ?>
						<option value="<?php echo esc_html( $flag ); ?>" <?php echo ( $flag === $player->flag ) ? 'selected' : ''; ?>><?php echo esc_html( $flag_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php foreach ( $icon_fields as $social_icon => $social_icon_data ) : ?>
			<div class="form-group row">
				<label for="<?php echo esc_html( $social_icon ); ?>" class="control-label col-sm-3"><?php echo esc_html( $social_icon_data['display_name'] ); ?></label>
				<div class="col-sm-4">
					<input class="form-control" type="<?php echo esc_html( $social_icon_data['input_type'] ); ?>" id="<?php echo esc_html( $social_icon ); ?>" name="<?php echo esc_html( $social_icon ); ?>" value="<?php echo esc_html( get_user_meta( $user_id, "trn_$social_icon", true ) ); ?>">
				</div>
			</div>
		<?php endforeach; ?>
		<?php foreach ( $player_fields as $field_id => $field_data ) : ?>
			<div class="form-group row">
				<label for="<?php echo esc_html( $field_id ); ?>" class="control-label col-sm-3"><?php echo esc_html( $field_data['display_name'] ); ?></label>
				<div class="col-sm-4">
					<input class="form-control" type="<?php echo esc_html( $field_data['input_type'] ); ?>" id="<?php echo esc_html( $field_id ); ?>" name="<?php echo esc_html( $field_id ); ?>" value="<?php echo esc_html( get_user_meta( $user_id, "trn_$field_id", true ) ); ?>">
				</div>
			</div>
		<?php endforeach; ?>
		<div class="form-group row">
			<label for="avatar" class="control-label col-sm-3"><?php esc_html_e( 'Avatar', 'tournamatch' ); ?></label>
			<div class="col-sm-9">
				<input class="form-control-file" type="file" id="avatar" name="avatar" value="<?php echo esc_url( $player->avatar ); ?>">
				<small class="form-text text-muted"><?php esc_html_e( 'Only choose file if you wish to change your avatar.', 'tournamatch' ); ?></small>
				<?php trn_display_avatar( $player->user_id, 'players', $player->avatar ); ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="profile" class="control-label col-sm-3"><?php esc_html_e( 'Profile', 'tournamatch' ); ?></label>
			<div class="col-sm-6">
				<textarea class="form-control" rows="10" id="profile" name="profile"><?php echo wp_kses_post( $player->profile ); ?></textarea>
			</div>
		</div>
		<div class="form-group row">
			<label for="new_password" class="control-label col-sm-3"><?php esc_html_e( 'New Password', 'tournamatch' ); ?></label>
			<div class="col-sm-4">
				<input class="form-control" type="password" id="new_password" name="new_password">
				<small class="form-text text-muted"><?php esc_html_e( 'Only enter if you wish to change your password.', 'tournamatch' ); ?></small>
			</div>
		</div>
		<div class="form-group row">
			<label for="confirm_password" class="control-label col-sm-3"><?php esc_html_e( 'Confirm Password', 'tournamatch' ); ?></label>
			<div class="col-sm-4">
				<input class="form-control" type="password" id="confirm_password" name="confirm_password">
				<small class="form-text text-muted"><?php esc_html_e( 'Only enter if you wish to change your password.', 'tournamatch' ); ?></small>
			</div>
		</div>
		<div class="form-group row">
			<div class="offset-sm-3 col-sm-4">
				<input id="trn-save-button" class="btn btn-primary" type="submit" value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>">
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
		'failure_message' => esc_html__( 'Could not update player profile.', 'tournamatch' ),
		'success'         => esc_html__( 'Success', 'tournamatch' ),
		'success_message' => esc_html__( 'Player profile updated.', 'tournamatch' ),
	),
];

wp_register_script( 'update-player-profile', plugins_url( '../dist/js/update-player-profile.js', __FILE__ ), array( 'tournamatch' ), '3.16.0', true );
wp_localize_script( 'update-player-profile', 'trn_player_profile_options', $options );
wp_enqueue_script( 'update-player-profile' );

trn_get_footer();

get_footer();
