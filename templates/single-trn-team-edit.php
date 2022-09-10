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

$form_fields = array(
	'name'   => array(
		'id'       => 'name',
		'label'    => __( 'Team Name', 'tournamatch' ),
		'required' => true,
		'type'     => 'text',
		'value'    => isset( $team->name ) ? $team->name : '',
	),
	'tag'    => array(
		'id'        => 'tag',
		'label'     => __( 'Team Tag', 'tournamatch' ),
		'required'  => true,
		'type'      => 'text',
		'maxlength' => 5,
		'value'     => isset( $team->tag ) ? $team->tag : '',
	),
	'flag'   => array(
		'id'      => 'flag',
		'label'   => __( 'Country Flag', 'tournamatch' ),
		'type'    => 'select',
		'value'   => isset( $team->flag ) ? $team->flag : 'blank.gif',
		'options' => trn_get_flag_options(),
	),
	'avatar' => array(
		'id'          => 'avatar',
		'label'       => __( 'Avatar', 'tournamatch' ),
		'type'        => 'thumbnail',
		'description' => __( 'Only choose file if you wish to change your avatar.', 'tournamatch' ),
		'value'       => isset( $team->avatar ) ? $team->avatar : '',
		'thumbnail'   => function( $context ) {
			trn_display_avatar( $context->team_id, 'teams', $context->avatar );
		},
	),
	'banner' => array(
		'id'          => 'banner',
		'label'       => __( 'Banner', 'tournamatch' ),
		'type'        => 'thumbnail',
		'description' => __( 'Only choose file if you wish to change your banner.', 'tournamatch' ),
		'value'       => isset( $team->banner ) ? $team->banner : '',
		'thumbnail'   => function( $context ) {
			if ( 0 < strlen( $context->banner ) ) {
				echo '<img width="400" height="100" class="trn-profile-edit-banner" src="' . esc_attr( trn_upload_url() . '/images/avatars/' . $context->banner ) . '"/>"';
			}
		},
	),
);

$form = array(
	'attributes' => array(
		'id'           => 'trn-edit-team-profile-form',
		'action'       => '#',
		'method'       => 'post',
		'enctype'      => 'multipart/form-data',
		'data-team-id' => intval( $team_id ),
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
	<h1 class="trn-mb-4"><?php esc_html_e( 'Edit Team Profile', 'tournamatch' ); ?></h1>
	<?php trn_user_form( $form, $team ); ?>
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
