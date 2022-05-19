<?php
if (!class_exists('WPFA_WPMU_Global_Page')) {

	class WPFA_WPMU_Global_Page {

		static private $instance = false;
		var $post_type = 'page';
		var $master_blog_id = null;

		private function __construct() {
			
		}

		function init() {
			if (!is_multisite()) {
				return;
			}

			if (!dapof_fs()->is_plan__premium_only('standard', true)) {
				// Don't load the global page feature if the global dashboard feature is 
				// activated because they don't need to sync pages across sites
				$global_dashboard_id = (int) VG_Admin_To_Frontend_Obj()->get_settings('global_dashboard_id');
				if ($global_dashboard_id) {
					return;
				}
				if (is_admin()) {
					add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
					add_action('save_post', array($this, 'save_meta_box'), 10, 2);
					add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 99, 2);
				} else {
					add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_meta_box'));
				}
			}
		}

		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function render_meta_box($post) {
			$checked = (bool) get_post_meta($post->ID, 'wpfa_is_global_page', true);
			?>
			<div id="global-page-wrapper">
				<label>
					<input type="hidden" value="no" name="wpfa_is_global_page">
					<input type="checkbox" name="wpfa_is_global_page" <?php checked($checked); ?>> <?php echo __('Apply changes to all the subsites?', 'vg_admin_to_frontend'); ?> <a href="#" data-tooltip="down" aria-label="<?php esc_attr_e('If you select this option, we will find all the pages with same slug in all the subsites of the multisite network and synchronize all the page information (except the featured image)', 'vg_admin_to_frontend'); ?>">(?)</a>
				</label>

			</div>
			<hr>
			<?php
		}

		function save_meta_box($master_post_id, $master_post) {
			global $wpdb;

			if ($master_post->post_type !== $this->post_type) {
				return;
			}
			$current_blog_id = get_current_blog_id();
			if ($this->master_blog_id && $current_blog_id !== $this->master_blog_id) {
				return;
			}

			if (!isset($_REQUEST['wpfa_is_global_page'])) {
				return;
			}

			$checked = (!empty($_REQUEST['wpfa_is_global_page']) && $_REQUEST['wpfa_is_global_page'] !== 'no' ) ? true : false;

			update_post_meta($master_post_id, 'wpfa_is_global_page', $checked);

			if (!$checked) {
				return;
			}

			remove_action('save_post', array($this, 'save_meta_box'), 10);
			remove_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 10);

			$master_meta = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = " . $master_post_id, ARRAY_A);

			$blogs_ids = get_sites(array(
				'fields' => 'ids',
				'site__not_in' => array(get_current_blog_id())
			));

			foreach ($blogs_ids as $blog_id) {
				if ((int) $blog_id === (int) $current_blog_id) {
					continue;
				}
				switch_to_blog($blog_id);

				$child_post_id = $this->get_global_page_child($master_post);
				if (!$child_post_id) {
					continue;
				}

				$child_post = $master_post;
				$child_post->ID = $child_post_id;
				wp_update_post($child_post);
				$wpdb->delete($wpdb->postmeta, array('post_id' => $child_post_id));
				foreach ($master_meta as $meta_row) {
					// Featured image is not synchronized for now because we have to duplicate the media file
					// we'll do it later
					if ($meta_row['meta_key'] === '_thumbnail_id') {
						continue;
					}
					$meta_row['post_id'] = $child_post_id;
					$wpdb->insert($wpdb->postmeta, $meta_row);
				}

				$add_to_menu = (int) get_post_meta($child_post_id, 'vgfa_menu', true);
				if ($add_to_menu) {
					VG_Admin_To_Frontend_Obj()->maybe_add_to_menu($child_post_id, $add_to_menu);
				}

				// If we're using elementor, we clear elementor's css cache
				if (defined('ELEMENTOR_VERSION')) {
					\Elementor\Plugin::$instance->files_manager->clear_cache();
				}
			}

			switch_to_blog($current_blog_id);
			add_action('save_post', array($this, 'save_meta_box'), 10, 2);
			add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 10, 2);
		}

		function add_new_global_page_child($master_post) {
			return wp_insert_post(array(
				'post_type' => $this->post_type,
				'post_title' => $master_post->post_title,
				'meta_input' => array(
					'wpfa_global_child_of' => $master_post->ID,
				),
			));
		}

		function get_global_page_child($master_post) {
			global $wpdb;

			$child_id = (int) $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'wpfa_global_child_of' AND meta_value = '" . (int) $master_post->ID . "' LIMIT 1");

			if (!$child_id) {
				$child_id = (int) $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = '" . $this->post_type . "' AND post_name = '" . $master_post->post_name . "' LIMIT 1");

				if ($child_id) {
					update_post_meta($child_id, 'wpfa_global_child_of', $master_post->ID);
				}
			}

			if (!$child_id) {
				$child_id = $this->add_new_global_page_child($master_post);
			}
			return $child_id;
		}

		/**
		 * Register meta box(es).
		 */
		function register_meta_boxes() {
			if (!VG_Admin_To_Frontend_Obj()->is_master_user()) {
				return;
			}
			add_meta_box('wpfa-global-page', __('Global Page', 'vg_admin_to_frontend'), array($this, 'render_meta_box'), $this->post_type, 'side');
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_WPMU_Global_Page::$instance) {
				WPFA_WPMU_Global_Page::$instance = new WPFA_WPMU_Global_Page();
				WPFA_WPMU_Global_Page::$instance->init();
			}
			return WPFA_WPMU_Global_Page::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_WPMU_Global_Page_Obj')) {

	function WPFA_WPMU_Global_Page_Obj() {
		return WPFA_WPMU_Global_Page::get_instance();
	}

}
WPFA_WPMU_Global_Page_Obj();
