<style>
	ul.wp-frontend-admin-sites-selector {
		list-style: none;
		box-sizing: border-box;
		display: block;
		text-align: center;
		overflow: auto;
		color: black;
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
	ul.wp-frontend-admin-sites-selector li h3 {
		font-size: 20px;
	}
</style>
<ul class="wp-frontend-admin-sites-selector">
	<?php
	foreach ($manageable_sites as $user_blog) {
		switch_to_blog($user_blog->userblog_id);
		if ($exclude_current_site === 'yes' && (int) $current_site_id === $user_blog->userblog_id) {
			restore_current_blog();
			continue;
		}
		?>
		<li class="<?php
		if ((int) $current_site_id === $user_blog->userblog_id) {
			echo 'active';
		}
		?>">
			<h3><?php echo esc_html($user_blog->blogname); ?></h3>
			<?php
			echo "<a class='wpfa-visit-link' href='" . esc_url(home_url('/')) . "'>" . __('Visit') . '</a>';

			if ((int) $current_site_id === $user_blog->userblog_id) {
				echo __('Active', VG_Admin_To_Frontend::$textname);
			} else {
				echo "<a class='wpfa-dashboard-link' href='" . esc_url($this->get_dashboard_url($user_blog->userblog_id)) . "'>" . __('Dashboard') . '</a>';
			}
			?>
		</li>
		<?php
		restore_current_blog();
	}
	?>
</ul>