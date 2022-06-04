<?php
/**
 * The uninstall script for Tournamatch.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// :'( Good-bye.
$tables = array(
	'challenges',
	'games',
	'ladders',
	'ladders_entries',
	'matches',
	'players_profiles',
	'teams',
	'teams_members',
	'teams_members_invitations',
	'teams_members_requests',
	'teams_ranks',
	'tournaments',
	'tournaments_entries',
);

global $wpdb;
foreach ( $tables as $table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}trn_{$table}`;" );
}

// Remove table options.
delete_option( 'tournamatch_options' );
