<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class.
 */
class Ing_Ksiegowosc {

	/**
	 * @var Ing_Ksiegowosc_Loader $loader
	 */
	protected $loader;

	/**
	 * @var string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * @var string $version
	 */
	protected $version;

	/**
	 * @return void
	 */
	public function __construct() {
		$this->version = defined( 'ING_KSIEGOWOSC_VERSION' )
			? ING_KSIEGOWOSC_VERSION
			: '0.0.1';

		$this->plugin_name = 'ing-ksiegowosc';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * @return void
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ing-ksiegowosc-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ing-ksiegowosc-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ing-ksiegowosc-admin.php';

		$this->loader = new Ing_Ksiegowosc_Loader();
	}

	/**
	 * @return void
	 */
	private function set_locale() {

		$plugin_i18n = new Ing_Ksiegowosc_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * @return void
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ing_Ksiegowosc_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'ing_ksiegowosc_enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'ing_ksiegowosc_enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'ing_ksiegowosc_create_invoice', 10, 4 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ing_ksiegowosc_admin_menu', 9 );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ing_ksiegowosc_register_and_build_fields' );
		$this->loader->add_filter( 'plugin_action_links_' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/ing-ksiegowosc.php', $plugin_admin, 'ing_ksiegowosc_page_settings_link' );
	}

	/**
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * @return Ing_Ksiegowosc_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

}
