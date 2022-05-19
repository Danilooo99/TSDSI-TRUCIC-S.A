<?php

add_filter('aioseo_conflicting_shortcodes', function ($shortcodes) {
	$shortcodes['WP Frontend Admin'] = 'vg_display_admin_page';
	return $shortcodes;
});
