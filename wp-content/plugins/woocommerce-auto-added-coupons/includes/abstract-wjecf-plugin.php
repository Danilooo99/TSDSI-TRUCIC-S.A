<?php /* phpcs:ignore */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

abstract class Abstract_WJECF_Plugin {

	//Override these functions in the WJECF plugin

	public function init_hook() {
	}

	public function init_admin_hook() {
	}

	/**
	 * Returns an array with the meta_keys for this plugin and the sanitation to apply.
	 * Instead of a sanitation a callback can be supplied; which must return the meta value to save to the database
	 *
	 * e.g. [
	 *  '_wjecf_some_comma_separated_ints' => 'int,',
	 *  '_wjecf_some_callback' => [ 'callback' => [ callback ] ],
	 * ]
	 *
	 * @param null|WC_Coupon $coupon Note: Can be null
	 * @return array The fields for this plugin
	 */
	public function admin_coupon_meta_fields( $coupon ) {
		return array();
	}

	/**
	 * Asserts that all dependencies are respected. If not an Exception is thrown. Override this function for extra assertions (e.g. minimum plugin versions)
	 * @return void
	 */
	public function assert_dependencies() {
		foreach ( $this->get_plugin_dependencies() as $dependency ) {
			$plugin = WJECF()->get_plugin( $dependency );
			if ( ! $plugin || ! $plugin->plugin_is_enabled() ) {
				throw new Exception( sprintf( 'Missing dependency %s', $dependency ) );
			}
		}

		if ( ! empty( $this->plugin_data['minimal_wjecf_version'] ) ) {
			$this->assert_wjecf_version( $this->plugin_data['minimal_wjecf_version'] );
		}
	}

	/**
	 * Assert minimum WJECF version number
	 * @param string $required_version
	 * @return void
	 */
	protected function assert_wjecf_version( $required_version ) {
		if ( version_compare( WJECF()->plugin_version(), $required_version, '<' ) ) {
			/* translators: 1: required version 2: current version */
			throw new Exception( sprintf( __( 'WooCommerce Extended Coupon Features version %1$s is required. You have version %2$s', 'woocommerce-jos-autocoupon' ), $required_version, WJECF()->plugin_version() ) );
		}
	}

	//

	/**
	 * Log a message (for debugging)
	 *
	 * @param string $message The message to log
	 *
	 */
	protected function log( $level, $message = null ) {
		//Backwards compatibility; $level was introduced in 2.4.4
		if ( is_null( $message ) ) {
			$message = $level;
			$level   = 'debug';
		}
		WJECF()->log( $level, $message, 1 );
	}

	private $plugin_data = null;

	/**
	 *  Information about the WJECF plugin
	 * @param string|null $key The data to look up. Will return an array with all data when omitted
	 * @return mixed
	 */
	protected function get_plugin_data( $key = null ) {
		if ( ! isset( $this->plugin_data ) ) {
			$this->set_plugin_data();
		}

		if ( null === $key ) {
			return $this->plugin_data;
		}

		return isset( $this->plugin_data[ $key ] ) ? $this->plugin_data[ $key ] : $this->default_data[ $key ];
	}

	/**
	 *  Set information about the WJECF plugin
	 * @param array $plugin_data The data for this plugin
	 * @return void
	 */
	protected function set_plugin_data( $plugin_data = array() ) {
		$default_data = array(
			'description'           => '',
			'can_be_disabled'       => false,
			'hidden'                => false, // Does not display on settings page
			'dependencies'          => array(),
			'admin_dependencies'    => array(), // Dependencies if admin
			'minimal_wjecf_version' => '',
			'name'                  => self::sanitize_plugin_name( get_class( $this ) ),
		);

		$this->plugin_data = array_merge( $default_data, $plugin_data );
	}

	/**
	 *  Get the description of this WJECF plugin.
	 * @return string
	 */
	public function get_plugin_description() {
		return $this->get_plugin_data( 'description' );
	}

	/**
	 *  Get the identifier of this WJECF plugin.
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->get_plugin_data( 'name' );
	}

	/**
	 *  Get the class name of this WJECF plugin.
	 * @deprecated 3.0.0
	 * @return string
	 */
	public function get_plugin_class_name() {
		_deprecated_function( 'comma_separated_int_array', '3.0.0', 'Abstract_WJECF_Plugin::get_plugin_name()' );
		return get_class( $this );
	}

	public function get_plugin_dependencies() {
		$dependencies = $this->get_plugin_data( 'dependencies' );
		if ( WJECF()->is_request( 'admin' ) ) {
			$dependencies = array_merge( $dependencies, $this->get_plugin_data( 'admin_dependencies' ) );
		}
		return $dependencies;
	}

	public function plugin_is_enabled() {
		if ( ! $this->get_plugin_data( 'can_be_disabled' ) ) {
			return true;
		}
		return ! in_array( $this->get_plugin_name(), WJECF()->get_option( 'disabled_plugins' ) );
	}

	/**
	 * The default plugin_name for the given class name
	 * It removes WJECF_ prefix and replace '_' by '-'. e.g. 'WJECF_Admin_Settings' -> 'admin-settings'
	 *
	 * @since 3.0.0
	 * @param string $class_name
	 * @return string
	 */
	public static function sanitize_plugin_name( $class_name ) {
		return sanitize_title( preg_replace( array( '/^WJECF_/', '/_/' ), array( '', '-' ), $class_name ) );
	}

}
