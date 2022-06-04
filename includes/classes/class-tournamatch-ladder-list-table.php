<?php
/**
 * Defines all the ladder list table used to display ladders in the WordPress backend.
 *
 * @link       https://www.tournamatch.com
 * @since      3.24.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * This class defines all code necessary to render the ladder list table in the WordPress backend.
 *
 * @since      3.24.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Tournamatch_Ladder_List_Table extends WP_List_Table {

	/**
	 * Gets the list of columns.
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'name'         => esc_html__( 'Name', 'tournamatch' ),
			'participants' => esc_html__( 'Participants', 'tournamatch' ),
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
	 * Gets the list of views available for this table.
	 *
	 * The format is an associative array:
	 * - `'id' => 'link'`
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_views() {
		global $wpdb;

		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ) );
		}

		$all_ladders      = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders`" );
		$active_ladders   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders` WHERE `status` = %s", 'active' ) );
		$inactive_ladders = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders` WHERE `status` = %s", 'inactive' ) );

		$view_links = array();

		/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
		$view_links['all'] = sprintf( esc_html__( 'All %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $all_ladders ), '</span>' );

		if ( 0 < $active_ladders ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['active'] = sprintf( esc_html__( 'Active %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $active_ladders ), '</span>' );
		}

		if ( 0 < $inactive_ladders ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['inactive'] = sprintf( esc_html__( 'Inactive %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $inactive_ladders ), '</span>' );
		}

		$status = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all';

		array_walk(
			$view_links,
			function( &$value, $key ) use ( $status ) {
				$class = isset( $status ) && ( $key === $status ) ? ' class="current"' : '';
				$value = sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', $key ), $class, $value );
			}
		);

		return $view_links;
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
		$status = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : '';

		// Get total items count.
		$total = "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_ladders` AS `l` WHERE 1=1";
		if ( 0 < strlen( $search ) ) {
			$total .= $wpdb->prepare( ' AND `l`.`name` LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		if ( in_array( $status, array( 'inactive', 'active', 'all' ), true ) ) {
			switch ( $status ) {
				case 'active':
					$total .= $wpdb->prepare( ' AND `l`.`status` = %s ', 'active' );
					break;
				case 'inactive':
					$total .= $wpdb->prepare( ' AND `l`.`status` = %s ', 'inactive' );
					break;

				case 'all':
				default:
					break;
			}
		}
		$total_items = $wpdb->get_var( $total ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Get list of ladders.
		$ladders = "SELECT `l`.*, COUNT(`le`.`ladder_entry_id`) AS participants FROM `{$wpdb->prefix}trn_ladders` AS `l` LEFT JOIN `{$wpdb->prefix}trn_ladders_entries` AS `le` ON `l`.`ladder_id` = `le`.`ladder_id` WHERE 1=1 ";
		if ( 0 < strlen( $search ) ) {
			$ladders .= $wpdb->prepare( ' AND `l`.`name` LIKE %s ', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		if ( in_array( $status, array( 'inactive', 'active', 'all' ), true ) ) {
			switch ( $status ) {
				case 'active':
					$ladders .= $wpdb->prepare( ' AND `l`.`status` = %s ', 'active' );
					break;
				case 'inactive':
					$ladders .= $wpdb->prepare( ' AND `l`.`status` = %s ', 'inactive' );
					break;

				case 'all':
				default:
					break;
			}
		}
		$ladders .= 'GROUP BY `l`.`ladder_id`';

		// Ordering.
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';
		$order_by = ( 0 < strlen( $order_by ) ) ? $order_by : 'name';
		$order_by = in_array( $order_by, array_keys( $this->get_sortable_columns() ), true ) ? $order_by : 'name';

		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : '';
		$order = ( 0 < strlen( $order ) ) ? $order : 'asc';
		$order = in_array( $order, array( 'asc', 'desc' ), true ) ? $order : 'asc';

		$ladders .= "ORDER BY `l`.`$order_by` $order ";

		// Pagination.
		$per_page     = 10;
		$current_page = max( 0, $this->get_pagenum() - 1 );
		$offset       = ( $per_page * $current_page );
		$ladders     .= $wpdb->prepare( 'LIMIT %d, %d', $offset, $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $wpdb->get_results( $ladders ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                  // WE have to calculate the total number of items.
				'per_page'    => $per_page,                     // WE have to determine how many items to show on a page.
			)
		);
	}

	/**
	 * Message to display when there are no ladders.
	 *
	 * @since 3.24.0
	 */
	public function no_items() {
		esc_html_e( 'No ladders to display.', 'tournamatch' );
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
		return sprintf( '<input type="checkbox" name="id[]" value="%s" />', intval( $item->ladder_id ) );
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

		$nonce = wp_create_nonce( 'tournamatch-bulk-ladders' );

		if ( 'active' === $item->status ) {
			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				trn_route(
					'admin.matches.select-competitors',
					array(
						'competition_id' => $item->ladder_id,
						'_wpnonce'       => wp_create_nonce( 'tournamatch-bulk-matches' ),
					)
				),
				esc_html__( 'Report Match', 'tournamatch' )
			);
		}

		$actions = array_merge(
			$actions,
			array(
				'edit'   => sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						trn_route(
							'admin.ladders.edit',
							array(
								'id'       => $item->ladder_id,
								'_wpnonce' => $nonce,
							)
						)
					),
					esc_html__( 'Edit', 'tournamatch' )
				),
				'clone'  => sprintf(
					'<a href="%s" title="%s">%s</a>',
					esc_url(
						trn_route(
							'admin.ladders.clone',
							array(
								'id'       => $item->ladder_id,
								'_wpnonce' => $nonce,
							)
						)
					),
					esc_html__( 'Clone this ladder.', 'tournamatch' ),
					esc_html__( 'Clone', 'tournamatch' )
				),
				'delete' => sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						trn_route(
							'admin.ladders.delete',
							array(
								'id'       => $item->ladder_id,
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
			case 'participants':
				return ( '0' !== $item->participants ) ? $item->participants : 'NA';
			default:
				return $item;
		}
	}

}
