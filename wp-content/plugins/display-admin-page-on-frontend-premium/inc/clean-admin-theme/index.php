<?php
if (!class_exists('WPFA_Clean_Admin_Theme')) {

	class WPFA_Clean_Admin_Theme {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (dapof_fs()->is_plan('platform', true)) {
				add_filter('redux/options/' . VG_Admin_To_Frontend::$textname . '/sections', array($this, 'add_global_options'));
				add_filter('wp_frontend_admin/admin_css', array($this, 'render_css'));
			}
		}

		function render_css($out) {

			// Dont load the admin theme if they haven't selected a main color manually
			// We skip filters because this runs in the filter, so it would trigger an infinite loop
			$main_color = VG_Admin_To_Frontend_Obj()->get_settings('main_color', '', true);
			if (empty($main_color)) {
				return $out;
			}

			$out .= file_get_contents(__DIR__ . '/styles.css');
			ob_start();
			$this->dynamic_css();
			$dynamic_css = ob_get_clean();
			$out .= $dynamic_css;
			return str_replace(array('<style>', '</style>', "\t"), '', $out);
		}

		function add_global_options($sections) {
			$sections['apperance']['fields'][] = array(
				'id' => 'main_color',
				'type' => 'color',
				'title' => __('Clean Admin Look : Primary color for admin content', VG_Admin_To_Frontend::$textname),
				'desc' => __('Select a color and we will change the design of the admin content to look much better and elegant. Leave it empty and the admin content will use the standard look. If you are using a plugin that loos bad when this option is activated, contact us and we will fix it.', VG_Admin_To_Frontend::$textname),
				'validate' => 'color',
			);
			$sections['apperance']['fields'][] = array(
				'id' => 'hover_color',
				'type' => 'color',
				'title' => __('Clean Admin Look : Hover color for links and buttons', VG_Admin_To_Frontend::$textname),
				'desc' => __('The primary color will be used for the regular state and this hover color will be used when you place the cursor over links and buttons. Leave this empty and we will use the primary color here.', VG_Admin_To_Frontend::$textname),
				'validate' => 'color',
			);
			return $sections;
		}

		function dynamic_css() {
			$main_color = VG_Admin_To_Frontend_Obj()->get_settings('main_color', '', true);
			$hover_color = VG_Admin_To_Frontend_Obj()->get_settings('hover_color', '', true);
			if (empty($hover_color)) {
				$hover_color = $main_color;
			}
			?><style>
				.wp-core-ui .button, .wp-core-ui .button-secondary,
				.wrap .page-title-action {
					color: <?php echo sanitize_text_field($main_color); ?>;
					border-color: <?php echo sanitize_text_field($main_color); ?>;
				}
				a,
				.wp-core-ui .button-link {
					color: <?php echo sanitize_text_field($main_color); ?>;
				}
				a:active, a:hover,
				.wp-core-ui .button-secondary:hover, .wp-core-ui .button.hover, .wp-core-ui .button:hover,
				.wp-core-ui .button-secondary:active, .wp-core-ui .button:active{
					opacity: 0.9;
				}
				#adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu li.wp-has-current-submenu .wp-submenu .wp-submenu-head, .folded #adminmenu li.current.menu-top, #adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu .wp-menu-arrow, #adminmenu .wp-menu-arrow div, #adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, .folded #adminmenu li.current.menu-top, .folded #adminmenu li.wp-has-current-submenu, #adminmenu li.opensub > a.menu-top, #adminmenu li > a.menu-top:focus, .woocommerce-message .button-primary, p.woocommerce-actions .button-primary, .jp-connect-full__button-container .dops-button.is-primary, .dops-button.is-primary,
				.woocommerce-BlankState a.button-primary, .woocommerce-BlankState button.button-primary, .woocommerce-message a.button-primary, .woocommerce-message button.button-primary, .wp-core-ui .button-primary,
				.components-button.is-primary {
					background: <?php echo sanitize_text_field($main_color); ?>;
					box-shadow: none;
					text-shadow: none;
				}
				.wp-core-ui .button-primary.active,
				.wp-core-ui .button-primary:focus,
				.wp-core-ui .button-primary:hover {
					box-shadow: 0 0 0 1px #a5a5a5, 0 0 0 3px #cacaca;
				}

				#adminmenu div.wp-menu-image:before {
					color: <?php echo sanitize_text_field($main_color); ?>;
					font-weight: 500;
				}

				.jp-connect-full__button-container .dops-button.is-primary,
				.woocommerce-BlankState a.button-primary, .woocommerce-BlankState button.button-primary, .woocommerce-message a.button-primary, .woocommerce-message button.button-primary,
				.components-button.is-primary,
				.dops-button.is-primary,
				.wp-core-ui .button-primary {
					font-weight: 500;
					color: white;
					border: 1px solid <?php echo sanitize_text_field($main_color); ?>;
				}
				div.woocommerce-message {
					border-left-color: <?php echo sanitize_text_field($main_color); ?> !important;
				}

				/*Hover*/				
				.wp-core-ui .button-secondary:hover, 
				.wp-core-ui .button.hover, 
				.wp-core-ui .button:hover,
				.wp-core-ui .button-secondary:active, 
				.wp-core-ui .button:active{
					color: <?php echo sanitize_text_field($hover_color); ?>;
					border-color: <?php echo sanitize_text_field($hover_color); ?>;
				}
				a:active, a:hover {
					color: <?php echo sanitize_text_field($hover_color); ?>;
				}

				#adminmenu a:hover, #adminmenu li.menu-top:hover, #adminmenu li > a.menu-top:focus, 
				.wp-core-ui .button-primary.active,
				.wp-core-ui .button-primary:focus,
				.wp-core-ui .button-primary:hover {
					background: <?php echo sanitize_text_field($hover_color); ?>;
					box-shadow: none;
					text-shadow: none;
				}
				.wp-core-ui .button-primary.active,
				.wp-core-ui .button-primary:focus,
				.wp-core-ui .button-primary:hover {
					font-weight: 500;
					color: white;
					border: 1px solid <?php echo sanitize_text_field($hover_color); ?>;
				}
			</style><?php
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Clean_Admin_Theme::$instance) {
				WPFA_Clean_Admin_Theme::$instance = new WPFA_Clean_Admin_Theme();
				WPFA_Clean_Admin_Theme::$instance->init();
			}
			return WPFA_Clean_Admin_Theme::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Clean_Admin_Theme_Obj')) {

	function WPFA_Clean_Admin_Theme_Obj() {
		return WPFA_Clean_Admin_Theme::get_instance();
	}

}
WPFA_Clean_Admin_Theme_Obj();
