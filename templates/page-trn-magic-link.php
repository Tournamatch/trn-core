<?php
/**
 * The template that displays a page for processing magic links.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$confirm_hash = get_query_var( 'confirm_hash' );

// Determine which action to confirm from link.
$magic_links = apply_filters( 'trn_magic_links', array() );

// The first 2 characters of the hash identify the action.
$magic_key = substr( $confirm_hash, 0, 2 );

if ( array_key_exists( $magic_key, $magic_links ) ) {
	get_header();

	trn_get_header();

	do_action( $magic_links[ $magic_key ], $confirm_hash );

	trn_get_footer();

	get_footer();
} else {
	echo '404 Not found';
}
