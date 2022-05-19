<?php

// Allow our JS to load in the mailpoet pages
add_filter('mailpoet_conflict_resolver_whitelist_script', function ($scripts) {
	$scripts[] = 'display-admin-page-on-frontend-premium';
	return $scripts;
});
