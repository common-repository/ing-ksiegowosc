<?php

/**
 * Bootstrap file
 *
 * @link              https://ingksiegowosc.pl
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: ING Księgowość
 * Plugin URI: https://www.ingksiegowosc.pl/
 * Description: Niech faktury za zakupy Twoich klientów wystawiają się automatycznie! Wtyczka pozwala na powiązanie sklepu z kontem firmy w aplikacji ING Księgowość.
 * Version: 1.0.5
 * Author: ING Księgowość
 * Author URI: https://ingksiegowosc.pl
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ing-ksiegowosc
 * Domain Path: /languages
 */

// abort while called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version
 */
const ING_KSIEGOWOSC_VERSION = '1.0.5';

/**
 * Code that runs during plugin activation
 */
function ing_ksiegowosc_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ing-ksiegowosc-activator.php';

	Ing_Ksiegowosc_Activator::activate();
}

/**
 * Code that runs during plugin deactivation
 */
function ing_ksiegowosc_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ing-ksiegowosc-deactivator.php';
	Ing_Ksiegowosc_Deactivator::deactivate();
}

// region register hooks
register_activation_hook( __FILE__, 'ing_ksiegowosc_activate' );
register_deactivation_hook( __FILE__, 'ing_ksiegowosc_deactivate' );
// endregion

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ing-ksiegowosc.php';

/**
 * Begins execution of the plugin.
 */
function ing_ksiegowosc_run() {

	$plugin = new Ing_Ksiegowosc();
	$plugin->run();
}

ing_ksiegowosc_run();
