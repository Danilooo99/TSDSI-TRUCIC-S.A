<?php

if (!class_exists('WPFA_Thrive_Architect')) {

	class WPFA_Thrive_Architect {

		static private $instance = false;

		private function __construct() {
			if (!defined('TVE_IN_ARCHITECT')) {
				return;
			}
			add_filter('vg_admin_to_frontend/frontend/js_data', array($this, 'enable_full_screen_on_editor'));
			// We enqueue the assets on the footer after jQuery loaded
			add_action('tcb_hook_editor_footer', array($this, 'enqueue_assets'), 99);
			if (!is_admin() && (!empty($_GET['tge']) || !empty($_GET['tie']) ) && !empty($_GET['vgfa_referrer'])) {
				add_filter('admin_url', array($this, 'filter_save_quiz_url'), 100);
			}
		}

		function filter_save_quiz_url($url) {
			if (strpos($url, 'page=tqb_admin_dashboard#dashboard/quiz/') !== false) {
				$referrer = esc_url(base64_decode($_GET['vgfa_referrer']));
				$url = $referrer;
			}
			return $url;
		}

		function enqueue_assets() {
			VG_Admin_To_Frontend_Obj()->cleanup_admin_page_for_frontend();
		}

		function enable_full_screen_on_editor($js_data) {
			$js_data['fullscreen_pages_keywords'][] = '&action=architect';
			return $js_data;
		}

		function init() {
			
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Thrive_Architect::$instance) {
				WPFA_Thrive_Architect::$instance = new WPFA_Thrive_Architect();
				WPFA_Thrive_Architect::$instance->init();
			}
			return WPFA_Thrive_Architect::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Thrive_Architect_Obj')) {

	function WPFA_Thrive_Architect_Obj() {
		return WPFA_Thrive_Architect::get_instance();
	}

}
add_action('plugins_loaded', 'WPFA_Thrive_Architect_Obj');
