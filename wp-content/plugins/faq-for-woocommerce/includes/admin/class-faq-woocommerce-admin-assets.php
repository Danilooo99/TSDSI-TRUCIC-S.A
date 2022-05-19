<?php
/**
 * Load assets
 *
 * @package FAQ_Woocommerce\Admin
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FAQ_Woocommerce_Admin_Assets', false ) ) :

    /**
     * FAQ_Woocommerce_Admin_Assets Class.
     */
    class FAQ_Woocommerce_Admin_Assets {

        /**
         * Hook in tabs.
         */
        public function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 999 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 999 );
        }

        /**
         * Enqueue styles.
         */
        public function admin_styles($needle) {

            // Register admin styles.
            wp_register_style( 'ffw_bootstrap', FFW_PLUGIN_URL . '/assets/admin/css/bootstrap.min.css', array(), FFW_PLUGIN_VERSION );
            wp_register_style( 'ffw_select2_styles', FFW_PLUGIN_URL . '/assets/admin/css/ffw-select2.min.css', array(), FFW_PLUGIN_VERSION );
            wp_register_style( 'ffw_admin_menu_styles', FFW_PLUGIN_URL . '/assets/admin/css/faq-woocommerce-admin.min.css', array(), FFW_PLUGIN_VERSION );
            wp_register_style( 'ffw_admin_popup_styles', FFW_PLUGIN_URL . '/assets/admin/css/faq-woocommerce-popup.min.css', array(), FFW_PLUGIN_VERSION );

            

            // Add RTL support for admin styles.
            //wp_style_add_data( 'ffw_admin_menu_styles', 'rtl', 'replace' );

            // enqueue CSS.
            if( "ffw_page_woocommerce-faq" === $needle ) {
                wp_enqueue_style( 'ffw_bootstrap' );
            }
            wp_enqueue_style( 'ffw_admin_menu_styles' );

            if ( isset($_GET['post']) && isset($_GET['action']) && 'edit' === $_GET['action'] ) {
                wp_enqueue_style( 'ffw_select2_styles' );
                wp_enqueue_style( 'ffw_admin_popup_styles' );
            } elseif( isset($_GET['page']) && 'woocommerce-faq' === $_GET['page'] ) {
                wp_enqueue_style( 'ffw_select2_styles' );
            }
        }


        /**
         * Enqueue scripts.
         */
        public function admin_scripts($needle) {
            global $wp_query, $post;

            // Add the color picker css file
            wp_enqueue_style( 'wp-color-picker' );


            $post_type = '';
            $page_action = '';
            if( isset($_GET['post']) && isset($_GET['action']) ) {
                $post_type = get_post_type($_GET['post']);
                $page_action = wp_unslash($_GET['action']);
            }

            // Register scripts.
            wp_register_script( 'ffw_bootstrap_js', FFW_PLUGIN_URL . '/assets/admin/js/bootstrap.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), FFW_PLUGIN_VERSION, true );
            wp_register_script( 'ffw_sweetalert', FFW_PLUGIN_URL . '/assets/admin/js/ffw-sweetalert.all.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), FFW_PLUGIN_VERSION, true );
            wp_register_script( 'ffw_select2_js', FFW_PLUGIN_URL . '/assets/admin/js/ffw-select2.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), FFW_PLUGIN_VERSION, true );
            wp_register_script( 'ffw_admin', FFW_PLUGIN_URL . '/assets/admin/js/faq-woocommerce-admin.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'wp-color-picker' ), FFW_PLUGIN_VERSION, true );
			wp_localize_script( 'ffw_admin', 'ffw_admin', [
				'ajaxurl'               => admin_url( 'admin-ajax.php' ),
				'nonce'                 => wp_create_nonce( 'ffw_admin' ),
                'current_post_type'     => $post_type,
                'current_page_action'   => $page_action,
			] );



            //Enqueue scripts
            if( "ffw_page_woocommerce-faq" === $needle ) {
                wp_enqueue_script( 'ffw_bootstrap_js' );
            }

            if ( isset($_GET['post']) && isset($_GET['action']) && 'edit' === $_GET['action'] ) {
                wp_enqueue_script( 'ffw_select2_js' );
                wp_enqueue_script( 'ffw_sweetalert' );
                wp_enqueue_script( 'ffw_admin' );
            } elseif( isset($_GET['page']) && 'woocommerce-faq' === $_GET['page'] ) {
                wp_enqueue_script( 'ffw_select2_js' );
                wp_enqueue_script( 'ffw_admin' );
            }


        }

    }

endif;

return new FAQ_Woocommerce_Admin_Assets();
