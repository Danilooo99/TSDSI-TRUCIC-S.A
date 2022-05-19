<?php

if (!class_exists('WPFA_Automator_WP')) {

	class WPFA_Automator_WP {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!class_exists('AutomatorWP')) {
				return;
			}
			add_filter('admin_url', array($this, 'filter_automation_save_redirect_url'));
		}

		function filter_automation_save_redirect_url($url) {
			if (strpos($url, 'admin.php?page=edit_automatorwp_automations') !== false && strpos($url, 'vgfa_source') === false && !empty($_GET['vgfa_source'])) {
				$url = add_query_arg('vgfa_source', (int) $_GET['vgfa_source'], $url);
			}
			return $url;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Automator_WP::$instance) {
				WPFA_Automator_WP::$instance = new WPFA_Automator_WP();
				WPFA_Automator_WP::$instance->init();
			}
			return WPFA_Automator_WP::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Automator_WP_Obj')) {

	function WPFA_Automator_WP_Obj() {
		return WPFA_Automator_WP::get_instance();
	}

}
add_action('init', 'WPFA_Automator_WP_Obj');
