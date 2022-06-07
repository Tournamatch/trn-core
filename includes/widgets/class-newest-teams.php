<?php
/**
 * Defines the newest teams widget.
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
 * Defines the newest teams widget.
 *
 * @since      3.16.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Newest_Teams extends \WP_Widget {

	/**
	 * Newest_Teams constructor.
	 *
	 * @since 3.16.0
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'trn_newest_teams',
			'description' => esc_html__( 'Display the newest teams on your website.', 'tournamatch' ),
		);

		parent::__construct( 'trn_newest_teams', esc_html__( 'Newest Teams', 'tournamatch' ), $widget_options );
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

		$newest_members = $wpdb->get_results( $wpdb->prepare( "SELECT t.team_id AS id, t.name AS name, t.flag AS flag, t.joined_date FROM {$wpdb->prefix}trn_teams AS t ORDER BY t.team_id DESC LIMIT %d", $instance['count'] ), ARRAY_A );

		echo '<ul class="trn-newest-teams-widget">';
		foreach ( $newest_members as $new_member ) {
			echo '<li class="trn-newest-teams-widget-team"><img class="trn-newest-teams-widget-team-flag" src="' . esc_url( plugins_url( 'tournamatch' ) . '/dist/images/flags/' . $new_member['flag'] ) . '" title="' . esc_html( $new_member['flag'] ) . '" border="0"> <a class="trn-newest-teams-widget-team-name" href="' . esc_url( trn_route( 'teams.single', array( 'id' => $new_member['id'] ) ) ) . '">' . esc_html( $new_member['name'] ) . '</a></li>';
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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'tournamatch' );
		$count = ! empty( $instance['count'] ) ? $instance['count'] : 5;

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'tournamatch' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_attr_e( 'Count:', 'tournamatch' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" value="<?php echo esc_attr( $count ); ?>">
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
	function() {
		register_widget( Newest_Teams::class );
	}
);
