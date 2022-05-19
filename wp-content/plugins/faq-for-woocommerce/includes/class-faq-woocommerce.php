<?php
/**
 * FAQ_Woocommerce setup
 *
 * @package FAQ_Woocommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main FAQ_Woocommerce Class.
 *
 * @class FAQ_Woocommerce
 */

if ( ! class_exists( 'FAQ_Woocommerce', false ) ) :
    /**
     * Main FAQ_Woocommerce Class.
     *
     * @class FAQ_Woocommerce
     */
    final class FAQ_Woocommerce {

        /**
         * FAQ_Woocommerce version.
         *
         * @var string
         */
        public $version = '1.0.0';

        /**
         * The single instance of the class.
         *
         * @var FAQ_Woocommerce
         * @since 1.0.0
         */
        protected static $_instance = null;

        /**
         * Main FAQ_Woocommerce Instance.
         *
         * Ensures only one instance of FAQ_Woocommerce is loaded or can be loaded.
         *
         * @return FAQ_Woocommerce - Main instance.
         * @since 1.0.0
         */
        public static function instance() {
            if ( is_null(self::$_instance) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         *
         * @since 1.0.0
         */
        public function __clone() {
            wc_doing_it_wrong(__FUNCTION__, __('Cloning is forbidden.', 'faq-for-woocommerce'), '1.0.0');
        }

        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 2.1
         */
        public function __wakeup() {
            wc_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'faq-for-woocommerce'), '1.0.0');
        }

        /**
         * PluginName Constructor.
         */
        public function __construct() {
            $this->define_constants();
            $this->includes();
            add_action('init', array( $this, 'init' ), 0);
            add_filter( 'plugin_action_links_' . FFW_BASENAME, array($this, 'ffw_add_action_links') );
        }


        /**
         * Define WC Constants.
         */
        private function define_constants() {
            $this->define('FFW_ABSPATH', dirname(FFW_FILE) );
            $this->define('FFW_PLUGIN_BASENAME', plugin_basename(FFW_FILE));
            $this->define('FFW_PLUGIN_VERSION', $this->version);
        }

        /**
         * Define constant if not already set.
         *
         * @param string $name Constant name.
         * @param string|bool $value Constant value.
         */
        private function define( $name, $value ) {
            if ( ! defined($name) ) {
                define($name, $value);
            }
        }

        /**
         * What type of request is this?
         *
         * @param string $type admin, ajax, cron or frontend.
         * @return bool
         */
        private function is_request( $type ) {
            switch ( $type ) {
                case 'admin':
                    return is_admin();
                case 'ajax':
                    return defined('DOING_AJAX');
                case 'cron':
                    return defined('DOING_CRON');
            }
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        public function includes() {

			include_once FFW_ABSPATH . '/includes/ffw-helper.php';
			include_once FFW_ABSPATH . '/includes/class-faq-comments.php';

            if ( $this->is_request('admin') ) {
                include_once FFW_ABSPATH . '/includes/admin/class-faq-woocommerce-admin.php';
            }

            include_once FFW_ABSPATH . '/includes/class-faq-woocommerce-public.php';
            include_once FFW_ABSPATH . '/includes/faq-woocommerce-public.php';

        }

        /**
         * Action links.
         *
         * @since 1.2.3
         */
        public function ffw_add_action_links($actions) {
            $setting = sprintf('<a href="%s">%s</a>', admin_url( 'admin.php?page=woocommerce-faq' ), esc_html__('Settings', 'faq-for-woocommerce'));
            $ffw_links = array($setting);

            return array_merge( $ffw_links, $actions );
        }

        /**
         * Init PluginName when WordPress Initialises.
         * 
         * @since 1.0.0
         */
        public function init() {
            // Before init action.
            do_action('before_faq_woocommerce_init');

            // Set up localisation.
            $this->load_plugin_textdomain();

            add_action( 'admin_notices', array( $this, 'ffw_admin_notices' ) );

            // Init action.
            do_action('faq_woocommerce_init');
        }

        /**
         * Admin Notices.
         * 
         * @since 1.0.0
         */
        public function ffw_admin_notices() {
            $this->ffw_woocommerce_dependency_check();
            
        }

        /**
         * Woocommerce dependency check.
         * 
         * @since 1.0.0
         */
        public function ffw_woocommerce_dependency_check() {
            $plugin_url = self_admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );
            $plugin_url  = sprintf( '<a href="%s">%s</a>', $plugin_url, esc_html__( 'WooCommerce', 'faq-for-woocommerce' ) );
            $plugin_name = sprintf( '<code>%s</code>', esc_html__( 'XPlainer', 'faq-for-woocommerce' ) );
            $wc_name     = sprintf( '<code>%s</code>', esc_html__( 'WooCommerce', 'faq-for-woocommerce' ) );
            $wcVersion  = defined( 'WC_VERSION' ) ? '<code>' . WC_VERSION . '</code>' : '<code>UNKNOWN</code>';
            $minVersion = '<code>' . FFW_MIN_WC_VERSION . '</code>';

            if ( ! ffw_check_woocommerce() ) {
                /** @noinspection HtmlUnknownTarget */
                $message = sprintf(
                    /* translators: 1: this plugin name, 2: required plugin name, 3: required plugin name and installation url */
                    esc_html__( '%1$s requires %2$s to be installed and active. You can installed/activate %3$s here.', 'faq-for-woocommerce' ),
                    $plugin_name,
                    $wc_name,
                    $plugin_url
                );
                printf( '<div class="error"><p><strong>%1$s</strong></p></div>', $message ); // phpcs:ignore
            }
            if ( ffw_check_woocommerce() && ! ffw_is_WC_supported() ) {
                /** @noinspection HtmlUnknownTarget */
                $message = sprintf(
                    /* translators: 1: this plugin name, 2: required plugin name, 3: required plugin required version, 4: required plugin current version, 5: required plugin update url and name */
                    esc_html__( '%1$s requires %2$s version %3$s or above and %4$s found. Please upgrade %2$s to the latest version here %5$s', 'faq-for-woocommerce' ),
                    $plugin_name,
                    $wc_name,
                    $minVersion,
                    $wcVersion,
                    $plugin_url
                );
                printf( '<div class="error"><p><strong>%1$s</strong></p></div>', $message ); // phpcs:ignore
            }
        }

        /**
         * Load Localisation files.
         *
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain('faq-woocommerce', false, plugin_basename(dirname(FFW_PLUGIN_URL)) . '/i18n/languages');
        }

        /**
         * Get the plugin url.
         *
         * @return string
         */
        public function plugin_url() {
            return untrailingslashit(plugins_url('/', FFW_PLUGIN_URL));
        }

        /**
         * Get the plugin path.
         *
         * @return string
         */
        public function plugin_path() {
            return untrailingslashit(plugin_dir_path(FFW_PLUGIN_URL));
        }

        /**
         * Get the template path.
         *
         * @return string
         */
        public function template_path() {
            return apply_filters('ffw_template_path', 'faq-for-woocommerce/');
        }

        /**
         * Get Ajax URL.
         *
         * @return string
         */
        public function ajax_url() {
            return admin_url('admin-ajax.php', 'relative');
        }
    }

endif;

