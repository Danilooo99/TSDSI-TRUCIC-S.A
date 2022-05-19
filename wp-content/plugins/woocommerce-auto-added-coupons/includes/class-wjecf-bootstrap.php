<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Loads the plugin
 */
class WJECF_Bootstrap {


	public static function execute() {
		$bootstrap = new self();
		$bootstrap->bootstrap();
	}

	protected function bootstrap() {
		$this->register_autoload();
		$this->register_global_functions();

		$this->require_once_php( 'includes/abstract-wjecf-plugin.php' );
		$pro = $this->try_include_php( 'pro/class-wjecf-pro-controller.php' );

		$this->load_plugin( 'includes/plugins/WJECF_Debug' );

		if ( WJECF()->is_request( 'admin' ) ) {
			$this->load_plugin( 'includes/plugins/WJECF_Admin' );
			$this->load_plugin( 'includes/plugins/WJECF_Admin_Settings' );
			$this->load_plugin( 'includes/plugins/WJECF_Admin_Data_Update' );
		}

		$this->load_plugin( 'includes/plugins/WJECF_Autocoupon' );
		$this->load_plugin( 'includes/plugins/WJECF_WPML' );

		if ( $pro ) {
			if ( WJECF()->is_request( 'admin' ) ) {
				$this->load_plugin( 'pro/plugins/WJECF_Pro_Admin_Auto_Update' );
			}
			$this->load_plugin( 'pro/plugins/WJECF_Pro_Free_Products/WJECF_Pro_Free_Products' );
			$this->load_plugin( 'pro/plugins/WJECF_Pro_Coupon_Queueing' );
			$this->load_plugin( 'pro/plugins/WJECF_Pro_Product_Filter' );
			$this->load_plugin( 'pro/plugins/WJECF_Pro_Limit_Discount_Quantities' );
		}

		//DEPRECATED. We keep $wjecf_extended_coupon_features for backwards compatibility; use WJECF_API()
		$GLOBALS['wjecf_extended_coupon_features'] = WJECF();

		WJECF()->start();

		//WP-cli for debugging
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WJECF_Debug_CLI::add_command();
		}
	}

	protected function register_autoload() {
		spl_autoload_register(
			function ( $class ) {
				$prefix = 'WJECF_';

				//Our class Prefix.
				if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
					return;
				}
				$file    = strtolower( 'class-' . str_replace( '_', '-', $class ) . '.php' );
				$subdirs = array( '/includes/', '/pro/' );
				foreach ( $subdirs as $subdir ) {
					$fullpath = dirname( __DIR__ ) . $subdir . $file;
					if ( file_exists( $fullpath ) ) {
						require( $fullpath );
						return;
					}
				}
			}
		);
	}

	protected function register_global_functions() {
		/**
		 * Get the instance of WJECF
		 * @return WJECF_Controller|WJECF_Pro_Controller The instance of WJECF
		 */
		function wjecf() {
			return WJECF_Controller::instance();
		}

		/**
		 * Get the instance of WJECF_Admin
		 * @return WJECF_Admin The instance of WJECF_Admin
		 */
		function wjecf_admin() {
			return WJECF()->get_plugin( 'admin' );
		}

		/**
		 * Get the instance of WJECF_WC
		 * @return WJECF_WC The instance of WJECF_WC
		 */
		function wjecf_wc() {
			return WJECF_WC::instance();
		}

		/**
		 * Get the instance if the WooCommerce Extended Coupon Features API
		 * @return WJECF_Pro_API The API object
		 */
		function wjecf_api() {
			return WJECF_Pro_API::instance();
		}

		/**
		 * Wraps a product or coupon in a decorator
		 *
		 * @deprecated 3.0.0
		 * @param mixed $object The WC_Coupon or WC_Product instance, or the post id
		 * @return WJECF_Wrap
		 */
		function wjecf_wrap( $object ) {
			return WJECF_WC::instance()->wrap( $object );
		}
	}

	/**
	 * require_once( $path + '.php' )
	 * @param string $path Path to the php file (excluding extension) relative to the current path
	 * @return bool True if succesful
	 */
	protected function require_once_php( $path ) {
		require_once( $this->get_path( $path ) );
	}

	/**
	 * tries to include_once( $path + '.php' )
	 * @param string $path Path to the php file (excluding extension) relative to the current path
	 * @return bool True if succesful
	 */
	protected function try_include_php( $path ) {
		$fullpath = $this->get_path( $path );

		if ( ! file_exists( $fullpath ) ) {
			return false;
		}

		include_once( $fullpath );
		return true;
	}

	/**
	 * Loads the class file and adds the plugin to WJECF(). Throws exception on failure.
	 * @param string $path Path to the php file (excluding extension) relative to the current path
	 * @return void
	 */
	protected function load_plugin( $path ) {
		$class_name = basename( $path );

		//Load disabled plugins only if they are enabled or on the admin page
		if ( ! is_admin() && in_array( $class_name, WJECF()->get_option( 'disabled_plugins' ) ) ) {
			return;
		}

		$this->try_include_php( $path . '.php' );

		if ( ! WJECF()->add_plugin( new $class_name() ) ) {
			throw new Exception( sprintf( 'Unable to add plugin %s', $class_name ) );
		}
	}

	/**
	 * Gets the path relative to the includes/ directory
	 * @param string $path
	 * @return string
	 */
	private function get_path( $path ) {
		return dirname( __DIR__ ) . '/' . $path;
	}
}
