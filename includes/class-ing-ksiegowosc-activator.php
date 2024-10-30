<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation
 */
class Ing_Ksiegowosc_Activator {

	const DB_SCHEMA = '1.0.0';

	/**
	 * Logic - fired before activated plugin
	 *
	 * @return void
	 */
	public static function activate() {


		if ( ! function_exists( 'curl_version' ) ) {
			die( 'cURL is not enabled' );
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = Ing_Ksiegowosc_Admin::get_table_name_invoices();

		global $wpdb;

		maybe_create_table( $table_name, $wpdb->prepare( 'CREATE TABLE %i (
		`ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`order_id` VARCHAR(255) NOT NULL, 
		`invoice_id` VARCHAR(255), 
		`uuid_generated` VARCHAR(36), 
		`created` INT(10) UNSIGNED NOT NULL, 
		`modified` INT(10) UNSIGNED NOT NULL, 
		PRIMARY KEY (`id`) 
		);', $table_name ) );

		update_option( 'ing_ksiegowosc_db_schema', self::DB_SCHEMA );
	}
}
