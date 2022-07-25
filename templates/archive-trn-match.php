<?php
/**
 * The template that displays an archive of matches.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

trn_get_header();

?>
	<h1 class="trn-mb-4"><?php esc_html_e( 'Matches', 'tournamatch' ); ?></h1>
<?php

echo do_shortcode( '[trn-matches-list-table]' );

trn_get_footer();

get_footer();
