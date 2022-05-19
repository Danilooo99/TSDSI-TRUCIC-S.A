<p><?php _e('Thank you for installing our plugin.', VG_Admin_To_Frontend::$textname); ?></p>

<?php
VG_Admin_To_Frontend_Obj()->set_main_admin_id(null, true);

$steps = array();
$admin_url = admin_url();
$home_url = home_url();
if (strpos($admin_url, 'https://') !== false && strpos($home_url, 'https://') === false) {
	$steps['http_protocol_mismatch'] = '<p>' . sprintf(__('IMPORTANT. You are using https for wp-admin and http for the public website. Both need to use the same protocol (https) for security reasons. Please change the public URL to use https. <a href="%s" target="_blank" class="button">Fix it</a>', VG_Admin_To_Frontend::$textname), esc_url(admin_url('options-general.php'))) . '</p>';
}
if (!get_option('permalink_structure')) {
	$steps['permalink_missing'] = '<p>' . sprintf(__('IMPORTANT. You need to enable pretty permalinks for our plugin to work. <a href="%s" target="_blank" class="button">Fix it</a>', VG_Admin_To_Frontend::$textname), esc_url(admin_url('options-permalink.php'))) . '</p>';
}

$steps['open_settings_page'] = '<p>' . sprintf(__('Check the plugin settings. <a href="%s" target="_blank" class="button">Open settings page</a>', VG_Admin_To_Frontend::$textname), esc_url(WPFA_Options_Obj()->get_settings_page_url())) . '</p>';

$steps['create_page_easy'] = '<p>' . sprintf(__('You can use this plugin in 2 ways:<br><b>Automatic way:</b> Go to the admin page that you want to display on the frontend, and click the "View on the frontend" option in the top toolbar: <img src="%s"/><br><br><b>Manual way:</b> You can create a page manually and use this shortcode:<br><code>[vg_display_admin_page page_url="http://site.com/wp-admin/edit.php"]</code><br>You can add the shortcode anywhere in the page or using a page builder, you can use it multiple times on the same page, and you just need to replace the page_url in the shortcode with the URL of the wp-admin page.', VG_Admin_To_Frontend::$textname), esc_url(plugins_url('/assets/imgs/toolbar-item-screenshot.png', dirname(__FILE__)))) . '</p>';

if (empty(VG_Admin_To_Frontend_Obj()->allowed_urls)) {
	$allowed_urls_message = '<p>' . __('You can view any admin URL in the frontend. For example, the settings page, the widgets page, the WooCommerce settings page, the WooCommerce Sales Stats page, etc.', VG_Admin_To_Frontend::$textname) . '</p>';
} else {
	$allowed_urls_message = '<p>' . sprintf(__('You are using the Free plugin. You can view these pages in the frontend: blog posts, post editor, blog categories, and blog tags.', VG_Admin_To_Frontend::$textname)) . '</p>';

	$allowed_urls_message .= sprintf(__('<h3>Go Premium</h3><p>View ANY admin page in the frontend<br/>View settings pages from the frontend<br/>View WooCommerce settings on the frontend<br/>View WooCommerce stats from the frontend<br/>Install plugins from the frontend<br/>View plugin settings from the frontend<br/>View any page from wp-admin on the frontend<br/>And more.</p><a href="%s" class="button button-primary">%s</a> - <a href="#tutorial" class="button">Watch a demo video</a> - <a href="%s" class="button" target="_blank">Need help? Contact us</a></p><p>Try the plugin without worries.</p><p>Check this video of the premium features.</p><iframe id="tutorial" width="560" height="315" src="https://www.youtube.com/embed/EG1NE3X5yNs?rel=0&amp;controls=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>.', VG_Admin_To_Frontend::$textname), VG_Admin_To_Frontend_Obj()->args['buy_link'], VG_Admin_To_Frontend_Obj()->args['buy_link_text'], 'https://wpfrontendadmin.com/contact/?utm_source=wp-admin&utm_campaign=welcome-page-help&utm_medium=' . (empty(VG_Admin_To_Frontend_Obj()->allowed_urls) ? 'free' : 'pro') . '-plugin');
}
$steps['allowed_urls'] = $allowed_urls_message;

$steps['note'] = '<p>' . sprintf(__('You can read more about advanced settings and configuration in <a href="%s" target="_blank">our website</a>.', VG_Admin_To_Frontend::$textname), 'https://wpfrontendadmin.com/advanced-settings/?utm_source=wp-admin&utm_campaign=welcome-page-advanced-settings&utm_medium=' . (empty(VG_Admin_To_Frontend_Obj()->allowed_urls) ? 'free' : 'pro') . '-plugin') . '</p>';

$steps = apply_filters('vg_admin_to_frontend/welcome_steps', $steps);

if (!empty($steps)) {
	echo '<ol class="steps">';
	foreach ($steps as $key => $step_content) {
		?>
		<li><?php echo $step_content; ?></li>		
		<?php
	}

	echo '</ol>';
}
?>
<hr>
<h2><?php _e('Free Courses', VG_Admin_To_Frontend::$textname); ?></h2>
<div class="elementor-text-editor elementor-clearfix">
	<h3><?php _e('Free Course: Create a Restaurant Management Platform using WordPress', VG_Admin_To_Frontend::$textname); ?></h3>
	<p><?php _e('In this course, we will show you how to create a platform for restaurants from start to finish, using WordPress Multisite, WP Ultimo, WooCommerce, and WP Frontend Admin.', VG_Admin_To_Frontend::$textname); ?></p>
	<p><?php _e('<b>Restaurants will pay you a monthly fee</b> and they will be able to sell meals, manage orders, create a QR code, and have a beautiful digital menu for their restaurant.', VG_Admin_To_Frontend::$textname); ?></p>
	<a href="https://wpfrontendadmin.com/restaurant-management-platform/" target="_blank" rel="noopener" class="button button-primary"><?php _e('Sign up for FREE', VG_Admin_To_Frontend::$textname); ?></a>
</div>
<hr>
<div class="elementor-text-editor elementor-clearfix">
	<h2><?php _e('Free Course: Create a Platform like Shopify', VG_Admin_To_Frontend::$textname); ?></h2>
	<p><?php _e('In this course, we will show you how to create a platform like Shopify from start to finish, using WordPress Multisite, WP Ultimo, WooCommerce, and WP Frontend Admin.', VG_Admin_To_Frontend::$textname); ?></p>
	<p><?php _e('<b>Charge a monthly fee for each store created in your</b>&nbsp;
		<strong>platform</strong> and start making money.', VG_Admin_To_Frontend::$textname); ?></p>
	<a href="https://wpfrontendadmin.com/how-to-create-a-platform-like-shopify/" target="_blank" rel="noopener" class="button button-primary"><?php _e('Sign up for FREE', VG_Admin_To_Frontend::$textname); ?></a>
</div>
<hr>
<?php
_e('<h3>Tutorials</h3> ', VG_Admin_To_Frontend::$textname);
_e('<ul role = "menu" class = " dropdown-menu">
				<li >Allow Post Submissions from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/allow-post-submissions-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Change Permalink Settings from the Frontend in WordPress <a target = "_blank" href = "https://wpfrontendadmin.com/change-permalink-settings-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Change Site Settings from the Frontend in WordPress <a target = "_blank" href = "https://wpfrontendadmin.com/change-site-settings-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Create and Manage Users from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/create-and-manage-users-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Create WooCommerce Coupons from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/create-woocommerce-coupons-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Create WooCommerce Products from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/create-woocommerce-products-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Install Themes from the Frontend in WordPress <a target = "_blank" href = "https://wpfrontendadmin.com/install-themes-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Install Updates from the Frontend in WordPress <a target = "_blank" href = "https://wpfrontendadmin.com/install-updates-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Install WordPress Plugins from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/install-wordpress-plugins-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Manage Nav Menus from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/manage-nav-menus-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Manage User Comments from the Frontend in WordPress <a target = "_blank" href = "https://wpfrontendadmin.com/manage-user-comments-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Manage Widgets from the Frontend in WordPress <a target = "_blank" href = "https://wpfrontendadmin.com/manage-widgets-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Manage WooCommerce Settings from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/manage-woocommerce-settings-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >Setup a Theme from the Frontend in WordPress <a target = "_blank" href = "https://wpfrontendadmin.com/setup-a-theme-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >View and Dispatch WooCommerce Orders from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/view-and-dispatch-woocommerce-orders-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
				<li >View WooCommerce Sales Reports from the Frontend <a target = "_blank" href = "https://wpfrontendadmin.com/view-woocommerce-sales-reports-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li><li ><a target = "_blank" href = "https://wpfrontendadmin.com/documentation/tutorials/">More tutorials</a></li>
				</ul> ', VG_Admin_To_Frontend::$textname);
?>
<script>
	jQuery('.vg-logo').parent().attr('href', 'https://wpfrontendadmin.com/?utm_source=wp-admin&utm_campaign=logo&utm_medium=welcome-page')
	jQuery('.install-plugin-trigger').click(function (e) {
		return !window.open(this.href, 'Install plugin', 'width=500,height=500');
	});
</script>