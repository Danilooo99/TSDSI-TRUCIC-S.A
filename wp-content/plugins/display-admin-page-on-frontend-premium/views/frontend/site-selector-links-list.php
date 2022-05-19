<style>
	ul.wp-frontend-admin-sites-selector {
		list-style: none;
		box-sizing: border-box;
		display: block;
		text-align: center;
		overflow: auto;
		color: black;
		margin: 0;
		padding: 0;
	}
	ul.wp-frontend-admin-sites-selector li {
		margin: 0;
		border-right: 1px solid #999;
		width: 200px;
		display: inline-block;
		padding: 10px;
		box-sizing: border-box;
		float: left;
	}
	ul.wp-frontend-admin-sites-selector li a {
		display: block;
	}
	ul.wp-frontend-admin-sites-selector.wpfa-links-list li {
		border-right: 0;
		float: none;
	}

	ul.wp-frontend-admin-sites-selector.wpfa-links-list {
		text-align: left;
	}
</style>
<ul class="wp-frontend-admin-sites-selector wpfa-links-list">
	<?php
	foreach ($manageable_sites as $user_blog) {
		switch_to_blog($user_blog->userblog_id);
		if ($exclude_current_site && (int) $current_site_id === $user_blog->userblog_id) {
			restore_current_blog();
			continue;
		}
		?>
		<li class="<?php
		if ((int) $current_site_id === $user_blog->userblog_id) {
			echo 'active';
		}
		?>">
				<?php if ((int) $current_site_id !== $user_blog->userblog_id) { ?>			
				<a class="wpfa-dashboard-link" href="<?php echo esc_url($this->get_dashboard_url($user_blog->userblog_id)); ?>">			
				<?php } ?>
				<?php echo esc_html($user_blog->blogname); ?>
				<?php if ((int) $current_site_id !== $user_blog->userblog_id) { ?>
				</a>
			<?php } ?>
		</li>
		<?php
		restore_current_blog();
	}
	?>
</ul>