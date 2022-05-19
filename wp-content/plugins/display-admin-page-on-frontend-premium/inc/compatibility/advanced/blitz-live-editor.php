<?php

if (!class_exists('BLITZ_Live_Editor')) {

	class BLITZ_Live_Editor {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!defined('EDITPRO_PlUGIN_MAIN_FILE')) {
				return;
			}
			if (!dapof_fs()->is_plan('platform', true)) {
				return;
			}
			if (!is_admin()) {
				add_action('get_edit_post_link', array($this, 'modify_edit_link'), 200, 2);
			}
		}

		/**
		 * 
		 * @param type $link
		 * @param type $post_id
		 * @return type
		 */
		function modify_edit_link($link, $post_id) {
			$default_editor = VG_Admin_To_Frontend_Obj()->get_settings('default_editor', '');
			if (!empty($_GET['elementor-preview']) || !post_type_supports(get_post_type($post_id), 'elementor') || $default_editor !== 'elementor' || (!empty($_GET['action']) && $_GET['action'] === 'elementor')) {
				return $link;
			}
			// Disable the link because we'll just open the live editor
			$link = '#';
			return $link;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == BLITZ_Live_Editor::$instance) {
				BLITZ_Live_Editor::$instance = new BLITZ_Live_Editor();
				BLITZ_Live_Editor::$instance->init();
			}
			return BLITZ_Live_Editor::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('BLITZ_Live_Editor_Obj')) {

	function BLITZ_Live_Editor_Obj() {
		return BLITZ_Live_Editor::get_instance();
	}

}
BLITZ_Live_Editor_Obj();
