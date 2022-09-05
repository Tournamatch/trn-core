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

if ( ! current_user_can( 'manage_tournamatch' ) ) {
	wp_safe_redirect( trn_route( 'ladders.archive' ) );
	exit;
}

global $wpdb;

$competitor = trn_get_ladder_competitor( $ladder_entry_id );

if ( is_null( $competitor ) ) {
	wp_safe_redirect( trn_route( 'ladders.archive' ) );
	exit;
}

$ladder = trn_get_ladder( $competitor->ladder_id );
$ladder = trn_the_ladder( $ladder );

$form_fields = array(
	'name'                      => array(
		'id'    => 'name',
		'label' => __( 'Competitor', 'tournamatch' ),
		'type'  => 'static',
		'value' => $competitor->competitor_name,
	),
	$ladder->ranking_mode_field => array(
		'id'    => $ladder->ranking_mode_field,
		'label' => $ladder->ranking_mode_label,
		'type'  => 'number',
		'value' => isset( $competitor->{$ladder->ranking_mode_field} ) ? $competitor->{$ladder->ranking_mode_field} : '',
	),
	'wins'                      => array(
		'id'    => 'wins',
		'label' => __( 'Wins', 'tournamatch' ),
		'type'  => 'number',
		'value' => isset( $competitor->wins ) ? $competitor->wins : '',
	),
	'losses'                    => array(
		'id'    => 'losses',
		'label' => __( 'Losses', 'tournamatch' ),
		'type'  => 'number',
		'value' => isset( $competitor->losses ) ? $competitor->losses : '',
	),
	'streak'                    => array(
		'id'    => 'streak',
		'label' => __( 'Streak', 'tournamatch' ),
		'type'  => 'number',
		'value' => isset( $competitor->streak ) ? $competitor->streak : '',
	),
);

if ( trn_get_option( 'uses_draws' ) ) {
	$form_fields = trn_array_merge_after_key(
		$form_fields,
		'losses',
		array(
			'draws' => array(
				'id'    => 'draws',
				'label' => __( 'Draws', 'tournamatch' ),
				'type'  => 'number',
				'value' => isset( $competitor->draws ) ? $competitor->draws : '',
			),
		)
	);
}

$form = array(
	'attributes' => array(
		'id'                        => 'trn-edit-competitor-form',
		'action'                    => '#',
		'method'                    => 'post',
		'data-ladder-competitor-id' => intval( $ladder_entry_id ),
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
	<h1 class="trn-mb-4"><?php esc_html_e( 'Edit Competitor', 'tournamatch' ); ?></h1>
	<?php trn_user_form( $form, $competitor ); ?>
	<div id="trn-update-response"></div>
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
