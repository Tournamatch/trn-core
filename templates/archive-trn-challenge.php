<?php
/**
 * The template that displays an archive of challenges.
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
	<h1 class="trn-mb-4">
		<?php esc_html_e( 'Challenges', 'tournamatch' ); ?>
		<?php if ( is_user_logged_in() ) : ?>
			<div class="trn-float-right">
				<a
					href="<?php trn_esc_route_e( 'challenges.single.create' ); ?>"
					class="trn-button"><?php esc_html_e( 'New Challenge', 'tournamatch' ); ?></a>
			</div>
		<?php endif; ?>
	</h1>
	<div class="trn-clearfix"></div>

<?php

echo do_shortcode( '[trn-challenges-list-table]' );

trn_get_footer();

get_footer();
