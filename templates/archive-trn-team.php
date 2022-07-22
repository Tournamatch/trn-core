<?php
/**
 * The template that displays an archive of teams.
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
		<?php esc_html_e( 'Teams', 'tournamatch' ); ?>
		<?php if ( is_user_logged_in() ) : ?>
			<div class="trn-float-right">
				<a class="trn-button" href="<?php trn_esc_route_e( 'teams.single.create' ); ?>"><?php esc_html_e( 'Create Team', 'tournamatch' ); ?></a>
			</div>
		<?php endif; ?>
	</h1>
	<div class="trn-clearfix"></div>

<?php

echo do_shortcode( '[trn-teams-list-table]' );

trn_get_footer();

get_footer();
