<?php
/**
 * Defines the base code necessary to display matches in the WordPress backend.
 *
 * @link       https://www.tournamatch.com
 * @since      3.24.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * This class defines the base code necessary to display the match list table in the WordPress backend.
 *
 * @since      3.24.0
 *
 * @package    Tournamatch
 * @author     Tournamatch <support@tournamatch.com>
 */
class Tournamatch_Match_List_Table extends WP_List_Table {

	/**
	 * The type of competition to display matches.
	 *
	 * @var string Indicates the type of competition to display matches for.
	 *
	 * @since 3.24.0
	 */
	private $competition_type;

	/**
	 * Class constructor.
	 *
	 * @param array $args Table parameters.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->competition_type = isset( $args['competition_type'] ) ? $args['competition_type'] : 'ladders';
		$this->competition_type = in_array( $this->competition_type, array( 'ladders', 'tournaments' ), true ) ? $this->competition_type : 'ladders';
	}

	/**
	 * Gets the list of columns.
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'name'     => esc_html__( 'Competition', 'tournamatch' ),
			'details'  => esc_html__( 'Details', 'tournamatch' ),
			'reported' => esc_html__( 'Reported', 'tournamatch' ),
			'status'   => esc_html__( 'Status', 'tournamatch' ),
		);

		return $columns;
	}

	/**
	 * Displays extra navigation for this table.
	 *
	 * @since 3.24.0
	 *
	 * @param string $which Either 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		global $wpdb;
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' === $which ) {
				$competitions = $wpdb->get_results(
					$wpdb->prepare(
						"
SELECT 
  DISTINCT `m`.`competition_id`,
  CASE `m`.`competition_type`
    WHEN 'ladders' THEN `l`.`name`
    ELSE `t`.`name`
    END `competition_name`  
FROM `{$wpdb->prefix}trn_matches` AS `m` 
  LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `m`.`competition_id` AND `m`.`competition_type` = 'ladders'
  LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `t`.`tournament_id` = `m`.`competition_id` AND `m`.`competition_type` = 'tournaments'
WHERE `m`.`competition_type` = %s
",
						$this->competition_type
					)
				);

				if ( 0 < count( $competitions ) ) {
					?>
					<select id="trn-match-competition-filter-list" name="competition_id">
					<?php foreach ( $competitions as $competition ) : ?>
						<option value="<?php echo intval( $competition->competition_id ); ?>"><?php echo esc_html( $competition->competition_name ); ?></option>
					<?php endforeach; ?>
					</select>
					<?php
					submit_button( esc_html__( 'Filter', 'tournamatch' ), '', 'filter_action', false, array( 'id' => 'trn-match-competition-filter-submit' ) );
				}
			}
			?>
		</div>
		<?php
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

		$all_matches          = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` WHERE `competition_type` = %s AND `match_status` != 'tournament_none' ", $this->competition_type ) );
		$confirmed_matches    = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` WHERE `competition_type` = %s AND `match_status` = %s", $this->competition_type, 'confirmed' ) );
		$reported_matches     = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` WHERE `competition_type` = %s AND `match_status` = %s", $this->competition_type, 'reported' ) );
		$disputed_matches     = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` WHERE `competition_type` = %s AND `match_status` = %s", $this->competition_type, 'disputed' ) );
		$scheduled_matches    = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` WHERE `competition_type` = %s AND `match_status` = %s", $this->competition_type, 'scheduled' ) );
		$undetermined_matches = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` WHERE `competition_type` = %s AND `match_status` = %s", $this->competition_type, 'undetermined' ) );

		$view_links = array();

		/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
		$view_links['all'] = sprintf( esc_html__( 'All %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $all_matches ), '</span>' );

		if ( 0 < $confirmed_matches ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['confirmed'] = sprintf( esc_html__( 'Confirmed %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $confirmed_matches ), '</span>' );
		}

		if ( 0 < $scheduled_matches ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['scheduled'] = sprintf( esc_html__( 'Scheduled %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $scheduled_matches ), '</span>' );
		}

		if ( 0 < $reported_matches ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['reported'] = sprintf( esc_html__( 'Reported %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $reported_matches ), '</span>' );
		}

		if ( 0 < $disputed_matches ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['disputed'] = sprintf( esc_html__( 'Disputed %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $disputed_matches ), '</span>' );
		}

		if ( 0 < $undetermined_matches ) {
			/* translators: 1 and 3 are span tags; #2 is a numerical number of items. */
			$view_links['undetermined'] = sprintf( esc_html__( 'Undetermined %1$s(%2$s)%3$s', 'tournamatch' ), '<span class="count">', number_format_i18n( $undetermined_matches ), '</span>' );
		}

		$status = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all';

		array_walk(
			$view_links,
			function( &$value, $key ) use ( $status ) {
				$class = isset( $status ) && ( $key === $status ) ? ' class="current"' : '';
				$url   = remove_query_arg( array( 'paged', 'competition_id', 'filter_action' ) );
				$url   = add_query_arg( 'status', $key, $url );
				$value = sprintf( '<a href="%s"%s>%s</a>', $url, $class, $value );
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

		$search         = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$status         = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all';
		$competition_id = isset( $_REQUEST['competition_id'] ) ? intval( $_REQUEST['competition_id'] ) : 0;

		// Get total items count.
		$total = $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}trn_matches` AS `m` WHERE `competition_type` = %s AND `match_status` != 'tournament_none' ", $this->competition_type );

		if ( 0 < $competition_id ) {
			$total .= $wpdb->prepare( ' AND `m`.`competition_id` = %d ', $competition_id );
		}

		if ( 0 < strlen( $search ) ) {
			$total .= $wpdb->prepare( ' AND `m`.`match_status` LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}

		if ( in_array( $status, array( 'scheduled', 'undetermined', 'reported', 'disputed', 'confirmed', 'all' ), true ) ) {
			switch ( $status ) {
				case 'reported':
					$total .= $wpdb->prepare( ' AND `m`.`match_status` = %s ', 'reported' );
					break;
				case 'disputed':
					$total .= $wpdb->prepare( ' AND `m`.`match_status` = %s ', 'disputed' );
					break;
				case 'undetermined':
					$total .= $wpdb->prepare( ' AND `m`.`match_status` = %s ', 'undetermined' );
					break;
				case 'scheduled':
					$total .= $wpdb->prepare( ' AND `m`.`match_status` = %s ', 'scheduled' );
					break;
				case 'confirmed':
					$total .= $wpdb->prepare( ' AND `m`.`match_status` = %s ', 'confirmed' );
					break;
				case 'all':
				default:
					break;
			}
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total_items = $wpdb->get_var( $total );

		// Get list of ladder matches.
		$matches = $wpdb->prepare(
			"
SELECT 
  `tm`.*, 
  CASE `tm`.`competition_type`
    WHEN 'ladders' THEN `l`.`name`
    ELSE `t`.`name`
    END `name`,  
  CASE `tm`.`competition_type`
    WHEN 'ladders' THEN `l`.`competitor_type`
    ELSE `t`.`competitor_type`
    END `competitor_type` 
FROM `{$wpdb->prefix}trn_matches` AS `tm` 
LEFT JOIN `{$wpdb->prefix}trn_ladders` AS `l` ON `l`.`ladder_id` = `tm`.`competition_id` AND `tm`.`competition_type` = 'ladders'
LEFT JOIN `{$wpdb->prefix}trn_tournaments` AS `t` ON `t`.`tournament_id` = `tm`.`competition_id` AND `tm`.`competition_type` = 'tournaments'
WHERE `tm`.`competition_type` = %s AND `match_status` != 'tournament_none' ",
			$this->competition_type
		);

		if ( 0 < $competition_id ) {
			$matches .= $wpdb->prepare( ' AND `tm`.`competition_id` = %d ', $competition_id );
		}

		if ( 0 < strlen( $search ) ) {
			$matches .= $wpdb->prepare( ' AND (`l`.`name` LIKE %s ', '%' . $wpdb->esc_like( $search ) . '%' );
			$matches .= $wpdb->prepare( ' OR `t`.`name` LIKE %s )', '%' . $wpdb->esc_like( $search ) . '%' );
		}

		if ( in_array( $status, array( 'scheduled', 'undetermined', 'confirmed', 'reported', 'disputed', 'all' ), true ) ) {
			switch ( $status ) {
				case 'reported':
					$matches .= $wpdb->prepare( ' AND `tm`.`match_status` = %s ', 'reported' );
					break;
				case 'disputed':
					$matches .= $wpdb->prepare( ' AND `tm`.`match_status` = %s ', 'disputed' );
					break;
				case 'scheduled':
					$matches .= $wpdb->prepare( ' AND `tm`.`match_status` = %s ', 'scheduled' );
					break;
				case 'undetermined':
					$matches .= $wpdb->prepare( ' AND `tm`.`match_status` = %s ', 'undetermined' );
					break;
				case 'confirmed':
					$matches .= $wpdb->prepare( ' AND `tm`.`match_status` = %s ', 'confirmed' );
					break;
				case 'all':
				default:
					break;
			}
		}
		$matches .= 'GROUP BY `tm`.`match_id` ';

		// Ordering.
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';
		$order_by = ( 0 < strlen( $order_by ) ) ? $order_by : 'match_date';
		$order_by = in_array( $order_by, array_keys( $this->get_sortable_columns() ), true ) ? $order_by : 'match_date';

		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : '';
		$order = ( 0 < strlen( $order ) ) ? $order : 'asc';
		$order = in_array( $order, array( 'asc', 'desc' ), true ) ? $order : 'asc';

		switch ( $order_by ) {
			case 'status':
				$matches .= "ORDER BY `tm`.`match_status` $order ";
				break;

			case 'reported':
			default:
				$matches .= "ORDER BY `tm`.`match_date` $order ";
				break;

			case 'name':
				$matches .= "ORDER BY `name` $order ";
				break;
		}

		// Pagination.
		$per_page     = 10;
		$current_page = max( 0, $this->get_pagenum() - 1 );
		$offset       = ( $per_page * $current_page );

		$matches .= $wpdb->prepare( 'LIMIT %d, %d', $offset, $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $wpdb->get_results( $matches ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                  // WE have to calculate the total number of items.
				'per_page'    => $per_page,                     // WE have to determine how many items to show on a page.
			)
		);
	}

	/**
	 * Message to display when there are no matches.
	 *
	 * @since 3.24.0
	 */
	public function no_items() {
		esc_html_e( 'No matches to display.', 'tournamatch' );
	}

	/**
	 * Gets the list of sortable columns for the table.
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name'     => array( 'name', false ),
			'reported' => array( 'reported', false ),
			'status'   => array( 'status', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Gets the content to display for the 'competition' column for the item.
	 *
	 * @param array|object $item The item.
	 *
	 * @since 3.24.0
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		global $wpdb;

		$actions = array();

		$nonce = wp_create_nonce( 'tournamatch-bulk-matches' );

		if ( 'disputed' === $item->match_status ) {
			if ( 'players' === $item->competitor_type ) {
				$one = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $item->one_competitor_id ), ARRAY_A );
				$two = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $item->two_competitor_id ), ARRAY_A );
			} else {
				$one = $wpdb->get_row( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $item->one_competitor_id ), ARRAY_A );
				$two = $wpdb->get_row( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $item->two_competitor_id ), ARRAY_A );
			}
			$actions[] = sprintf(
				'<a href="%s" title="%s">%s</a>',
				trn_route(
					'admin.ladders.resolve-match',
					array(

						'id'        => $item->match_id,
						'winner_id' => $item->one_competitor_id,
						'_wpnonce'  => $nonce,
					)
				),
				sprintf(
					/* translators: A competitor name. */
					esc_html__( 'Select "%s" as winner.', 'tournamatch' ),
					esc_html( $one['name'] )
				),
				sprintf(
					/* translators: A competitor name. */
					esc_html__( 'Winner: %s', 'tournamatch' ),
					esc_html( $one['name'] )
				)
			);
			$actions[] = sprintf(
				'<a href="%s" title="%s">%s</a>',
				trn_route(
					'admin.ladders.resolve-match',
					array(

						'id'        => $item->match_id,
						'winner_id' => $item->two_competitor_id,
						'_wpnonce'  => $nonce,
					)
				),
				sprintf(
					/* translators: A competitor name. */
					esc_html__( 'Select "%s" as winner.', 'tournamatch' ),
					esc_html( $two['name'] )
				),
				sprintf(
					/* translators: A competitor name. */
					esc_html__( 'Winner: %s', 'tournamatch' ),
					esc_html( $two['name'] )
				)
			);
		}

		if ( ( 'tournaments' === $item->competition_type ) && ( 'scheduled' === $item->match_status ) ) {
			if ( 'players' === $item->competitor_type ) {
				$one = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $item->one_competitor_id ), ARRAY_A );
				$two = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $item->two_competitor_id ), ARRAY_A );
			} else {
				$one = $wpdb->get_row( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $item->one_competitor_id ), ARRAY_A );
				$two = $wpdb->get_row( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $item->two_competitor_id ), ARRAY_A );
			}

			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				trn_route(
					'admin.tournaments.advance-match',
					array(
						'id'        => $item->match_id,
						'winner_id' => $item->one_competitor_id,
						'_wpnonce'  => $nonce,
					)
				),
				/* translators: A competitor name. */
				sprintf( esc_html__( 'Advance %s', 'tournamatch' ), esc_html( $one['name'] ) )
			);

			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				trn_route(
					'admin.tournaments.advance-match',
					array(
						'id'        => $item->match_id,
						'winner_id' => $item->two_competitor_id,
						'_wpnonce'  => $nonce,
					)
				),
				/* translators: A competitor name. */
				sprintf( esc_html__( 'Advance %s', 'tournamatch' ), esc_html( $two['name'] ) )
			);
		}

		if ( 'reported' === $item->match_status ) {
			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				trn_route(
					'admin.' . $this->competition_type . '.confirm-match',
					array(
						'id'       => $item->match_id,
						'_wpnonce' => $nonce,
					)
				),
				esc_html__( 'Confirm', 'tournamatch' )
			);
			if ( 'tournaments' === $item->competition_type ) {
				$actions[] = sprintf(
					'<a href="%s">%s</a>',
					trn_route(
						'admin.tournaments.clear-match',
						array(
							'id'       => $item->match_id,
							'_wpnonce' => $nonce,
						)
					),
					esc_html__( 'Clear', 'tournamatch' )
				);
			}
		}

		if ( ( 'confirmed' === $item->match_status ) && ( 'ladders' === $item->competition_type ) ) {
			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				trn_route(
					'admin.ladders.edit-match',
					array(
						'id'       => $item->match_id,
						'_wpnonce' => $nonce,
					)
				),
				esc_html__( 'Edit', 'tournamatch' )
			);
		}

		if ( 'ladders' === $item->competition_type ) {
			$actions['delete'] = sprintf(
				'<a href="%s" >%s</a>',
				trn_route(
					'admin.ladders.delete-match',
					array(
						'id'       => $item->match_id,
						'_wpnonce' => $nonce,
					)
				),
				esc_html__( 'Delete', 'tournamatch' )
			);
		}

		return sprintf( '%1$s %2$s', $item->name, $this->row_actions( $actions ) );
	}

	/**
	 * Gets the content to display for the 'result' column for the item.
	 *
	 * @param array|object $item The item.
	 *
	 * @since 3.24.0
	 *
	 * @return string
	 */
	public function column_details( $item ) {
		global $wpdb;

		$item = (array) $item;

		if ( 'players' === $item['competitor_type'] ) {
			$row3  = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $item['one_competitor_id'] ), ARRAY_A );
			$row33 = $wpdb->get_row( $wpdb->prepare( "SELECT `display_name` AS `name` FROM `{$wpdb->prefix}trn_players_profiles` WHERE `user_id` = %d", $item['two_competitor_id'] ), ARRAY_A );
		} else {
			$row3  = $wpdb->get_row( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $item['one_competitor_id'] ), ARRAY_A );
			$row33 = $wpdb->get_row( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}trn_teams` WHERE `team_id` = %d", $item['two_competitor_id'] ), ARRAY_A );
		}

		$html = '';
		if ( 'undetermined' === $item['match_status'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= esc_html__( 'The competitors in this tournament match are not yet determined.', 'tournamatch' );
		} elseif ( 'scheduled' === $item['match_status'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= sprintf( esc_html__( 'A match between %1$s and %2$s is scheduled.', 'tournamatch' ), esc_html( $row3['name'] ), esc_html( $row33['name'] ) );
		} elseif ( 'won' === $item['one_result'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= sprintf( esc_html__( '%1$s reported a win against %2$s.', 'tournamatch' ), esc_html( $row3['name'] ), esc_html( $row33['name'] ) );
		} elseif ( 'draw' === $item['one_result'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= sprintf( esc_html__( '%1$s reported a draw against %2$s.', 'tournamatch' ), esc_html( $row3['name'] ), esc_html( $row33['name'] ) );
		} elseif ( 'lost' === $item['one_result'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= sprintf( esc_html__( '%1$s reported a loss against %2$s.', 'tournamatch' ), esc_html( $row3['name'] ), esc_html( $row33['name'] ) );
		} elseif ( 'won' === $item['two_result'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= sprintf( esc_html__( '%1$s reported a win against %2$s.', 'tournamatch' ), esc_html( $row33['name'] ), esc_html( $row3['name'] ) );
		} elseif ( 'draw' === $item['two_result'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= sprintf( esc_html__( '%1$s reported a draw against %2$s.', 'tournamatch' ), esc_html( $row33['name'] ), esc_html( $row3['name'] ) );
		} elseif ( 'lost' === $item['two_result'] ) {
			/* translators: One competitor name and another competitor name. */
			$html .= sprintf( esc_html__( '%1$s reported a loss against %2$s.', 'tournamatch' ), esc_html( $row33['name'] ), esc_html( $row3['name'] ) );
		}

		return $html;
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
			case 'reported':
				if ( '0000-00-00 00:00:00' === $item->match_date ) {
					return '';
				} else {
					return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_date_from_gmt( $item->match_date ) ) );
				}
			case 'status':
				return ucfirst( $item->match_status );
			default:
				return $item;
		}
	}

}
