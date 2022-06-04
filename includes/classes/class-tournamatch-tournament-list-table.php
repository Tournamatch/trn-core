<?php
/**
 * Defines all the tournament list table used to display tournaments in the WordPress backend.
 *
 * @link       https://www.tournamatch.com
 * @since      3.24.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * This class defines all code necessary to render the tournament list table in the WordPress backend.
 *
 * @since      3.24.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Tournamatch_Tournament_List_Table extends WP_List_Table {

	/**
	 * Gets the list of columns.
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'name'       => esc_html__( 'Name', 'tournamatch' ),
			'status'     => esc_html__( 'Status', 'tournamatch' ),
			'registered' => esc_html__( 'Signed-Up', 'tournamatch' ),
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

		$all_tournaments      = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments`" );
		$open_tournaments     = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments` WHERE `status` != %s", 'complete' ) );
		$finished_tournaments = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments` WHERE `status` = %s", 'complete' ) );

		$view_links = array();

		/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
		$view_links['all'] = sprintf( esc_html__( 'All %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $all_tournaments ), '</span>' );

		if ( 0 < $open_tournaments ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['open'] = sprintf( esc_html__( 'Open %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $open_tournaments ), '</span>' );
		}

		if ( 0 < $finished_tournaments ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['finished'] = sprintf( esc_html__( 'Finished %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $finished_tournaments ), '</span>' );
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
		$total = "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_tournaments` AS `t` WHERE 1=1";
		if ( 0 < strlen( $search ) ) {
			$total .= $wpdb->prepare( ' AND `t`.`name` LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		if ( in_array( $status, array( 'finished', 'open', 'all' ), true ) ) {
			switch ( $status ) {
				case 'open':
					$total .= $wpdb->prepare( ' AND `t`.`status` != %s ', 'complete' );
					break;

				case 'finished':
					$total .= $wpdb->prepare( ' AND `t`.`status` = %s ', 'complete' );
					break;

				case 'all':
				default:
					break;
			}
		}
		$total_items = $wpdb->get_var( $total ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Get list of tournaments.
		$tournaments = "SELECT `t`.*, COUNT(`te`.`tournament_entry_id`) AS `participants` FROM `{$wpdb->prefix}trn_tournaments` AS `t` LEFT JOIN `{$wpdb->prefix}trn_tournaments_entries` AS `te` ON `t`.`tournament_id` = `te`.`tournament_id` WHERE 1=1 ";
		if ( 0 < strlen( $search ) ) {
			$tournaments .= $wpdb->prepare( ' AND `t`.`name` LIKE %s ', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		if ( in_array( $status, array( 'finished', 'open', 'all' ), true ) ) {
			switch ( $status ) {
				case 'open':
					$tournaments .= $wpdb->prepare( ' AND `t`.`status` != %s ', 'complete' );
					break;

				case 'finished':
					$tournaments .= $wpdb->prepare( ' AND `t`.`status` = %s ', 'complete' );
					break;

				case 'all':
				default:
					break;
			}
		}
		$tournaments .= 'GROUP BY `t`.`tournament_id`';

		// Ordering.
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';
		$order_by = ( 0 < strlen( $order_by ) ) ? $order_by : 'name';
		$order_by = in_array( $order_by, array_keys( $this->get_sortable_columns() ), true ) ? $order_by : 'name';

		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : '';
		$order = ( 0 < strlen( $order ) ) ? $order : 'asc';
		$order = in_array( $order, array( 'asc', 'desc' ), true ) ? $order : 'asc';

		$tournaments .= "ORDER BY `t`.`$order_by` $order ";

		// Pagination.
		$per_page     = 10;
		$current_page = max( 0, $this->get_pagenum() - 1 );
		$offset       = ( $per_page * $current_page );
		$tournaments .= $wpdb->prepare( 'LIMIT %d, %d', $offset, $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $wpdb->get_results( $tournaments ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                  // WE have to calculate the total number of items.
				'per_page'    => $per_page,                     // WE have to determine how many items to show on a page.
			)
		);
	}

	/**
	 * Message to display when there are no tournaments.
	 *
	 * @since 3.24.0
	 */
	public function no_items() {
		esc_html_e( 'No tournaments to display.', 'tournamatch' );
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
		return sprintf( '<input type="checkbox" name="id[]" value="%s" />', intval( $item->tournament_id ) );
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

		$nonce = wp_create_nonce( 'tournamatch-bulk-tournaments' );

		if ( in_array( $item->status, array( 'created', 'open' ), true ) ) {
			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				trn_route(
					'admin.tournaments.registration',
					array(
						'id'       => $item->tournament_id,
						'_wpnonce' => $nonce,
					)
				),
				esc_html__( 'Registration', 'tournamatch' )
			);
			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				trn_route(
					'admin.tournaments.start',
					array(
						'id'       => $item->tournament_id,
						'_wpnonce' => $nonce,
					)
				),
				esc_html__( 'Start', 'tournamatch' )
			);
		}

		if ( 'in_progress' === $item->status ) {
			$actions = array_merge(
				$actions,
				array(
					'finish' => sprintf(
						'<a href = "%s" >%s</a>',
						trn_route(
							'admin.tournaments.finish',
							array(
								'id'       => $item->tournament_id,
								'_wpnonce' => $nonce,
							)
						),
						esc_html__( 'Finish', 'tournamatch' )
					),
					'reset'  => sprintf(
						'<a href = "%s" >%s</a>',
						trn_route(
							'admin.tournaments.reset',
							array(
								'id'       => $item->tournament_id,
								'_wpnonce' => $nonce,
							)
						),
						esc_html__( 'Reset', 'tournamatch' )
					),
				)
			);
		}

		$actions = array_merge(
			$actions,
			array(
				'edit'   => sprintf(
					'<a href="%s">%s</a>',
					trn_route(
						'admin.tournaments.edit',
						array(
							'id'       => $item->tournament_id,
							'_wpnonce' => $nonce,
						)
					),
					esc_html__( 'Edit', 'tournamatch' )
				),
				'clone'  => sprintf(
					'<a href="%s" title="%s">%s</a>',
					trn_route(
						'admin.tournaments.clone',
						array(
							'id'       => $item->tournament_id,
							'_wpnonce' => $nonce,
						)
					),
					esc_html__( 'Clone this tournament.', 'tournamatch' ),
					esc_html__( 'Clone', 'tournamatch' )
				),
				'delete' => sprintf(
					'<a href="%s">%s</a>',
					trn_route(
						'admin.tournaments.delete',
						array(
							'id'       => $item->tournament_id,
							'_wpnonce' => $nonce,
						)
					),
					esc_html__( 'Delete', 'tournamatch' )
				),
			)
		);

		$actions = apply_filters( 'trn_admin_tournament_item_row_actions', $actions, $item );

		return sprintf( '%1$s %2$s', $item->name, $this->row_actions( $actions ) );
	}

	/**
	 * Gets the content to display for the 'status' column for the item.
	 *
	 * @param array|object $item The item.
	 *
	 * @since 3.24.0
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		if ( in_array( $item->status, array( 'created', 'open' ), true ) ) {
			return esc_html__( 'Players are allowed to sign up.', 'tournamatch' );
		} elseif ( 'in_progress' === $item->status ) {
			return esc_html__( 'Check-ins are already closed.', 'tournamatch' );
		} else {
			return esc_html__( 'You have marked this tournament complete.', 'tournamatch' );
		}
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
			case 'registered':
				return $item->participants;
			default:
				return $item;
		}
	}
}
