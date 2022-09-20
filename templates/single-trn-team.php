<?php
/**
 * The template that displays a single team.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$team_id = get_query_var( 'id' );

$team = trn_get_team( $team_id );
if ( is_null( $team ) ) {
	wp_safe_redirect( trn_route( 'teams.archive' ) );
	exit;
}

get_header();

trn_get_header();

$team_owner = trn_get_team_owner( $team_id );

?>
<div class="trn-profile-header"<?php trn_competitor_header_banner_style( $team->banner ); ?>>
	<div class="trn-profile-avatar">
		<?php trn_display_avatar( $team->team_id, 'teams', $team->avatar, 'trn-header-avatar' ); ?>
	</div>
	<div class="trn-profile-details">
		<h1 class="trn-profile-name"><?php echo esc_html( $team->name ); ?></h1>
		<?php if ( trn_is_plugin_active( 'trn-profile-social-icons' ) ) :
			$social_icons = trn_get_team_icon_fields();

			if ( is_array( $social_icons ) && ( 0 < count( $social_icons ) ) ) : ?>
				<ul class="trn-list-inline">
					<?php foreach( $social_icons as $icon => $data ) : $key = 'psi_icon_' . $icon; ?>
						<?php if ( isset( $team->$key ) && ( 0 < strlen( $team->$key ) ) ) : ?>
							<li class="trn-list-inline-item"><a href="<?php echo esc_url( $team->$key ); ?>" target="_blank"><i class="<?php echo esc_attr( $data['icon'] ); ?>"></i></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
		<span class="trn-profile-record"><?php echo do_shortcode( '[trn-career-record competitor_type="teams" competitor_id="' . intval( $team->team_id ) . '"]' ); ?></span>
	</div>
	<div class="trn-profile-actions">
		<?php if ( is_user_logged_in() ) : ?>
			<a class="trn-button trn-button-sm" id="trn-edit-team-button" style="display:none" href="<?php trn_esc_route_e( 'teams.single.edit', array( 'id' => $team_id ) ); ?>"><?php esc_html_e( 'Edit Team', 'tournamatch' ); ?></a>
			<button class="trn-button trn-button-sm trn-button-danger" id="trn-delete-team-button" style="display:none"><?php esc_html_e( 'Delete Team', 'tournamatch' ); ?></button>
			<button class="trn-button trn-button-sm" id="trn-leave-team-button" style="display:none"><?php esc_html_e( 'Leave Team', 'tournamatch' ); ?></button>
			<button class="trn-button trn-button-sm" id="trn-join-team-button" style="display:none" data-team-id="<?php echo intval( $team_id ); ?>" data-user-id="<?php echo intval( get_current_user_id() ); ?>"><?php esc_html_e( 'Join Team', 'tournamatch' ); ?></button>
		<?php endif; ?>
	</div>
	<ul class="trn-profile-list">
		<li class="trn-profile-list-item joined">
			<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $team->joined_date ) ) ) ); ?>
		</li>
		<li class="trn-profile-list-item members">
			<?php /* translators: 1 Member or 2 Members. */ ?>
			<?php echo esc_html( sprintf( _n( '%d Member', '%d Members', $team->members, 'tournamatch' ), $team->members ) ); ?>
		</li>
	</ul>
</div>
<div id="trn-leave-team-response"></div>
<div id="trn-join-team-response"></div>
<?php

$options = array(
	'api_url'         => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
	'team_id'         => intval( $team_id ),
	'current_user_id' => get_current_user_id(),
	'is_logged_in'    => is_user_logged_in(),
	'teams_url'       => trn_route( 'teams.archive' ),
	'can_add'         => current_user_can( 'manage_tournamatch' ),
	'can_edit'        => current_user_can( 'manage_tournamatch' ),
	'language'        => array(
		'success'         => esc_html__( 'Success', 'tournamatch' ),
		'success_message' => esc_html__( 'Your request to join the team has been recorded. The team leader must accept your request.', 'tournamatch' ),
		'failure'         => esc_html__( 'Error', 'tournamatch' ),
		'failure_message' => esc_html__( 'Failed to add player to team.', 'tournamatch' ),
		'zero_members'    => esc_html__( 'No members to display.', 'tournamatch' ),
		'error_members'   => esc_html__( 'An error occurred.', 'tournamatch' ),
	),
);
wp_register_script( 'trn_team_profile', plugins_url( '../dist/js/team-profile.js', __FILE__ ), array( 'tournamatch' ), '4.3.0', true );
wp_localize_script( 'trn_team_profile', 'trn_team_profile_options', $options );
wp_enqueue_script( 'trn_team_profile' );

$views = array(
	'roster'        => array(
		'heading' => __( 'Team Roster', 'tournamatch' ),
		'content' => function( $team ) use ( $team_owner ) {
			$team_id = $team->team_id;
			$user_id = get_current_user_id();

			echo do_shortcode( '[trn-team-roster-table team_id="' . intval( $team_id ) . '"]' );

			if ( current_user_can( 'manage_tournamatch' ) ) {
				?>
				<div class="trn-float-right">
					<form autocomplete="off" class="form-inline" id="trn-add-player-form">
						<label for="trn-add-player-input" class="sr-only"><?php esc_html_e( 'Player Name', 'tournamatch' ); ?>:</label>
						<div class="trn-auto-complete mr-sm-2">
							<input type="text" id="trn-add-player-input" class="trn-form-control" placeholder="<?php esc_html_e( 'Player name', 'tournamatch' ); ?>" required>
						</div>
						<button id="trn-add-player-button" class="trn-button"><?php esc_html_e( 'Add Player', 'tournamatch' ); ?></button>
					</form>
				</div>
				<div class="trn-clearfix trn-mb-3"></div>
				<?php
			}

			if ( intval( $team_owner->id ) === $user_id ) {
				?>
				<div class="trn-row">
					<div class="trn-col-md-6" id="invite-panel">
						<?php echo do_shortcode( '[trn-email-team-invitation-form team_id="' . intval( $team_id ) . '"]' ); ?>
					</div>
					<div class="trn-col-md-3" id="invitations-panel">
						<?php echo do_shortcode( '[trn-team-invitations-list team_id="' . intval( $team_id ) . '"]' ); ?>
					</div>
					<div class="trn-col-md-3" id="requests-panel">
						<?php echo do_shortcode( '[trn-team-requests-list team_id="' . intval( $team_id ) . '"]' ); ?>
					</div>
				</div>
				<?php
			}
		},
	),
	'ladders'       => array(
		'heading' => __( 'Ladders', 'tournamatch' ),
		'content' => function( $team ) {
			echo do_shortcode( '[trn-competitor-ladders-list-table competitor_type="teams" competitor_id="' . intval( $team->team_id ) . '"]' );
		},
	),
	'tournaments'   => array(
		'heading' => __( 'Tournaments', 'tournamatch' ),
		'content' => function( $team ) {
			echo do_shortcode( '[trn-competitor-tournaments-list-table competitor_type="teams" competitor_id="' . intval( $team->team_id ) . '"]' );
		},
	),
	'match-history' => array(
		'heading' => __( 'Match History', 'tournamatch' ),
		'content' => function( $team ) {
			echo do_shortcode( '[trn-competitor-match-list-table competitor_type="teams" competitor_id="' . intval( $team->team_id ) . '"]' );
		},
	),
);

/**
 * Filters an array of views for the single team template page.
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
 * @param stdClass $team The data context item we are rendering a page for.
 */
$views = apply_filters( 'trn_single_team_views', $views, $team );

trn_single_template_tab_views( $views, $team );

trn_get_footer();

get_footer();
