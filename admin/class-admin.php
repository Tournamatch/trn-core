<?php
/**
 * Manages the Tournamatch admin components.
 *
 * @link       https://www.tournamatch.com
 * @since      3.0.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages the Tournamatch admin components.
 *
 * @since      3.0.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Admin {

	/**
	 * Initializes the Tournamatch admin components.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Sets up the admin hooks, actions, and filters.
	 *
	 * @since 3.0.0
	 *
	 * @access private
	 */
	private function setup_actions() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		$post_hooks = array(
			'trn-save-settings' => array( $this, 'save_settings' ),
		);

		array_walk(
			$post_hooks,
			function( $callable, $action ) {
				add_action( "admin_post_$action", $callable );
			}
		);
	}

	/**
	 * Creates the admin menu.
	 *
	 * @since 3.0.0
	 */
	public function admin_menu() {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$icon = base64_encode( '<svg id="svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" viewBox="0, 0, 400,400"><g id="svgg"><path id="path0" d="M161.636 26.391 C 47.301 48.892,-19.438 179.890,28.741 287.246 C 42.316 317.495,45.667 318.309,63.043 295.580 C 75.590 279.169,132.151 230.435,138.651 230.435 C 140.388 230.435,140.977 213.042,139.961 191.784 C 137.942 149.559,145.713 115.891,160.584 102.432 C 180.987 83.968,252.174 143.435,252.174 178.943 C 252.174 183.744,262.567 184.951,303.261 184.876 C 331.359 184.825,360.333 186.126,367.648 187.769 L 380.948 190.755 377.958 165.399 C 366.225 65.892,271.201 4.830,161.636 26.391 M220.720 314.193 C 208.481 333.869,195.652 365.926,195.652 376.831 C 195.652 383.377,205.646 382.691,249.153 373.160 C 268.877 368.839,321.739 337.935,321.739 330.726 C 321.739 324.975,268.232 300.282,250.000 297.618 C 233.186 295.162,232.214 295.712,220.720 314.193 " stroke="none" fill="#9ca2a7" fill-rule="evenodd"></path></g></svg>' );

		add_menu_page(
			'Tournamatch',
			'Tournamatch',
			'manage_tournamatch',
			'tournamatch',
			array( $this, 'tournamatch' ),
			'data:image/svg+xml;base64,' . $icon
		);

		add_submenu_page(
			'tournamatch',
			esc_html__( 'Tools', 'tournamatch' ),
			esc_html__( 'Tools', 'tournamatch' ),
			'manage_tournamatch',
			'trn-tools',
			array( $this, 'tools' )
		);
		add_submenu_page(
			'tournamatch',
			esc_html__( 'Settings', 'tournamatch' ),
			esc_html__( 'Settings', 'tournamatch' ),
			'manage_tournamatch',
			'trn-settings',
			array( $this, 'settings' )
		);
	}

	/**
	 * Displays the main Tournamatch menu.
	 *
	 * @since 4.0.0
	 */
	public function tournamatch() {
		$version = TOURNAMATCH_VERSION;

		$shortcuts = array(
			'challenges' => array(
				'content' => __( 'Challenges', 'tournamatch' ),
				'link'    => trn_route( 'challenges.archive' ),
			),
			'games' => array(
				'content' => __( 'Games', 'tournamatch' ),
				'link'    => trn_route( 'games.archive' ),
			),
			'players' => array(
				'content' => __( 'Players', 'tournamatch' ),
				'link'    => trn_route( 'players.archive' ),
			),
			'teams' => array(
				'content' => __( 'Teams', 'tournamatch' ),
				'link'    => trn_route( 'teams.archive' ),
			),
			'matches' => array(
				'content' => __( 'Matches', 'tournamatch' ),
				'link'    => trn_route( 'matches.archive' ),
			),
			'ladders' => array(
				'content' => __( 'Ladders', 'tournamatch' ),
				'link'    => trn_route( 'ladders.archive' ),
			),
			'tournaments' => array(
				'content' => __( 'Tournaments', 'tournamatch' ),
				'link'    => trn_route( 'tournaments.archive' ),
			),
			'user-dashboard' => array(
				'content' => __( 'User Dashboard', 'tournamatch' ),
				'link'    => trn_route( 'players.single.dashboard' ),
			),
			'results-dashboard' => array(
				'content' => __( 'Results Dashboard', 'tournamatch' ),
				'link'    => trn_route( 'report.page' ),
			)
		);
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Tournamatch</h1>
			<h2><?php esc_html_e( 'Quick Start', 'tournamatch' ); ?></h2>
			<ol>
				<?php /* translators: Opening and closing anchor tags. */ ?>
				<li><?php printf( esc_html__( 'Create a %1$sgame%2$s or two for organizing ladder and tournament events.', 'tournamatch' ), '<a href="' . esc_url( trn_route( 'admin.games' ) ) . '">', '</a>' ); ?></li>
				<?php /* translators: Opening and closing anchor tags. */ ?>
				<li><?php printf( esc_html__( 'Update your %1$smain menu%2$s with links to the pages created in the previous step.', 'tournamatch' ), '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">', '</a>' ); ?></li>
				<?php /* translators: Opening and closing anchor tags. */ ?>
				<li><?php printf( esc_html__( 'Continue on creating your first %1$stournament%2$s or %3$sladder%4$s.', 'tournamatch' ), '<a href="' . esc_url( trn_route( 'admin.tournaments' ) ) . '">', '</a>', '<a href="' . esc_url( trn_route( 'admin.ladders' ) ) . '">', '</a>' ); ?></li>
			</ol>
			<h2><?php esc_html_e( 'Support', 'tournamatch' ); ?></h2>
			<?php /* translators: Opening and closing anchor tags. */ ?>
			<p><?php printf( esc_html__( 'For support, please email us at support@tournamatch.com or %1$sopen a support%2$s ticket.', 'tournamatch' ), '<a href="https://www.tournamatch.com/support" target="_blank">', '</a>' ); ?></p>
			<h2><?php esc_html_e( 'Version', 'tournamatch' ); ?></h2>
			<?php /* translators: Semantic version number such as 10.9.86. */ ?>
			<p><?php printf( esc_html__( 'You are using Tournamatch version %s.', 'tournamatch' ), esc_html( $version ) ); ?></p>
			<h2><?php esc_html_e( 'Shortcuts', 'tournamatch' ); ?></h2>
			<p><?php esc_html_e( 'You should consider adding links to the following front end (user-facing) pages.', 'tournamatch' ); ?></p>
			<ul>
				<?php foreach( $shortcuts as $shortcut ): ?>
				<li><?php echo esc_html( $shortcut['content'] ); ?> (<a href="<?php echo esc_url( $shortcut['link'] ); ?>"><?php echo esc_url( $shortcut['link'] ); ?></a>) </li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Displays the tools screen.
	 *
	 * @since 4.0.0
	 */
	public function tools() {
		global $wpdb;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null;

		if ( ! is_null( $action ) ) {
			if ( 'clear-data' === $action ) {

				check_admin_referer( 'tournamatch-admin-tools' );

				$confirm = isset( $_POST['confirm'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm'] ) ) : '';

				if ( 'DELETE' === $confirm ) {
					$tables = array(
						'challenges',
						'ladders',
						'ladders_entries',
						'matches',
						'teams',
						'teams_members',
						'teams_members_invitations',
						'teams_members_requests',
						'tournaments',
						'tournaments_entries',
					);

					foreach ( $tables as $table ) {
						//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}trn_{$table}`" );
					}

					$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_players_profiles` SET `wins` = %d, `losses` = %d, `draws` = %d", 0, 0, 0 ) );
					echo '<meta http-equiv="refresh" content="0;url=' . esc_url( trn_route( 'admin.tools' ) ) . '">';
					die();
				}
			}
		} else {
			?>
			<style type="text/css">
				#trn-clear-data-button {
					background: #d63638;
					border-color: #d63638;
				}

				#trn-clear-data-button:active, #trn-clear-data-button:hover {
					background: #b22426;
				}
			</style>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Tools', 'tournamatch' ); ?></h1>
				<div id="col-container">
					<div id="col-left">
						<div class="col-wrap">
							<div class="form-wrap">
								<h2><?php esc_html_e( 'Clear Data', 'tournamatch' ); ?></h2>
								<div class="notice notice-error inline">
									<p>
										<?php esc_html_e( 'This action is irreversible. All data not backed up will be lost.', 'tournamatch' ); ?>
									</p>
								</div>
								<form method="post" action="<?php trn_esc_route_e( 'admin.clear-data' ); ?>">
									<p>
										<?php esc_html_e( 'Use this form to clear all ladder, tournament, teams, challenge, and match history.', 'tournamatch' ); ?>
										<?php /* translators: HTML <strong> and </strong> tags. */ ?>
										<?php printf( esc_html__( 'This will %1$s not %2$s clear your current settings or games.', 'tournamatch' ), '<strong>', '</strong>' ); ?>
										<?php esc_html_e( 'You will need to manually delete game data or reinstall to reset everything.', 'tournamatch' ); ?>
									</p>
									<p>
										<?php /* translators: HTML <strong> and </strong> tags. */ ?>
										<?php printf( esc_html__( 'Enter the word %1$s DELETE %2$s in the \'Confirm\' box below to proceed.', 'tournamatch' ), '<strong>', '</strong>' ); ?>
									</p>
									<div class="form-field form-required">
										<label for="confirm"><?php esc_html_e( 'Confirm', 'tournamatch' ); ?></label>
										<input type="text" id="confirm" name="confirm" class="form-control" required>
									</div>
									<p class="submit">
										<input id="trn-clear-data-button" type="submit"
												class="button button-primary button-danger"
												value="<?php esc_html_e( 'Clear Data', 'tournamatch' ); ?>">
									</p>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Handles saving the settings.
	 *
	 * @since 4.0.0
	 */
	public function save_settings() {

		check_admin_referer( 'tournamatch-save-settings' );

		$settings = trn_get_default_options();
		$settings = array_merge( $settings, get_option( 'tournamatch_options' ) );

		$options = $settings;
		foreach ( $options as $key => $value ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value           = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				$options[ $key ] = $value;
			}
		}

		$options = apply_filters( 'tournamatch_save_options', $options );
		update_option( 'tournamatch_options', $options );

		wp_safe_redirect( trn_route( 'admin.tournamatch.settings' ) );
		exit;
	}

	/**
	 * Displays the ladder settings page.
	 *
	 * @since 4.0.0
	 */
	public function settings() {

		$settings = trn_get_default_options();
		$settings = array_merge( $settings, get_option( 'tournamatch_options' ) );

		$form_values = array();
		$form_action = trn_route( 'admin.tournamatch.save-settings', array( '_wpnonce' => wp_create_nonce( 'tournamatch-save-settings' ) ) );
		foreach ( $settings as $key => $value ) {
			$option = trn_get_option( $key );
			if ( ! is_null( $option ) ) {
				$form_values[ $key ] = $option;
			} else {
				$form_values[ $key ] = $value;
			}
		}

		?>
		<style type="text/css">
			.trn-settings-form .form-field input, .trn-settings-form .form-field select, .trn-settings-form .form-field textarea {
				width: 25em;
			}

			@media screen and (max-width: 782px) {
				.trn-settings-form .form-field input, .trn-settings-form .form-field select, .trn-settings-form .form-field textarea {
					width: 100%;
				}
			}

			.tab-content {
				padding: 0 3px;
			}

			.tab-content .tab-pane {
				display: none;
			}

			.tab-content .tab-pane.active {
				display: inline-block;
			}
		</style>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Settings', 'tournamatch' ); ?></h1>
			<nav class="nav-tab-wrapper wp-clearfix">
				<a href="#ladder" class="nav-tab nav-tab-active"
						data-tab="ladder"><?php esc_html_e( 'Ladder', 'tournamatch' ); ?></a>
				<a href="#tournament" class="nav-tab"
						data-tab="tournament"><?php esc_html_e( 'Tournament', 'tournamatch' ); ?></a>
				<a href="#team" class="nav-tab" data-tab="team"><?php esc_html_e( 'Team', 'tournamatch' ); ?></a>
				<a href="#other" class="nav-tab" data-tab="other"><?php esc_html_e( 'Other', 'tournamatch' ); ?></a>
			</nav>
			<div class="tab-content wp-clearfix">
				<div class="tab-pane active" id="ladder">
					<form class="trn-settings-form" action="<?php echo esc_html( $form_action ); ?>" method="post">
						<h2 class="title"><?php esc_html_e( 'Ladder Settings', 'tournamatch' ); ?></h2>
						<table class="form-table" role="presentation">
							<tr class="form-field">
								<th scope="row">
									<label for="uses_draws"><?php esc_html_e( 'Draw Results', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="uses_draws" name="uses_draws">
										<option value="0" <?php echo ( '0' === $form_values['uses_draws'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
										<option value="1" <?php echo ( '1' === $form_values['uses_draws'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'Toggles the display of draws on report results, ladder standings, and win-loss records.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field">
								<th scope="row">
									<label for="open_play_enabled"><?php esc_html_e( 'Open Play', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="open_play_enabled" name="open_play_enabled">
										<option value="0" <?php echo ( '0' === $form_values['open_play_enabled'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
										<option value="1" <?php echo ( '1' === $form_values['open_play_enabled'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'Toggles whether competitors may report matches without first creating a challenge.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field">
								<th scope="row">
									<label for="can_leave_ladder"><?php esc_html_e( 'Can Leave Ladder', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="can_leave_ladder" name="can_leave_ladder">
										<option value="1" <?php echo ( '1' === $form_values['can_leave_ladder'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
										<option value="0" <?php echo ( '0' === $form_values['can_leave_ladder'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'Permits a player or team to leave a ladder.', 'tournamatch' ); ?></p>
									<p class="description"><?php esc_html_e( 'When a player or team (competitor) leaves a ladder, the standings data associated with this competitor is also erased. A competitor could use this ability to leave a ladder and rejoin to reset his or her rating or points.', 'tournamatch' ); ?></p>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>"
									class="button button-primary" id="trn-save-button"/>
							<input type="reset" value="<?php esc_html_e( 'Cancel', 'tournamatch' ); ?>"
									class="button button-default" id="cancelButton"/>
						</p>
					</form>
				</div>
				<div class="tab-pane" id="tournament">
					<form class="trn-settings-form" action="<?php echo esc_html( $form_action ); ?>" method="post">
						<h2 class="title"><?php esc_html_e( 'Tournament Settings', 'tournamatch' ); ?></h2>

						<table class="form-table" role="presentation">
							<tr class="form-field">
								<th scope="row">
									<label for="tournament_undecided_display"><?php esc_html_e( 'Tournament Undecided Display', 'tournamatch' ); ?></label>
								</th>
								<td>
									<input type="text" id="tournament_undecided_display"
											name="tournament_undecided_display"
											value="<?php echo esc_html( $form_values['tournament_undecided_display'] ); ?>">
									<p class="description"><?php esc_html_e( 'Enter the text displayed on brackets when a competitor is undecided.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field">
								<th scope="row">
									<label for="bracket_seeds_enabled"><?php esc_html_e( 'Bracket Seeds', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="bracket_seeds_enabled" name="bracket_seeds_enabled">
										<option value="1" <?php echo ( '1' === $form_values['bracket_seeds_enabled'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
										<option value="0" <?php echo ( '0' === $form_values['bracket_seeds_enabled'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'Display the numeric seed on brackets.', 'tournamatch' ); ?></p>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>"
									class="button button-primary" id="trn-save-button"/>
							<input type="reset" value="<?php esc_html_e( 'Cancel', 'tournamatch' ); ?>"
									class="button button-default" id="cancelButton"/>
						</p>
					</form>
				</div>
				<div class="tab-pane" id="team">
					<form class="trn-settings-form" action="<?php echo esc_html( $form_action ); ?>" method="post">
						<h2><?php esc_html_e( 'Team Settings', 'tournamatch' ); ?></h2>
						<div class="notice notice-info inline">
							<p><?php esc_html_e( 'Carefully consider the consequence of the below team options. If you only permit users to create one team and you host events with various sizes, such as a 2v2 and 4v4, enabling both features may have unintended results. A team signing up for the 2v2 would need four players if the same team was also signed up for the 4v4 (because enforce team minimum required four team members). In addition, enforce team minimum only filters the team when registering for an event. A team may drop players or add new players afterwards.', 'tournamatch' ); ?></p>
						</div>
						<table class="form-table" role="presentation">
							<tr class="form-field">
								<th scope="row">
									<label for="one_team_per_player"><?php esc_html_e( 'One Team per Player', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="one_team_per_player" name="one_team_per_player">
										<option value="1" <?php echo ( '1' === $form_values['one_team_per_player'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
										<option value="0" <?php echo ( '0' === $form_values['one_team_per_player'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'When enabled, players may be a member of only one team at a time.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field">
								<th scope="row">
									<label for="enforce_team_minimum"><?php esc_html_e( 'Enforce Team Minimum', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="enforce_team_minimum" name="enforce_team_minimum">
										<option value="1" <?php echo ( '1' === $form_values['enforce_team_minimum'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
										<option value="0" <?php echo ( '0' === $form_values['enforce_team_minimum'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'A team must meet the minimum team size to join an event.', 'tournamatch' ); ?></p>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>"
									class="button button-primary" id="trn-save-button"/>
							<input type="reset" value="<?php esc_html_e( 'Cancel', 'tournamatch' ); ?>"
									class="button button-default" id="cancelButton"/>
						</p>
					</form>
				</div>
				<div class="tab-pane" id="other">
					<form class="trn-settings-form" action="<?php echo esc_html( $form_action ); ?>" method="post">
						<h2><?php esc_html_e( 'Additional Settings', 'tournamatch' ); ?></h2>
						<table class="form-table" role="presentation">
							<tr class="form-field">
								<th scope="row">
									<label for="include_bootstrap_scripts"><?php esc_html_e( 'Include Bootstrap Scripts', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="include_bootstrap_scripts" name="include_bootstrap_scripts">
										<option value="1" <?php echo ( '1' === $form_values['include_bootstrap_scripts'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
										<option value="0" <?php echo ( '0' === $form_values['include_bootstrap_scripts'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'Recommended if your template doesn\'t use Bootstrap. This will include Bootstrap 4 sandboxed to the #trn CSS namespace.', 'tournamatch' ); ?></p>
								</td>
							</tr>
							<tr class="form-field">
								<th scope="row">
									<label for="display_user_email"><?php esc_html_e( 'Display User Email Addresses', 'tournamatch' ); ?></label>
								</th>
								<td>
									<select id="display_user_email" name="display_user_email">
										<option value="1" <?php echo ( '1' === $form_values['display_user_email'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Enabled', 'tournamatch' ); ?>
										</option>
										<option value="0" <?php echo ( '0' === $form_values['display_user_email'] ) ? 'selected' : ''; ?>>
											<?php esc_html_e( 'Disabled', 'tournamatch' ); ?>
										</option>
									</select>
									<p class="description"><?php esc_html_e( 'Enable or disable displaying users\' email addresses.', 'tournamatch' ); ?></p>
								</td>
							</tr>
						</table>
						<p class="submit">
							<input type="submit" value="<?php esc_html_e( 'Save', 'tournamatch' ); ?>"
									class="button button-primary" id="trn-save-button"/>
							<input type="reset" value="<?php esc_html_e( 'Cancel', 'tournamatch' ); ?>"
									class="button button-default" id="cancelButton"/>
						</p>
					</form>
				</div>
			</div>
		</div>
		<?php

		wp_register_script( 'trn-settings-page', plugins_url( '../dist/js/settings.js', __FILE__ ), array(), '3.24.0', true );
		wp_enqueue_script( 'trn-settings-page' );
	}

}

new Admin();
