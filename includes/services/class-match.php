<?php
/**
 * Defines the business service for managing matches.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Services;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the business service for managing matches.
 *
 * @since      3.11.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Match {

	/**
	 * Reports a match with the given result.
	 *
	 * @since 3.11.0
	 *
	 * @param array $competition An array of competition data.
	 * @param array $match An array of match data.
	 *
	 * @return int
	 */
	public function report( $competition, $match ) {
		global $wpdb;

		$data = array(
			'competition_id'      => $competition['id'],
			'competition_type'    => 'ladders',
			'one_competitor_id'   => $match['one_id'],
			'one_competitor_type' => $competition['competitor_type'],
			'one_ip'              => '',
			'one_result'          => $match['one_result'],
			'one_comment'         => $match['one_comment'],
			'two_competitor_id'   => $match['two_id'],
			'two_competitor_type' => $competition['competitor_type'],
			'two_ip'              => '',
			'two_result'          => $match['two_result'],
			'two_comment'         => $match['two_comment'],
			'match_date'          => $match['match_date'],
			'match_status'        => 'reported',
		);

		// insert new match.
		$wpdb->insert( $wpdb->prefix . 'trn_matches', $data );
		return $wpdb->insert_id;
	}

	/**
	 * Confirms a match.
	 *
	 * @since 3.11.0
	 *
	 * @param array $match An array of match data.
	 */
	public function confirm( $match ) {
		global $wpdb;

		$id      = isset( $match['id'] ) ? intval( $match['id'] ) : 0;
		$comment = isset( $match['comment'] ) ? esc_html( $match['comment'] ) : esc_html__( 'Admin confirmed', 'tournamatch' );

		$row             = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $id ), ARRAY_A );
		$competitor_type = $row['one_competitor_type'];

		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		// determine the columns to update.
		if ( strlen( $row['one_result'] ) === 0 ) {
			$confirm_result_column  = 'one_result';
			$confirm_comment_column = 'one_comment';
			$confirm_ip_column      = 'one_ip';
		} else {
			$confirm_result_column  = 'two_result';
			$confirm_comment_column = 'two_comment';
			$confirm_ip_column      = 'two_ip';
		}

		// determine the id of the winner.
		if ( ( 'won' === $row['one_result'] ) || ( 'lost' === $row['two_result'] ) ) {
			$winner_id = $row['one_competitor_id'];
			$loser_id  = $row['two_competitor_id'];
		} elseif ( ( 'draw' === $row['one_result'] ) || ( 'draw' === $row['two_result'] ) ) {
			$winner_id = $row['one_competitor_id'];
			$loser_id  = $row['two_competitor_id'];
		} else {
			$winner_id = $row['two_competitor_id'];
			$loser_id  = $row['one_competitor_id'];
		}

		// determine the result of the winner.
		if ( ( 'won' === $row['one_result'] ) || ( 'won' === $row['two_result'] ) ) {
			$confirm_result = 'lost';
		} elseif ( ( 'draw' === $row['one_result'] ) || ( 'draw' === $row['two_result'] ) ) {
			$confirm_result = 'draw';
		} else {
			$confirm_result = 'won';
		}

		$data = array(
			$confirm_result_column  => $confirm_result,
			$confirm_ip_column      => $ip,
			$confirm_comment_column => $comment,
			'match_status'          => 'confirmed',
		);

		$wpdb->update( $wpdb->prefix . 'trn_matches', $data, array( 'match_id' => $id ) );

		if ( 'ladders' === $row['competition_type'] ) {
			$updated_match = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_matches` WHERE `match_id` = %d", $id ), ARRAY_A );

			$arguments = array(
				$row['one_competitor_id'] => $updated_match['one_result'],
				$row['two_competitor_id'] => $updated_match['two_result'],
			);
			update_ladder( $row['competition_id'], $arguments );
		} else {
			update_tournament(
				$row['competition_id'],
				array(
					'match_id'  => $id,
					'winner_id' => $winner_id,
				)
			);
		}

		// Update career results.
		if ( 'draw' === $row['one_result'] ) {
			update_career_draws( $winner_id, $competitor_type );
			update_career_draws( $loser_id, $competitor_type );
		} else {
			update_career_wins( $winner_id, $competitor_type );
			update_career_losses( $loser_id, $competitor_type );
		}
	}

}
