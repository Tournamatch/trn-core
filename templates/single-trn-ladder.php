<?php
/**
 * The template for displaying a single ladder.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 */

// Exit if access directly.
defined( 'ABSPATH' ) || exit;

$ladder_id = get_query_var( 'id' );

$ladder = trn_get_ladder( $ladder_id );
if ( is_null( $ladder ) ) {
	wp_safe_redirect( trn_route( 'ladders.archive' ) );
	exit;
}

$competitor_type = $ladder->competitor_type;
$can_join        = is_user_logged_in();
$can_report      = false;
$can_leave       = false;
$competitor      = null;

if ( is_user_logged_in() ) {
	$competitor = trn_get_user_ladder( get_current_user_id(), $ladder_id );

	if ( trn_get_option( 'can_leave_ladder' ) ) {
		$can_leave = ! is_null( $competitor );
	}

	if ( ! is_null( $competitor ) ) {
		$can_report = true;
		$can_join   = false;
	}
}

$image_directory    = trn_upload_url() . '/images';
$game_avatar_source = $image_directory . '/games/' . ( is_null( $ladder->game_thumbnail ) ? 'blank.gif' : $ladder->game_thumbnail );

get_header();

trn_get_header();

?>
	<div class="row mb-sm">
		<div class="col-sm-2 col-xs-4 text-center">
			<img class="game-thumbnail" src="<?php echo esc_url( $game_avatar_source ); ?>" alt="">
		</div>
		<div class="col-sm-4 col-xs-8">
			<h3 id="trn-ladder-title"><?php echo esc_html( $ladder->name ); ?><br>
				<small><?php echo esc_html( $ladder->game_name ); ?></small>
			</h3>
		</div>
	</div>
<?php

$views = array(
	'standings'    => array(
		'heading' => __( 'Standings', 'tournamatch' ),
		'content' => function( $ladder ) {
			if ( 'active' !== $ladder->status ) {
				echo '<p>' . esc_html__( 'This ladder is no longer active.', 'tournamatch' ) . '</p>';
			} else {
				echo do_shortcode( '[trn-ladder-standings-list-table ladder_id="' . intval( $ladder->ladder_id ) . '"]' );
			}
		},
	),
	'rules'      => array(
		'heading' => __( 'Rules', 'tournamatch' ),
		'content' => function( $ladder ) {
			if ( strlen( $ladder->rules ) > 0 ) {
				echo wp_kses_post( stripslashes( $ladder->rules ) );
			} else {
				echo '<p class="text-center">';
				esc_html_e( 'No rules to display.', 'tournamatch' );
				echo '</p>';
			}
		},
	),
	'matches'    => array(
		'heading' => __( 'Matches', 'tournamatch' ),
		'content' => function( $ladder ) {
			if ( 'active' !== $ladder->status ) {
				echo '<p>' . esc_html__( 'This ladder is no longer active.', 'tournamatch' ) . '</p>';
			} else {
				echo do_shortcode( '[trn-ladder-matches-list-table ladder_id="' . intval( $ladder->ladder_id ) . '"]' );
			}
		},
	),
);

if ( $can_join ) {
	$views = array_merge(
		$views,
		array(
			'join' => array(
				'heading' => __( 'Join', 'tournamatch' ),
				'href'    => trn_route( 'ladders.single.join', array( 'id' => $ladder->ladder_id ) ),
			),
		)
	);
}

if ( $can_join ) {
	$views = array_merge(
		$views,
		array(
			'report' => array(
				'heading' => __( 'Report', 'tournamatch' ),
				'href'    => trn_route( 'matches.single.create', array( 'ladder_id' => $ladder->ladder_id ) ),
			),
		)
	);
}

if ( ( $can_report ) && ( 'enabled' === $ladder->direct_challenges ) ) {
	$views = array_merge(
		$views,
		array(
			'challenge' => array(
				'heading' => __( 'Challenge', 'tournamatch' ),
				'href'    => trn_route( 'challenges.single.create', array( 'ladder_id' => $ladder->ladder_id ) ),
			),
		)
	);
}

if ( $can_leave ) {
	$views = array_merge(
		$views,
		array(
			'leave' => array(
				'heading' => function( $ladder ) use ( $competitor ) {
					?>
					<a class="tournamatch-nav-link trn-confirm-action-link trn-leave-ladder-link"
							id="trn-leave-ladder-link"
							href="#leave"
							data-competitor-id="<?php echo intval( $competitor->ladder_entry_id ); ?>"
							data-confirm-title="<?php esc_html_e( 'Leave Ladder', 'tournamatch' ); ?>"
							data-confirm-message="<?php esc_html_e( 'Are you sure you want to leave this ladder?', 'tournamatch' ); ?>"
					>
						<?php esc_html_e( 'Leave', 'tournamatch' ); ?>
					</a>
					<?php
				},
			),
		)
	);

	$options = array(
		'api_url'    => site_url( 'wp-json/tournamatch/v1/' ),
		'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		'language'   => array(
			'failure' => esc_html__( 'Error', 'tournamatch' ),
		),
	);

	wp_register_script(
		'leave-ladder',
		plugins_url( '../dist/js/leave-ladder.js', __FILE__ ),
		array(
			'tournamatch',
		),
		'3.26.0',
		true
	);
	wp_localize_script( 'leave-ladder', 'trn_leave_ladder_options', $options );
	wp_enqueue_script( 'leave-ladder' );
}

$views = apply_filters( 'trn_single_ladder_views', $views, $ladder );

trn_single_template_tab_views( $views, $ladder );

trn_get_footer();

get_footer();
