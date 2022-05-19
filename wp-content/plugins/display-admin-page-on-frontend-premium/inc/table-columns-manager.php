<?php
if (!class_exists('WPFA_Table_Columns_Manager')) {

	class WPFA_Table_Columns_Manager {

		static private $instance = false;
		var $disable_column_removal = false;

		private function __construct() {
			
		}

		function init() {

			if (is_admin() && !is_network_admin()) {
				add_action('current_screen', array($this, 'filter_columns'));
				add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 10, 2);
				add_action('admin_footer', array($this, 'detect_admin_columns'));
			} else {
				add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_meta_box'));
			}
		}

		function filter_columns() {

			$screen_id = false;

			if (function_exists('get_current_screen')) {
				$screen = get_current_screen();
				$screen_id = isset($screen, $screen->id) ? $screen->id : '';
			}

			if (!empty($_REQUEST['screen'])) {
				$screen_id = sanitize_text_field(wp_unslash($_REQUEST['screen']));
			}

			if (strpos($screen_id, 'edit-') === false) {
				return;
			}
			add_filter('manage_' . str_replace('edit-', '', $screen_id) . '_posts_columns', array($this, 'remove_columns'), 99);
		}

		function remove_columns($original_columns) {
			if ($this->disable_column_removal) {
				return $original_columns;
			}
			$post_type = (!empty($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : 'post';
			$vgfa = VG_Admin_To_Frontend_Obj();
			if ($vgfa->is_master_user() && empty($_GET['vgfa_source'])) {
				return $original_columns;
			}
			$all_columns = (array) $vgfa->get_current_page_settings($post_type, 'vgfa_disabled_columns', array());

			foreach ($all_columns as $columns) {
				$columns = maybe_unserialize($columns);
				if (empty($columns) || !is_array($columns) || !isset($columns[$post_type])) {
					continue;
				}
				$original_columns = array_diff_key($original_columns, array_flip($columns[$post_type]));
			}
			return $original_columns;
		}

		function detect_admin_columns() {
			$screen = get_current_screen();
			if (!isset($screen->post_type) || $screen->base !== 'edit' || strpos($screen->id, 'edit-') === false) {
				return;
			}
			$this->disable_column_removal = true;
			require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
			$table = new WP_Posts_List_Table(array('screen' => $screen->id));
			$columns = array_filter(array_map('wp_strip_all_tags', $table->get_columns()));
			?>
			<script>
				var vgfaTableColumns = <?php echo json_encode($columns); ?>;
				var vgfaTableColumnsPostType = <?php echo json_encode($screen->post_type); ?>;
			</script>
			<?php
		}

		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function render_meta_box($post) {
			$disabled_columns = get_post_meta($post->ID, 'vgfa_disabled_columns', true);
			if (empty($disabled_columns) || !is_array($disabled_columns)) {
				$disabled_columns = array();
			}
			?>
			<div class="field columns-manager">
				<label><?php _e('Disabled columns', VG_Admin_To_Frontend::$textname); ?></label>

				<div class="column-template">
					<label><input type="checkbox" name="vgfa_disabled_columns[{post_type}][]"> <span class="column-name"></span></label>
				</div>
				<div class="columns-wrapper"></div>
				<hr>
			</div>
			<script>var vgfaDisabledColumns = <?php echo json_encode($disabled_columns); ?>;</script>
			<?php
		}

		function save_meta_box($post_id, $post) {
			$columns = isset($_REQUEST['vgfa_disabled_columns']) ? $_REQUEST['vgfa_disabled_columns'] : array();
			if (empty($columns)) {
				$columns = array();
			}
			if (isset($columns['{post_type}'])) {
				$columns = array();
			}
			$prepared = array();
			foreach ($columns as $post_type => $columns) {
				$prepared[sanitize_text_field($post_type)] = array_map('sanitize_text_field', $columns);
			}
			update_post_meta($post_id, 'vgfa_disabled_columns', $prepared);
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Table_Columns_Manager::$instance) {
				WPFA_Table_Columns_Manager::$instance = new WPFA_Table_Columns_Manager();
				WPFA_Table_Columns_Manager::$instance->init();
			}
			return WPFA_Table_Columns_Manager::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Table_Columns_Manager_Obj')) {

	function WPFA_Table_Columns_Manager_Obj() {
		return WPFA_Table_Columns_Manager::get_instance();
	}

}
WPFA_Table_Columns_Manager_Obj();
