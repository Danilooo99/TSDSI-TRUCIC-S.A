<?php
if (!class_exists('WPFA_Plugin_Restrictions')) {

	class WPFA_Plugin_Restrictions {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (dapof_fs()->is_plan('platform', true)) {
				if (is_admin()) {
					add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_quick_settings'));
				} else {
					add_filter('the_content', array($this, 'notify_super_admins_why_page_wont_load'));
					add_action('template_redirect', array($this, 'redirect_unavailable_page'));
					add_filter('wp_get_nav_menu_items', array($this, 'remove_pages_from_menu'), 10, 3);
					add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_quick_settings_field'));
				}
				add_filter('vg_plugin_sdk/settings/' . VG_Admin_To_Frontend::$textname . '/options', array($this, 'add_global_option'));
			}
		}

		function notify_super_admins_why_page_wont_load($content) {
			$post_id = get_the_ID();
			if (VG_Admin_To_Frontend_Obj()->is_master_user() && !$this->page_id_can_be_seen($post_id)) {
				// Check if get_plugins() function exists. This is required on the front end of the
// site, since it is in a file that is normally only loaded in the backend.
				if (!function_exists('get_plugins')) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$all_plugins = get_plugins();
				$required_plugin = get_post_meta($post_id, 'wpfa_required_plugin', true);
				$plugin_name = isset($all_plugins[$required_plugin]) ? $all_plugins[$required_plugin]['Name'] : $required_plugin;
				ob_start();
				include VG_Admin_To_Frontend::$dir . '/views/frontend/required-plugin-missing.php';
				$message = ob_get_clean();
				$content = $message . $content;
			}
			return $content;
		}

		function _get_site_active_plugins() {
			if (is_multisite()) {
				$active_plugins = get_blog_option(WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content(), 'active_plugins');
				$network_active_plugins = get_site_option('active_sitewide_plugins');
				$active_plugins = array_unique(array_merge($active_plugins, array_keys($network_active_plugins)));
			} else {
				$active_plugins = get_option('active_plugins');
			}
			return $active_plugins;
		}

		function page_id_can_be_seen($page_id, $active_plugins = null, $ultimo_filters_handled_externally = false) {
			if (!VG_Admin_To_Frontend_Obj()->get_settings('enable_required_plugins_check')) {
				return true;
			}
			$current_value = get_post_meta($page_id, 'wpfa_required_plugin', true);
			if (empty($current_value)) {
				return true;
			}
			$out = true;
			if (is_multisite()) {

				// Run WP Ultimo filters, so we include plugins forcefully activated/deactivated by WP Ultimo.
				if (function_exists('WP_Ultimo') && !$ultimo_filters_handled_externally && class_exists('\WP_Ultimo\Limits\Plugin_Limits')) {
					$original_blog_id = get_current_blog_id();
					switch_to_blog(WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content());
					$wp_ultimo_plugin_limits = \WP_Ultimo\Limits\Plugin_Limits::get_instance();
					add_filter('site_option_active_sitewide_plugins', array($wp_ultimo_plugin_limits, 'deactivate_network_plugins'));
					add_filter('option_active_plugins', array($wp_ultimo_plugin_limits, 'deactivate_plugins'));
				}

				$active_plugins = $active_plugins ? $active_plugins : $this->_get_site_active_plugins();

				if (function_exists('WP_Ultimo') && !$ultimo_filters_handled_externally && class_exists('\WP_Ultimo\Limits\Plugin_Limits')) {
					remove_filter('site_option_active_sitewide_plugins', array($wp_ultimo_plugin_limits, 'deactivate_network_plugins'));
					remove_filter('option_active_plugins', array($wp_ultimo_plugin_limits, 'deactivate_plugins'));
					switch_to_blog($original_blog_id);
				}
			} else {
				$active_plugins = $active_plugins ? $active_plugins : $this->_get_site_active_plugins();
			}
			if (!in_array($current_value, $active_plugins, true)) {
				$out = false;
			}
			return $out;
		}

		function redirect_unavailable_page() {
			if (is_singular() && !VG_Admin_To_Frontend_Obj()->is_master_user() && !$this->page_id_can_be_seen(get_queried_object_id())) {
				$redirect_to = VG_Admin_To_Frontend_Obj()->get_settings('redirect_to_frontend', home_url('/'));
				wp_redirect(esc_url($redirect_to));
				exit();
			}
		}

		function remove_pages_from_menu($items, $menu, $args) {

			$available_menu_items = $items;
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {

				// We get the active plugins and run the filters here for performance reasons
				// If we do this inside the page_id_can_be_seen() method, it will waste resources
				// with those steps on dozens of menu items.
				// Run WP Ultimo filters, so we include plugins forcefully activated/deactivated by WP Ultimo.
				if (is_multisite() && function_exists('WP_Ultimo')) {
					$original_blog_id = get_current_blog_id();
					switch_to_blog(WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content());
					$wp_ultimo_plugin_limits = \WP_Ultimo\Limits\Plugin_Limits::get_instance();
					add_filter('site_option_active_sitewide_plugins', array($wp_ultimo_plugin_limits, 'deactivate_network_plugins'));
					add_filter('option_active_plugins', array($wp_ultimo_plugin_limits, 'deactivate_plugins'));
				}
				$active_plugins = $this->_get_site_active_plugins();

				if (is_multisite() && function_exists('WP_Ultimo')) {
					remove_filter('site_option_active_sitewide_plugins', array($wp_ultimo_plugin_limits, 'deactivate_network_plugins'));
					remove_filter('option_active_plugins', array($wp_ultimo_plugin_limits, 'deactivate_plugins'));
					switch_to_blog($original_blog_id);
				}

				$available_menu_items = array();
				foreach ($items as $item) {
					if ($item->type === 'post_type' && !$this->page_id_can_be_seen((int) $item->object_id, $active_plugins, true)) {
						continue;
					}

					$available_menu_items[] = $item;
				}
			}

			return $available_menu_items;
		}

		function save_quick_settings($post_id) {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			if (!VG_Admin_To_Frontend_Obj()->get_settings('enable_required_plugins_check')) {
				return;
			}
			if (empty($_REQUEST['wpfa_required_plugin'])) {
				$_REQUEST['wpfa_required_plugin'] = '';
			}

			update_post_meta($post_id, 'wpfa_required_plugin', sanitize_text_field($_REQUEST['wpfa_required_plugin']));
		}

		function render_quick_settings_field($post) {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			if (!VG_Admin_To_Frontend_Obj()->get_settings('enable_required_plugins_check')) {
				return;
			}
			//// Check if get_plugins() function exists. This is required on the front end of the
// site, since it is in a file that is normally only loaded in the backend.
			if (!function_exists('get_plugins')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Show all plugins, even deactivated, because it's helpful to disable plugins in the dashboard site
			// for performance reasons and only enable them in the customer sites
			$all_plugins = get_plugins();
			$current_value = get_post_meta($post->ID, 'wpfa_required_plugin', true);
			if (empty($current_value)) {
				$current_value = '';
			}
			?>
			<div class="field plugins-manager">
				<label><?php _e('Required plugin', VG_Admin_To_Frontend::$textname); ?> <a href="#" data-tooltip="down" aria-label="<?php esc_attr_e('We will remove this page from the menus when the selected plugin is not activated.', VG_Admin_To_Frontend::$textname); ?>">(?)</a>
				</label>
				<select name="wpfa_required_plugin">
					<option value="">--</option>
					<?php
					foreach ($all_plugins as $plugin_id => $plugin) {
						$plugin_name = $plugin['Name'];
						?>
						<option <?php selected($plugin_id === $current_value); ?> value="<?php echo esc_attr($plugin_id); ?>"><?php echo esc_html($plugin_name); ?></option>
						<?php
					}
					?>
				</select>
			</div>
			<hr>
			<?php
		}

		function add_global_option($sections) {
			$sections['access-restrictions']['fields'][] = array(
				'id' => 'enable_required_plugins_check',
				'type' => 'switch',
				'title' => __('Hide pages when a required plugin is deactivated?', VG_Admin_To_Frontend::$textname),
				'desc' => __('If you enable this option, we will allow you to select the required plugin on every page that you create. So we can remove the dashboard pages from  the menus when the required plugin is not activated. This is good if you want to allow users to activate and deactivate plugins and automatically adjust the frontend dashboard menus.', VG_Admin_To_Frontend::$textname),
				'default' => false,
			);
			return $sections;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Plugin_Restrictions::$instance) {
				WPFA_Plugin_Restrictions::$instance = new WPFA_Plugin_Restrictions();
				WPFA_Plugin_Restrictions::$instance->init();
			}
			return WPFA_Plugin_Restrictions::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Plugin_Restrictions_Obj')) {

	function WPFA_Plugin_Restrictions_Obj() {
		return WPFA_Plugin_Restrictions::get_instance();
	}

}
WPFA_Plugin_Restrictions_Obj();
