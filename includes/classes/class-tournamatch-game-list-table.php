<?php
/**
 * Defines all the game list table used to display games in the WordPress backend.
 *
 * @link       https://www.tournamatch.com
 * @since      3.24.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * This class defines all code necessary to render the game list table in the WordPress backend.
 *
 * @since      3.24.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Tournamatch_Game_List_Table extends WP_List_Table {

	/**
	 * Gets the list of columns.
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'name'     => esc_html__( 'Name', 'tournamatch' ),
			'platform' => esc_html__( 'Platform', 'tournamatch' ),
			'image'    => esc_html__( 'Image', 'tournamatch' ),
		);

		return $columns;
	}

	/**
	 * Gets the list of bulk actions available for this table.
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => esc_html__( 'Delete', 'tournamatch' ),
		);
		return $actions;
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @since 3.24.0
	 */
	public function prepare_items() {
		global $wpdb;

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ) );
		}

		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

		// Get total items count.
		$total = "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_games` WHERE 1=1";
		if ( 0 < strlen( $search ) ) {
			$total .= $wpdb->prepare( " AND `{$wpdb->prefix}trn_games`.`name` LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$total_items = $wpdb->get_var( $total ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Get list of $games.
		$games = "SELECT * FROM `{$wpdb->prefix}trn_games` WHERE 1=1 ";
		if ( 0 < strlen( $search ) ) {
			$games .= $wpdb->prepare( " AND `{$wpdb->prefix}trn_games`.`name` LIKE %s ", '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$games .= "GROUP BY `{$wpdb->prefix}trn_games`.`game_id`";

		// Ordering.
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';
		$order_by = ( 0 < strlen( $order_by ) ) ? $order_by : 'name';
		$order_by = in_array( $order_by, array_keys( $this->get_sortable_columns() ), true ) ? $order_by : 'name';

		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : '';
		$order = ( 0 < strlen( $order ) ) ? $order : 'asc';
		$order = in_array( $order, array( 'asc', 'desc' ), true ) ? $order : 'asc';

		$games .= "ORDER BY `{$wpdb->prefix}trn_games`.`$order_by` $order ";

		// Pagination.
		$per_page     = 10;
		$current_page = max( 0, $this->get_pagenum() - 1 );
		$offset       = ( $per_page * $current_page );
		$games       .= $wpdb->prepare( 'LIMIT %d, %d', $offset, $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $wpdb->get_results( $games ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                  // WE have to calculate the total number of items.
				'per_page'    => $per_page,                     // WE have to determine how many items to show on a page.
			)
		);
	}

	/**
	 * Message to display when there are no games.
	 *
	 * @since 3.24.0
	 */
	public function no_items() {
		esc_html_e( 'No games to display.', 'tournamatch' );
	}

	/**
	 * Gets the list of sortable columns for the table.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Gets the content of the 'checkbox' column for the item.
	 *
	 * @param array|object $item The item.
	 *
	 * @since 3.24.0
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%s" />', intval( $item->game_id ) );
	}

	/**
	 * Gets the content to display for the 'name' column for the item.
	 *
	 * @param array|object $item The item.
	 *
	 * @since 3.24.0
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		$actions = array();

		$nonce = wp_create_nonce( 'tournamatch-bulk-games' );

		$actions = array_merge(
			$actions,
			array(
				'edit'   => sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						trn_route(
							'admin.games.edit',
							array(
								'id'       => $item->game_id,
								'_wpnonce' => $nonce,
							)
						)
					),
					esc_html__( 'Edit', 'tournamatch' )
				),
				'delete' => sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						trn_route(
							'admin.games.delete',
							array(
								'id'       => $item->game_id,
								'_wpnonce' => $nonce,
							)
						)
					),
					esc_html__( 'Delete', 'tournamatch' )
				),
			)
		);

		return sprintf( '%1$s %2$s', $item->name, $this->row_actions( $actions ) );
	}

	/**
	 * Gets the content to display for any column not explicitly defined.
	 *
	 * @param array|object $item The item.
	 * @param string       $column_name The column name.
	 *
	 * @since 3.24.0
	 *
	 * @return array|object|string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'platform':
				return $item->platform;
			case 'image':
				return $item->thumbnail;
			default:
				return $item;
		}
	}
}
