<?php
/**
 * The template that displays a single match.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$match_id = get_query_var( 'id' );

$match = trn_get_match( $match_id );
if ( is_null( $match ) ) {
	wp_safe_redirect( trn_route( 'matches.archive' ) );
	exit;
}

$competition = ( 'ladders' === $match->competition_type ) ? trn_get_ladder( $match->competition_id ) : trn_get_tournament( $match->competition_id );

// Get match entrants.
if ( 'players' === $competition->competitor_type ) {
	$one_row = trn_get_player( $match->one_competitor_id );
	$two_row = trn_get_player( $match->two_competitor_id );
} else {
	$one_row = trn_get_team( $match->one_competitor_id );
	$two_row = trn_get_team( $match->two_competitor_id );
}

$can_report  = false;
$can_delete  = ( 'ladders' === $match->competition_type ) && current_user_can( 'manage_tournamatch' );
$can_clear   = false;
$can_confirm = false;
$can_dispute = false;

get_header();

trn_get_header();
?>
<div class="trn-match-header"<?php trn_header_banner_style( $competition->banner_id, $competition->game_id ); ?>>
	<div class="trn-match-header-left-avatar">
		<?php trn_display_avatar( $match->one_competitor_id, $competition->competitor_type, $one_row->avatar, 'trn-match-header-avatar' ); ?>
	</div>
	<div class="trn-match-header-left-details">
		<h1 class="trn-match-competitor"><?php echo esc_html( $one_row->name ); ?></h1>
		<?php if ( 'confirmed' === $match->match_status ) : ?>
			<span class="trn-match-result">
					<?php
					if ( 'won' === $match->one_result ) {
						echo esc_html__( 'Winner', 'tournamatch' );
					} elseif ( 'draw' === $match->one_result ) {
						echo esc_html__( 'Draw', 'tournamatch' );
					} else {
						echo esc_html__( 'Loser', 'tournamatch' );
					}
					?>
				</span>
		<?php endif; ?>
	</div>
	<div class="trn-match-header-right-avatar">
		<?php trn_display_avatar( $match->two_competitor_id, $competition->competitor_type, $two_row->avatar, 'trn-match-header-avatar' ); ?>
	</div>
	<div class="trn-match-header-right-details">
		<h1 class="trn-match-competitor"><?php echo esc_html( $two_row->name ); ?></h1>
		<?php if ( 'confirmed' === $match->match_status ) : ?>
			<span class="trn-match-result">
					<?php
					if ( 'won' === $match->two_result ) {
						echo esc_html__( 'Winner', 'tournamatch' );
					} elseif ( 'draw' === $match->two_result ) {
						echo esc_html__( 'Draw', 'tournamatch' );
					} else {
						echo esc_html__( 'Loser', 'tournamatch' );
					}
					?>
				</span>
		<?php endif; ?>
	</div>
	<div class="trn-match-actions">
		<div class="trn-pull-right">
		<?php if ( $can_confirm ) : ?>
			<a class="trn-button" href="<?php trn_esc_route_e( 'matches.single.confirm', array( 'id' => $match->match_id ) ); ?>"><?php esc_html_e( 'Confirm', 'tournamatch' ); ?></a>
		<?php endif; ?>
			<?php if ( $can_dispute ) : ?>
				<?php echo do_shortcode( '[trn-dispute-match-button id="' . intval( $match->match_id ) . '"]' ); ?>
			<?php endif; ?>
			<?php if ( $can_report ) : ?>
				<a class="trn-button" href=""><?php esc_html_e( 'Report', 'tournamatch' ); ?></a>
			<?php endif; ?>
			<?php if ( $can_delete ) : ?>
				<a
						class="trn-button trn-button-danger trn-confirm-action-link trn-delete-match-action"
						href="#"
						data-match-id="<?php echo intval( $match->match_id ); ?>"
						data-confirm-title="<?php esc_html_e( 'Delete Match', 'tournamatch' ); ?>"
						data-confirm-message="<?php esc_html_e( 'Are you sure you want to delete this match?', 'tournamatch' ); ?>"
						data-modal-id="delete-match"
				><?php esc_html_e( 'Delete', 'tournamatch' ); ?></a>
			<?php endif; ?>
			<?php if ( $can_clear ) : ?>
				<a class="trn-button trn-button-danger" href="
				<?php
				trn_esc_route_e(
					'admin.tournaments.clear-match',
					[
						'id'       => $match->match_id,
						'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-matches' ),
					]
				);
				?>
				"><?php esc_html_e( 'Clear', 'tournamatch' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php

$views = array(
	'match_details' => array(
		'heading' => __( 'Details', 'tournamatch' ),
		'content' => function( $match ) use ( $one_row, $two_row, $competition ) {
			$competition_label = substr( $match->competition_type, 0, -1 );

			$description_list = array(
				$competition_label => array(
					'term'        => ucwords( $competition_label ),
					'description' => function( $match ) use ( $competition ) {
						$competition_route = ( 'ladders' === $match->competition_type ) ? 'ladders.single' : 'tournaments.single.brackets';

						echo '<a href="' . esc_url( trn_route( $competition_route, array( 'id' => $match->competition_id ) ) ) . '">' . esc_html( $competition->name ) . '</a>';
					},
				),
				'status'           => array(
					'term'        => __( 'Status', 'tournamatch' ),
					'description' => ucwords( $match->match_status ),
				),
			);

			if ( '0000-00-00 00:00:00' !== $match->match_date ) {
				$description_list = trn_array_merge_after_key(
					$description_list,
					'status',
					array(
						'date' => array(
							'term'        => __( 'Date', 'tournamatch' ),
							'description' => date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $match->match_date ) ) ),
						),
					),
					false,
					true
				);
			}

			if ( 'confirmed' === $match->match_status ) {
				if ( 'draw' === $match->one_result ) {
					$result = array(
						'result' => array(
							'term'        => __( 'Result', 'tournamatch' ),
							'description' => __( 'Draw', 'tournamatch' ),
						),
					);
				} else {
					$winner_callback = function ( $match ) use ( $one_row ) {
						echo '<a href="' . esc_url( trn_route( "{$match->one_competitor_type}.single", array( 'id' => $match->one_competitor_id ) ) ) . '">' . esc_html( $one_row->name ) . '</a>';
					};
					$loser_callback = function ( $match ) use ( $two_row ) {
						echo '<a href="' . esc_url( trn_route( "{$match->two_competitor_type}.single", array( 'id' => $match->two_competitor_id ) ) ) . '">' . esc_html( $two_row->name ) . '</a>';
					};

					if ( 'lost' === $match->one_result ) {
						list( $winner_callback, $loser_callback ) = array( $loser_callback, $winner_callback );
					}

					$result = array(
						'winner' => array(
							'term'        => __( 'Winner', 'tournamatch' ),
							'description' => $winner_callback,
						),
						'loser'  => array(
							'term'        => __( 'Loser', 'tournamatch' ),
							'description' => $loser_callback,
						),
					);
				}

				$description_list = trn_array_merge_after_key( $description_list, 'status', $result, true, true );
			}

			$description_list = apply_filters( 'trn_single_match_details_description_list', $description_list, $match );

			trn_single_template_description_list( $description_list, $match );
		},
	),
	'comments'      => array(
		'heading' => __( 'Comments', 'tournamatch' ),
		'content' => function( $match ) use ( $one_row, $two_row ) {
			?>
			<dl class="trn-dl">
				<dt class="trn-dt"><?php echo esc_html( $one_row->name ); ?>: </dt>
				<dd class="trn-dd"><?php echo esc_html( $match->one_comment ); ?></dd>
				<dt class="trn-dt"><?php echo esc_html( $two_row->name ); ?>: </dt>
				<dd class="trn-dd"><?php echo esc_html( $match->two_comment ); ?></dd>
			</dl>
			<?php
		},
	),
);

/**
 * Filters an array of views for the single match template page.
 *
 * @since 4.3.0
 *
 * @param array $views {
 *          An associative array of tabbed views.
 *
 *          @param string|callable $heading The content or callable content of the header tab.
 *          @param string $href The url of the header tab.
 *          @param string|callable $content The content or callable content of the tabbed page.
 *      }
 * @param stdClass $match The data context item we are rendering a page for.
 */
$views = apply_filters( 'trn_single_match_views', $views, $match );

trn_single_template_tab_views( $views, $match );

$options = array(
	'redirect_link' => trn_route( 'matches.archive' ),
);

wp_enqueue_script( 'trn-delete-match' );
wp_register_script( 'trn-match-details', plugins_url( '../dist/js/match-details.js', __FILE__ ), array( 'tournamatch' ), '3.11.0', true );
wp_localize_script( 'trn-match-details', 'trn_match_details_options', $options );
wp_enqueue_script( 'trn-match-details' );

trn_get_footer();

get_footer();
