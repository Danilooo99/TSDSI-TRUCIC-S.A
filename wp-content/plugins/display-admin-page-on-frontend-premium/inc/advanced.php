<?php
if (!class_exists('WPFA_Advanced')) {

	class WPFA_Advanced {

		static private $instance = false;
		var $floating_button_rendered = false;

		private function __construct() {
			
		}

		function hide_system_pages__premium_only($wp_query) {
			global $wpdb;

			if ($wp_query->query['post_type'] !== 'page' || !$wp_query->is_main_query() || VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return $wp_query;
			}

			$system_page_ids = VG_Admin_To_Frontend_Obj()->get_system_page_ids();
			if (empty($system_page_ids)) {
				return $wp_query;
			}

			$pages_to_hide = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE ID IN (" . implode(',', $system_page_ids) . ") AND post_author != " . (int) get_current_user_id());

			if (!empty($pages_to_hide)) {
				$wp_query->query_vars['post__not_in'] = ( empty($wp_query->query_vars['post__not_in'])) ? $pages_to_hide : array_merge($wp_query->query_vars['post__not_in'], $pages_to_hide);
			}
			return $wp_query;
		}

		function init() {
			if (!dapof_fs()->can_use_premium_code__premium_only()) {
				return;
			}

			if (is_admin() && !is_network_admin()) {
				add_action('admin_footer', array($this, 'default_user_role__premium_only'));
				add_action('admin_footer', array($this, 'admin_footer_html'), 99);
				if (VG_Admin_To_Frontend_Obj()->get_settings('hide_system_pages')) {
					add_filter('pre_get_posts', array($this, 'hide_system_pages__premium_only'));
				}
			} else {
				add_action('wp_footer', array($this, 'render_floating_edit_button'));
			}
			add_filter('login_redirect', array($this, 'maybe_redirect_to_the_dashboard__premium_only'), 10, 3);
			if (dapof_fs()->is_plan('platform', true)) {
				add_filter('allowed_redirect_hosts', array($this, 'allow_safe_redirects_to_dashboard_subsite'));
			}
		}

		function admin_footer_html() {
			$html = VG_Admin_To_Frontend_Obj()->get_settings('admin_footer_html');
			if (empty($html) || is_network_admin() || !is_user_logged_in() || ( empty($_REQUEST['vgfa_source']) && empty($_REQUEST['wpfa_id']))) {
				return;
			}
			echo $html;
		}

		function render_floating_edit_button() {
			if ($this->floating_button_rendered) {
				return;
			}
			if (!is_user_logged_in() || !is_singular()) {
				return;
			}

			$button_position = VG_Admin_To_Frontend_Obj()->get_settings('show_floating_edit_button');
			if (!$button_position || (!VG_Admin_To_Frontend_Obj()->is_master_user() && !$this->is_frontend_dashboard_user(get_current_user_id()))) {
				return;
			}

			$post_id = get_queried_object_id();
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}

			// Don't show button when viewing a system page
			$system_page_ids = VG_Admin_To_Frontend_Obj()->get_system_page_ids();
			if (in_array($post_id, $system_page_ids, true)) {
				return;
			}

			// Auto enable the global settings add_post_edit_link to prevent errors
			if (!VG_Admin_To_Frontend_Obj()->get_settings('add_post_edit_link')) {
				VG_Admin_To_Frontend_Obj()->update_option('add_post_edit_link', true);
			}
			$class = $button_position;
			$edit_url = get_edit_post_link($post_id);
			if (!$edit_url) {
				return;
			}
			$main_color = VG_Admin_To_Frontend_Obj()->get_settings('main_color', '', true);
			if (empty($main_color)) {
				$main_color = 'black';
			}
			$this->floating_button_rendered = true;
			// Make sure that dashicons have loaded
			wp_enqueue_style('dashicons');
			include VG_Admin_To_Frontend::$dir . '/views/frontend/floating-edit-button.php';
		}

		function default_user_role__premium_only() {
			if (empty(VG_Admin_To_Frontend_Obj()->get_settings('default_user_role_add')) || VG_Admin_To_Frontend_Obj()->is_master_user() || is_network_admin()) {
				return;
			}
			?>
			<script>
				jQuery(window).on('load', function () {
					var defaultRole = <?php echo json_encode(sanitize_text_field(VG_Admin_To_Frontend_Obj()->get_settings('default_user_role_add'))); ?>;
					if (defaultRole && jQuery('#createuser').length) {
						var $role = jQuery('#createuser select#role');
						$role.val(defaultRole);
						if ($role.val() === defaultRole) {
							$role.parents('tr').hide();
						}
					}
				});
			</script>
			<?php
		}

		function allow_safe_redirects_to_dashboard_subsite($hosts) {
			$frontend_dashboard_url = VG_Admin_To_Frontend_Obj()->get_settings('redirect_to_frontend');
			if (is_multisite() && $frontend_dashboard_url) {
				$dashboard_host = parse_url($frontend_dashboard_url, PHP_URL_HOST);
				if (!in_array($dashboard_host, $hosts, true)) {
					$hosts[] = $dashboard_host;
				}
			}
			return $hosts;
		}

		function maybe_redirect_to_the_dashboard__premium_only($url, $requested_redirect_to, $user) {
			if (strpos($url, 'user_site_base_url') !== false) {
				$blog_id = WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content();
				if (is_multisite()) {
					$url = str_replace(array('{user_site_base_url}', 'http://{user_site_base_url}', 'https://{user_site_base_url}', 'http://user_site_base_url', 'https://user_site_base_url'), get_site_url($blog_id), $url);
				}
			}
			$url = str_replace(array('{user_site_base_url}', 'http://{user_site_base_url}', 'https://{user_site_base_url}', 'http://user_site_base_url', 'https://user_site_base_url'), get_site_url(), $url);
			$frontend_dashboard_url = VG_Admin_To_Frontend_Obj()->get_settings('redirect_to_frontend');
			// We will change the login_redirect URL if a front end dashboard URL is set, and the user is not allowed to access wp-admin
			if ($frontend_dashboard_url && $this->is_frontend_dashboard_user($user)) {
				$url = $frontend_dashboard_url;
			} elseif (!is_wp_error($user) && is_super_admin($user->ID)) {
				$url = admin_url('/');
			}
			return $url;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Advanced::$instance) {
				WPFA_Advanced::$instance = new WPFA_Advanced();
				WPFA_Advanced::$instance->init();
			}
			return WPFA_Advanced::$instance;
		}

		function is_frontend_dashboard_user($user) {
			$out = false;
			if (is_object($user) && !is_wp_error($user)) {
				$user_id = $user->ID;
			} elseif (is_int($user)) {
				$user_id = $user;
			} else {
				return $out;
			}

			if (VG_Admin_To_Frontend_Obj()->is_master_user($user_id)) {
				return $out;
			}

			if (VG_Admin_To_Frontend_Obj()->get_settings('dashboard_users_role')) {
				$roles = array_map('trim', explode(',', VG_Admin_To_Frontend_Obj()->get_settings('dashboard_users_role')));
				if (!empty($roles) && VG_Admin_To_Frontend_Obj()->user_has_any_role($roles, $user)) {
					$out = true;
				}
			} elseif (!VG_Admin_To_Frontend_Obj()->is_master_user($user_id)) {
				$out = true;
			}

			// If this is a multisite network and user is only administrator of blog 1, allow wp-admin
			if (is_multisite()) {
				$user_belongs_to_blogs = get_blogs_of_user($user_id);
				$first_blog = end($user_belongs_to_blogs);
				if (count($user_belongs_to_blogs) === 1 && $first_blog->userblog_id === 1 && VG_Admin_To_Frontend_Obj()->user_has_any_role(array('administrator'), $user)) {
					$out = false;
				}
			}
			return $out;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Advanced_Obj')) {

	function WPFA_Advanced_Obj() {
		return WPFA_Advanced::get_instance();
	}

}
WPFA_Advanced_Obj();
