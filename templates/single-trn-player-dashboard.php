<?php
/**
 * The template that displays the player dashboard page.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( trn_route( 'players.single.dashboard' ) ) );
	exit;
}

$user_id = get_current_user_id();

get_header();

trn_get_header();

?>
<h1 class="trn-mb-4"><?php esc_html_e( 'Dashboard', 'tournamatch' ); ?></h1>
<div class="trn-row trn-mb-3">
	<div class="trn-col-sm-12 trn-pull-right">
		<a class="trn-pull-right trn-button" href="<?php trn_esc_route_e( 'report.page' ); ?>"><?php esc_html_e( 'Results', 'tournamatch' ); ?></a>
		<a class="trn-pull-right trn-button trn-mr-1" href="<?php trn_esc_route_e( 'teams.single.create' ); ?>"><?php esc_html_e( 'Create Team', 'tournamatch' ); ?></a>
		<div class="trn-pull-right trn-button-group trn-mr-1">
			<a type="button" class="trn-button" href="<?php trn_esc_route_e( 'players.single', array( 'id' => $user_id ) ); ?>"><?php esc_html_e( 'My Profile', 'tournamatch' ); ?></a>
			<button type="button" class="trn-button trn-dropdown-toggle trn-dropdown-toggle-split" aria-haspopup="true" aria-expanded="false">
				<span class="sr-only"><?php esc_html_e( 'Toggle Dropdown', 'tournamatch' ); ?></span>
			</button>
			<div class="trn-dropdown-menu dropdown-menu-right">
				<a class="trn-dropdown-item" href="<?php trn_esc_route_e( 'players.single.edit', array( 'id' => $user_id ) ); ?>"><?php esc_html_e( 'Edit My Profile', 'tournamatch' ); ?></a>
			</div>
		</div>
	</div>
</div>
<div class="trn-row trn-mb-3">
	<div class="trn-col-sm-6">
		<div class="trn-card">
			<div class="trn-card-header trn-text-center">
				<?php esc_html_e( 'Team Invitations Received', 'tournamatch' ); ?>
			</div>
			<div class="trn-card-body">
				<?php echo do_shortcode( '[trn-my-team-invitations-list]' ); ?>
			</div>
		</div>
	</div>
	<div class="trn-col-sm-6">
		<div class="trn-card">
			<div class="trn-card-header trn-text-center">
				<?php esc_html_e( 'Team Requests Sent', 'tournamatch' ); ?>
			</div>
			<div class="trn-card-body">
				<?php echo do_shortcode( '[trn-my-team-requests-list]' ); ?>
			</div>
		</div>
	</div>
</div>
<div class="trn-row trn-mb-3">
	<div class="trn-col-sm-12">
		<div class="trn-card">
			<div class="trn-card-header trn-text-center">
				<?php esc_html_e( 'My Challenges', 'tournamatch' ); ?>
			</div>
			<?php
			$challenges = trn_get_user_challenges( get_current_user_id() );

			if ( 0 === count( $challenges ) ) :
				?>
				<div class="trn-card-body">
					<p class="trn-text-center"><?php esc_html_e( 'No challenges to display.', 'tournamatch' ); ?></p>
				</div>
			<?php else : ?>
				<table class="trn-card-body trn-table trn-table-striped trn-challenges-table" id="trn-my-challenges-table">
					<thead>
					<tr>
						<th class="trn-challenges-table-ladder"><?php esc_html_e( 'Ladder', 'tournamatch' ); ?></th>
						<th class="trn-challenges-table-challenger"><?php esc_html_e( 'Challenger', 'tournamatch' ); ?></th>
						<th class="trn-challenges-table-challengee"><?php esc_html_e( 'Challengee', 'tournamatch' ); ?></th>
						<th class="trn-challenges-table-match-time"><?php esc_html_e( 'Match Time', 'tournamatch' ); ?></th>
						<th class="trn-challenges-table-status"><?php esc_html_e( 'Status', 'tournamatch' ); ?></th>
						<th class="trn-challenges-table-action"></th>
					</tr>
					</thead>
					<tbody>
					<?php
					// display list of scheduled matches needing to be reported.
					foreach ( $challenges as $challenge ) :
						?>
						<tr data-challenge-id="<?php echo intval( $challenge->challenge_id ); ?>">
							<td class="trn-challenges-table-ladder"><a href="<?php echo esc_url( trn_route( 'ladders.single.standings', array( 'id' => $challenge->ladder_id ) ) ); ?>"><?php echo esc_html( $challenge->ladder_name ); ?></a></td>
							<td class="trn-challenges-table-challenger"><a href="<?php echo esc_url( trn_route( $challenge->competitor_slug, array( $challenge->competitor_slug_argument => $challenge->challenger_id ) ) ); ?>"><?php echo esc_html( $challenge->challenger_name ); ?></a></td>
							<td class="trn-challenges-table-challengee">
								<?php
								if ( ( 'blind' === $challenge->challenge_type ) && is_null( $challenge->challengee_id ) ) :
									esc_html_e( '(open)', 'tournamatch' );
								else :
									?>
									<a href="<?php echo esc_url( trn_route( $challenge->competitor_slug, array( $challenge->competitor_slug_argument => $challenge->challengee_id ) ) ); ?>"><?php echo esc_html( $challenge->challengee_name ); ?></a>
								<?php endif; ?>
							</td>
							<td class="trn-challenges-table-match-time"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $challenge->match_time ) ) ) ); ?></td>
							<td class="trn-challenges-table-status"><?php echo esc_html( ucwords( $challenge->accepted_state ) ); ?></td>
							<td class="trn-challenges-table-action">
								<div class="trn-pull-right">
									<a class="trn-button trn-button-sm" href="<?php trn_esc_route_e( 'challenges.single', array( 'id' => $challenge->challenge_id ) ); ?>"><i class="fa fa-info"></i></a>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="trn-row">
	<div class="trn-col-sm-12">
		<div class="trn-card">
			<div class="trn-card-header trn-text-center">
				<?php esc_html_e( 'My Competitions', 'tournamatch' ); ?>
			</div>
			<?php
			$competitions = trn_get_user_competitions( get_current_user_id() );

			if ( 0 === count( $competitions ) ) :
				?>
				<div class="trn-card-body">
					<p class="trn-text-center"><?php esc_html_e( 'You are not currently competing in any events.', 'tournamatch' ); ?></p>
				</div>
			<?php else : ?>
				<table class="trn-card-body trn-table trn-table-striped trn-my-competitions-table" id="trn-my-competitions-table">
					<thead>
					<tr>
						<th class="trn-my-competitions-table-event"><?php esc_html_e( 'Event', 'tournamatch' ); ?></th>
						<th class="trn-my-competitions-table-name"><?php esc_html_e( 'Name', 'tournamatch' ); ?></th>
						<th class="trn-my-competitions-table-game"><?php esc_html_e( 'Game', 'tournamatch' ); ?></th>
						<th class="trn-my-competitions-table-action"></th>
					</tr>
					</thead>
					<tbody>
					<?php
					// display list of scheduled matches needing to be reported.
					foreach ( $competitions as $competition ) :
						?>
						<tr data-competition-type="<?php echo esc_html( $competition->competition_type ); ?>" data-competition-id="<?php echo intval( $competition->id ); ?>">
							<td class="trn-my-competitions-table-event"><?php echo esc_html( ucwords( $competition->competition_type ) ); ?></td>
							<td class="trn-my-competitions-table-name"><a href="<?php trn_esc_route_e( $competition->route_name, array( 'id' => $competition->id ) ); ?>"><?php echo esc_html( $competition->name ); ?></a></td>
							<td class="trn-my-competitions-table-game"><?php echo esc_html( $competition->game_name ); ?></td>
							<td class="trn-my-competitions-table-action">
								<?php if ( 'ladder' === $competition->competition_type ) : ?>
									<div class="trn-pull-right">
										<a class="trn-button trn-button-sm" href="<?php trn_esc_route_e( 'matches.single.create', array( 'ladder_id' => $competition->id ) ); ?>"><?php esc_html_e( 'Report', 'tournamatch' ); ?></a>
										<a class="trn-button trn-button-sm" href="<?php trn_esc_route_e( 'ladders.single.standings', array( 'id' => $competition->id ) ); ?>"><?php esc_html_e( 'Standings', 'tournamatch' ); ?></a>
									</div>
								<?php else : ?>
									<?php if ( in_array( $competition->status, array( 'in_progress', 'complete' ), true ) ) : ?>
										<div class="trn-pull-right">
											<a class="trn-button trn-button-sm" href="<?php trn_esc_route_e( 'tournaments.single.brackets', array( 'id' => $competition->id ) ); ?>"><?php esc_html_e( 'Brackets', 'tournamatch' ); ?></a>
										</div>
									<?php else : ?>
										<div class="trn-pull-right">
											<a class="trn-button trn-button-sm" href="<?php trn_esc_route_e( 'tournaments.single.rules', array( 'id' => $competition->id ) ); ?>"><?php esc_html_e( 'Rules', 'tournamatch' ); ?></a>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php

trn_get_footer();

get_footer();
