<?php
if (!class_exists('WPFA_Show_Own_Posts')) {

	class WPFA_Show_Own_Posts {

		static private $instance = false;
		var $disable_column_removal = false;

		private function __construct() {
			
		}

		function init() {

			if (is_admin() && !is_network_admin()) {
				add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 10, 2);
				add_action('pre_get_posts', array($this, 'filter_posts_query'));
			} else {
				add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_meta_box'), 15);
			}
		}

		function filter_posts_query($wp_query) {

			$vgfa = VG_Admin_To_Frontend_Obj();
			if (!function_exists('is_user_logged_in') || !is_user_logged_in() || !current_user_can('edit_posts') || wp_doing_ajax() || wp_doing_cron() || $vgfa->is_master_user()) {
				return;
			}
			$post_type = $wp_query->get('post_type');
			if (!is_string($post_type)) {
				return;
			}
			$raw_restricted_post_types = trim($vgfa->get_settings('restrict_post_types_by_author', ''));
			if (!$raw_restricted_post_types) {
				return;
			}
			$restricted_post_types = array_map('trim', explode(',', $raw_restricted_post_types));
			if (in_array($post_type, $restricted_post_types, true)) {
				$is_post_type_activated = true;
			} else {
				$is_post_type_activated = (bool) $vgfa->get_current_page_settings($post_type, 'vgfa_show_own_posts', false);
			}


			if ($is_post_type_activated) {
				$wp_query->set('author', get_current_user_id());
				add_filter('views_edit-post', array($this, 'fix_post_counts'));
			}
		}

		function fix_post_counts($views) {

			global $current_user, $wp_query;

			unset($views['mine']);

			$types = array(
				array('status' => NULL),
				array('status' => 'publish'),
				array('status' => 'draft'),
				array('status' => 'pending'),
				array('status' => 'trash')
			);

			foreach ($types as $type) {

				$query = array(
					'author' => $current_user->ID,
					'post_type' => 'post',
					'post_status' => $type['status']
				);

				$result = new WP_Query($query);

				if ($type['status'] == NULL):

					$class = ($wp_query->query_vars['post_status'] == NULL) ? ' class="current"' : '';

					$views['all'] = sprintf(__('<a href="%s"' . $class . '>All <span class="count">(%d)</span></a>', 'all'), admin_url('edit.php?post_type=post'), $result->found_posts);

				elseif ($type['status'] == 'publish'):

					$class = ($wp_query->query_vars['post_status'] == 'publish') ? ' class="current"' : '';

					$views['publish'] = sprintf(__('<a href="%s"' . $class . '>Published <span class="count">(%d)</span></a>', 'publish'), admin_url('edit.php?post_status=publish&post_type=post'), $result->found_posts);

				elseif ($type['status'] == 'draft'):

					$class = ($wp_query->query_vars['post_status'] == 'draft') ? ' class="current"' : '';

					$views['draft'] = sprintf(__('<a href="%s"' . $class . '>Draft' . ((sizeof($result->posts) > 1) ? "s" : "") . ' <span class="count">(%d)</span></a>', 'draft'), admin_url('edit.php?post_status=draft&post_type=post'), $result->found_posts);

				elseif ($type['status'] == 'pending'):

					$class = ($wp_query->query_vars['post_status'] == 'pending') ? ' class="current"' : '';

					$views['pending'] = sprintf(__('<a href="%s"' . $class . '>Pending <span class="count">(%d)</span></a>', 'pending'), admin_url('edit.php?post_status=pending&post_type=post'), $result->found_posts);

				elseif ($type['status'] == 'trash'):

					$class = ($wp_query->query_vars['post_status'] == 'trash') ? ' class="current"' : '';

					$views['trash'] = sprintf(__('<a href="%s"' . $class . '>Trash <span class="count">(%d)</span></a>', 'trash'), admin_url('edit.php?post_status=trash&post_type=post'), $result->found_posts);

				endif;
			}

			return $views;
		}

		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function render_meta_box($post) {
			$show_own_posts = get_post_meta($post->ID, 'vgfa_show_own_posts', true);
			?>
			<div class="field show-own-posts">
				<label>
					<input type="hidden" name="vgfa_show_own_posts[{post_type}]" value="">
					<input type="checkbox" name="vgfa_show_own_posts[{post_type}]" <?php checked(!empty($show_own_posts)); ?>> <?php _e('The users should see the posts created by them only', VG_Admin_To_Frontend::$textname); ?> <a href="#" data-tooltip="down" aria-label="<?php esc_attr_e('This does not apply to administrators, please test it as a normal user.', VG_Admin_To_Frontend::$textname); ?>">(?)</a>
				</label>

				<hr>
			</div>
			<?php
		}

		function save_meta_box($post_id, $post) {
			if (isset($_REQUEST['vgfa_show_own_posts']) && is_array($_REQUEST['vgfa_show_own_posts'])) {
				if (isset($_REQUEST['vgfa_show_own_posts']['{post_type}'])) {
					$_REQUEST['vgfa_show_own_posts'] = array();
				}
				$data = array_filter(array_map('sanitize_text_field', $_REQUEST['vgfa_show_own_posts']));
				update_post_meta($post_id, 'vgfa_show_own_posts', $data);
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Show_Own_Posts::$instance) {
				WPFA_Show_Own_Posts::$instance = new WPFA_Show_Own_Posts();
				WPFA_Show_Own_Posts::$instance->init();
			}
			return WPFA_Show_Own_Posts::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Show_Own_Posts_Obj')) {

	function WPFA_Show_Own_Posts_Obj() {
		return WPFA_Show_Own_Posts::get_instance();
	}

}
WPFA_Show_Own_Posts_Obj();
