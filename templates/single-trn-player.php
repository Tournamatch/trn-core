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

// Secondary Links.
$secondary_links = [];

if ( is_user_logged_in() ) {

	// Edit profile.
	if ( get_current_user_id() === $user_id ) {
		$secondary_links[] = '<a class="btn btn-sm btn-secondary" href="' . esc_url( trn_route( 'players.single.edit', array( 'id' => $user_id ) ) ) . '">' . esc_html__( 'Edit Profile', 'tournamatch' ) . '</a>';
	}

	// Team invite.
	if ( get_current_user_id() !== $user_id ) {
		$secondary_links[] = do_shortcode( '[trn-invite-player-to-team user_id="' . intval( $user_id ) . '"]' );
	}
}

$player_fields = apply_filters( 'trn_player_fields', array() );
$icon_fields   = apply_filters( 'trn_player_icon_fields', array() );

$social_links = array();
foreach ( $icon_fields as $social_icon => $social_icon_data ) {
	if ( 0 < strlen( get_user_meta( $user_id, "trn_$social_icon", true ) ) ) {
		$social_links[] = '<a href="' . esc_html( get_user_meta( $user_id, "trn_$social_icon", true ) ) . '"><i class="' . esc_html( $social_icon_data['icon'] ) . '"></i></a>';
	}
}

if ( intval( trn_get_option( 'display_user_email' ) ) === 1 ) {
	$social_links[] = '<a href="mailto:' . esc_html( get_userdata( $user_id )->user_email ) . '"><i class="fa fa-envelope"></i></a>';
}

if ( 0 === count( $social_links ) ) {
	$social_links[] = '<em>' . esc_html__( 'No contacts to display.', 'tournamatch' ) . '</em>';
}
$social_links = implode( ' ', $social_links );
?>
<div class="tournamatch-profile">
	<div class="tournamatch-profile-details">
		<h1 class="text-center"><?php echo esc_html( $player->name ); ?></h1>
		<dl>
			<dt><?php esc_html_e( 'Joined Date', 'tournamatch' ); ?>:</dt>
			<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( get_user_by( 'id', $user_id )->data->user_registered ) ) ) ); ?></dd>
			<dt><?php esc_html_e( 'Location', 'tournamatch' ); ?>:</dt>
			<dd><?php echo esc_html( $player->location ); ?></dd>
			<?php foreach ( $player_fields as $field_id => $field_data ) : ?>
				<dt><?php echo esc_html( $field_data['display_name'] ); ?>:</dt>
				<dd><?php echo esc_html( get_user_meta( $user_id, "trn_$field_id", true ) ); ?></dd>
			<?php endforeach; ?>
			<dt><?php esc_html_e( 'Contact', 'tournamatch' ); ?>:</dt>
			<dd><?php echo wp_kses_post( $social_links ); ?></dd>
			<dt><?php esc_html_e( 'Career Record', 'tournamatch' ); ?>:</dt>
			<dd><?php echo do_shortcode( '[trn-career-record competitor_type="players" competitor_id="' . intval( $user_id ) . '"]' ); ?></dd>
		</dl>
		<?php if ( 0 < count( $secondary_links ) ) : ?>
			<div id="trn-send-invite-response"></div>
			<div class="text-center">
				<?php echo wp_kses_post( implode( ' &nssp; ', $secondary_links ) ); ?><br>
			</div>
		<?php endif; ?>
	</div>
	<div class="tournamatch-profile-avatar">
		<?php trn_display_avatar( $player->user_id, 'players', $player->avatar ); ?>
	</div>
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

$views = apply_filters( 'trn_single_player_views', $views, $player );

trn_single_template_tab_views( $views, $player );

trn_get_footer();

get_footer();
