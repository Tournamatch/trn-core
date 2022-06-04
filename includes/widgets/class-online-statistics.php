<?php
/**
 * Defines the online statistics widget.
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
 * Defines the online statistics widget.
 *
 * @since      3.16.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Online_Statistics extends \WP_Widget {

	/**
	 * Online_Statistics constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'trn_online_statistics_class',
			'description' => esc_html__( 'Display online statistics', 'tournamatch' ),
		);

		parent::__construct( 'trn_online_statistics', esc_html__( 'Online Statistics', 'tournamatch' ), $widget_options );
	}

	/**
	 * Displays the widget.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args The arguments for this widget.
	 * @param array $instance The instance of this widget.
	 */
	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		// users registered today.
		$after      = new \DateTime( date( 'Y-m-d' ) . ' 00:00:00' );
		$query_args = array(
			'date_query' => array(
				array(
					'after'     => $after->format( 'Y-m-d H:i:s' ),
					'inclusive' => true,
				),
			),
		);

		$user_query  = new \WP_User_Query( $query_args );
		$users_today = $user_query->total_users;

		// Get users registered yesterday.
		$after           = $after->sub( new \DateInterval( 'P1D' ) );
		$query_args      = array(
			'date_query' => array(
				array(
					'after'     => $after->format( 'Y-m-d H:i:s' ),
					'inclusive' => true,
				),
			),
		);
		$user_query      = new \WP_User_Query( $query_args );
		$users_yesterday = $user_query->total_users;
		$users_yesterday = $users_yesterday - $users_today;

		// Get last user registered.
		$query_args = array(
			'orderby' => 'registered',
			'order'   => 'DESC',
			'number'  => 1,
		);
		$user_query = get_users( $query_args );
		$last_user  = $user_query[0];

		$members = array(
			'latest'        => array(
				'title' => esc_html__( 'Latest', 'tournamatch' ),
				'data'  => '<a href="' . trn_route( 'players.single', array( 'id' => $last_user->ID ) ) . '">' . $last_user->display_name . '</a>',
			),
			'new_today'     => array(
				'title' => esc_html__( 'New Today', 'tournamatch' ),
				'data'  => $users_today,
			),
			'new_yesterday' => array(
				'title' => esc_html__( 'New Yesterday', 'tournamatch' ),
				'data'  => $users_yesterday,
			),
			'overall'       => array(
				'title' => esc_html__( 'Overall', 'tournamatch' ),
				'data'  => count_users()['total_users'],
			),
		);
		echo '<h5>' . esc_html__( 'Members', 'tournamatch' ) . '</h5>';
		echo '<dl class="trn-online-statistics-widget-list">';
		foreach ( $members as $list_item ) {
			echo '<dt>' . esc_html( $list_item['title'] ) . ':</dt> <dd>' . wp_kses_post( $list_item['data'] ) . '</dd>';
		}
		echo '</dl>';

		// Render visitors section.
		$online_users   = get_transient( 'trn_online_users' );
		$online_members = count( $online_users['users'] );
		$online_guests  = count( $online_users['guests'] );
		$total_online   = $online_members + $online_guests;

		echo '<h5>' . esc_html__( 'Visitation', 'tournamatch' ) . '</h5>';
		echo '<dl class="trn-online-statistics-widget-list">';
		echo ' <dt>' . esc_html__( 'Guests', 'tournamatch' ) . ':</dt>';
		echo ' <dd>' . intval( $online_guests ) . '</dd>';
		echo ' <dt>' . esc_html__( 'Members', 'tournamatch' ) . ':</dt>';
		echo ' <dd>' . intval( $online_members ) . '</dd>';
		echo ' <dt>' . esc_html__( 'Total', 'tournamatch' ) . ':</dt>';
		echo ' <dd>' . intval( $total_online ) . '</dd>';
		echo '</dl>';
		echo '<h5>' . esc_html__( 'Online Now', 'tournamatch' ) . '</h5>';
		echo '<ul>';
		foreach ( $online_users['users'] as $user_id => $last_online ) {
			echo '<li><a href="' . esc_url( trn_route( 'players.single', array( 'id' => $user_id ) ) ) . '">' . esc_html( get_user_by( 'id', $user_id )->display_name ) . '</a>';
			echo '</li>';
		}
		echo '</ul>';
		echo wp_kses_post( $args['after_widget'] );

		wp_register_style( 'trn-online-statistics-widget-styling', plugins_url( '../../dist/css/online-statistics-widget.css', __FILE__ ), [], '3.16.0' );
		wp_enqueue_style( 'trn-online-statistics-widget-styling' );
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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'tournamatch' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
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
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}
}

add_action(
	'widgets_init',
	function() {
		register_widget( Online_Statistics::class );
	}
);
