<?php

if (!class_exists('WPFA_WPForms')) {

	class WPFA_WPForms {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (isset($_GET['asdf'])) {
				return;
			}
			if (!defined('WPFORMS_VERSION')) {
				return;
			}
			add_action('wpforms_pro_admin_entries_printpreview_print_html_head', array(VG_Admin_To_Frontend_Obj(), 'cleanup_admin_page_for_frontend'));
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_WPForms::$instance) {
				WPFA_WPForms::$instance = new WPFA_WPForms();
				WPFA_WPForms::$instance->init();
			}
			return WPFA_WPForms::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_WPForms_Obj')) {

	function WPFA_WPForms_Obj() {
		return WPFA_WPForms::get_instance();
	}

}
add_action('plugins_loaded', 'WPFA_WPForms_Obj');

