<?php
/**
 * Defines the ladder top competitor widget.
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
 * Defines the ladder top competitor widget.
 *
 * @since      3.16.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Ladder_Top_Competitor extends \WP_Widget {

	/**
	 * Ladder_Top_Competitors constructor.
	 *
	 * @since 3.16.0
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'trn_ladder_top_competitors',
			'description' => esc_html__( 'Display the Top Competitors of any ladder', 'tournamatch' ),
		);

		parent::__construct( 'trn_ladder_top_competitors', esc_html__( 'Ladder Top Competitors', 'tournamatch' ), $widget_options );
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

		$ladder = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}trn_ladders` WHERE ladder_id = %d", $instance['ladder_id'] ), ARRAY_A );

		if ( 'players' === $ladder['competitor_type'] ) {
			$competitors = $wpdb->get_results(
				$wpdb->prepare(
					"
SELECT 
  le.points, 
  le.competitor_id, 
  'players.single' AS route, 
  'id' AS slug, 
  p.display_name AS `name`, 
  p.flag 
FROM `{$wpdb->prefix}trn_ladders_entries` AS le 
  LEFT JOIN `{$wpdb->prefix}trn_players_profiles` AS p ON le.competitor_id = p.user_id 
WHERE le.ladder_id = %d 
ORDER BY points DESC LIMIT %d",
					$instance['ladder_id'],
					$instance['count']
				),
				ARRAY_A
			);
		} else {
			$competitors = $wpdb->get_results(
				$wpdb->prepare(
					"
SELECT le.points, le.competitor_id AS competitor_id, 'teams.single' AS route, 'id' AS slug, t.name AS name, t.flag 
FROM `{$wpdb->prefix}trn_ladders_entries` AS le 
  LEFT JOIN `{$wpdb->prefix}trn_teams` AS t ON le.competitor_id = t.team_id 
WHERE le.ladder_id = %d 
ORDER BY points DESC LIMIT %d",
					$instance['ladder_id'],
					$instance['count']
				),
				ARRAY_A
			);
		}

		echo '<ul class="trn-ladder-top-competitor-widget">';
		foreach ( $competitors as $competitor ) {
			echo '<li class="trn-ladder-top-competitor-widget-competitor">';
			echo ' <img src="' . esc_url( plugins_url( 'tournamatch' ) . '/dist/images/flags/' . esc_html( $competitor['flag'] ) ) . '" title="' . esc_html( $competitor['flag'] ) . '" border="0">';
			echo ' <a href="' . esc_url( trn_route( $competitor['route'], array( $competitor['slug'] => $competitor['competitor_id'] ) ) ) . '">' . esc_html( $competitor['name'] ) . '</a>';
			echo ' <span class="trn-ladder-top-competitor-widget-points">' . intval( $competitor['points'] ) . '</span>';
			echo '</li>';
		}
		echo '</ul>';
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
		global $wpdb;

		$ladders = $wpdb->get_results( "SELECT `ladder_id` AS id, `name` FROM {$wpdb->prefix}trn_ladders", ARRAY_A );

		$title     = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'tournamatch' );
		$ladder_id = ! empty( $instance['ladder_id'] ) ? $instance['ladder_id'] : 0;
		$count     = ! empty( $instance['count'] ) ? $instance['count'] : 5;

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'tournamatch' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
					value="<?php echo esc_attr( $title ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'ladder_id' ) ); ?>"><?php esc_attr_e( 'Ladder:', 'tournamatch' ); ?></label>
			<?php if ( count( $ladders ) > 0 ) : ?>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ladder_id' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'ladder_id' ) ); ?>">
					<?php foreach ( $ladders as $ladder ) : ?>
						<option value="<?php echo intval( $ladder['id'] ); ?>" <?php echo ( intval( $ladder_id ) === intval( $ladder['id'] ) ) ? 'selected' : ''; ?>><?php echo esc_html( $ladder['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<?php esc_html_e( 'No ladders exist.', 'tournamatch' ); ?>
			<?php endif; ?>
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

		$instance['title']     = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['ladder_id'] = ( ! empty( $new_instance['ladder_id'] ) ) ? intval( $new_instance['ladder_id'] ) : '';
		$instance['count']     = ( ! empty( $new_instance['count'] ) ) ? intval( $new_instance['count'] ) : '';

		return $instance;
	}
}

add_action(
	'widgets_init',
	function () {
		register_widget( Ladder_Top_Competitor::class );
	}
);
