<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\Overrides\Order;
use Ingk\Api;
use Ingk\Invoice;

/**
 * The admin-specific functionality of the plugin
 */
class Ing_Ksiegowosc_Admin {

	/**
	 * @const string
	 */
	const WC_STATE_COMPLETED = 'completed';

	/**
	 * Plugin ID
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @const string
	 */
	const TABLE_NAME_INVOICE = 'ing_ksiegowosc_invoices';

	/**
	 * @return string
	 */
	public static function get_table_name_invoices() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_NAME_INVOICE;
	}

	/**
	 * @param string $plugin_name
	 * @param string $version
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function ing_ksiegowosc_enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ing-ksiegowosc-admin.min.css', [], $this->version );
	}

	/**
	 * Register the JavaScript for the admin area
	 *
	 * @return void
	 */
	public function ing_ksiegowosc_enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ing-ksiegowosc-admin.min.js', [ 'jquery' ], $this->version, false );
	}

	/**
	 * @param string $id
	 * @param string $status_from
	 * @param string $status_to
	 * @param Order  $order
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 * @return void
	 */
	public function ing_ksiegowosc_create_invoice( $id, $status_from, $status_to, $order ) {

		$api_key = get_option( 'ing_ksiegowosc_api_key' );

		if ( ! $api_key ) {
			return;
		}

		global $wpdb;

		if ( $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `%i` WHERE `order_id` = %d", Ing_Ksiegowosc_Admin::get_table_name_invoices(), $id ) ) ) {
			return;
		}

		$status_list = [
			self::WC_STATE_COMPLETED => self::WC_STATE_COMPLETED,
		];

		if ( ! isset( $status_list[ $status_to ] ) ) {
			return;
		}

		include_once dirname( dirname( __FILE__ ) ) . '/sdk/idk-core/src/Api.php';
		include_once dirname( dirname( __FILE__ ) ) . '/sdk/idk-core/src/Invoice.php';

		$generated_uuid = wp_generate_uuid4();

		$invoice = new Invoice( $order->get_total(), $order->get_currency(), $generated_uuid, Invoice::S_WOOCOMMERCE );

		$fullname = $order->get_billing_company()
			?: sprintf( "%s %s", $order->get_billing_first_name(), $order->get_billing_last_name() );

		/** @noinspection PhpTernaryExpressionCanBeReplacedWithConditionInspection */
		$tax_number = $order->get_meta( get_option( 'ing_ksiegowosc_tax_option_meta' ) )
			?: '';

		$type = Invoice::BUYER_PERSON;

		if ( $tax_number ) {
			$type = Invoice::BUYER_COMPANY;
		}

		$invoice->setBuyer(
			$type,
			$order->get_billing_email(),
			$fullname,
			$order->get_billing_address_1() . ' ' . $order->get_billing_address_2(),
			$order->get_billing_city(),
			$order->get_billing_postcode(),
			$order->get_billing_country(),
			'',
			$tax_number
		);

		// add this var to calc - usable when shipping tax is inherited.
		$shipping_rate = 0;

		/**
		 * @var WC_Order_Item_Product $item
		 */
		foreach ( $order->get_items() as $item ) {

			/**
			 * @var WC_Product $product
			 */
			$product = $item->get_product();

			$item_tax_class      = $item->get_tax_class();
			$item_tax_clas_lower = strtolower( $item_tax_class );
			$rate                = '';
			$basis               = '';

			if ( $item_tax_clas_lower === Invoice::SHOP_TAX_EXEMPT ) {
				$rate = explode( '_', Invoice::TAX_EXEMPT )[1];
			}

			//if ( strpos( $item_tax_clas_lower, 'zw_' ) === 0 ) {
			//	$part = substr( $item_tax_clas_lower, strpos( $item_tax_clas_lower, '_' ) + 1 );
			//	if ( Invoice::getBasisExempt( $part ) ) {
			//		$basis = Invoice::getBasisExempt( $part );
			//	}
			//}

			if ( ! $rate ) {
				$rate             = 0;
				$tax_rate_product = current( WC_Tax::get_rates( $item_tax_class ) );
				if ( $tax_rate_product && isset( $tax_rate_product['rate'] ) ) {
					$rate = (float) $tax_rate_product['rate'];
				}
				// endregion
			}

			// endregion

			$amount_gross = (float) $item->get_total() + (float) $item->get_total_tax();

			$invoice->addItem(
				$product->get_id()
					?: $product->get_sku(),
				$item->get_name(),
				$amount_gross,
				$item->get_quantity(),
				constant( '\Ingk\Invoice::TAX_' . $rate ),
				false
			);
		}

		$shipping_methods = $order->get_shipping_methods();

		if ( $shipping_methods ) {

			foreach ( $shipping_methods as $shipping_method ) {

				$shipping_method_tax_class = $shipping_method->get_tax_class();

				$total = (float) $shipping_method->get_total();
				$tax   = (float) $shipping_method->get_total_tax();

				if ( $shipping_method_tax_class !== 'inherit' ) {

					$shipping_tax_class_rates = current( WC_Tax::get_rates( $shipping_method_tax_class ) );

					if ( $shipping_tax_class_rates && isset( $shipping_tax_class_rates['rate'] ) ) {

						$shipping_rate = $shipping_tax_class_rates['rate'];
					} else {
						if ( $tax > 0 ) {
							$shipping_rate = ( $tax / $total ) * 100;
						}
					}
				}

				$shipping_rate = constant( '\Ingk\Invoice::TAX_' . round( $shipping_rate ) );

				$invoice->addItem(
					$shipping_method->get_method_id(),
					$shipping_method->get_method_title(),
					( $total + $tax ),
					1,
					$shipping_rate,
					true
				);
			}
		}

		$api            = new Api( $api_key );
		$create_invoice = $api->createInvoice( $invoice->get() );

		if ( ! $create_invoice['success'] || empty( $create_invoice['data']['id'] ) ) {
			error_log( 'ING Ksiegowosc: something went wrong with create invoice: ' . $create_invoice['data'] );

			return;
		}

		$time = time();

		$wpdb->insert( Ing_Ksiegowosc_Admin::get_table_name_invoices(), [
			'order_id'       => $id,
			'invoice_id'     => $create_invoice['data']['id'],
			'uuid_generated' => $generated_uuid,
			'created'        => $time,
			'modified'       => $time,
		] );
	}

	/**
	 * @noinspection PhpUnused
	 * @return array
	 */
	public function ing_ksiegowosc_page_settings_link( $links ) {

		return array_merge(
			[
				'settings' => sprintf( '<a href="%s" aria-label="%s">%s</a>',
					admin_url( 'admin.php?page=ing-ksiegowosc-settings' ),
					esc_attr__( 'View WooCommerce settings', 'woocommerce' ),
					esc_html__( 'Settings', 'woocommerce' ) ),
			],
			$links
		);
	}

	/**
	 * @noinspection PhpUnused
	 * @return void
	 */
	public function ing_ksiegowosc_admin_menu() {
		add_menu_page(
			$this->plugin_name,
			esc_html__( 'ING Księgowość', 'ing-ksiegowosc' ),
			'administrator',
			$this->plugin_name,
			[
				$this,
				'display_plugin_admin_dashboard',
			],
			'dashicons-pdf',
			58
		);

		$e_review_invoice = esc_html__( 'Review invoices', 'ing-ksiegowosc' );

		add_submenu_page(
			$this->plugin_name,
			$e_review_invoice,
			$e_review_invoice,
			'administrator',
			$this->plugin_name,
			[
				$this,
				'display_plugin_admin_dashboard',
			]
		);

		$e_settings = esc_html__( 'Settings', 'ing-ksiegowosc' );

		add_submenu_page(
			$this->plugin_name,
			$e_settings,
			$e_settings,
			'administrator',
			$this->plugin_name . '-settings',
			[
				$this,
				'display_plugin_admin_settings',
			]
		);
	}

	/**
	 * @return void
	 */
	public function display_plugin_admin_dashboard() {
		require_once sprintf( 'partials/%s-admin-display.php', $this->plugin_name );
	}

	/**
	 * @return void
	 */
	public function display_plugin_admin_settings() {

		if ( isset( $_GET['error_message'] ) && $_GET['error_message'] ) {
			add_action( 'admin_notices', [
				$this,
				'ing_ksiegowosc_settings_messages',
			] );
			do_action( 'admin_notices', sanitize_text_field( $_GET['error_message'] ) );
		}
		require_once sprintf( 'partials/%s-admin-settings-display.php', $this->plugin_name );
	}

	/**
	 * @param string $error_message
	 *
	 * @return void
	 */
	public function ing_ksiegowosc_settings_messages( $error_message ) {
		switch ( $error_message ) {
			case '1':
				$message       = esc_html__( 'There was an error adding this setting. Please try again. If this persists, shoot us an email.', 'ing-ksiegowosc' );
				$err_code      = esc_attr( 'plugin_name_example_setting' );
				$setting_field = 'plugin_name_example_setting';
				break;
			default:
				return;
		}

		add_settings_error(
			$setting_field,
			$err_code,
			$message,
			'error'
		);
	}

	/**
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function ing_ksiegowosc_register_and_build_fields() {

		add_settings_section(
			$this->plugin_name . '_settings',
			'',
			'',
			$this->plugin_name . '_settings'
		);
		unset( $args );

		$fields = [
			[
				'name'    => $this->generate_option_name( 'api_key' ),
				'title'   => esc_html__( 'API key', 'ing-ksiegowosc' ),
				'type'    => 'input',
				'subtype' => 'text',
				'tooltip' => esc_html__( 'Paste here an API Key from ING Księgowość. After logging in, go to the Data & Settings tab > Integrations > API Key.', 'ing-ksiegowosc' ),
			],
			[
				'name'    => $this->generate_option_name( 'tax_option_meta' ),
				'title'   => esc_html__( 'Tax option name', 'ing-ksiegowosc' ),
				'type'    => 'input',
				'subtype' => 'text',
				'tooltip' => esc_html__( 'If companies buy from you, they must receive an invoice for this along with the buyer\'s VAT number. Install the Flexible Checkout plugin – in its settings you will find the value that needs to be pasted into this field. Usually, this is "_billing_vat_number". Please note that if you do not do this, your business customers will not be able to provide their tax identification number (NIP) during the purchase process.', 'ing-ksiegowosc' ),
			],
		];

		$page = $section = $this->plugin_name . '_settings';
		foreach ( $fields as $field ) {
			add_settings_field(
				$field['name'],
				$field['title'],
				[
					$this,
					'ing_ksiegowosc_render_settings_field',
				],
				$page,
				$section,
				$this->get_option_args( $field['name'], $field['type'], $field['subtype'], $field['tooltip'] )
			);

			register_setting(
				$this->plugin_name . '_settings',
				$field['name'],
				[
					$this,
					'sanitize_whitespace',
				]
			);
		}
	}

	/**
	 * @param string $input
	 *
	 * @return array|string|string[]|null
	 */
	public function sanitize_whitespace( $input ) {
		return preg_replace( '/\s+/', '', $input );
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	private function generate_option_name( $name ) {
		return sprintf( 'ing_ksiegowosc_%s', $name );
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $subtype
	 * @param string $required
	 * @param string $id
	 * @param string $get_options_list
	 * @param string $value_type
	 * @param string $wp_data
	 *
	 * @return array
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function get_option_args(
		$name,
		$type = 'input',
		$subtype = 'text',
		$tooltip = '',
		$required = false,
		$id = '',
		$get_options_list = '',
		$value_type = 'normal',
		$wp_data = 'option'
	) {

		if ( ! $id ) {
			$id = $name;
		}

		return [
			'type'             => $type,
			'subtype'          => $subtype,
			'id'               => $id,
			'name'             => $name,
			'required'         => $required
				? 'required="required"'
				: '',
			'get_options_list' => $get_options_list,
			'value_type'       => $value_type,
			'wp_data'          => $wp_data,
			'tooltip'          => $tooltip,
		];
	}

	/**
	 * Example arg
	 *
	 * 'type'            = 'input',
	 * 'subtype'         = '',
	 * 'id'              = $this->plugin_name.'_example_setting',
	 * 'name'            = $this->plugin_name.'_example_setting',
	 * 'required'        = 'required="required"',
	 * 'get_option_list' = "",
	 * 'value_type'      = 'serialized' || 'normal',
	 * 'wp_data'         = 'option' || 'post_meta',
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function ing_ksiegowosc_render_settings_field( $args ) {


		if ( empty( $args['wp_data'] ) ) {
			return;
		}

		switch ( $args['wp_data'] ) {
			case 'option':
				$wp_data_value = get_option( $args['name'] );
				break;
			case 'post_meta':
				$wp_data_value = get_post_meta( $args['post_id'], $args['name'], true );
				break;
			default:
				return;
		}

		switch ( $args['type'] ) {

			case 'input':

				$value = $args['value_type'] != 'serialized'
					? $wp_data_value
					: serialize( $wp_data_value );
				switch ( $args['subtype'] ) {
					case 'checkbox':
						$checked = ( $value )
							? 'checked'
							: '';
						echo wp_kses( '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . $args['required'] . '" name="' . $args['name'] . '" size="40" value="1" ' . $checked . ' />', [
							'div'   => [
								'class' => [],
							],
							'span'  => [
								'class' => [],
								'id'    => [],
							],
							'input' => [
								'type'  => [],
								'id'    => [],
								'name'  => [],
								'size'  => [],
								'value' => [],

								'required' => [],
							],
						] );
						break;
					default:

						$tooltip = $args['tooltip']
							? sprintf( '<div class="ing-ksiegowosc-hover-text"><span class="dashicons dashicons-info"></span><span class="ing-ksiegowosc-tooltip-text" id="ing-ksiegowosc-bottom">%s</span></div>', $args['tooltip'] )
							: '';

						$prepend_start = $tooltip . ( ( isset( $args['prepend_value'] ) )
								? '<div class="input-prepend"> <span class="add-on">' . $args['prepend_value'] . '</span>'
								: '' );
						$prepend_end   = ( isset( $args['prepend_value'] ) )
							? '</div>'
							: '';
						$step          = ( isset( $args['step'] ) )
							? 'step="' . $args['step'] . '"'
							: '';
						$min           = ( isset( $args['min'] ) )
							? 'min="' . $args['min'] . '"'
							: '';
						$max           = ( isset( $args['max'] ) )
							? 'max="' . $args['max'] . '"'
							: '';
						if ( isset( $args['disabled'] ) ) {
							$s = $prepend_start . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '_disabled" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '_disabled" size="40" disabled value="' . esc_attr( $value ) . '" /><input type="hidden" id="' . $args['id'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr( $value ) . '" />' . $prepend_end;
						} else {
							$s = $prepend_start . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" ' . ( $args["required"]
									? 'required="required"'
									: '' ) . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr( $value ) . '" />' . $prepend_end;
						}

						echo wp_kses( $s, [
							'div'   => [
								'class' => [],
							],
							'span'  => [
								'class' => [],
								'id'    => [],
							],
							'input' => [
								'type'  => [],
								'id'    => [],
								'name'  => [],
								'size'  => [],
								'value' => [],

								'required' => [],
							],
						] );
						break;
				}
				break;
			default:
				break;
		}
	}
}
