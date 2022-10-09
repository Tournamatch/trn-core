<?php
/**
 * Defines queries necessary for front end templates.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'trn_get_user_owned_teams' ) ) {
	/**
	 * Retrieves teams owned by a user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_owned_teams( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT `t`.`team_id` AS `id`, `t`.* 
FROM `{$wpdb->prefix}trn_teams` AS `t` 
  LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` 
WHERE `tm`.`team_rank_id` = 1 
  AND `tm`.`user_id` = %d",
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_user_teams' ) ) {
	/**
	 * Retrieves all teams the user is a member of.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_teams( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT `t`.`team_id` AS `id`, `t`.* 
FROM `{$wpdb->prefix}trn_teams` AS `t` 
  LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` 
WHERE `tm`.`user_id` = %d",
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_user_competitions' ) ) {
	/**
	 * Retrieves all competitions that a user is participating on.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_competitions( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `l`.`ladder_id` AS `id`, 
  'ladders.single' AS `route_name`, 
  'ladder' AS `competition_type`, 
  `l`.`name` AS `name`, 
  `l`.`game_id` AS `game_id`, 
  `g`.`name` AS `game_name`, 
  `g`.`thumbnail` AS `game_thumbnail`, 
  `l`.`status` 
FROM `{$wpdb->prefix}trn_ladders_entries` AS `le` 
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `le`.`ladder_id` 
  LEFT JOIN `{$wpdb->prefix}trn_games` AS `g` ON `l`.`game_id` = `g`.`game_id` 
WHERE `l`.`visibility` = 'visible' 
  AND (
   (`le`.`competitor_type` = 'players' AND `le`.`competitor_id` = %d) OR 
   (`le`.`competitor_type` = 'teams' AND `le`.`competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)))
UNION
SELECT 
  `t`.`tournament_id` AS `id`, 
  'tournaments.single' AS `route_name`, 
  'tournament' AS `competition_type`, 
  `t`.`name` AS `name`, 
  `t`.`game_id` AS `game_id`, 
  `g`.`name` AS `game_name`, 
  `g`.`thumbnail` AS `game_thumbnail`, 
  `t`.`status` 
FROM `{$wpdb->prefix}trn_tournaments_entries` AS `te` 
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `te`.`tournament_id` = `t`.`tournament_id` 
  LEFT JOIN `{$wpdb->prefix}trn_games` AS `g` ON `g`.`game_id` = `t`.`game_id` 
WHERE `t`.`visibility` = 'visible' 
  AND (
   (`te`.`competitor_type` = 'players' AND `competitor_id` = %d) OR 
   (`te`.`competitor_type` = 'teams' AND `competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)))
",
				$user_id,
				$user_id,
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_user_challenges' ) ) {
	/**
	 * Retrieves all player or team challenges that include the user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_challenges( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  c.*,
  l.competitor_type,
  l.name AS ladder_name,
  IF(rp.user_id IS NULL, rt.name, rp.display_name) AS challenger_name,
  IF(ep.user_id IS NULL, et.name, ep.display_name) AS challengee_name,
  IF(l.competitor_type = 'players', 'players.single', 'teams.single') AS competitor_slug,
  IF(l.competitor_type = 'players', 'id', 'id') AS competitor_slug_argument
FROM {$wpdb->prefix}trn_challenges AS c
  LEFT JOIN {$wpdb->prefix}trn_ladders AS l ON c.ladder_id = l.ladder_id
  LEFT JOIN {$wpdb->prefix}trn_players_profiles AS rp ON rp.user_id = c.challenger_id AND l.competitor_type = 'players'
  LEFT JOIN {$wpdb->prefix}trn_players_profiles AS ep ON ep.user_id = c.challengee_id AND l.competitor_type = 'players'
  LEFT JOIN {$wpdb->prefix}trn_teams AS rt ON rt.team_id = c.challenger_id AND l.competitor_type = 'teams'
  LEFT JOIN {$wpdb->prefix}trn_teams AS et ON et.team_id = c.challengee_id AND l.competitor_type = 'teams'
WHERE (((c.accepted_state = 'pending') AND (c.match_time > UTC_TIMESTAMP())) OR (c.accepted_state <> 'pending')) AND 
   (
    (
     l.competitor_type = 'teams'
       AND 
     (c.challengee_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) OR c.challenger_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d))
    )
   OR   
    (
     l.competitor_type = 'players'
       AND 
     (c.challenger_id = %d OR c.challengee_id = %d)
    )
   )
ORDER BY FIELD(c.accepted_state, 'pending') DESC, match_time ASC",
				$user_id,
				$user_id,
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_scheduled_matches' ) ) {
	/**
	 * Retrieves all player or team scheduled matches for the user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_scheduled_matches( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT m.*, l.name, p1.display_name AS one_name, p2.display_name AS two_name, 'ladders.single.standings' AS competition_slug, 'players.single' AS route_name, 'id' AS route_var
FROM {$wpdb->prefix}trn_matches AS m
  LEFT JOIN {$wpdb->prefix}trn_ladders AS l ON l.ladder_id = m.competition_id
    LEFT JOIN {$wpdb->prefix}trn_players_profiles AS p1 ON p1.user_id = m.one_competitor_id
      LEFT JOIN {$wpdb->prefix}trn_players_profiles AS p2 ON p2.user_id = m.two_competitor_id
WHERE m.competition_type = 'ladders'
  AND m.match_status = 'scheduled'
  AND l.visibility = 'visible'
  AND l.status = 'active'
  AND (
       (m.one_competitor_type = 'players' AND (m.one_competitor_id = %d OR m.two_competitor_id = %d))
)
UNION
SELECT m.*, l.name, t1.name AS one_name, t2.name AS two_name, 'ladders.single.standings' AS competition_slug, 'teams.single' AS route_name, 'id' AS route_var
FROM {$wpdb->prefix}trn_matches AS m
  LEFT JOIN {$wpdb->prefix}trn_ladders AS l ON l.ladder_id = m.competition_id
    LEFT JOIN {$wpdb->prefix}trn_teams AS t1 ON t1.team_id = m.one_competitor_id
      LEFT JOIN {$wpdb->prefix}trn_teams AS t2 ON t2.team_id = m.two_competitor_id
WHERE m.competition_type = 'ladders'
  AND m.match_status = 'scheduled'
  AND l.visibility = 'visible'
  AND l.status = 'active'
  AND (m.one_competitor_type = 'teams' 
    AND (
     m.one_competitor_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) OR 
     m.two_competitor_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d))
     )
UNION
SELECT m.*, t.name, p1.display_name AS one_name, p2.display_name AS two_name, 'tournaments.single.brackets' AS competition_slug, 'players.single' AS route_name, 'id' AS route_var
FROM {$wpdb->prefix}trn_matches AS m
  LEFT JOIN {$wpdb->prefix}trn_tournaments AS t ON t.tournament_id = m.competition_id
    LEFT JOIN {$wpdb->prefix}trn_players_profiles AS p1 ON p1.user_id = m.one_competitor_id
      LEFT JOIN {$wpdb->prefix}trn_players_profiles AS p2 ON p2.user_id = m.two_competitor_id
WHERE m.competition_type = 'tournaments'
  AND m.match_status = 'scheduled'
  AND t.visibility = 'visible'
  AND t.status = 'in_progress'
  AND (
       (m.one_competitor_type = 'players' AND (m.one_competitor_id = %d OR m.two_competitor_id = %d))
	)
UNION
SELECT m.*, t.name, t1.name AS one_name, t2.name AS two_name, 'tournaments.single.brackets' AS competition_slug, 'teams.single' AS route_name, 'id' AS route_var
FROM {$wpdb->prefix}trn_matches AS m
  LEFT JOIN {$wpdb->prefix}trn_tournaments AS t ON t.tournament_id = m.competition_id
    LEFT JOIN {$wpdb->prefix}trn_teams AS t1 ON t1.team_id = m.one_competitor_id
      LEFT JOIN {$wpdb->prefix}trn_teams AS t2 ON t2.team_id = m.two_competitor_id
WHERE m.competition_type = 'tournaments'
  AND m.match_status = 'scheduled'
  AND t.visibility = 'visible'
  AND t.status = 'in_progress'
  AND (m.one_competitor_type = 'teams' 
    AND (
     m.one_competitor_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) OR 
     m.two_competitor_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d))
     )
",
				$user_id,
				$user_id,
				$user_id,
				$user_id,
				$user_id,
				$user_id,
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_games' ) ) {
	/**
	 * Retrieves all games.
	 *
	 * @since 4.0.0
	 *
	 * @param string|null $platform Optional platform filter for the game.
	 *
	 * @return array|null|object
	 */
	function trn_get_games_with_competition_counts( $platform = null ) {
		global $wpdb;

		$sql = "
SELECT g.*, COUNT(DISTINCT l.ladder_id) AS ladders, COUNT(DISTINCT t.tournament_id) AS tournaments
FROM `{$wpdb->prefix}trn_games` AS g
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS l ON g.game_id = l.game_id AND l.status = 'active' AND l.visibility = 'visible'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS t ON g.game_id = t.game_id AND t.status <> 'complete' AND t.visibility = 'visible'
WHERE 1 = 1
";

		if ( ! is_null( $platform ) ) {
			$sql .= $wpdb->prepare( ' AND g.platform = %s', $platform );
		}

		$sql .= ' GROUP BY g.game_id';

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

if ( ! function_exists( 'trn_get_challenge' ) ) {
	/**
	 * Retrieves a single challenge item.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $challenge_id The id of the challenge.
	 *
	 * @return array|null|object
	 */
	function trn_get_challenge( $challenge_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
            SELECT 
              `c`.*, 
              `l`.`name` AS `ladder_name`, 
              `l`.`competitor_type`, 
              IF(`pcr`.`display_name` IS NULL, `tcr`.`name`, `pcr`.`display_name`) AS `challenger_name`, 
              IF(`pce`.`display_name` IS NULL, `tce`.`name`, `pce`.`display_name`) AS `challengee_name` 
            FROM `{$wpdb->prefix}trn_challenges` AS `c` 
              LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `c`.`ladder_id` 
              LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `pcr` ON `pcr`.`user_id` = `c`.`challenger_id` AND `l`.`competitor_type` = 'players' 
              LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `pce` ON `pce`.`user_id` = `c`.`challengee_id` AND `l`.`competitor_type` = 'players' 
              LEFT JOIN `{$wpdb->prefix}trn_teams` AS `tcr` ON `tcr`.`team_id` = `c`.`challenger_id` AND `l`.`competitor_type` = 'teams' 
              LEFT JOIN `{$wpdb->prefix}trn_teams` AS `tce` ON `tce`.`team_id` = `c`.`challengee_id` AND `l`.`competitor_type` = 'teams' 
            WHERE `c`.`challenge_id` = %d",
				$challenge_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_user_ladders_with_challenges' ) ) {
	/**
	 * Retrieve all ladders where that a user is participating on.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_ladders_with_challenges( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
                  SELECT 
                    `l`.`ladder_id` AS `id`, 
                    `l`.`name` AS `name` 
                  FROM `{$wpdb->prefix}trn_ladders` AS `l`
                    LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `l`.`ladder_id` = `le`.`ladder_id`
                  WHERE `l`.`status` = %s 
                    AND `l`.`visibility` = %s 
                    AND `l`.`direct_challenges` = %s
                    AND `le`.`competitor_id` = %d
                    AND `l`.`competitor_type` = 1
                  UNION
                  SELECT 
                    `l`.`ladder_id` AS `id`, 
                    `l`.`name` AS `name` 
                  FROM `{$wpdb->prefix}trn_ladders` AS `l`
                    LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `l`.`ladder_id` = `le`.`ladder_id`
                    LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `le`.`competitor_id` = `tm`.`team_id`
                  WHERE `l`.`status` = %s 
                    AND `l`.`visibility` = %s 
                    AND `l`.`direct_challenges` = %s
                    AND `l`.`competitor_type` = 'teams'
                    AND `tm`.`user_id` = %d
                    AND `tm`.`team_rank_id` = %d
                ",
				'active',
				'visible',
				'enabled',
				$user_id,
				'active',
				'visible',
				'enabled',
				$user_id,
				1
			),
			OBJECT_K
		);
	}
}

if ( ! function_exists( 'trn_the_ladder' ) ) {
	/**
	 * Prepares a ladder after it is retrieved from the database and before
	 * it is printed to the screen.
	 *
	 * @since 4.3.0
	 *
	 * @param object $ladder The ladder to prepare.
	 *
	 * @return mixed
	 */
	function trn_the_ladder( $ladder ) {
		global $wpdb;

		$ladder->competitors        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders_entries` WHERE `ladder_id` = %d", $ladder->ladder_id ) );
		$ladder->ranking_mode_field = 'points';
		$ladder->ranking_mode_label = __( 'Points', 'tournamatch' );

		return apply_filters( 'trn_the_ladder', $ladder );
	}
}

if ( ! function_exists( 'trn_get_ladder' ) ) {
	/**
	 * Retrieves a single ladder item.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $id The id of the ladder.
	 *
	 * @return array|null|object
	 */
	function trn_get_ladder( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  `l`.*, 
  `g`.`name` AS `game_name`, 
  `g`.`thumbnail` AS `game_thumbnail` 
FROM `{$wpdb->prefix}trn_ladders` AS `l` 
  LEFT JOIN `{$wpdb->prefix}trn_games` AS `g` ON `l`.`game_id` = `g`.`game_id` 
WHERE `l`.`ladder_id` = %d 
  AND `l`.`visibility` = 'visible'",
				$id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_ladders' ) ) {
	/**
	 * Retrieves all ladders.
	 *
	 * @since 4.0.0
	 *
	 * @param integer|null $game_id An optional game id filter for the ladder.
	 * @param string|null  $platform An optional platform filter for the ladder.
	 *
	 * @return array|null|object
	 */
	function trn_get_ladders( $game_id = null, $platform = null ) {
		global $wpdb;

		$sql = "
SELECT l.*, g.game_id, g.thumbnail, g.platform, g.name AS game, COUNT(le.ladder_entry_id) as competitors
FROM {$wpdb->prefix}trn_ladders AS l
  LEFT JOIN {$wpdb->prefix}trn_ladders_entries AS le ON l.ladder_id = le.ladder_id
  LEFT JOIN {$wpdb->prefix}trn_games AS g ON l.game_id = g.game_id
WHERE l.visibility = 'visible'";

		if ( ! is_null( $game_id ) ) {
			$sql .= $wpdb->prepare( ' AND l.game_id = %d', $game_id );
		}

		if ( ! is_null( $platform ) ) {
			$sql .= $wpdb->prepare( ' AND g.platform = %s', $platform );
		}

		$sql .= ' GROUP BY l.ladder_id';

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

if ( ! function_exists( 'trn_get_user_ladder' ) ) {
	/**
	 * Retrieves a single ladder entry item for the user and ladder.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 * @param integer $ladder_id The id of the ladder.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_ladder( $user_id, $ladder_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  *,
  `ladder_entry_id` AS `id`
FROM `{$wpdb->prefix}trn_ladders_entries`
WHERE (
  (`competitor_type` = 'players' AND `competitor_id` = %d) OR
  (`competitor_type` = 'teams' AND `competitor_id` IN 
    (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)
  )
) AND `ladder_id` = %d",
				$user_id,
				$user_id,
				$ladder_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_user_open_play_ladders' ) ) {
	/**
	 * Retrieves all open play-eligible ladders for a user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_open_play_ladders( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT `l`.`name`, `l`.`ladder_id`
FROM `{$wpdb->prefix}trn_ladders_entries` AS `le`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `le`.`ladder_id`
WHERE `l`.`visibility` = 'visible' AND `l`.`status` = 'active' AND
(
  (`le`.`competitor_type` = 'players' AND `le`.`competitor_id` = %d) OR
  (`le`.`competitor_type` = 'teams' AND `le`.`competitor_id` IN 
    (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)
  )
)",
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_user_ladders' ) ) {
	/**
	 * Retrieves all ladders for a user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_ladders( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT `ladder_id`
FROM `{$wpdb->prefix}trn_ladders_entries`
WHERE (
  (`competitor_type` = 'players' AND `competitor_id` = %d) OR
  (`competitor_type` = 'teams' AND `competitor_id` IN 
    (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)
  )
)",
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_ladder_competitors' ) ) {
	/**
	 * Retrieves a many ladder entry items for a ladder.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $id The id of the ladder.
	 *
	 * @return array|null|object
	 */
	function trn_get_ladder_competitors( $id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `le`.*,
  IF(`p`.`display_name` IS NULL, `t`.`name`, `p`.`display_name`) AS `competitor_name`
FROM `{$wpdb->prefix}trn_ladders_entries` AS `le`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p` ON `le`.`competitor_id` = `p`.`user_id` AND `le`.`competitor_type` = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t` ON `le`.`competitor_id` = `t`.`team_id` AND `le`.`competitor_type` = 'teams'
WHERE `le`.`ladder_id` = %d
ORDER BY `competitor_name` ASC",
				$id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_ladder_competitor' ) ) {
	/**
	 * Retrieves a single ladder entry item by id.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $id The id of the ladder entry.
	 *
	 * @return array|null|object
	 */
	function trn_get_ladder_competitor( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  `le`.*,
  IF(`p`.`display_name` IS NULL, `t`.`name`, `p`.`display_name`) AS `competitor_name`
FROM `{$wpdb->prefix}trn_ladders_entries` AS `le`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p` ON `le`.`competitor_id` = `p`.`user_id` AND `le`.`competitor_type` = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t` ON `le`.`competitor_id` = `t`.`team_id` AND `le`.`competitor_type` = 'teams'
WHERE `ladder_entry_id` = %d
LIMIT 1",
				$id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_player' ) ) {
	/**
	 * Retrieves a single player item.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $id The id of the player.
	 *
	 * @return array|null|object
	 */
	function trn_get_player( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  *,
  `display_name` AS `name`
FROM `{$wpdb->prefix}trn_players_profiles` 
WHERE `user_id` = %d",
				$id
			)
		);
	}
}

if ( ! function_exists( 'trn_the_player' ) ) {
	/**
	 * Prepares a player after it is retrieved from the database and before
	 * it is printed to the screen.
	 *
	 * @since 4.4.0
	 *
	 * @param object $player The player to prepare.
	 *
	 * @return mixed
	 */
	function trn_the_player( $player ) {
		return apply_filters( 'trn_the_player', $player );
	}
}

if ( ! function_exists( 'trn_get_tournaments' ) ) {
	/**
	 * Retrieves many tournament items.
	 *
	 * @since 4.0.0
	 *
	 * @param integer|null $game_id An optional game id filter for the tournament.
	 * @param integer|null $platform An optional platform filter for the tournament.
	 *
	 * @return array|null|object
	 */
	function trn_get_tournaments( $game_id = null, $platform = null ) {
		global $wpdb;

		$sql = "
SELECT t.*, g.game_id, g.thumbnail AS `game_thumbnail`, g.platform AS `game_platform`, g.name AS `game_name`, COUNT(te.tournament_entry_id) AS competitors
FROM {$wpdb->prefix}trn_tournaments AS t
  LEFT JOIN {$wpdb->prefix}trn_games AS g ON t.game_id = g.game_id
  LEFT JOIN {$wpdb->prefix}trn_tournaments_entries AS te ON te.tournament_id = t.tournament_id
WHERE t.visibility = 'visible'
		";

		if ( ! is_null( $game_id ) ) {
			$sql .= $wpdb->prepare( '  AND t.game_id = %d', $game_id );
		}
		if ( ! is_null( $platform ) ) {
			$sql .= $wpdb->prepare( '  AND g.platform = %s', $platform );
		}

		$sql .= ' GROUP BY t.tournament_id';
		$sql .= ' ORDER BY t.start_date ASC';

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

if ( ! function_exists( 'trn_get_tournament' ) ) {
	/**
	 * Retrieves a single tournament item.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $id The id of the tournament.
	 *
	 * @return array|null|object
	 */
	function trn_get_tournament( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT t.*, g.name AS game_name, g.thumbnail AS game_thumbnail, COUNT(te.tournament_entry_id) AS competitors
FROM {$wpdb->prefix}trn_tournaments AS t
  LEFT JOIN {$wpdb->prefix}trn_games AS g ON t.game_id = g.game_id
  LEFT JOIN {$wpdb->prefix}trn_tournaments_entries AS te ON te.tournament_id = t.tournament_id
WHERE t.tournament_id = %d
",
				$id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_user_tournaments' ) ) {
	/**
	 * Retrieves all tournaments for a user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_tournaments( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT *
FROM `{$wpdb->prefix}trn_tournaments_entries`
WHERE (
  (`competitor_type` = 'players' AND `competitor_id` = %d) OR 
  (`competitor_type` = 'teams' AND `competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d))
)",
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_tournament_competitors' ) ) {
	/**
	 * Retrieves competitors for a tournament.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $tournament_id The id for the tournament.
	 *
	 * @return array|null|object
	 */
	function trn_get_tournament_competitors( $tournament_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `te`.`competitor_id`,
  `te`.*,
  IF(`p`.`display_name` IS NULL, `t`.`name`, `p`.`display_name`) AS `competitor_name`
FROM `{$wpdb->prefix}trn_tournaments_entries` AS `te`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p` ON `te`.`competitor_id` = `p`.`user_id` AND `te`.`competitor_type` = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t` ON `te`.`competitor_id` = `t`.`team_id` AND `te`.`competitor_type` = 'teams'
WHERE `te`.`tournament_id` = %d AND `te`.`seed` > 0",
				$tournament_id
			),
			OBJECT_K
		);
	}
}

if ( ! function_exists( 'trn_get_registered_competitors' ) ) {
	/**
	 * Retrieves competitors registered for a tournament.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $tournament_id The id for the tournament.
	 *
	 * @return array|null|object
	 */
	function trn_get_registered_competitors( $tournament_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `te`.*,
  IF(`p`.`display_name` IS NULL, `t`.`name`, `p`.`display_name`) AS `competitor_name`,
  IF(`p`.`flag` IS NULL, `t`.`flag`, `p`.`flag`) AS `flag`,
  IF(`p`.`avatar` IS NULL, `t`.`avatar`, `p`.`avatar`) AS `avatar`,
  `t`.`members`
FROM `{$wpdb->prefix}trn_tournaments_entries` AS `te`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p` ON `te`.`competitor_id` = `p`.`user_id` AND `te`.`competitor_type` = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t` ON `te`.`competitor_id` = `t`.`team_id` AND `te`.`competitor_type` = 'teams'
WHERE `te`.`tournament_id` = %d",
				$tournament_id
			)
		);
	}
}

if ( ! function_exists( 'trn_get_team' ) ) {
	/**
	 * Retrieves a single team item.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $team_id The id of the team.
	 *
	 * @return array|null|object
	 */
	function trn_get_team( $team_id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT *, `team_id` AS `id` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $team_id ) );
	}
}

if ( ! function_exists( 'trn_get_user_ladder_teams' ) ) {
	/**
	 * Retrieves a users' teams for a ladder.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 * @param integer $ladder_id The id of the ladder.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_ladder_teams( $user_id, $ladder_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `t`.* 
FROM `{$wpdb->prefix}trn_teams` AS `t` 
  LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` 
  LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `l` ON `l`.`competitor_id` = `tm`.`team_id` 
WHERE `tm`.`user_id` = %d 
  AND `l`.`ladder_id` = %d",
				$user_id,
				$ladder_id
			),
			ARRAY_A
		);
	}
}

if ( ! function_exists( 'trn_get_user_tournament_teams' ) ) {
	/**
	 * Retrieves a users' teams for a tournament.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id of the user.
	 * @param integer $tournament_id The id of the tournament.
	 *
	 * @return array|null|object
	 */
	function trn_get_user_tournament_teams( $user_id, $tournament_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `t`.* 
FROM `{$wpdb->prefix}trn_teams` AS `t` 
  LEFT JOIN `{$wpdb->prefix}trn_teams_members` AS `tm` ON `t`.`team_id` = `tm`.`team_id` 
  LEFT JOIN `{$wpdb->prefix}trn_tournaments_entries` AS `te` ON `te`.`competitor_id` = `tm`.`team_id` 
WHERE `tm`.`user_id` = %d 
  AND `te`.`tournament_id` = %d",
				$user_id,
				$tournament_id
			),
			ARRAY_A
		);
	}
}

if ( ! function_exists( 'trn_get_team_owner' ) ) {
	/**
	 * Retrieves the owner of a team.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $team_id The id of the team.
	 *
	 * @return array|null|object
	 */
	function trn_get_team_owner( $team_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
SELECT 
  `tm`.`user_id` AS `id`, 
  `pp`.`display_name` AS `name` 
FROM `{$wpdb->prefix}trn_teams_members` AS `tm` 
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `pp` ON `pp`.`user_id` = `tm`.`user_id` 
WHERE `tm`.`team_id` = %d 
  AND `tm`.`team_rank_id` = %d",
				$team_id,
				1
			)
		);
	}
}

if ( ! function_exists( 'trn_get_match' ) ) {
	/**
	 * Retrieves a single match item.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $id The id of the match.
	 *
	 * @return array|null|object
	 */
	function trn_get_match( $id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $id ) );
	}
}

if ( ! function_exists( 'trn_get_scheduled_ladder_matches' ) ) {
	/**
	 * Retrieves scheduled matches for a user.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id for the user.
	 * @param integer $ladder_id The id for the ladder.
	 *
	 * @return array|null|object
	 */
	function trn_get_scheduled_ladder_matches( $user_id, $ladder_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT m.*, l.name, p1.display_name AS one_name, p2.display_name AS two_name, 'ladders.single.standings' AS competition_slug, 'players.single' AS route_name, 'id' AS route_var
FROM {$wpdb->prefix}trn_matches AS m
  LEFT JOIN {$wpdb->prefix}trn_ladders AS l ON l.ladder_id = m.competition_id
    LEFT JOIN {$wpdb->prefix}trn_players_profiles AS p1 ON p1.user_id = m.one_competitor_id
      LEFT JOIN {$wpdb->prefix}trn_players_profiles AS p2 ON p2.user_id = m.two_competitor_id
WHERE m.competition_type = 'ladders'
  AND m.competition_id = %d
  AND m.match_status = 'scheduled'
  AND l.visibility = 'visible'
  AND l.status = 'active'
  AND (
       (m.one_competitor_type = 'players' AND (m.one_competitor_id = %d OR m.two_competitor_id = %d))
)
UNION
SELECT m.*, l.name, t1.name AS one_name, t2.name AS two_name, 'ladders.single.standings' AS competition_slug, 'team-profile' AS route_name, 'team_id' AS route_var
FROM {$wpdb->prefix}trn_matches AS m
  LEFT JOIN {$wpdb->prefix}trn_ladders AS l ON l.ladder_id = m.competition_id
    LEFT JOIN {$wpdb->prefix}trn_teams AS t1 ON t1.team_id = m.one_competitor_id
      LEFT JOIN {$wpdb->prefix}trn_teams AS t2 ON t2.team_id = m.two_competitor_id
WHERE m.competition_type = 'ladders'
  AND m.competition_id = %d
  AND m.match_status = 'scheduled'
  AND l.visibility = 'visible'
  AND l.status = 'active'
  AND (m.one_competitor_type = 'teams' 
  AND 
    (
    m.one_competitor_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) OR 
    m.two_competitor_id IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d)
    )
  )  
",
				$ladder_id,
				$user_id,
				$user_id,
				$ladder_id,
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'get_can_confirm_matches' ) ) {
	/**
	 * Retrieves all matches reported by others that this user or this user's team may confirm.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id for the user.
	 *
	 * @return mixed
	 */
	function get_can_confirm_matches( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `m`.*, 
  IF(`l`.`name` IS NULL, `t`.`name`, `l`.`name`) AS `name`,
  IF(`l`.`competitor_type` IS NULL, `t`.`competitor_type`, `l`.`competitor_type`) AS `competitor_type`,
  `p1`.`display_name` AS `one_competitor_name`,
  `p2`.`display_name` AS `two_competitor_name`
FROM `{$wpdb->prefix}trn_matches` AS `m`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `m`.`competition_id` AND `m`.`competition_type` = 'ladders'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `t`.`tournament_id` = `m`.`competition_id` AND `m`.`competition_type` = 'tournaments'
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p1` ON `m`.`one_competitor_id` = `p1`.`user_id`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p2` ON `m`.`two_competitor_id` = `p2`.`user_id`
WHERE (`l`.`visibility` = 'visible' OR `l`.`visibility` IS NULL OR `t`.`visibility` = 'visible' OR `t`.`visibility` IS NULL)
  AND `one_competitor_type` = 'players'
  AND `match_status` = 'reported' 
  AND ((`one_competitor_id` = %d AND `one_result` = '') OR (`two_competitor_id` = %d AND `two_result` = ''))
UNION
SELECT 
  `m`.*, 
  IF(`l`.`name` IS NULL, `t`.`name`, `l`.`name`) AS `name`,
  IF(`l`.`competitor_type` IS NULL, `t`.`competitor_type`, `l`.`competitor_type`) AS `competitor_type`,
  `t1`.`name` AS `one_competitor_name`,
  `t2`.`name` AS `two_competitor_name`
FROM `{$wpdb->prefix}trn_matches` AS `m`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `m`.`competition_id` AND `m`.`competition_type` = 'ladders'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `t`.`tournament_id` = `m`.`competition_id` AND `m`.`competition_type` = 'tournaments'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t1` ON `m`.`one_competitor_id` = `t1`.`team_id`
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t2` ON `m`.`two_competitor_id` = `t2`.`team_id`
WHERE (`l`.`visibility` = 'visible' OR `l`.`visibility` IS NULL OR `t`.`visibility` = 'visible' OR `t`.`visibility` IS NULL)
  AND `one_competitor_type` = 'teams'
  AND `match_status` = 'reported' 
  AND (
    (`one_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) AND `one_result` = '') OR 
    (`two_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) AND `two_result` = '')
    )
ORDER BY match_date ASC
",
				$user_id,
				$user_id,
				$user_id,
				$user_id
			)
		);
	}
}

if ( ! function_exists( 'get_reported_matches' ) ) {
	/**
	 * Retrieves all matches reported by this user or this user's team.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $user_id The id for the user.
	 *
	 * @return mixed
	 */
	function get_reported_matches( $user_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  `m`.*, 
  IF(`l`.`name` IS NULL, `t`.`name`, `l`.`name`) AS `name`,
  IF(`l`.`competitor_type` IS NULL, `t`.`competitor_type`, `l`.`competitor_type`) AS `competitor_type`,
  `p1`.`display_name` AS `one_competitor_name`,
  `p2`.`display_name` AS `two_competitor_name`
FROM `{$wpdb->prefix}trn_matches` AS `m`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `m`.`competition_id` AND `m`.`competition_type` = 'ladders'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `t`.`tournament_id` = `m`.`competition_id` AND `m`.`competition_type` = 'tournaments'
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p1` ON `m`.`one_competitor_id` = `p1`.`user_id`
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS `p2` ON `m`.`two_competitor_id` = `p2`.`user_id`
WHERE (`l`.`visibility` = 'visible' OR `l`.`visibility` IS NULL OR `t`.`visibility` = 'visible' OR `t`.`visibility` IS NULL)
  AND `one_competitor_type` = 'players'
  AND `match_status` = 'reported' 
  AND ((`one_competitor_id` = %d AND `two_result` = '') OR (`two_competitor_id` = %d AND `one_result` = ''))
UNION
SELECT 
  `m`.*, 
  IF(`l`.`name` IS NULL, `t`.`name`, `l`.`name`) AS `name`,
  IF(`l`.`competitor_type` IS NULL, `t`.`competitor_type`, `l`.`competitor_type`) AS `competitor_type`,
  `t1`.`name` AS `one_competitor_name`,
  `t2`.`name` AS `two_competitor_name`
FROM `{$wpdb->prefix}trn_matches` AS `m`
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `m`.`competition_id` AND `m`.`competition_type` = 'ladders'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `t`.`tournament_id` = `m`.`competition_id` AND `m`.`competition_type` = 'tournaments'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t1` ON `m`.`one_competitor_id` = `t1`.`team_id`
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS `t2` ON `m`.`two_competitor_id` = `t2`.`team_id`
WHERE (`l`.`visibility` = 'visible' OR `l`.`visibility` IS NULL OR `t`.`visibility` = 'visible' OR `t`.`visibility` IS NULL)
  AND `one_competitor_type` = 'teams'
  AND `match_status` = 'reported' 
  AND (
    (`one_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) AND `two_result` = '') OR 
    (`two_competitor_id` IN (SELECT `team_id` FROM `{$wpdb->prefix}trn_teams_members` WHERE `user_id` = %d) AND `one_result` = '')
    )
ORDER BY match_date ASC
",
				$user_id,
				$user_id,
				$user_id,
				$user_id
			)
		);
	}
}
