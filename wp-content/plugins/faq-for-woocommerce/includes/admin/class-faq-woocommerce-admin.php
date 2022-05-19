<?php
/**
 * FAQ Woocommerce Admin
 *
 * @class    FAQ_Woocommerce_Admin
 * @package  FAQ_Woocommerce\Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_Admin class.
 */
class FAQ_Woocommerce_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'init', array( $this, 'shutdown' ));
        add_filter( 'plugin_action_links_' . FFW_BASENAME,  array( $this, 'ffw_plugin_action_links' ) );

        //add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
        //add_filter( 'update_footer', array( $this, 'admin_footer_version'), '', 9999 );
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        include_once dirname( __FILE__ ) . '/faq-woocommerce-admin-functions.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-admin-menus.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-admin-notices.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-admin-assets.php';
        
        //Setting page
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-settings.php';
    }

    /**
     * After all files loaded
     */
    public function shutdown() {
        $options = get_option( 'ffw_general_settings' );
        $options = ! empty( $options ) ? $options : [];
        $ffw_counter = isset( $options['ffw_hide_faq_number_for_product'] ) ? $options['ffw_hide_faq_number_for_product'] : "1";

        //remove filter to hide faq count column from product list table
        if( isset($ffw_counter) & "2" === $ffw_counter ) {
            remove_filter( 'manage_product_posts_columns', 'ffw_set_custom_faq_count_column' );
        }

    }

    /**
     * Change the admin footer text on woocommerce-faq admin pages.
     *
     * @since  1.0.0
     * @param  string $footer_text text to be rendered in the footer.
     * @return string
     */
    public function admin_footer_text( $footer_text ) {
        if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wc_get_screen_ids' ) ) {
            return $footer_text;
        }

        $footer_text = __( 'Thank you for using XPlainer – WooCommerce Product FAQ.', 'faq-for-woocommerce' );

        return $footer_text;
    }


    /**
     * Filter plugin action links
     *
     * @since  1.3.16
     * @param  array  $links List of existing plugin action links.
     * @return array         List of modified plugin action links
     */
    public function ffw_plugin_action_links($links) {
        $links = array_merge( array(
            '<a href="' . esc_url( 'https://wpfeel.net/docs/faq-for-woocommerce/' ) . '">' . __( 'Documentation', 'faq-for-woocommerce' ) . '</a>'
        ), $links );

        return $links;
    }

    public function admin_footer_version() {

    	$footer_version = sprintf( '<span class="ffw-admin-footer-version">Version: %s</span>', esc_html__( FFW_VERSION , 'faq-for-woocommerce') );

    	echo $footer_version;

    	return;
    }
}

return new FAQ_Woocommerce_Admin();
