<?php

if (!class_exists('WPFA_Beaver_Builder')) {

	class WPFA_Beaver_Builder {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!class_exists('FLBuilderLoader')) {
				return;
			}
			if (dapof_fs()->is_plan('platform', true)) {
				add_filter('vg_frontend_admin/compatible_default_editors', array($this, 'add_compatible_default_editor'));
				add_action('get_edit_post_link', array($this, 'modify_edit_link'), 100, 2);
			}
		}

		function modify_edit_link($link, $post_id) {
			$default_editor = VG_Admin_To_Frontend_Obj()->get_settings('default_editor', '');
			$post = get_post($post_id);
			$supported_post_types = FLBuilderModel::get_post_types();

			if (!in_array($post->post_type, $supported_post_types, true) || $default_editor !== 'beaver_builder') {
				return $link;
			}

			if (isset($_GET['fl_builder']) && !empty($_GET['vgfa_referrer'])) {
				$referrer = preg_replace('/\#.+$/', '', esc_url(base64_decode($_GET['vgfa_referrer'])));
				$link = $referrer . '#wpfa:' . base64_encode('post.php?action=edit&post=' . $post_id);
			} elseif (!isset($_GET['fl_builder'])) {
				$link = esc_url(get_permalink($post_id) . '?fl_builder');
			}
			return $link;
		}

		function add_compatible_default_editor($editors) {
			$editors['beaver_builder'] = 'Beaver Builder';
			return $editors;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Beaver_Builder::$instance) {
				WPFA_Beaver_Builder::$instance = new WPFA_Beaver_Builder();
				WPFA_Beaver_Builder::$instance->init();
			}
			return WPFA_Beaver_Builder::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Beaver_Builder_Obj')) {

	function WPFA_Beaver_Builder_Obj() {
		return WPFA_Beaver_Builder::get_instance();
	}

}

add_action('plugins_loaded', 'WPFA_Beaver_Builder_Obj');
