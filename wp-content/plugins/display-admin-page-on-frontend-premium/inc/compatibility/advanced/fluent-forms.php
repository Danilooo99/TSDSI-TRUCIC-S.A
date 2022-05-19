<?php
if (!class_exists('WPFA_Fluent_Forms')) {

	class WPFA_Fluent_Forms {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!defined('FLUENTFORM_VERSION')) {
				return;
			}
			if (dapof_fs()->is_plan('platform', true)) {
				add_action('wp_footer', array($this, 'render_js'), 99);
			}
		}

		function render_js() {
			if (empty($_GET['vgfa_referrer']) || empty($_GET['fluentform_pages'])) {
				return;
			}
			$referrer_url = esc_url(base64_decode($_GET['vgfa_referrer']));
			if (empty($referrer_url)) {
				return;
			}
			?>
			<script>
				jQuery(window).on('load', function () {
					var $editLink = jQuery('#ff_preview_header a');
					$editLink.data('wpfa-orig-url', $editLink.attr('href'));
					$editLink.attr('href', <?php echo json_encode($referrer_url); ?>);
				});
			</script>
			<?php
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Fluent_Forms::$instance) {
				WPFA_Fluent_Forms::$instance = new WPFA_Fluent_Forms();
				WPFA_Fluent_Forms::$instance->init();
			}
			return WPFA_Fluent_Forms::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Fluent_Forms_Obj')) {

	function WPFA_Fluent_Forms_Obj() {
		return WPFA_Fluent_Forms::get_instance();
	}

}
add_action('init', 'WPFA_Fluent_Forms_Obj');
