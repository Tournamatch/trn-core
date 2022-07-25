<?php
/**
 * The template for displaying a competitor's scheduled matches.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

$scheduled_matches = isset( $args['scheduled_matches'] ) ? $args['scheduled_matches'] : array();

?>

<table class="trn-table trn-table-striped trn-scheduled-matches-table" id="scheduled-matches-table">
	<tr>
		<th class="trn-scheduled-matches-table-event"><?php esc_html_e( 'Event', 'tournamatch' ); ?></th>
		<th class="trn-scheduled-matches-table-name"><?php esc_html_e( 'Name', 'tournamatch' ); ?></th>
		<th class="trn-scheduled-matches-table-competitors"><?php esc_html_e( 'Competitors', 'tournamatch' ); ?></th>
		<th class="trn-scheduled-matches-table-date"><?php esc_html_e( 'Scheduled', 'tournamatch' ); ?></th>
		<th class="trn-scheduled-matches-table-action"></th>
	</tr>
	<!--<template id="trn-scheduled-matches-table-row-template">-->
	<?php foreach ( $scheduled_matches as $scheduled_match ) : ?>
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
				<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $scheduled_match->match_date ) ) ) ); ?>
			</td>
			<td class="trn-scheduled-matches-table-action">
				<a class="trn-button trn-button-sm" href="<?php trn_esc_route_e( 'matches.single.report', array( 'id' => $scheduled_match->match_id ) ); ?>"><?php esc_html_e( 'Report', 'tournamatch' ); ?></a>
			</td>
		</tr>
	<?php endforeach; ?>
	<!--</template>-->
</table>
