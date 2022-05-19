<?php
if (!class_exists('WPFA_Gravity_Forms')) {

	class WPFA_Gravity_Forms {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			add_action('init', array($this, 'gravity_forms_compat'));
			if (!empty($_GET['page']) && $_GET['page'] === 'gf_edit_forms') {
				add_filter('wp_frontend_admin/back_button_container_selector', '__return_false');
				add_action('admin_footer', array($this, 'render_back_buttons_for_internal_pages'));
			}
		}

		function render_back_buttons_for_internal_pages() {
			?>
			<script>
				jQuery(window).on('load', function () {
					if (jQuery('html').hasClass('vgca-only-admin-content') && window.location.href.indexOf('vgfa_internal=1') > -1) {
						jQuery('.gf_toolbar_buttons_container').prepend('<button type="button" onclick="window.history.back(2); event.preventDefault; return false;" class="button"><?php echo sanitize_text_field(__('Go back', VG_Admin_To_Frontend::$textname)); ?></button>');
					}
				});
			</script>
			<?php
		}

		function gravity_forms_compat() {
			if (!class_exists('GFForms')) {
				return;
			}

			// Disable noconflict mode because it prevents our JS from loading
			update_option('gform_enable_noconflict', false);
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Gravity_Forms::$instance) {
				WPFA_Gravity_Forms::$instance = new WPFA_Gravity_Forms();
				WPFA_Gravity_Forms::$instance->init();
			}
			return WPFA_Gravity_Forms::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Gravity_Forms_Obj')) {

	function WPFA_Gravity_Forms_Obj() {
		return WPFA_Gravity_Forms::get_instance();
	}

}
WPFA_Gravity_Forms_Obj();
