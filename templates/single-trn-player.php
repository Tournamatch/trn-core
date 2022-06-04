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
			<dd><?php echo esc_html( date( get_option( 'date_format' ), strtotime( get_user_by( 'id', $user_id )->data->user_registered ) ) ); ?></dd>
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

<ul id="tournamatch-player-views" class="tournamatch-nav mt-md" role="tablist">
	<li class="tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link tournamatch-nav-active" href="#teams" role="tab" aria-controls="teams" aria-selected="true" data-target="teams"><span><?php esc_html_e( 'Teams', 'tournamatch' ); ?></span></a></li>
	<li class="tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#ladders" role="tab" aria-controls="ladders" aria-selected="false" data-target="ladders"><span><?php esc_html_e( 'Ladders', 'tournamatch' ); ?></span></a></li>
	<li class="tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#tournaments" role="tab" aria-controls="tournaments" aria-selected="false" data-target="tournaments"><span><?php esc_html_e( 'Tournaments', 'tournamatch' ); ?></span></a></li>
	<li class="tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#matches" role="tab" aria-controls="matches" aria-selected="false" data-target="matches"><span><?php esc_html_e( 'Match History', 'tournamatch' ); ?></span></a></li>
	<li class="tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#about" role="tab" aria-controls="about" aria-selected="false" data-target="about"><span><?php esc_html_e( 'About', 'tournamatch' ); ?></span></a></li>
</ul>

<div class="tournamatch-tab-content">
	<div id="teams" class="tournamatch-tab-pane tournamatch-tab-active" role="tabpanel" aria-labelledby="teams-tab" >
		<h4 class="text-center"><?php esc_html_e( 'Teams', 'tournamatch' ); ?></h4>
		<?php echo do_shortcode( '[trn-player-teams-list-table player_id="' . intval( $user_id ) . '"]' ); ?>
	</div>
	<div id="ladders" class="tournamatch-tab-pane" role="tabpanel" aria-labelledby="ladders-tab">
		<h4 class="text-center"><?php esc_html_e( 'Ladders', 'tournamatch' ); ?></h4>
		<?php echo do_shortcode( '[trn-competitor-ladders-list-table competitor_type="players" competitor_id="' . intval( $user_id ) . '"]' ); ?>
	</div>
	<div id="tournaments" class="tournamatch-tab-pane" role="tabpanel" aria-labelledby="tournaments-tab">
		<h4 class="text-center"><?php esc_html_e( 'Tournaments', 'tournamatch' ); ?></h4>
		<?php echo do_shortcode( '[trn-competitor-tournaments-list-table competitor_type="players" competitor_id="' . intval( $user_id ) . '"]' ); ?>
	</div>
	<div id="matches" class="tournamatch-tab-pane" role="tabpanel" aria-labelledby="matches-tab">
		<h4 class="text-center"><?php esc_html_e( 'Match History', 'tournamatch' ); ?></h4>
		<?php echo do_shortcode( '[trn-competitor-match-list-table competitor_type="players" competitor_id="' . intval( $user_id ) . '"]' ); ?>
	</div>
	<div id="about" class="tournamatch-tab-pane" role="tabpanel" aria-labelledby="about-tab">
		<h4 class="text-center"><?php esc_html_e( 'About', 'tournamatch' ); ?></h4>
		<h4><?php echo wp_kses_post( $player->profile ); ?></h4>
	</div>
</div>
<?php

trn_get_footer();

wp_register_script( 'player-profile', plugins_url( '../dist/js/player-profile.js', __FILE__ ), array( 'tournamatch' ), '3.25.0', true );
wp_enqueue_script( 'player-profile' );

get_footer();
