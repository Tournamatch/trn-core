<?php
/**
 * Fires during plugin activation.
 *
 * @link       https://www.tournamatch.com
 * @since      3.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fires during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Tournamatch_Activator {

	/**
	 * Launches the activation process.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->install();
		$this->setup_options();
		$this->setup_image_directory_and_blanks();
		$this->setup_roles();
	}

	/**
	 * Configures the minimum Tournamatch admin role.
	 */
	private function setup_roles() {
		$role = get_role( 'administrator' );
		if ( ! empty( $role ) ) {
			$role->add_cap( 'manage_tournamatch' );
		}
	}

	/**
	 * Wraps the verify_image_directory_and_blanks static method into an instance method.
	 */
	private function setup_image_directory_and_blanks() {
		self::verify_image_directory_and_blanks();
	}

	/**
	 * Copies default images for avatars and games.
	 */
	public static function verify_image_directory_and_blanks() {
		$upload      = wp_upload_dir();
		$upload_dir  = $upload['basedir'];
		$upload_dir  = $upload_dir . '/tournamatch';
		$directories = array(
			$upload_dir . '/images/avatars',
			$upload_dir . '/images/games',
		);
		foreach ( $directories as $directory ) {
			if ( ! is_dir( $directory ) ) {
				wp_mkdir_p( $directory );
			}
		}
		$files = array(
			__TRNPATH . 'dist/images/avatars/blank.gif' => $upload_dir . '/images/avatars/blank.gif',
			__TRNPATH . 'dist/images/games/blank.gif'   => $upload_dir . '/images/games/blank.gif',
		);
		foreach ( $files as $source => $destination ) {
			if ( is_file( $source ) && ! is_file( $destination ) ) {
				copy( $source, $destination );
			}
		}
	}

	/**
	 * Setup options used by Tournamatch.
	 */
	private function setup_options() {
		// Setup options we can edit in admin later.
		$options = apply_filters(
			'tournamatch_activation_options',
			trn_get_default_options()
		);

		update_option( 'tournamatch_options', $options );
	}

	/**
	 * Returns a SQL statements to install tables.
	 *
	 * @return string SQL to install custom tables.
	 */
	private function get_sql() {
		global $wpdb;

		return "
CREATE TABLE `{$wpdb->prefix}trn_challenges` (
  `challenge_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ladder_id` int(10) unsigned NOT NULL,
  `challenge_type` enum('direct','blind') NOT NULL DEFAULT 'direct',
  `challenger_id` int(10) unsigned NOT NULL,
  `challengee_id` int(10) unsigned DEFAULT NULL,
  `match_time` timestamp NULL DEFAULT NULL,
  `accepted_state` enum('pending','accepted','declined','reported') NOT NULL DEFAULT 'pending',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `match_id` int(10) unsigned DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`challenge_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_games` (
  `game_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL DEFAULT '',
  `thumbnail` varchar(191) NOT NULL DEFAULT 'blank.gif',
  `thumbnail_id` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL,
  `platform` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`game_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_ladders` (
  `ladder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `thumbnail_id` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL,
  `competitor_type` enum('players','teams') NOT NULL DEFAULT 'players',
  `team_size` tinyint(2) unsigned DEFAULT NULL,
  `win_points` tinyint(2) NOT NULL DEFAULT '0',
  `loss_points` tinyint(2) NOT NULL DEFAULT '0',
  `draw_points` tinyint(2) NOT NULL DEFAULT '0',
  `direct_challenges` enum('enabled','disabled') NOT NULL DEFAULT 'disabled',
  `rules` text NOT NULL,
  `visibility` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`ladder_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_ladders_entries` (
  `ladder_entry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ladder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `competitor_id` int(10) unsigned NOT NULL,
  `competitor_type` enum('players','teams') NOT NULL DEFAULT 'players',
  `joined_date` datetime NOT NULL,
  `points` int(10) unsigned NOT NULL DEFAULT '0',
  `wins` int(10) unsigned NOT NULL DEFAULT '0',
  `losses` int(10) unsigned NOT NULL DEFAULT '0',
  `draws` int(10) unsigned NOT NULL DEFAULT '0',
  `streak` int(10) NOT NULL DEFAULT '0',
  `best_streak` int(10) NOT NULL DEFAULT '0',
  `worst_streak` int(10) NOT NULL DEFAULT '0',
  `time` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`ladder_entry_id`),
  KEY `ladder_id` (`ladder_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_matches` (
  `match_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `competition_type` enum('ladders','tournaments') NOT NULL,
  `spot` int(10) unsigned DEFAULT NULL,
  `one_competitor_id` int(10) NOT NULL DEFAULT '0',
  `one_competitor_type` enum('players','teams') NOT NULL DEFAULT 'players',
  `one_ip` varchar(20) NOT NULL DEFAULT '',
  `one_result` varchar(5) NOT NULL DEFAULT '',
  `one_comment` varchar(191) NOT NULL DEFAULT '',
  `two_competitor_id` int(11) NOT NULL DEFAULT '0',
  `two_competitor_type` enum('players','teams') NOT NULL DEFAULT 'players',
  `two_ip` varchar(20) NOT NULL DEFAULT '',
  `two_result` varchar(5) NOT NULL DEFAULT '',
  `two_comment` varchar(191) NOT NULL DEFAULT '',
  `match_date` datetime NOT NULL,
  `match_status` enum('scheduled','reported','confirmed','disputed','tournament_bye','undetermined') NOT NULL DEFAULT 'scheduled',
  `confirm_hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`match_id`),
  KEY `one_competitor_id` (`one_competitor_id`),
  KEY `two_competitor_id` (`two_competitor_id`),
  KEY `competition_id` (`competition_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_players_profiles` (
  `user_id` int(10) unsigned NOT NULL,
  `display_name` varchar(191) NOT NULL DEFAULT '',
  `location` varchar(191) NOT NULL DEFAULT '',
  `flag` varchar(191) NOT NULL DEFAULT 'blank.gif',
  `wins` int(10) unsigned NOT NULL DEFAULT '0',
  `losses` int(10) unsigned NOT NULL DEFAULT '0',
  `draws` int(10) unsigned NOT NULL DEFAULT '0',
  `profile` text DEFAULT NULL,
  `avatar` varchar(191) NOT NULL DEFAULT 'blank.gif',
  `banner` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_teams` (
  `team_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(5) NOT NULL DEFAULT '',
  `name` varchar(191) NOT NULL DEFAULT '',
  `flag` varchar(191) NOT NULL DEFAULT 'blank.gif',
  `joined_date` datetime NOT NULL,
  `avatar` varchar(191) NOT NULL DEFAULT '',
  `banner` varchar(191) DEFAULT NULL,
  `wins` int(10) unsigned NOT NULL DEFAULT '0',
  `losses` int(10) unsigned NOT NULL DEFAULT '0',
  `draws` int(10) unsigned NOT NULL DEFAULT '0',
  `members` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`team_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_teams_members` (
  `team_member_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `joined_date` datetime NOT NULL,
  `team_rank_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wins` int(10) unsigned NOT NULL DEFAULT '0',
  `losses` int(10) unsigned NOT NULL DEFAULT '0',
  `draws` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`team_member_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_teams_members_invitations` (
  `team_member_invitation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL,
  `invitation_type` ENUM('user','email') NOT NULL DEFAULT 'user',
  `user_id` INT(10) UNSIGNED NULL,
  `user_email` varchar(191) NULL,
  `invited_at` datetime NOT NULL,
  `accept_hash` char(32) NOT NULL,
  PRIMARY KEY (`team_member_invitation_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_teams_members_requests` (
  `team_member_request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `requested_at` datetime NOT NULL,
  PRIMARY KEY (`team_member_request_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_teams_ranks` (
  `team_rank_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `max` tinyint(2) NOT NULL,
  `weight` tinyint(2) NOT NULL,
  PRIMARY KEY (`team_rank_id`)
);

INSERT INTO `{$wpdb->prefix}trn_teams_ranks` (`team_rank_id`, `title`, `max`, `weight`) VALUES
(1, 'Leader', 1, 1),
(2, 'Member', -1, 2);

CREATE TABLE `{$wpdb->prefix}trn_tournaments` (
  `tournament_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `game_id` int(10) unsigned DEFAULT NULL,
  `thumbnail_id` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `competitor_type` enum('players','teams') NOT NULL DEFAULT 'players',
  `team_size` tinyint(2) unsigned DEFAULT NULL,
  `bracket_size` int(10) unsigned NOT NULL,
  `started_size` int(10) unsigned DEFAULT NULL,
  `games` smallint(5) unsigned NOT NULL,
  `rules` text NOT NULL,
  `visibility` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `status` enum('created','open','check_in','in_progress','complete') NOT NULL DEFAULT 'open',
  PRIMARY KEY (`tournament_id`)
);

CREATE TABLE `{$wpdb->prefix}trn_tournaments_entries` (
  `tournament_entry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tournament_id` int(10) unsigned NOT NULL,
  `competitor_id` int(10) unsigned NOT NULL,
  `competitor_type` enum('players','teams') NOT NULL DEFAULT 'players',
  `joined_date` datetime NOT NULL,
  `seed` int(10) unsigned NULL,
  PRIMARY KEY (`tournament_entry_id`),
  KEY `tournament_id` (`tournament_id`)
);
";
	}

	/**
	 * Installs Tournamatch tables.
	 *
	 * @return mixed
	 */
	private function install() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $this->get_sql(), true );

		// Migrate users into users table.
		trn_migrate_users();

		return true;
	}
}

new Tournamatch_Activator();
