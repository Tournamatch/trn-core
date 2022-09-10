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
if ( is_user_logged_in() && ( ( get_current_user_id() === intval( $user_id ) ) || is_null( $user_id ) ) ) {
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

$form_fields = array(
	'display_name'     => array(
		'id'       => 'display_name',
		'label'    => __( 'Display Name', 'tournamatch' ),
		'required' => true,
		'type'     => 'text',
		'value'    => isset( $player->display_name ) ? $player->display_name : '',
	),
	'location'         => array(
		'id'    => 'location',
		'label' => __( 'Location', 'tournamatch' ),
		'type'  => 'text',
		'value' => isset( $player->location ) ? $player->location : '',
	),
	'flag'             => array(
		'id'      => 'flag',
		'label'   => __( 'Country Flag', 'tournamatch' ),
		'type'    => 'select',
		'value'   => isset( $player->flag ) ? $player->flag : 'blank.gif',
		'options' => trn_get_flag_options(),
	),
	'avatar'           => array(
		'id'          => 'avatar',
		'label'       => __( 'Avatar', 'tournamatch' ),
		'type'        => 'thumbnail',
		'description' => __( 'Only choose file if you wish to change your avatar.', 'tournamatch' ),
		'value'       => isset( $player->avatar ) ? $player->avatar : '',
		'thumbnail'   => function( $context ) {
			trn_display_avatar( $context->user_id, 'players', $context->avatar );
		},
	),
	'banner'           => array(
		'id'          => 'banner',
		'label'       => __( 'Banner', 'tournamatch' ),
		'type'        => 'thumbnail',
		'description' => __( 'Only choose file if you wish to change your banner.', 'tournamatch' ),
		'value'       => isset( $player->banner ) ? $player->banner : '',
		'thumbnail'   => function( $context ) {
			if ( 0 < strlen( $context->banner ) ) {
				echo '<img width="400" height="100" class="trn-profile-edit-banner" src="' . esc_attr( trn_upload_url() . '/images/avatars/' . $context->banner ) . '"/>"';
			}
		},
	),
	'profile'          => array(
		'id'    => 'profile',
		'label' => __( 'Profile', 'tournamatch' ),
		'type'  => 'textarea',
		'value' => isset( $player->profile ) ? $player->profile : '',
	),
	'new_password'     => array(
		'id'          => 'new_password',
		'label'       => __( 'New password', 'tournamatch' ),
		'type'        => 'password',
		'description' => __( 'Only enter if you wish to change your password.', 'tournamatch' ),
	),
	'confirm_password' => array(
		'id'          => 'confirm_password',
		'label'       => __( 'Confirm password', 'tournamatch' ),
		'type'        => 'password',
		'description' => __( 'Only enter if you wish to change your password.', 'tournamatch' ),
	),
);

$form = array(
	'attributes' => array(
		'id'             => 'trn-edit-player-profile-form',
		'action'         => '#',
		'method'         => 'post',
		'enctype'        => 'multipart/form-data',
		'data-player-id' => intval( $user_id ),
	),
	'fields'     => $form_fields,
	'submit'     => array(
		'id'      => 'trn-save-button',
		'content' => __( 'Save', 'tournamatch' ),
	),
);

get_header();

trn_get_header();

?>
	<h1 class="trn-mb-4"><?php esc_html_e( 'Edit Profile', 'tournamatch' ); ?></h1>
	<?php trn_user_form( $form, $player ); ?>
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
