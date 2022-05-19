<?php

if (!class_exists('WPFA_Dokan')) {

	class WPFA_Dokan {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!class_exists('WeDevs_Dokan')) {
				return;
			}
			add_filter('option_dokan_general', array($this, 'filter_dokan_settings'));
			add_filter('dokan_settings_general_site_options', array($this, 'filter_dokan_settings_fields'));
		}

		function filter_dokan_settings_fields($fields) {
			$fields['admin_access']['desc'] .= __('NOTE. WP Frontend Admin unchecked this option automatically because users need access to wp-admin. You can restrict the access using the WP Frontend Admin settings page', VG_Admin_To_Frontend::$textname);
			return $fields;
		}

		function filter_dokan_settings($value) {
			$value['admin_access'] = 'off';
			return $value;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Dokan::$instance) {
				WPFA_Dokan::$instance = new WPFA_Dokan();
				WPFA_Dokan::$instance->init();
			}
			return WPFA_Dokan::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Dokan_Obj')) {

	function WPFA_Dokan_Obj() {
		return WPFA_Dokan::get_instance();
	}

}
WPFA_Dokan_Obj();
