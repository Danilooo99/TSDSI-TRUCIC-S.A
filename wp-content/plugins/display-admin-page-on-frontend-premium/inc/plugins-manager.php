<?php
// Experimental feature: Allow to disable plugins for specific admin pages
if (!class_exists('WPFA_Plugins_Manager')) {

	class WPFA_Plugins_Manager {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			// Dont load this feature if the "Plugin Organizer" plugin exists because there is a conflict that disabled 
			// plugins on one admin page become disabled everywhere and it's redundant
			if (class_exists('PluginOrganizerMU')) {
				return;
			}
			if (dapof_fs()->can_use_premium_code__premium_only()) {
				add_filter('vg_plugin_sdk/settings/' . VG_Admin_To_Frontend::$textname . '/options', array($this, 'add_global_option'));

				if (VG_Admin_To_Frontend_Obj()->get_settings('enable_plugins_manager')) {
					if (is_admin()) {
						add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_quick_settings'));
						add_action('vg_plugin_sdk/settings/' . VG_Admin_To_Frontend::$textname . '/after_saving', array($this, 'after_global_settings_saved'));
					} else {
						add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_quick_settings_field'));
					}
				}
			}
		}

		function _get_mu_plugin_path() {
			$new_file_path = WP_CONTENT_DIR . '/mu-plugins/wpfa-plugins-manager-mu.php';
			return $new_file_path;
		}

		function _activate_mu_plugin() {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			if (!is_dir(WP_CONTENT_DIR . '/mu-plugins')) {
				wp_mkdir_p(WP_CONTENT_DIR . '/mu-plugins');
			}

			$new_file_path = $this->_get_mu_plugin_path();
			$old_file_path = __DIR__ . '/plugins-manager-mu.tmp.php';

			copy($old_file_path, $new_file_path);
		}

		function _deactivate_mu_plugin() {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			$new_file_path = $this->_get_mu_plugin_path();
			if (file_exists($new_file_path)) {
				unlink($new_file_path);
			}
		}

		function after_global_settings_saved($options) {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			if (empty($options['enable_plugins_manager'])) {
				$this->_deactivate_mu_plugin();
				return;
			}
			$this->_activate_mu_plugin();
		}

		function save_quick_settings($post_id) {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			if (!VG_Admin_To_Frontend_Obj()->get_settings('enable_plugins_manager')) {
				return;
			}
			if (empty($_REQUEST['wpfa_disabled_plugins']) || !is_array($_REQUEST['wpfa_disabled_plugins'])) {
				$_REQUEST['wpfa_disabled_plugins'] = array();
			}

			$allowed_plugins = $this->get_plugins_that_can_be_disabled();
			$sanitized = array();
			foreach ($_REQUEST['wpfa_disabled_plugins'] as $plugin_id) {
				if (in_array($plugin_id, $allowed_plugins)) {
					$sanitized[] = sanitize_text_field($plugin_id);
				}
			}
			update_post_meta($post_id, 'wpfa_disabled_plugins', $sanitized);

			if (empty($_REQUEST['wpfa_iframe_urls'])) {
				return;
			}
			$wpfa_disabled_plugins_file_path = WP_CONTENT_DIR . '/mu-plugins/wpfa-disabled-plugins.php';
			if (file_exists($wpfa_disabled_plugins_file_path)) {
				include $wpfa_disabled_plugins_file_path;
			}
			if (empty($GLOBALS["wpfa_disabled_plugins"]) || !is_array($GLOBALS["wpfa_disabled_plugins"])) {
				$GLOBALS["wpfa_disabled_plugins"] = array();
			}
			$iframe_urls = array_map('esc_url', explode(',', $_REQUEST['wpfa_iframe_urls']));
			foreach ($iframe_urls as $iframe_url) {
				$iframe_url = remove_query_arg(array('vgfa_source', 'wpfa_id'), html_entity_decode($iframe_url));
				if (is_multisite()) {
					$url_parts = parse_url($iframe_url);
					$iframe_url = str_replace($url_parts['scheme'] . '://' . $url_parts['host'], '', $iframe_url);
					if (defined('SUBDOMAIN_INSTALL') && !SUBDOMAIN_INSTALL) {
						$uri_parts = array_filter(explode('/', $iframe_url));
						array_shift($uri_parts);
						$iframe_url = '/' . implode('/', $uri_parts);
					}
				}
				$GLOBALS["wpfa_disabled_plugins"][$iframe_url] = $sanitized;
			}
			file_put_contents($wpfa_disabled_plugins_file_path, '<?php $GLOBALS["wpfa_disabled_plugins"] = ' . var_export($GLOBALS["wpfa_disabled_plugins"], true) . ';');
			$this->_activate_mu_plugin();
		}

		function get_plugins_that_can_be_disabled() {
			$active_plugins = get_option('active_plugins');
			if (is_multisite()) {
				$network_active_plugins = get_site_option('active_sitewide_plugins');
			} else {
				$network_active_plugins = array();
			}
			$active_plugins = array_unique(array_merge($active_plugins, array_keys($network_active_plugins)));

			if (!function_exists('get_plugins')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Include deactivated plugins in the list, so we can disable them in the dashboard pages in case they are active in the subsites
			$all_plugins = array_keys(get_plugins());
			foreach ($all_plugins as $plugin_path) {
				$active_plugins[] = $plugin_path;
			}

			// Exclude any important plugin from the list, like WPFA, WU, security, cache, membership plugins
			$disallowed_plugins = array(
				'display-admin-page-on-frontend/index.php',
				'display-admin-page-on-frontend-premium/index.php',
				'wp-ultimo/wp-ultimo.php',
			);
			$disallowed_keywords = array('cache', 'security', 'limit', 'attempt', 'protect', 'firewall', 'spam', 'snippet', 'permission', 'optimize', 'speed', 'wordfence', 'scanner', 'safe', 'guard', 'admin-menu-editor', 'gotmls', 'cerber', 'lock', 'block-bad-queries', 'captcha', 'construction', 'hide', 'https', 'fail2ban', 'defender', 'geo-block', 'block', 'auth', 'custom-wp-admin', 'secure');
			foreach ($active_plugins as $index => $plugin_id) {
				if (in_array($plugin_id, $disallowed_plugins, true) || preg_match('/(' . implode('|', $disallowed_keywords) . ')/', $plugin_id)) {
					unset($active_plugins[$index]);
				}
			}
			return $active_plugins;
		}

		function render_quick_settings_field($post) {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			if (!VG_Admin_To_Frontend_Obj()->get_settings('enable_plugins_manager')) {
				return;
			}
			//// Check if get_plugins() function exists. This is required on the front end of the
// site, since it is in a file that is normally only loaded in the admin.
			if (!function_exists('get_plugins')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$all_plugins = get_plugins();
			$active_plugins = $this->get_plugins_that_can_be_disabled();
			$current_value = get_post_meta($post->ID, 'wpfa_disabled_plugins', true);
			if (empty($current_value) || !is_array($current_value)) {
				$current_value = array();
			}
			?>
			<div class="field plugins-manager">
				<label><?php _e('Experimental: Plugins that you will not use in this admin page', VG_Admin_To_Frontend::$textname); ?> <a href="#" data-tooltip="down" aria-label="<?php esc_attr_e('We will deactivate the plugins for this admin page only to improve performance, they will run in other pages as usual. This is not a security feature. This will apply to administrators too so use carefully. Never deactivate security plugins or any plugin related to user permissions', VG_Admin_To_Frontend::$textname); ?>">(?)</a>
				</label>
				<div class="wpfa-plugins-list">
					<?php
					foreach ($active_plugins as $plugin_id) {
						if (!isset($all_plugins[$plugin_id])) {
							continue;
						}
						$plugin = $all_plugins[$plugin_id];
						?>
						<label><input type="checkbox"  <?php checked(in_array($plugin_id, $current_value, true)); ?> name="wpfa_disabled_plugins[]" value="<?php echo esc_attr($plugin_id); ?>"> <?php echo esc_html($plugin['Name']); ?></label><br>
						<?php
					}
					?>
				</div>
				<hr>
			</div>
			<?php
		}

		function add_global_option($sections) {
			$sections['solutions']['fields'][] = array(
				'id' => 'enable_plugins_manager',
				'type' => 'switch',
				'title' => __('Experimental feature: Allow to disable plugins for specific admin pages?', VG_Admin_To_Frontend::$textname),
				'desc' => __('If you enable this option, we will allow you to deactivate plugins on specific frontend admin pages to make everything load faster. Note, it might cause issues if you deactivate plugins required by other plugins. Deactivate this option if it causes issues. This is NOT A SECURITY feature, this is just for performance.', VG_Admin_To_Frontend::$textname),
				'default' => false,
			);
			return $sections;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Plugins_Manager::$instance) {
				WPFA_Plugins_Manager::$instance = new WPFA_Plugins_Manager();
				WPFA_Plugins_Manager::$instance->init();
			}
			return WPFA_Plugins_Manager::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Plugins_Manager_Obj')) {

	function WPFA_Plugins_Manager_Obj() {
		return WPFA_Plugins_Manager::get_instance();
	}

}
WPFA_Plugins_Manager_Obj();
