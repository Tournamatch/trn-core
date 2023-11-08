<?php
/**
 * The template that displays the register action for a single tournament.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$tournament_id = get_query_var( 'id' );

$tournament = trn_get_tournament( $tournament_id );
if ( is_null( $tournament ) ) {
	wp_safe_redirect( trn_route( 'tournaments.archive' ) );
	exit;
}

$one_competitor_per_tournament_rule = new \Tournamatch\Rules\One_Competitor_Per_Tournament( $tournament->tournament_id, get_current_user_id() );
$can_register                       = $one_competitor_per_tournament_rule->passes();

$competitor_type = $tournament->competitor_type;
if ( 'teams' === $tournament->competitor_type ) {
	$teams = trn_get_user_owned_teams( get_current_user_id() );
}

get_header();

trn_get_header();

?>
<h1 class="trn-mb-4"><?php esc_html_e( 'Register', 'tournamatch' ); ?></h1>
<?php if ( ! $can_register ) : ?>
	<div class="trn-alert trn-alert-info">
		<?php esc_html_e( 'You have already registered for this tournament.', 'tournamatch' ); ?>
	</div>
<?php else : ?>
	<div id="trn-tournament-join-response"></div>
	<form id="trn-tournament-join-form" class="form-horizontal" action="#" method="post">
		<div class="trn-form-group">
			<label class="trn-col-sm-3" for="tournament_id"><?php esc_html_e( 'Tournament', 'tournamatch' ); ?>:</label>
			<div class="trn-col-sm-4">
				<p class="trn-form-control-static"><?php echo esc_html( $tournament->name ); ?></p>
				<input type="hidden" name="tournament_id" id="tournament_id" value="<?php echo intval( $tournament->tournament_id ); ?>">
			</div>
		</div>
		<?php if ( trn_is_plugin_active( 'trn-mycred' ) ) : ?>
			<?php if ( 0 < intval( $tournament->mycred_entry_fee ) ) : ?>
				<div class="trn-form-group">
					<label class="trn-col-sm-3" for="mycred_entry_fee"><?php esc_html_e( 'Entry Fee', 'tournamatch' ); ?>:</label>
					<div class="trn-col-sm-4">
						<p class="trn-form-control-static">
							<?php /* translators: An integer number of tokens. */ ?>
							<?php echo sprintf( esc_html__( '%d Tokens', 'tournamatch' ), intval( $tournament->mycred_entry_fee ) ); ?>
						</p>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( 'teams' === $tournament->competitor_type ) : ?>
			<div class="trn-form-group">
				<label class="trn-col-sm-3" for="competitor_id"><?php esc_html_e( 'Team', 'tournamatch' ); ?>:</label>
				<?php if ( 0 < count( $teams ) ) : ?>
					<div class="trn-col-sm-4">
						<select id="competitor_id" name="competitor_id" class="trn-form-control">
							<?php foreach ( $teams as $team ) : ?>
								<option value="<?php echo intval( $team->team_id ); ?>"><?php echo esc_html( $team->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php else : ?>
					<div class="trn-col-sm-12">
						<?php /* translators: Opening and closing anchor tags. */ ?>
						<p><?php esc_html_e( 'This is a teams tournament and you do not currently own any teams.', 'tournamatch' ); ?> <?php printf( esc_html__( 'You may create one %1$shere%2$s.', 'tournamatch' ), '<a href="' . esc_url( trn_route( 'teams.single.create' ) ) . '">', '</a>' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( 0 < strlen( $tournament->rules ) ) : ?>
			<div class="trn-form-group">
				<label for="rules" class="trn-col-sm-3"><?php esc_html_e( 'Rules', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-6">
					<p><?php echo wp_kses_post( $tournament->rules ); ?></p>
				</div>
			</div>
		<?php endif; ?>
		<div class="trn-form-group">
			<div class="trn-col-sm-offset-3 trn-col-sm-9">
				<?php if ( 'players' === $tournament->competitor_type ) : ?>
					<input type="hidden" name="competitor_id" id="competitor_id" value="<?php echo intval( get_current_user_id() ); ?>">
				<?php endif; ?>
				<input type="hidden" name="competitor_type" id="competitor_type" value="<?php echo esc_html( $competitor_type ); ?>">
				<input type="submit" class="trn-button" id="trn-register-button" value="<?php esc_html_e( 'Register', 'tournamatch' ); ?>" <?php echo ( isset( $teams ) && ( 0 === count( $teams ) ) ) ? 'disabled' : ''; ?>>
			</div>
		</div>
	</form>
	<?php
endif;

$options = array(
	'api_url'       => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'    => wp_create_nonce( 'wp_rest' ),
	'redirect_link' => trn_route( 'tournaments.single.registered', [ 'id' => $tournament_id ] ),
	'language'      => array(
		'success'  => esc_html__( 'Success', 'tournamatch' ),
		'failure'  => esc_html__( 'Error', 'tournamatch' ),
		'petition' => esc_html__( 'Your request has been recorded. You will be registered after an admin approves your request.', 'tournamatch' ),
	),
);

wp_register_script( 'trn-tournament-register', plugins_url( '../dist/js/tournament-register.js', __FILE__ ), array( 'tournamatch' ), '3.28.0', true );
wp_localize_script( 'trn-tournament-register', 'trn_tournament_register_options', $options );
wp_enqueue_script( 'trn-tournament-register' );

trn_get_footer();

get_footer();
