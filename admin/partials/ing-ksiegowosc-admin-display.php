<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( ABSPATH . 'wp-content/plugins/ing-ksiegowosc/admin/class-ing-ksiegowosc-admin-ing-ksiegowosc-invoice-list-table.php' );

?>

<div class="wrap">

	<div id="icon-themes" class="icon32"></div>
	<h2><?php echo esc_html__( 'List of all invoices', 'ing-ksiegowosc' ); ?></h2>

	<p><?php echo esc_html__( 'Here you will find all documents confirming the sale that have been automatically issued in ING Księgowość', 'ing-ksiegowosc' ); ?></p>

	<?php
	$wp_list_table = new Ing_Ksiegowosc_Invoice_List_Table();
	$wp_list_table->prepare_items();
	?>

	<form method="post" name="ing_ksiegowosc_search_invoice" action="<?php echo esc_html( sanitize_text_field( $_SERVER['PHP_SELF'] ) . '?page=' . $this->plugin_name ); ?>">
		<?php
		$wp_list_table->search_box( esc_html__( 'Search invoice', 'ing-ksiegowosc' ), 'search_invoice' );
		?>
	</form>

	<?php
	$wp_list_table->display();
	?>

</div>
