
<p><b><?php _e('Note from WP Frontend Admin.', VG_Admin_To_Frontend::$textname); ?></b></p>
<p><?php printf(__('You need to enable pretty permalinks for our plugin to work. <a href="%s" target="_blank" class="button">Click here to fix it</a>', VG_Admin_To_Frontend::$textname), esc_url(admin_url('options-permalink.php')));
;
?></p>