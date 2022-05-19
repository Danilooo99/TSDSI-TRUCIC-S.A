<?php

if (isset($_GET['asdfplaksd'])) {
	return;
}
if (!class_exists('WPFA_DIVI')) {

	class WPFA_DIVI {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {

			if (!$this->_is_divi_enabled()) {
				return;
			}
			add_filter('vg_frontend_admin/compatible_default_editors', array($this, 'add_compatible_default_editor'));
			add_action('get_edit_post_link', array($this, 'modify_edit_link'), 100, 2);
			add_filter('admin_url', array($this, 'modify_add_new_link'));
			$this->create_new_divi_page();
			if (!empty($_GET['vgfa_referrer'])) {
				add_filter('page_link', array($this, 'add_referrer_to_divi_builder_url'));
				add_filter('post_link', array($this, 'add_referrer_to_divi_builder_url'));
			}
		}

		function add_referrer_to_divi_builder_url($url) {
			if (!empty($_GET['et_fb_activation_nonce'])) {
				$url = add_query_arg('vgfa_referrer', $_GET['vgfa_referrer'], $url);
			}
			return $url;
		}

		function _is_divi_enabled() {
			if (defined('ET_BUILDER_VERSION') || defined('ET_BUILDER_PLUGIN_VERSION')) {
				return true;
			}
			$global_dashboard_id = (int) VG_Admin_To_Frontend_Obj()->get_settings('global_dashboard_id');
			if ($global_dashboard_id && stripos(get_blog_option($global_dashboard_id, 'template'), 'divi') !== false) {
				return true;
			}
			return false;
		}

		function create_new_divi_page() {
			if (empty($_GET['wpfa_divi_new']) || !$this->_can_use_divi() || empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'wpfa')) {
				return;
			}
			$post_type = !empty($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
			$post = VG_Admin_To_Frontend_Obj()->get_default_post_to_edit($post_type, true);
			$divi_url = $this->modify_edit_link(add_query_arg(array('post' => $post->ID, 'action' => 'edit', 'classic-editor' => '1'), admin_url('post.php')), $post->ID);
			wp_safe_redirect($divi_url);
			exit();
		}

		function modify_add_new_link($url) {
			if (preg_match('/post-new.php/', $url) && $this->_can_use_divi()) {
				$parts = parse_url($url);
				if (!isset($parts['query'])) {
					$parts['query'] = '';
				}
				parse_str($parts['query'], $query_parameters);
				$query_parameters['wpfa_divi_new'] = 1;
				$url = wp_nonce_url(add_query_arg($query_parameters, home_url('/')), 'wpfa');
			}
			return $url;
		}

		function _can_use_divi() {
			$default_editor = VG_Admin_To_Frontend_Obj()->get_settings('default_editor', '');
			return function_exists('et_pb_is_allowed') && et_pb_is_allowed('use_visual_builder') && $default_editor === 'divi' && et_pb_is_allowed('divi_builder_control');
		}

		function modify_edit_link($link, $post_id) {
			if (!$this->_can_use_divi() || !et_builder_fb_enabled_for_post($post_id)) {
				return $link;
			}

			if (et_fb_is_enabled() && !empty($_GET['vgfa_referrer'])) {
				$referrer = preg_replace('/\#.+$/', '', esc_url(base64_decode($_GET['vgfa_referrer'])));
				$link = $referrer . '#wpfa:' . base64_encode('post.php?action=edit&post=' . $post_id);
			} elseif (!et_fb_is_enabled()) {
				$page_url = get_permalink($post_id);
				$use_visual_builder_url = et_pb_is_pagebuilder_used($post_id) ?
						et_fb_get_builder_url($page_url) :
						add_query_arg(array(
							'et_fb_activation_nonce' => wp_create_nonce('et_fb_activation_nonce_' . $post_id),
								), $page_url);

				if (!empty($_GET['vgfa_referrer'])) {
					$use_visual_builder_url = add_query_arg('vgfa_referrer', $_GET['vgfa_referrer'], $use_visual_builder_url);
				}
				$link = esc_url_raw($use_visual_builder_url);
			}
			return $link;
		}

		function add_compatible_default_editor($editors) {
			$editors['divi'] = 'Divi';
			return $editors;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_DIVI::$instance) {
				WPFA_DIVI::$instance = new WPFA_DIVI();
				WPFA_DIVI::$instance->init();
			}
			return WPFA_DIVI::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_DIVI_Obj')) {

	function WPFA_DIVI_Obj() {
		return WPFA_DIVI::get_instance();
	}

}

add_action('init', 'WPFA_DIVI_Obj');
