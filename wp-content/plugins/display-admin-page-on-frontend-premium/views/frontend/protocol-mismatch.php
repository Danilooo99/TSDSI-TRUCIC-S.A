
<p><b><?php _e('Note from WP Frontend Admin.', VG_Admin_To_Frontend::$textname); ?></b></p>
<p><?php printf(__('The shortcode is loading a URL with https, but the frontend page uses http. The browser will not accept this. Both pages must load using https or both must load using http.', VG_Admin_To_Frontend::$textname), esc_url($final_url), esc_url($final_url)); ?></p>
<p><?php _e('Please enable https for your entire website and the shortcode will work.', VG_Admin_To_Frontend::$textname); ?></p>