<?php
/**
 * The template that displays the report results page.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Add backwards compatibility for old email match confirmation URLs (3.x and <= 4.3.4).
if ( 'confirm_e_results' === get_query_var( 'mode' ) ) {
	wp_safe_redirect( trn_route( 'magic.match-confirm-result', array( 'reference_id' => get_query_var( 'mrf' ) ) ) );
	exit;
}

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( trn_route( 'report.page' ) ) );
	exit;
}

$user_id             = get_current_user_id();
$can_confirm_matches = get_can_confirm_matches( $user_id );
$reported_matches    = get_reported_matches( $user_id );

get_header();

trn_get_header();

?>
	<h1 class="trn-mb-4"><?php esc_html_e( 'Results Dashboard', 'tournamatch' ); ?></h1>
	<section>
		<h4 class="trn-text-center"><?php esc_html_e( 'Confirm Results', 'tournamatch' ); ?></h4>
		<div id="trn-dispute-match-response">
			<?php //phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<?php if ( isset( $_GET['dispute_match_id'] ) ) : ?>
				<div class="trn-alert trn-alert-success"><strong><?php esc_html_e( 'Success', 'tournamatch' ); ?>
						:</strong> <?php esc_html_e( 'The match dispute has been logged and an admin notified.', 'tournamatch' ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php //phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<?php if ( isset( $_GET['confirmed_match'] ) ) : ?>
			<div id="trn-confirm-match-response">
				<div class="trn-alert trn-alert-success"><strong><?php esc_html_e( 'Success', 'tournamatch' ); ?>
						:</strong> <?php esc_html_e( 'The match has been confirmed.', 'tournamatch' ); ?></div>
			</div>
		<?php endif; ?>
		<?php if ( 0 < count( $can_confirm_matches ) ) : ?>
			<?php /* translators: An integer number of matches. */ ?>
			<p><?php printf( esc_html( _n( 'You have %d match waiting for your confirmation.', 'You have %d matches waiting for your confirmation.', count( $can_confirm_matches ), 'tournamatch' ) ), count( $can_confirm_matches ) ); ?></p>
			<table class="trn-table trn-table-striped trn-confirm-results-table" id="confirm-results-list">
				<tr>
					<th class="trn-confirm-results-table-event"><?php esc_html_e( 'Event', 'tournamatch' ); ?></th>
					<th class="trn-confirm-results-table-name"><?php esc_html_e( 'Name', 'tournamatch' ); ?></th>
					<th class="trn-confirm-results-table-result"><?php esc_html_e( 'Information', 'tournamatch' ); ?></th>
					<th class="trn-confirm-results-table-action">&nbsp;</th>
				</tr>
				<?php foreach ( $can_confirm_matches as $match ) : ?>
					<tr>
						<td class="trn-confirm-results-table-event"><?php echo esc_html( ucwords( $match->competition_type ) ); ?></td>
						<td class="trn-confirm-results-table-name">
							<a href="<?php trn_esc_route_e( "{$match->competition_type}.single", array( 'id' => $match->competition_id ) ); ?>">
								<?php echo esc_html( $match->name ); ?>
							</a>
						</td>
						<td class="trn-confirm-results-table-result">
							<?php

							$match_date = date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $match->match_date ) ) );

							$opponent = ( 0 < strlen( $match->one_result ) ) ? 'one_competitor_name' : 'two_competitor_name';

							if ( 'players' === $match->competitor_type ) {
								if ( ( 'won' === $match->one_result ) || ( 'won' === $match->two_result ) ) {
									/* translators: First is opponent; second is date and time. */
									echo sprintf( esc_html__( '%1$s reported that you lost on %2$s', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
								} elseif ( ( 'draw' === $match->one_result ) || ( 'draw' === $match->two_result ) ) {
									/* translators: First is opponent; second is date and time. */
									echo sprintf( esc_html__( '%1$s reported a draw against you on %2$s', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
								} else {
									/* translators: First is opponent; second is date and time. */
									echo sprintf( esc_html__( '%1$s reported that you won on %2$s', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
								}
							} else {
								if ( ( 'won' === $match->one_result ) || ( 'won' === $match->two_result ) ) {
									/* translators: First is opponent; second is date and time. */
									echo sprintf( esc_html__( 'Team %1$s reported that your team lost on %2$s', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
								} elseif ( ( 'draw' === $match->one_result ) || ( 'draw' === $match->two_result ) ) {
									/* translators: First is opponent; second is date and time. */
									echo sprintf( esc_html__( 'Team %1$s reported a draw against your team on %2$s', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
								} else {
									/* translators: First is opponent; second is date and time. */
									echo sprintf( esc_html__( 'Team %1$s reported that your team won on %2$s', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
								}
							}
							?>
						</td>
						<td class="action-link-cell trn-confirm-results-table-action">
							<a class="trn-button trn-button-sm trn-button-success" href="<?php trn_esc_route_e( 'matches.single.confirm', array( 'id' => $match->match_id ) ); ?>">
								<?php esc_html_e( 'Confirm', 'tournamatch' ); ?>
							</a>
							&nbsp;
							<?php echo do_shortcode( '[trn-dispute-match-button id="' . intval( $match->match_id ) . '"]' ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<p class='trn-text-center'>
				<?php esc_html_e( 'There are no results waiting for your confirmation.', 'tournamatch' ); ?>
			</p>
		<?php endif; ?>
	</section>
	<section>
	<h4 class='trn-text-center'><?php esc_html_e( 'Your Reported Results', 'tournamatch' ); ?></h4>
	<?php if ( 0 < count( $reported_matches ) ) : ?>
		<?php /* translators: An integer number of matches. */ ?>
		<p><?php printf( esc_html( _n( 'You are waiting on %d match to be confirmed.', 'You are waiting on %d matches to be confirmed.', count( $reported_matches ), 'tournamatch' ) ), count( $reported_matches ) ); ?></p>
		<table class="trn-table trn-table-striped trn-report-results-table" id="reported-results-list">
			<tr>
				<th class="trn-report-results-table-event"><?php esc_html_e( 'Event', 'tournamatch' ); ?></th>
				<th class="trn-report-results-table-name"><?php esc_html_e( 'Name', 'tournamatch' ); ?></th>
				<th class="trn-report-results-table-result"><?php esc_html_e( 'Information', 'tournamatch' ); ?></th>
				<th class="trn-report-results-table-action"><?php esc_html_e( 'Details', 'tournamatch' ); ?></th>
			</tr>
			<?php foreach ( $reported_matches as $match ) : ?>
				<tr>
					<td class="trn-report-results-table-event"><?php echo esc_html( ucwords( $match->competition_type ) ); ?></td>
					<td class="trn-report-results-table-name">
						<a href="<?php trn_esc_route_e( "{$match->competition_type}.single", array( 'id' => $match->competition_id ) ); ?>">
							<?php echo esc_html( $match->name ); ?>
						</a>
					</td>
					<td class="trn-report-results-table-result">
						<?php

						$match_date = date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $match->match_date ) ) );

						$opponent = ( 0 < strlen( $match->one_result ) ) ? 'two_competitor_name' : 'one_competitor_name';

						if ( 'players' === $match->competitor_type ) {
							if ( ( 'won' === $match->one_result ) || ( 'won' === $match->two_result ) ) {
								/* translators: First is name of the opponent, second is the date and time. */
								echo sprintf( esc_html__( 'You reported that you defeated %1$s on %2$s.', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
							} elseif ( ( 'draw' === $match->one_result ) || ( 'draw' === $match->two_result ) ) {
								/* translators: First is name of the opponent, second is the date and time. */
								echo sprintf( esc_html__( 'You reported a draw against %1$s on %2$s.', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
							} else {
								/* translators: First is name of the opponent, second is the date and time. */
								echo sprintf( esc_html__( 'You reported that you lost to %1$s on %2$s.', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
							}
						} else {
							if ( ( 'won' === $match->one_result ) || ( 'won' === $match->two_result ) ) {
								/* translators: First is name of the opponent, second is the date and time. */
								echo sprintf( esc_html__( 'You reported that you defeated team %1$s on %2$s.', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
							} elseif ( ( 'draw' === $match->one_result ) || ( 'draw' === $match->two_result ) ) {
								/* translators: First is name of the opponent, second is the date and time. */
								echo sprintf( esc_html__( 'You reported a draw against team %1$s on %2$s.', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
							} else {
								/* translators: First is name of the opponent, second is the date and time. */
								echo sprintf( esc_html__( 'You reported that you lost to team %1$s on %2$s.', 'tournamatch' ), esc_html( $match->$opponent ), esc_html( $match_date ) );
							}
						}
						?>
					</td>
					<td class="action-link-cell trn-report-results-table-action">
						<a class="trn-button trn-button-sm trn-button-danger trn-confirm-action-link trn-delete-match-action"
								href="#"
								data-match-id="<?php echo intval( $match->match_id ); ?>"
								data-confirm-title="<?php esc_html_e( 'Delete Match', 'tournamatch' ); ?>"
								data-confirm-message="<?php esc_html_e( 'Are you sure you want to delete this match?', 'tournamatch' ); ?>"
								data-modal-id="delete-match"
						>
							<?php esc_html_e( 'Delete', 'tournamatch' ); ?>
						</a>
						<?php if ( 'test' === TOURNAMATCH_ENV ) : ?>
							<input type="hidden" id="trn_match_id[<?php echo intval( $match->match_id ); ?>]"
									name="trn_match_id[<?php echo intval( $match->match_id ); ?>]"
									value="<?php echo intval( $match->match_id ); ?>">
							<input type="hidden" id="trn_match_reference[<?php echo intval( $match->match_id ); ?>]"
									name="trn_match_reference[<?php echo intval( $match->match_id ); ?>]"
									value="<?php echo esc_html( $match->confirm_hash ); ?>">
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php else : ?>
		<p class='trn-text-center'>
			<?php esc_html_e( 'There are no results you\'re waiting on for confirmation.', 'tournamatch' ); ?>
		</p>
	<?php endif; ?>
	</section>
	<section>
		<div class="trn-row">
			<div class="trn-col-sm-12">
				<h4 class="trn-text-center"><?php esc_html_e( 'Your Scheduled Matches', 'tournamatch' ); ?></h4>
				<?php
				$scheduled_matches = trn_get_scheduled_matches( $user_id );
				?>
				<?php /* translators: An integer number of matches. */ ?>
				<p><?php echo esc_html( sprintf( _n( 'You have %s match scheduled that has not been reported.', 'You have %s matches scheduled that have not been reported.', count( $scheduled_matches ), 'tournamatch' ), count( $scheduled_matches ) ) ); ?></p>
				<?php scheduled_matches_table( $scheduled_matches ); ?>
			</div>
		</div>
		<br>
		<div class="trn-row">
			<?php if ( trn_get_option( 'open_play_enabled' ) ) : ?>
				<div class="trn-col-md-6">
					<h4 class="trn-text-center"><?php esc_html__( 'Report Ladder Results', 'tournamatch' ); ?></h4>
					<?php
					$ladders = trn_get_user_open_play_ladders( $user_id );
					if ( 0 < count( $ladders ) ) {
						?>
						<form id="report-ladder-form" class="form-inline trn-text-center"
								action="<?php trn_esc_route_e( 'matches.single.create' ); ?>" method="post">
							<div class="trn-form-group">
								<label for="ladder_id"
										class="control-label"><?php esc_html_e( 'Select Ladder', 'tournamatch' ); ?></label>
								<select id="ladder_id" class="trn-form-control mx-sm-3" name='ladder_id'>
									<?php foreach ( $ladders as $ladder ) : ?>
										<option value="<?php echo intval( $ladder->ladder_id ); ?>"><?php echo esc_html( $ladder->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<button class="trn-button trn-button-sm" type='submit'
									id="report-ladder-button"><?php esc_html_e( 'Report', 'tournamatch' ); ?></button>
						</form>
						<?php
					} else {
						echo '<p class="trn-text-center">' . esc_html__( 'You are not participating in any active ladders.', 'tournamatch' ) . '</p>';
					}
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php

$options = array(
	'api_url'       => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'    => wp_create_nonce( 'wp_rest' ),
	'redirect_link' => trn_route( 'report.page' ),
	'language'      => array(
		'failure' => esc_html__( 'Error', 'tournamatch' ),
	),
);

wp_enqueue_script( 'trn-delete-match' );
wp_register_script( 'trn-report-dashboard', plugins_url( '../dist/js/report-dashboard.js', __FILE__ ), array( 'tournamatch' ), '4.3.5', true );
wp_localize_script( 'trn-report-dashboard', 'trn_report_dashboard_options', $options );
wp_enqueue_script( 'trn-report-dashboard' );

trn_get_footer();

get_footer();
