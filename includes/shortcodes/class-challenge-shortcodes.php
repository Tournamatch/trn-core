<?php
/**
 * Defines Tournamatch challenge shortcodes.
 *
 * @link       https://www.tournamatch.com
 * @since      3.15.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Shortcodes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines Tournamatch challenge shortcodes.
 *
 * @since      3.15.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Challenge_Shortcodes {

	/**
	 * Sets up our handler to register our endpoints.
	 *
	 * @since 3.15.0
	 */
	public function __construct() {
		add_shortcode( 'trn-accept-challenge-button', array( $this, 'accept_challenge_button' ) );
		add_shortcode( 'trn-decline-challenge-button', array( $this, 'decline_challenge_button' ) );
		add_shortcode( 'trn-delete-challenge-button', array( $this, 'delete_challenge_button' ) );
	}

	/**
	 * Shortcode to create a challenge accept button.
	 *
	 * @since 3.15.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function accept_challenge_button( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'language'   => array(
				'error' => __( 'An error occurred.', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_accept_challenge_button', plugins_url( '../../dist/js/accept-challenge-button.js', __FILE__ ), array( 'tournamatch' ), '3.27.0', true );
		wp_localize_script( 'trn_accept_challenge_button', 'trn_accept_challenge_button_options', $options );
		wp_enqueue_script( 'trn_accept_challenge_button' );

		return '<a href="#" id="trn-accept-test" class="trn-button trn-accept-challenge-button trn-mx-1" data-challenge-id="' . intval( $atts['id'] ) . '">' . __( 'Accept', 'tournamatch' ) . '</a>';
	}

	/**
	 * Shortcode to create a challenge decline button.
	 *
	 * @since 3.15.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function decline_challenge_button( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'language'   => array(
				'error' => __( 'An error occurred.', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_decline_challenge_button', plugins_url( '../../dist/js/decline-challenge-button.js', __FILE__ ), array( 'tournamatch' ), '3.15.0', true );
		wp_localize_script( 'trn_decline_challenge_button', 'trn_decline_challenge_button_options', $options );
		wp_enqueue_script( 'trn_decline_challenge_button' );

		return '<a href="#" class="trn-button trn-decline-challenge-button trn-mx-1" data-challenge-id="' . intval( $atts['id'] ) . '">' . __( 'Decline', 'tournamatch' ) . '</a>';
	}

	/**
	 * Shortcode to create a challenge delete button.
	 *
	 * @since 3.15.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param null   $content Content between the shortcode tags.
	 * @param string $tag Given shortcode tag.
	 *
	 * @return string
	 */
	public function delete_challenge_button( $atts = [], $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'language'   => array(
				'error' => __( 'An error occurred.', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn_delete_challenge_button', plugins_url( '../../dist/js/delete-challenge-button.js', __FILE__ ), array( 'tournamatch', 'trn-confirm-action' ), '4.3.5', true );
		wp_localize_script( 'trn_delete_challenge_button', 'trn_delete_challenge_button_options', $options );
		wp_enqueue_script( 'trn_delete_challenge_button' );

		return '<a 
		  href="#" 
		  class="trn-button trn-button-danger trn-delete-challenge-button trn-confirm-action-link trn-mx-1" 
		  data-challenge-id="' . intval( $atts['id'] ) . '" 
		  data-confirm-title="' . esc_html__( 'Delete Challenge', 'tournamatch' ) . '" 
		  data-confirm-message="' . esc_html__( 'Are you sure you want to delete this challenge?', 'tournamatch' ) . '"
		  data-modal-id="delete-challenge"
		>' . esc_html__( 'Delete', 'tournamatch' ) . '</a>';
	}

}

new Challenge_Shortcodes();
