<?php

if (!class_exists('WPFA_LearnDash')) {

	class WPFA_LearnDash {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!defined('LEARNDASH_VERSION')) {
				return;
			}
			if (is_network_admin()) {
				return;
			}
			if (dapof_fs()->can_use_premium_code__premium_only()) {
				add_filter('vg_plugin_sdk/settings/' . VG_Admin_To_Frontend::$textname . '/options', array($this, 'add_global_option'));
				add_action('pre_get_users', array($this, 'filter_users'));
				add_filter('ct_query_where', array($this, 'gamipress_user_earnings_query_where'), 10, 2);
				add_filter('wp_frontend_admin/global_replacement/disallowed_search_strings', array($this, 'prevent_text_replacement_errors'));
				add_filter('wp_frontend_admin/text_edits_for_current_page', array($this, 'change_text_replacements'));
			}
		}

		function change_text_replacements($text_edits) {
			if (isset($text_edits['Learndash'])) {
				// We must replace only full words, "Elementor" breaks Elementor because it changes ElementorConfig
				$text_edits['/\\\bLearnDash\\\b/'] = $text_edits['Learndash'];
				unset($text_edits['Learndash']);
			}
			if (isset($text_edits['LearnDash'])) {
				// We must replace only full words, "Elementor" breaks Elementor because it changes ElementorConfig
				$text_edits['/\\\bLearnDash\\\b/'] = $text_edits['LearnDash'];
				unset($text_edits['LearnDash']);
			}
			return $text_edits;
		}

		function prevent_text_replacement_errors($keywords) {
			$keywords[] = 'Builder';
			return $keywords;
		}

		function gamipress_user_earnings_query_where($where, $ct_query) {

			global $ct_table;

			if ($ct_table->name !== 'gamipress_user_earnings' || !VG_Admin_To_Frontend_Obj()->get_settings('learndash_groups_leaders_manage_group_members')) {
				return $where;
			}

			$group_members = $this->_get_group_members();
			if ($group_members === false) {
				return $where;
			}
			$table_name = $ct_table->db->table_name;
			$user_id = implode(", ", $group_members);

			$where .= " AND {$table_name}.user_id IN ( {$user_id} )";
			return $where;
		}

		function _get_group_members() {
			global $wpdb;
			$user_leader_of_group_ids = array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s GROUP BY meta_value", get_current_user_id(), 'learndash_group_leaders_%')));
			if (!$user_leader_of_group_ids) {
				return false;
			}

			$group_members = array_map('intval', $wpdb->get_col("SELECT user_id FROM $wpdb->usermeta WHERE meta_key LIKE 'learndash_group_users_%' AND meta_value IN (" . implode(',', $user_leader_of_group_ids) . ") GROUP BY user_id"));
			return $group_members;
		}

		function filter_users($query) {
			global $pagenow;
			if (!VG_Admin_To_Frontend_Obj()->get_settings('learndash_groups_leaders_manage_group_members')) {
				return;
			}
			$group_members = $this->_get_group_members();
			if ($group_members === false) {
				return;
			}
			if (is_admin() && 'users.php' == $pagenow && !VG_Admin_To_Frontend_Obj()->is_master_user()) {
				$query->set('include', $group_members);
			}
		}

		function add_global_option($sections) {
			$sections['access-restrictions']['fields'][] = array(
				'id' => 'learndash_groups_leaders_manage_group_members',
				'type' => 'switch',
				'title' => __('LearnDash: Allow group leaders to manage group members only?', VG_Admin_To_Frontend::$textname),
				'desc' => __('Enable this option if you want to make sure users who are leaders of any group, can view and edit only users who are members of those groups.', VG_Admin_To_Frontend::$textname),
				'default' => false,
			);
			return $sections;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_LearnDash::$instance) {
				WPFA_LearnDash::$instance = new WPFA_LearnDash();
				WPFA_LearnDash::$instance->init();
			}
			return WPFA_LearnDash::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_LearnDash_Obj')) {

	function WPFA_LearnDash_Obj() {
		return WPFA_LearnDash::get_instance();
	}

}
add_action('plugins_loaded', 'WPFA_LearnDash_Obj');
