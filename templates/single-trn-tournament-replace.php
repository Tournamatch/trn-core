<?php
/**
 * The template that displays the replace action for a single tournament.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_tournamatch' ) ) {
	wp_safe_redirect( trn_route( 'tournaments.archive' ) );
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$match_id = isset( $_GET['match_id'] ) ? intval( $_GET['match_id'] ) : null;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$competitor_id = isset( $_GET['competitor_id'] ) ? intval( $_GET['competitor_id'] ) : null;

$match       = trn_get_match( $match_id );
$tournament  = trn_get_tournament( $match->competition_id );
$opponent_id = ( intval( $match->one_competitor_id ) === $competitor_id ) ? $match->two_competitor_id : $match->one_competitor_id;
$opponent    = ( 'players' === $tournament->competitor_type ) ? trn_get_player( $opponent_id ) : trn_get_team( $opponent_id );
$competitors = trn_get_registered_competitors( $tournament->tournament_id );
$competitors = array_filter(
	$competitors,
	function( $competitor ) use ( $opponent_id ) {
		return ( intval( $opponent_id ) !== intval( $competitor->competitor_id ) );
	}
);

get_header();

trn_get_header();

?>
	<h1 class="mb-4"><?php esc_html_e( 'Select New Competitor', 'tournamatch' ); ?></h1>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<div class="form-group row">
			<label class="col-sm-4 col-lg-3 control-label"><?php esc_html_e( 'Tournament', 'tournamatch' ); ?>:</label>
			<div class="col-sm-6 col-lg-4">
				<p class="form-control-static"><?php echo esc_html( $tournament->name ); ?></p>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-4 col-lg-3 control-label"><?php esc_html_e( 'Opponent', 'tournamatch' ); ?>:</label>
			<div class="col-sm-6 col-lg-4">
				<?php if ( isset( $opponent ) ) : ?>
					<p class="form-control-static"><?php echo esc_html( $opponent->name ); ?> </p>
				<?php else : ?>
					<p class="form-control-static"><?php esc_html_e( 'Undecided', 'tournamatch' ); ?> </p>
				<?php endif; ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="new_competitor_id"
					class="col-sm-4 col-lg-3 control-label"><?php esc_html_e( 'Competitor', 'tournamatch' ); ?>:</label>
			<div class="col-sm-4">
				<select id="new_competitor_id" name="new_competitor_id" class="form-control">
					<?php foreach ( $competitors as $competitor ) : ?>
						<option value="<?php echo intval( $competitor->competitor_id ); ?>" <?php echo ( intval( $competitor->competitor_id ) === $competitor_id ) ? 'selected' : ''; ?>><?php echo esc_html( $competitor->competitor_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-offset-4 col-lg-offset-3 col-sm-6">
				<?php wp_nonce_field( 'tournamatch-replace-tournament-competitor' ); ?>
				<input type="hidden" name="match_id" value="<?php echo intval( $match_id ); ?>">
				<input type="hidden" name="competitor_id" value="<?php echo intval( $competitor_id ); ?>">
				<input type="hidden" name="action" value="trn-replace-tournament-competitor">
				<input type="submit" class="btn btn-default" value="Update">
			</div>
		</div>
	</form>
<?php

trn_get_footer();

get_footer();

