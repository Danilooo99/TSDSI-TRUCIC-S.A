<?php
if (!class_exists('WPFA_Rank_Math')) {

	class WPFA_Rank_Math {

		static private $instance = false;
		var $cleanup_code_in_setup_wizard_done = false;

		private function __construct() {
			
		}

		function init() {
			if (!class_exists('RankMath')) {
				return;
			}
			if (dapof_fs()->is_plan('platform', true)) {
				add_action('admin_footer', array($this, 'render_js'), 99);
				add_action('wp_print_footer_scripts', array($this, 'render_admin_page_cleanup_code_in_setup_wizard'));
			}
		}

		function render_admin_page_cleanup_code_in_setup_wizard() {
			if (!is_admin() || empty($_GET['page']) || $_GET['page'] !== 'rank-math-wizard' || $this->cleanup_code_in_setup_wizard_done) {
				return;
			}
			$this->cleanup_code_in_setup_wizard_done = true;
			VG_Admin_To_Frontend_Obj()->cleanup_admin_page_for_frontend();
			wp_print_scripts('vg-frontend-admin-outline');
		}

		function render_js() {
			if (empty($_GET['page']) || $_GET['page'] !== 'rank-math-options-general') {
				return;
			}
			// Replace the redirect_to part in the auth links with the frontend URLs, so Rank Math send 
			// people to the frontend dashboard after a successful authentication
			$auth_url = \RankMath\Google\Authentication::get_auth_url();
			$page = \RankMath\Google\Authentication::get_page_slug();
			$old_redirect_to = rawurlencode(admin_url('admin.php?page=' . $page));
			$new_url = str_replace($old_redirect_to, 'wpfa-redirect-url', $auth_url);
			?>
			<script>
				if (window.parent != window) {
					jQuery(document).on('wpFrontendAdmin/iframeStateUpdated', function () {
						var $authLinks = jQuery('a.rank-math-authorize-account');
						var newUrl = <?php echo json_encode($new_url); ?>;
						var parentUrl = wpFrontendAdminBackend.getParentData('url');
						if (!parentUrl) {
							return true;
						}
						parentUrl = parentUrl.replace(/#.+$/, '');
						var finalUrl = newUrl.replace('wpfa-redirect-url', encodeURIComponent(parentUrl + '?a'));
						$authLinks.each(function () {
							var $authLink = jQuery(this);
							var origHref = $authLink.attr('href');
							console.log('origHref', origHref);
							$authLink.attr('data-wpfa-orig-url', origHref);
							$authLink.attr('href', finalUrl);
						});
					});
				}
			</script>
			<?php
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Rank_Math::$instance) {
				WPFA_Rank_Math::$instance = new WPFA_Rank_Math();
				WPFA_Rank_Math::$instance->init();
			}
			return WPFA_Rank_Math::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Rank_Math_Obj')) {

	function WPFA_Rank_Math_Obj() {
		return WPFA_Rank_Math::get_instance();
	}

}
add_action('init', 'WPFA_Rank_Math_Obj');
