<?php
/**
 * Plugin Name: XPlainer - WooCommerce Product FAQ
 * Plugin URI: https://profiles.wordpress.org/wpfeelteam/
 * Description: This plugin shows faq question and answers for per product with comment and FAQ schema support.
 * Version: 1.3.31
 * Author: WPFeel
 * Author URI: https://profiles.wordpress.org/wpfeelteam/
 * Text Domain: faq-for-woocommerce
 * Domain Path: /i18n/languages/
 *
 * WP Requirement & Test
 * Requires at least: 4.4
 * Tested up to: 5.9
 * Requires PHP: 5.6
 *
 * WC Requirement & Test
 * WC requires at least: 3.2
 * WC tested up to: 6.3
 *
 * @package FAQ_Woocommerce
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'FFW_VERSION' ) ) {
	/**
	 * Plugin Version
	 * @var string
	 * @since 1.0.0
	 */
	define( 'FFW_VERSION', '1.3.31' );
}

if ( ! defined( 'FFW_FILE' ) ) {
    define( 'FFW_FILE', __FILE__ );
}

if ( ! defined( 'FFW_BASENAME' ) ) {
    define( 'FFW_BASENAME', plugin_basename(__FILE__) );
}

if ( ! defined( 'FFW_FILE_DIR' ) ) {
    define( 'FFW_FILE_DIR', dirname( __FILE__ ) );
}

if ( ! defined( 'FFW_PLUGIN_URL' ) ) {
    define( 'FFW_PLUGIN_URL', plugins_url( '', FFW_FILE ) );
}

if ( ! defined( 'FFW_MIN_WC_VERSION' ) ) {
    /**
     * Minimum WooCommerce Version Supported
     * 
     * @var string
     * @since 1.0.0
     */
    define( 'FFW_MIN_WC_VERSION', '3.2' );
}

// Include the main FAQ_Woocommerce class.
include_once FFW_FILE_DIR . '/includes/class-faq-woocommerce.php';


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_faq_for_woocommerce() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
        require_once __DIR__ . '/lib/tracker/appsero/src/Client.php';
    }

    $client = new Appsero\Client( 'c989292b-3c3e-454a-a216-cc0df37fb6d9', 'XPlainer – WooCommerce Product FAQ', __FILE__ );

    // Active insights
    $client->insights()->init();

}

appsero_init_tracker_faq_for_woocommerce();

/**
 * Returns the main instance of FAQ_Woocommerce.
 *
 * @since  1.0.0
 * @return FAQ_Woocommerce
 */
function FAQ_Woocommerce_init() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
    return FAQ_Woocommerce::instance();
}

// Global for backwards compatibility.
$GLOBALS['faq_woocommerce_init'] = FAQ_Woocommerce_init();