<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Accommodation Bookings class
 */
class WC_Accommodation_Bookings_Plugin {

	/**
	 * Main plugin's file.
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Plugin's version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Dependencies check result.
	 *
	 * @var bool|WP_Error
	 */
	public $dependencies_check_result;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Path to main plugin's file
	 * @param string $version     Plugin's version
	 */
	public function __construct( $plugin_file, $version ) {
		$this->plugin_file = $plugin_file;
		$this->version     = $version;
	}

	/**
	 * Run the plugin.
	 */
	public function run() {
		$this->_define_constants();
		$this->_register_hooks();
	}

	/**
	 * Handles additional tasks when product is duplicated.
	 *
	 * @since 1.1.14
	 * @param  WC_Product $new_product Duplicated product.
	 * @param  WC_Product $product     Original product.
	 * @return void
	 */
	public function woocommerce_duplicate_product( $new_product, $product ) {
		if ( $product->is_type( 'accommodation-booking' ) ) {
			// Clone and re-save person types.
			foreach ( $product->get_person_types() as $person_type ) {
				$dupe_person_type = clone $person_type;
				$dupe_person_type->set_id( 0 );
				$dupe_person_type->set_parent_id( $new_product->get_id() );
				$dupe_person_type->save();
			}
		}
	}

	/**
	 * Define plugin's constants.
	 *
	 * @return void
	 */
	private function _define_constants() {
		define( 'WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH', untrailingslashit( plugin_dir_path( $this->plugin_file ) ) . '/includes/' );
		define( 'WC_ACCOMMODATION_BOOKINGS_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( $this->plugin_file ) ) . '/templates/' );
		define( 'WC_ACCOMMODATION_BOOKINGS_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( $this->plugin_file ) ), basename( $this->plugin_file ) ) ) );
		define( 'WC_ACCOMMODATION_BOOKINGS_MAIN_FILE', $this->plugin_file );
	}

	/**
	 * Register to hooks.
	 *
	 * @return void
	 */
	private function _register_hooks() {
		register_activation_hook( $this->plugin_file, array( $this, 'check_dependencies' ) );
		add_action( 'plugins_loaded', array( $this, 'check_dependencies' ) );

		if ( is_wp_error( $this->check_dependencies() ) ) {
			return;
		}

		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'includes' ), 20 );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'booking_form_styles' ) );

		if ( is_admin() ) {
			add_action( 'init', array( $this, 'admin_includes' ), 10 );
			add_action( 'woocommerce_product_duplicate', array( $this, 'woocommerce_duplicate_product' ), 10, 2 );
		}

		add_action( 'shutdown', array( $this, 'install' ) );
	}

	/**
	 * Check dependencies.
	 *
	 * @return bool|WP_Error Returns true if dependencies are satisfied. Otherwise error.
	 */
	public function check_dependencies() {
		if ( $this->dependencies_check_result ) {
			return $this->dependencies_check_result;
		}

		require_once( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-accommodation-dependencies.php' );
		try {
			WC_Accommodation_Dependencies::check_dependencies();
			$this->dependencies_check_result = true;
		} catch ( Exception $e ) {
			if ( function_exists( 'deactivate_plugins' ) ) {
				deactivate_plugins( plugin_basename( $this->plugin_file ) );
			}

			$this->dependencies_check_result = new WP_Error( 'unsatisfied_dependencies', $e->getMessage() );
			add_action( 'admin_notices', array( $this, 'deactivate_notice' ) );
		}

		return $this->dependencies_check_result;
	}


	/**
	 * Admin notice when plugin is automatically deactivated.
	 *
	 * @return void
	 */
	public function deactivate_notice() {
		if ( is_wp_error( $this->dependencies_check_result ) ) {
			$error_message = esc_html( $this->dependencies_check_result->get_error_message() );
			echo wp_kses_post( sprintf( '<div class="error">%s %s</div>', wpautop( $error_message ), wpautop( 'Plugin <strong>deactivated</strong>.' ) ) );
		}
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-accommodation-bookings' );
		$dir    = trailingslashit( WP_LANG_DIR );

		load_textdomain( 'woocommerce-accommodation-bookings', $dir . 'woocommerce-accommodation-bookings/woocommerce-accommodation-bookings-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-accommodation-bookings', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' );
	}

	/**
	 * Load Classes
	 */
	public function includes() {
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-product-accommodation-booking.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-product-accommodation-booking-resource.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-accommodation-booking.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-accommodation-booking-cart-manager.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-accommodation-booking-date-picker.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-accommodation-booking-product-tabs.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'class-wc-accommodation-booking-order-manager.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'integrations/class-wc-accommodation-booking-addons.php' );
	}

	/**
	 * Include admin
	 */
	public function admin_includes() {
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'admin/class-wc-accommodation-booking-admin-panels.php' );
		include( WC_ACCOMMODATION_BOOKINGS_INCLUDES_PATH . 'admin/class-wc-accommodation-booking-admin-product-settings.php' );
	}

	/**
	 * Frontend booking form scripts
	 */
	public function booking_form_styles() {
		wp_enqueue_style( 'wc-accommodation-bookings-styles', WC_ACCOMMODATION_BOOKINGS_PLUGIN_URL . '/assets/css/frontend.css', null, WC_ACCOMMODATION_BOOKINGS_VERSION );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @access	public
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file == plugin_basename( WC_ACCOMMODATION_BOOKINGS_MAIN_FILE ) ) {
			$row_meta = array(
				'docs'		=>	'<a href="' . esc_url( apply_filters( 'woocommerce_accommodation_bookings_docs_url', 'https://docs.woocommerce.com/document/woocommerce-accommodation-bookings/' ) ) . '" title="' . esc_attr( __( 'View Documentation', 'woocommerce-accommodation-bookings' ) ) . '">' . __( 'Docs', 'woocommerce-accommodation-bookings' ) . '</a>',
				'support'	=>	'<a href="' . esc_url( apply_filters( 'woocommerce_accommodation_bookings_support_url', 'https://docs.woocommerce.com/' ) ) . '" title="' . esc_attr( __( 'Visit Premium Customer Support Forum', 'woocommerce-accommodation-bookings' ) ) . '">' . __( 'Premium Support', 'woocommerce-accommodation-bookings' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Installer
	 */
	public function install() {
		global $wpdb;

		$force_update = false;
		$accommodation_bookings_version = get_option( 'wc_accommodation_bookings_version' );

		if ( ! $accommodation_bookings_version ) {
			$force_update = true;
			$accommodation_bookings_version = $this->version;
		}

		// Data updates
		if ( $force_update || version_compare( $accommodation_bookings_version, '1.1.3', '<' ) ) {
			$accommodation_bookings = $wpdb->get_results( "SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = '_wc_booking_pricing' AND meta_value LIKE '%override_block%';" );
			foreach ( $accommodation_bookings as $accommodation_booking ) {
				$product = wc_get_product( $accommodation_booking->post_id );

				if ( ! is_a( $product, 'WC_Product' ) ) {
					continue;				
				}

				if ( 'accommodation-booking' != $product->get_type() ) {
					continue;
				}

				$pricing = get_post_meta( $product->get_id(), '_wc_booking_pricing', true );
				$original_base_cost = absint( get_post_meta( $product->get_id(), '_wc_booking_base_cost', true ) );

				// Convert from the old to the new structure
				foreach ( $pricing as &$pricing_row ) {
					$pricing_row['base_cost'] = $pricing_row['cost'] = 0;
					$new_cost = $pricing_row['override_block'];
					unset( $pricing_row['override_block'] );
					$pricing_row['base_modifier'] = $pricing_row['modifier'] = $new_cost > $original_base_cost ? 'plus' : 'minus';
					$pricing_row['cost'] = absint( $new_cost - $original_base_cost );
				}

				update_post_meta( $product->get_id(), '_wc_booking_pricing', $pricing );
			}
		}

		// Update version
		update_option( 'wc_accommodation_bookings_version', $this->version );
	}
}
