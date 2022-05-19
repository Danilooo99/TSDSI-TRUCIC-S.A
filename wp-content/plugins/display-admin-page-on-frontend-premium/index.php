<?php
/*
  Plugin Name: WP Frontend Admin (Premium)
  Plugin URI: https://wpfrontendadmin.com/?utm_source=wp-admin&utm_medium=plugins-list
  Description: Display wp-admin pages on the frontend using a shortcode.
  Version: 1.17.0.6
  Author: WP Frontend Admin
  Author Email: josevega@wpfrontendadmin.com
  Author URI: https://wpfrontendadmin.com/?utm_source=wp-admin&utm_medium=plugins-list
  @fs_premium_only /inc/global-page.php, /inc/advanced.php, /inc/compatibility/advanced/, /inc/clean-admin-theme/, /assets/vendor/spinkit.min.css, /assets/css/elementor-templates.css, /inc/plugins-manager.php, /inc/plugins-manager-mu.tmp.php, /views/frontend/site-selector.php, /views/frontend/site-selector-links-list.php, /views/frontend/new-site-page-missing.php, /views/frontend/floating-edit-button.php, /inc/plugin-restrictions.php, /views/frontend/required-plugin-missing.php
  Text Domain: vg_admin_to_frontend
  Domain Path: /lang
  License:

  Copyright 2018 JoseVega (josevega@vegacorp.me)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
require_once 'inc/freemius-init.php';
require_once 'vendor/vg-plugin-sdk/index.php';
require_once 'vendor/vg-plugin-sdk/settings-page.php';

if (!class_exists('VG_Admin_To_Frontend')) {

	class VG_Admin_To_Frontend {

		static private $instance = false;
		static $textname = 'vg_admin_to_frontend';
		static $dir = __DIR__;
		static $file = __FILE__;
		static $version = '1.17.0.6';
		static $name = 'Frontend Admin';
		static $main_admin_id_key = 'vgfa_admin_main_admin_id';
		var $allowed_urls = array();
		var $base_page_id = 'vgca_base_page_id';
		var $redirect_after_save_post = null;
		var $master_users = array();
		var $base_options = array();

		private function __construct() {
			
		}

		function get_upgrade_url() {
			if (function_exists('dapof_fs')) {
				if (is_multisite()) {
					$url = dapof_fs()->checkout_url(WP_FS__PERIOD_MONTHLY, true, array(
						'plan_id' => 11102,
					));
				} else {
					$url = dapof_fs()->checkout_url(WP_FS__PERIOD_ANNUALLY, true);
				}
			} else {
				$url = 'https://wpfrontendadmin.com/go/start-free-trial-wpadmin';
			}
			return $url;
		}

		function get_system_page_ids() {
			global $wpdb;
			$pages_with_wpfa_shortcode = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_content LIKE '%[vg_display_admin_page%' ");
			$pages_with_wpfa_meta = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'is_wpfa_page' AND meta_value = 1");

			$extra_pages_slugs = array_map('trim', explode(',', $this->get_settings('extra_system_pages_slugs', '')));
			$slugs_in_query_placeholders = implode(', ', array_fill(0, count($extra_pages_slugs), '%s'));
			$extra_pages = array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name IN ($slugs_in_query_placeholders)", $extra_pages_slugs)));

			$system_page_ids = array_unique(array_map('intval', array_merge($pages_with_wpfa_shortcode, $pages_with_wpfa_meta, $extra_pages)));
			return apply_filters('wp_frontend_admin/system_page_ids', $system_page_ids);
		}

		function get_main_admin_id() {
			$admin_id = (int) get_option(VG_Admin_To_Frontend::$main_admin_id_key, null);
			if (!empty($_GET['vgfa_is_main_admin']) && current_user_can('manage_options')) {
				$admin_id = (int) $_GET['vgfa_is_main_admin'];
			}
			if (!empty($_GET['vgfa_im_main_admin']) && current_user_can('manage_options')) {
				$admin_id = (int) get_current_user_id();
				$this->set_main_admin_id($admin_id, true);
			}
			if (!current_user_can($this->master_capability())) {
				$admin_id = false;
			}
			return $admin_id;
		}

		function set_main_admin_id($user_id = null, $overwrite = false) {
			if (!$user_id) {
				$user_id = get_current_user_id();
			}
			if ($this->get_main_admin_id() && !$overwrite) {
				return;
			}
			update_option(VG_Admin_To_Frontend::$main_admin_id_key, $user_id);
		}

		function get_settings($key = null, $default = null, $skip_filter = false) {
			if (empty($this->base_options)) {
				if (is_multisite()) {
					$main_options = get_blog_option(1, VG_Admin_To_Frontend::$textname, array());
					if (!empty($main_options['enable_wpmu_mode'])) {
						$options = $main_options;
					}
				}
				if (empty($options)) {
					$options = get_option(VG_Admin_To_Frontend::$textname, array());
				}
				$this->base_options = $options;
			} else {
				$options = $this->base_options;
			}

			$out = $options;
			if (!empty($key)) {
				$out = ( isset($options[$key])) ? $options[$key] : null;
			}
			if (empty($out)) {
				$out = $default;
			}
			return $out;
		}

		function get_plugin_install_url($plugin_slug) {
			$install_plugin_base_url = ( is_multisite() ) ? network_admin_url() : admin_url();
			$install_plugin_url = add_query_arg(array(
				's' => $plugin_slug,
				'tab' => 'search',
				'type' => 'term'
					), $install_plugin_base_url . 'plugin-install.php');
			return $install_plugin_url;
		}

		function init() {
			if ($this->get_settings('excluded_network_sites_ids') && is_multisite()) {
				$excluded_site_ids = array_filter(array_map('intval', explode(',', $this->get_settings('excluded_network_sites_ids'))));
				if (!empty($excluded_site_ids) && in_array(get_current_blog_id(), $excluded_site_ids, true)) {
					return;
				}
			}
			if (dapof_fs()->can_use_premium_code__premium_only()) {
				$all_args = serialize($_GET);
				if (strpos($all_args, 'tb-preview') !== false || (strpos($all_args, 'et_fb') !== false && strpos($all_args, 'et_fb_activation_nonce') === false && empty($_GET['vgfa_referrer']) ) || strpos($all_args, 'et-fb') !== false || strpos($all_args, 'cornerstone')) {
//				if (strpos($all_args, 'tb-preview') !== false || (strpos($all_args, 'et_fb') !== false && strpos($all_args, 'et_fb_activation_nonce') === false ) || strpos($all_args, 'et-fb') !== false || strpos($all_args, 'cornerstone')) {
//				if (strpos($all_args, 'tb-preview') !== false || strpos($all_args, 'cornerstone')) {
					return;
				}
			}
			$this->args = array(
				'main_plugin_file' => __FILE__,
				'show_welcome_page' => true,
				'welcome_page_file' => VG_Admin_To_Frontend::$dir . '/views/welcome-page-content.php',
				'logo' => plugins_url('/assets/imgs/logo.png', __FILE__),
				'plugin_name' => VG_Admin_To_Frontend::$name,
				'plugin_prefix' => 'wpatof_',
				'plugin_version' => VG_Admin_To_Frontend::$version,
				'plugin_options' => get_option(VG_Admin_To_Frontend::$textname, false),
				'buy_link' => $this->get_upgrade_url(),
				'website' => 'https://wpfrontendadmin.com/?utm_source=wp-admin&utm_medium=secondary-pages-logo',
				'buy_link_text' => __('Try premium plugin for FREE - 7 Days', VG_Admin_To_Frontend::$textname),
			);

			$this->vg_plugin_sdk = new VG_Freemium_Plugin_SDK(apply_filters('vg_admin_to_frontend/plugin_sdk_args', $this->args));

			$inc_files = $this->get_files_list(__DIR__ . '/inc/');
			foreach ($inc_files as $file) {
				if (strpos($file, '.tmp.') === false) {
					require_once $file;
				}
			}

			if (is_admin()) {
				add_action('admin_init', array($this, 'identify_source_id'));
				add_action('admin_head', array($this, 'cleanup_admin_page_for_frontend'), 5);
				add_action('admin_menu', array($this, 'register_menu_page'));
				add_action('network_admin_menu', array($this, 'register_menu_page'));
				add_action('admin_init', array($this, 'maybe_redirect_direct_link'));
				add_action('admin_bar_menu', array($this, 'add_direct_link_menu'), 999);
				add_action('wp_ajax_vg_frontend_admin_save_quick_settings', array($this, 'save_quick_settings'));
				add_action('transition_post_status', array($this, 'redirect_to_new_after_publish'), 10, 3);
				add_action('transition_post_status', array($this, 'redirect_to_new_after_post_pending_review'), 10, 3);
				add_filter('redirect_post_location', array($this, 'maybe_redirect_after_post_saved'), 999);
				add_action('admin_notices', array($this, 'remove_update_nag'), 1);

				// Add back button to internal pages. For example, user edit, taxonomy term edit
				add_action('admin_footer', array($this, 'render_back_buttons_for_internal_pages'));
				// Add more classes, so we can select more accurately for the "hide elements" tool
				add_filter('admin_body_class', array($this, 'add_admin_body_classes'));
			}
			add_action('wp', array($this, 'identify_source_id'));
			// The customizer page doesn't run the admin_head hook, so we use customize_controls_print_scripts
			// with priority 99 to make sure jquery already loaded
			add_action('customize_controls_print_scripts', array($this, 'cleanup_admin_page_for_frontend'), 99);
			add_action('wp_head', array($this, 'cleanup_admin_page_for_frontend'));

			add_filter('wp_die_handler', array($this, 'register_custom_die_handler'));
			add_action('after_setup_theme', array($this, 'late_init'));

			// Priority 99 to override the "edit" link from other frontend dashboards
			add_action('get_edit_post_link', array($this, 'add_post_edit_link'), 99);
			// Override the edit link from the "wp user frontend" plugin
			add_action('wpuf_edit_post_link', array($this, 'add_post_edit_link'), 99);

			$extra_files = array_merge($this->get_files_list(__DIR__ . '/inc/compatibility/'), $this->get_files_list(__DIR__ . '/inc/clean-admin-theme/'));

			if (dapof_fs()->can_use_premium_code__premium_only()) {
				$extra_files = array_merge($extra_files, $this->get_files_list(__DIR__ . '/inc/compatibility/advanced'));
			}
			foreach ($extra_files as $file) {
				require_once $file;
			}

			if (!empty($_POST) && !wp_doing_ajax()) {
				add_action('wp_authenticate', array($this, 'redirect_to_login_page_after_empty_credentials'), 10, 2);
				add_action('wp_login_failed', array($this, 'redirect_to_login_page_after_wrong_credentials'));
			}
			add_action('wp_logout', array($this, 'redirect_after_log_out'), 999);

			load_plugin_textdomain(VG_Admin_To_Frontend::$textname, false, basename(dirname(__FILE__)) . '/lang/');
		}

		function add_admin_body_classes($classes) {
			if (!empty($_GET['page'])) {
				$classes .= ' vgfa-page-' . sanitize_html_class($_GET['page']);
			}
			if (!empty($_GET['tab'])) {
				$classes .= ' vgfa-tab-' . sanitize_html_class($_GET['tab']);
			}
			if (is_user_logged_in()) {
				$user = wp_get_current_user();
				foreach ($user->roles as $role_key) {
					$classes .= ' vgfa-role-' . $role_key;
				}
			}
			return $classes;
		}

		function render_back_buttons_for_internal_pages() {
			$back_button_container = apply_filters('wp_frontend_admin/back_button_container_selector', '#wpbody-content > .wrap');
			if (!$back_button_container) {
				return;
			}
			$go_to = 'window.history.back(2);';
			if (strpos($_SERVER['REQUEST_URI'], '/post.php?post=') !== false) {
				$go_to = 'window.location.href = ' . json_encode(esc_url(add_query_arg('post_type', get_post_type((int) $_GET['post']), admin_url('edit.php')))) . ';';
			}
			?>
			<script>
				jQuery(window).on('load', function () {
					if (jQuery('html').hasClass('vgca-only-admin-content') && window.location.href.indexOf('vgfa_internal=1') > -1) {
						jQuery(<?php echo json_encode(sanitize_text_field($back_button_container)); ?>).prepend('<button type="button" onclick="<?php echo esc_attr($go_to); ?> event.preventDefault; return false;" class="page-title-action wpfa-back-button"><span class="dashicons dashicons-arrow-left-alt2" style="height: auto"></span> <?php echo sanitize_text_field(__('Go back', VG_Admin_To_Frontend::$textname)); ?></button><br>');
					}
				});
			</script>
			<?php
		}

		function remove_update_nag() {
			if (!empty($_GET['vgfa_source'])) {
				remove_action('admin_notices', 'update_nag', 3);
			}
		}

		function redirect_after_log_out() {
			if ($this->get_settings('disable_logout_redirection') || !empty($_REQUEST['redirect_to'])) {
				return;
			}
			$login_page_url = $this->get_login_url(home_url('/'));
			if (empty($login_page_url)) {
				return;
			}
			wp_redirect(esc_url(add_query_arg('wpfa_redirect_after_logout', 1, $login_page_url)));
			exit();
		}

		function redirect_to_login_page_after_empty_credentials($user, $password) {
			if (empty($_GET['loggedout']) && (empty($user) || empty($password))) {
				$this->redirect_to_login_page_after_wrong_credentials();
			}
		}

		function redirect_to_login_page_after_wrong_credentials($username = null) {
			if (defined('THEME_MY_LOGIN_VERSION')) {
				return;
			}
			$referrer = wp_get_referer();  // where did the post submission come from?
			// if there's a valid referrer, and it's not the default log-in screen

			if (!empty($referrer) && !empty($_POST['wpfa_login_form'])) {
				wp_redirect(esc_url(add_query_arg('vgfa_login_failed', '1', $referrer)));
				exit;
			}
		}

		function redirect_to_new_after_publish($new_status, $old_status, $post) {
			if (strpos($_SERVER['REQUEST_URI'], 'wp-admin/post') === false || $new_status !== 'publish' || $new_status == $old_status || !$this->get_settings('redirect_after_publish_post') || wp_doing_ajax()) {
				return;
			}
			$redirect_to = $this->get_settings('redirect_after_publish_post');
			if ($redirect_to === 'create_new') {
				$url = admin_url('post-new.php?post_type=' . $post->post_type);
			} elseif ($redirect_to === 'posts_list') {
				$url = admin_url('edit.php?post_type=' . $post->post_type);
			}
			if (empty($url)) {
				return;
			}
			$this->redirect_after_save_post = $url;
		}

		function redirect_to_new_after_post_pending_review($new_status, $old_status, $post) {
			if (strpos($_SERVER['REQUEST_URI'], 'wp-admin/post') === false || $new_status !== 'pending' || $new_status == $old_status || !$this->get_settings('redirect_after_pending_review_post') || wp_doing_ajax()) {
				return;
			}
			$redirect_to = $this->get_settings('redirect_after_pending_review_post');
			if ($redirect_to === 'create_new') {
				$url = admin_url('post-new.php?post_type=' . $post->post_type);
			} elseif ($redirect_to === 'posts_list') {
				$url = admin_url('edit.php?post_type=' . $post->post_type);
			}
			if (empty($url)) {
				return;
			}
			$this->redirect_after_save_post = $url;
		}

		function maybe_redirect_after_post_saved($url) {
			// If we saved a page that was internal, add the internal parameter to the redirect so WPFA shows the "go back" button upon refresh.
			if (!wp_doing_ajax() && strpos($url, 'vgfa_internal') === false && wp_get_referer() && strpos(wp_get_referer(), 'vgfa_internal') !== false) {
				$url = add_query_arg('vgfa_internal', 1, $url);
			}


			if ($this->redirect_after_save_post && !wp_doing_ajax()) {
				$url = esc_url($this->redirect_after_save_post);
			}
			return $url;
		}

		function user_has_any_role($roles, $user_id = null) {
			global $wpdb;
			if (is_object($user_id) && !is_wp_error($user_id)) {
				$user_id = $user_id->ID;
			}
			if (!$user_id) {
				$user_id = get_current_user_id();
			}
			if (!$user_id) {
				return false;
			}

			$blog_id = null;
			if (is_multisite()) {
				$user_belongs_to_blogs = get_blogs_of_user($user_id);
				if ($user_belongs_to_blogs) {
					$first_blog = end($user_belongs_to_blogs);
					$blog_id = $first_blog->userblog_id;
				}
			}

			$meta_key = $wpdb->get_blog_prefix($blog_id) . 'capabilities';
			$user_roles = get_user_meta($user_id, $meta_key, true);

			$matching_roles = array_intersect_key($user_roles, array_flip($roles));
			return !empty($matching_roles);
		}

		function master_capability() {
			return is_multisite() ? 'manage_network' : 'manage_options';
		}

		function is_master_user($user_id = null) {
			if (!$user_id) {
				$user_id = get_current_user_id();
			}
			if (isset($this->master_users['user' . $user_id])) {
				return $this->master_users['user' . $user_id];
			}
			$this->master_users['user' . $user_id] = is_multisite() ? user_can($user_id, $this->master_capability()) : $user_id === $this->get_main_admin_id();
			return $this->master_users['user' . $user_id];
		}

		function update_option($key, $value) {
			$use_main_site = false;
			if (is_multisite()) {
				$main_options = get_blog_option(1, VG_Admin_To_Frontend::$textname, array());
				if (!empty($main_options['enable_wpmu_mode'])) {
					$use_main_site = true;
					$options = $main_options;
				}
			}
			if (!$use_main_site) {
				$options = get_option(VG_Admin_To_Frontend::$textname, array());
			}

			$options[$key] = $value;

			if ($use_main_site) {
				update_blog_option(1, VG_Admin_To_Frontend::$textname, $options);
			} else {
				update_option(VG_Admin_To_Frontend::$textname, $options);
			}
		}

		function get_current_url($with_port = true) {
			// If they are using local by flywheel, the public URL does not use port
			if (!empty($_SERVER['LOCALAPPDATA'])) {
				$with_port = false;
			}
			$pageURL = 'http';
			if (isset($_SERVER["HTTPS"])) {
				if ($_SERVER["HTTPS"] == "on") {
					$pageURL .= "s";
				}
			}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443" && $with_port) {
				$pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}

		function get_admin_url_without_base($page_url) {
			if (is_user_logged_in()) {
				$user = get_userdata(get_current_user_id());
				$page_path_only = str_ireplace(array('{{user_id}}', '{{user_email}}'), array($user->ID, $user->user_email), $page_url);
			} else {
				$page_path_only = $page_url;
			}

			$page_path_only = preg_replace('#^https?://#', '', $page_path_only);
			$admin_directory_path = '/' . basename(admin_url()) . '/';
			if (is_multisite()) {
				// If this is a multisite network, we'll remove the url base using a less accurate method
				// because we can't make the replacement that we do for standalone wp sites, which is 
				// more accurate but it performs badly on large multisite networks because it would require
				// iterating on each subsite in the network
				$path_parts = explode($admin_directory_path, $page_url);
				if (count($path_parts) > 1) {
					unset($path_parts[0]);
				}
				$page_path_only = implode($admin_directory_path, $path_parts);
			} else {
				$admin_url_without_protocol = preg_replace('#^https?://#', '', admin_url());
				if (stripos($page_path_only, $admin_url_without_protocol) === 0) {
					$page_path_only = str_ireplace($admin_url_without_protocol, '', $page_path_only);
				}
			}

			// This is required for the quick-settings > remove elements tool,
			// Note. The upload.php breaks when the url has any query string
			if (strpos($page_path_only, 'upload.php') === false) {
				$page_path_only = add_query_arg('vgfa_source', get_the_ID(), $page_path_only);
			}
			if (empty($page_path_only)) {
				$page_path_only = 'index.php';
			}
			return $page_path_only;
		}

		function get_login_url($default = null) {
			$login_url = $this->get_settings('login_page_url');
			if (empty($login_url)) {
				return $default;
			}

			if (is_multisite()) {
				$sites = array_reverse(get_sites(array('order' => 'ASC')));
				foreach ($sites as $site) {
					$login_url = str_ireplace(get_site_url($site->blog_id), '', $login_url);
				}
				$login_url = home_url($login_url);
			}

			return apply_filters('wp_frontend_admin/login_url', $login_url);
		}

		function prepare_loose_url($url) {
			$url = str_replace('post_status=all', '', $url);
			// page_id is used by WP when we are previewing a draft page, but if we send page_id to the 
			// wp-admin page it redirects to comments.php by mistake. It's a wp bug
			$url = remove_query_arg(array('post', 'token', '_wpnonce', 'user_id', 'wp_http_referer', 's', 'page_id', 'revision', 'tag_ID', 'wpfa_id', 'trid', 'vgfa_blacklisted_url'), $url);
			$url_path = $this->get_admin_url_without_base($url);
			// We only use the first query string that indicates the page or 
			// post type and remove all the other query strings
			$url_path = current(explode('&', $url_path));
			return $url_path;
		}

		function register_custom_die_handler() {
			return array($this, 'show_message_if_page_not_allowed');
		}

		/**
		 * Copied from the wp core function: _default_wp_die_handler
		 * We need to register our own die handler to inject the WPFA JS into the wp_die page, because WP's function doesn't have hooks
		 * Kills WordPress execution and displays HTML page with an error message.
		 *
		 * This is the default handler for wp_die(). If you want a custom one,
		 * you can override this using the {@see 'wp_die_handler'} filter in wp_die().
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @param string|WP_Error $message Error message or WP_Error object.
		 * @param string          $title   Optional. Error title. Default empty.
		 * @param string|array    $args    Optional. Arguments to control behavior. Default empty array.
		 */
		function _default_wp_die_handler($message, $title = '', $args = array()) {

			if (!WPFA_Advanced_Obj()->is_frontend_dashboard_user(get_current_user_id())) {
				return _default_wp_die_handler($message, $title, $args);
			}
			list( $message, $title, $parsed_args ) = _wp_die_process_input($message, $title, $args);

			if (is_string($message)) {
				if (!empty($parsed_args['additional_errors'])) {
					$message = array_merge(
							array($message),
							wp_list_pluck($parsed_args['additional_errors'], 'message')
					);
					$message = "<ul>\n\t\t<li>" . join("</li>\n\t\t<li>", $message) . "</li>\n\t</ul>";
				}

				$message = sprintf(
						'<div class="wp-die-message">%s</div>',
						$message
				);
			}

			$have_gettext = function_exists('__');

			if (!empty($parsed_args['link_url']) && !empty($parsed_args['link_text'])) {
				$link_url = $parsed_args['link_url'];
				if (function_exists('esc_url')) {
					$link_url = esc_url($link_url);
				}
				$link_text = $parsed_args['link_text'];
				$message .= "\n<p><a href='{$link_url}'>{$link_text}</a></p>";
			}

			if (isset($parsed_args['back_link']) && $parsed_args['back_link']) {
				$back_text = $have_gettext ? __('&laquo; Back') : '&laquo; Back';
				$message .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
			}

			if (!did_action('admin_head')) :
				if (!headers_sent()) {
					header("Content-Type: text/html; charset={$parsed_args['charset']}");
					status_header($parsed_args['response']);
					nocache_headers();
				}

				$text_direction = $parsed_args['text_direction'];
				$dir_attr = "dir='$text_direction'";

				// If `text_direction` was not explicitly passed,
				// use get_language_attributes() if available.
				if (empty($args['text_direction']) && function_exists('language_attributes') && function_exists('is_rtl')
				) {
					$dir_attr = get_language_attributes();
				}
				?>
				<!DOCTYPE html>
				<html <?php echo $dir_attr; ?>>
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $parsed_args['charset']; ?>" />
						<meta name="viewport" content="width=device-width">
						<?php
						if (function_exists('wp_no_robots')) {
							wp_no_robots();
						}
						?>
						<title><?php echo $title; ?></title>
						<style type="text/css">
							html {
								background: #f1f1f1;
							}
							body {
								background: #fff;
								color: #444;
								font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
								margin: 0;
								padding: 20px 0;
								/*max-width: 700px;*/
							}
							h1 {
								border-bottom: 1px solid #dadada;
								clear: both;
								color: #666;
								font-size: 24px;
								margin: 30px 0 0 0;
								padding: 0;
								padding-bottom: 7px;
							}
							#error-page p,
							#error-page .wp-die-message {
								font-size: 14px;
								line-height: 1.5;
								margin: 25px 0 20px;
							}
							#error-page code {
								font-family: Consolas, Monaco, monospace;
							}
							ul li {
								margin-bottom: 10px;
								font-size: 14px ;
							}
							a {
								color: #0073aa;
							}
							a:hover,
							a:active {
								color: #006799;
							}
							a:focus {
								color: #124964;
								-webkit-box-shadow:
									0 0 0 1px #5b9dd9,
									0 0 2px 1px rgba(30, 140, 190, 0.8);
								box-shadow:
									0 0 0 1px #5b9dd9,
									0 0 2px 1px rgba(30, 140, 190, 0.8);
								outline: none;
							}
							.button {
								background: #f7f7f7;
								border: 1px solid #ccc;
								color: #555;
								display: inline-block;
								text-decoration: none;
								font-size: 13px;
								line-height: 2;
								height: 28px;
								margin: 0;
								padding: 0 10px 1px;
								cursor: pointer;
								-webkit-border-radius: 3px;
								-webkit-appearance: none;
								border-radius: 3px;
								white-space: nowrap;
								-webkit-box-sizing: border-box;
								-moz-box-sizing:    border-box;
								box-sizing:         border-box;

								-webkit-box-shadow: 0 1px 0 #ccc;
								box-shadow: 0 1px 0 #ccc;
								vertical-align: top;
							}

							.button.button-large {
								height: 30px;
								line-height: 2.15384615;
								padding: 0 12px 2px;
							}

							.button:hover,
							.button:focus {
								background: #fafafa;
								border-color: #999;
								color: #23282d;
							}

							.button:focus {
								border-color: #5b9dd9;
								-webkit-box-shadow: 0 0 3px rgba(0, 115, 170, 0.8);
								box-shadow: 0 0 3px rgba(0, 115, 170, 0.8);
								outline: none;
							}

							.button:active {
								background: #eee;
								border-color: #999;
								-webkit-box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, 0.5);
								box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, 0.5);
							}

							<?php
							if ('rtl' === $text_direction) {
								echo 'body { font-family: Tahoma, Arial; }';
							}
							?>
						</style>
					</head>
					<body id="error-page">
					<?php endif; // ! did_action( 'admin_head' )  ?>
					<?php echo $message; ?>

					<script src="<?php echo includes_url('/js/jquery/jquery.min.js'); ?>" id='wpfa-jquery'></script>
					<?php
					// Load the WPFA backend JS to be able to display the error messages in the front end correctly
					$this->cleanup_admin_page_for_frontend();
					?>
				</body>
			</html>
			<?php
			if ($parsed_args['exit']) {
				die();
			}
		}

		function show_message_if_page_not_allowed($message, $title, $args) {

			if (!is_string($message)) {
				return _default_wp_die_handler($message, $title, $args);
			}
			$text_not_found = strpos($message, __('Sorry, you are not allowed to access this page.')) === false && strpos($message, __('You need a higher level of permission.')) === false;
			if ($text_not_found) {
				return $this->_default_wp_die_handler($message, $title, $args);
			}


			$url = $this->get_settings('wrong_permissions_page_url', false);
			if (filter_var($url, FILTER_VALIDATE_URL)) {
				wp_safe_redirect(esc_url($url));
				exit();
			}

			if (empty($_GET['vgfa_source']) || $this->get_settings('disable_permissions_help_message', false)) {
				return $this->_default_wp_die_handler($message, $title, $args);
			}
			ob_start();
			include __DIR__ . '/views/frontend/wrong-permissions.php';
			$custom_message = ob_get_clean();
			return $this->_default_wp_die_handler($custom_message, $title, $args);
		}

		function save_quick_settings() {
			global $wpdb;
			if (!$this->is_master_user() || empty($_REQUEST['ID']) || empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'vg_frontend_admin_save_quick_settings')) {
				wp_send_json_error(__('Settings couldn\'t be saved', VG_Admin_To_Frontend::$textname));
			}

			$post_id = (int) $_REQUEST['ID'];
			$update = array();

			update_post_meta($post_id, 'is_wpfa_page', 1);
			if (!empty($_REQUEST['post_title'])) {
				$update['post_title'] = sanitize_text_field($_REQUEST['post_title']);
			}
			if (!empty($_REQUEST['page_template'])) {
				update_post_meta($post_id, '_wp_page_template', sanitize_text_field($_REQUEST['page_template']));
			}
			if (isset($_REQUEST['vgfa_hidden_elements'])) {
				update_post_meta($post_id, 'vgfa_hidden_elements', implode(', ', array_filter(explode(',', sanitize_text_field($_REQUEST['vgfa_hidden_elements'])))));
			}
			if (!empty($_REQUEST['post_name'])) {
				$update['post_name'] = sanitize_title($_REQUEST['post_name']);
			}

			if (!empty($update)) {
				$wpdb->update($wpdb->posts, $update, array('ID' => $post_id), '%s', '%d');
			}

			if (!empty($_REQUEST['menu'])) {
				update_post_meta($post_id, 'vgfa_menu', sanitize_text_field($_REQUEST['menu']));
				$this->maybe_add_to_menu($post_id, sanitize_text_field($_REQUEST['menu']));
			}

			$this->update_option('disable_all_admin_notices', isset($_REQUEST['vgfa_hide_notices']) && $_REQUEST['vgfa_hide_notices'] === 'on');

			clean_post_cache($post_id);
			do_action('wp_frontend_admin/quick_settings/after_save', $post_id, get_post($post_id));

			wp_send_json_success(array(
				'message' => __('Settings saved successfully. We will reload the page to show the new changes', VG_Admin_To_Frontend::$textname),
				'new_url' => get_permalink($post_id)
			));
		}

		function maybe_add_to_menu($post_id, $menu) {

			$already_in_menu = new WP_Query(array(
				'post_type' => 'nav_menu_item',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => '_menu_item_object_id',
						'value' => $post_id
					)
				),
				'tax_query' => array(
					array(
						'taxonomy' => 'nav_menu',
						'field' => 'term_id',
						'terms' => (int) $menu,
					),
				),
			));

			if (!$already_in_menu->posts) {
				wp_update_nav_menu_item((int) $menu, 0, array(
					'menu-item-title' => get_the_title($post_id),
					'menu-item-object-id' => $post_id,
					'menu-item-object' => 'page',
					'menu-item-status' => 'publish',
					'menu-item-type' => 'post_type',
				));
			}
		}

		/**
		 * Get all files in the folder
		 * @return array
		 */
		function get_files_list($directory_path, $file_format = '.php') {
			$files = glob(trailingslashit($directory_path) . '*' . $file_format);
			return $files;
		}

		function add_post_edit_link($link) {
			if ($this->is_master_user() || is_admin()) {
				return $link;
			}
			$add_post_edit_link = $this->get_settings('add_post_edit_link');

			if ($add_post_edit_link) {
				$post_id = get_the_ID();

				$original_blog_id = WPFA_Global_Dashboard_Obj()->switch_to_dashboard_site();

				$page_id = $this->get_page_id(admin_url('post.php?action=edit'), __('Edit'), 'edit_posts');
				$url_parameters = array('post' => $post_id);
				$link = esc_url(add_query_arg($url_parameters, get_permalink($page_id)));

				WPFA_Global_Dashboard_Obj()->restore_site($original_blog_id);
			}
			return $link;
		}

		function get_shortcode_for_slug($slug, $only_beginning = false) {
			$out = '[vg_display_admin_page page_url="' . $slug . '"';
			if (!$only_beginning) {
				$out .= ']';
			}

			return $out;
		}

		function get_page_id($admin_url, $title, $required_capability = null) {
			global $wpdb;

			$original_blog_id = WPFA_Global_Dashboard_Obj()->switch_to_dashboard_site();

			$admin_url = str_replace('#038;', '&', $admin_url);
			$admin_url = esc_url_raw($admin_url);

			if (strpos($admin_url, '/edit.php') !== false && strpos($admin_url, 'post_type') === false) {
				$admin_url = add_query_arg('post_type', 'post', $admin_url);
			}

			$admin_url = remove_query_arg(array('vgfa_source', 'wpfa_id'), $admin_url);
			$url_path = remove_query_arg(array('vgfa_source', 'wpfa_id'), $this->get_admin_url_without_base($admin_url));
			$url_path = apply_filters('wp_frontend_admin/page_id_from_path/prepared_path', $url_path, $admin_url);

			if ($url_path === 'index.php') {
				$page_id = (int) $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND post_content LIKE '%[vg_display_admin_page%' AND post_content LIKE '%" . esc_url_raw($url_path) . "' ");
			} else {
				$page_id = (int) $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND post_content LIKE '%[vg_display_admin_page%' AND post_content LIKE '%" . esc_url_raw($url_path) . "%' ");
			}
			if (!$page_id && ($this->is_master_user() || ($required_capability && current_user_can($required_capability)))) {
				$full_shortcode = $this->get_shortcode_for_slug($admin_url);
				$page_id = wp_insert_post(array(
					'post_type' => 'page',
					'post_status' => 'publish',
					'post_title' => trim(preg_replace('/\d+/', '', $title)),
					'post_content' => $full_shortcode
				));

				do_action('wp_frontend_admin/frontend_page_created', $page_id, $admin_url, $full_shortcode);
			}
			WPFA_Global_Dashboard_Obj()->restore_site($original_blog_id);
			return apply_filters('wp_frontend_admin/page_id_from_path', $page_id, $url_path, $admin_url);
		}

		function is_wpfa_page($post = null) {
			if (!$post) {
				$post = get_queried_object();
			}

			$out = false;
			if (!$post || !$post instanceof WP_Post) {
				return $out;
			}

			if (strpos($post->post_content, '[vg_display_admin_page') !== false || get_post_meta($post->ID, 'is_wpfa_page', true)) {
				$out = true;
			}
			return $out;
		}

		/**
		 * Redirect from wp-admin url to the real frontend url.
		 * We could link to the frontend url directly on the toolbar, but we´re doing this
		 * redirect so we create the base page only when needed.
		 * 
		 * @return null
		 */
		function maybe_redirect_direct_link() {
			if (empty($_GET['vgca_direct']) || !$this->is_master_user()) {
				return;
			}
			if (empty($_GET['vgca_slug'])) {
				$_GET['vgca_slug'] = 'index.php';
			}
			$page_id = $this->get_page_id(esc_url(admin_url($_GET['vgca_slug'])), sanitize_text_field($_GET['title']));
			$page_url = get_permalink($page_id);
			wp_redirect($page_url);
			exit();
		}

		function add_direct_link_menu($wp_admin_bar) {
			if (!is_admin() || !$this->is_master_user()) {
				return;
			}
			$args = array(
				'id' => 'vgca-direct-frontend-link',
				'title' => __('View on the frontend', VG_Admin_To_Frontend::$textname),
				'href' => add_query_arg(array(
					'vgca_direct' => 1,
						), admin_url()),
				'meta' => array('class' => 'vgca-direct-frontend-link')
			);
			$wp_admin_bar->add_node($args);
		}

		function late_init() {
			$urls = array();
			if (function_exists('dapof_fs') && !dapof_fs()->can_use_premium_code__premium_only()) {
				$urls = array(
					admin_url('edit.php'),
					admin_url('post-new.php'),
					admin_url('post.php?action=edit'),
					admin_url('post.php?action=trash'),
					admin_url('edit-tags.php?taxonomy=post_tag'),
					admin_url('edit-tags.php?taxonomy=category'),
				);
			}
			$this->allowed_urls = apply_filters('vg_admin_to_frontend/allowed_urls', $urls);

			if ($this->get_settings('hide_admin_bar_frontend') && !is_admin() && !$this->is_master_user()) {
				add_filter('show_admin_bar', '__return_false');
			}
		}

		function register_menu_page() {
			if (!current_user_can(VG_Admin_To_Frontend_Obj()->master_capability())) {
				return;
			}
			add_menu_page(
					VG_Admin_To_Frontend::$name, VG_Admin_To_Frontend::$name, 'manage_options', 'wpatof_welcome_page', array($this->vg_plugin_sdk, 'render_welcome_page'), plugins_url('/assets/imgs/wp-admin-icon.png', __FILE__)
			);
		}

		function required_capability_by_current_page() {

			global $pagenow, $menu, $submenu;

			if (!is_admin() || !current_user_can('manage_options')) {
				return;
			}

			if (!empty($_GET['page'])) {
				$page_slug = sanitize_text_field($_GET['page']);
			} elseif (!empty($_GET['post_type'])) {
				$page_slug = $pagenow . '?post_type=' . sanitize_text_field($_GET['post_type']);
			} elseif (!empty($_GET['taxonomy'])) {
				$page_slug = $pagenow . '?taxonomy=' . sanitize_text_field($_GET['taxonomy']);
			} else {
				$page_slug = $pagenow;
			}

			$capability = null;
			foreach ($menu as $menu_page) {
				if (!empty($menu_page[2]) && $menu_page[2] === $page_slug) {
					$capability = $menu_page[1];
					break;
				}
			}
			if (!$capability) {
				foreach ($submenu as $submenu_items) {
					foreach ($submenu_items as $menu_page) {

						if (!empty($menu_page[2]) && $menu_page[2] === $page_slug) {
							$capability = $menu_page[1];
							break;
						}
					}
				}
			}
			return $capability;
		}

		function identify_source_id() {

			// If it's a frontend page, allow it only for ?elementor-preview pages
			if (wp_doing_ajax() || (!is_admin() && !preg_match('/(elementor-preview|brizy-edit-iframe|pagebuilder-edit-iframe)/', $_SERVER['REQUEST_URI']))) {
				return;
			}

			$referer = wp_get_referer();
			if (strpos($referer, 'vgfa_source=') !== false && empty($_GET['vgfa_source'])) {
				$_GET['vgfa_source'] = (int) preg_replace('/.+vgfa_source=(\d+).*/', '$1', $referer);
				setcookie('vgfa_source', $_GET['vgfa_source'], null, '/');
			} elseif (empty($_GET['vgfa_source']) && !empty($_COOKIE['vgfa_source'])) {
				$_GET['vgfa_source'] = (int) $_COOKIE['vgfa_source'];
			}
		}

		function get_current_page_settings($page_id, $key, $default = '') {
			global $wpdb;
			if (empty($page_id)) {
				return $default;
			}
			$original_blog_id = WPFA_Global_Dashboard_Obj()->switch_to_dashboard_site();

			if ($key === 'vgfa_text_changes') {
				$value = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE p.post_status = 'publish' AND pm.meta_key = 'vgfa_text_changes' AND pm.meta_value LIKE '%" . esc_sql($page_id) . "%' ");
			} elseif ($key === 'vgfa_show_own_posts') {
				$value = (bool) $wpdb->get_var("SELECT COUNT(meta_value) FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE p.post_status = 'publish' AND meta_key = 'vgfa_show_own_posts' AND meta_value LIKE '%\"" . esc_sql($page_id) . "\"%' LIMIT 1");
			} elseif ($key === 'vgfa_disabled_columns') {
				$value = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE p.post_status = 'publish' AND  meta_key = 'vgfa_disabled_columns' AND meta_value LIKE '%\"" . esc_sql($page_id) . "\"%' ");
			} else {
				$value = (!empty($page_id) ) ? get_post_meta($page_id, $key, true) : $default;
			}

			WPFA_Global_Dashboard_Obj()->restore_site($original_blog_id);
			return $value;
		}

		function render_admin_css($source_id = null) {
			if (!$source_id && !empty($_GET['vgfa_source'])) {
				$source_id = (int) $_GET['vgfa_source'];
			}

			$admin_css = wp_unslash(preg_replace('/(\r|\n)/', '', htmlspecialchars_decode($this->get_settings('admin_view_css', ''))));
			$custom_css = apply_filters('wp_frontend_admin/admin_css', $admin_css);
			$custom_css .= file_get_contents(__DIR__ . '/assets/css/admin.css');
			$hidden_elements = $this->get_current_page_settings($source_id, 'vgfa_hidden_elements');
			$minimum_height_px = (int) $this->get_settings('minimum_content_height', 700);
			?>
			<style class="vgfa-admin-css">
			<?php if (!empty($this->get_settings('disable_all_admin_notices'))) { ?>
					.vgca-only-admin-content body.wp-admin .update-nag, 
					.vgca-only-admin-content body.wp-admin .updated, 
					.vgca-only-admin-content body.wp-admin .notice.error, 
					.vgca-only-admin-content body.wp-admin .is-dismissible, 
					.vgca-only-admin-content body.wp-admin .notice{
						display: none !important;
					}

			<?php } ?>	

				.vgca-only-admin-content div#wpwrap {
					min-height: initial !important;
				}
				.vgca-only-admin-content .woocommerce-page #wpcontent, 
				.vgca-only-admin-content .woocommerce-page.woocommerce_page_wc-admin #wpbody-content {
					min-height: auto;
				} 

				.vgca-only-admin-content #wpbody-content {
					padding-bottom: 0;
					padding-right: 0;
				}
				.vgca-only-admin-content body[class*="post-type-"]:not(.post-php) #wpbody-content {
					padding-bottom: 100px;
				}
				.vgca-only-admin-content .wpfa-force-hide {
					display: none !important;
				}

				.vgca-only-admin-content body, 
				html.vgca-only-admin-content {
					height: auto;
					overflow: auto;
					min-height: <?php echo (int) $minimum_height_px; ?>px;
					-webkit-overflow-scrolling: touch !important;
					background: transparent !important;
				}
				.vgca-only-admin-content body#error-page {
					box-shadow: none;
				}

				.vgca-only-admin-content body {
					min-width: 100%;
					box-sizing: border-box;
				}
				.vgca-only-admin-content .postbox {
					min-width: 100%;
				}

			<?php
// Hide elements selected on quick-settings
			if (!empty($hidden_elements)) {
				$cleaned_selectors = '.vgca-only-admin-content ' . implode(', .vgca-only-admin-content ', array_filter(explode(',', $hidden_elements)));
				echo $cleaned_selectors . ',';
			}
			?>				
				.vgca-only-admin-content #wpadminbar,
				.vgca-only-admin-content #adminmenumain,
				.vgca-only-admin-content #update-nag, 
				.vgca-only-admin-content body > record, 
				.vgca-only-admin-content .woocommerce-embed-page .woocommerce-layout__header,
				.vgca-only-admin-content .update-nag,
				.vgca-only-admin-content #screen-meta-links,
				.vgca-only-admin-content #wpfooter{
					display: none !important;
				}
				.vgca-only-admin-content .folded:not(.has-premio-box) #wpcontent, 
				.vgca-only-admin-content .folded:not(.has-premio-box) #wpfooter,
				.vgca-only-admin-content body:not(.has-premio-box) #wpcontent,
				.vgca-only-admin-content #wpfooter {
					margin-left: 0px !important;
					padding-left: 0px !important;
				}

				html.wp-toolbar.vgca-only-admin-content  {
					padding-top: 0px !important;
				}
				/*Limit media popups height*/
				.vgca-only-admin-content .thickbox-loading .media-modal.wp-core-ui,
				.vgca-only-admin-content .media-modal {
					max-height: 600px;
				}
				.vgca-only-admin-content #TB_ajaxContent,
				.vgca-only-admin-content #TB_window {
					max-height: 580px;
				}

				.vgca-only-admin-content .DomOutline_label {
					display: none;
				}
				.vgca-only-admin-content #wpcontent {
					height: auto;
				}
				/*In case they force gutenberg to not be full screen*/
				.vgca-only-admin-content .block-editor {
					min-height: 700px;
				}															
				@media (max-width: 782px) {
					.vgca-only-admin-content .woocommerce-page #wpbody-content,
					.vgca-only-admin-content .woocommerce-page #wpcontent {
						min-height: auto;
					}
				}
				/*Learndash*/
				.vgca-only-admin-content #sfwd-header {
					position: relative;
					top: 0;
				}
				/*Bookly calendar compatibility*/
				.vgca-only-admin-content #bookly-tbs .ec {
					height: 100% !important;
				}
				/*Support for admin 2020*/
				.vgca-only-admin-content .a2020_admin_theme div#a2020-front-wrap {
					display: none;
				}

				.vgca-only-admin-content .a2020_admin_theme #a2020-admin-bar-app,
				.vgca-only-admin-content .a2020_admin_theme .uk-sticky-placeholder {
					display: none;
				}
				/*Gutenberg - back button*/
				.vgca-only-admin-content .edit-post-fullscreen-mode-close {
					font-size: 25px !important;
				}
				.vgca-only-admin-content .edit-post-fullscreen-mode-close svg {
					display: none; 
				}
				.vgca-only-admin-content .edit-post-fullscreen-mode-close.has-icon::after {
					content: "";
					font-family: dashicons;
					font-size: 22px;
				}
				.vgca-only-admin-content #wp-ultimo-account .wu_status_list li > a::before, 
				.vgca-only-admin-content #wp-ultimo-account .wu_status_list li > div::before, 
				.vgca-only-admin-content #wp-ultimo-status .wu_status_list li > a::before, 
				.vgca-only-admin-content #wp-ultimo-status .wu_status_list li > div::before, 
				.vgca-only-admin-content #wp-ultimo-custom-domain .wu_status_list li > a::before, 
				.vgca-only-admin-content #wp-ultimo-custom-domain .wu_status_list li > div::before, 
				.vgca-only-admin-content #wp-ultimo-quotas .wu_status_list li > a::before, 
				.vgca-only-admin-content #wp-ultimo-quotas .wu_status_list li > div::before, 
				.vgca-only-admin-content #wp-ultimo-mrr .wu_status_list li > a::before, 
				.vgca-only-admin-content #wp-ultimo-mrr .wu_status_list li > div::before, 
				.vgca-only-admin-content #wp-ultimo-users .wu_status_list li > a::before, 
				.vgca-only-admin-content #wp-ultimo-users .wu_status_list li > div::before, 
				.vgca-only-admin-content #wp-ultimo-general .wu_status_list li > a::before, 
				.vgca-only-admin-content #wp-ultimo-general .wu_status_list li > div::before {
					height: 40px;
				}
				/*FluentForms editor*/										
				.vgca-only-admin-content 	#ff_form_editor_app .form-editor--sidebar-content {
					min-height: 600px;
				}
				.vgca-only-admin-content #ff_form_editor_app .form-editor--body {
					height: 95%;
				}
				.vgca-only-admin-content .form_internal_menu {
					left: 0;
				}
				/* Hide logo and ads added by wp esignature by approve.me */
				.vgca-only-admin-content #esig-settings-container div#postbox-container-1 {
					display: none;
				}
				.vgca-only-admin-content #esig-headlink-col1,
				.vgca-only-admin-content #esig-headlink-col2,
				.vgca-only-admin-content .admin_page_esign-edit-document .esig-sidebar-ad, 
				.vgca-only-admin-content .admin_page_esign-edit-document .postbox.premium-modules,
				.vgca-only-admin-content div#esig-logo-box {
					display: none;
				}		
				.vgca-only-admin-content .wu-setup-logo {
					display: none;
				}
				/*Remove WC mobile app banner*/
				.vgca-only-admin-content .woocommerce-mobile-app-banner {
					display: none !important;
				}
				.vgca-only-admin-content button.wpfa-back-button {
					margin-top: 10px;
				}
				/*wpcloud deploy*/
				.vgca-only-admin-content body.wp-admin.post-type-wpcd_app_server #postbox-container-1 {
					display: none;
				}

				.vgca-only-admin-content body.wp-admin.post-type-wpcd_app_server #post-body {
					width: 100%;
				}

				.vgca-only-admin-content #wpcd_server_wordpress-app_tab_top_of_server_details {
					margin: 0;
					box-sizing: border-box;
				}

				.vgca-only-admin-content .mfp-wrap {
					height: 100vh;
				}

				.vgca-only-admin-content .mfp-content {
					vertical-align: top;
					padding-top: 40px;
				}

				.vgca-only-admin-content div#wpcd_server_wordpress-app_tab3 {
					padding: 0 !important;
				}
				.vgca-only-admin-content #wpcd_wordpress-app_tab_top_of_site_details,
				.vgca-only-admin-content #wpcd_wordpress-app_tab2 {
					margin: 0;
					box-sizing: border-box;
					padding: 0;
				}
				/*Compatibility with latepoint by latepoint.com */
				.vgca-only-admin-content.wp-toolbar body.latepoint-admin {
					margin-top: 0;
				}
				/*Compatibility with admin columns pro*/
				.vgca-only-admin-content .ac-tooltip {
					display: none;
				}
				.vgca-only-admin-content .ac-tooltip.hover {
					display: block;
				}
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																							
				/*Fix double scroll bar, some plugin is adding this blank div*/
				.vgca-only-admin-content .scrollbar-test {
					display: none !important;
				}
				/*Groundhogg*/
				.vgca-only-admin-content body[class*="groundhogg"] div#quick-search {    
					box-sizing: border-box;
				}
				.vgca-only-admin-content div#groundhogg-notices:empty {
					display: none;
				}
				.vgca-only-admin-content body[class*="groundhogg"] #postbox-container-1,
				.vgca-only-admin-content body[class*="groundhogg"] #postbox-container-2-inner,
				.vgca-only-admin-content body[class*="groundhogg"] #post-body {
					max-height: 1000px;
					height: 100vh;
					min-height: unset;
				}
				/*.vgca-only-admin-content .wp-heading-inline,*/ 
				/*				.vgca-only-admin-content .page-title-action.wpfa-back-button {
									display:none!important;
								}*/
																																																																																																																																																																																																																																																																																											
				/* Groundhogg */

				.vgca-only-admin-content #groundhogg-datepicker-wrap {
					margin-top: 6px!important;
				}

				.vgca-only-admin-content body[class*="groundhogg"] #postbox-container-1, 
				.vgca-only-admin-content body[class*="groundhogg"] #postbox-container-2-inner, 
				.vgca-only-admin-content body[class*="groundhogg"] #post-body {
					max-height: inherit!important;
					height: inherit!important;
				}

				/*WP Amelia*/
				@media screen and (min-width: 783px){
					.vgca-only-admin-content .am-side-dialog .el-dialog {
						max-height: 850px;
					}
				}
				@media screen and (max-width: 782px){
					.vgca-only-admin-content .am-side-dialog .el-dialog {
						max-height: 1100px;
					}
				}
				/* Folders Pro by Premio*/							
				.vgca-only-admin-content body.has-premio-box #wpcontent {
					margin-left: 0px !important;
				}
				.vgca-only-admin-content body.has-premio-box .hide-folders-area .wcp-content, 
				.vgca-only-admin-content body.has-premio-box .wcp-content {
					left: -20px !important;
				}
				/*Project huddle*/
				.vgca-only-admin-content .ph-mockup-admin__menu.el-menu--horizontal.el-menu {
					margin-left: 0;
					margin-right: 0;
				}

				.vgca-only-admin-content div#ph-project-gallery {
					padding-left: 0 !important;
					padding-right: 0 !important;
				}
				/*Show tinymce popups at the top*/
				.vgca-only-admin-content .mce-window.mce-in {
					top: 200px !important;
				}
				/*Hide the divi editor in the backend because divi has a fatal js error when it loads in an iframe,
				so people will need to use the front end divi editor instead*/
				.vgca-only-admin-content .post-php #post div#et_pb_layout {
					display: none;
				}
				/*Fluentcrm*/
				.fluentcrm_main_menu_items .fluentcrm_menu_logo_holder {
					display: none !important;
				}

			</style>
			<script>
				var vgfaCustomCss = <?php echo json_encode($custom_css); ?>;
				var vgfaWpAdminBase = <?php echo json_encode(admin_url()); ?>;
			</script>
			<?php
		}

		function cleanup_admin_page_for_frontend() {
			// If it's a frontend page, allow it only for ?elementor-preview pages
			if (wp_doing_ajax() || (!is_admin() && !preg_match('/(elementor-preview|brizy-edit-iframe|pagebuilder-edit-iframe)/', $_SERVER['REQUEST_URI']))) {
				return;
			}
			$this->render_admin_css((!empty($_GET['vgfa_source'])) ? (int) $_GET['vgfa_source'] : '');

			$required_capability_html = '';
			if (!empty($_GET['vgfa_source'])) {
				$required_capability = $this->required_capability_by_current_page();

				if ($required_capability) {
					if (!function_exists('get_editable_roles')) {
						require_once ABSPATH . 'wp-admin/includes/user.php';
					}

					$required_capability_html = '<ul>';
					foreach (get_editable_roles() as $role_name => $role_info) {
						if (!empty($role_info['capabilities']) && isset($role_info['capabilities'][$required_capability])) {
							$required_capability_html .= '<li>' . $role_name . '</li>';
						}
					}
					$required_capability_html .= '</ul>';
					$required_capability_html .= sprintf(__('<div><b>Give permission to other user roles</b>. You can use the <a href="%s" target="_blank">User Role Editor</a> plugin to assign the capability to the user role required by the page: "%s". Careful, assign advanced capabilities only if you trust the users.</div>', VG_Admin_To_Frontend::$textname), VG_Admin_To_Frontend_Obj()->get_plugin_install_url('user-role-editor'), esc_html($required_capability));
				}
			}
			?>
			<script>
				var vgfaRequiredRoles = <?php echo json_encode($required_capability_html); ?>;

				// We add the class here, inline, to hide the admin menu and bar quicker
				// if we load it on the backend.js file it appears for a second while the file is downloaded
				if (window.parent != window) {
					jQuery('html').addClass('vgca-only-admin-content');

			<?php
			if (dapof_fs()->can_use_premium_code__premium_only()) {
				$custom_js = $this->get_settings('admin_view_js', '');
				if (!empty($custom_js) && is_string($custom_js)) {
					echo wp_unslash($custom_js);
				}
			}
			?>
				}
			</script>
			<?php
			wp_enqueue_script('vg-frontend-admin-outline', plugins_url('/assets/vendor/jQuery.DomOutline.js', VG_Admin_To_Frontend::$file), array('jquery'));
			// We add the script without wp_enqueue_script because we want it to load in the header in this exact location
			$popup_selectors = '.thickbox-loading .media-modal.wp-core-ui,.media-modal, .showwuSweetAlert, .mfp-content, .mce-window.mce-in, #WUB_window';
			$popup_selectors .= (!empty($this->get_settings('extra_popup_selectors'))) ? ',' . $this->get_settings('extra_popup_selectors') : '';
			$data = apply_filters('vg_admin_to_frontend/backend/js_data', array(
				'extra_popup_selectors' => $popup_selectors,
				'links_open_inside_iframe' => array(admin_url(), '&pagebuilder-edit', '#ff_preview'),
				'minimum_content_height' => (int) $this->get_settings('minimum_content_height', 300),
				'disable_stateful_navigation' => (int) VG_Admin_To_Frontend_Obj()->get_settings('disable_stateful_navigation'),
				'stateful_urls_that_require_referer' => '',
				'disable_all_admin_notices' => !empty($this->get_settings('disable_all_admin_notices')),
				'enable_loose_css_selectors' => !empty($this->get_settings('enable_loose_css_selectors')),
				'domoutline_js_file_url' => plugins_url('/assets/vendor/jQuery.DomOutline.js', VG_Admin_To_Frontend::$file)
			));
			?>
			<script id='vg-frontend-admin-backend-init-js-extra'>
				var vgfa_backend_data = <?php echo wp_json_encode($data); ?>;
			</script>
			<script src='<?php echo esc_url(plugins_url('/assets/js/backend.js', VG_Admin_To_Frontend::$file)); ?>?ver=<?php echo filemtime(VG_Admin_To_Frontend::$dir . '/assets/js/backend.js'); ?>' id='vg-frontend-admin-backend-init-js'></script>

			<?php
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == VG_Admin_To_Frontend::$instance) {
				VG_Admin_To_Frontend::$instance = new VG_Admin_To_Frontend();
				VG_Admin_To_Frontend::$instance->init();
			}
			return VG_Admin_To_Frontend::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

		/**
		 * Default post information to use when populating the "Write Post" form.
		 *
		 * @since 2.0.0
		 *
		 * @param string $post_type    Optional. A post type string. Default 'post'.
		 * @param bool   $create_in_db Optional. Whether to insert the post into database. Default false.
		 * @return WP_Post Post object containing all the default post data as attributes
		 */
		function get_default_post_to_edit($post_type = 'post', $create_in_db = false) {
			$post_title = '';
			if (!empty($_REQUEST['post_title'])) {
				$post_title = esc_html(wp_unslash($_REQUEST['post_title']));
			}

			$post_content = '';
			if (!empty($_REQUEST['content'])) {
				$post_content = esc_html(wp_unslash($_REQUEST['content']));
			}

			$post_excerpt = '';
			if (!empty($_REQUEST['excerpt'])) {
				$post_excerpt = esc_html(wp_unslash($_REQUEST['excerpt']));
			}

			if ($create_in_db) {
				$post_id = wp_insert_post(
						array(
							'post_title' => __('Auto Draft'),
							'post_type' => $post_type,
							'post_status' => 'draft',
						),
						false,
						false
				);
				$post = get_post($post_id);
				if (current_theme_supports('post-formats') && post_type_supports($post->post_type, 'post-formats') && get_option('default_post_format')) {
					set_post_format($post, get_option('default_post_format'));
				}
				wp_after_insert_post($post, false, null);

				// Schedule auto-draft cleanup.
				if (!wp_next_scheduled('wp_scheduled_auto_draft_delete')) {
					wp_schedule_event(time(), 'daily', 'wp_scheduled_auto_draft_delete');
				}
			} else {
				$post = new stdClass;
				$post->ID = 0;
				$post->post_author = '';
				$post->post_date = '';
				$post->post_date_gmt = '';
				$post->post_password = '';
				$post->post_name = '';
				$post->post_type = $post_type;
				$post->post_status = 'draft';
				$post->to_ping = '';
				$post->pinged = '';
				$post->comment_status = get_default_comment_status($post_type);
				$post->ping_status = get_default_comment_status($post_type, 'pingback');
				$post->post_pingback = get_option('default_pingback_flag');
				$post->post_category = get_option('default_category');
				$post->page_template = 'default';
				$post->post_parent = 0;
				$post->menu_order = 0;
				$post = new WP_Post($post);
			}

			/**
			 * Filters the default post content initially used in the "Write Post" form.
			 *
			 * @since 1.5.0
			 *
			 * @param string  $post_content Default post content.
			 * @param WP_Post $post         Post object.
			 */
			$post->post_content = (string) apply_filters('default_content', $post_content, $post);

			/**
			 * Filters the default post title initially used in the "Write Post" form.
			 *
			 * @since 1.5.0
			 *
			 * @param string  $post_title Default post title.
			 * @param WP_Post $post       Post object.
			 */
			$post->post_title = (string) apply_filters('default_title', $post_title, $post);

			/**
			 * Filters the default post excerpt initially used in the "Write Post" form.
			 *
			 * @since 1.5.0
			 *
			 * @param string  $post_excerpt Default post excerpt.
			 * @param WP_Post $post         Post object.
			 */
			$post->post_excerpt = (string) apply_filters('default_excerpt', $post_excerpt, $post);

			return $post;
		}

	}

	if (!function_exists('VG_Admin_To_Frontend_Obj')) {

		function VG_Admin_To_Frontend_Obj() {
			return VG_Admin_To_Frontend::get_instance();
		}

	}

	VG_Admin_To_Frontend_Obj();
}