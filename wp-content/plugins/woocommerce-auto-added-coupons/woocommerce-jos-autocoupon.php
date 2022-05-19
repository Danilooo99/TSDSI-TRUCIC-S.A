<?php
/**
 * Plugin Name: WooCommerce Extended Coupon Features FREE
 * Plugin URI: http://www.soft79.nl
 * Description: Additional functionality for WooCommerce Coupons.
 * Version: 3.2.7
 * Text Domain: woocommerce-jos-autocoupon
 * Author: Soft79
 * License: GPL2
 * WC requires at least: 3.0.0
 * WC tested up to: 5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! defined( 'WJECF_VERSION' ) ) {
	define( 'WJECF_VERSION', '3.2.7' );
}

// NOTE: This file must be compatible with old PHP versions. All other files can be PHP 5.6+ .
if ( ! function_exists( 'wjecf_load_plugin_textdomain' ) ) {
	// We must define wjecf_load_plugin_textdomain() so that versions prior to 3.0 detect this plugin instance.
	function wjecf_load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce' );
		load_textdomain(
			'woocommerce-jos-autocoupon',
			WP_LANG_DIR . '/woocommerce-jos-autocoupon/woocommerce-jos-autocoupon-' . $locale . '.mo'
		);
		load_plugin_textdomain( 'woocommerce-jos-autocoupon', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	// The plugins_loaded action hook fires early,
	// it precedes the setup_theme, after_setup_theme, init and wp_loaded action hooks.
	add_action( 'plugins_loaded', 'wjecf_action_plugins_loaded' );
	function wjecf_action_plugins_loaded() {
		wjecf_load_plugin_textdomain();

		try {
			$requirements = array(
				array(
					'program'          => 'PHP',
					'required_version' => '5.6',
					'current_version'  => phpversion(),
				),
				array(
					'program'          => 'WooCommerce',
					'required_version' => '3.0',
					'current_version'  => is_callable( 'wc' ) && isset( wc()->version ) ? wc()->version : null,
				),
				array(
					'program'          => 'WordPress',
					'required_version' => '4.9',
					'current_version'  => $GLOBALS['wp_version'],
				),
			);

			foreach ( $requirements as $req ) {
				if ( ! $req['current_version'] ) {
					/* translators: 1: program 2: version */
					$message = __( 'This plugin requires %1$s, please install it.', 'woocommerce-jos-autocoupon' );
					throw new Exception( sprintf( $message, $req['program'], $req['required_version'] ) );
				}
				if ( version_compare( $req['current_version'], $req['required_version'], '<' ) ) {
					/* translators: 1: program 2: version 3: version of WooCommerce Extended Coupon Features */
					$message = __(
						'This plugin requires %1$s version %2$s or higher. You are running version %3$s. Please update %1$s or install a version of WooCommerce Extended Coupon Features prior to %4$s.',
						'woocommerce-jos-autocoupon'
					);
					throw new Exception(
						sprintf( $message, $req['program'], $req['required_version'], $req['current_version'], '3.0' )
					);
				}
			}

			// Here we load WooCommerce Extended Coupon Features.
			require_once 'includes/class-wjecf-bootstrap.php';
			WJECF_Bootstrap::execute();
		} catch ( Exception $ex ) {
			$GLOBALS['wjecf_admin_notice'] = $ex->getMessage();
			add_action( 'admin_notices', 'wjecf_admin_notices' );
		}
	}
} else {
	$GLOBALS['wjecf_admin_notice'] = __(
		'Multiple instances of the plugin are detected. Please disable one of them.',
		'woocommerce-jos-autocoupon'
	);
	add_action( 'admin_notices', 'wjecf_admin_notices' );
}

if ( ! function_exists( 'wjecf_admin_notices' ) ) {
	function wjecf_admin_notices() {
		if ( ! isset( $GLOBALS['wjecf_admin_notice'] ) ) {
			return;
		}
		error_log( 'WJECF: ' . $GLOBALS['wjecf_admin_notice'] );

		echo '<div class="notice error">';
		echo '<p><strong>WooCommerce Extended Coupon Features</strong> &#8211; ';
		echo $GLOBALS['wjecf_admin_notice'];
		echo '</div>';
	}
}
