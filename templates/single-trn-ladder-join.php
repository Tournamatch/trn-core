<?php
/**
 * The template that displays the join action for a single ladder.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$ladder_id = get_query_var( 'id' );

$ladder = trn_get_ladder( $ladder_id );

if ( is_null( $ladder ) ) {
	wp_safe_redirect( wp_login_url( trn_route( 'ladders.single.join' ) ) );
	exit;
}


$one_competitor_per_ladder_rule = new \Tournamatch\Rules\One_Competitor_Per_Ladder( $ladder->ladder_id, get_current_user_id() );
$can_join                       = $one_competitor_per_ladder_rule->passes();


$competitor_type = $ladder->competitor_type;
if ( 'teams' === $ladder->competitor_type ) {
	$teams = trn_get_user_owned_teams( get_current_user_id() );
}

get_header();

trn_get_header();

?>
<h1 class="trn-mb-4"><?php esc_html_e( 'Join Ladder', 'tournamatch' ); ?></h1>
<?php if ( ! $can_join ) : ?>
	<div class="alert alert-info">
		<?php esc_html_e( 'You are already participating on this ladder.', 'tournamatch' ); ?>
	</div>
<?php else : ?>
	<div id="trn-ladder-join-response"></div>
	<form id="trn-ladder-join-form" class="form-horizontal" action="#" method="post">
		<div class="trn-form-group">
			<label class="trn-col-sm-3" for="ladder_id"><?php esc_html_e( 'Ladder', 'tournamatch' ); ?>:</label>
			<div class="trn-col-sm-4">
				<p class="trn-form-control-static"><?php echo esc_html( $ladder->name ); ?></p>
				<input type="hidden" name="ladder_id" id="ladder_id" value="<?php echo intval( $ladder->ladder_id ); ?>">
			</div>
		</div>
		<?php if ( 'teams' === $ladder->competitor_type ) : ?>
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
					<p><?php esc_html_e( 'This is a teams ladder and you do not currently own any teams.', 'tournamatch' ); ?> <?php printf( esc_html__( 'You may create one %1$shere%2$s.', 'tournamatch' ), '<a href="' . esc_url( trn_route( 'teams.single.create' ) ) . '">', '</a>' ); ?></p>
				</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( 0 < strlen( $ladder->rules ) ) : ?>
			<div class="trn-form-group">
				<label for="rules" class="trn-col-sm-3"><?php esc_html_e( 'Rules', 'tournamatch' ); ?>:</label>
				<div class="trn-col-sm-6">
					<p><?php echo wp_kses_post( $ladder->rules ); ?></p>
				</div>
			</div>
		<?php endif; ?>
		<div class="trn-form-group">
			<div class="trn-col-sm-offset-3 trn-col-sm-9">
				<?php if ( 'players' === $ladder->competitor_type ) : ?>
					<input type="hidden" name="competitor_id" id="competitor_id" value="<?php echo intval( get_current_user_id() ); ?>">
				<?php endif; ?>
				<input type="hidden" name="competitor_type" id="competitor_type" value="<?php echo esc_html( $competitor_type ); ?>">
				<input type="submit" class="trn-button" id="trn-join-button" value="<?php esc_html_e( 'Join', 'tournamatch' ); ?>" <?php echo ( isset( $teams ) && ( 0 === count( $teams ) ) ) ? 'disabled' : ''; ?>>
			</div>
		</div>
	</form>
	<?php
endif;

$options = array(
	'api_url'       => site_url( 'wp-json/tournamatch/v1/' ),
	'rest_nonce'    => wp_create_nonce( 'wp_rest' ),
	'redirect_link' => trn_route( 'ladders.single.standings', [ 'id' => $ladder_id ] ),
	'language'      => array(
		'success'  => esc_html__( 'Success', 'tournamatch' ),
		'failure'  => esc_html__( 'Error', 'tournamatch' ),
		'petition' => esc_html__( 'Your request has been recorded. You will appear on the ladder after an admin approves your request.', 'tournamatch' ),
	),
);

wp_register_script( 'trn-ladder-join', plugins_url( '../dist/js/ladder-join.js', __FILE__ ), array( 'tournamatch' ), '3.28.0', true );
wp_localize_script( 'trn-ladder-join', 'trn_ladder_join_options', $options );
wp_enqueue_script( 'trn-ladder-join' );

trn_get_footer();

get_footer();
