<?php

if (!defined('ABSPATH')) {
	return;
}
// Only run for GET requests
if (!empty($_POST) || wp_doing_ajax() || wp_doing_cron()) {
	return;
}
// Don't run for multisite admin panel
if (is_multisite() && is_network_admin()) {
	return;
}
// Only run for admin pages
if (!is_admin()) {
	return;
}
// Only run if the page was embedded in a frontend dashboard
if (empty($_GET['vgfa_source'])) {
	return;
}
// Only run if the file with the list of disabled plugins exists
$wpfa_disabled_plugins_file_path = WP_CONTENT_DIR . '/mu-plugins/wpfa-disabled-plugins.php';
if (!file_exists($wpfa_disabled_plugins_file_path)) {
	return;
}
if (empty($GLOBALS["wpfa_disabled_plugins"])) {
	return;
}
$wpfa_disabled_plugins = $GLOBALS["wpfa_disabled_plugins"];
if (!function_exists('wpfa_get_settings')) {

	function wpfa_get_settings($key = null, $default = null) {
		if (is_multisite()) {
			$main_options = get_blog_option(1, 'vg_admin_to_frontend', array());
			if (!empty($main_options['enable_wpmu_mode'])) {
				$options = get_blog_option(1, 'vg_admin_to_frontend', array());
			}
		}
		if (empty($options)) {
			$options = get_option('vg_admin_to_frontend', array());
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

	function wpfa_get_current_url($with_port = true, $without_host = false) {
		// If they are using local by flywheel, the public URL does not use port
		if (!empty($_SERVER['LOCALAPPDATA'])) {
			$with_port = false;
		}
		if ($without_host) {
			$pageURL = '';
		} else {
			$pageURL = 'http';
			if (isset($_SERVER["HTTPS"])) {
				if ($_SERVER["HTTPS"] == "on") {
					$pageURL .= "s";
				}
			}
			$pageURL .= "://" . $_SERVER["HTTP_HOST"];
			if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443" && $with_port) {
				$pageURL .= ":" . $_SERVER["SERVER_PORT"];
			}
		}
		$request_uri = $_SERVER["REQUEST_URI"];
		if (is_multisite() && defined('SUBDOMAIN_INSTALL') && !SUBDOMAIN_INSTALL) {
			$uri_parts = array_filter(explode('/', $request_uri));
			array_shift($uri_parts);
			$request_uri = '/' . implode('/', $uri_parts);
		}

		$pageURL .= $request_uri;
		return $pageURL;
	}

	// Never run if zero plugins were restricted for the current page
	if (is_multisite()) {
		$wpfa_current_url = wpfa_get_current_url(true, true);
	} else {
		$wpfa_current_url = wpfa_get_current_url();
	}
	$wpfa_current_url = remove_query_arg(array('vgfa_source', 'wpfa_id'), $wpfa_current_url);
	if (empty($wpfa_disabled_plugins[$wpfa_current_url])) {
		return;
	}

	// Never run if the plugins manager feature was not enabled
	if (empty(wpfa_get_settings('enable_plugins_manager'))) {
		return;
	}
	$GLOBALS['wpfa_disabled_plugins_for_page'] = $wpfa_disabled_plugins[$wpfa_current_url];

	add_filter('option_active_plugins', function ($plugins) {
		$plugins = array_diff($plugins, $GLOBALS['wpfa_disabled_plugins_for_page']);
		return $plugins;
	});

	add_filter('site_option_active_sitewide_plugins', function ($option) {
		$option = array_diff_key($option, array_flip($GLOBALS['wpfa_disabled_plugins_for_page']));
		return $option;
	});
}