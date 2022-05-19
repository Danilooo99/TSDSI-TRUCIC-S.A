<style>
	body {
		min-height: 250px;
	}
</style>
<?php
printf(__('Tip from WP Frontend Admin:'
				. '<br>1. This page requires a different user role. Please view this page as administrator and follow the instructions in the "quick settings" section to fix this. <a href="%s" target="_blank">See an screenshot</a>.<br>2. You can get help in the <a href="%s" target="_blank">live chat on our website</a> or open a support ticket via email. We will help you with the setup for free.', VG_Admin_To_Frontend::$textname), esc_url(plugins_url('/assets/imgs/page-not-allowed-instructions.png', VG_Admin_To_Frontend::$file)), 'https://wpfrontendadmin.com/contact/?utm_source=wp-admin&utm_campaign=wrong-permissions-help&utm_medium=' . (empty(VG_Admin_To_Frontend_Obj()->allowed_urls) ? 'free' : 'pro') . '-plugin');
?>
<script>
	var body = document.body,
			html = document.documentElement;

	var height = Math.max(body.scrollHeight, body.offsetHeight,
			html.clientHeight, html.scrollHeight, html.offsetHeight);

	// Remove the loading indicator from the parent iframe
	var parent = (window.location.href.indexOf('&elementor-preview') > -1) ? window.parent.parent : window.parent;
	parent.document.querySelectorAll('.vgca-iframe-wrapper .vgca-loading-indicator').forEach(function (element) {
		element.style.display = 'none';
	});
	parent.document.querySelectorAll('.vgca-iframe-wrapper').forEach(function (element) {
		element.classList.remove("vgfa-is-loading");
		element.style.height = height + 'px';
	});
	parent.document.querySelectorAll('.vgca-iframe-wrapper iframe').forEach(function (element) {
		element.style.height = height + 'px';
	});
</script>
