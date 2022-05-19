<style>
	.wpfa-floating-edit-button {
		position: fixed;
		background: <?php echo sanitize_text_field($main_color); ?>;
		display: block;
		width: 70px;
		height: 70px;
		text-align: center;
		border-radius: 50%;
	}
	.wpfa-floating-edit-button.top_right {
		top: 2px; 
		right: 2px;
	}
	.wpfa-floating-edit-button.top_left {
		top: 2px; 
		left: 2px;
	}
	.wpfa-floating-edit-button.bottom_right {
		bottom: 2px; 
		right: 2px;
	}
	.wpfa-floating-edit-button.bottom_left {
		bottom: 2px; 
		left: 2px;
	}
	.wpfa-floating-edit-button  span {
		font-size: 40px;
		color: white;
		width: auto;
		height: auto;
		display: inline-block;
		line-height: 70px;		
	}
	.admin-bar .wpfa-floating-edit-button.top_right {
		top: 42px; 
	}
	.admin-bar .wpfa-floating-edit-button.top_left {
		top: 42px; 
	}
	.vg-frontend-admin-visible-quick-settings .wpfa-floating-edit-button.top_left,
	.vg-frontend-admin-visible-quick-settings .wpfa-floating-edit-button.bottom_left {
		left: 274px; 
	}
</style>
<a href="<?php echo esc_url($edit_url); ?>" <?php if (!empty($edit_url) && $edit_url !== '#') {
	echo 'target="_blank"';
} ?> class="wpfa-floating-edit-button live-edit <?php echo sanitize_html_class($class); ?>">
	<span class="dashicons dashicons-edit"></span>
</a>