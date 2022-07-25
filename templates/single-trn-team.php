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
$user_id    = get_current_user_id();

$team_links = array();
if ( 0 !== $user_id ) {
	$team_links[] = '<a class="trn-button trn-button-sm trn-button-secondary" id="trn-edit-team-button" style="display:none" href="' . trn_route( 'teams.single.edit', array( 'id' => $team_id ) ) . '">' . esc_html__( 'Edit Team', 'tournamatch' ) . '</a>';
	$team_links[] = '<button class="trn-button trn-button-sm trn-button-danger" id="trn-delete-team-button" style="display:none">' . esc_html__( 'Delete Team', 'tournamatch' ) . '</button>';
	$team_links[] = '<button class="trn-button trn-button-sm trn-button-secondary" id="trn-leave-team-button" style="display:none">' . esc_html__( 'Leave Team', 'tournamatch' ) . '</button>';
	$team_links[] = '<button class="trn-button trn-button-sm trn-button-secondary" id="trn-join-team-button" style="display:none" data-team-id="' . intval( $team_id ) . '" data-user-id="' . get_current_user_id() . '">' . esc_html__( 'Join Team', 'tournamatch' ) . '</button>';
}

$description_list = array(
	'owner'         => array(
		'term'        => array(
			'text' => __( 'Owner', 'tournamatch' ),
			'id'   => 'trn-team-owner',
		),
		'description' => $team_owner->name,
	),
	'joined_date'   => array(
		'term'        => __( 'Joined Date', 'tournamatch' ),
		'description' => date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $team->joined_date ) ) ),
	),
	'members'       => array(
		'term'        => __( 'Members', 'tournamatch' ),
		'description' => array(
			'text' => function( $team ) {
				echo '<em>' . esc_html__( 'Loading team members...', 'tournamatch' ) . '</em>';
			},
			'id'   => 'trn-team-members-list',
		),
	),
	'career_record' => array(
		'term'        => __( 'Career Record', 'tournamatch' ),
		'description' => function( $team ) {
			echo do_shortcode( '[trn-career-record competitor_type="teams" competitor_id="' . intval( $team->team_id ) . '"]' );
		},
	),
);

$description_list = apply_filters( 'trn_single_team_description_list', $description_list, $team );

?>
<div class="trn-profile">
	<div class="trn-profile-details">
		<h1 class="trn-text-center trn-mb-4">
			<?php echo esc_html( $team->name ); ?>
		</h1>
		<?php trn_single_template_description_list( $description_list, $team ); ?>
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
			<div class="trn-text-center">
				<?php echo wp_kses_post( implode( ' &nbsp; ', $team_links ) ); ?><br>
			</div>
		<?php endif; ?>
	</div>
	<div class="trn-profile-avatar">
		<?php trn_display_avatar( $team->team_id, 'teams', $team->avatar ); ?>
	</div>
</div>
<?php

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
