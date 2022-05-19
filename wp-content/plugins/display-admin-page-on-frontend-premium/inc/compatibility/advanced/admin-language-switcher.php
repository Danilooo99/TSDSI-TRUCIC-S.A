<?php

if (!class_exists('WPFA_Admin_Language_Switcher')) {

	class WPFA_Admin_Language_Switcher {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (is_user_logged_in() && !empty($_GET['wpfa_admin_language'])) {
				$this->switch_language_from_url();
			}
		}

		function switch_language_from_url() {
			$user_id = get_current_user_id();

			$lang = isset($_REQUEST['wpfa_admin_language']) ? sanitize_text_field(wp_unslash($_REQUEST['wpfa_admin_language'])) : false;

			if (!$user_id || !$lang) {
				return;
			}

			if ($lang === 'site-default') {
				$lang = null;
			}

			wp_update_user(array('ID' => $user_id, 'locale' => $lang));
			wp_redirect(esc_url_raw(remove_query_arg('wpfa_admin_language')));
			exit();
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Admin_Language_Switcher::$instance) {
				WPFA_Admin_Language_Switcher::$instance = new WPFA_Admin_Language_Switcher();
				WPFA_Admin_Language_Switcher::$instance->init();
			}
			return WPFA_Admin_Language_Switcher::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Admin_Language_Switcher_Obj')) {

	function WPFA_Admin_Language_Switcher_Obj() {
		return WPFA_Admin_Language_Switcher::get_instance();
	}

}

add_action('init', 'WPFA_Admin_Language_Switcher_Obj');
