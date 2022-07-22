<?php
/**
 * Tournamatch
 *
 * @package     tournamatch
 * @author      Tournamatch
 * @copyright   2022 MessyHair, LLC
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Tournamatch
 * Plugin URI: https://www.tournamatch.com/
 * Description: Ladder and tournament plugin for eSports and online gaming leagues.
 * Version: 4.1.0
 * Author: Tournamatch
 * Author URI: https://www.tournamatch.com
 * Text Domain: tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Currently plugin version.
 *
 * @since 3.0.0
 *
 * Uses Semantic Versioning
 * @see SemVer - https://semver.org
 *
 *  - MAJOR version when you make incompatible API changes.
 *  - MINOR version when you add-functionality in a backwards-compatible manner.
 *  - PATCH version when you make backwards-compatible bug fixes.
 */
define( 'TOURNAMATCH_VERSION', '4.1.0' );

/* setup path variables, database, and includes */
define( '__TRNPATH', plugin_dir_path( __FILE__ ) );

if ( file_exists( __TRNPATH . 'tournamatch.dev.php' ) ) {
	include __TRNPATH . 'tournamatch.dev.php';
} else {
	define( 'TOURNAMATCH_ENV', 'production' );
}

// Common includes.
require_once __TRNPATH . 'includes/data-access.php';
require_once __TRNPATH . 'includes/classes/class-tournamatch-email.php';
require_once __TRNPATH . 'includes/classes/class-tournamatch-online-users.php';

add_action(
	'init',
	function () {
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
			require_once ABSPATH . 'wp-admin/includes/screen.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			require_once ABSPATH . 'wp-admin/includes/template.php';
		}
		require_once __TRNPATH . 'includes/classes/class-tournamatch-tournament-list-table.php';
		require_once __TRNPATH . 'includes/classes/class-tournamatch-ladder-list-table.php';
		require_once __TRNPATH . 'includes/classes/class-tournamatch-match-list-table.php';
		require_once __TRNPATH . 'includes/classes/class-tournamatch-game-list-table.php';
	}
);

require_once __TRNPATH . 'includes/rest/class-controller.php';
require_once __TRNPATH . 'includes/rest/class-challenge.php';
require_once __TRNPATH . 'includes/rest/class-challenge-builder.php';
require_once __TRNPATH . 'includes/rest/class-game.php';
require_once __TRNPATH . 'includes/rest/class-game-image.php';
require_once __TRNPATH . 'includes/rest/class-ladder.php';
require_once __TRNPATH . 'includes/rest/class-ladder-competitor.php';
require_once __TRNPATH . 'includes/rest/class-matche.php';
require_once __TRNPATH . 'includes/rest/class-match-dispute.php';
require_once __TRNPATH . 'includes/rest/class-team.php';
require_once __TRNPATH . 'includes/rest/class-team-invitation.php';
require_once __TRNPATH . 'includes/rest/class-team-member.php';
require_once __TRNPATH . 'includes/rest/class-team-rank.php';
require_once __TRNPATH . 'includes/rest/class-team-request.php';
require_once __TRNPATH . 'includes/rest/class-tournament.php';
require_once __TRNPATH . 'includes/rest/class-tournament-competitor.php';
require_once __TRNPATH . 'includes/rest/class-tournament-registration.php';
require_once __TRNPATH . 'includes/rest/class-tournament-registration-list.php';
require_once __TRNPATH . 'includes/rest/class-player.php';

require_once __TRNPATH . 'includes/rules/class-business-rule.php';
require_once __TRNPATH . 'includes/rules/class-can-create-ladder-challenges.php';
require_once __TRNPATH . 'includes/rules/class-can-dispute-match.php';
require_once __TRNPATH . 'includes/rules/class-cannot-challenge-self.php';
require_once __TRNPATH . 'includes/rules/class-cannot-change-ladder-competition.php';
require_once __TRNPATH . 'includes/rules/class-cannot-change-tournament-field.php';
require_once __TRNPATH . 'includes/rules/class-cannot-move-default-rank.php';
require_once __TRNPATH . 'includes/rules/class-cannot-move-owner-rank.php';
require_once __TRNPATH . 'includes/rules/class-cannot-remove-default-rank.php';
require_once __TRNPATH . 'includes/rules/class-cannot-remove-owner-rank.php';
require_once __TRNPATH . 'includes/rules/class-direct-challenge-requires-enabled.php';
require_once __TRNPATH . 'includes/rules/class-ladder-challenges-enabled.php';
require_once __TRNPATH . 'includes/rules/class-must-participate-on-ladder.php';
require_once __TRNPATH . 'includes/rules/class-must-report-own-match.php';
require_once __TRNPATH . 'includes/rules/class-one-competitor-per-ladder.php';
require_once __TRNPATH . 'includes/rules/class-one-competitor-per-tournament.php';
require_once __TRNPATH . 'includes/rules/class-one-user-per-team.php';
require_once __TRNPATH . 'includes/rules/class-one-team-request-per-user.php';
require_once __TRNPATH . 'includes/rules/class-one-team-per-user.php';
require_once __TRNPATH . 'includes/rules/class-requires-minimum-members.php';
require_once __TRNPATH . 'includes/rules/class-team-rank-maxed.php';
require_once __TRNPATH . 'includes/rules/class-team-not-maxed.php';
require_once __TRNPATH . 'includes/rules/class-unique-team-name.php';
require_once __TRNPATH . 'includes/rules/class-team-name-required.php';
require_once __TRNPATH . 'includes/rules/class-must-promote-before-leaving.php';
require_once __TRNPATH . 'includes/rules/class-unique-player-name.php';
require_once __TRNPATH . 'includes/rules/class-password-must-match.php';

require_once __TRNPATH . 'includes/services/class-matche.php';

require_once __TRNPATH . 'includes/shortcodes/class-shortcodes.php';
require_once __TRNPATH . 'includes/shortcodes/class-challenge-shortcodes.php';
require_once __TRNPATH . 'includes/shortcodes/class-table-shortcodes.php';

require_once __TRNPATH . 'includes/widgets/class-ladder-top-competitor.php';
require_once __TRNPATH . 'includes/widgets/class-latest-matches.php';
require_once __TRNPATH . 'includes/widgets/class-newest-members.php';
require_once __TRNPATH . 'includes/widgets/class-newest-teams.php';
require_once __TRNPATH . 'includes/widgets/class-online-statistics.php';
require_once __TRNPATH . 'includes/widgets/class-upcoming-matches.php';

register_activation_hook( __FILE__, 'tournamatch_activate' );

if ( ! function_exists( 'tournamatch_activate' ) ) {
	/**
	 * Calls the code that runs during plugin activation.
	 *
	 * @since 4.0.0
	 */
	function tournamatch_activate() {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class-tournamatch-activator.php';
	}
}

require __TRNPATH . 'admin' . DIRECTORY_SEPARATOR . 'class-admin.php';
require __TRNPATH . 'admin' . DIRECTORY_SEPARATOR . 'class-game.php';
require __TRNPATH . 'admin' . DIRECTORY_SEPARATOR . 'class-tournament.php';
require __TRNPATH . 'admin' . DIRECTORY_SEPARATOR . 'class-ladder.php';
require __TRNPATH . 'admin' . DIRECTORY_SEPARATOR . 'class-matche.php';

if ( ! function_exists( 'trn_get_default_options' ) ) {
	/**
	 * Returns the default options for Tournamatch.
	 *
	 * @since 4.0.0
	 *
	 * @return array Array of options.
	 */
	function trn_get_default_options() {
		return apply_filters(
			'trn_default_options',
			array(
				'can_leave_ladder'             => '0',
				'allowed_extensions'           => '["jpg","jpeg","gif","tsvg"]',
				'display_user_email'           => '0',
				'include_bootstrap_scripts'    => '1',
				'uses_draws'                   => '1',
				'open_play_enabled'            => '1',
				'one_team_per_player'          => '0',
				'enforce_team_minimum'         => '0',
				'version'                      => TOURNAMATCH_VERSION,
				'tournament_undecided_display' => '????????',
				'bracket_seeds_enabled'        => '0',
			)
		);
	}
}

if ( ! function_exists( 'trn_get_option' ) ) {
	/**
	 * Retrieves a Tournamatch option value by option name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $option The option name.
	 *
	 * @return mixed The option value.
	 */
	function trn_get_option( $option ) {
		static $options;

		if ( is_null( $options ) ) {
			$options        = trn_get_default_options();
			$stored_options = get_option( 'tournamatch_options' );
			if ( is_array( $stored_options ) ) {
				$options = array_merge( $options, $stored_options );
			}
			$options['allowed_extensions'] = json_decode( trn_get_option( 'allowed_extensions' ), true );
			$options['admin_email']        = get_option( 'admin_email' );
		}

		return $options[ $option ];
	}
}

if ( ! function_exists( 'trn_load_text_domain' ) ) {
	/**
	 * Loads the text domain for language translation.
	 *
	 * @since 4.0.0
	 */
	function trn_load_text_domain() {
		$domain  = 'tournamatch';
		$mo_file = WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . get_locale() . '.mo';

		load_textdomain( $domain, $mo_file );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
add_action( 'init', 'trn_load_text_domain' );

if ( ! function_exists( 'trn_admin_message' ) ) {
	/**
	 * Displays a message page in the WordPress backend.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title The title of the page.
	 * @param string $message The message to display.
	 * @param bool   $go_back Indicates whether to display the 'Go Back' hyperlink.
	 */
	function trn_admin_message( $title, $message, $go_back = false ) {
		?>
		<div class="wrap">
			<h1 class="class-heading-inline">
				<?php echo esc_html( $title ); ?>
			</h1>
			<p>
				<?php echo wp_kses_post( $message ); ?>
				<?php
				if ( $go_back ) :
					echo ' - <a href="javascript:history.go(-1)">' . esc_html__( 'Go Back', 'tournamatch' ) . '</a>';
				endif;
				?>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'trn_get_current_user_team_rank' ) ) {
	/**
	 * Retrieves the team rank id for the current user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $team_id The id for the team.
	 *
	 * @return string
	 */
	function trn_get_current_user_team_rank( $team_id ) {
		global $wpdb;
		$team_id = intval( $team_id );
		if ( is_user_logged_in() ) {
			$row  = $wpdb->get_row( $wpdb->prepare( "SELECT `team_rank_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `user_id` = %d", $team_id, get_current_user_id() ), ARRAY_A );
			$rank = $row['team_rank_id'];
			if ( $rank ) {
				return $rank;
			} else {
				return '0';
			}
		}
	}
}

if ( ! function_exists( 'email_eliminated' ) ) {
	/**
	 * Emails a tournament competitor when they have been eliminated.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $tournament_id The id for the tournament.
	 * @param integer $spot_id The spot for the tournament.
	 */
	function email_eliminated( $tournament_id, $spot_id ) {
		global $wpdb;

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `spot` = %d AND `competition_id` = %d", $spot_id, $tournament_id ) );
		if ( intval( $match->one_competitor_id ) === 0 || intval( $match->two_competitor_id ) === 0 ) {
			return;
		}
		if ( 'players' === $match->one_competitor_type ) {
			$one = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $match->one_competitor_id ) );
			$two = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $match->two_competitor_id ) );
		} else {
			$one = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `p`.`user_id` WHERE `tm`.`team_id` = %d AND `tm`.`team_rank_id` = 1", $match->one_competitor_id ) );
			$two = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `p`.`user_id` WHERE `tm`.`team_id` = %d AND `tm`.`team_rank_id` = 1", $match->two_competitor_id ) );
		}

		$loser = ( 'lost' === $match->one_result ) ? $one : $two;

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $tournament_id ) );

		$data = [
			'tournament_link' => trn_route( 'tournaments.single', [ 'id' => $tournament_id ] ),
			'tournament_name' => $tournament->name,
		];

		/* Bye, Felicia! */
		do_action(
			'trn_notify_tournament_eliminated',
			[
				'email' => $loser->email,
				'name'  => $loser->display_name,
			],
			__( 'Eliminated from Tournament', 'tournamatch' ),
			$data
		);
	}
}

if ( ! function_exists( 'email_matched' ) ) {
	/**
	 * Emails the given competitor that their match opponent is set.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $tournament_id The id for the tournament.
	 * @param integer $spot_id The spot for the tournament.
	 */
	function email_matched( $tournament_id, $spot_id ) {
		global $wpdb;

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `competition_id` = %d AND `competition_type` = %s AND `spot` = %d", $tournament_id, 'tournaments', $spot_id ) );
		if ( intval( $match->one_competitor_id ) === 0 || intval( $match->two_competitor_id ) === 0 ) {
			return;
		}
		if ( 'players' === $match->one_competitor_type ) {
			$one = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $match->one_competitor_id ) );
			$two = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` WHERE `p`.`user_id` = %d", $match->two_competitor_id ) );
		} else {
			$one = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `p`.`user_id` WHERE `tm`.`team_id` = %d AND `tm`.`team_rank_id` = 1", $match->one_competitor_id ) );
			$two = $wpdb->get_row( $wpdb->prepare( "SELECT `u`.`user_email` AS `email`, `p`.`display_name` FROM `{$wpdb->prefix}trn_players_profiles` AS `p` LEFT JOIN {$wpdb->users} AS `u` ON `u`.`ID` = `p`.`user_id` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `tm`.`user_id` = `p`.`user_id` WHERE `tm`.`team_id` = %d AND `tm`.`team_rank_id` = 1", $match->two_competitor_id ) );
		}

		$data = [
			'brackets_link' => trn_route( 'tournaments.single.brackets', [ 'id' => $tournament_id ] ),
		];

		// Email first competitor.
		do_action(
			'trn_notify_tournament_matched',
			[
				'email' => $one->email,
				'name'  => $one->display_name,
			],
			__( 'Tournament Match Set', 'tournamatch' ),
			$data
		);

		// Email second competitor.
		do_action(
			'trn_notify_tournament_matched',
			[
				'email' => $two->email,
				'name'  => $two->display_name,
			],
			__( 'Tournament Match Set', 'tournamatch' ),
			$data
		);
	}
}

if ( ! function_exists( 'get_match_result_text' ) ) {
	/**
	 * Retrieves a summary of the match rest.
	 *
	 * @since 4.0.0
	 *
	 * @param object $match The match.
	 *
	 * @return string
	 */
	function get_match_result_text( $match ) {
		global $wpdb;

		if ( 'players' === $match->one_competitor_type ) {
			$one_name   = $wpdb->get_var( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $match->one_competitor_id ) );
			$two_name   = $wpdb->get_var( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $match->two_competitor_id ) );
			$route_name = 'players.single';
		} else {
			$one_name   = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $match->one_competitor_id ) );
			$two_name   = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $match->two_competitor_id ) );
			$route_name = 'teams.single';
		}

		// Format result to display.
		if ( is_null( $one_name ) ) {
			$one_name = get_option( 'tournamatch_options' )['tournament_undecided_display'];
		} else {
			$one_name = sprintf( '<a href="%1$s">%2$s</a>', esc_url( trn_route( $route_name, array( 'id' => $match->one_competitor_id ) ) ), $one_name );
		}
		if ( is_null( $two_name ) ) {
			$two_name = get_option( 'tournamatch_options' )['tournament_undecided_display'];
		} else {
			$two_name = sprintf( '<a href="%1$s">%2$s</a>', esc_url( trn_route( $route_name, array( 'id' => $match->two_competitor_id ) ) ), $two_name );
		}

		// Display IP address if admin.
		if ( current_user_can( 'manage_tournamatch' ) !== false ) {
			if ( isset( $match->one_ip ) ) {
				$one_name .= " ($match->one_ip)";
			}
			if ( isset( $match->two_ip ) ) {
				$two_name .= " ($match->two_ip)";
			}
		}

		// Write who defeated who, or the current match status.
		if ( 'scheduled' === $match->match_status ) {
			$match_result_text = esc_html__( 'Match result has not been reported.', 'tournamatch' );
		} elseif ( 'undetermined' === $match->match_status ) {
			$match_result_text = esc_html__( 'Match opponents have not been decided.', 'tournamatch' );
		} elseif ( 'reported' === $match->match_status ) {
			$match_result_text = esc_html__( 'Match result is pending.', 'tournamatch' );
		} elseif ( 'disputed' === $match->match_status ) {
			$match_result_text = esc_html__( 'Reported match result is disputed.', 'tournamatch' );
		} elseif ( ( 'won' === $match->one_result ) || ( 'lost' === $match->two_result ) ) {
			/* translators: Competitor name defeated an opponent name. */
			$match_result_text = sprintf( esc_html__( '%1$s defeated %2$s.', 'tournamatch' ), $one_name, $two_name );
		} elseif ( ( 'lost' === $match->one_result ) || ( 'won' === $match->two_result ) ) {
			/* translators: Competitor name defeated an opponent name. */
			$match_result_text = sprintf( esc_html__( '%1$s defeated %2$s.', 'tournamatch' ), $two_name, $one_name );
		} else {
			/* translators: Competitor name and opponent name tied. */
			$match_result_text = sprintf( esc_html__( '%1$s and %2$s tied.', 'tournamatch' ), $one_name, $two_name );
		}

		return $match_result_text;
	}
}

if ( ! function_exists( 'upload_image' ) ) {
	/**
	 * Uploads a file.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Where to upload the file.
	 * @param array  $allowed_extensions An array of allowed file extensions.
	 * @param array  $file Associative array of information about the file.
	 *
	 * @return bool|string
	 */
	function upload_image( $path, $allowed_extensions, $file ) {

		// Move uploaded file...
		$extensions = pathinfo( $file['name'], PATHINFO_EXTENSION );

		// Verify extensions are lower case.
		$allowed_extensions = array_map( 'strtolower', $allowed_extensions );

		// Verify extension is allowed.
		if ( ! in_array( strtolower( $extensions ), $allowed_extensions, true ) ) {
			return esc_html__( 'Extension is not allowed.', 'tournamatch' );
		}

		if ( file_exists( $path . $file['name'] ) ) {
			return esc_html__( 'File with given name already exists.', 'tournamatch' );
		}

		// Copy the file over.
		if ( ! move_uploaded_file( $file['tmp_name'], $path . $file['name'] ) ) {
			return esc_html__( 'Could not store file.', 'tournamatch' );
		}

		return true;
	}
}

if ( ! function_exists( 'update_career_wins' ) ) {
	/**
	 * Updates the career wins for the given competitor.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id for the competitor.
	 * @param string  $competitor_type The type for the competitor.
	 */
	function update_career_wins( $competitor_id, $competitor_type ) {
		global $wpdb;
		if ( 'players' === $competitor_type ) {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_players_profiles` SET `wins` = `wins` + 1 WHERE `user_id` = %d", $competitor_id ) );
		} else {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET `wins` = `wins` + 1 WHERE `team_id` = %d", $competitor_id ) );
		}
	}
}

if ( ! function_exists( 'update_career_losses' ) ) {
	/**
	 * Updates the career losses for the given competitor.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id for the competitor.
	 * @param string  $competitor_type The type for the competitor.
	 */
	function update_career_losses( $competitor_id, $competitor_type ) {
		global $wpdb;
		if ( 'players' === $competitor_type ) {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_players_profiles` SET `losses` = `losses` + 1 WHERE `user_id` = %d", $competitor_id ) );
		} else {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET `losses` = `losses` + 1 WHERE `team_id` = %d", $competitor_id ) );
		}
	}
}

if ( ! function_exists( 'update_career_draws' ) ) {
	/**
	 * Updates the career draws for the given competitor.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id for the competitor.
	 * @param string  $competitor_type The type for the competitor.
	 */
	function update_career_draws( $competitor_id, $competitor_type ) {
		global $wpdb;
		if ( 'players' === $competitor_type ) {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_players_profiles` SET `draws` = `draws` + 1 WHERE `user_id` = %d", $competitor_id ) );
		} else {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET `draws` = `draws` + 1 WHERE `team_id` = %d", $competitor_id ) );
		}
	}
}

if ( ! function_exists( 'trn_upload_dir' ) ) {
	/**
	 * Retrieves the directory where uploads are stored.
	 *
	 * @since 4.0.0
	 *
	 * @return bool|string
	 */
	function trn_upload_dir() {
		$upload_dir = wp_upload_dir();

		if ( false !== $upload_dir ) {
			return $upload_dir['basedir'] . '/tournamatch';
		}

		return false;
	}
}

if ( ! function_exists( 'trn_upload_url' ) ) {
	/**
	 * Retrieves the URL where uploads are stored.
	 *
	 * @since 4.0.0
	 *
	 * @return bool|string
	 */
	function trn_upload_url() {
		$upload_dir = wp_upload_dir();

		if ( false !== $upload_dir ) {
			return $upload_dir['baseurl'] . '/tournamatch';
		}

		return false;
	}
}

if ( ! function_exists( 'trn_array_keys_exists' ) ) {
	/**
	 * Checks if the given keys exists in the array.
	 *
	 * @param array $keys Keys to check.
	 * @param array $arr An array to check against.
	 *
	 * @return bool Returns TRUE if all keys are found, FALSE otherwise.
	 */
	function trn_array_keys_exists( array $keys, array $arr ) {
		return ! array_diff_key( array_flip( $keys ), $arr );
	}
}

if ( ! function_exists( 'update_ladder' ) ) {
	/**
	 * Updates the ladder standings for the given results.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $ladder_id The id for the ladder.
	 * @param array   $competitor_ids An array of ('won', 'lost', 'draw') results with the key corresponding to a competitor id.
	 */
	function update_ladder( $ladder_id, $competitor_ids ) {
		global $wpdb;

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE `ladder_id` = %d", intval( $ladder_id ) ) );
		$time   = time();

		array_walk(
			$competitor_ids,
			function ( $match_result, $competitor_id ) use ( $wpdb, $ladder, $time ) {
				switch ( $match_result ) {
					case 'won':
						$wpdb->query(
							$wpdb->prepare(
								"UPDATE `{$wpdb->prefix}trn_ladders_entries` SET `time` = %s, `wins` = `wins` + 1, `points` = `points` + %d WHERE `competitor_id` = %d AND `ladder_id` = %d",
								$time,
								$ladder->win_points,
								$competitor_id,
								$ladder->ladder_id
							)
						);
						break;

					case 'lost':
						$wpdb->query(
							$wpdb->prepare(
								"UPDATE `{$wpdb->prefix}trn_ladders_entries` SET `time` = %s, `losses` = `losses`  + 1, `points` = `points` + %d WHERE `competitor_id` = %d AND `ladder_id` = %d",
								$time,
								$ladder->loss_points,
								$competitor_id,
								$ladder->ladder_id
							)
						);
						break;

					case 'draw':
						$wpdb->query(
							$wpdb->prepare(
								"UPDATE `{$wpdb->prefix}trn_ladders_entries` SET `time` = %s, `draws` = `draws` + 1, `points` = `points` + %d WHERE `competitor_id` = %d AND `ladder_id` = %d",
								$time,
								$ladder->draw_points,
								$competitor_id,
								$ladder->ladder_id
							)
						);
						break;
				}
			}
		);
	}
}

if ( ! function_exists( 'update_tournament' ) ) {
	/**
	 * Updates a tournament's matches per the given result.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $tournament_id The id for the tournament.
	 * @param array   $result Array of result data for the tournament.
	 *
	 * @return string
	 */
	function update_tournament( $tournament_id, $result ) {
		global $wpdb;

		if ( ! trn_array_keys_exists( array( 'match_id', 'winner_id' ), $result ) ) {
			return esc_html__( 'Result parameter is missing arguments.', 'tournamatch' );
		}

		$match_id  = $result['match_id'];
		$winner_id = $result['winner_id'];

		// Need to know tournament and match details.
		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $tournament_id ) );
		$match      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ) );
		$matches    = $wpdb->get_results( $wpdb->prepare( "SELECT `m`.`spot`, `m`.* FROM `{$wpdb->prefix}trn_matches` AS `m` WHERE `competition_id` = %d AND `competition_type` = %s", $tournament_id, 'tournaments' ), OBJECT_K );

		$current_round_matches = $tournament->bracket_size / 2;
		$total_rounds          = log( $tournament->bracket_size, 2 );
		$match_count           = 0;
		$next_spots            = array();

		for ( $round = 0; $round < $total_rounds; $round++ ) {

			for ( $spot = 1; $spot <= $current_round_matches; $spot++ ) {
				$next_spots[ $spot + $match_count ] = (int) ( $match_count + $current_round_matches + ceil( $spot / 2 ) );
			}

			$match_count          += $current_round_matches;
			$current_round_matches = $current_round_matches / 2;
		}

		$next_spot = $next_spots[ $match->spot ];
		$side      = ( $match->spot % 2 ) ? 'one_competitor_id' : 'two_competitor_id';

		if ( 0 < $next_spot ) {
			if ( isset( $matches[ $next_spot ] ) ) {
				$update = array(
					'match_status' => 'scheduled',
					$side          => $winner_id,
				);

				$wpdb->update( $wpdb->prefix . 'trn_matches', $update, array( 'match_id' => $matches[ $next_spot ]->match_id ) );
			} else {
				$insert = array(
					'competition_id'      => $tournament_id,
					'competition_type'    => 'tournaments',
					'spot'                => $next_spot,
					'one_competitor_id'   => 0,
					'one_competitor_type' => $tournament->competitor_type,
					'two_competitor_id'   => 0,
					'two_competitor_type' => $tournament->competitor_type,
					'match_status'        => 'undetermined',
				);

				$insert[ $side ] = $winner_id;

				$wpdb->insert( $wpdb->prefix . 'trn_matches', $insert );
			}
		}

	}
}

if ( ! function_exists( 'initialize_tournament' ) ) {
	/**
	 * Initializes a tournament by creating the first round matches.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $tournament_id The id for the tournament.
	 *
	 * @return string
	 */
	function initialize_tournament( $tournament_id ) {
		global $wpdb;

		$tournament    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $tournament_id ) );
		$competitors   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE tournament_id = %d ORDER BY tournament_entry_id", $tournament_id ) );
		$required_size = intval( $tournament->bracket_size );

		if ( count( $competitors ) < $required_size ) {
			/* translators: A number indicating the required number of competitors. */
			return sprintf( esc_html__( 'Cannot seed tournament: Fewer ladder participants than bracket size. This tournament requires %d competitors to seed.', 'tournamatch' ), $required_size );
		}

		if ( ! in_array( $required_size, array( 4, 8, 16, 32, 64, 128, 256 ), true ) ) {
			return null;
		}

		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_matches` WHERE `competition_id` = %d AND `competition_type` = %s", $tournament_id, 'tournaments' ) );

		$first_round_matches = ( $required_size / 2 );

		shuffle( $competitors );

		for ( $i = 0; $i < $first_round_matches; $i ++ ) {
			$match = array(
				'competition_id'      => $tournament_id,
				'competition_type'    => 'tournaments',
				'spot'                => $i + 1,
				'one_competitor_id'   => $competitors[ $i * 2 ]->competitor_id,
				'one_competitor_type' => $competitors[ $i * 2 ]->competitor_type,
				'two_competitor_id'   => $competitors[ ( $i * 2 ) + 1 ]->competitor_id,
				'two_competitor_type' => $competitors[ ( $i * 2 ) + 1 ]->competitor_type,
				'match_status'        => 'scheduled',
			);

			$wpdb->insert( $wpdb->prefix . 'trn_matches', $match );
		}

		for ( $i = 0; $i < $required_size; $i ++ ) {
			$wpdb->update( $wpdb->prefix . 'trn_tournaments_entries', array( 'seed' => $i + 1 ), array( 'tournament_entry_id' => $competitors[ $i ]->tournament_entry_id ) );
		}

		$wpdb->update( $wpdb->prefix . 'trn_tournaments', array( 'status' => 'in_progress' ), array( 'tournament_id' => $tournament_id ) );

		return true;
	}
}

if ( ! function_exists( 'trn_display_avatar' ) ) {
	/**
	 * Displays the avatar for the competitor.
	 *
	 * @since 4.0.0
	 *
	 * @see trn_get_avatar()
	 *
	 * @param integer $id The id for the user or team.
	 * @param string  $player_or_team The type for the competitor.
	 * @param string  $configured_avatar The avatar set within Tournamatch.
	 * @param string  $class The CSS class to use in the img src or call to get_avatar.
	 */
	function trn_display_avatar( $id, $player_or_team, $configured_avatar = '', $class = 'trn-profile-picture' ) {
		$avatar = trn_get_avatar( $id, $player_or_team, $configured_avatar, $class );
		echo wp_kses_post( $avatar );
	}
}

if ( ! function_exists( 'trn_get_avatar' ) ) {
	/**
	 * Retrieves the avatar for the competitor.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $id The id for the user or team.
	 * @param string  $player_or_team The type for the competitor.
	 * @param string  $configured_avatar The avatar set within Tournamatch.
	 * @param string  $class The CSS class to use in the img src or call to get_avatar.
	 *
	 * @see get_avatar()
	 *
	 * @return null|string
	 */
	function trn_get_avatar( $id, $player_or_team, $configured_avatar = '', $class = 'trn-profile-picture' ) {
		global $wpdb;

		if ( 0 === strlen( $configured_avatar ) ) {
			if ( in_array( $player_or_team, array( 'player', 'players' ), true ) ) {
				$configured_avatar = $wpdb->get_var( $wpdb->prepare( "SELECT `avatar` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $id ) );
			} else {
				$configured_avatar = $wpdb->get_var( $wpdb->prepare( "SELECT `avatar` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $id ) );
			}
		}
		if ( 'blank.gif' === $configured_avatar ) {
			$configured_avatar = '';
		}
		if ( 0 !== strlen( $configured_avatar ) ) {
			$avatar_directory = trn_upload_url() . '/images/avatars/';

			/**
			 * No longer store the path of the user-specified avatar in the database. This is here
			 * for backwards compatibility. If the give avatar string path does not start with the avatar directory,
			 * we prefix it here.
			 *
			 * @since 3.16.0
			 */
			if ( substr( $configured_avatar, 0, strlen( $avatar_directory ) ) !== $avatar_directory ) {
				$configured_avatar = $avatar_directory . $configured_avatar;
			}

			return '<img src="' . $configured_avatar . '" class="' . $class . '" />';
		} else {
			if ( in_array( $player_or_team, array( 'player', 'players' ), true ) ) {
				return get_avatar( $id, 96, '', '', array( 'class' => 'trn-profile-picture' ) );
			} elseif ( in_array( $player_or_team, array( 'team', 'teams' ), true ) ) {
				return get_avatar( null, 96, 'mm', '', array( 'class' => 'trn-profile-picture' ) );
			} else {
				return get_avatar( null, 96, 'mm', '', array( 'class' => 'trn-profile-picture' ) );
			}
		}
	}
}

if ( ! function_exists( 'trn_get_display_name' ) ) {
	/**
	 * Retrieves the display name for the competitor.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id for the competitor.
	 * @param string  $competitor_type The type for the competitor.
	 *
	 * @return null|string
	 */
	function trn_get_display_name( $competitor_id, $competitor_type ) {
		global $wpdb;

		if ( ! in_array( $competitor_type, array( 'player', 'players', 'team', 'teams' ), true ) ) {
			return '';
		}

		if ( in_array( $competitor_type, array( 'player', 'players' ), true ) ) {
			$display_name = $wpdb->get_var( $wpdb->prepare( "SELECT `display_name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $competitor_id ) );
		} else {
			$display_name = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $competitor_id ) );
		}

		return is_null( $display_name ) ? '' : $display_name;
	}
}


if ( ! function_exists( 'trn_deleted_user' ) ) {
	/**
	 * Handles the deleted user event and cleans up any related data.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id for the user.
	 */
	function trn_deleted_user( $user_id ) {
		global $wpdb;

		// remove challenges.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_challenges` WHERE `ladder_id` IN (SELECT `ladder_id` FROM `{$wpdb->prefix}trn_ladders` WHERE `competitor_type` = %d) AND (`challenger_id` = %d OR `challengee_id` = %d)", 1, $user_id, $user_id ) );

		// fix ladder position (move everyone up one).
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT `ladder_id` AS `ladder_id`, `position` AS `position` FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `competitor_type` = %s AND `competitor_id` = %d AND `position` > 0", 'players', $user_id ) );
		foreach ( $entries as $entry ) {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_ladders_entries` SET `position` = `position` - 1 WHERE `ladder_id` = %d AND `position` >= %d", $entry->ladder_id, $entry->position ) );
		}

		// remove ladder entries for single player.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `competitor_type` = %s AND `competitor_id` = %d", 'players', $user_id ) );

		// remove ladder matches.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_matches` WHERE (`one_competitor_id` = %d OR `two_competitor_id` = %d) AND `one_competitor_type` = %s AND `competition_type` = %s", $user_id, $user_id, 'players', 'ladders' ) );

		// remove player profiles.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $user_id ) );

		// remove player team requests.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members_requests` WHERE `user_id` = %d", $user_id ) );

		// remove tournament entries.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `competitor_type` = %s AND `competitor_id` = %d", 'players', $user_id ) );

		// set tournament entries to deleted/ghost user/anonymous.
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `one_competitor_id` = -2 WHERE `one_competitor_id` = %d AND `one_competitor_type` = %s", $user_id, 'players' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `two_competitor_id` = -2 WHERE `two_competitor_id` = %d AND `two_competitor_type` = %s", $user_id, 'players' ) );

		// where owner and team members > 1, set next rank to owner.
		$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `t`.`team_id`, `tm`.`team_member_id` FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` WHERE `t`.`members` > 1 AND `tm`.`user_id` = %d AND `tm`.`team_rank_id` = 1", $user_id ) );
		foreach ( $teams as $team ) {
			$new_owner = $wpdb->get_row( $wpdb->prepare( "SELECT `team_member_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `team_rank_id` = 2 ORDER BY `joined_date` ASC LIMIT 1", $team->team_id ) );
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_members` SET `team_rank_id` = %d WHERE `team_member_id` = %d", 1, $new_owner->team_member_id ) );
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams_members` SET `team_rank_id` = %d WHERE `team_member_id` = %d", 2, $team->team_member_id ) );
		}

		// remove team memberships where not owner.
		$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `tm`.`team_id`, `tm`.`team_member_id` FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` WHERE `t`.`members` > 1 AND `tm`.`user_id` = %d AND `tm`.`team_rank_id` != 1", $user_id ) );
		foreach ( $teams as $team ) {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET `members` = `members` - 1 WHERE `team_id` = %d", $team->team_id ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_member_id` = %d", $team->team_member_id ) );
		}

		// where owner and team members === 1.
		$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `tm`.`team_id` FROM `{$wpdb->prefix}trn_teams` AS `t` LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` WHERE `t`.`members` = 1 AND `tm`.`user_id` = %d", $user_id ) );
		foreach ( $teams as $team ) {
			trn_deleted_team( $team->team_id );
		}
	}
}

add_action( 'deleted_user', 'trn_deleted_user' );
if ( ! function_exists( 'trn_deleted_team' ) ) {
	/**
	 * Handles the deleted team event and cleans up any related data.
	 *
	 * @param integer $team_id The id for the team.
	 */
	function trn_deleted_team( $team_id ) {
		global $wpdb;

		// remove challenges.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_challenges` WHERE `ladder_id` IN (SELECT `ladder_id` FROM `{$wpdb->prefix}trn_ladders` WHERE `competitor_type` = 2) AND (`challenger_id` = %d OR `challengee_id` = %d)", $team_id, $team_id ) );

		// fix ladder position (move everyone up one).
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT `ladder_id` AS `ladder_id`, `position` AS `position` FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `competitor_type` = 'teams' AND `competitor_id` = %d AND `position` > 0", $team_id ) );
		foreach ( $entries as $entry ) {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_ladders_entries` SET `position` = `position` - 1 WHERE `ladder_id` = %d AND `position` >= %d", $entry->ladder_id, $entry->position ) );
		}

		// remove ladder entries for teams.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `competitor_type` = %s AND `competitor_id` = %d", 'teams', $team_id ) );

		// remove ladder matches.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_matches` WHERE (`one_competitor_id` = %d OR `two_competitor_id` = %d) AND `one_competitor_type` = 'teams' AND `competition_type` = 'ladders'", $team_id, $team_id ) );

		// remove team requests.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members_requests` WHERE `team_id` = %d", $team_id ) );

		// remove team invitations.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE `team_id` = %d", $team_id ) );

		// remove tournament entries.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `competitor_type` = %s AND `competitor_id` = %d", 'teams', $team_id ) );

		// set tournament matches to deleted/ghost user/anonymous.
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `one_competitor_id` = -2 WHERE `one_competitor_id` = %d AND `one_competitor_type` = 'teams'", $team_id ) );
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `two_competitor_id` = -2 WHERE `two_competitor_id` = %d AND `two_competitor_type` = 'teams'", $team_id ) );

		// delete team memberships.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d", $team_id ) );

		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT `post_id` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $team_id ) );
		wp_delete_post( $post_id, true );

		// delete team.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $team_id ) );

	}
}

if ( ! function_exists( 'trn_register_scripts' ) ) {
	/**
	 * Registers front end javascript.
	 *
	 * @since 4.0.0
	 */
	function trn_register_scripts() {
		wp_register_script( 'tournamatch', plugins_url( '/dist/js/tournamatch.js', __FILE__ ), array(), '3.25.0', true );
		wp_register_script( 'trn-confirm-action', plugins_url( '/dist/js/confirm-action.js', __FILE__ ), array(), '3.25.0', true );
		wp_localize_script(
			'trn-confirm-action',
			'trn_confirm_action_options',
			array(
				'language' => array(
					'yes' => esc_html__( 'Yes', 'tournamatch' ),
					'no'  => esc_html__( 'No', 'tournamatch' ),
				),
			)
		);

		$delete_options = array(
			'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'language'   => array(
				'failure' => __( 'Error', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn-delete-match', plugins_url( '/dist/js/delete-match.js', __FILE__ ), array( 'tournamatch' ), '3.11.0', true );
		wp_localize_script( 'trn-delete-match', 'trn_delete_match_options', $delete_options );

		wp_register_style( 'trn_font_awesome_css', plugins_url( '/dist/css/fontawesome.5.14.0.css', __FILE__ ), array(), '5.14.0' );
		wp_enqueue_style( 'trn_font_awesome_css' );

		wp_register_style( 'trn_components_css', plugins_url( '/dist/css/components.css', __FILE__ ), array(), '3.0.0' );
		wp_enqueue_style( 'trn_components_css' );

		wp_register_style( 'datatables', plugins_url( '/dist/css/trn.datatable.bootstrap4.css', __FILE__ ), array(), '1.10.19' );
		wp_enqueue_style( 'datatables' );

		wp_register_script(
			'trn_bootstrap_js',
			plugins_url( '/dist/vendor/bootstrap.js', __FILE__ ),
			array(
				'jquery',
			),
			'4.3',
			true
		);
		wp_enqueue_script( 'trn_bootstrap_js' );

		wp_register_script( 'datatables', plugins_url( '/dist/vendor/jquery.dataTables.min.js', __FILE__ ), array( 'jquery' ), '1.10.20', true );
		wp_register_script(
			'datatables-bootstrap',
			plugins_url( '/dist/vendor/dataTables.bootstrap4.js', __FILE__ ),
			array(
				'jquery',
				'datatables',
			),
			'1.10.20',
			true
		);
		wp_enqueue_script( 'datatables-bootstrap' );

		wp_enqueue_script( 'trn-confirm-action' );
	}

	add_action( 'wp_enqueue_scripts', 'trn_register_scripts' );
}

if ( ! function_exists( 'trn_register_admin_scripts' ) ) {
	/**
	 * Registers backend javascript.
	 *
	 * @since 4.0.0
	 */
	function trn_register_admin_scripts() {
		wp_register_script( 'tournamatch', plugins_url( '/dist/js/tournamatch.js', __FILE__ ), array(), '3.25.0', true );
	}

	add_action( 'admin_enqueue_scripts', 'trn_register_admin_scripts' );
}

if ( ! function_exists( 'trn_get_folder_contents' ) ) {
	/**
	 * Retrieves an array of file paths at the given location.
	 *
	 * @since 4.0.0
	 *
	 * @param string $location The directory to search.
	 *
	 * @return array
	 */
	function trn_get_folder_contents( $location ) {
		$contents = array();
		$handle   = opendir( $location );

		if ( $handle ) {
			$index = 0;
			$file  = readdir( $handle );
			while ( $file ) {
				if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
					$contents[ $index ] = $file;
					$index ++;
				}
				$file = readdir( $handle );
			}
		}

		return $contents;
	}
}

if ( ! function_exists( 'trn_get_files_of_type' ) ) {
	/**
	 * Retrieves a list of images filtered by type.
	 *
	 * @since 4.0.0
	 *
	 * @param string $location The directory to search.
	 * @param array  $type An array of file name extensions.
	 *
	 * @return array
	 */
	function trn_get_files_of_type( $location, $type = array( 'gif', 'jpg', 'png' ) ) {
		$images          = array();
		$folder_contents = trn_get_folder_contents( $location );
		if ( count( $folder_contents ) > 0 ) {
			foreach ( $folder_contents as $file ) {
				$temp_file_array = explode( '.', $file );
				$extension       = $temp_file_array[ count( $temp_file_array ) - 1 ];
				if ( in_array( strtolower( $extension ), $type, true ) ) {
					array_push( $images, $file );
				}
			}
		}

		return $images;
	}
}

if ( ! function_exists( 'trn_can_accept_challenge' ) ) {
	/**
	 * Evaluates whether a competitor has permission to accept a challenge.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id for the competitor.
	 * @param integer $challenge_id The id for the challenge.
	 *
	 * @return bool
	 */
	function trn_can_accept_challenge( $competitor_id, $challenge_id ) {
		global $wpdb;

		$challenge = $wpdb->get_row( $wpdb->prepare( "SELECT `c`.*, `l`.`competitor_type` FROM `{$wpdb->prefix}trn_challenges` AS `c` LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `c`.`ladder_id` = `l`.`ladder_id` WHERE `challenge_id` = %d", $challenge_id ) );

		if ( 'pending' === $challenge->accepted_state ) {
			if ( 'players' === $challenge->competitor_type ) {
				if ( 'blind' === $challenge->challenge_type ) {
					return ( absint( $challenge->challenger_id ) !== $competitor_id );
				} else {
					return ( absint( $challenge->challengee_id ) === $competitor_id );
				}
			} else {
				$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d", $competitor_id ) );
				$teams = array_map(
					function ( $team ) {
						return $team->team_id;
					},
					$teams
				);

				if ( 'blind' === $challenge->challenge_type ) {
					return ! in_array( $challenge->challenger_id, $teams, true );
				} else {
					return in_array( $challenge->challengee_id, $teams, true );
				}
			}
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'trn_can_delete_challenge' ) ) {
	/**
	 * Evaluates whether a competitor has permission to delete a challenge.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id for the competitor.
	 * @param integer $challenge_id The id for the challenge.
	 *
	 * @return bool
	 */
	function trn_can_delete_challenge( $competitor_id, $challenge_id ) {
		global $wpdb;

		$is_admin = current_user_can( 'manage_tournamatch' );
		if ( ! $is_admin ) {
			$challenge = $wpdb->get_row( $wpdb->prepare( "SELECT `c`.*, `l`.`competitor_type` FROM `{$wpdb->prefix}trn_challenges` AS `c` LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `c`.`ladder_id` = `l`.`ladder_id` WHERE `challenge_id` = %d", $challenge_id ) );

			if ( 'players' === $challenge->competitor_type ) {
				return ( ( absint( $challenge->challenger_id ) === $competitor_id ) && ( 'pending' === $challenge->accepted_state ) );
			} else {
				$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d", $competitor_id ) );
				$teams = array_map(
					function ( $team ) {
						return $team->team_id;
					},
					$teams
				);

				return ( in_array( $challenge->challenger_id, $teams, true ) && ( 'pending' === $challenge->accepted_state ) );
			}
		}

		return $is_admin;
	}
}

if ( ! function_exists( 'trn_can_decline_challenge' ) ) {
	/**
	 * Evaluates whether a competitor has permission to decline a challenge.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id for the competitor.
	 * @param integer $challenge_id The id for the challenge.
	 *
	 * @return bool
	 */
	function trn_can_decline_challenge( $competitor_id, $challenge_id ) {
		global $wpdb;

		$challenge = $wpdb->get_row( $wpdb->prepare( "SELECT `c`.*, `l`.`competitor_type` FROM `{$wpdb->prefix}trn_challenges` AS `c` LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `c`.`ladder_id` = `l`.`ladder_id` WHERE `challenge_id` = %d", $challenge_id ) );

		if ( 'pending' === $challenge->accepted_state ) {
			if ( 'players' === $challenge->competitor_type ) {
				return ( absint( $challenge->challengee_id ) === $competitor_id );
			} else {
				$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d", $competitor_id ) );
				$teams = array_map(
					function ( $team ) {
						return $team->team_id;
					},
					$teams
				);

				return in_array( $challenge->challengee_id, $teams, true );
			}
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'get_challenge_email_data' ) ) {
	/**
	 * Retrieves data about a challenge for the purpose of emailing participants.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $challenge_id The id for the challenge.
	 *
	 * @return bool|object
	 */
	function get_challenge_email_data( $challenge_id ) {
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  c.challenge_id AS challenge_id, 
  l.ladder_id AS ladder_id, 
  l.name AS ladder_name, 
  l.competitor_type, 
  c.challenger_id AS challenger_id, 
  c.challengee_id AS challengee_id, 
  c.match_time AS challenge_date
FROM 
  `{$wpdb->prefix}trn_challenges` AS `c` LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `c`.`ladder_id`
WHERE `c`.`challenge_id` = %d",
				$challenge_id
			),
			ARRAY_A
		);

		if ( $result ) {
			if ( 'players' === $result['competitor_type'] ) {
				$challenger_result = $wpdb->get_row(
					$wpdb->prepare(
						"
SELECT p.display_name AS challenger, u.user_email AS challenger_email
FROM `{$wpdb->prefix}trn_players_profiles` AS p LEFT JOIN `{$wpdb->users}` AS u ON p.user_id = u.ID
WHERE p.user_id = %d",
						$result['challenger_id']
					),
					ARRAY_A
				);

				$challengee_result = $wpdb->get_row(
					$wpdb->prepare(
						"
SELECT p.display_name AS challengee, u.user_email AS challengee_email
FROM `{$wpdb->prefix}trn_players_profiles` AS p LEFT JOIN `{$wpdb->users}` AS u ON p.user_id = u.ID
WHERE p.user_id = %d",
						$result['challengee_id']
					),
					ARRAY_A
				);
			} else {
				$challenger_result = $wpdb->get_row(
					$wpdb->prepare(
						"
SELECT t.name AS challenger, u.user_email AS challenger_email
FROM `{$wpdb->prefix}trn_teams` AS t
  LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS tm ON t.team_id = tm.team_id AND `team_rank_id` = 1
    LEFT JOIN {$wpdb->users} AS u ON u.ID = tm.user_id
WHERE t.team_id = %d
",
						$result['challenger_id']
					),
					ARRAY_A
				);

				$challengee_result = $wpdb->get_row(
					$wpdb->prepare(
						"
SELECT t.name AS challengee, u.user_email AS challengee_email
FROM `{$wpdb->prefix}trn_teams` AS t
  LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS tm ON t.team_id = tm.team_id AND `team_rank_id` = 1
    LEFT JOIN {$wpdb->users} AS u ON u.ID = tm.user_id
WHERE t.team_id = %d
",
						$result['challengee_id']
					),
					ARRAY_A
				);
			}

			if ( $challengee_result && $challenger_result ) {
				return (object) array_merge( $result, $challengee_result, $challenger_result );
			}
		}

		return false;
	}
}

if ( ! function_exists( 'scheduled_matches_table' ) ) {
	//phpcs:ignore Squiz.Commenting.FunctionComment.Missing
	function scheduled_matches_table( $scheduled_matches ) {
		?>
		<table class="trn-table trn-table-striped trn-scheduled-matches-table" id="scheduled-matches-table">
			<tr>
				<th class="trn-scheduled-matches-table-event"><?php esc_html_e( 'Event', 'tournamatch' ); ?></th>
				<th class="trn-scheduled-matches-table-name"><?php esc_html_e( 'Name', 'tournamatch' ); ?></th>
				<th class="trn-scheduled-matches-table-competitors"><?php esc_html_e( 'Competitors', 'tournamatch' ); ?></th>
				<th class="trn-scheduled-matches-table-date"><?php esc_html_e( 'Scheduled', 'tournamatch' ); ?></th>
				<th class="trn-scheduled-matches-table-action"></th>
			</tr>
			<?php
			// display list of scheduled matches needing to be reported.
			foreach ( $scheduled_matches as $scheduled_match ) :
				?>
				<tr data-competition-type="<?php echo esc_html( $scheduled_match->competition_type ); ?>"
						data-competition-id="<?php echo intval( $scheduled_match->competition_id ); ?>"
						data-match-id="<?php echo intval( $scheduled_match->match_id ); ?>">
					<td class="trn-scheduled-matches-table-event">
						<?php echo esc_html( ucwords( $scheduled_match->competition_type ) ); ?>
					</td>
					<td class="trn-scheduled-matches-table-name">
						<a href="<?php trn_esc_route_e( $scheduled_match->competition_slug, array( 'id' => $scheduled_match->competition_id ) ); ?>"><?php echo esc_html( $scheduled_match->name ); ?></a>
					</td>
					<td class="trn-scheduled-matches-table-competitors">
						<a href="<?php trn_esc_route_e( $scheduled_match->route_name, array( $scheduled_match->route_var => $scheduled_match->one_competitor_id ) ); ?>"><?php echo esc_html( $scheduled_match->one_name ); ?></a>
						vs
						<a href="<?php trn_esc_route_e( $scheduled_match->route_name, array( $scheduled_match->route_var => $scheduled_match->two_competitor_id ) ); ?>"><?php echo esc_html( $scheduled_match->two_name ); ?></a>
					</td>
					<td class="trn-scheduled-matches-table-date">
						<?php
						if ( '0000-00-00 00:00:00' !== $scheduled_match->match_date ) {
							echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $scheduled_match->match_date ) ) ) );
						} else {
							echo '&nbsp;';
						}
						?>
					</td>
					<td class="trn-scheduled-matches-table-action">
						<a class="trn-button trn-button-sm"
								href="<?php trn_esc_route_e( 'matches.single.report', array( 'id' => $scheduled_match->match_id ) ); ?>"><?php esc_html_e( 'Report', 'tournamatch' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}
}

if ( ! function_exists( 'trn_get_tournament_register_conditions' ) ) {
	/**
	 * Retrieves tournament registration conditions for register and unregister.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $tournament_id The id for the tournament.
	 * @param integer $competitor_id The id for the competitor.
	 *
	 * @return array
	 */
	function trn_get_tournament_register_conditions( $tournament_id, $competitor_id ) {
		global $wpdb;

		$tournament = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_tournaments` WHERE `tournament_id` = %d", $tournament_id ) );

		// verify player or team is registered for tournament.
		if ( 'players' === $tournament->competitor_type ) {
			$registration = $wpdb->get_row( $wpdb->prepare( "SELECT `tournament_entry_id` FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_id` = %d AND `competitor_id` = %d", $tournament_id, $competitor_id ) );
		} else {
			$registration = $wpdb->get_row( $wpdb->prepare( "SELECT `tournament_entry_id` FROM `{$wpdb->prefix}trn_tournaments_entries` WHERE `tournament_id` = %d AND `competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d )", $tournament_id, $competitor_id ) );
		}

		$is_open    = in_array(
			$tournament->status,
			[
				'open',
			],
			true
		);
		$registered = ! is_null( $registration );

		$data = array(
			'id'             => $registered ? (int) $registration->tournament_entry_id : null,
			'can_register'   => $is_open && ! $registered && ( 0 !== $competitor_id ),
			'can_unregister' => $is_open && $registered,
		);

		return $data;
	}
}

if ( ! function_exists( 'rest_is_field_included' ) ) {
	/**
	 * Given an array of fields to include in a response, some of which may be nested.fields, determine whether the
	 * provided field should be included in the response body.
	 *
	 * This is included for compatibility with WordPress < 5.3.0. This method was introduced in WordPress 5.3.0.
	 *
	 * @since 4.0.0
	 *
	 * @param string $field A field to test for inclusion in the response body.
	 * @param array  $fields An array of string fields supported by the endpoint.
	 *
	 * @return bool Whether to include the field or not.
	 */
	function rest_is_field_included( $field, $fields ) {
		if ( in_array( $field, $fields, true ) ) {
			return true;
		}

		foreach ( $fields as $accepted_field ) {
			// Check to see if $field is the parent of any item in $fields.
			// A field "parent" should be accepted if "parent.child" is accepted.
			if ( strpos( $accepted_field, "$field." ) === 0 ) {
				return true;
			}
			// Conversely, if "parent" is accepted, all "parent.child" fields should also be accepted.
			if ( strpos( $field, "$accepted_field." ) === 0 ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'trn_get_flag_options' ) ) {
	/**
	 * Retrieves an array of select drop down flag options.
	 *
	 * @since 4.2.0
	 *
	 * @return array Select drop down options.
	 */
	function trn_get_flag_options() {
		$flag_options = array();

		foreach ( trn_get_flag_list() as $flag => $flag_title ) {
			$flag_options[] = array(
				'value'   => $flag,
				'content' => $flag_title,
			);
		}

		return $flag_options;
	}
}

if ( ! function_exists( 'trn_get_flag_list' ) ) {
	/**
	 * Retrieves the list of supported flags.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed Array of flag file paths.
	 */
	function trn_get_flag_list() {
		$flags  = array();
		$handle = opendir( __TRNPATH . '/dist/images/flags/' );
		if ( $handle ) {
			$file = readdir( $handle );
			while ( $file ) {
				if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
					$flags[ $file ] = $file;
				}
				$file = readdir( $handle );
			}
			asort( $flags );
			array_walk(
				$flags,
				function ( &$file_name ) {
					$file_name = preg_replace( '/.gif/', '', $file_name );
					$file_name = ucwords( preg_replace( '/_/', ' ', $file_name ) );

					if ( 'Usa' === $file_name ) {
						$file_name = 'USA';
					}
				}
			);
			closedir( $handle );
		}

		return apply_filters( 'trn_filter_flag_list', $flags );
	}
}

if ( ! function_exists( 'trn_can_confirm_match' ) ) {
	/**
	 * Evaluates whether a competitor has permission to confirm a match.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id for the user to check.
	 * @param integer $match_id The id for the match.
	 *
	 * @return bool Returns true if the user has permission, false otherwise.
	 */
	function trn_can_confirm_match( $user_id, $match_id ) {
		global $wpdb;

		$can_confirm = user_can( $user_id, 'manage_tournamatch' );

		if ( ! $can_confirm ) {
			$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ) );
			if ( 'reported' === $match->match_status ) {

				// The opposite side that reported may confirm. The side that reported has a result set.
				$can_confirm_id = ( 0 < strlen( $match->one_result ) ) ? $match->two_competitor_id : $match->one_competitor_id;

				if ( 'players' === $match->one_competitor_type ) {
					return ( intval( $can_confirm_id ) === intval( $user_id ) );
				} else {
					$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d AND `team_id` = %d", $user_id, $can_confirm_id ) );

					return ( 0 < $count );
				}
			}
		}

		return $can_confirm;
	}
}

if ( ! function_exists( 'trn_can_report_match' ) ) {
	/**
	 * Evaluates whether a competitor has permission to report a match.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id for the user to check.
	 * @param integer $match_id The id for the match.
	 *
	 * @return bool Returns true if the user has permission, false otherwise.
	 */
	function trn_can_report_match( $user_id, $match_id ) {
		global $wpdb;

		$can_report = user_can( $user_id, 'manage_tournamatch' );

		if ( ! $can_report ) {
			$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $match_id ) );
			if ( 'scheduled' === $match->match_status ) {

				if ( 'players' === $match->one_competitor_type ) {
					return in_array(
						(string) $user_id,
						array(
							$match->one_competitor_id,
							$match->two_competitor_id,
						),
						true
					);
				} else {
					$teams = $wpdb->get_results( $wpdb->prepare( "SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d", $user_id ) );
					$teams = array_column( $teams, 'team_id' );

					return in_array( $match->one_competitor_id, $teams, true ) || in_array( $match->two_competitor_id, $teams, true );
				}
			}
		}

		return $can_report;
	}
}

if ( ! function_exists( 'starts_with' ) ) {
	/** Evaluates whether the haystack is found in any of the given needles.
	 *
	 * @param string $haystack String to search for.
	 * @param array  $needles Array of strings to search.
	 *
	 * @return bool True if found in any needle, false otherwise.
	 */
	function starts_with( $haystack, $needles ) {
		foreach ( (array) $needles as $needle ) {
			if ( ( '' !== (string) $needle ) && strncmp( $haystack, $needle, strlen( $needle ) ) === 0 ) {
				return true;
			}
		}

		return false;
	}
}

add_action(
	'admin_init',
	function () {
		// For plugin pages, the admin-header is loaded before executing plugin code. This causes any call to wp_redirect to
		// fail because headers have already been sent. Another option is to include a $_GET['noheader'] equal to true. See
		// wp-admin/admin.php line 238 for more info.

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['page'] ) && in_array(
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$_REQUEST['page'],
			array(
				'tournaments',
				'tournament-matches',
				'ladders',
				'ladder-matches',
			),
			true
		)
		) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
				//phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect(
					remove_query_arg(
						array(
							'_wp_http_referer',
							'_wpnonce',
						),
						//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
						wp_unslash( $_SERVER['REQUEST_URI'] )
					)
				);
				exit;
			}
		}
	}
);

if ( ! function_exists( 'trn_esc_route_e' ) ) {
	/**
	 * Displays an escaped result from trn_route(...).
	 *
	 * @see trn_route()
	 *
	 * @since 4.0.0
	 *
	 * @param string     $name The name of the route to retrieve.
	 * @param array|null $parameters Array of parameters necessary or optional for the route URL.
	 */
	function trn_esc_route_e( $name, $parameters = null ) {
		echo esc_url( trn_route( $name, $parameters ) );
	}
}

if ( ! function_exists( 'trn_route' ) ) {
	/**
	 * Retrieves the link for the corresponding route name. Any necessary named place holders must be included in the
	 * parameters array. Any unused item in parameters is considered optional and will be tacked on to the end of the
	 * URL as additional query parameters with the name of the variable equal to the key in the array.
	 *
	 * @since 4.0.0
	 *
	 * @param string     $name The name of the route to retrieve.
	 * @param array|null $parameters Array of parameters necessary or optional for the route URL.
	 *
	 * @throws InvalidArgumentException If the route name is invalid.
	 *
	 * @return string The formatted url.
	 */
	function trn_route( $name, $parameters = null ) {
		$routes = [
			'challenges.archive'               => 'challenges',
			'challenges.single'                => 'challenges/{id}',
			'challenges.single.create'         => 'challenges/create',
			'games.archive'                    => 'games',
			'ladder-competitors.single.edit'   => 'ladder-competitors/{id}/edit',
			'ladders.archive'                  => 'ladders',
			'ladders.single'                   => 'ladders/{id}',
			'ladders.single.matches'           => 'ladders/{id}#matches',
			'ladders.single.rules'             => 'ladders/{id}#rules',
			'ladders.single.standings'         => 'ladders/{id}#standings',
			'ladders.single.join'              => 'ladders/{id}/join',
			'matches.archive'                  => 'matches',
			'matches.single'                   => 'matches/{id}',
			'matches.single.create'            => 'matches/create',
			'matches.single.confirm'           => 'matches/{id}/confirm',
			'matches.single.report'            => 'matches/{id}/report',
			'players.archive'                  => 'players',
			'players.single'                   => 'players/{id}',
			'players.single.edit'              => 'players/{id}/edit',
			'players.single.dashboard'         => 'players/dashboard',
			'report.page'                      => 'report',
			'teams.archive'                    => 'teams',
			'teams.single'                     => 'teams/{id}',
			'teams.single.create'              => 'teams/create',
			'teams.single.edit'                => 'teams/{id}/edit',
			'tournaments.archive'              => 'tournaments',
			'tournaments.single'               => 'tournaments/{id}',
			'tournaments.single.brackets'      => 'tournaments/{id}#brackets',
			'tournaments.single.matches'       => 'tournaments/{id}#matches',
			'tournaments.single.registered'    => 'tournaments/{id}#registered',
			'tournaments.single.rules'         => 'tournaments/{id}#rules',
			'tournaments.single.seeding'       => 'tournaments/{id}#seeding',
			'tournaments.single.register'      => 'tournaments/{id}/register',
			'tournaments.single.replace'       => 'tournaments/{id}/replace',

			'tournament.clear'                 => 'report/?&mode=clear&match_id={match_id}',

			// Magic link TODO.
			'accept-invitation'                => 'teams/?&mode=acceptInvitation&code={join_code}',
			'confirm-email-result'             => 'report/?&mode=confirm_e_results&type={competition_type}&match_id={match_id}&mrf={reference_id}',

			// Begin admin routes.
			'admin.games'                      => 'admin.php?page=trn-games',
			'admin.upload-game-image'          => 'admin.php?page=games',
			'admin.games.edit'                 => 'admin.php?page=trn-games&id={id}&action=edit',
			'admin.games.delete'               => 'admin.php?page=trn-games&id={id}&action=delete',
			'admin.games.delete-confirm'       => 'admin.php?page=trn-games&id={id}&action=delete-confirm',

			'admin.ranks'                      => 'admin.php?page=ranks',
			'admin.save-rank'                  => 'admin.php?page=ranks&id={id}',

			'admin.tournaments'                => 'admin.php?page=trn-tournaments',
			'admin.tournaments.create'         => 'admin.php?page=trn-tournaments-new',
			'admin.tournaments.seed'           => 'admin.php?page=trn-tournaments&id={id}&action=seed',
			'admin.tournaments.start'          => 'admin.php?page=trn-tournaments&id={id}&action=start',
			'admin.tournaments.edit'           => 'admin.php?page=trn-tournaments&id={id}&action=edit',
			'admin.tournaments.clone'          => 'admin.php?page=trn-tournaments&id={id}&action=clone',
			'admin.tournaments.delete'         => 'admin.php?page=trn-tournaments&id={id}&action=delete',
			'admin.tournaments.delete-confirm' => 'admin.php?page=trn-tournaments&id={id}&action=delete-confirm',
			'admin.tournaments.finish'         => 'admin.php?page=trn-tournaments&id={id}&action=finish',
			'admin.tournaments.reset'          => 'admin.php?page=trn-tournaments&id={id}&action=reset',
			'admin.tournaments.reset-confirm'  => 'admin.php?page=trn-tournaments&id={id}&action=reset-confirm',
			'admin.tournaments.registration'   => 'admin.php?page=trn-tournaments&id={id}&action=registration',
			'admin.tournaments.remove-entry'   => 'admin.php?page=trn-tournaments&tournament_entry_id={tournament_entry_id}&action=remove-entry',

			'admin.ladders'                    => 'admin.php?page=trn-ladders',
			'admin.ladders.create'             => 'admin.php?page=trn-ladders-new',
			'admin.ladders.delete'             => 'admin.php?page=trn-ladders&id={id}&action=delete',
			'admin.ladders.delete-confirm'     => 'admin.php?page=trn-ladders&id={id}&action=delete-confirm',
			'admin.ladders.edit'               => 'admin.php?page=trn-ladders&id={id}&action=edit',
			'admin.ladders.clone'              => 'admin.php?page=trn-ladders&id={id}&action=clone',

			'admin.ladders.matches'            => 'admin.php?page=trn-ladders-matches',
			'admin.ladders.report-match'       => 'admin.php?page=trn-ladders-matches',
			'admin.ladders.edit-match'         => 'admin.php?page=trn-ladders-matches&action=edit-match&id={id}',
			'admin.ladders.save-match'         => 'admin-post.php?action=trn-update-match&id={id}',
			'admin.ladders.confirm-match'      => 'admin.php?page=trn-ladders-matches&action=confirm&id={id}',
			'admin.ladders.delete-match'       => 'admin.php?page=trn-ladders-matches&action=delete&id={id}',
			'admin.ladders.resolve-match'      => 'admin.php?page=trn-ladders-matches&action=resolve&id={id}&winner_id={winner_id}',
			'admin.matches.select-competitors' => 'admin.php?page=trn-ladders-matches&action=select-competitors',

			'admin.tournaments.matches'        => 'admin.php?page=trn-tournaments-matches',
			'admin.tournaments.clear-match'    => 'admin.php?page=trn-tournaments-matches&action=clear&id={id}',
			'admin.tournaments.confirm-match'  => 'admin.php?page=trn-tournaments-matches&action=confirm&id={id}',
			'admin.tournaments.advance-match'  => 'admin.php?page=trn-tournaments-matches&action=advance&id={id}&winner_id={winner_id}',

			'admin.tournamatch.settings'       => 'admin.php?page=trn-settings',
			'admin.tournamatch.save-settings'  => 'admin-post.php?action=trn-save-settings',
			'admin.tools'                      => 'admin.php?page=tools',
			'admin.clear-data'                 => 'admin.php?page=tools&action=clear-data',
			'admin.purge-data'                 => 'admin.php?page=tools&action=purge-data',
		];

		if ( ! array_key_exists( $name, $routes ) ) {
			throw new InvalidArgumentException( "TRN route '$name' does not exist." );
		}

		$url = $routes[ $name ];

		$matches = array();
		preg_match_all( '/\{(.*?)\}/', $url, $matches );

		$required_parameters  = array_flip( $matches[1] );
		$remaining_parameters = $parameters;
		if ( count( $required_parameters ) > 0 ) {
			$to_replace = array();
			foreach ( $required_parameters as $key => $value ) {
				if ( ! array_key_exists( $key, $parameters ) ) {
					throw new InvalidArgumentException( "TRN route '$name' missing argument: $key" );
				}
				$to_replace[ $matches[0][ $value ] ] = $parameters[ $key ];
				unset( $remaining_parameters[ $key ] );
			}

			$url = str_replace( array_keys( $to_replace ), array_values( $to_replace ), $url );
		}

		if ( is_array( $remaining_parameters ) && ( 0 < count( $remaining_parameters ) ) ) {
			array_walk(
				$remaining_parameters,
				function ( &$value, $key ) {
					$value = "$key=$value";
				}
			);

			if ( false === strstr( $url, '?' ) ) {
				$url = trailingslashit( $url ) . '?' . implode( '&', $remaining_parameters );
			} else {
				$url = $url . '&' . implode( '&', $remaining_parameters );
			}
		}

		if ( starts_with( $url, 'admin' ) ) {
			return admin_url( $url );
		} else {
			return site_url( $url );
		}
	}
}


if ( ! function_exists( 'trn_template_list' ) ) {
	/**
	 * Returns available page templates defined by the Tournamatch Brackets plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function trn_template_list() {
		return array(
			'trn_challenges_single'              => 'single-trn-challenge.php',
			'trn_challenges_single_create'       => 'single-trn-challenge-create.php',
			'trn_challenges_archive'             => 'archive-trn-challenge.php',
			'trn_games_archive'                  => 'archive-trn-game.php',
			'trn_ladders_single'                 => 'single-trn-ladder.php',
			'trn_ladders_single_join'            => 'single-trn-ladder-join.php',
			'trn_ladder_competitors_single_edit' => 'single-trn-ladder-competitor-edit.php',
			'trn_ladders_archive'                => 'archive-trn-ladder.php',
			'trn_matches_single'                 => 'single-trn-match.php',
			'trn_matches_single_report'          => 'single-trn-match-report.php',
			'trn_matches_single_create'          => 'single-trn-match-create.php',
			'trn_matches_single_confirm'         => 'single-trn-match-confirm.php',
			'trn_matches_archive'                => 'archive-trn-match.php',
			'trn_players_single'                 => 'single-trn-player.php',
			'trn_players_single_edit'            => 'single-trn-player-edit.php',
			'trn_players_single_dashboard'       => 'single-trn-player-dashboard.php',
			'trn_players_archive'                => 'archive-trn-player.php',
			'trn_teams_single'                   => 'single-trn-team.php',
			'trn_teams_single_create'            => 'single-trn-team-create.php',
			'trn_teams_single_edit'              => 'single-trn-team-edit.php',
			'trn_teams_archive'                  => 'archive-trn-team.php',
			'trn_tournaments_single'             => 'single-trn-tournament.php',
			'trn_tournaments_single_replace'     => 'single-trn-tournament-replace.php',
			'trn_tournaments_single_register'    => 'single-trn-tournament-register.php',
			'trn_tournaments_archive'            => 'archive-trn-tournament.php',
			'trn_report_dashboard_page'          => 'page-trn-report-dashboard.php',
			'trn_magic_link_page'                => 'page-trn-magic-link.php',
		);
	}
}

if ( ! function_exists( 'trn_filter_template_include' ) ) {
	/**
	 * Filters included template. Used to provide Tournamatch Brackets plugin-defined templates.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string
	 */
	function trn_filter_template_include( $template ) {
		global $post;

		$pagename = get_query_var( 'pagename' );

		$trn_templates = trn_template_list();

		// Return default template if we don't have a custom one defined.
		if ( ! isset( $trn_templates[ $pagename ] ) ) {
			return $template;
		}

		$file = plugin_dir_path( __FILE__ ) . 'templates/' . $trn_templates[ $pagename ];

		// Just to be safe, we check if the file exist first.
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			include $file;
		}

		// Return template.
		return $template;
	}

	add_filter( 'template_include', 'trn_filter_template_include' );
}

if ( ! function_exists( 'trn_add_rewrite_rules' ) ) {
	/**
	 * Adds URL rewrite rules to find Tournamatch pages by pretty links.
	 *
	 * @since 4.0.0
	 */
	function trn_add_rewrite_rules() {
		add_rewrite_rule( 'challenges[/]?$', 'index.php?pagename=trn_challenges_archive', 'top' );
		add_rewrite_rule( 'challenges/create[/]?$', 'index.php?pagename=trn_challenges_single_create', 'top' );
		add_rewrite_rule( 'challenges/([0-9]+)[/]?$', 'index.php?pagename=trn_challenges_single&id=$matches[1]', 'top' );
		add_rewrite_rule( 'games[/]?$', 'index.php?pagename=trn_games_archive', 'top' );
		add_rewrite_rule( 'ladders[/]?$', 'index.php?pagename=trn_ladders_archive', 'top' );
		add_rewrite_rule( 'ladders/([0-9]+)[/]?$', 'index.php?pagename=trn_ladders_single&id=$matches[1]', 'top' );
		add_rewrite_rule( 'ladders/([0-9]+)/join[/]?$', 'index.php?pagename=trn_ladders_single_join&id=$matches[1]', 'top' );
		add_rewrite_rule( 'ladder-competitors/([0-9]+)/edit[/]?$', 'index.php?pagename=trn_ladder_competitors_single_edit&id=$matches[1]', 'top' );
		add_rewrite_rule( 'matches[/]?$', 'index.php?pagename=trn_matches_archive', 'top' );
		add_rewrite_rule( 'matches/create[/]?$', 'index.php?pagename=trn_matches_single_create', 'top' );
		add_rewrite_rule( 'matches/([0-9]+)[/]?$', 'index.php?pagename=trn_matches_single&id=$matches[1]', 'top' );
		add_rewrite_rule( 'matches/([0-9]+)/report[/]?$', 'index.php?pagename=trn_matches_single_report&id=$matches[1]', 'top' );
		add_rewrite_rule( 'matches/([0-9]+)/confirm[/]?$', 'index.php?pagename=trn_matches_single_confirm&id=$matches[1]', 'top' );
		add_rewrite_rule( 'players[/]?$', 'index.php?pagename=trn_players_archive', 'top' );
		add_rewrite_rule( 'players/([0-9]+)[/]?$', 'index.php?pagename=trn_players_single&id=$matches[1]', 'top' );
		add_rewrite_rule( 'players/edit[/]?$', 'index.php?pagename=trn_players_single_edit', 'top' );
		add_rewrite_rule( 'players/([0-9]+)/edit[/]?$', 'index.php?pagename=trn_players_single_edit&id=$matches[1]', 'top' );
		add_rewrite_rule( 'players/dashboard[/]?$', 'index.php?pagename=trn_players_single_dashboard', 'top' );
		add_rewrite_rule( 'teams[/]?$', 'index.php?pagename=trn_teams_archive', 'top' );
		add_rewrite_rule( 'teams/create[/]?$', 'index.php?pagename=trn_teams_single_create', 'top' );
		add_rewrite_rule( 'teams/([0-9]+)[/]?$', 'index.php?pagename=trn_teams_single&id=$matches[1]', 'top' );
		add_rewrite_rule( 'teams/([0-9]+)/edit[/]?', 'index.php?pagename=trn_teams_single_edit&id=$matches[1]', 'top' );
		add_rewrite_rule( 'tournaments[/]?$', 'index.php?pagename=trn_tournaments_archive', 'top' );
		add_rewrite_rule( 'tournaments/([0-9]+)[/]?$', 'index.php?pagename=trn_tournaments_single&id=$matches[1]', 'top' );
		add_rewrite_rule( 'tournaments/([0-9]+)/replace[/]?$', 'index.php?pagename=trn_tournaments_single_replace&id=$matches[1]', 'top' );
		add_rewrite_rule( 'tournaments/([0-9]+)/register[/]?$', 'index.php?pagename=trn_tournaments_single_register&id=$matches[1]', 'top' );
		add_rewrite_rule( 'report[/]?$', 'index.php?pagename=trn_report_dashboard_page', 'top' );
		add_rewrite_rule( 'confirm/([A-Za-z0-9]+)[/]?$', 'index.php?pagename=trn_magic_link_page&confirm_hash=$matches[1]', 'top' );

		flush_rewrite_rules();
	}

	add_action( 'init', 'trn_add_rewrite_rules' );
}

if ( ! function_exists( 'trn_add_query_var' ) ) {
	/**
	 * Adds Tournamatch query variables to the global request object.
	 *
	 * @since 4.0.0
	 *
	 * @param array $vars The array of allowed query variables.
	 *
	 * @return array The array of allowed query variables.
	 */
	function trn_add_query_var( $vars ) {
		$vars[] = 'id';
		$vars[] = 'confirm_hash';

		return $vars;
	}

	add_filter( 'query_vars', 'trn_add_query_var' );
}

if ( ! function_exists( 'trn_get_template_part' ) ) {
	/**
	 * Returns the template part for Tournamatch templates.
	 *
	 * @since 4.0.0
	 *
	 * @param string      $slug The slug name for the generic template.
	 * @param string|null $name The name of the specialized template.
	 * @param array       $args Additional arguments passed to the template.
	 *
	 * @return bool Returns the template or false if not found.
	 */
	function trn_get_template_part( $slug, $name = null, $args = array() ) {
		/**
		 * Fires before the specified template part file is loaded.
		 *
		 * The dynamic portion of the hook name, `$slug`, refers to the slug name
		 * for the generic template part.
		 *
		 * @since 3.0.0
		 * @since 5.5.0 The `$args` parameter was added.
		 *
		 * @param string $slug The slug name for the generic template.
		 * @param string|null $name The name of the specialized template.
		 * @param array $args Additional arguments passed to the template.
		 */
		do_action( "get_template_part_{$slug}", $slug, $name, $args );

		$templates = array();
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "{$slug}-{$name}.php";
		}

		$templates[] = "{$slug}.php";

		/**
		 * Fires before an attempt is made to locate and load a template part.
		 *
		 * @since 5.2.0
		 * @since 5.5.0 The `$args` parameter was added.
		 *
		 * @param string $slug The slug name for the generic template.
		 * @param string $name The name of the specialized template.
		 * @param string[] $templates Array of template files to search for, in order.
		 * @param array $args Additional arguments passed to the template.
		 */
		do_action( 'get_template_part', $slug, $name, $templates, $args );

		if ( ! trn_locate_template( $templates, true, false, $args ) ) {
			return false;
		}
	}
}

if ( ! function_exists( 'trn_locate_template' ) ) {
	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
	 * inherit from a parent theme can just overload one file. If the template is
	 * not found in either of those, it looks in the theme-compat folder last.
	 *
	 * @attribution https://pippinsplugins.com/template-file-loaders-plugins/
	 *
	 * @since 4.0.0
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool         $load If true the template file will be loaded if it is found.
	 * @param bool         $require_once Whether to require_once or require. Default true.
	 *                                                                                    Has no effect if $load is false.
	 * @param array        $args Optional. Additional arguments passed to the template.
	 *                                                                     Default empty array.
	 *
	 * @return string The template filename if one is located.
	 */
	function trn_locate_template( $template_names, $load = false, $require_once = true, $args = array() ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			if ( file_exists( get_stylesheet_directory() . '/trn/' . $template_name ) ) {
				$located = get_stylesheet_directory() . '/trn/' . $template_name;
				break;
			} elseif ( file_exists( get_template_directory() . '/trn/' . $template_name ) ) {
				$located = get_template_directory() . '/trn/' . $template_name;
				break;
			} elseif ( file_exists( __TRNPATH . 'templates/' . $template_name ) ) {
				$located = __TRNPATH . 'templates/' . $template_name;
				break;
			}
		}

		if ( $load && ( '' !== $located ) ) {
			load_template( $located, $require_once, $args );
		}

		return $located;
	}
}

if ( ! function_exists( 'trn_template_redirect' ) ) {
	/**
	 * Adds an action hook fro redirecting a Tournamatch template.
	 *
	 * @since 4.0.0
	 */
	function trn_template_redirect() {
		global $wp_query;

		if ( array_key_exists( get_query_var( 'pagename' ), trn_template_list() ) ) {
			$wp_query->is_404 = false;
			header( 'HTTP/1.1 200 OK' );
		}
	}

	add_action( 'template_redirect', 'trn_template_redirect' );
}

if ( ! function_exists( 'trn_get_header' ) ) {
	/**
	 * Displays the header for all Tournamatch front end templates.
	 *
	 * @since 4.0.0
	 */
	function trn_get_header() {
		do_action( 'tournamatch_after_header' );
	}
}

if ( ! function_exists( 'trn_get_footer' ) ) {
	/**
	 * Displays the footer for all Tournamatch front end templates.
	 *
	 * @since 4.0.0
	 */
	function trn_get_footer() {
		do_action( 'tournamatch_before_footer' );

	}
}

if ( ! function_exists( 'trn_ordinal_suffix' ) ) {
	/**
	 * Returns a number suffix like in 1st, 2nd, 3rd, 11th, 21st, etc. for the given number.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $number Number to evaluate.
	 *
	 * @return string Returns 'st', 'nd', 'rd', or 'th'.
	 */
	function trn_ordinal_suffix( $number ) {
		$number = $number % 100;
		if ( $number < 11 || $number > 13 ) {
			switch ( $number % 10 ) {
				case 1:
					return 'st';
				case 2:
					return 'nd';
				case 3:
					return 'rd';
			}
		}

		return 'th';
	}
}

add_filter(
	'trn_magic_links',
	function ( $links ) {
		return array_merge(
			array(
				'0a' => 'trn_magic_link_accept_team_invitation',
				'1a' => 'trn_magic_link_confirm_match_result',
			),
			$links
		);
	}
);

add_action(
	'trn_magic_link_confirm_match_result',
	function ( $confirm_hash ) {
		global $wpdb;

		$match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `confirm_hash` = %s", $confirm_hash ) );
		echo '<h1 class="trn-mb-4">' . esc_html__( 'Confirm Match', 'tournamatch' ) . '</h1>';
		if ( is_null( $match ) ) {
			echo '<p>' . esc_html__( 'The given match is not valid.', 'tournamatch' ) . '</p>';
		} else {
			if ( 'reported' === $match->match_status ) {
				$service = new Tournamatch\Services\Matche();
				$service->confirm(
					array(
						'id'      => $match->match_id,
						'comment' => esc_html__(
							'Confirmed via email.',
							'tournamatch'
						),
					)
				);
				echo '<p>' . esc_html__( 'The match has been confirmed.', 'tournamatch' ) . '</p>';
			} else {
				echo '<p>' . esc_html__( 'Contest has already been confirmed.', 'tournamatch' ) . '</p>';
			}
		}
	}
);

add_action(
	'trn_magic_link_accept_team_invitation',
	function ( $confirm_hash ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE `accept_hash` = %s", $confirm_hash ), ARRAY_A );

		echo '<h1 class="trn-mb-4">' . esc_html__( 'Join Team', 'tournamatch' ) . '</h1>';
		if ( $row['team_member_invitation_id'] ) {
			$user_id = $row['user_id'];
			$exists  = $wpdb->get_row( $wpdb->prepare( "SELECT `team_member_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `team_id` = %d AND `user_id` = %d", $row['team_id'], $user_id ), ARRAY_A );

			if ( ! $exists['team_member_id'] ) {
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_teams` SET `members` = `members` + 1 WHERE `team_id` = %d", $row['team_id'] ) );
				$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_teams_members` (`team_member_id`, `team_id`, `user_id`, `joined_date`, `team_rank_id`) VALUES (NULL, %d, %d, UTC_TIMESTAMP(), 2)", $row['team_id'], $user_id ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}trn_teams_members_invitations` WHERE `team_member_invitation_id` = %d LIMIT 1", $row['team_member_invitation_id'] ) );
				/* translators: Closing and opening URL tags. */
				echo '<p>' . sprintf( esc_html__( 'You have been added to the team. %1$sClick here%2$s to view the team profile.', 'tournamatch' ), '<a href="' . esc_url( trn_route( 'teams.single', array( 'id' => $row['team_id'] ) ) ) . '">', '</a>' ) . '</p>';
			} else {
				echo '<p>' . esc_html__( 'You are already a member of this team.', 'tournamatch' ) . '</p>';
			}
		} else {
			echo '<p>' . esc_html__( 'The invitation no longer exists. The team owner either deleted it or this code has already been used.', 'tournamatch' ) . '</p>';
		}
	}
);

// Bracket helpers.

if ( ! function_exists( 'dropdown' ) ) {
	/**
	 * Displays the dropdown for a tournament bracket spot.
	 *
	 * @since 4.0.0
	 *
	 * @param object $competitor The competitor.
	 * @param object $match The match.
	 */
	function dropdown( $competitor, $match ) {
		if ( is_null( $match ) ) {
			return;
		}

		$user_id  = get_current_user_id();
		$is_admin = user_can( $user_id, 'activate_plugins' );

		$match_id    = intval( $match->match_id );
		$can_advance = ( $is_admin && ( 'confirmed' !== $match->match_status ) && ( $match->one_competitor_id > 0 ) && ( $match->two_competitor_id > 0 ) );
		$can_confirm = ( 'reported' === $match->match_status ) && ( $is_admin || ( ( strlen( $match->one_result ) === 0 && ( intval( $match->one_competitor_id ) === intval( $user_id ) ) ) || ( strlen( $match->two_result ) === 0 && ( intval( $match->two_competitor_id ) === intval( $user_id ) ) ) ) );
		$can_replace = ( $is_admin && in_array( $match->match_status, array( 'confirmed', 'scheduled' ), true ) );
		$has_dispute = ( 'wrong' === $match->one_result ) || ( 'wrong' === $match->two_result );
		$can_clear   = ( $is_admin && ( ( 'reported' === $match->match_status ) || $has_dispute ) );

		$notifications = array();
		if ( $can_confirm ) {
			$notifications[] = esc_html__( 'Match result pending confirmation.', 'tournamatch' );
		}
		if ( $has_dispute ) {
			$notifications[] = esc_html__( 'Match result has been disputed.', 'tournamatch' );
		}

		if ( count( $notifications ) > 0 ) {
			echo '<i class="fa fa-exclamation-circle" title="' . esc_html( implode( '&#10;', $notifications ) ) . '"></i>';
		}
		?>
		<div class="bracket-dropdown">
			<i class="fa fa-ellipsis-v trn-pull-right"></i>
			<div class="bracket-dropdown-content">
				<?php if ( $can_advance ) : ?>
					<a href="
					<?php
					trn_esc_route_e(
						'admin.tournaments.advance-match',
						[
							'id'        => $match_id,
							'winner_id' => $competitor->competitor_id,
							'_wpnonce'  => wp_create_nonce( 'tournamatch-bulk-matches' ),
						]
					)
					?>
								">
						<?php
						/* translators: This is a call-to-action that displays the player or team to be advanced. Keep it short and concise because this is displayed within a dropdown menu on the brackets page. */
						printf( esc_html__( 'Advance %s', 'tournamatch' ), esc_html( $competitor->competitor_name ) )
						?>
					</a>
				<?php endif; ?>
				<?php if ( $can_confirm ) : ?>
					<a href="<?php trn_esc_route_e( 'admin.tournaments.confirm-match', [ 'id' => $match->tournament_id ] ); ?>"><?php esc_html_e( 'Confirm Result', 'tournamatch' ); ?></a>
				<?php endif; ?>
				<?php if ( $can_replace ) : ?>
					<a href="
					<?php
					trn_esc_route_e(
						'tournaments.single.replace',
						[
							'id'            => $match->tournament_id,
							'match_id'      => $match_id,
							'competitor_id' => $competitor->competitor_id,
							'_wpnonce'      => wp_create_nonce( 'tournament-replace-competitor' ),
						]
					)
					?>
								">
						<?php /* translators: This is a call-to-action that display a player or team name to be replaced. Keep it short and concise because this is displayed within a dropdown menu on the brackets page. */ ?>
						<?php printf( esc_html__( 'Replace %s', 'tournamatch' ), esc_html( $competitor->competitor_name ) ); ?></a>
				<?php endif; ?>
				<?php if ( $has_dispute ) : ?>
					<a href="<?php trn_esc_route_e( 'admin.tournaments.matches' ); ?>"><?php esc_html_e( 'Manage Disputes', 'tournamatch' ); ?></a>
				<?php endif; ?>
				<?php if ( $can_clear ) : ?>
					<a
							href="
							<?php
							trn_esc_route_e(
								'admin.tournaments.clear-match',
								[
									'match_id' => $match_id,
									'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-matches' ),
								]
							);
							?>
									"
							class="trn-confirm-action-link trn-clear-match-link"
							data-match-id="<?php echo intval( $match_id ); ?>"
							data-confirm-title="<?php esc_html_e( 'Confirm Clear', 'tournamatch' ); ?>"
							data-confirm-message="<?php esc_html_e( 'Are you sure you want to clear the result? All pending reports and disputes will be erased.', 'tournamatch' ); ?>"
					><?php esc_html_e( 'Clear Result', 'tournamatch' ); ?></a>
				<?php endif; ?>

				<a href="<?php trn_esc_route_e( 'matches.single', array( 'id' => $match_id ) ); ?> "><?php esc_html_e( 'Match Details', 'tournamatch' ); ?></a>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'display' ) ) {
	/**
	 * Displays the competitor name and dropdown for a tournament spot.
	 *
	 * @since 4.0.0
	 *
	 * @param object $competitor The competitor to display.
	 */
	function display( $competitor ) {
		$options = get_option( 'tournamatch_options', null );
		if ( isset( $options['bracket_seeds_enabled'] ) && ( '1' === $options['bracket_seeds_enabled'] ) ) {
			echo '<span class="seed">' . intval( $competitor->seed ) . '.</span> ';
		}

		echo '<a href="' . esc_url( trn_route( "{$competitor->competitor_type}.single", array( 'id' => intval( $competitor->competitor_id ) ) ) ) . '">' . esc_html( $competitor->competitor_name ) . '</a>';
	}
}

if ( ! function_exists( 'name_or_undecided' ) ) {
	/**
	 * Displays the appropriate name, missing, or deleted information for a spot on a tournament bracket.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $competitor_id The id of the competitor.
	 * @param array   $competitors Array of competitors.
	 * @param object  $match Match information.
	 */
	function name_or_undecided( $competitor_id, $competitors, $match ) {
		if ( intval( $competitor_id ) === - 2 ) {
			echo '<center>' . esc_html__( 'DELETED', 'tournamatch' ) . '</center>';
		} elseif ( $competitor_id > 0 ) {
			display( $competitors[ $competitor_id ] );
			dropdown( $competitors[ $competitor_id ], $match );
		} else {
			echo '<center>' . esc_html( trn_get_option( 'tournament_undecided_display' ) ) . '</center>';
		}
	}
}

if ( ! function_exists( 'is_odd' ) ) {
	/**
	 * Evaluates whether a given number is odd.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $num Number to evaluate.
	 *
	 * @return int Returns true if the given number is odd, false otherwise.
	 */
	function is_odd( $num ) {
		return ( is_numeric( $num ) & ( $num & 1 ) );
	}
}

if ( ! function_exists( 'is_even' ) ) {
	/**
	 * Evaluates whether a given number is even.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $num Number to evaluate.
	 *
	 * @return int Returns true if the given number is even or 0, false otherwise.
	 */
	function is_even( $num ) {
		return ( is_numeric( $num ) & ( ! ( $num & 1 ) ) );
	}
}

// End of brackeks helpers.

if ( ! function_exists( 'match_form' ) ) {
	/**
	 * Displays the form for reporting a match result.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args Array of arguments.
	 */
	function match_form( $args ) {

		$side      = 'one';
		$my_fields = array(
			'id'      => 'one_competitor_id',
			'result'  => 'one_result',
			'comment' => 'one_comment',
		);

		$opponent_fields = array(
			'id'      => 'two_competitor_id',
			'result'  => 'two_result',
			'comment' => 'two_comment',
		);

		if ( isset( $args['match_id'] ) ) {
			if ( 'players' === $args['competitor_type'] ) {
				$side = ( intval( $args['one_competitor_id'] ) === get_current_user_id() ) ? 'one' : 'two';
			} else {
				$my_teams = array_column( $args['my_teams'], 'team_id' );
				$side     = ( in_array( $args['one_competitor_id'], $my_teams, true ) ) ? 'one' : 'two';
			}
		}

		if ( 'two' === $side ) {
			list( $opponent_fields, $my_fields ) = array( $my_fields, $opponent_fields );
		}
		?>
		<div id="trn-report-match-form-message"></div>
		<form id="trn-report-match-form" action="<?php trn_esc_route_e( 'report.page' ); ?>" method="post">
			<div class="trn-form-group row">
				<label for="competition_name"
						class="trn-col-sm-3"><?php echo esc_html( $args['competition_type'] ); ?></label>
				<div class="trn-col-sm-4">
					<p class="trn-form-control-static"><?php echo esc_html( $args['competition_name'] ); ?></p>
				</div>
			</div>
			<?php if ( 'players' === $args['competitor_type'] ) : ?>
				<input type="hidden" name="<?php echo esc_html( $my_fields['id'] ); ?>"
						value="<?php echo intval( get_current_user_id() ); ?>">
			<?php else : ?>
				<div class="trn-form-group row">
					<label for="<?php echo esc_html( $my_fields['id'] ); ?>"
							class="trn-col-sm-3"><?php esc_html_e( 'My Team', 'tournamatch' ); ?></label>
					<div class="trn-col-sm-4">
						<select id="<?php echo esc_html( $my_fields['id'] ); ?>"
								name="<?php echo esc_html( $my_fields['id'] ); ?>" class="trn-form-control">
							<?php foreach ( $args['my_teams'] as $my_team ) : ?>
								<option value="<?php echo intval( $my_team['team_id'] ); ?>"><?php echo esc_html( $my_team['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>
			<div class="trn-form-group row">
				<label for="<?php echo esc_html( $opponent_fields['id'] ); ?>"
						class="trn-col-sm-3"><?php esc_html_e( 'Opponent', 'tournamatch' ); ?></label>
				<div class="trn-col-sm-4">
					<?php if ( ! isset( $args['match_id'] ) ) : ?>
						<select id="<?php echo esc_html( $opponent_fields['id'] ); ?>"
								name="<?php echo esc_html( $opponent_fields['id'] ); ?>" class="trn-form-control">
							<?php foreach ( $args['opponents'] as $opponent ) : ?>
								<option value="<?php echo intval( $opponent->competitor_id ); ?>"><?php echo esc_html( $opponent->competitor_name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<p class="trn-form-control-static"><?php echo esc_html( $args['opponents']['competitor_name'] ); ?></p>
						<input type="hidden" name="<?php echo esc_html( $opponent_fields['id'] ); ?>"
								value="<?php echo intval( $args['opponents']['competitor_id'] ); ?>">
					<?php endif; ?>
				</div>
			</div>
			<div class="trn-form-group row">
				<label for="<?php echo esc_html( $my_fields['result'] ); ?>"
						class="trn-col-sm-3"><?php esc_html_e( 'Result', 'tournamatch' ); ?></label>
				<div class="trn-col-sm-4">
					<select id="<?php echo esc_html( $my_fields['result'] ); ?>"
							name="<?php echo esc_html( $my_fields['result'] ); ?>" class="trn-form-control">
						<option value='won'><?php esc_html_e( 'You Won', 'tournamatch' ); ?></option>
						<option value='lost'><?php esc_html_e( 'You Lost', 'tournamatch' ); ?></option>
						<?php if ( $args['uses_draws'] && ( 'Tournament' !== $args['competition_type'] ) ) : ?>
							<option value='draw'><?php esc_html_e( 'A Draw', 'tournamatch' ); ?></option>
						<?php endif; ?>
					</select>
				</div>
			</div>
			<div class="trn-form-group row">
				<label for="<?php echo esc_html( $my_fields['comment'] ); ?>"
						class="trn-col-sm-3"><?php esc_html_e( 'Comment', 'tournamatch' ); ?></label>
				<div class="trn-col-sm-6">
					<textarea class="trn-form-control" id="<?php echo esc_html( $my_fields['comment'] ); ?>"
							name="<?php echo esc_html( $my_fields['comment'] ); ?>" rows="5"></textarea>
				</div>
			</div>
			<div class="trn-form-group row">
				<div class="offset-sm-3 trn-col-sm-3">
					<input type='hidden' name='<?php echo esc_html( $args['competition_slug'] ); ?>'
							value='<?php echo intval( $args['competition_id'] ); ?>'>
					<input type='hidden' name='competition_id'
							value='<?php echo esc_html( $args['competition_id'] ); ?>'>
					<input type='hidden' name='competition_type'
							value='<?php echo esc_html( strtolower( $args['competition_type'] ) ); ?>s'>
					<?php if ( isset( $args['match_id'] ) ) : ?>
						<input type='hidden' name='match_id' value='<?php echo intval( $args['match_id'] ); ?>'>
					<?php endif; ?>
					<input type='submit' id="report-button" class="trn-button"
							value='<?php esc_html_e( 'Report', 'tournamatch' ); ?>'>
				</div>
			</div>
		</form>
		<?php

		$options = array(
			'api_url'                => site_url( 'wp-json/tournamatch/v1/' ),
			'rest_nonce'             => wp_create_nonce( 'wp_rest' ),
			'can_select_my_team'     => isset( $args['my_teams'] ) && ( 0 < count( $args['my_teams'] ) ),
			'my_competitor_id_field' => $my_fields['id'],
			'language'               => array(
				'failure' => esc_html__( 'Error', 'tournamatch' ),
			),
		);

		wp_register_script( 'trn-report-new-match', plugins_url( './dist/js/report-new-match.js', __FILE__ ), array( 'tournamatch' ), '3.28.0', true );
		wp_localize_script( 'trn-report-new-match', 'trn_report_new_match_options', $options );
		wp_enqueue_script( 'trn-report-new-match' );
	}
}

add_action( 'user_register', 'trn_user_register' );
if ( ! function_exists( 'trn_user_register' ) ) {
	/**
	 * Handles the user registration action.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id Id of the user registered.
	 */
	function trn_user_register( $user_id ) {
		global $wpdb;

		$display_name = get_userdata( $user_id )->user_login;

		$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}trn_players_profiles` VALUES (%d, %s, '', 'blank.gif', 0, 0, 0, '', 'blank.gif')", $user_id, $display_name ) );
	}
}

add_action( 'admin_notices', 'trn_admin_notices' );
if ( ! function_exists( 'trn_admin_notices' ) ) {
	/**
	 * Displays relevant notices in the admin backend.
	 *
	 * @since 4.0.0
	 */
	function trn_admin_notices() {
		if ( ! get_option( 'permalink_structure' ) || ( '/%postname%/' !== get_option( 'permalink_structure' ) ) ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong>Error:</strong> Tournamatch requires permalinks. Please go <a
							href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>">here</a> and enable
					permalinks
					by selecting a 'Post name'.
				</p>
			</div>
			<?php
		}
	}
}

add_action(
	'admin_post_trn-replace-tournament-competitor',
	function () {
		global $wpdb;

		wp_verify_nonce( 'tournamatch-replace-tournament-competitor' );

		if ( current_user_can( 'manage_tournamatch' ) ) {
			$match_id          = isset( $_POST['match_id'] ) ? intval( $_POST['match_id'] ) : null;
			$competitor_id     = isset( $_POST['competitor_id'] ) ? intval( $_POST['competitor_id'] ) : null;
			$new_competitor_id = isset( $_POST['new_competitor_id'] ) ? intval( $_POST['new_competitor_id'] ) : false;
			$match             = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d LIMIT 1", $match_id ) );

			if ( ! is_null( $match ) && ( 0 < $new_competitor_id ) ) {
				if ( intval( $match->one_competitor_id ) === $competitor_id ) {
					$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `one_competitor_id` = %d WHERE `match_id` = %d", $new_competitor_id, $match_id ) );
				} else {
					$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}trn_matches` SET `two_competitor_id` = %d WHERE `match_id` = %d", $new_competitor_id, $match_id ) );
				}
			}
		}
		wp_safe_redirect( trn_route( 'tournaments.single.brackets', array( 'id' => $match->competition_id ) ) );
		exit;
	}
);

if ( ! function_exists( 'trn_table_column_exists' ) ) {
	/**
	 * Evaluates whether a database table has a given column.
	 *
	 * @since 4.1.0
	 *
	 * @param string $table_name The name of the table to check.
	 * @param string $column_name The name of the column to look for.
	 *
	 * @return bool True if exists, false otherwise.
	 */
	function trn_table_column_exists( $table_name, $column_name ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA` = %s AND `TABLE_NAME` = %s AND `column_name` = %s', DB_NAME, $table_name, $column_name ) );

		return ( '0' !== $exists );
	}
}

if ( ! function_exists( 'trn_verify_plugin_dependencies' ) ) {
	/**
	 * Verifies a plugin meets the necessary dependencies to activate.
	 *
	 * @since 4.1.0
	 *
	 * @param string $plugin Name of the plugin that is activating.
	 * @param array  $dependencies Array of dependencies to verify.
	 */
	function trn_verify_plugin_dependencies( $plugin, $dependencies ) {
		$errors = array();

		foreach ( $dependencies as $dependency => $minimum_version ) {
			$path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $dependency;
			if ( file_exists( $path ) ) {
				$data   = get_plugin_data( $path );
				$failed = ! version_compare( $data['Version'], $minimum_version, '>=' );
				if ( $failed ) {
					/* translators: Plugin Name, a semantic version, another plugin name. Another plugin name and the actual version. */
					$errors[] = sprintf( esc_html__( 'Plugin "%1$s" requires a minimum version "%2$s" of "%3$s". "%4$s" is version "%5$s" and the minimum was not met.', 'tournamatch' ), $plugin, $minimum_version, $data['Name'], $data['Name'], $data['Version'] );
				}
			}
		};

		if ( 0 < count( $errors ) ) {
			echo '<h3>' . esc_html__( 'Please update all Tournamatch plugins before activating.', 'tournamatch' ) . ' ' . esc_html__( 'The minimum version was not met for one or more plugins.', 'tournamatch' ) . '</h3>';
			echo '<p>';
			array_walk(
				$errors,
				function( $error ) {
					echo esc_html( $error ) . '<br>';
				}
			);
			echo '</p>';

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error, WordPress.PHP.NoSilencedErrors.Discouraged
			@trigger_error( esc_html__( 'Please update all Tournamatch plugins before activating.', 'tournamatch' ), E_USER_ERROR );
		}
	}
}

if ( ! function_exists( 'trn_array_insert' ) ) {
	/**
	 * Inserts a key/value pair in an array before or after a given key.
	 *
	 * @since 4.1.0
	 *
	 * https://stackoverflow.com/a/25878227
	 *
	 * @param array  $array The target array to manipulate.
	 * @param string $search_key The key in the target array to find.
	 * @param string $insert_key The key of the value to insert.
	 * @param mixed  $insert_value The value to insert.
	 * @param bool   $insert_after_founded_key Indicates whether to insert before or after the found key.
	 * @param bool   $append_if_not_found Indicates whether to add the new item to the end of the key was not found.
	 *
	 * @return array
	 */
	function trn_array_insert( $array, $search_key, $insert_key, $insert_value, $insert_after_founded_key = true, $append_if_not_found = false ) {
		$new_array = array();

		foreach ( $array as $key => $value ) {
			if ( $key === $search_key && ! $insert_after_founded_key ) {
				$new_array[ $insert_key ] = $insert_value;
			}

			$new_array[ $key ] = $value;

			if ( $key === $search_key && $insert_after_founded_key ) {
				$new_array[ $insert_key ] = $insert_value;
			}
		}

		if ( $append_if_not_found && count( $array ) === count( $new_array ) ) {
			$new_array[ $insert_key ] = $insert_value;
		}

		return $new_array;
	}
}

if ( ! function_exists( 'trn_array_merge_after_key' ) ) {
	/**
	 * Merges an array into another array after a given key.
	 *
	 * @since 4.2.0
	 *
	 * https://stackoverflow.com/a/25878227
	 *
	 * @param array  $array The target array to manipulate.
	 * @param string $search_key The key in the target array to find.
	 * @param array  $insert_array The array to insert.
	 * @param bool   $insert_after_founded_key Indicates whether to insert before or after the found key.
	 * @param bool   $append_if_not_found Indicates whether to add the new item to the end of the key was not found.
	 *
	 * @return array
	 */
	function trn_array_merge_after_key( $array, $search_key, $insert_array, $insert_after_founded_key = true, $append_if_not_found = false ) {
		$new_array = array();

		foreach ( $array as $key => $value ) {
			if ( $key === $search_key && ! $insert_after_founded_key ) {
				$new_array = array_merge( $new_array, $insert_array );
			}

			$new_array[ $key ] = $value;

			if ( $key === $search_key && $insert_after_founded_key ) {
				$new_array = array_merge( $new_array, $insert_array );
			}
		}

		if ( $append_if_not_found && count( $array ) === count( $new_array ) ) {
			$new_array = array_merge( $new_array, $insert_array );
		}

		return $new_array;
	}
}

if ( ! function_exists( 'trn_user_form' ) ) {
	/**
	 * Displays a Tournamatch user-facing form in the WordPress front end.
	 *
	 * @since 4.2.0
	 *
	 * @param array $form Array of form attributes and meta data.
	 * @param mixed $context The data context this form targets.
	 */
	function trn_user_form( $form, $context ) {
		$attributes = isset( $form['attributes'] ) ? $form['attributes'] : array();

		$form_id = isset( $attributes['id'] ) ? $attributes['id'] : '';
		$action  = isset( $attributes['action'] ) ? $attributes['action'] : '#';
		$method  = isset( $attributes['method'] ) ? $attributes['method'] : 'post';

		$fields = isset( $form['fields'] ) ? $form['fields'] : array();

		/**
		 * Filters an array of fields for a given form.
		 *
		 * The dynamic portion of the hook name, `$form_id`, refers to the user form's HTML id.
		 *
		 * Possible hook names include:
		 *
		 *  - `trn_trn_tournament_form_general_fields`
		 *  - `trn_trn_tournament_form_other_fields`
		 *  - `trn_trn_ladder_form_challenge_fields`
		 *
		 * @since 4.2.0
		 *
		 * @param stdClass $fields An array of field items to display.
		 * @param stdClass $context The data context item we are rendering a form for.
		 */
		$fields = apply_filters( "trn_{$form_id}_fields", $fields, $context );
		?>
		<form action="<?php echo esc_url( $action ); ?>" method="<?php echo esc_attr( $method ); ?>" id="<?php echo esc_attr( $form_id ); ?>"
		<?php
		$remaining_attributes = array_diff( $attributes, array( 'action', 'method', 'id' ) );
		foreach ( $remaining_attributes as $name => $value ) {
			echo ' ' . esc_html( $name ) . '="' . esc_attr( $value ) . '"';
		}
		?>
		>
			<?php
			foreach ( $fields as $field ) {
				$id          = isset( $field['id'] ) ? $field['id'] : '';
				$label       = isset( $field['label'] ) ? $field['label'] : $field['label'];
				$name        = isset( $field['name'] ) ? $field['name'] : $id;
				$type        = isset( $field['type'] ) ? $field['type'] : 'text';
				$required    = isset( $field['required'] ) ? $field['required'] : false;
				$disabled    = isset( $field['disabled'] ) ? $field['disabled'] : false;
				$value       = isset( $field['value'] ) ? $field['value'] : '';
				$description = isset( $field['description'] ) ? $field['description'] : null;
				?>
				<div class="trn-form-group trn-row">
					<label for="<?php echo esc_attr( $id ); ?>" class="trn-col-sm-3"><?php echo esc_html( $label ); ?></label>
						<?php

						switch ( $type ) {
							case 'select':
								echo '<div class="trn-col-sm-4">';

								$options = isset( $field['options'] ) ? $field['options'] : array();
								$options = is_array( $options ) ? $options : array();
								$options = array_map(
									function( $option ) {
											$default_option = array(
												'content' => $option,
												'value'   => $option,
											);

										if ( is_array( $option ) ) {
											return array_merge( $default_option, $option );
										} else {
											return $default_option;
										}
									},
									$options
								);

								/**
								 * Filters an array of options for a select drop down.
								 *
								 * The dynamic portion of the hook name, `$form_id`, refers to the client form's HTML id.
								 * The dynamic portion of the hook name, `$id`, refers to the client form input's HTML id.
								 *
								 * Possible hook names include:
								 *
								 *  - `trn_trn_tournament_form_game_id_options`
								 *  - `trn_trn_tournament_form_initial_seeding_options`
								 *  - `trn_trn_ladder_form_ranking_method_options`
								 *
								 * @since 4.1.0
								 *
								 * @param stdClass $options An array of 'content' 'value' items to display.
								 * @param stdClass $context The data context item we are rendering a form for.
								 */
								$options = apply_filters( "trn_{$form_id}_{$id}_options", $options, $context );

								if ( 0 < count( $options ) ) {
									echo '<select class="trn-form-control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"';
									if ( $required ) {
										echo ' required';
									}
									if ( $disabled ) {
										echo ' disabled';
									}
									echo '>';
									foreach ( $options as $option ) {
										$option_value   = isset( $option['value'] ) ? $option['value'] : '';
										$option_content = isset( $option['content'] ) ? $option['content'] : '';
										echo '<option value="' . esc_attr( $option_value ) . '"';
										if ( $value === $option_value ) {
											echo ' selected';
										}
										echo '>' . esc_html( $option_content ) . '</option>';
									}

									echo '</select>';
								} else {
									echo '<p>' . esc_html__( 'No items exist.', 'tournamatch' ) . '</p>';
								}
								break;
							case 'thumbnail':
								echo '<div class="trn-col-sm-9">';
								echo '<input class="trn-form-control-file" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="file" value="' . intval( $value ) . '"';
								if ( $required ) {
									echo ' required';
								}
								if ( $disabled ) {
									echo ' disabled';
								}
								echo '/>';
								break;
							case 'textarea':
								echo '<div class="trn-col-sm-6">';
								echo '<textarea class="trn-form-control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="10"';
								if ( $required ) {
									echo ' required';
								}
								if ( $disabled ) {
									echo ' disabled';
								}
								echo '>' . esc_textarea( $value ) . '</textarea>';
								break;

							case 'number':
								echo '<div class="trn-col-sm-4">';
								echo '<input class="trn-form-control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="number" value="' . intval( $value ) . '"';
								if ( $required ) {
									echo ' required';
								}
								if ( $disabled ) {
									echo ' disabled';
								}
								echo '/>';
								break;

							case 'text':
							default:
								echo '<div class="trn-col-sm-4">';
								echo '<input class="trn-form-control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="text" value="' . esc_attr( $value ) . '"';
								if ( $required ) {
									echo ' required';
								}
								if ( $disabled ) {
									echo ' disabled';
								}
								if ( isset( $field['maxlength'] ) && ( 0 < intval( $field['maxlength'] ) ) ) {
									echo ' maxlength="' . intval( $field['maxlength'] ) . '"';
								}
								echo '/>';
								break;
						}

						if ( ! is_null( $description ) ) {
							echo '<small class="trn-form-text trn-text-muted">' . esc_html( $description ) . '</small>';
						}

						if ( 'thumbnail' === $type ) {
							if ( isset( $field['thumbnail'] ) && ( $field['thumbnail'] instanceof \Closure ) ) {
								call_user_func( $field['thumbnail'], $context );
							}
						}
						?>
					</div>
				</div>
				<?php
			}

			$submit_id      = isset( $form['submit']['id'] ) ? $form['submit']['id'] : '';
			$submit_content = isset( $form['submit']['content'] ) ? $form['submit']['content'] : __( 'Submit', 'tournamatch' );
			?>
			<div class="trn-form-group trn-row">
				<div class="trn-offset-sm-3 trn-col-sm-4">
					<input id="<?php echo esc_attr( $submit_id ); ?>" class="trn-button" type="submit" value="<?php echo esc_attr( $submit_content ); ?>">
				</div>
			</div>
		</form>
		<?php
	}
}

if ( ! function_exists( 'trn_admin_form' ) ) {
	/**
	 * Displays an Tournamatch form in the WordPress backend.
	 *
	 * @since 4.1.0
	 *
	 * @param array $form Array of form attributes and meta data.
	 * @param mixed $context The data context this form targets.
	 */
	function trn_admin_form( $form, $context ) {
		$form_id  = isset( $form['id'] ) ? $form['id'] : '';
		$action   = isset( $form['action'] ) ? $form['action'] : '#';
		$method   = isset( $form['method'] ) ? $form['method'] : 'post';
		$sections = isset( $form['sections'] ) ? $form['sections'] : array();

		/**
		 * Filters an array of sections for this form.
		 *
		 * The dynamic portion of the hook name, `$form_id`, refers to the admin form's HTML id.
		 *
		 * Possible hook names include:
		 *
		 *  - `trn_trn_tournament_form_sections`
		 *  - `trn_trn_tournament_form_sections`
		 *  - `trn_trn_ladder_form_sections`
		 *
		 * @since 4.1.0
		 *
		 * @param stdClass $sections An array of sections to display.
		 * @param stdClass $context The data context item we are rendering a form for.
		 */
		$sections = apply_filters( "trn_{$form_id}_sections", $sections, $context );

		$submit_id      = isset( $form['submit']['id'] ) ? $form['submit']['id'] : '';
		$submit_content = isset( $form['submit']['content'] ) ? $form['submit']['content'] : __( 'Submit', 'tournamatch' );
		?>
		<form action="<?php echo esc_url( $action ); ?>" method="<?php echo esc_attr( $method ); ?>" id="<?php echo esc_attr( $form_id ); ?>">
			<?php
			foreach ( $sections as $section ) :
				$section_id = isset( $section['id'] ) ? $section['id'] : null;
				$content    = isset( $section['content'] ) ? $section['content'] : '';
				$fields     = isset( $section['fields'] ) ? $section['fields'] : array();

				if ( is_null( $section_id ) ) {
					continue;
				}

				/**
				 * Filters an array of fields for a given section.
				 *
				 * The dynamic portion of the hook name, `$form_id`, refers to the admin form's HTML id.
				 * The dynamic portion of the hook name, `$section_id`, refers to the admin form section's HTML id.
				 *
				 * Possible hook names include:
				 *
				 *  - `trn_trn_tournament_form_general_fields`
				 *  - `trn_trn_tournament_form_other_fields`
				 *  - `trn_trn_ladder_form_challenge_fields`
				 *
				 * @since 4.1.0
				 *
				 * @param stdClass $fields An array of field items to display.
				 * @param stdClass $context The data context item we are rendering a form for.
				 */
				$fields = apply_filters( "trn_{$form_id}_{$section_id}_fields", $fields, $context );

				?>
			<h2 class="title"><?php echo esc_html( $content ); ?></h2>
			<table class="form-table">
				<?php
				foreach ( $fields as $field ) :
					$id          = isset( $field['id'] ) ? $field['id'] : '';
					$label       = isset( $field['label'] ) ? $field['label'] : $field['label'];
					$name        = isset( $field['name'] ) ? $field['name'] : $id;
					$type        = isset( $field['type'] ) ? $field['type'] : 'text';
					$required    = isset( $field['required'] ) ? $field['required'] : false;
					$disabled    = isset( $field['disabled'] ) ? $field['disabled'] : false;
					$value       = isset( $field['value'] ) ? $field['value'] : '';
					$description = isset( $field['description'] ) ? $field['description'] : null;

					?>
					<tr class="form-field <?php echo esc_attr( "trn_{$id}_row" ); ?>">
						<th scope="row">
							<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?>
								<?php if ( $required ) : ?>
									<span class="description"><?php esc_html_e( '(required)', 'tournamatch' ); ?></span>
								<?php endif; ?>
							</label>
						</th>
						<td>
							<?php

							switch ( $type ) {
								case 'datetime-local':
									if ( 0 < strlen( $value ) ) {
										$start_date = new \DateTime( $value . 'Z' );
										$start_date->setTimezone( new \DateTimeZone( wp_timezone_string() ) );
										$start_date = $start_date->format( 'Y-m-d\TH:i' );
									} else {
										$start_date = '';
									}

									echo '<input id="' . esc_attr( $id ) . '_field" name="' . esc_attr( $name ) . '_field" type="datetime-local" value="' . esc_attr( $start_date ) . '"';
									if ( $required ) {
										echo ' required';
									}
									if ( $disabled ) {
										echo ' disabled';
									}
									echo '>';
									echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="hidden" value="' . esc_attr( $start_date ) . '"';
									if ( $disabled ) {
										echo ' disabled';
									}
									echo '>';
									break;

								case 'select':
									$options = isset( $field['options'] ) ? $field['options'] : array();
									$options = is_array( $options ) ? $options : array();
									$options = array_map(
										function( $option ) {
												$default_option = array(
													'content' => $option,
													'value'   => $option,
												);

											if ( is_array( $option ) ) {
												return array_merge( $default_option, $option );
											} else {
												return $default_option;
											}
										},
										$options
									);

									/**
									 * Filters an array of options for a select drop down.
									 *
									 * The dynamic portion of the hook name, `$form_id`, refers to the admin form's HTML id.
									 * The dynamic portion of the hook name, `$id`, refers to the admin form input's HTML id.
									 *
									 * Possible hook names include:
									 *
									 *  - `trn_trn_tournament_form_game_id_options`
									 *  - `trn_trn_tournament_form_initial_seeding_options`
									 *  - `trn_trn_ladder_form_ranking_method_options`
									 *
									 * @since 4.1.0
									 *
									 * @param stdClass $options An array of 'content' 'value' items to display.
									 * @param stdClass $context The data context item we are rendering a form for.
									 */
									$options = apply_filters( "trn_{$form_id}_{$id}_options", $options, $context );

									if ( 0 < count( $options ) ) {
										echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"';
										if ( $required ) {
											echo ' required';
										}
										if ( $disabled ) {
											echo ' disabled';
										}
										echo '>';
										foreach ( $options as $option ) {
											$option_value   = isset( $option['value'] ) ? $option['value'] : '';
											$option_content = isset( $option['content'] ) ? $option['content'] : '';
											echo '<option value="' . esc_attr( $option_value ) . '"';
											if ( $value === $option_value ) {
												echo ' selected';
											}
											echo '>' . esc_html( $option_content ) . '</option>';
										}

										echo '</select>';
									} else {
										echo '<p>' . esc_html__( 'No items exist.', 'tournamatch' ) . '</p>';
									}
									break;

								case 'textarea':
									echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="10"';
									if ( $required ) {
										echo ' required';
									}
									if ( $disabled ) {
										echo ' disabled';
									}
									echo '>' . esc_textarea( $value ) . '</textarea>';
									break;

								case 'number':
									echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="number" value="' . intval( $value ) . '"';
									if ( $required ) {
										echo ' required';
									}
									if ( $disabled ) {
										echo ' disabled';
									}
									echo '/>';
									break;

								case 'text':
								default:
									echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="text" value="' . esc_attr( $value ) . '"';
									if ( $required ) {
										echo ' required';
									}
									if ( $disabled ) {
										echo ' disabled';
									}
									echo '/>';
									break;
							}

							if ( ! is_null( $description ) ) {
								echo '<p class="description">' . esc_html( $description ) . '</p>';
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endforeach; ?>
			<p class="submit">
				<input type="submit" id="<?php echo esc_attr( $submit_id ); ?>" value="<?php echo esc_attr( $submit_content ); ?>" class="button button-primary">
			</p>
		</form>
		<?php
	}
}

if ( ! function_exists( 'trn_single_template_description_list' ) ) {
	/**
	 * Renders a description list.
	 *
	 * @since 4.2.0
	 *
	 * @param array $list Array of description list items.
	 * @param mixed $data_context Data context to bind each term to.
	 */
	function trn_single_template_description_list( $list, $data_context ) {
		?>
		<dl class="trn-dl">
			<?php foreach ( $list as $id => $item ) : ?>
				<?php

				if ( isset( $item['term'] ) && is_object( $item['term'] ) && ( $item['term'] instanceof \Closure ) ) {
					echo '<dt class="trn-dt">';
					call_user_func( $item['term'], $data_context );
					echo '</dt>';
				} elseif ( isset( $item['term'] ) && is_array( $item['term'] ) ) {
					$text = isset( $item['term']['text'] ) ? $item['term']['text'] : '';
					unset( $item['term']['text'] );

					echo '<dt class="trn-dt"';
					foreach ( $item['term'] as $attribute => $value ) {
						echo ' ' . esc_html( $attribute ) . '="' . esc_attr( $value ) . '"';
					}
					echo '>';

					if ( is_object( $text ) && ( $text instanceof \Closure ) ) {
						call_user_func( $text, $data_context );
					} else {
						echo esc_html( $text );
					}

					echo '</dt>';
				} else {
					echo '<dt class="trn-dt">' . esc_html( $item['term'] ) . '</dt>';
				}

				if ( isset( $item['description'] ) && is_object( $item['description'] ) && ( $item['description'] instanceof \Closure ) ) {
					echo '<dd class="trn-dd">';
					call_user_func( $item['description'], $data_context );
					echo '</dd>';
				} elseif ( isset( $item['description'] ) && is_array( $item['description'] ) ) {
					$text = isset( $item['description']['text'] ) ? $item['description']['text'] : '';
					unset( $item['description']['text'] );

					echo '<dd class="trn-dd"';
					foreach ( $item['description'] as $attribute => $value ) {
						echo ' ' . esc_html( $attribute ) . '="' . esc_attr( $value ) . '"';
					}
					echo '>';

					if ( is_object( $text ) && ( $text instanceof \Closure ) ) {
						call_user_func( $text, $data_context );
					} else {
						echo esc_html( $text );
					}

					echo '</dd>';
				} else {
					echo '<dd class="trn-dd">' . esc_html( $item['description'] ) . '</dd>';
				}
				?>
			<?php endforeach; ?>
		</dl>
		<?php
	}
}

if ( ! function_exists( 'trn_single_template_tab_views' ) ) {
	/**
	 * Renders a tabbed view of pages.
	 *
	 * @since 4.1.0
	 *
	 * @param array $views Array of view pages.
	 * @param mixed $data_context Data context to bind each page to.
	 */
	function trn_single_template_tab_views( $views, $data_context ) {
		?>
		<ul class="trn-nav trn-mt-md">
			<?php foreach ( $views as $view_id => $view ) : ?>
				<li class="trn-nav-item" role="presentation" >
					<?php

					if ( isset( $view['heading'] ) && is_object( $view['heading'] ) && ( $view['heading'] instanceof \Closure ) ) {
						call_user_func( $view['heading'], $data_context );
					} else {
						echo '<a class="trn-nav-link" href="';

						if ( isset( $view['href'] ) ) {
							echo esc_attr( esc_url( $view['href'] ) );
						} else {
							echo '#' . esc_attr( $view_id );
						}

						echo '"';

						if ( ! isset( $view['href'] ) ) {
							echo ' data-target="' . esc_attr( $view_id ) . '"';
						}

						$heading = isset( $view['heading'] ) ? $view['heading'] : $view_id;

						echo '>' . esc_html( $heading ) . '</a>';
					}
					?>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="trn-tab-content">
			<?php foreach ( $views as $view_id => $view ) : ?>
				<div id="<?php echo esc_attr( $view_id ); ?>" class="trn-tab-pane" role="tabpanel" aria-labelledby="<?php echo esc_attr( $view_id ); ?>-tab">
					<?php

					if ( isset( $view['content'] ) && is_object( $view['content'] ) && ( $view['content'] instanceof \Closure ) ) {
						call_user_func( $view['content'], $data_context );
					}

					?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
	}
}

add_action( 'plugins_loaded', 'trn_update_db_check' );
if ( ! function_exists( 'trn_update_db_check' ) ) {
	/**
	 * Determines if the database needs to be upgraded.
	 *
	 * @since 4.1.0
	 */
	function trn_update_db_check() {
		$options         = get_option( 'tournamatch_options', null );
		$current_version = isset( $options['version'] ) ? $options['version'] : '4.0.0';

		if ( TOURNAMATCH_VERSION !== $current_version ) {
			trn_upgrade_sql( $current_version );

			$options            = get_option( 'tournamatch_options', null );
			$options['version'] = TOURNAMATCH_VERSION;
			update_option( 'tournamatch_options', $options );
		}
	}
}

if ( ! function_exists( 'trn_upgrade_from_3' ) ) {
	/**
	 * Handles upgrades from Tournamatch 3.x to 4.x.
	 *
	 * @since 4.2.0
	 */
	function trn_upgrade_from_3() {
		global $wpdb;

		$sql   = array();
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders` ADD `competitor_type` ENUM('players','teams') NOT NULL DEFAULT 'players' AFTER `comp`";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_ladders` SET competitor_type = 'teams' WHERE comp = 3";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders` DROP `comp`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments` ADD `competitor_type` ENUM('players','teams') NOT NULL DEFAULT 'players' AFTER `comp`";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_tournaments` SET competitor_type = 'teams' WHERE comp = 3";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments` DROP `comp`";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_ladders_entries` SET competitor_type = 'players' WHERE competitor_type = 'player'";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_ladders_entries` SET competitor_type = 'teams' WHERE competitor_type = 'team'";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_tournaments_entries` SET competitor_type = 'players' WHERE competitor_type = 'player'";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_tournaments_entries` SET competitor_type = 'teams' WHERE competitor_type = 'team'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ratings` CHANGE `competitor_type` `competitor_type` ENUM('players','teams') NOT NULL DEFAULT 'players'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` CHANGE `competitor_type` `competitor_type` ENUM('players','teams') NOT NULL DEFAULT 'players'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments_entries` CHANGE `competitor_type` `competitor_type` ENUM('players','teams') NOT NULL DEFAULT 'players'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_competitions_petitions` CHANGE `competitor_type` `competitor_type` ENUM('players','teams') NOT NULL DEFAULT 'players'";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_matches` SET onetype = 'players' WHERE onetype = 'player'";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_matches` SET twotype = 'players' WHERE twotype = 'player'";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_matches` SET onetype = 'teams' WHERE onetype = 'team'";
		$sql[] = "UPDATE `{$wpdb->prefix}trn_matches` SET twotype = 'teams' WHERE twotype = 'team'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_matches` CHANGE `onetype` `onetype` ENUM('players','teams') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'players'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_matches` CHANGE `twotype` `twotype` ENUM('players','teams') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'players'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders` CHANGE `maxnppt` `team_size` TINYINT NULL DEFAULT NULL";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders` CHANGE `scr` `uses_score` TINYINT(1) NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments` CHANGE `scr` `uses_score` TINYINT(1) NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` CHANGE `leid` `ladder_entry_id` INT NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` DROP `bst`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` CHANGE `pos` `position` INT NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` CHANGE `ties` `draws` INT NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_members` CHANGE `ties` `draws` INT UNSIGNED NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` DROP `lastid`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` DROP `wlpercent`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` CHANGE `bststreak` `best_streak` INT NOT NULL DEFAULT '0', CHANGE `wrsstreak` `worst_streak` INT NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams` CHANGE `tid` `team_id` INT NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_members_requests` CHANGE `tid` `team_id` INT UNSIGNED NOT NULL";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_ranks` CHANGE `tid` `team_id` INT NOT NULL";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments` CHANGE `chckin` `check_in_seconds` INT NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_members` DROP `matches`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_members` CHANGE `rank` `team_rank_id` INT NOT NULL";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_players_profiles` DROP `tot_matches`, DROP `tot_wlpercent`, DROP `tot_events`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams` DROP `tot_matches`, DROP `tot_wlpercent`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams` CHANGE `tot_wins` `wins` INT NOT NULL DEFAULT '0', CHANGE `tot_losses` `losses` INT NOT NULL DEFAULT '0', CHANGE `tot_ties` `draws` INT NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_players_profiles` CHANGE `tot_wins` `wins` INT UNSIGNED NOT NULL, CHANGE `tot_losses` `losses` INT UNSIGNED NOT NULL, CHANGE `tot_ties` `draws` INT UNSIGNED NOT NULL";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_players_profiles` DROP `username`, DROP `joined_date`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_players_profiles` CHANGE `pic` `avatar` VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams` CHANGE `pic` `avatar` VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_matches` CHANGE `oneid` `one_competitor_id` INT NOT NULL DEFAULT '0', CHANGE `onetype` `one_competitor_type` ENUM('players','teams') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'players', CHANGE `oneip` `one_ip` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `oneres` `one_result` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `onescr` `one_score` INT NOT NULL DEFAULT '0', CHANGE `onecom` `one_comment` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `twoid` `two_competitor_id` INT NOT NULL DEFAULT '0', CHANGE `twotype` `two_competitor_type` ENUM('players','teams') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'players', CHANGE `twoip` `two_ip` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `twores` `two_result` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `twoscr` `two_score` INT NOT NULL DEFAULT '0', CHANGE `twocom` `two_comment` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_matches` CHANGE `mid` `match_id` INT NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_challenges` CHANGE `id` `challenge_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_attachments` CHANGE `id` `attachment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_games` CHANGE `gid` `game_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_games` CHANGE `img` `thumbnail` VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'blank.gif', CHANGE `console` `platform` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_games` DROP `gdesc`, DROP `active`";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders` CHANGE `lid` `ladder_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders` CHANGE `gid` `game_id` INT NOT NULL DEFAULT '0', CHANGE `wpts` `win_points` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `lpts` `loss_points` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `tpts` `draw_points` TINYINT(1) NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ladders_entries` CHANGE `lid` `ladder_id` INT NOT NULL DEFAULT '0'";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_players_profiles` CHANGE `uid` `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_ratings` CHANGE `id` `rating_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_series` CHANGE `id` `series_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_series_standings` CHANGE `id` `series_standing_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_trophies` CHANGE `id` `trophy_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_members_requests` CHANGE `id` `team_member_request_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_members_invitations` CHANGE `id` `team_member_invitation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_ranks` CHANGE `trid` `team_rank_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_teams_members` CHANGE `id` `team_member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments_entries` CHANGE `teid` `tournament_entry_id` INT NOT NULL AUTO_INCREMENT";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments_entries` CHANGE `tournid` `tournament_id` INT NOT NULL";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_players_profiles` CHANGE `loc` `location` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$sql[] = "ALTER TABLE `{$wpdb->prefix}trn_tournaments` CHANGE `tournid` `tournament_id` INT NOT NULL AUTO_INCREMENT, CHANGE `gid` `game_id` INT UNSIGNED NULL DEFAULT NULL";

		foreach ( $sql as $query ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $query );
		}
	}
}

if ( ! function_exists( 'trn_upgrade_sql' ) ) {
	/**
	 * Performs a database update for Tournamatch.
	 *
	 * @since 4.1.0
	 *
	 * @param string|null $version The current version.
	 */
	function trn_upgrade_sql( $version = null ) {
		global $wpdb;

		if ( empty( $version ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( version_compare( $version, '4.0.0', '<' ) ) {
			trn_upgrade_from_3();
		}

		if ( version_compare( $version, '4.1.0', '<' ) ) {
			$sql_fix = "CREATE TABLE `{$wpdb->prefix}trn_teams_members` (
  `team_member_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `joined_date` datetime NOT NULL,
  `team_rank_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wins` int(10) unsigned NOT NULL DEFAULT '0',
  `losses` int(10) unsigned NOT NULL DEFAULT '0',
  `draws` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`team_member_id`)
);";
			dbDelta( $sql_fix, true );
		}
	}
}

add_action(
	'tournamatch_after_header',
	function() {
		echo '<div class="trn-page"><div class="trn-container">';
	}
);

add_action(
	'tournamatch_before_footer',
	function() {
		echo '</div></div>';
	}
);
