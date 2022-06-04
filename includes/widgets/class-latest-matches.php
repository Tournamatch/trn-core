<?php
/**
 * Defines the latest matches widget.
 *
 * @link       https://www.tournamatch.com
 * @since      3.16.0
 *
 * @package    Tournamatch
 */

namespace Tournamatch\Widgets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the latest matches widget.
 *
 * @since      3.16.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Latest_Matches extends \WP_Widget {

	/**
	 * Latest_Matches constructor.
	 *
	 * @since 3.16.0
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'trn_latest_matches',
			'description' => esc_html__( 'Display the latest matches.', 'tournamatch' ),
		);

		parent::__construct( 'trn_latest_matches', esc_html__( 'Latest Matches', 'tournamatch' ), $widget_options );
	}

	/**
	 * Displays the widget.
	 *
	 * @since 3.16.0
	 *
	 * @param array $args Arguments for this widget.
	 * @param array $instance Instance of this widget.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		$latest_matches = $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT 
  m.match_id AS match_id, 
  m.competition_id, 
  m.competition_type, 
  IF(l.name IS NULL, t.name, l.name) AS competition_name,
  IF(m.competition_type = 'ladders', 'ladders.single.standings', 'tournaments.single.brackets') AS competition_route,
  m.match_status,
  m.one_competitor_id AS one_id, 
  m.two_competitor_id AS two_id, 
  m.match_date, 
  m.match_status, 
  m.one_result AS one_result, 
  m.two_result AS two_result, 
  IF(m.one_competitor_type = 'players', 'players.single', 'teams.single') AS profile_route,
  IF(m.one_competitor_type = 'players', 'id', 'id') AS profile_slug,
  IF(t1.name IS NULL, p1.display_name, t1.tag) AS one_name,   
  IF(t2.name IS NULL, p2.display_name, t2.tag) AS two_name
FROM `{$wpdb->prefix}trn_matches` AS m
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS t1 ON t1.team_id = m.one_competitor_id AND m.one_competitor_type = 'teams'
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS t2 ON t2.team_id = m.two_competitor_id AND m.two_competitor_type = 'teams'
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS p1 ON p1.user_id = m.one_competitor_id AND m.one_competitor_type = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS p2 ON p2.user_id = m.two_competitor_id AND m.two_competitor_type = 'players'
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS l ON l.ladder_id = m.competition_id AND m.competition_type = 'ladders'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS t ON t.tournament_id = m.competition_id AND m.competition_type = 'tournaments'
WHERE m.match_status = 'confirmed' AND t.tournament_id IS NULL
ORDER BY m.match_date DESC LIMIT %d",
				$instance['count']
			),
			ARRAY_A
		);

		?>
		<ul class="trn-latest-matches-widget">
			<?php foreach ( $latest_matches as $latest_match ) : ?>
				<li class="trn-latest-matches-widget-match">
				<span class="trn-latest-matches-widget-match-result">
					<?php echo esc_html( date( 'M j', strtotime( $latest_match['match_date'] ) ) ); ?> &nbsp; &nbsp; <a
							href="<?php echo esc_url( trn_route( $latest_match['profile_route'], array( $latest_match['profile_slug'] => $latest_match['one_id'] ) ) ); ?>"><?php echo esc_html( $latest_match['one_name'] ); ?></a> - <a
							href="<?php echo esc_url( trn_route( $latest_match['profile_route'], array( $latest_match['profile_slug'] => $latest_match['two_id'] ) ) ); ?>"><?php echo esc_html( $latest_match['two_name'] ); ?></a>
				</span>
					<span class="trn-latest-matches-widget-match-link">
						<a href="<?php trn_esc_route_e( 'matches.single', array( 'id' => $latest_match['match_id'] ) ); ?>"><i
									class="fa fa-info"></i></a>
				</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'tournamatch' );
		$count = ! empty( $instance['count'] ) ? $instance['count'] : 5;

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'tournamatch' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
					value="<?php echo esc_attr( $title ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_attr_e( 'Count:', 'tournamatch' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number"
					value="<?php echo esc_attr( $count ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['count'] = ( ! empty( $new_instance['count'] ) ) ? intval( $new_instance['count'] ) : '';

		return $instance;
	}
}

add_action(
	'widgets_init',
	function () {
		register_widget( Latest_Matches::class );
	}
);
