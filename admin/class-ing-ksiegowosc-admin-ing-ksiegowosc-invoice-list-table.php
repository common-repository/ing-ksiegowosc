<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Ingk\Api;

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 *
 */
class Ing_Ksiegowosc_Invoice_List_Table extends WP_List_Table {

	/**
	 * @const int
	 */
	const ROWS_PER_PAGE = 10;

	/**
	 * @param int    $rows_per_page
	 * @param string $orderby
	 * @param string $order
	 * @param string $search_term
	 *
	 * @return array
	 */
	protected function get_invoices( $rows_per_page, $orderby = '', $order = '', $search_term = '' ) {

		$table_name = Ing_Ksiegowosc_Admin::get_table_name_invoices();

		global $wpdb;
		$query[] = $wpdb->prepare( 'SELECT * FROM %i WHERE 1=1', $table_name );

		global $wpdb;

		if ( $search_term ) {
			$query[] = $wpdb->prepare( "AND invoice_id LIKE '%%s%'", esc_sql( $wpdb->esc_like( wc_clean( wp_unslash( $search_term ) ) ) ) );
		}

		if ( $orderby && $order ) {
			$query[] = $wpdb->prepare( "ORDER BY %s DESC ", sanitize_sql_orderby( "{$orderby} {$order}" ) );
		}

		$query[] = $wpdb->prepare( "LIMIT %d OFFSET %d", $rows_per_page, ( ( $this->get_pagenum() - 1 ) * $rows_per_page ) );

		$dataset = $wpdb->get_results(  implode( ' ', $query )  );

		$arr = [];

		if ( count( $dataset ) > 0 ) {

			foreach ( $dataset as $data ) {
				$arr[] = [
					'ID'         => $data->ID,
					'order_id'   => $data->order_id,
					'invoice_id' => $data->invoice_id,
					'created'    => date( "Y-m-d, H:i:s", $data->created ),
					'modified'   => date( "Y-m-d, H:i:s", $data->modified ),
				];
			}
		}

		return [
			'count'   => $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM %i", $table_name ) ),
			'dataset' => $arr,
		];
	}

	/**
	 * @param array $invoice
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function column_invoice_id( $invoice ) {

		$ing_ksiegowosc        = new Ing_Ksiegowosc();
		$plugin_name = $ing_ksiegowosc->get_plugin_name();

		include_once dirname( dirname( __FILE__ ) ) . '/sdk/idk-core/src/Api.php';

		//TODO: reminder - environment if in future will appear
		$api = new Api( get_option( 'ing_ksiegowosc_api_key' ) );

		$output = sprintf( '<strong><a href="%s" target="_blank">%s</a></strong>', $api->getPreviewInvoice( $invoice['invoice_id'] ), $invoice['invoice_id'] );

		if ( empty( $invoice['invoice_id'] ) ) {

			$invoice['invoice_id'] = esc_html__( 'Invoice was not created', 'ing-ksiegowosc' );

			$output = sprintf( '<strong>%s</strong>', $invoice['invoice_id'] );

			// TODO: leave, maybe usable in future
			//$actions = [
			//	'generate' => sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( 123 ), __( 'Generate', 'ing-ksiegowosc' ) ),
			//];
			//
			//$row_actions = [];
			//
			//foreach ( $actions as $action => $link ) {
			//	$row_actions[] = sprintf( '<span class="%s">%s</span>', $action, $link );
			//}

			//$output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';
		}

		return wp_kses( $output, [
				'strong' => [],
				'a'      => [
					'href'   => [],
					'target' => [],
				],
			]
		);
	}

	/**
	 * @param array $webhook
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function column_order_id( $webhook ) {

		return wp_kses_post(
			sprintf(
				'<a href="%s" ><strong>#%s</strong></a>',
				admin_url( 'post.php?post=' . esc_attr( $webhook['order_id'] ) . '&action=edit' ),
				esc_html( $webhook['order_id'] )
			)
		);
	}

	/**
	 * @return void
	 */
	public function prepare_items() {

		$order_by = ( isset( $_GET['orderby'] ) && $_GET['orderby'] )
			? trim( sanitize_text_field( $_GET['orderby'] ) )
			: '';
		$order    = ( isset( $_GET['order'] ) && $_GET['order'] )
			? trim( sanitize_text_field( $_GET['order'] ) )
			: '';

		$search_term = ( isset( $_POST['s'] ) && $_POST['s'] )
			? trim( sanitize_text_field( $_POST['s'] ) )
			: '';

		$invoices = $this->get_invoices( self::ROWS_PER_PAGE, $order_by, $order, $search_term );

		$this->set_pagination_args( [
			'total_items' => $invoices['count'],
			'per_page'    => self::ROWS_PER_PAGE,
		] );

		$this->items = $invoices['dataset'];

		$this->_column_headers = [ $this->get_columns(), $this->get_hidden_columns(), $this->get_sortable_columns() ];
	}

	/**
	 * @return array
	 */
	private function get_hidden_columns() {
		return [];
	}

	/**
	 * @return array
	 */
	public function get_sortable_columns() {
		return [];
	}

	/**
	 * @return array
	 */
	public function get_columns() {

		return [
			'ID'         => esc_html__( 'ID', 'ing-ksiegowosc' ),
			'order_id'   => esc_html__( 'Order ID', 'ing-ksiegowosc' ),
			'invoice_id' => esc_html__( 'Invoice ID', 'ing-ksiegowosc' ),
			'created'    => esc_html__( 'Created', 'ing-ksiegowosc' ),
			'modified'   => esc_html__( 'Modified', 'ing-ksiegowosc' ),
		];
	}

	/**
	 * @return string
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'ID':
			case 'order_id':
			case 'invoice_id':
			case 'created':
			case 'modified':
				return $item[ $column_name ];
			default:
				return esc_html__( 'No data', 'ing-ksiegowosc' );
		}
	}
}
