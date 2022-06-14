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

$competition     = ( 'ladders' === $match->competition_type ) ? trn_get_ladder( $match->competition_id ) : trn_get_tournament( $match->competition_id );
$competitor_type = $competition->competitor_type;
$route           = "{$competition->competitor_type}.single";
$slug            = 'id';

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

$match = array(
	'competition_type' => $match->competition_type,
	'competition_id'   => $match->competition_id,
	'competition_name' => $competition->name,
	'competitor_type'  => $competitor_type,
	'match_id'         => $match->match_id,
	'match_date'       => $match->match_date,
	'match_status'     => $match->match_status,
	'one_id'           => $match->one_competitor_id,
	'one_name'         => is_null( $one_row ) ? trn_get_option( 'tournament_undecided_display' ) : $one_row->name,
	'one_avatar'       => is_null( $one_row ) ? 'blank.gif' : $one_row->avatar,
	'one_comment'      => $match->one_comment,
	'one_result'       => $match->one_result,
	'two_id'           => $match->two_competitor_id,
	'two_name'         => is_null( $two_row ) ? trn_get_option( 'tournament_undecided_display' ) : $two_row->name,
	'two_avatar'       => is_null( $two_row ) ? 'blank.gif' : $two_row->avatar,
	'two_comment'      => $match->two_comment,
	'two_result'       => $match->two_result,
	'route'            => $route,
	'slug'             => $slug,
);

$one_name = '<a href="' . esc_url( trn_route( $match['route'], array( $match['slug'] => $match['one_id'] ) ) ) . '">' . esc_html( $match['one_name'] ) . '</a>';
$two_name = '<a href="' . esc_url( trn_route( $match['route'], array( $match['slug'] => $match['two_id'] ) ) ) . '">' . esc_html( $match['two_name'] ) . '</a>';

get_header();

trn_get_header();
?>
<div class="row" id="trn-match-details">
	<div class="col-sm-6">
		<h3 style="display: flex">
			<div style="flex: 1; display: flex; justify-content: center; align-items: center">
				<div style="display: inline-block; text-align: center"><?php trn_display_avatar( $match['one_id'], $competitor_type, $match['one_avatar'], 'match-avatar' ); ?><br><span style="font-size: .75em"><?php echo esc_html( $match['one_name'] ); ?></span>

				</div>
			</div>
			<span style="margin: auto 0"> vs. </span>
			<div style="flex:1; display: flex; justify-content: center; align-items: center">
				<div style="display: inline-block; text-align: center"><?php trn_display_avatar( $match['two_id'], $competitor_type, $match['two_avatar'], 'match-avatar' ); ?><br><span style="font-size: .75em"><?php echo esc_html( $match['two_name'] ); ?></span>
				</div>
			</div>
		</h3>
	</div>
	<div class="col-sm-6">
		<h3><?php esc_html_e( 'Match Details', 'tournamatch' ); ?></h3>
		<dl class="row">
			<dt class="col-sm-6"><?php echo esc_html( substr( ucwords( $match['competition_type'] ), 0, -1 ) ); ?>: </dt>
			<dd class="col-sm-6">
				<?php if ( 'ladders' === $match['competition_type'] ) : ?>
					<a href="<?php trn_esc_route_e( 'ladders.single', array( 'id' => $match['competition_id'] ) ); ?>"><?php echo esc_html( $match['competition_name'] ); ?></a>
				<?php else : ?>
					<a href="<?php trn_esc_route_e( 'tournaments.single.brackets', array( 'id' => $match['competition_id'] ) ); ?>"><?php echo esc_html( $match['competition_name'] ); ?></a>
				<?php endif; ?>
			</dd>
			<dt class="col-sm-6"><?php esc_html_e( 'Status', 'tournamatch' ); ?>: </dt>
			<dd class="col-sm-6" id="trn_match_status"><?php echo esc_html( ucwords( $match['match_status'] ) ); ?></dd>
			<dt class="col-sm-6"><?php esc_html_e( 'Date', 'tournamatch' ); ?>: </dt>
			<dd class="col-sm-6"><?php echo ( ( '0000-00-00 00:00:00' === $match['match_date'] ) ? '&nbsp;' : esc_html( date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $match['match_date'] ) ) ) ) ); ?></dd>
		</dl>
	</div>
</div>
<div class="row">
	<div class="col-sm-6">
		<h3><?php esc_html_e( 'Result', 'tournamatch' ); ?></h3>
		<dl class="row">
			<?php if ( 'draw' === $match['one_result'] ) : ?>
				<dt class="col-sm-6"><?php esc_html_e( 'Result', 'tournamatch' ); ?>: </dt>
				<dd class="col-sm-6"><?php esc_html_e( 'Draw', 'tournamatch' ); ?></dd>
			<?php elseif ( 'confirmed' !== $match['match_status'] ) : ?>
				<dt class="col-sm-6"><?php esc_html_e( 'Winner', 'tournamatch' ); ?>: </dt>
				<dd class="col-sm-6"></dd>
				<dt class="col-sm-6"><?php esc_html_e( 'Loser', 'tournamatch' ); ?>: </dt>
				<dd class="col-sm-6"></dd>
			<?php else : ?>
				<dt class="col-sm-6"><?php esc_html_e( 'Winner', 'tournamatch' ); ?>: </dt>
				<dd class="col-sm-6" id="trn_match_winner">
					<?php
					if ( 'won' === $match['one_result'] ) {
						echo wp_kses_data( $one_name );
					} else {
						echo wp_kses_data( $two_name );
					}
					?>
				</dd>
				<dt class="col-sm-6"><?php esc_html_e( 'Loser', 'tournamatch' ); ?>: </dt>
				<dd class="col-sm-6" id="trn_match_loser">
					<?php
					if ( 'lost' === $match['one_result'] ) {
						echo wp_kses_data( $one_name );
					} else {
						echo wp_kses_data( $two_name );
					}
					?>
				</dd>
			<?php endif ?>
		</dl>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<h3><?php esc_html_e( 'Comments', 'tournamatch' ); ?></h3>
		<dl>
			<dt><?php echo esc_html( $match['one_name'] ); ?>: </dt>
			<dd><?php echo esc_html( $match['one_comment'] ); ?></dd>
			<dt><?php echo esc_html( $match['two_name'] ); ?>: </dt>
			<dd><?php echo esc_html( $match['two_comment'] ); ?></dd>
		</dl>
	</div>
</div>
<div class="pull-right">
	<?php if ( $can_confirm ) : ?>
		<a class="btn btn-primary" href="<?php trn_esc_route_e( 'matches.single.confirm', array( 'id' => $match['match_id'] ) ); ?>"><?php esc_html_e( 'Confirm', 'tournamatch' ); ?></a>
	<?php endif; ?>
	<?php if ( $can_dispute ) : ?>
		<?php echo do_shortcode( '[trn-dispute-match-button id="' . intval( $match['match_id'] ) . '"]' ); ?>
	<?php endif; ?>
	<?php if ( $can_report ) : ?>
		<a class="btn btn-primary" href=""><?php esc_html_e( 'Report', 'tournamatch' ); ?></a>
	<?php endif; ?>
	<?php if ( $can_delete ) : ?>
		<a class="btn btn-danger trn-confirm-action-link trn-delete-match-action" href="#" data-match-id="<?php echo intval( $match['match_id'] ); ?>" data-confirm-title="<?php esc_html_e( 'Delete Match', 'tournamatch' ); ?>" data-confirm-message="<?php esc_html_e( 'Are you sure you want to delete this match?', 'tournamatch' ); ?>"><?php esc_html_e( 'Delete', 'tournamatch' ); ?></a>
	<?php endif; ?>
	<?php if ( $can_clear ) : ?>
		<a class="btn btn-danger" href="
		<?php
		trn_esc_route_e(
			'admin.tournaments.clear-match',
			[
				'id'       => $match['match_id'],
				'_wpnonce' => wp_create_nonce( 'tournamatch-bulk-matches' ),
			]
		);
		?>
										"><?php esc_html_e( 'Clear', 'tournamatch' ); ?></a>
	<?php endif; ?>
</div>
<?php

$options = array(
	'redirect_link' => trn_route( 'matches.archive' ),
);

wp_enqueue_script( 'trn-delete-match' );
wp_register_script( 'trn-match-details', plugins_url( '../dist/js/match-details.js', __FILE__ ), array( 'tournamatch' ), '3.11.0', true );
wp_localize_script( 'trn-match-details', 'trn_match_details_options', $options );
wp_enqueue_script( 'trn-match-details' );

trn_get_footer();

get_footer();
