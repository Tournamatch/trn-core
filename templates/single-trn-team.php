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

$team_owner  = trn_get_team_owner( $team_id );
$team_fields = apply_filters( 'trn_team_fields', array() );
$user_id     = get_current_user_id();

$social_fields = apply_filters( 'trn_team_icon_fields', array() );

$team_links = array();
if ( 0 !== $user_id ) {
	$team_links[] = '<a class="btn btn-sm btn-secondary" id="trn-edit-team-button" style="display:none" href="' . trn_route( 'teams.single.edit', array( 'id' => $team_id ) ) . '">' . esc_html__( 'Edit Team', 'tournamatch' ) . '</a>';

	$team_links[] = '<button class="btn btn-sm btn-danger" id="trn-delete-team-button" style="display:none">' . esc_html__( 'Delete Team', 'tournamatch' ) . '</button>';
	$team_links[] = '<button class="btn btn-sm btn-secondary" id="trn-leave-team-button" style="display:none">' . esc_html__( 'Leave Team', 'tournamatch' ) . '</button>';
	$team_links[] = '<button class="btn btn-sm btn-secondary" id="trn-join-team-button" style="display:none" data-team-id="' . intval( $team_id ) . '" data-user-id="' . get_current_user_id() . '">' . esc_html__( 'Join Team', 'tournamatch' ) . '</button>';
}

$social_links = array();
foreach ( $social_fields as $social_icon => $social_icon_data ) {
	if ( 0 < strlen( get_post_meta( $team->post_id, $social_icon, true ) ) ) {
		$social_links[] = '<a href="' . esc_html( get_post_meta( $team->post_id, $social_icon, true ) ) . '"><i class="' . esc_html( $social_icon_data['icon'] ) . '"></i></a>';
	}
}
$social_links = implode( ' ', $social_links );

?>
<div class="tournamatch-profile">
	<div class="tournamatch-profile-details">
		<h1 class="text-center mb-4">
			<?php echo esc_html( $team->name ); ?>
		</h1>
		<dl>
			<dt><?php esc_html_e( 'Owner', 'tournamatch' ); ?>:</dt>
			<dd id="trn-team-owner"><?php echo esc_html( $team_owner->name ); ?></dd>
			<dt><?php esc_html_e( 'Joined Date', 'tournamatch' ); ?>:</dt>
			<dd><?php echo esc_html( date( get_option( 'date_format' ), strtotime( $team->joined_date ) ) ); ?></dd>
			<?php foreach ( $team_fields as $field_id => $field_data ) : ?>
				<dt><?php echo esc_html( $field_data['display_name'] ); ?>:</dt>
				<dd><?php echo esc_html( get_post_meta( $team->post_id, $field_id, true ) ); ?></dd>
			<?php endforeach; ?>
			<dt><?php esc_html_e( 'Members', 'tournamatch' ); ?>:</dt>
			<dd id="trn-team-members-list">
				<?php esc_html_e( 'Loading team members...', 'tournamatch' ); ?>
			</dd>
			<dt><?php esc_html_e( 'Contact', 'tournamatch' ); ?>:</dt>
			<dd><?php echo wp_kses_post( $social_links ); ?></dd>
			<dt><?php esc_html_e( 'Career Record', 'tournamatch' ); ?>:</dt>
			<dd><?php echo do_shortcode( '[trn-career-record competitor_type="teams" competitor_id="' . intval( $team_id ) . '"]' ); ?></dd>
		</dl>
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
		wp_register_script( 'trn_team_profile', plugins_url( '../dist/js/team-profile.js', __FILE__ ), array( 'tournamatch' ), '3.27.0', true );
		wp_localize_script( 'trn_team_profile', 'trn_team_profile_options', $options );
		wp_enqueue_script( 'trn_team_profile' );

		if ( 0 < count( $team_links ) ) :
			?>
			<div class="text-center">
				<?php echo wp_kses_post( implode( ' &nbsp; ', $team_links ) ); ?><br>
			</div>
		<?php endif; ?>
	</div>
	<div class="tournamatch-profile-avatar">
		<?php trn_display_avatar( $team->team_id, 'teams', $team->avatar ); ?>
	</div>
</div>

<ul id="tournamatch-team-views" class="tournamatch-nav mt-md">
	<li class="flex-sm tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#roster" aria-selected="true" aria-controls="roster" role="tab" data-target="roster"><span><?php esc_html_e( 'Team Roster', 'tournamatch' ); ?></span></a></li>
	<li class="flex-sm tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#ladders" aria-selected="false" aria-controls="ladders" role="tab" data-target="ladders"><span><?php esc_html_e( 'Ladders', 'tournamatch' ); ?></span></a></li>
	<li class="flex-sm tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#tournaments" aria-selected="false" aria-controls="tournaments" role="tab" data-target="tournaments"><span><?php esc_html_e( 'Tournaments', 'tournamatch' ); ?></span></a></li>
	<li class="flex-sm tournamatch-nav-item" role="presentation"><a class="tournamatch-nav-link" href="#match-history" aria-selected="false" aria-controls="match-history" role="tab" data-target="match-history"><span><?php esc_html_e( 'Match History', 'tournamatch' ); ?></span></a></li>
</ul>

<!-- Tab panes -->
<div class="tournamatch-tab-content">
	<div id="roster" class="tournamatch-tab-pane tournamatch-tab-active" role="tabpanel" aria-labelledby="roster-tab">
		<h4 class="text-center"><?php esc_html_e( 'Team Roster', 'tournamatch' ); ?></h4>
		<?php
		echo do_shortcode( '[trn-team-roster-table team_id="' . intval( $team_id ) . '"]' );

		if ( current_user_can( 'manage_tournamatch' ) ) {
			?>
			<div class="float-right">
				<form autocomplete="off" class="form-inline" id="trn-add-player-form">
					<label for="trn-add-player-input" class="sr-only"><?php esc_html_e( 'Player Name', 'tournamatch' ); ?>:</label>
					<div class="autocomplete mr-sm-2">
						<input type="text" id="trn-add-player-input" class="form-control" placeholder="<?php esc_html_e( 'Player name', 'tournamatch' ); ?>" required>
					</div>
					<button id="trn-add-player-button" class="btn btn-primary"><?php esc_html_e( 'Add Player', 'tournamatch' ); ?></button>
				</form>
			</div>
			<div class="clearfix mb-3"></div>
			<?php
		}

		if ( intval( $team_owner->id ) === $user_id ) {
			?>
			<div class="row">
				<div class="col-md-6" id="invite-panel">
					<?php echo do_shortcode( '[trn-email-team-invitation-form team_id="' . intval( $team_id ) . '"]' ); ?>
				</div>
				<div class="col-md-3" id="invitations-panel">
					<?php echo do_shortcode( '[trn-team-invitations-list team_id="' . intval( $team_id ) . '"]' ); ?>
				</div>
				<div class="col-md-3" id="requests-panel">
					<?php echo do_shortcode( '[trn-team-requests-list team_id="' . intval( $team_id ) . '"]' ); ?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<div id="ladders" class="tournamatch-tab-pane" role="tabpanel" aria-labelledby="ladders-tab">
		<h4 class="text-center"><?php esc_html_e( 'Ladders', 'tournamatch' ); ?></h4>
		<?php echo do_shortcode( '[trn-competitor-ladders-list-table competitor_type="teams" competitor_id="' . intval( $team_id ) . '"]' ); ?>
	</div>
	<div id="tournaments" class="tournamatch-tab-pane" role="tabpanel" aria-labelledby="tournaments-tab">
		<h4 class="text-center"><?php esc_html_e( 'Tournaments', 'tournamatch' ); ?></h4>
		<?php echo do_shortcode( '[trn-competitor-tournaments-list-table competitor_type="teams" competitor_id="' . intval( $team_id ) . '"]' ); ?>
	</div>
	<div id="match-history" class="tournamatch-tab-pane" role="tabpanel" aria-labelledby="match-history-tab">
		<h4 class="text-center"><?php esc_html_e( 'Match History', 'tournamatch' ); ?></h4>
		<?php echo do_shortcode( '[trn-competitor-match-list-table competitor_type="teams" competitor_id="' . intval( $team_id ) . '"]' ); ?>
	</div>
</div>
<?php

trn_get_footer();

get_footer();
