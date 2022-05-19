<?php

if (!function_exists('wpfa_gravity_view_compat')) {

	add_action('init', 'wpfa_gravity_view_compat');

	function wpfa_gravity_view_compat() {
		if (!defined('GRAVITYVIEW_FILE')) {
			return;
		}

		// Disable noconflict mode because it prevents our JS from loading
		$settings = get_option('gravityformsaddon_gravityview_app_settings');
		if (empty($settings)) {
			$settings = array();
		}
		$settings['no-conflict-mode'] = 0;

		update_option('gravityformsaddon_gravityview_app_settings', $settings);
	}

}