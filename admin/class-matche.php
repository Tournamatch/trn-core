<?php
/**
 * Manages match admin pages.
 *
 * @link  https://www.tournamatch.com
 * @since 4.0.0
 *
 * @package Tournamatch
 */

namespace Tournamatch\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Manages match admin pages.
 *
 * @since 4.0.0
 *
 * @package Tournamatch
 * @author  Tournamatch <support@tournamatch.com>
 */
class Matche {

	/**
	 * Initializes the ladder admin components.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'setup_menu' ) );

		$post_hooks = array(
			'trn-save-new-match' => array( $this, 'save' ),
			'trn-update-match'   => array( $this, 'update' ),
		);

		array_walk(
			$post_hooks,
			function( $callable, $action ) {
				add_action( "admin_post_$action", $callable );
			}
		);

		add_action( 'load-ladders_page_trn-ladders-matches', array( $this, 'pre_headers' ) );
		add_action( 'load-tournaments_page_trn-tournaments-matches', array( $this, 'pre_headers' ) );
	}

	/**
	 * Initialize the menu for match screens.
	 *
	 * @since 4.0.0
	 */
	public function setup_menu() {
		add_submenu_page(
			'trn-tournaments',
			esc_html__( 'Matches', 'tournamatch' ),
			esc_html__( 'Matches', 'tournamatch' ),
			'manage_tournamatch',
			'trn-tournaments-matches',
			array( $this, 'tournament_matches' )
		);

		add_submenu_page(
			'trn-ladders',
			esc_html__( 'Matches', 'tournamatch' ),
			esc_html__( 'Matches', 'tournamatch' ),
			'manage_tournamatch',
			'trn-ladders-matches',
			array( $this, 'ladder_matches' )
		);
	}

	/**
	 * Handles saving an existing ladder match.
	 *
	 * @since 4.0.0
	 */
	public function update() {
		global $wpdb;

		check_admin_referer( 'tournamatch-update-match' );

		$match_id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
		$match          = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ) );
		$competition_id = $match->competition_id;
		$one_result     = isset( $_REQUEST['one_result'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['one_result'] ) ) : null;
		$one_comment    = isset( $_REQUEST['one_comment'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['one_comment'] ) ) : null;
		$two_result     = isset( $_REQUEST['two_result'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['two_result'] ) ) : null;
		$two_comment    = isset( $_REQUEST['two_comment'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['two_comment'] ) ) : null;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $competition_id ) );

		$data = array(
			'one_result'  => $one_result,
			'one_comment' => $one_comment,
			'two_result'  => $two_result,
			'two_comment' => $two_comment,
		);
		$wpdb->update( $wpdb->prefix . 'trn_matches', $data, array( 'match_id' => $match_id ) );

		if ( $match->one_result !== $one_result ) {

			if ( 'players' === $ladder->competitor_type ) {
				$one_pre_change = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $match->one_competitor_id ) );
				$two_pre_change = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $match->two_competitor_id ) );
			} else {
				$one_pre_change = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $match->one_competitor_id ) );
				$two_pre_change = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $match->two_competitor_id ) );
			}

			// determine how we would roll back the win/losses/tie counts.
			if ( 'won' === $match->one_result ) {
				$one_update = array(
					'wins' => $one_pre_change->wins - 1,
				);
				$two_update = array(
					'losses' => $two_pre_change->losses - 1,
				);
			} elseif ( 'lost' === $match->one_result ) {
				$one_update = array(
					'losses' => $one_pre_change->losses - 1,
				);
				$two_update = array(
					'wins' => $two_pre_change->wins - 1,
				);
			} else {
				$one_update = array(
					'draws' => $one_pre_change->draws - 1,
				);
				$two_update = array(
					'draws' => $two_pre_change->draws - 1,
				);
			}

			// determine how we would update the new win/losses/ties.
			if ( 'won' === $one_result ) {
				$one_update['wins']   = $one_pre_change->wins + 1;
				$two_update['losses'] = $two_pre_change->losses + 1;
			} elseif ( 'lost' === $one_result ) {
				$one_update['losses'] = $one_pre_change->losses + 1;
				$two_update['wins']   = $two_pre_change->wins + 1;
			} else {
				$one_update['draws'] = $one_pre_change->draws + 1;
				$two_update['draws'] = $two_pre_change->draws + 1;
			}

			// update the individual career stats.
			if ( 'players' === $ladder->competitor_type ) {
				$wpdb->update( $wpdb->prefix . 'trn_players_profiles', $one_update, array( 'user_id' => $match->one_competitor_id ) );
				$wpdb->update( $wpdb->prefix . 'trn_players_profiles', $two_update, array( 'user_id' => $match->two_competitor_id ) );
			} else {
				$wpdb->update( $wpdb->prefix . 'trn_teams', $one_update, array( 'team_id' => $match->one_competitor_id ) );
				$wpdb->update( $wpdb->prefix . 'trn_teams', $two_update, array( 'team_id' => $match->two_competitor_id ) );
			}
		}

		wp_safe_redirect( trn_route( 'admin.ladders.matches', array( 'id' => $match_id ) ) );
		exit;
	}

	/**
	 * Handles saving a new ladder match.
	 *
	 * @since 4.0.0
	 */
	public function save() {
		global $wpdb;

		check_admin_referer( 'tournamatch-save-new-match' );

		$competition_id = isset( $_REQUEST['competition_id'] ) ? intval( $_REQUEST['competition_id'] ) : null;
		$one_id         = isset( $_REQUEST['one_id'] ) ? intval( $_REQUEST['one_id'] ) : null;
		$one_result     = isset( $_REQUEST['one_result'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['one_result'] ) ) : null;
		$one_comment    = isset( $_REQUEST['one_comment'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['one_comment'] ) ) : null;
		$two_id         = isset( $_REQUEST['two_id'] ) ? intval( $_REQUEST['two_id'] ) : null;
		$two_result     = isset( $_REQUEST['two_result'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['two_result'] ) ) : null;
		$two_comment    = isset( $_REQUEST['two_comment'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['two_comment'] ) ) : null;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $competition_id ) );

		// insert new match.
		$data = array(
			'competition_id'      => $competition_id,
			'competition_type'    => 'ladders',
			'one_competitor_id'   => $one_id,
			'one_competitor_type' => $ladder->competitor_type,
			'one_ip'              => '',
			'one_result'          => $one_result,
			'one_comment'         => $one_comment,
			'two_competitor_id'   => $two_id,
			'two_competitor_type' => $ladder->competitor_type,
			'two_ip'              => '',
			'two_result'          => $two_result,
			'two_comment'         => $two_comment,
			'match_date'          => $wpdb->get_var( 'SELECT UTC_TIMESTAMP()' ),
			'match_status'        => 'reported',
		);

		$wpdb->insert( $wpdb->prefix . 'trn_matches', $data );

		$match_id = $wpdb->insert_id;

		$service = new \Tournamatch\Services\Matche();
		$service->confirm(
			array(
				'id'      => $match_id,
				'comment' => esc_html__(
					'Admin confirmed',
					'tournamatch'
				),
			)
		);

		wp_safe_redirect( trn_route( 'admin.ladders.matches' ) );
		exit;
	}

	/**
	 * Handles displaying a match form.
	 *
	 * @since 4.0.0
	 *
	 * @param string $action The form action.
	 * @param array  $arguments A collection of fields for the form.
	 * @param string $form_state Indicates whether this is a save or update.
	 */
	private function form( $action, $arguments, $form_state = 'create' ) {

		$uses_draws = ( intval( trn_get_option( 'uses_draws' ) ) === 1 );

		$match            = $arguments['match'];
		$competition_type = $arguments['competition_type'];
		$competition_name = $arguments['competition_name'];
		$one_name         = $arguments['one_name'];
		$two_name         = $arguments['two_name'];

		?>
		<style type="text/css">
			#trn-match-details .form-field input, #trn-match-details .form-field select {
				width: 25em;
			}
			@media screen and (max-width: 782px) {
				#trn-match-details .form-field input, #trn-match-details .form-field select {
					width: 100%;
				}
			}
		</style>
		<form action="<?php echo esc_html( $action ); ?>" method="post" id="trn-match-details">
			<table class="form-table" role="presentation">
				<tr class="form-field">
					<th scope="row">
						<label for="competition" class="col-sm-4 col-lg-3 control-label"><?php echo esc_html( ucwords( $competition_type ) ); ?>:</label>
					</th>
					<td>
						<p class="form-control-static"><?php echo esc_html( $competition_name ); ?> </p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="one_result"><?php echo esc_html( $one_name ); ?> <?php esc_html_e( 'Result:', 'tournamatch' ); ?></label>
					</th>
					<td>
						<select id="one_result" name="one_result">
							<option value='<?php echo esc_html( 'won' ); ?>' <?php echo ( isset( $match->one_result ) && ( 'won' === $match->one_result ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Won', 'tournamatch' ); ?></option>
							<option value='<?php echo esc_html( 'lost' ); ?>' <?php echo ( isset( $match->one_result ) && ( 'lost' === $match->one_result ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Lost', 'tournamatch' ); ?></option>
							<?php if ( $uses_draws ) : ?>
								<option value='<?php echo esc_html( 'draw' ); ?>' <?php echo ( isset( $match->one_result ) && ( 'draw' === $match->one_result ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Draw', 'tournamatch' ); ?></option>
							<?php endif; ?>
						</select>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="one_comment"><?php echo esc_html( $one_name ); ?> <?php esc_html_e( 'Comment:', 'tournamatch' ); ?></label>
					</th>
					<td>
						<textarea id="one_comment" name="one_comment" rows="10"><?php echo isset( $match->one_comment ) ? esc_html( $match->one_comment ) : ''; ?></textarea>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="" class="col-sm-3 control-label"><?php echo esc_html( $two_name ); ?> <?php esc_html_e( 'Result:', 'tournamatch' ); ?></label>
					</th>
					<td>
						<p class="form-control-static" id="two_result_string"></p>
						<input type="hidden" name="two_result" id="two_result">
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="two_comment"><?php echo esc_html( $two_name ); ?> <?php esc_html_e( 'Comment:', 'tournamatch' ); ?></label>
					</th>
					<td>
						<textarea id="two_comment" name="two_comment" rows="10"><?php echo isset( $match->two_comment ) ? esc_html( $match->two_comment ) : ''; ?></textarea>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type='submit' value='<?php echo ( ( 'create' === $form_state ) ? esc_html__( 'Report', 'tournamatch' ) : esc_html__( 'Save', 'tournamatch' ) ); ?>' class="button button-primary">
				<a href="#" class="button button-cancel"><?php esc_html_e( 'Cancel', 'tournamatch' ); ?></a>
			</p>
		</form>

		<?php

		$options = array(
			'won'  => esc_html__( 'Won', 'tournamatch' ),
			'lost' => esc_html__( 'Lost', 'tournamatch' ),
			'draw' => esc_html__( 'Draw', 'tournamatch' ),
		);

		wp_register_script( 'trn_match_details_form', plugins_url( '../dist/js/match-details-form.js', __FILE__ ), array( 'tournamatch' ), '3.20.0', true );
		wp_localize_script( 'trn_match_details_form', 'trn_match_details_options', $options );
		wp_enqueue_script( 'trn_match_details_form' );
	}

	/**
	 * Displays the ladder matches page.
	 *
	 * @since 4.0.0
	 */
	public function ladder_matches() {
		global $wpdb;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		switch ( $action ) {

			case 'select-ladder':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$ladders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `status` = %s", 'active' ) );
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Ladder Matches', 'tournamatch' ); ?></h1>
					<hr class="wp-header-end">
					<form id="trn-select-ladder-form"
							action="<?php trn_esc_route_e( 'admin.matches.select-competitors' ); ?>" method="post">
						<table class="form-table" role="presentation">
							<tr class="form-field">
								<th scope="row">
									<label for="competition_id"><?php esc_html_e( 'Select Ladder', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="competition_id" name="competition_id">
										<?php foreach ( $ladders as $ladder ) : ?>
											<option value="<?php echo intval( $ladder->ladder_id ); ?>"><?php echo esc_html( $ladder->name ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Select the ladder for which to report a new match result.', 'tournamatch' ); ?></p>
								</td>
							</tr>
						</table>
						<p class="submit">
							<?php wp_nonce_field( 'tournamatch-bulk-matches' ); ?>
							<input type="submit" id="select-ladder-button" value="Report" class="button button-primary">
						</p>
					</form>
				</div>
				<?php
				break;

			case 'select-competitors':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$ladder_id = isset( $_REQUEST['competition_id'] ) ? intval( $_REQUEST['competition_id'] ) : null;

				$competition_type = 'ladder';
				$competition_id   = intval( $ladder_id );
				$ladders          = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `status` = %s AND `ladder_id` = %d", 'active', $ladder_id ) );
				if ( 'players' === $ladders->competitor_type ) {
					$competitors = $wpdb->get_results( $wpdb->prepare( "SELECT `le`.*, `p`.`display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `le`.`competitor_id` = `p`.`user_id` WHERE `ladder_id` = %d ORDER BY `p`.`display_name` ASC", $ladder_id ) );
				} else {
					$competitors = $wpdb->get_results( $wpdb->prepare( "SELECT `le`.*, `t`.`name` AS `name` FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `le`.`competitor_id` = `t`.`team_id` WHERE `ladder_id` = %d ORDER BY `t`.`name` ASC", $ladder_id ) );
				}

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline">
						<?php esc_html_e( 'Ladder Matches', 'tournamatch' ); ?>
					</h1>
					<hr class="wp-header-end">
					<form action="<?php echo esc_url( trn_route( 'admin.ladders.report-match' ) . '&action=report-match' ); ?>"
							method="post" id="trn-select-competitors">
						<table class="form-table" role="presentation">
							<tr class="form-field">
								<th scope="row">
									<label for="one_id"><?php esc_html_e( 'First Competitor', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="one_id" name="one_id">
										<?php foreach ( $competitors as $competitor ) : ?>
											<option value="<?php echo intval( $competitor->competitor_id ); ?>"><?php echo esc_html( $competitor->name ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr class="form-field">
								<th scope="row">
									<label for="two_id"><?php esc_html_e( 'Second Competitor', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="two_id" name="two_id">
										<?php foreach ( $competitors as $competitor ) : ?>
											<option value="<?php echo intval( $competitor->competitor_id ); ?>"><?php echo esc_html( $competitor->name ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
						</table>
						<p>
							<?php wp_nonce_field( 'tournamatch-bulk-matches' ); ?>
							<input type="hidden" name="competition_type" id="competition_type"
									value="<?php echo esc_html( $competition_type ); ?>">
							<input type="hidden" name="competition_id" id="competition_id"
									value="<?php echo intval( $competition_id ); ?>">
							<input class="button button-primary" type="submit"
									value="<?php esc_html_e( 'Report', 'tournamatch' ); ?>">
						</p>
					</form>

					<?php

					$options = array(
						'unique_message' => esc_html__( 'First Competitor and Second Competitor must be unique.', 'tournamatch' ),
					);

					wp_register_script( 'trn_match_competitors_form', plugins_url( '../dist/js/match-competitors-form.js', __FILE__ ), array( 'tournamatch' ), '3.20.0', true );
					wp_localize_script( 'trn_match_competitors_form', 'trn_match_competitors_options', $options );
					wp_enqueue_script( 'trn_match_competitors_form' );

					?>
				</div>
				<?php
				break;

			case 'report-match':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$competition_type = isset( $_REQUEST['competition_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['competition_type'] ) ) : null;
				$competition_id   = isset( $_REQUEST['competition_id'] ) ? intval( $_REQUEST['competition_id'] ) : null;
				$one_id           = isset( $_REQUEST['one_id'] ) ? intval( $_REQUEST['one_id'] ) : null;
				$two_id           = isset( $_REQUEST['two_id'] ) ? intval( $_REQUEST['two_id'] ) : null;

				$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $competition_id ) );
				if ( 'players' === $ladder->competitor_type ) {
					$one_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $one_id ) );
					$two_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $two_id ) );
				} else {
					$one_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `name` AS `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $one_id ) );
					$two_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `name` AS `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $two_id ) );
				}

				$one_name         = $one_competitor->name;
				$two_name         = $two_competitor->name;
				$competition_name = $ladder->name;

				$nonce              = wp_create_nonce( 'tournamatch-save-new-match' );
				$action             = esc_url( admin_url( 'admin-post.php?action=trn-save-new-match' ) . '&competition_type=' . $competition_type . '&competition_id=' . $competition_id . '&one_id=' . $one_id . '&two_id=' . $two_id . '&_wpnonce=' . $nonce );
				$match              = new \stdClass();
				$match->one_comment = esc_html__( 'Admin Reported', 'tournamatch' );
				$match->two_comment = esc_html__( 'Admin Confirmed', 'tournamatch' );

				$arguments = array(
					'one_name'         => $one_name,
					'two_name'         => $two_name,
					'competition_name' => $competition_name,
					'competition_type' => $competition_type,
					'match'            => $match,
				);

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Manage Matches', 'tournamatch' ); ?></h1>
					<h2 class="title"><?php esc_html_e( 'Report Result', 'tournamatch' ); ?></h2>
					<?php $this->form( $action, $arguments, 'create' ); ?>
				</div>
				<?php
				break;
			case 'edit-match':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$match_id         = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$match            = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ) );
				$competition_type = 'ladder';
				$competition_id   = $match->competition_id;
				$one_id           = $match->one_competitor_id;
				$two_id           = $match->two_competitor_id;

				$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", $competition_id ) );
				if ( 'players' === $ladder->competitor_type ) {
					$one_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $one_id ) );
					$two_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $two_id ) );
				} else {
					$one_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `name` AS `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $one_id ) );
					$two_competitor = $wpdb->get_row( $wpdb->prepare( "SELECT `name` AS `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $two_id ) );
				}

				$one_name         = $one_competitor->name;
				$two_name         = $two_competitor->name;
				$competition_name = $ladder->name;

				$nonce     = wp_create_nonce( 'tournamatch-update-match' );
				$action    = trn_route(
					'admin.ladders.save-match',
					array(
						'id'       => $match_id,
						'_wpnonce' => $nonce,
					)
				);
				$arguments = array(
					'one_name'         => $one_name,
					'two_name'         => $two_name,
					'competition_name' => $competition_name,
					'competition_type' => $competition_type,
					'match'            => $match,
				);

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline">
						<?php esc_html_e( 'Manage Matches', 'tournamatch' ); ?>
					</h1>
					<h2 class="title"><?php esc_html_e( 'Edit Result', 'tournamatch' ); ?></h2>
					<div class="notice notice-info">
						<p>
							<?php /* translators: HTML <strong> and </strong> tags. */ ?>
							<?php printf( esc_html__( '%1$s Note: %2$s Editing a match result will not change the points/rating on a ladder.', 'tournamatch' ), '<strong>', '</strong>' ); ?>
							<?php esc_html_e( 'The wins/losses/ties and result of this match will update, but you must edit the ladder to change a competitor\'s points or rating.', 'tournamatch' ); ?>
						</p>
					</div>
					<?php if ( isset( $success ) ) : ?>
						<div class="notice notice-success">
							<?php echo esc_html( $success ); ?>
						</div>
					<?php endif; ?>
					<?php $this->form( $action, $arguments, 'update' ); ?>
				</div>
				<?php
				break;
			default:
				$list_table = new \Tournamatch_Match_List_Table();
				$list_table->prepare_items();

				$nonce = wp_create_nonce( 'tournamatch-bulk-matches' );

				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Ladder Matches', 'tournamatch' ); ?></h1>
					<?php
					echo '<a href="admin.php?page=trn-ladders-matches&action=select-ladder&_wpnonce=' . esc_html( $nonce ) . '" class="page-title-action">' . esc_html__( 'Report Match', 'tournamatch' ) . '</a>';

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$search_text = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

					if ( 0 < strlen( $search_text ) ) {
						echo '<span class="subtitle">';
						printf(
							/* translators: %s: Search query. */
							esc_html__( 'Search results for: %s', 'tournamatch' ),
							'<strong>' . esc_attr( $search_text ) . '</strong>'
						);
						echo '</span>';
					}
					?>
					<hr class="wp-header-end">
					<form method="get" id="trn_ladder_matches_list_table_filter">
						<?php
						$list_table->views();
						$list_table->search_box( esc_html__( 'Search Ladder Matches', 'tournamatch' ), 'trn_search_ladder_matches_input' );
						?>
						<input type="hidden" name="page" value="ladder-matches"/>
						<?php
						$list_table->display();
						?>
					</form>
				</div>
				<?php
				break;
		}
	}

	/**
	 * Displays the tournament matches page.
	 *
	 * @since 4.0.0
	 */
	public function tournament_matches() {
		global $wpdb;

		$list_table = new \Tournamatch_Match_List_Table( array( 'competition_type' => 'tournaments' ) );
		$list_table->prepare_items();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Tournament Matches', 'tournamatch' ); ?></h1>
			<?php

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$search_text = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

			if ( 0 < strlen( $search_text ) ) {
				echo '<span class="subtitle">';
				printf(
					/* translators: %s: Search query. */
					esc_html__( 'Search results for: %s', 'tournamatch' ),
					'<strong>' . esc_attr( $search_text ) . '</strong>'
				);
				echo '</span>';
			}
			?>
			<hr class="wp-header-end">
			<form method="get" id="trn_tournament_matches_list_table_filter">
				<?php
				$list_table->views();
				$list_table->search_box( esc_html__( 'Search Tournament Matches', 'tournamatch' ), 'trn_search_tournament_matches_input' );
				?>
				<input type="hidden" name="page" value="tournament-matches"/>
				<?php
				$list_table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handles pages that must be processed before any headers are sent.
	 *
	 * @since 4.0.0
	 */
	public function pre_headers() {
		global $wpdb;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'dashboard';

		switch ( $action ) {
			case 'clear':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$match_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;

				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `one_result` = %s, `one_ip` = %s, `two_result` = %s, `two_ip` = %s, `match_status` = %s WHERE `match_id` = %d", '', '', '', '', 'scheduled', $match_id ) );

				wp_safe_redirect( trn_route( 'admin.tournaments.matches' ) );
				break;

			case 'advance':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$match_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$winner   = isset( $_GET['winner_id'] ) ? intval( $_GET['winner_id'] ) : null;

				$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ), ARRAY_A );

				if ( intval( $match['one_competitor_id'] ) === $winner ) {
					$one_result = 'won';
					$two_result = 'lost';
					$winner_id  = $match['one_competitor_id'];
					$loser_id   = $match['two_competitor_id'];
				} else {
					$one_result = 'lost';
					$two_result = 'won';
					$winner_id  = $match['two_competitor_id'];
					$loser_id   = $match['one_competitor_id'];
				}

				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `one_result` = %s, `one_comment` = %s, `two_result` = %s, `two_comment` = %s, `match_date` = UTC_TIMESTAMP(), `match_status` = %s WHERE `match_id` = %d", $one_result, 'Admin Reported', $two_result, 'Admin Confirmed', 'confirmed', $match_id ) );

				update_tournament(
					$match['competition_id'],
					array(
						'match_id'  => $match_id,
						'winner_id' => $winner_id,
					)
				);

				$competitor_type = $match['one_competitor_type'];

				// Update career results.
				update_career_wins( $winner_id, $competitor_type );
				update_career_losses( $loser_id, $competitor_type );

				wp_safe_redirect( trn_route( 'admin.tournaments.matches' ) );
				break;

			case 'delete':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$match_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;

				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d LIMIT 1", $match_id ) );

				// TODO update the player or team wins, losses, draws, and ladder entry table.

				wp_safe_redirect( trn_route( 'admin.ladders.matches' ) );
				break;

			case 'resolve':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$match_id  = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$winner_id = isset( $_GET['winner_id'] ) ? intval( $_GET['winner_id'] ) : null;
				$match     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ), ARRAY_A );

				if ( intval( $match['one_competitor_id'] ) === intval( $winner_id ) ) {
					$one_result = 'won';
					$two_result = 'lost';
					$winner_id  = $match['one_competitor_id'];
					$loser_id   = $match['two_competitor_id'];
				} elseif ( intval( $match['two_competitor_id'] ) === intval( $winner_id ) ) {
					$one_result = 'lost';
					$two_result = 'won';
					$winner_id  = $match['two_competitor_id'];
					$loser_id   = $match['one_competitor_id'];
				} else {
					$one_result = 'draw';
					$two_result = 'draw';
					$winner_id  = $match['two_competitor_id'];
					$loser_id   = $match['one_competitor_id'];
				}

				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `one_result` = %s, `two_result` = %s, `match_status` = %s WHERE `match_id` = %d", $one_result, $two_result, 'confirmed', $match_id ) );

				if ( 'ladders' === $match['competition_type'] ) {
					$arguments = array(
						$match['one_competitor_id'] => $one_result,
						$match['two_competitor_id'] => $two_result,
					);
					update_ladder( $match['competition_id'], $arguments );
				} else {
					update_tournament(
						$match['competition_id'],
						array(
							'match_id'  => $match_id,
							'winner_id' => $winner_id,
						)
					);
				}

				// Since both one and two competitor must match, we can just use the one competitor type here.
				$competitor_type = $match['one_competitor_type'];

				// Update career results. Since a draw means the one and two result match, we can just look at the one result.
				if ( 'draw' === $one_result ) {
					update_career_draws( $winner_id, $competitor_type );
					update_career_draws( $loser_id, $competitor_type );
				} else {
					update_career_wins( $winner_id, $competitor_type );
					update_career_losses( $loser_id, $competitor_type );
				}

				wp_safe_redirect( trn_route( "admin.{$match['competition_type']}.matches" ) );
				break;

			case 'confirm':
				check_admin_referer( 'tournamatch-bulk-matches' );

				$match_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;

				$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ), ARRAY_A );

				$service = new \Tournamatch\Services\Matche();
				$service->confirm(
					array(
						'id'      => $match_id,
						'comment' => esc_html__(
							'Admin confirmed',
							'tournamatch'
						),
					)
				);

				if ( 'ladders' === $match['competition_type'] ) {
					wp_safe_redirect( trn_route( 'admin.ladders.matches' ) );
				} else {
					wp_safe_redirect( trn_route( 'admin.tournaments.matches' ) );
				}

				break;
		}
	}
}

new Matche();
