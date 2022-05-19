<?php
if (!class_exists('WPFA_Shortcodes')) {

	class WPFA_Shortcodes {

		static private $instance = false;
		var $quick_settings_rendered = false;

		private function __construct() {
			
		}

		function init() {

			add_shortcode('vg_display_admin_page', array($this, 'get_admin_page_for_frontend'));
			add_shortcode('vg_display_logout_link', array($this, 'get_logout_link'));
			add_shortcode('vg_display_logout_url', array($this, 'get_logout_url'));
			add_shortcode('vg_display_login_url', array($this, 'get_login_url'));
			add_shortcode('vg_display_edit_link', array($this, 'get_edit_link'));
			add_shortcode('vg_display_edit_url', array($this, 'get_edit_url'));
			add_shortcode('wp_frontend_admin_login_form', array($this, 'get_login_form'));
			if (dapof_fs()->can_use_premium_code__premium_only()) {
				add_shortcode('wp_frontend_admin_my_site_url', array($this, 'get_user_site_url__premium_only'));
				add_shortcode('wp_frontend_admin_current_site_info', array($this, 'get_current_site_info__premium_only'));
			}
		}

		function get_current_site_info__premium_only($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'field' => ''
							), $atts));

			$allowed_fields = apply_filters('wp_frontend_admin/current_site_info/allowed_fields', array('name', 'path'));
			if (!is_user_logged_in() || empty($field) || !in_array($field, $allowed_fields, true)) {
				return;
			}

			$out = '';
			$blog_id = WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content();
			if ($field === 'name') {
				$out = get_blog_option($blog_id, 'blogname');
			} elseif ($field === 'path') {
				$out = str_replace(array('https://', 'http://'), '', remove_query_arg('vgfacache', get_site_url($blog_id)));
			}
			return apply_filters('wp_frontend_admin/current_site_info/output', $out, $field, $blog_id);
		}

		function get_user_site_url__premium_only($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'path' => '/',
							), $atts));

			if (!is_user_logged_in()) {
				return;
			}
			$blog_id = WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content();
			$url = get_site_url($blog_id, $path);

			$url = str_replace('{homepage_id}', get_option('page_on_front'), $url);
			return $url;
		}

		function get_login_url($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'redirect_to' => $_SERVER['REQUEST_URI'],
							), $atts));
			$url = null;
			if (is_user_logged_in()) {
				return $url;
			}
			$url = esc_url(wp_login_url($redirect_to));
			return $url;
		}

		function get_logout_url($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'redirect_to' => VG_Admin_To_Frontend_Obj()->get_login_url($_SERVER['REQUEST_URI']),
							), $atts));
			$url = null;
			if (!is_user_logged_in()) {
				return $url;
			}
			$url = esc_url(wp_logout_url($redirect_to));
			return $url;
		}

		function get_logout_link($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'redirect_to' => VG_Admin_To_Frontend_Obj()->get_login_url($_SERVER['REQUEST_URI']),
							), $atts));

			if (!is_user_logged_in()) {
				return;
			}
			$logout_link = str_replace('<a ', '<a class="vg-logout-link" ', wp_loginout($redirect_to, false));
			$out = '<style>.vg-logout-link{padding:5px;background:#000;color:#fff;text-decoration:none}</style>' . $logout_link;
			return $out;
		}

		function get_admin_page_for_frontend($atts = array(), $content = '') {
			$shortcode_atts = shortcode_atts(array(
				'page_url' => '',
				'forward_parameters' => true,
				'allowed_roles' => null,
				'required_capabilities' => null,
				'allowed_user_ids' => null,
				'allow_single_post_edit' => null,
				'use_desktop_in_mobile' => false,
				'allow_any_url' => false,
				'lazy_load' => true,
				'wu_plans' => '',
				'minimum_height' => null,
				'class_name' => null
					), $atts);
			extract($shortcode_atts);
			$GLOBALS['wpfa_current_shortcode'] = $shortcode_atts;

			$current_uri = $_SERVER['REQUEST_URI'];
			if (is_admin() && !empty($_GET['action']) && $_GET['action'] === 'elementor' || preg_match('/(action=elementor-preview|action=ct_render_shortcode)/', $current_uri) || wp_doing_ajax()) {
				return wpautop(__('Note from WP Frontend Admin: The admin content will load in this place when you view the page outside the elementor editor.', VG_Admin_To_Frontend::$textname));
			}

			if (!is_user_logged_in()) {
				$login_page_url = VG_Admin_To_Frontend_Obj()->get_login_url();
				$login_message = wp_kses_post(wpautop(VG_Admin_To_Frontend_Obj()->get_settings('login_message')));
				$login_form = wp_login_form(array('echo' => false, 'redirect' => $_SERVER['REQUEST_URI']));

				ob_start();
				if (empty($login_page_url)) {
					include VG_Admin_To_Frontend::$dir . '/views/frontend/log-in-message.php';
				} else {
					// We use the JS redirect only when the shortcode is rendered programmatically
					// We also have the redirect in the wp hook.
					?>

					<script>
						window.location.href = <?php echo json_encode(esc_url(add_query_arg('vgfa_redirect_to_login', 1, $login_page_url))); ?>;
					</script>
					<?php
				}
				return ob_get_clean();
			}

			if (!$page_url) {
				return;
			}

			// Prevent errors. Sometimes users add forward/backward quotes to the shortcode
			$page_url = str_replace(array('‘', '’', '“', '”'), '', $page_url);
			$page_url = str_replace('.php?/page=', '.php?page=', $page_url);

			$allowed_to_view = true;
			if (!empty($allowed_roles)) {
				$allowed_roles = explode(',', $allowed_roles);
				$user_data = get_userdata(get_current_user_id());
				$allowed_to_view = false;

				foreach ($allowed_roles as $allowed_role) {
					if (in_array($allowed_role, $user_data->roles)) {
						$allowed_to_view = true;
						break;
					}
				}
			}
			if (!empty($required_capabilities)) {
				$required_capabilities = explode(',', $required_capabilities);
				$user_data = get_userdata(get_current_user_id());
				$allowed_to_view = false;

				foreach ($required_capabilities as $required_capability) {
					if (current_user_can($required_capability)) {
						$allowed_to_view = true;
						break;
					}
				}
			}

			if (!empty($allowed_user_ids)) {
				$allowed_user_ids = array_map('intval', explode(',', $allowed_user_ids));
				$allowed_to_view = in_array(get_current_user_id(), $allowed_user_ids);
			}
			$allowed_to_view = apply_filters('wp_frontend_admin/is_user_allowed_to_view_page', $allowed_to_view, $page_url, $shortcode_atts);
			if (is_wp_error($allowed_to_view) || !$allowed_to_view) {
				$wrong_permissions_message = VG_Admin_To_Frontend_Obj()->get_settings('wrong_permissions_message', __('You don\'t have permission to view this page. If this is a mistake please contact the site administrator.', VG_Admin_To_Frontend::$textname));
				if (is_wp_error($allowed_to_view)) {
					$wrong_permissions_message = $allowed_to_view->get_error_message();
				}
				ob_start();
				include VG_Admin_To_Frontend::$dir . '/views/frontend/logged-in-user-not-allowed.php';
				return ob_get_clean();
			}

			$full_url = ( strpos($page_url, '//') !== false ) ? $page_url : admin_url($page_url);
			if (empty($allow_single_post_edit)) {
				$full_url = remove_query_arg('post', $full_url);
				$full_url = remove_query_arg('classic-editor', $full_url);
			}
			if (!dapof_fs()->can_use_premium_code__premium_only()) {

				$allowed_urls = VG_Admin_To_Frontend_Obj()->get_admin_url_without_base(implode(',', VG_Admin_To_Frontend_Obj()->allowed_urls));
				$path_to_check = remove_query_arg(array('vgfa_source', 'wpfa_id', 'post'), html_entity_decode(VG_Admin_To_Frontend_Obj()->get_admin_url_without_base($full_url)));
				if (!empty($allowed_urls) && strpos($allowed_urls, $path_to_check) === false && strpos($full_url, 'post_type=post') === false) {
					if (is_super_admin()) {
						ob_start();
						include VG_Admin_To_Frontend::$dir . '/views/frontend/wrong-plan.php';
						return ob_get_clean();
					} else {
						return;
					}
				}
			}
			ob_start();

			// customize_theme is used by the customizer, we don't want to show the quick settings inside the customizer preview
			if (VG_Admin_To_Frontend_Obj()->is_master_user() && !VG_Admin_To_Frontend_Obj()->get_settings('disable_quick_settings') && empty($_GET['customize_theme']) && strpos($_SERVER['REQUEST_URI'], 'post.php') === false && !$this->quick_settings_rendered) {
				$this->quick_settings_rendered = true;
				$post = get_post(get_the_ID());
				$templates = wp_get_theme()->get_page_templates();
				$current_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
				$menus = get_terms('nav_menu', array('hide_empty' => false));
				$url_parts = explode('wp-admin/', $page_url);
				$help_url = 'https://wpfrontendadmin.com/contact/?utm_source=wp-admin&utm_term=' . esc_url(end($url_parts)) . '&utm_campaign=quick-settings-help&utm_medium=' . (empty(VG_Admin_To_Frontend_Obj()->allowed_urls) ? 'free' : 'pro') . '-plugin';
				wp_enqueue_style('vg-frontend-admin-quick-settings', plugins_url('/assets/css/quick-settings.css', VG_Admin_To_Frontend::$file));
				include VG_Admin_To_Frontend::$dir . '/views/frontend/quick-settings.php';
			}

			if ($allow_any_url) {
				$final_url = $page_url;
				$base_admin_url = admin_url();
			} else {
				$page_path_only = str_replace('#038;', '&', VG_Admin_To_Frontend_Obj()->get_admin_url_without_base(html_entity_decode($page_url)));

				// The close button in the customizer should send to the front end dashboard home instead of wp-admin
				if (strpos($page_path_only, 'customize.php') === 0) {
					$redirect_to = VG_Admin_To_Frontend_Obj()->get_settings('redirect_to_frontend', home_url('/'));
					$page_path_only = add_query_arg('return', $redirect_to, $page_path_only);
				}

				if ($forward_parameters) {
					$page_path_only = remove_query_arg(array('page_id', 'preview_id', 'preview', 'preview_nonce'), add_query_arg($_GET, $page_path_only));
				}
				// Sometimes tinymce urlencodes the url, so we use urldecode to prevent that

				$blog_id = WPFA_Global_Dashboard_Obj()->get_site_id_for_admin_content();
				$final_url = get_admin_url($blog_id, $page_path_only);
				$base_admin_url = get_admin_url($blog_id, '/');
			}

			$final_url = apply_filters('wp_frontend_admin/shortcode/admin_page_final_url', $final_url, $page_path_only, $blog_id, $forward_parameters);
			$loading_animation_style = VG_Admin_To_Frontend_Obj()->get_settings('loading_animation');
			if (dapof_fs()->can_use_premium_code__premium_only()) {
				if (!empty($loading_animation_style) && !in_array($loading_animation_style, array('none', 'custom_gif'), true)) {
					wp_enqueue_style('wp-frontend-admin-animations', plugins_url('/assets/vendor/spinkit.min.css', VG_Admin_To_Frontend::$file));
				}
				$loading_animation_color = VG_Admin_To_Frontend_Obj()->get_settings('loading_animation_color', '#000000');
				$custom_gif_animation = VG_Admin_To_Frontend_Obj()->get_settings('custom_gif_animation');
			}

			if (VG_Admin_To_Frontend_Obj()->is_master_user() && !VG_Admin_To_Frontend_Obj()->get_settings('disable_quick_settings') && !is_admin()) {
				if (!is_ssl() && strpos($final_url, 'https://') === 0) {
					include VG_Admin_To_Frontend::$dir . '/views/frontend/protocol-mismatch.php';
					return ob_get_clean();
				}
				if (!get_option('permalink_structure')) {
					include VG_Admin_To_Frontend::$dir . '/views/frontend/enable-permalinks.php';
					return ob_get_clean();
				}
				if (!VG_Admin_To_Frontend_Obj()->get_settings('disable_404_url_checks')) {
					$url_check_transient_name = 'wpfa_url' . md5($final_url);
					$url_check_response_code = get_transient($url_check_transient_name);

					if (!$url_check_response_code) {
						$url_check_response = wp_remote_head($final_url);
						$url_check_response_code = (int) wp_remote_retrieve_response_code($url_check_response);
					}
					// If the page is viewed by a super admin and it returns 404 or 403, it means the URL is invalid
					if (in_array($url_check_response_code, array(404, 403, 500), true)) {
						$final_url = remove_query_arg(array('vgfa_source', 'wpfa_id'), $final_url);
						include VG_Admin_To_Frontend::$dir . '/views/frontend/broken-url.php';
						return ob_get_clean();
					} else {
						set_transient($url_check_transient_name, $url_check_response_code, DAY_IN_SECONDS);
					}
				}
			}
			$fatal_errors = apply_filters('wp_frontend_admin/render_page_shortcode/fatal_errors', array(), $final_url, $page_url, $forward_parameters, $lazy_load);
			if ($fatal_errors) {
				foreach ($fatal_errors as $fatal_error) {
					echo $fatal_error;
				}
				return ob_get_clean();
			}

			$warnings = apply_filters('wp_frontend_admin/render_page_shortcode/warnings', array(), $final_url, $page_url, $forward_parameters, $lazy_load);
			if ($warnings) {
				foreach ($warnings as $warning) {
					echo wp_kses_post($warning);
				}
			}


			do_action('wp_frontend_admin/before_shortcode_html', $final_url, $page_url, $forward_parameters, $lazy_load);

			$iframe_id = uniqid();
			// Note. The upload.php breaks when the url has any query string
			if (strpos($page_path_only, 'upload.php') === false) {
				$final_url = add_query_arg('wpfa_id', $iframe_id, $final_url);
			}
			$minimum_height_px = (int) VG_Admin_To_Frontend_Obj()->get_settings('minimum_content_height', 700);
			$max_content_width = VG_Admin_To_Frontend_Obj()->get_settings('max_content_width', '1500px');
			include VG_Admin_To_Frontend::$dir . '/views/frontend/page.php';
			wp_enqueue_script('vg-frontend-admin-init', plugins_url('/assets/js/frontend.js', VG_Admin_To_Frontend::$file), array('jquery'), filemtime(VG_Admin_To_Frontend::$dir . '/assets/js/frontend.js'));

			ob_start();
			VG_Admin_To_Frontend_Obj()->render_admin_css(get_queried_object_id());
			$admin_css = ob_get_clean();

			$full_screen_keywords = array_map('trim', explode(',', VG_Admin_To_Frontend_Obj()->get_settings('fullscreen_pages_keywords', '')));
			$disable_fullscreen_pages_keywords = array_map('trim', explode(',', VG_Admin_To_Frontend_Obj()->get_settings('disable_fullscreen_pages_keywords', '')));

			wp_localize_script('vg-frontend-admin-init', 'vgfa_data', apply_filters('vg_admin_to_frontend/frontend/js_data', array(
				'wp_ajax_url' => admin_url('admin-ajax.php'),
				'backend_js_urls' => array(plugins_url('/assets/vendor/jQuery.DomOutline.js', VG_Admin_To_Frontend::$file), plugins_url('/assets/js/backend.js', VG_Admin_To_Frontend::$file)),
				'fullscreen_pages_keywords' => array_values(array_filter(array_merge($full_screen_keywords, array('brizy-edit-iframe', 'pagebuilder-edit-iframe', 'action=elementor', 'elementor-preview=', 'page=formidable-settings', 'page=formidable&fr', 'page=formidable-styles', 'page=wpforms-builder', 'customize.php')))),
				'disable_fullscreen_pages_keywords' => $disable_fullscreen_pages_keywords,
				'admin_css' => $admin_css,
				'wpadmin_base_url' => $base_admin_url,
				'disable_stateful_navigation' => (bool) VG_Admin_To_Frontend_Obj()->get_settings('disable_stateful_navigation'),
			)));
			return ob_get_clean();
		}

		function get_login_form($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'redirect_to' => isset($_REQUEST['redirect_to']) ? esc_url($_REQUEST['redirect_to']) : VG_Admin_To_Frontend_Obj()->get_settings('redirect_to_frontend', home_url())
							), $atts));

			if (is_user_logged_in()) {
				ob_start();
				if (VG_Admin_To_Frontend_Obj()->is_master_user()) {
					include VG_Admin_To_Frontend::$dir . '/views/frontend/already-logged-in-message.php';
				} elseif ($redirect_to) {
					?>
					<script>window.location.href = <?php echo json_encode(esc_url($redirect_to)); ?>;</script>
					<?php
				}
				return ob_get_clean();
			}

			$login_page_url = VG_Admin_To_Frontend_Obj()->get_login_url();
			$login_message = '';
			if (empty($redirect_to)) {
				$redirect_to = home_url();
			}

			// Needed by some recaptcha plugins
			add_filter('login_form_middle', array($this, 'run_login_form_middle_hooks'));
			$login_form = wp_login_form(array('echo' => false, 'redirect' => $redirect_to));
			remove_filter('login_form_middle', array($this, 'run_login_form_middle_hooks'));

			$disable_redirect_to_login_page = true;
			ob_start();
			include VG_Admin_To_Frontend::$dir . '/views/frontend/log-in-message.php';
			return ob_get_clean();
		}

		function run_login_form_middle_hooks($html) {
			ob_start();
			do_action('login_form');
			$html .= ob_get_clean();
			return $html;
		}

		function get_edit_link($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'post_id' => '',
							), $atts));

			if (!is_user_logged_in()) {
				return;
			}
			if (!$post_id) {
				$post_id = get_the_ID();
			}
			if ($post_id === 'homepage') {
				$post_id = (int) get_option('page_on_front');
			}
			ob_start();
			edit_post_link(__('Edit', VG_Admin_To_Frontend::$textname), '', '', $post_id);
			$out = ob_get_clean();
			return $out;
		}

		function get_edit_url($atts = array(), $content = '') {
			extract(shortcode_atts(array(
				'post_id' => '',
				'extra_params' => ''
							), $atts));

			if (!is_user_logged_in()) {
				return;
			}
			if (!$post_id) {
				$post_id = get_the_ID();
			}
			if ($post_id === 'homepage') {
				$post_id = (int) get_option('page_on_front');
			}
			$post = get_post($post_id);
			if (!$post) {
				return;
			}

			$url = get_edit_post_link($post->ID);
			if (!$url) {
				return;
			}

			parse_str($extra_params, $extra_params_parsed);
			$url = add_query_arg($extra_params_parsed, $url);
			return $url;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Shortcodes::$instance) {
				WPFA_Shortcodes::$instance = new WPFA_Shortcodes();
				WPFA_Shortcodes::$instance->init();
			}
			return WPFA_Shortcodes::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Shortcodes_Obj')) {

	function WPFA_Shortcodes_Obj() {
		return WPFA_Shortcodes::get_instance();
	}

}
WPFA_Shortcodes_Obj();
