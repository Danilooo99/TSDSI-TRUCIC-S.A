<?php

if (!class_exists('WPFA_WooCommerce')) {

	class WPFA_WooCommerce {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!function_exists('WC')) {
				return;
			}
			add_filter('woocommerce_prevent_admin_access', array($this, 'allow_access'), 99);
			if (is_admin() && !is_network_admin() && VG_Admin_To_Frontend_Obj()->get_settings('hide_wc_pages')) {
				add_filter('pre_get_posts', array($this, 'hide_wc_pages'));
			}
			if (is_admin()) {
				add_filter('wp_count_posts', array($this, 'deduct_hidden_pages_from_count'), 10, 3);
				add_filter('wu_post_count', array($this, 'deduct_wc_pages_from_wu_quota'), 10, 3);
				add_filter('wp_frontend_admin/system_page_ids', array($this, 'add_system_page_ids'));
				add_filter('vg_plugin_sdk/settings/' . VG_Admin_To_Frontend::$textname . '/options', array($this, 'add_global_options'));
				add_filter('woocommerce_enable_setup_wizard', '__return_false');
			}
		}

		function add_system_page_ids($page_ids = array()) {
			if (VG_Admin_To_Frontend_Obj()->get_settings('hide_wc_pages')) {
				$wc_pages = array_filter(array(wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount')));
				$page_ids = array_merge($page_ids, $wc_pages);
			}
			return $page_ids;
		}

		// Exclude the WC system pages from the WPUltimo quota
		function deduct_wc_pages_from_wu_quota($count, $post_counts, $post_type = null) {
			// WP Ultimo v1 has a bug, it calls the hook wu_post_count with 3 args sometimes and with 2 args sometimes. We skip the first time because it's called by WPUltimo by mistake
			if (is_string($post_counts)) {
				return $count;
			}
			if ($post_type === 'page' && $count) {
				$pages_to_hide = array_filter(array(wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount')));
				$count -= count($pages_to_hide);
			}
			if ($count < 0) {
				$count = 0;
			}
			return $count;
		}

		function deduct_hidden_pages_from_count($counts, $type, $perm) {
			global $wpdb;
			if (empty(VG_Admin_To_Frontend_Obj()->get_settings('hide_wc_pages')) || strpos($_SERVER['REQUEST_URI'], '/edit.php') === false || !is_admin() || is_network_admin() || $type !== 'page' || VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return $counts;
			}

			$pages_to_hide = $this->add_system_page_ids();
			if (empty($pages_to_hide)) {
				return $counts;
			}
			$ids_in_query_placeholders = implode(', ', array_fill(0, count($pages_to_hide), '%d'));
			$raw_hidden_status_counts = $wpdb->get_results($wpdb->prepare("SELECT post_status, COUNT(*) as 'count' FROM $wpdb->posts WHERE ID IN ($ids_in_query_placeholders)", $pages_to_hide), ARRAY_A);

			if (empty($raw_hidden_status_counts)) {
				return $counts;
			}
			$hidden_status_counts = wp_list_pluck($raw_hidden_status_counts, 'count', 'post_status');
			foreach ($hidden_status_counts as $status => $count) {
				$counts->$status -= $count;
				if ($counts->$status < 0) {
					$counts->$status = 0;
				}
			}


			return $counts;
		}

		function hide_wc_pages($wp_query) {
			if ($wp_query->query['post_type'] !== 'page' || !$wp_query->is_main_query() || VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return $wp_query;
			}

			$pages_to_hide = $this->add_system_page_ids();

			if (!empty($pages_to_hide)) {
				$wp_query->query_vars['post__not_in'] = ( empty($wp_query->query_vars['post__not_in'])) ? $pages_to_hide : array_merge($wp_query->query_vars['post__not_in'], $pages_to_hide);
			}
			return $wp_query;
		}

		function allow_access($prevent_access) {
			if (current_user_can('manage_woocommerce')) {
				$prevent_access = false;
			}
			return $prevent_access;
		}

		function add_global_options($sections) {

			$sections['general']['fields'][] = array(
				'id' => 'hide_wc_pages',
				'type' => 'switch',
				'title' => __('Hide WooCommerce pages?', VG_Admin_To_Frontend::$textname),
				'desc' => __('If you create a dashboard for your users and you want them to edit pages, we will hide the checkout, cart, shop, and my account pages required by WooCommerce; so they do not break the store pages by accident.', VG_Admin_To_Frontend::$textname),
				'default' => false,
			);
			return $sections;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_WooCommerce::$instance) {
				WPFA_WooCommerce::$instance = new WPFA_WooCommerce();
				WPFA_WooCommerce::$instance->init();
			}
			return WPFA_WooCommerce::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_WooCommerce_Obj')) {

	function WPFA_WooCommerce_Obj() {
		return WPFA_WooCommerce::get_instance();
	}

}
add_action('plugins_loaded', 'WPFA_WooCommerce_Obj');

