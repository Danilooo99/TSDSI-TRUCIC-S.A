<?php

if (!class_exists('WPFA_Global_Dashboard')) {

	class WPFA_Global_Dashboard {

		static private $instance = false;
		var $current_site_id = null;

		private function __construct() {
			
		}

		function init() {

			if (!is_multisite()) {
				return;
			}
			if (dapof_fs()->is_plan__premium_only('platform', true)) {
				add_shortcode('wp_frontend_admin_site_selector', array($this, 'get_site_selector__premium_only'));
				add_filter('myblogs_blog_actions', array($this, 'modify_dashboard_url__premium_only'), 10, 2);
				add_action('init', array($this, 'set_site_from_url__premium_only'));
			}
		}

		function set_site_from_url__premium_only() {
			if (is_multisite() && !empty($_GET['vgfa_site_id']) && !empty($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wpfa')) {
				$site_id = (int) $_GET['vgfa_site_id'];
				$this->switch_site($site_id);
				wp_redirect(remove_query_arg(array('vgfa_site_id', '_wpnonce')));
				exit();
			}
		}

		function get_site_id_for_admin_content() {
			$blog_id = null;
			$user_belongs_to_blogs = null;
			if (!dapof_fs()->is_plan__premium_only('standard', true)) {
				if (!VG_Admin_To_Frontend_Obj()->is_master_user() && is_multisite() && $this->get_dashboard_site_id()) {
					$blog_id = $this->get_current_site_id();
				}
			}
			return apply_filters('wp_frontend_admin/site_id_for_admin_content', $blog_id, $user_belongs_to_blogs);
		}

		function switch_to_dashboard_site() {
			$out = false;
			if (!dapof_fs()->is_plan__premium_only('standard', true)) {
				$global_dashboard_id = $this->get_dashboard_site_id();
				if ($global_dashboard_id && is_multisite() && get_current_blog_id() !== $global_dashboard_id) {
					$original_blog_id = get_current_blog_id();
					switch_to_blog($global_dashboard_id);
					$out = $original_blog_id;
				}
			}
			return $out;
		}

		function restore_site($site_id) {

			if (!dapof_fs()->is_plan__premium_only('standard', true)) {
				$global_dashboard_id = $this->get_dashboard_site_id();
				if ($global_dashboard_id && $site_id && is_multisite() && get_current_blog_id() !== $site_id) {
					switch_to_blog($site_id);
				}
			}
		}

		function is_global_dashboard() {
			$out = false;

			if (!dapof_fs()->is_plan__premium_only('standard', true)) {
				$global_dashboard_id = (int) $this->get_dashboard_site_id();
				if ($global_dashboard_id && is_multisite() && get_current_blog_id() === $global_dashboard_id) {
					$out = true;
				}
			}
			return $out;
		}

		function get_dashboard_site_id() {
			$id = VG_Admin_To_Frontend_Obj()->get_settings('global_dashboard_id');
			if ($id && !get_site($id)) {
				// If we saved a global dashboard id but the site does not exist, unset it on the options page
				VG_Admin_To_Frontend_Obj()->update_option('global_dashboard_id', '');
				$id = null;
			}
			return (int) $id;
		}

		function get_dashboard_url($blog_id, $url = null) {
			$dashboard_site_id = $this->get_dashboard_site_id();
			if (empty($url)) {
				$url = VG_Admin_To_Frontend_Obj()->get_settings('redirect_to_frontend', get_site_url($dashboard_site_id, '/'));
			}
			$current_blog_id = get_current_blog_id();
			switch_to_blog($dashboard_site_id);
			$url = add_query_arg('vgfa_site_id', $blog_id, $url);
			$url = esc_url(wp_nonce_url($url, 'wpfa'));
			switch_to_blog($current_blog_id);
			return $url;
		}

		function modify_dashboard_url__premium_only($actions, $user_blog) {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				$search = esc_url(admin_url());
				$replace = $this->get_dashboard_url($user_blog->userblog_id);
				$actions = str_replace($search, $replace, $actions);
			}
			return $actions;
		}

		function get_manageable_sites() {
			if (!is_multisite()) {
				return array();
			}
			$user_belongs_to_blogs = get_blogs_of_user(get_current_user_id());

			if (VG_Admin_To_Frontend_Obj()->is_master_user()) {
				$allowed_site = $user_belongs_to_blogs;
			} else {
				$allowed_site = array();
				foreach ($user_belongs_to_blogs as $user_blog) {
					switch_to_blog($user_blog->userblog_id);
					if (current_user_can('read')) {
						$allowed_site[] = $user_blog;
					}
					restore_current_blog();
				}
			}
			return $allowed_site;
		}

		function get_current_site_id() {
			$site_id = (int) get_user_meta(get_current_user_id(), 'wpfa_current_site_id', true);
			$manageable_sites = $this->get_manageable_sites();
			$blog_id = null;
			if ($manageable_sites) {
				$allowed_site_ids = wp_list_pluck($manageable_sites, 'userblog_id');
				$blog_id = ($site_id && in_array($site_id, $allowed_site_ids, true) ) ? $site_id : end($allowed_site_ids);
			}
			return $blog_id;
		}

		function switch_site($site_id) {
			if (!$site_id) {
				return;
			}
			$manageable_sites = $this->get_manageable_sites();
			if (!$manageable_sites) {
				return;
			}
			$allowed_site_ids = wp_list_pluck($manageable_sites, 'userblog_id');
			if (!in_array($site_id, $allowed_site_ids, true)) {
				return;
			}
			$this->current_site_id = (int) $site_id;
			update_user_meta(get_current_user_id(), 'wpfa_current_site_id', $site_id);
		}

		function get_site_selector__premium_only($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'format' => 'grid', // grid | links_list
				'exclude_current_site' => false
							), $atts));

			if (!is_user_logged_in()) {
				return;
			}

			$manageable_sites = $this->get_manageable_sites();
			if (empty($manageable_sites)) {
				return;
			}
			$current_site_id = $this->get_current_site_id();
			$template_name = $format === 'grid' ? 'site-selector' : 'site-selector-links-list';
			ob_start();
			include VG_Admin_To_Frontend::$dir . '/views/frontend/' . $template_name . '.php';
			return ob_get_clean();
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Global_Dashboard::$instance) {
				WPFA_Global_Dashboard::$instance = new WPFA_Global_Dashboard();
				WPFA_Global_Dashboard::$instance->init();
			}
			return WPFA_Global_Dashboard::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Global_Dashboard_Obj')) {

	function WPFA_Global_Dashboard_Obj() {
		return WPFA_Global_Dashboard::get_instance();
	}

}
WPFA_Global_Dashboard_Obj();
