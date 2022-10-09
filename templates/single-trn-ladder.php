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
$ladder = trn_the_ladder( $ladder );
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
<div class="trn-competition-header"<?php trn_header_banner_style( $ladder->banner_id, $ladder->game_id ); ?>>
	<div class="trn-competition-details">
		<h1 class="trn-competition-name"><?php echo esc_html( $ladder->name ); ?></h1>
		<span class="trn-competition-game"><?php echo esc_html( $ladder->game_name ); ?></span>
	</div>
	<ul class="trn-competition-list">
		<li class="trn-competition-list-item members">
			<?php /* translators: number of competitors. */ ?>
			<?php echo sprintf( esc_html( _n( '%s Competitor', '%s Competitors', intval( $ladder->competitors ), 'tournamatch' ) ), intval( $ladder->competitors ) ); ?>
		</li>
		<li class="trn-competition-list-item ranking">
			<?php echo esc_html( $ladder->ranking_mode_label ); ?>
		</li>
		<li class="trn-competition-list-item competitor-type">
			<?php if ( 'players' === $ladder->competitor_type ) : ?>
				<?php esc_html_e( 'Singles', 'tournamatch' ); ?>
			<?php else : ?>
				<?php /* translators: Opponent name vs opponent name. */ ?>
				<?php echo sprintf( esc_html__( 'Teams (%1$d vs %1$d)', 'tournamatch' ), intval( $ladder->team_size ) ); ?>
			<?php endif; ?>
		</li>
	</ul>
</div>
<?php

$views = array(
	'standings' => array(
		'heading' => __( 'Standings', 'tournamatch' ),
		'content' => function( $ladder ) {
			if ( 'active' !== $ladder->status ) {
				echo '<p>' . esc_html__( 'This ladder is no longer active.', 'tournamatch' ) . '</p>';
			} else {
				echo do_shortcode( '[trn-ladder-standings-list-table ladder_id="' . intval( $ladder->ladder_id ) . '"]' );
			}
		},
	),
	'rules'     => array(
		'heading' => __( 'Rules', 'tournamatch' ),
		'content' => function( $ladder ) {
			if ( strlen( $ladder->rules ) > 0 ) {
				echo wp_kses_post( stripslashes( $ladder->rules ) );
			} else {
				echo '<p class="trn-text-center">';
				esc_html_e( 'No rules to display.', 'tournamatch' );
				echo '</p>';
			}
		},
	),
	'matches'   => array(
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

if ( $can_report ) {
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
					<a class="trn-nav-link trn-confirm-action-link trn-leave-ladder-link"
							id="trn-leave-ladder-link"
							href="#leave"
							data-competitor-id="<?php echo intval( $competitor->ladder_entry_id ); ?>"
							data-confirm-title="<?php esc_html_e( 'Leave Ladder', 'tournamatch' ); ?>"
							data-confirm-message="<?php esc_html_e( 'Are you sure you want to leave this ladder?', 'tournamatch' ); ?>"
							data-modal-id="leave-ladder"
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
		'4.3.5',
		true
	);
	wp_localize_script( 'leave-ladder', 'trn_leave_ladder_options', $options );
	wp_enqueue_script( 'leave-ladder' );
}

/**
 * Filters an array of views for the single ladder template page.
 *
 * @since 4.1.0
 *
 * @param array $views {
 *          An associative array of tabbed views.
 *
 *          @param string|callable $heading The content or callable content of the header tab.
 *          @param string $href The url of the header tab.
 *          @param string|callable $content The content or callable content of the tabbed page.
 *      }
 * @param stdClass $ladder The data context item we are rendering a page for.
 */
$views = apply_filters( 'trn_single_ladder_views', $views, $ladder );

trn_single_template_tab_views( $views, $ladder );

trn_get_footer();

get_footer();
