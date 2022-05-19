<?php
if (!class_exists('WPFA_WP_Ultimo')) {

	class WPFA_WP_Ultimo {

		static private $instance = false;
		var $checking_site_url = false;

		private function __construct() {
			
		}

		function init() {

			if (!is_multisite()) {
				return;
			}
			if (!function_exists('WP_Ultimo')) {
				return;
			}
			/**
			 * WP Ultimo 2.0 todos:
			 * Test every integration feature
			 */
			if (dapof_fs()->is_plan('platform', true)) {
				if (is_admin()) {
					add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 99, 2);
					add_filter('vg_plugin_sdk/settings/' . VG_Admin_To_Frontend::$textname . '/options', array($this, 'add_global_options'));
					add_filter('vg_admin_to_frontend/potential_issues', array($this, 'add_potential_issues'));
					if (!is_network_admin()) {
						add_filter('vg_admin_to_frontend/is_page_blacklisted', array($this, 'whitelist_wu_account_pages'));
						if (class_exists('WP_UltimoWooSubscriptions')) {
							add_filter('vg_admin_to_frontend/skip_frontend_dashboard_enforcement', array($this, 'allow_dashboard_page_if_using_blitz_cartflows'));
						}
						add_action('admin_init', array($this, 'handle_signup_redirect'), 9);
					}
					add_action('admin_init', array($this, 'remove_iframe_protection'));
					// v1
					add_filter("wu_gateway_integration_button_paypal", array($this, 'open_paypal_integration_in_new_tab'));
					add_filter('vg_admin_to_frontend/backend/js_data', array($this, 'add_flag_to_frontend_links_for_sso'));
				} else {
					add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_meta_box'));
					add_action('wp', array($this, 'maybe_redirect_to_permissions_page'));
					add_filter('wp_get_nav_menu_items', array($this, 'remove_pages_from_menu'), 10, 3);
					add_action('wp_head', array($this, 'notify_if_new_site_page_is_missing'));
					add_action('wp_footer', array($this, 'render_footer_html_per_plan'), 100);
					add_filter('wp_frontend_admin/render_page_shortcode/warnings', array($this, 'add_warnings_before_shortcode'), 10, 3);
					if ($this->_is_v2()) {
						add_filter('wp_frontend_admin/current_site_info/allowed_fields', array($this, 'allow_extra_fields_in_site_shortcode'));
						add_filter('wp_frontend_admin/current_site_info/output', array($this, 'add_extra_values_to_site_shortcode'), 10, 3);
						add_filter('wp_frontend_admin/can_redirect_page_to_login', array($this, 'allow_to_load_ultimo_signup_without_login'));
					}
				}
				add_filter('wp_frontend_admin/is_user_allowed_to_view_page', array($this, 'is_user_allowed_to_view_page_from_shortcode'), 10, 3);
				add_filter('wp_ultimo_redirect_url_after_signup', array($this, 'wpultimo_signup_redirect_to'), 10, 3);
				add_action('template_redirect', array($this, 'modify_site_id_for_previews_late'));
				add_filter('login_url', array($this, 'add_flag_to_login_urls_for_sso'), 100);
				add_filter('mercator.sso.login_url', array($this, 'add_flag_to_login_urls_for_sso'), 100);
				add_filter('wp_frontend_admin/login_url', array($this, 'sso_use_main_site_login_url'));
				if (!is_network_admin() && !wp_doing_ajax()) {
					add_filter('site_url', array($this, 'sso_add_flag_to_site_urls'), 100);
				}
				add_filter('wp_frontend_admin/page_id_from_path/prepared_path', array($this, 'find_the_right_account_page'));
				if ($this->_is_v2()) {
					add_filter('wu_current_site_get_manage_url', array($this, 'modify_site_manage_url'), 10, 2);
				}
			}
			add_action('wu_duplicate_site', array($this, 'after_wu_duplicate'));
		}

		function _current_page_contains_shortcode($shortcode, $post = null) {
			if (!$post) {
				$post = get_queried_object();
			}

			$out = false;
			if (!$post || !$post instanceof WP_Post) {
				return $out;
			}

			if (strpos($post->post_content, '[' . $shortcode) !== false) {
				$out = true;
			}
			return $out;
		}

		function allow_to_load_ultimo_signup_without_login($allow_to_redirect) {
			if ($this->_current_page_contains_shortcode('wu_checkout')) {
				$allow_to_redirect = false;
			}
			return $allow_to_redirect;
		}

		function modify_site_manage_url($manage_site_url, $id) {

			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				$manage_site_url = WPFA_Global_Dashboard_Obj()->get_dashboard_url($id);
			}
			return $manage_site_url;
		}

		function add_extra_values_to_site_shortcode($out, $field, $blog_id) {
			$original_blog_id = get_current_blog_id();
			switch_to_blog($blog_id);
			$site = wu_get_current_site();
			if ($field === 'wpultimo_subscription_status') {

				if ($this->site_owned_by_user()) {
					$membership = $site->get_membership();
					$out = $membership->get_status_label();
				}
			} elseif ($field === 'wpultimo_subscription_plan') {

				if ($this->site_owned_by_user()) {
					$membership = $site->get_membership();
					$plan = $membership->get_plan();
					$out = $plan->get_name();
				}
			} elseif ($field === 'wpultimo_subscription_plan_group') {

				$membership = $site->get_membership();
				$plan = $membership->get_plan();
				$out = $plan->get_group();
			} elseif ($field === 'wpultimo_site_screenshot') {

				if ($this->site_owned_by_user()) {
					$out = '<img 
                  class="wu-h-48 wu-w-full wu-object-cover wu-block"
                  src="' . esc_url($site->get_featured_image('thumbnail')) . '" 
                  alt="' . sprintf(esc_attr__('Site Image: %s', 'wp-ultimo'), $site->get_title()) . '"
                  style="background-color: rgba(255, 255, 255, 0.5)"
                >';
				}
			} elseif ($field === 'path') {
				$out = str_replace(array('https://', 'http://'), '', remove_query_arg('vgfacache', $site->get_active_site_url()));
			}
			switch_to_blog($original_blog_id);
			return $out;
		}

		function allow_extra_fields_in_site_shortcode($fields) {
			$fields[] = 'wpultimo_subscription_status';
			$fields[] = 'wpultimo_subscription_plan';
			$fields[] = 'wpultimo_subscription_plan_group';
			$fields[] = 'wpultimo_site_screenshot';
			return $fields;
		}

		/**
		 * Modify the PayPal link to open in a new tab (outside the iframe) and 
		 * add #wpfaNoInterfere so WPFA won't intercept the click nor transform it into a stateful link
		 * @param string $a_html
		 * @return string
		 */
		function open_paypal_integration_in_new_tab($a_html) {
			$a_html = str_replace(array('class="button', '&gateway=paypal"'),
					array(' target="_blank" class="button', '&gateway=paypal#wpfaNoInterfere"'), $a_html);
			return $a_html;
		}

		function find_the_right_account_page($path) {
			if (!$this->_is_v2() && strpos($path, 'admin.php?page=wu-my-account&') === 0) {
				$path = 'admin.php?page=wu-my-account';
			}

			if ($this->_is_v2() && strpos($path, 'admin.php?page=account&') === 0) {
				$path = 'admin.php?page=account';
			}
			return $path;
		}

		function render_footer_html_per_plan() {
			if (!VG_Admin_To_Frontend_Obj()->is_wpfa_page() || !WPFA_Global_Dashboard_Obj()->is_global_dashboard()) {
				return;
			}
			$blog_id = WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content();
			$plan_id = $this->_get_blog_plan_id($blog_id);
			if (!$plan_id) {
				return;
			}
			$html = VG_Admin_To_Frontend_Obj()->get_settings('wu_html_footer_plan' . $plan_id);
			if (empty($html)) {
				return;
			}
			echo $html;
		}

		function sso_add_flag_to_site_urls($url) {
			if (!$this->checking_site_url && !empty($url) && $this->_is_sso_enabled() && is_multisite() && is_user_logged_in() && parse_url($url, PHP_URL_HOST)) {
				// Prevent recursive calls
				$this->checking_site_url = true;
				$current_url = VG_Admin_To_Frontend_Obj()->get_current_url();
				$frontend_hostname = parse_url($url, PHP_URL_HOST);
				$admin_url_parts = array_filter(explode('/', admin_url()));
				$admin_directory = end($admin_url_parts);
				// Add flag if the $url is for the front end of another site
				// We hardcoded wp-admin in the conditional because when they change wp-admin with a custom URL using security plugins
				// The URL here might have the new path or the old path so we must ensure the URL is not using any of both paths 
				if (strpos($current_url, $frontend_hostname) === false && strpos($url, $admin_directory) === false && strpos($url, 'wp-admin') === false) {
					$url = add_query_arg('vgfacache', wp_generate_password(4, false, false), $url);
				}
				$this->checking_site_url = false;
			}
			return $url;
		}

		function sso_use_main_site_login_url($login_url) {
			$wpfa_login_url = VG_Admin_To_Frontend_Obj()->get_settings('login_page_url');
			if ($this->_is_sso_enabled() && is_multisite() && defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL && VG_Admin_To_Frontend_Obj()->get_settings('enable_sso_support') && $wpfa_login_url && is_ssl()) {

				$slug = basename($wpfa_login_url);
				// Change the URL to use the main site's 
				$sites = array_reverse(get_sites(array('order' => 'ASC')));
				foreach ($sites as $site) {
					$login_url = str_ireplace(get_site_url($site->blog_id), '', $login_url);
				}
				$login_url = get_home_url(1, $login_url);
			}
			return $login_url;
		}

		function add_flag_to_login_urls_for_sso($login_url) {
			if ($this->_is_sso_enabled() && VG_Admin_To_Frontend_Obj()->get_settings('enable_sso_support') && is_ssl()) {
				$login_url = add_query_arg('vgfacache', wp_generate_password(4, false, false), $login_url);
			}
			return $login_url;
		}

		function add_flag_to_frontend_links_for_sso($data) {
			if (VG_Admin_To_Frontend_Obj()->get_settings('enable_sso_support') && is_ssl()) {
				$data['wu_sso_enabled'] = $this->_is_sso_enabled();
			}
			return $data;
		}

		function notify_if_new_site_page_is_missing() {
			if (!$this->_is_v2() && !VG_Admin_To_Frontend_Obj()->is_master_user() && $_SERVER['QUERY_STRING'] === 'page=wu-new-site&vgfa_frontend_url=1') {
				include VG_Admin_To_Frontend::$dir . '/views/frontend/new-site-page-missing.php';
				die();
			}
		}

		function remove_iframe_protection() {
			if (!is_super_admin() || $this->_is_v2() || !is_network_admin()) {
				return;
			}

			$file_path = WP_PLUGIN_DIR . '/wp-ultimo/assets/js/wu-template-preview.min.js';
			if (!file_exists($file_path) || !is_writable($file_path)) {
				return;
			}

			$original_contents = file_get_contents($file_path);
			$contents = str_replace(',top!=self&&window.open(self.location.href,"_top");', ';', $original_contents);
			if ($contents !== $original_contents && $contents) {
				file_put_contents($file_path, $contents);
			}
		}

		function _activate_license_on_site($new_id) {
			if (!doing_action('wp_initialize_site')) {
				$new_site = get_site($new_id);
				$admin_email = get_blog_option($new_id, 'admin_email');
				$user_id = email_exists($admin_email);
				$args = array();
				$args['user_id'] = $user_id;
				$args['domain'] = $new_site->domain;
				$args['path'] = $new_site->path;
				$args['network_id'] = $new_site->network_id;
				$args['options'] = array();
				do_action('wp_initialize_site', $new_site, $args);
			}
		}

		/**
		 * Fix Freemius license issue: duplicate entity ID
		 * @param array $duplicated
		 */
		function after_wu_duplicate($duplicated) {
			$source_id = get_current_blog_id();
			$new_id = $duplicated['site_id'];

			$source_fs_accounts = get_blog_option($source_id, 'fs_accounts');
			$new_fs_accounts = get_blog_option($new_id, 'fs_accounts');

			if ($new_fs_accounts && $source_fs_accounts && $source_fs_accounts['unique_id'] === $new_fs_accounts['unique_id']) {
				delete_blog_option($new_id, 'fs_accounts');
				delete_blog_option($new_id, 'fs_dbg_accounts');

				$this->_activate_license_on_site($new_id);
			}
		}

		function _is_dashboard_page() {
			$current_url = remove_query_arg('vgfa_wu_after_signup', VG_Admin_To_Frontend_Obj()->get_current_url());
			$required_url = admin_url('/');
			$out = false;
			if ($current_url === $required_url) {
				$out = true;
			}
			return $out;
		}

		function site_owned_by_user() {

			$site = wu_get_current_site();
			$site_owned_by_user = false;
			if ($site) {
				if ($this->_is_v2()) {
					$site_owned_by_user = $site->get_type() === 'customer_owned';
				} else {
					$site_owned_by_user = $site->is_user_owner();
				}
			}
			return $site_owned_by_user;
		}

		function _user_owns_this_site($user_id = null) {
			if (!$user_id) {
				$user_id = get_current_user_id();
			}

			$site = wu_get_current_site();
			if (!$site) {
				return false;
			}
			$owner_id = false;
			if ($this->_is_v2()) {
				$customer = $site->get_customer();
				if ($customer) {
					$owner_id = $customer->get_user_id();
				}
			} elseif ($site->site_owner) {
				$owner_id = $site->site_owner->ID;
			}
			return $user_id === $owner_id;
		}

		function _is_sso_enabled() {
			if ($this->_is_v2()) {
				$enabled = function_exists('wu_get_setting') ? wu_get_setting('enable_sso') : wu_get_setting_early('enable_sso');

				if (has_filter('mercator.sso.enabled')) {
					$enabled = apply_filters_deprecated('mercator.sso.enabled', $enabled, '2.0.0', 'wu_sso_enabled');
				}
				$enabled = apply_filters('wu_sso_enabled', (bool) $enabled);
			} else {
				$enabled = function_exists('\Mercator\SSO\is_enabled') ? \Mercator\SSO\is_enabled() : false;
			}
			return $enabled;
		}

		function allow_dashboard_page_if_using_blitz_cartflows($skip) {
			$site_owned_by_user = $this->site_owned_by_user();

			if ($site_owned_by_user && !empty($_GET['vgfa_wu_after_signup']) && $this->_is_dashboard_page()) {
				$skip = true;
			}
			return $skip;
		}

		function whitelist_wu_account_pages($is_blacklisted) {
			if ($this->_user_owns_this_site()) {
				// Allow dashboard page after sign up because some WU gateways make a JS redirection here
				if ($this->site_owned_by_user() && !empty($_GET['vgfa_wu_after_signup']) && $this->_is_dashboard_page()) {
					$is_blacklisted = false;
				}

				// Allow the WU account page because some WU extensions make JS redirections here
				if (!$this->_is_v2() && !empty($_GET['page']) && $_GET['page'] === 'wu-my-account') {
					$is_blacklisted = false;
				}
				if ($this->_is_v2() && !empty($_GET['page']) && $_GET['page'] === 'account') {
					$is_blacklisted = false;
				}
			}
			return $is_blacklisted;
		}

		function modify_site_id_for_previews_late() {
			if (!is_admin() && VG_Admin_To_Frontend_Obj()->is_master_user() && is_singular()) {
				add_filter('wp_frontend_admin/site_id_for_admin_content', array($this, 'modify_site_id_for_previews'));
			}
		}

		function modify_site_id_for_previews($site_id) {
			global $wpdb;
			if (empty($GLOBALS['wpfa_current_shortcode']) || !preg_match('/(wu-my-account|account|index\.php)/', $GLOBALS['wpfa_current_shortcode']['page_url'])) {
				return $site_id;
			}


			if ($this->_is_v2()) {
				// Get first site owned by the current user
				$sites_owned_by_current_user = wu_get_sites(array(
					'number' => 1,
					'meta_query' => array(
						'customer_id' => array(
							'key' => 'wu_customer_id',
							'value' => get_current_user_id()
						),
					),
				));
				// Or get a site owned by any user
				if (!$sites_owned_by_current_user) {
					$sites_owned_by_current_user = wu_get_sites(array(
						'number' => 1,
						'meta_query' => array(
							'type' => array(
								'key' => 'wu_type',
								'value' => 'customer_owned',
							)
						),
					));
				}
				if ($sites_owned_by_current_user) {
					$first_blog_id_with_owner = $sites_owned_by_current_user[0]->get_id();
				}
			} else {
				$first_blog_id_with_owner = (int) $wpdb->get_var($wpdb->prepare("SELECT ws.site_id FROM {$wpdb->base_prefix}blogs wb 
LEFT JOIN {$wpdb->base_prefix}wu_site_owner ws 
ON wb.blog_id = ws.site_id 
WHERE ws.user_id = %d  ORDER BY ws.site_id DESC LIMIT 1", get_current_user_id()));

				if (!$first_blog_id_with_owner) {
					$first_blog_id_with_owner = (int) $wpdb->get_var("SELECT site_id FROM {$wpdb->base_prefix}wu_site_owner ORDER BY site_id DESC LIMIT 1");
				}

				// Make sure that the super admin can access the site for the previews
				if ($first_blog_id_with_owner && !is_user_member_of_blog(get_current_user_id(), $first_blog_id_with_owner) && is_super_admin()) {
					add_user_to_blog($first_blog_id_with_owner, get_current_user_id(), 'administrator');
				}
			}
			if ($first_blog_id_with_owner) {
				$site_id = $first_blog_id_with_owner;
			}
			return $site_id;
		}

		function _is_site_active() {
			$site = wu_get_current_site();
			if ($this->_is_v2()) {
				$membership = $site->get_membership();
				$out = is_object($membership) && in_array($membership->get_status(), array('active', 'trialing'), true);
			} else {
				$subscription = $site->get_subscription();
				$out = $subscription->is_active();
			}
			return $out;
		}

		function add_warnings_before_shortcode($warnings_html, $final_url, $page_url) {
			$original_blog_id = get_current_blog_id();
			$blog_id = WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content();
			switch_to_blog($blog_id);
			if ($this->site_owned_by_user()) {
				$is_site_active = $this->_is_site_active();
				if (!$is_site_active) {
					$warnings_html['wu_subscription_expired'] = __('<p>Warning: Your subscription has expired. Please renew your subscription in your account page to not lose access to your site.</p>', VG_Admin_To_Frontend::$textname);
				}
			}
			switch_to_blog($original_blog_id);
//			if ($this->_is_sso_enabled() && !$this->_is_v2() && VG_Admin_To_Frontend_Obj()->get_settings('enable_sso_support') && 'http' === parse_url(get_option('home'), PHP_URL_SCHEME)) {
//				$warnings_html['wu_replace_http_with_https_db'] = sprintf(__('<p>Warning: The WP Ultimo single sign on will not work right now because your database has URLs using http instead of https. Please use <a href="%s" target="_blank" class="button">this free plugin</a> to replace all the http URLs with https.</p>', VG_Admin_To_Frontend::$textname), 'https://wordpress.org/plugins/better-search-replace/');
//			}

			return $warnings_html;
		}

		function add_potential_issues($issues) {
			if ($this->_is_sso_enabled()) {
				if (is_ssl() && !defined('WPFA_DISABLE_SSO_FIX') && !$this->_is_v2()) {
					VG_Admin_To_Frontend_Obj()->update_option('enable_sso_support', true);
				}

				$wpfa_login_page_url = VG_Admin_To_Frontend_Obj()->get_settings('login_page_url');
				if (SUBDOMAIN_INSTALL && $wpfa_login_page_url && strpos($wpfa_login_page_url, get_home_url(1)) === false) {
					$issues['ultimo_main_login_url'] = sprintf(__('The WP Ultimo single sign on works only when you log in on the main site in the network. According to the WP Frontend Admin settings, your login page URL is from a subsite. You must change it to use the login URL from the main site. <a href="%s" target="_blank" class="button">Fix it</a>', VG_Admin_To_Frontend::$textname), esc_url(WPFA_Options_Obj()->get_settings_page_url()));
				}
			}

			/* if ($this->_is_v2()) {
			  $sso_enabled = $this->_is_sso_enabled();
			  } else {
			  $settings = get_network_option(null, 'wp-ultimo_settings');
			  $sso_enabled = isset($settings['enable_sso']);
			  }
			  if ($sso_enabled && (int) VG_Admin_To_Frontend_Obj()->get_settings('global_dashboard_id')) {
			  $issues['ultimo_disable_sso'] = sprintf(__('You need to disable the "Single Sign On" in WP Ultimo settings because it is not compatible with our global dashboards feature. The single sign on is not necessary. <a href="%s" target="_blank" class="button">Fix it</a>', VG_Admin_To_Frontend::$textname), esc_url(network_admin_url('admin.php?page=wp-ultimo&wu-tab=domain_mapping')));
			  } */
			return $issues;
		}

		function remove_pages_from_menu($items, $menu, $args) {

			if (!VG_Admin_To_Frontend_Obj()->get_settings('wu_remove_disallowed_pages_from_menus')) {
				return $items;
			}
			$available_menu_items = array();

			foreach ($items as $item) {
				if ($item->type === 'post_type' && !$this->is_user_allowed_to_view_page(true, $item->object_id)) {
					continue;
				}

				$available_menu_items[] = $item;
			}

			return $available_menu_items;
		}

		function add_global_options($sections) {
			$sections['wp_ultimo'] = array(
				'icon' => 'el-icon-cogs',
				'title' => __('WP Ultimo', VG_Admin_To_Frontend::$textname),
				'fields' => array()
			);
			$sections['wp_ultimo']['fields'][] = array(
				'id' => 'wu_remove_disallowed_pages_from_menus',
				'type' => 'switch',
				'title' => __('WP Ultimo: Remove disallowed pages from menus?', VG_Admin_To_Frontend::$textname),
				'desc' => __('By default, we show all the dashboard pages in the menu and the disallowed pages show a message when they are opened, saying they are not allowed or will redirect to a custom URL defined in the setting "Wrong permissions url" (above). You can enable this option to automatically remove the pages from the menu when they are not allowed for the current plan.', VG_Admin_To_Frontend::$textname),
			);
			if ($this->_is_v2()) {
				VG_Admin_To_Frontend_Obj()->update_option('enable_sso_support', false);
			} else {
				$sections['wp_ultimo']['fields'][] = array(
					'id' => 'enable_sso_support',
					'type' => 'switch',
					'title' => __('WP Ultimo: Make the SSO (Single Sign On) work', VG_Admin_To_Frontend::$textname),
					'desc' => __('WP Ultimo v1.x has an error where the SSO will not work most of the times. So we fixed WP Ultimos error inside our plugin and you can activate this option to load our fix automatically. IMPORTANT. This will work only if you use HTTPS for the entire network.', VG_Admin_To_Frontend::$textname),
					'default' => false,
				);
			}
			$plans = $this->_get_plans();
			foreach ($plans as $plan_id => $plan_title) {
				$sections['wp_ultimo']['fields'][] = array(
					'id' => 'wu_html_footer_plan' . $plan_id,
					'type' => 'textarea',
					'validate' => 'html',
					'title' => sprintf(__('Plan %s : Footer html', VG_Admin_To_Frontend::$textname), $plan_title),
					'desc' => __('We will add this html code to all the front end dashboard pages if the current user has the specific WP Ultimo plan. Useful for showing support chat widgets for higher plans.', VG_Admin_To_Frontend::$textname),
					'default' => false,
				);
			}
			return $sections;
		}

		/**
		 * If we have defined a login page and the inline login form is not used
		 * We check if the current has our shortcode and redirect the page to the login page
		 * 
		 * @return null
		 */
		function maybe_redirect_to_permissions_page() {
			if (!is_user_logged_in() || !is_singular()) {
				return;
			}
			if (!VG_Admin_To_Frontend_Obj()->is_wpfa_page()) {
				return;
			}

			$allowed = $this->is_user_allowed_to_view_page(true);
			$url = VG_Admin_To_Frontend_Obj()->get_settings('wrong_permissions_page_url', false);
			if (!$allowed && $url && filter_var($url, FILTER_VALIDATE_URL)) {
				wp_safe_redirect(esc_url($url));
				exit();
			}
		}

		/**
		 * If the user comes from the sign up flow and there's no payment needed,
		 * we redirect to the frontend dashboard home.
		 * But if payment is needed, we don't redirect and let WU display the "payment integration" 
		 * needed screen so they can configure their payments and other WU extensions can also 
		 * redirect to their custom checkout pages
		 *
		 * @return null
		 */
		function handle_signup_redirect() {
			if (empty($_GET['vgfa_wu_after_signup'])) {
				return;
			}
			if (!WPFA_Advanced_Obj()->is_frontend_dashboard_user(get_current_user_id())) {
				return;
			}
			$site_owned_by_user = $this->site_owned_by_user();
			if (!$site_owned_by_user) {
				return;
			}

			$site = wu_get_current_site();
			$plan = $site->get_plan();
			$subscription = $site->get_subscription();
			$site_trial = $subscription->get_trial();
			// If the user owns the site, the plan is premium, there is no trial active, and there's no gateway integrated in the wu account,
			// bail, so the wp-admin/index.php screen loads as usual and WU redirects to pay
			if ($site_owned_by_user && !$subscription->integration_status && !$plan->free && !$site_trial) {
				return;
			}
			// If the user doesn't need to pay anything at this point, we redirect to the front end dashboard
			$url = VG_Admin_To_Frontend_Obj()->get_settings('redirect_to_frontend', home_url('/'));
			wp_redirect(esc_url($url));
			exit();
		}

		function wpultimo_signup_redirect_to($url, $site_id, $user_id) {
			if (!WPFA_Advanced_Obj()->is_frontend_dashboard_user($user_id)) {
				return $url;
			}
			$url = add_query_arg('vgfa_wu_after_signup', 1, $url);
			return $url;
		}

		function format_plans($saved_plans) {
			if (empty($saved_plans) || !is_array($saved_plans)) {
				$saved_plans = array();
			}
			$saved_plans = array_unique(array_filter(array_map('intval', $saved_plans)));
			return $saved_plans;
		}

		function is_user_allowed_to_view_page_from_shortcode($allowed = true, $post_id = null, $shortcode_atts = array()) {
			$post_id = get_the_ID();
			return $this->is_user_allowed_to_view_page($allowed, $post_id, $shortcode_atts);
		}

		function _get_blog_plan_id($blog_id) {

			$site = wu_get_site($blog_id);
			$out = null;
			if (!$site) {
				return $out;
			}

			$plan = $site->get_plan();
			if (!$plan) {
				return $out;
			}

			if ($this->_is_v2()) {
				$out = $plan->get_id();
			} else {
				$out = $plan->id;
			}
			return $out;
		}

		function is_user_allowed_to_view_page($allowed = true, $post_id = null, $shortcode_atts = array()) {
			if (!$post_id) {
				$post_id = get_the_ID();
			}
			if (!$post_id) {
				return $allowed;
			}

			if (!empty($shortcode_atts['wu_plans'])) {
				$saved_plans = array_map('intval', array_map('trim', explode(',', $shortcode_atts['wu_plans'])));
			} else {
				$saved_plans = $this->format_plans(get_post_meta($post_id, 'wpfa_wu_plans', true));
			}
			$blog_id = WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content();
			if (!empty($shortcode_atts) && VG_Admin_To_Frontend_Obj()->is_master_user() && !is_admin() && preg_match('/wu-my-account|account/', $shortcode_atts['page_url']) && !$blog_id) {
				return new WP_Error('wp_frontend_admin', sprintf(__('Note from WP Frontend Admin: The Account page is created by WP Ultimo only for sites with a WP Ultimo plan and you have zero sites with a WP Ultimo plan. Please <a href="%s" target="_blank">create one site</a> and associated it with a WP Ultimo plan and you will be able to preview the account page in the frontend here.', VG_Admin_To_Frontend::$textname), network_admin_url('site-new.php')));
			}

			$plan_id = $this->_get_blog_plan_id($blog_id);
			if (!$plan_id) {
				return $allowed;
			}

			if (!empty($saved_plans) && !in_array($plan_id, $saved_plans, true) && !VG_Admin_To_Frontend_Obj()->is_master_user()) {
				$allowed = false;
			}

			return $allowed;
		}

		function _is_v2() {
			return strpos(WP_Ultimo()->version, '2.') === 0;
		}

		function _get_plans() {
			if ($this->_is_v2()) {
				$out = wu_get_plans_as_options();
			} else {
				$raw_plans = WU_Plans::get_plans();
				$out = array();
				foreach ($raw_plans as $plan) {
					$out[$plan->id] = $plan->title;
				}
			}
			return $out;
		}

		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function render_meta_box($post) {
			$saved_plans = $this->format_plans(get_post_meta($post->ID, 'wpfa_wu_plans', true));
			$plans = $this->_get_plans();
			?>
			<div id="wpfa-wu-wrapper" class="field">
				<label>
					<?php echo __('This page is available for these WP Ultimo plans', 'vg_admin_to_frontend'); ?> <a href="#" data-tooltip="down" aria-label="<?php esc_attr_e('If you select a specific plan, we will show the content of this page only for users with the selected plan and users of other plans will be see an error message or redirect to your plans/upgrade page defined in the WP Frontend Admin settings page. Leave this field empty to display this page for all the plans', 'vg_admin_to_frontend'); ?>">(?)</a>					
				</label>
				<?php foreach ($plans as $plan_id => $plan_title) { ?>
					<div class="wpfa-wu-plan-row">
						<label><input <?php checked(in_array($plan_id, $saved_plans, true)); ?> type="checkbox" name="wpfa_wu_plans[]" value="<?php echo (int) $plan_id; ?>"> <?php echo esc_html($plan_title); ?></label>
					</div>
				<?php }
				?>

			</div>
			<hr>
			<?php
		}

		function save_meta_box($post_id, $post) {
			if (!isset($_REQUEST['wpfa_wu_plans'])) {
				return;
			}
			$saved_plans = $this->format_plans($_REQUEST['wpfa_wu_plans']);
			update_post_meta($post_id, 'wpfa_wu_plans', $saved_plans);
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_WP_Ultimo::$instance) {
				WPFA_WP_Ultimo::$instance = new WPFA_WP_Ultimo();
				WPFA_WP_Ultimo::$instance->init();
			}
			return WPFA_WP_Ultimo::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_WP_Ultimo_Obj')) {

	function WPFA_WP_Ultimo_Obj() {
		return WPFA_WP_Ultimo::get_instance();
	}

}
add_action('plugins_loaded', 'WPFA_WP_Ultimo_Obj');
