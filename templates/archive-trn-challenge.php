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
	<h1 class="mb-4">
		<?php esc_html_e( 'Challenges', 'tournamatch' ); ?>
		<?php if ( is_user_logged_in() ) : ?>
			<div class="float-right">
				<a
					href="<?php trn_esc_route_e( 'challenges.single.create' ); ?>"
					class="btn btn-primary"><?php esc_html_e( 'New Challenge', 'tournamatch' ); ?></a>
			</div>
		<?php endif; ?>
	</h1>
	<div class="clearfix"></div>

<?php

echo do_shortcode( '[trn-challenges-list-table]' );

trn_get_footer();

get_footer();
