<?php
/**
 * The template for displaying a single player.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch Brackets
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

trn_get_header();

$user_id = (int) get_query_var( 'id' );
$player  = trn_get_player( $user_id );

?>
<div class="trn-profile-header"<?php trn_competitor_header_banner_style( $player->banner ); ?>>
	<div class="trn-profile-avatar">
		<?php trn_display_avatar( $player->user_id, 'players', $player->avatar, 'trn-header-avatar' ); ?>
	</div>
	<h1 class="trn-profile-name"><?php echo esc_html( $player->name ); ?></h1>
	<span class="trn-profile-record"><?php echo do_shortcode( '[trn-career-record competitor_type="players" competitor_id="' . intval( $player->user_id ) . '"]' ); ?></span>
	<span class="trn-profile-actions">
	<?php if ( is_user_logged_in() ) : ?>
		<?php if ( get_current_user_id() === $user_id ) : ?>
			<a class="trn-button trn-button-sm" href="<?php echo esc_url( trn_route( 'players.single.edit', array( 'id' => $user_id ) ) ); ?>"><?php esc_html_e( 'Edit Profile', 'tournamatch' ); ?></a>
		<?php else : ?>
			<?php echo do_shortcode( '[trn-invite-player-to-team user_id="' . intval( $user_id ) . '"]' ); ?>
		<?php endif; ?>
	<?php endif; ?>
	</span>
	<ul class="trn-profile-list">
		<li class="trn-profile-list-item joined">
			<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( get_user_by( 'id', $user_id )->data->user_registered ) ) ) ); ?>
		</li>
		<li class="trn-profile-list-item location">
			<?php echo esc_html( $player->location ); ?>
		</li>
	</ul>
</div>
<?php

$views = array(
	'teams'       => array(
		'heading' => __( 'Teams', 'tournamatch' ),
		'content' => function( $player ) {
			echo do_shortcode( '[trn-player-teams-list-table player_id="' . intval( $player->user_id ) . '"]' );
		},
	),
	'ladders'     => array(
		'heading' => __( 'Ladders', 'tournamatch' ),
		'content' => function( $player ) {
			echo do_shortcode( '[trn-competitor-ladders-list-table competitor_type="players" competitor_id="' . intval( $player->user_id ) . '"]' );
		},
	),
	'tournaments' => array(
		'heading' => __( 'Tournaments', 'tournamatch' ),
		'content' => function( $player ) {
			echo do_shortcode( '[trn-competitor-tournaments-list-table competitor_type="players" competitor_id="' . intval( $player->user_id ) . '"]' );
		},
	),
	'matches'     => array(
		'heading' => __( 'Match History', 'tournamatch' ),
		'content' => function( $player ) {
			echo do_shortcode( '[trn-competitor-match-list-table competitor_type="players" competitor_id="' . intval( $player->user_id ) . '"]' );
		},
	),
	'about'       => array(
		'heading' => __( 'About', 'tournamatch' ),
		'content' => function( $player ) {
			echo wp_kses_post( $player->profile );
		},
	),
);

/**
 * Filters an array of views for the single player template page.
 *
 * @since 4.1.0
 *
 * @param array $views {
 *          An associative array of tabbed views.
 *
 *          @param string|callable $heading The content or callable content of the header tab.
 *          @param string $href The url of the header tab.
 *          @param string|callable $content The content or callable content of the tabbed page.
 *      }
 * @param stdClass $player The data context item we are rendering a page for.
 */
$views = apply_filters( 'trn_single_player_views', $views, $player );

trn_single_template_tab_views( $views, $player );

trn_get_footer();

get_footer();
