<?php /* phpcs:ignore */

if ( defined( 'ABSPATH' ) && ! class_exists( 'WJECF_Autocoupon' ) ) {
	class WJECF_Autocoupon extends Abstract_WJECF_Plugin {

		protected $_autocoupons            = null;
		protected $_executed_coupon_by_url = false;

		public function __construct() {
			$this->set_plugin_data(
				array(
					'description'        => __( 'Allow coupons to be automatically applied to the cart when restrictions are met or by url.', 'woocommerce-jos-autocoupon' ),
					'dependencies'       => array(),
					'admin_dependencies' => array( 'admin-settings' ),
					'can_be_disabled'    => true,
				)
			);
		}

		public function init_hook() {
			//Since 2.6.2 check for frontend request. Prevents 'Call to undefined function wc_add_notice()'.
			if ( ! WJECF()->is_request( 'frontend' ) ) {
				return;
			}

			//NOTE: WPML Causes an extra calculate_totals() !!!

			//Frontend hooks - logic
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'action_woocommerce_cart_loaded_from_session' ) );

			//Update of auto-coupons is required after those actions:
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'force_update_after_calculate' ) );
			add_action( 'woocommerce_check_cart_items', array( $this, 'action_woocommerce_check_cart_items' ), 0, 0 ); //Remove coupon before WC does it and shows a message.

			//update auto-coupons before WC_Cart_Session::set_session() which has priority 10
			add_action( 'woocommerce_after_calculate_totals', array( $this, 'maybe_update_matched_autocoupons' ), 5 );

			//Frontend hooks - visualisation
			add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'woocommerce_cart_totals_coupon_label' ), 10, 2 );
			add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'woocommerce_cart_totals_coupon_html' ), 10, 3 );

			//Checkout: force cart preview refresh
			add_filter( 'woocommerce_checkout_fields', array( $this, 'filter_woocommerce_checkout_fields' ), 10, 1 );
			add_action( 'woocommerce_review_order_before_payment', array( $this, 'action_woocommerce_review_order_before_payment' ) );

			//Inhibit redirect to cart when apply_coupon is supplied
			add_filter( 'option_woocommerce_cart_redirect_after_add', array( $this, 'option_woocommerce_cart_redirect_after_add' ) );

			if ( ! WJECF()->is_request( 'ajax' ) ) {
				//Cart is not available before wp_loaded!
				add_action( 'wp_loaded', array( $this, 'coupon_by_url' ), 90 ); //Coupon through url
			}

			/**
			 * Mark removed autocoupons to prevent them from being automatically applied again
			 * (PRO Only)
			 * @since 2.5.4
			 */
			if ( WJECF()->is_pro() ) {
				add_action( 'woocommerce_applied_coupon', array( $this, 'action_applied_coupon' ), 10, 1 );
				add_action( 'woocommerce_removed_coupon', array( $this, 'action_removed_coupon' ), 10, 1 );
				add_action( 'woocommerce_cart_emptied', array( $this, 'action_cart_emptied' ), 10, 0 );
			}
		}

		/* ADMIN HOOKS */
		public function init_admin_hook() {
			add_action( 'wjecf_woocommerce_coupon_options_extended_features', array( $this, 'admin_coupon_options_extended_features' ), 20, 2 );

			//Invalidate matched autocoupons after save/delete/create new coupon
			//This will cause update_matched_autocoupons for all carts (using the situation hash)
			add_action( 'woocommerce_update_coupon', array( $this, 'force_situation_change' ) );
			add_action( 'woocommerce_delete_coupon', array( $this, 'force_situation_change' ) );
			add_action( 'woocommerce_trash_coupon', array( $this, 'force_situation_change' ) );
			add_action( 'woocommerce_new_coupon', array( $this, 'force_situation_change' ) );

			//Inject columns
			if ( WJECF()->is_pro() ) {
				WJECF()->inject_coupon_column(
					'_wjecf_auto_coupon',
					__( 'Auto coupon', 'woocommerce-jos-autocoupon' ),
					array( $this, 'admin_render_shop_coupon_columns' ),
					'coupon_code'
				);
				WJECF()->inject_coupon_column(
					'_wjecf_individual_use',
					__( 'Individual use', 'woocommerce-jos-autocoupon' ),
					array( $this, 'admin_render_shop_coupon_columns' ),
					'coupon_code'
				);
			}

			add_filter( 'views_edit-shop_coupon', array( $this, 'admin_views_edit_coupon' ) );
			add_filter( 'request', array( $this, 'admin_request_query' ) );

			add_action( 'wjecf_admin_before_settings', array( $this, 'wjecf_admin_before_settings' ), 20 );
			add_filter( 'wjecf_admin_validate_settings', array( $this, 'wjecf_admin_validate_settings' ), 10, 2 );
		}

		public function wjecf_admin_before_settings() {
			$page = WJECF_Admin_Settings::SETTINGS_PAGE;

			add_settings_section(
				WJECF_Admin_Settings::DOM_PREFIX . 'section_autocoupon',
				__( 'Auto coupons', 'woocommerce-jos-autocoupon' ),
				array( $this, 'render_section' ),
				$page
			);

			if ( WJECF()->is_pro() ) {
				add_settings_field(
					WJECF_Admin_Settings::DOM_PREFIX . 'autocoupon_allow_remove',
					__( 'Allow remove \'Auto Coupons\'', 'woocommerce-jos-autocoupon' ),
					array( $this, 'render_setting_allow_remove_auto_coupon' ),
					$page,
					WJECF_Admin_Settings::DOM_PREFIX . 'section_autocoupon'
				);
			}

			add_settings_field(
				WJECF_Admin_Settings::DOM_PREFIX . 'autocoupon_checkout_update_on_email_change',
				__( 'Checkout order review', 'woocommerce-jos-autocoupon' ),
				array( $this, 'render_setting_autocoupon_checkout_update_on_email_change' ),
				$page,
				WJECF_Admin_Settings::DOM_PREFIX . 'section_autocoupon'
			);

			add_settings_field(
				WJECF_Admin_Settings::DOM_PREFIX . 'autocoupon_performance_mode',
				__( 'High performance mode', 'woocommerce-jos-autocoupon' ),
				array( $this, 'render_setting_autocoupon_performance_mode' ),
				$page,
				WJECF_Admin_Settings::DOM_PREFIX . 'section_autocoupon'
			);

		}

		public function render_section( $section ) {
			switch ( $section['id'] ) {
				case WJECF_Admin_Settings::DOM_PREFIX . 'section_autocoupon':
					//$body = ".....";
					//printf( '<p>%s</p>', $body );
					break;
			}
		}

		public function render_setting_allow_remove_auto_coupon() {
			$option_name = 'autocoupon_allow_remove';
			$args        = array(
				'type'  => 'checkbox',
				'id'    => WJECF_Admin_Settings::DOM_PREFIX . $option_name,
				'name'  => sprintf( '%s[%s]', WJECF_Admin_Settings::OPTION_NAME, $option_name ),
				'value' => $this->get_option_autocoupon_allow_remove() ? 'yes' : 'no',
			);

			WJECF_Admin_Html::render_input( $args );
			WJECF_Admin_Html::render_tag(
				'label',
				array( 'for' => esc_attr( $args['id'] ) ),
				__( 'Enabled', 'woocommerce' )
			);
			WJECF_Admin_Html::render_tag(
				'p',
				array( 'class' => 'description' ),
				__( 'Check this box to allow the customer to remove \'Auto Coupons\' from the cart.', 'woocommerce-jos-autocoupon' )
			);
		}

		public function render_setting_autocoupon_performance_mode() {
			$option_name = 'autocoupon_performance_mode';
			$args        = array(
				'type'  => 'checkbox',
				'id'    => WJECF_Admin_Settings::DOM_PREFIX . $option_name,
				'name'  => sprintf( '%s[%s]', WJECF_Admin_Settings::OPTION_NAME, $option_name ),
				'value' => $this->get_option_autocoupon_performance_mode() ? 'yes' : 'no', //Default: yes
			);

			WJECF_Admin_Html::render_input( $args );
			WJECF_Admin_Html::render_tag(
				'label',
				array( 'for' => esc_attr( $args['id'] ) ),
				__( 'Enabled', 'woocommerce' )
			);
			WJECF_Admin_Html::render_tag(
				'p',
				array( 'class' => 'description' ),
				__( "When enabled, valid 'Auto Coupons' will only be applied when a change to the customer's cart is detected. When unchecked, valid 'Auto Coupons' will be updated at every request. Default: Enabled.", 'woocommerce-jos-autocoupon' )
			);
		}

		public function render_setting_autocoupon_checkout_update_on_email_change() {
			$option_name = 'autocoupon_checkout_update_on_email_change';
			$args        = array(
				'type'  => 'checkbox',
				'id'    => WJECF_Admin_Settings::DOM_PREFIX . $option_name,
				'name'  => sprintf( '%s[%s]', WJECF_Admin_Settings::OPTION_NAME, $option_name ),
				'value' => $this->get_option_autocoupon_checkout_update_on_email_change() ? 'yes' : 'no', //Default: no
			);

			echo '<fieldset>';
			WJECF_Admin_Html::render_input( $args );
			WJECF_Admin_Html::render_tag(
				'label',
				array( 'for' => esc_attr( $args['id'] ) ),
				__( 'Update order review on billing email change', 'woocommerce-jos-autocoupon' )
			);
			WJECF_Admin_Html::render_tag(
				'p',
				array( 'class' => 'description' ),
				__( 'Use this for Auto Coupons that are restricted by email address or usage limit per user. Default: Disabled.', 'woocommerce-jos-autocoupon' )
			);
			echo '</fieldset><br>';

			// =========================

			$option_name = 'autocoupon_checkout_update_on_payment_change';
			$args        = array(
				'type'  => 'checkbox',
				'id'    => WJECF_Admin_Settings::DOM_PREFIX . $option_name,
				'name'  => sprintf( '%s[%s]', WJECF_Admin_Settings::OPTION_NAME, $option_name ),
				'value' => $this->get_option_autocoupon_checkout_update_on_payment_change() ? 'yes' : 'no', //Default: no
			);

			echo '<fieldset>';
			WJECF_Admin_Html::render_input( $args );
			WJECF_Admin_Html::render_tag(
				'label',
				array( 'for' => esc_attr( $args['id'] ) ),
				__( 'Update order review on payment method change', 'woocommerce-jos-autocoupon' )
			);
			WJECF_Admin_Html::render_tag(
				'p',
				array( 'class' => 'description' ),
				__( 'Use this for Auto Coupons that are restricted by payment method. Default: Disabled.', 'woocommerce-jos-autocoupon' )
			);
			echo '</fieldset><br>';
		}

		public function wjecf_admin_validate_settings( $options, $input ) {
			$names = array( 'autocoupon_allow_remove', 'autocoupon_performance_mode', 'autocoupon_checkout_update_on_email_change', 'autocoupon_checkout_update_on_payment_change' );
			foreach ( $names as $name ) {
				$options[ $name ] = isset( $input[ $name ] ) && 'yes' === $input[ $name ];
			}
			return $options;
		}

		/**
		 * Output a coupon custom column value
		 *
		 * @param string $column
		 * @param WP_Post The coupon post object
		 */
		public function admin_render_shop_coupon_columns( $column, $post ) {
			$coupon = new WC_Coupon( intval( $post->ID ) );

			switch ( $column ) {
				case '_wjecf_auto_coupon':
					$is_auto_coupon = $coupon->get_meta( '_wjecf_is_auto_coupon' ) == 'yes';
					echo $is_auto_coupon ? __( 'Yes', 'woocommerce' ) : __( 'No', 'woocommerce' );
					if ( $is_auto_coupon ) {
						$prio = $coupon->get_meta( '_wjecf_coupon_priority' );
						if ( $prio ) {
							echo ' (' . intval( $prio ) . ')';
						}
					}
					break;
				case '_wjecf_individual_use':
					$individual = $coupon->get_individual_use();
					echo $individual ? __( 'Yes', 'woocommerce' ) : __( 'No', 'woocommerce' );
					break;
			}
		}

		public function admin_views_edit_coupon( $views ) {
			global $post_type, $wp_query;

			$class                         = ( isset( $wp_query->query['meta_key'] ) && '_wjecf_is_auto_coupon' == $wp_query->query['meta_key'] ) ? 'current' : '';
			$query_string                  = remove_query_arg( array( 'wjecf_is_auto_coupon' ) );
			$query_string                  = add_query_arg( 'wjecf_is_auto_coupon', '1', $query_string );
			$views['wjecf_is_auto_coupon'] = '<a href="' . esc_url( $query_string ) . '" class="' . esc_attr( $class ) . '">' . __( 'Auto coupons', 'woocommerce-jos-autocoupon' ) . '</a>';

			return $views;
		}

		/**
		 * Filters and sorting handler
		 *
		 * @param  array $vars
		 * @return array
		 */
		public function admin_request_query( $vars ) {
			global $typenow, $wp_query, $wp_post_statuses;

			if ( 'shop_coupon' === $typenow ) {
				if ( isset( $_GET['wjecf_is_auto_coupon'] ) ) {
					$vars['meta_key']   = '_wjecf_is_auto_coupon';
					$vars['meta_value'] = '1' == $_GET['wjecf_is_auto_coupon'] ? 'yes' : 'no';
				}
			}

			return $vars;
		}

		public function admin_coupon_options_extended_features( $thepostid, $post ) {
			//=============================
			//Title
			echo '<h3>' . esc_html( __( 'Auto coupon', 'woocommerce-jos-autocoupon' ) ) . "</h3>\n";

			//=============================
			// Auto coupon checkbox
			woocommerce_wp_checkbox(
				array(
					'id'          => '_wjecf_is_auto_coupon',
					'label'       => __( 'Auto coupon', 'woocommerce-jos-autocoupon' ),
					'description' => __( "Automatically add the coupon to the cart if the restrictions are met. Please enter a description when you check this box, the description will be shown in the customer's cart if the coupon is applied.", 'woocommerce-jos-autocoupon' ),
				)
			);

			echo '<div class="_wjecf_show_if_autocoupon">';
			if ( WJECF()->is_pro() ) {
				// Maximum quantity of matching products (product/category)
				woocommerce_wp_text_input(
					array(
						'id'          => '_wjecf_coupon_priority',
						'label'       => __( 'Priority', 'woocommerce-jos-autocoupon' ),
						'placeholder' => __( 'No priority', 'woocommerce' ),
						'description' => __( 'When \'individual use\' is checked, auto coupons with a higher value will have priority over other auto coupons.', 'woocommerce-jos-autocoupon' ),
						'data_type'   => 'decimal',
						'desc_tip'    => true,
					)
				);
			}

			//=============================
			// Apply without notice
			woocommerce_wp_checkbox(
				array(
					'id'          => '_wjecf_apply_silently',
					'label'       => __( 'Apply silently', 'woocommerce-jos-autocoupon' ),
					'description' => __( "Don't display a message when this coupon is automatically applied.", 'woocommerce-jos-autocoupon' ),
				)
			);
			echo '</div>';

			?>
			<script type="text/javascript">
				jQuery( function( $ ) {
					//Hide/show when AUTO-COUPON value changes
					function toggled_autocoupon( animation ) {
						if ( animation === undefined ) animation = 'slow';

						if (jQuery("#_wjecf_is_auto_coupon").prop('checked')) {
							jQuery("._wjecf_show_if_autocoupon").show( animation );
							jQuery("._wjecf_hide_if_autocoupon").hide( animation );
						} else {
							jQuery("._wjecf_show_if_autocoupon").hide( animation );
							jQuery("._wjecf_hide_if_autocoupon").show( animation );
						}
					}

					toggled_autocoupon( 0 );
					$("#_wjecf_is_auto_coupon").click( toggled_autocoupon );
				} );
			</script>
			<?php
		}

		public function admin_coupon_meta_fields( $coupon ) {
			$fields = array(
				'_wjecf_is_auto_coupon' => 'yesno',
				'_wjecf_apply_silently' => 'yesno',
			);

			if ( WJECF()->is_pro() ) {
				$fields['_wjecf_coupon_priority'] = 'int';
			}
			return $fields;
		}

		/* FRONTEND HOOKS */

		/**
		 * Inhibit redirect to cart when apply_coupon supplied
		 *
		 * @param string $value 'yes' or 'no'
		 * @return string 'yes' or 'no'
		 */
		public function option_woocommerce_cart_redirect_after_add( $value ) {
			if ( ! $this->_executed_coupon_by_url && isset( $_GET['apply_coupon'] ) ) {
				$value = 'no';
			}
			return $value;
		}

		/**
		 * Add coupon through url
		 *
		 * @return void
		 */
		public function coupon_by_url() {
			//Apply coupon by url
			if ( ! WJECF()->is_request( 'frontend' ) || ! isset( $_GET['apply_coupon'] ) ) {
				return;
			}

			$this->_executed_coupon_by_url = true;
			$split                         = explode( ',', wc_clean( $_GET['apply_coupon'] ) );
			//2.2.2 Make sure a session cookie is set
			if ( ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}

			$cart = WC()->cart;
			foreach ( $split as $coupon_code ) {
				$coupon = WJECF_WC()->get_coupon( $coupon_code );
				$cart->add_discount( $coupon_code );
			}

			//Redirect to page without autocoupon query args
			wp_safe_redirect( remove_query_arg( array( 'apply_coupon', 'add-to-cart' ) ) );
			exit;
		}

		/**
		 * Overwrite the html created by wc_cart_totals_coupon_label() so a descriptive text will be shown for the discount.
		 *
		 * @param string $coupon_label The default text created by wc_cart_totals_coupon_label()
		 * @param WC_Coupon $coupon The coupon data
		 * @return string The overwritten text
		 */
		function woocommerce_cart_totals_coupon_label( $coupon_label, $coupon ) {
			if ( $this->is_auto_coupon( $coupon ) ) {
				$coupon_label = $coupon->get_description();
			}
			return $coupon_label;
		}

		/**
		 * Overwrite the html created by wc_cart_totals_coupon_html(). This function is required to remove the "Remove" link.
		 * @param  string $coupon_html The html created by wc_cart_totals_coupon_html()
		 * @param  WC_Coupon $coupon The coupon data
		 * @return string The overwritten html
		 */
		function woocommerce_cart_totals_coupon_html( $coupon_html, $coupon, $discount_amount_html ) {
			if ( $this->is_auto_coupon( $coupon ) && ! $this->get_option_autocoupon_allow_remove() ) {
				//Remove the '[Remove]'-part.
				$coupon_html = $discount_amount_html;
			}
			return $coupon_html;
		}

		// ============ BEGIN wjecf_autocoupon_removed_coupons =======================

		// When a custom clicks the [remove]-button of an auto coupon, the coupon will not be applied automatically anymore...
		// After that the coupon can only be applied manually by entering the coupon code.

		/**
		 * Remove the coupon from session 'wjecf_autocoupon_removed_coupons'
		 *
		 * @since 2.5.4
		 * @param string $coupon_code
		 * @return void
		 */
		function action_applied_coupon( $coupon_code ) {
			if ( ! $this->is_auto_coupon( $coupon_code ) || ! $this->get_option_autocoupon_allow_remove() ) {
				return;
			}

			$removed_autocoupon_codes = $this->get_removed_autocoupon_codes();
			if ( ! isset( $removed_autocoupon_codes[ $coupon_code ] ) ) {
				return;
			}

			unset( $removed_autocoupon_codes[ $coupon_code ] );
			$this->set_removed_autocoupon_codes( $removed_autocoupon_codes );
		}

		/**
		 * Add the coupon to session 'wjecf_autocoupon_removed_coupons'
		 *
		 * @since 2.5.4
		 * @param string $coupon_code
		 */
		function action_removed_coupon( $coupon_code ) {
			if ( ! $this->is_auto_coupon( $coupon_code ) || ! $this->get_option_autocoupon_allow_remove() ) {
				return;
			}
			$manual  = isset( $_GET['wc-ajax'] ) && 'remove_coupon' === $_GET['wc-ajax'] && sanitize_text_field( $_POST['coupon'] ) === $coupon_code;
			$manual |= isset( $_GET['remove_coupon'] ) && sanitize_text_field( $_GET['remove_coupon'] ) === $coupon_code;

			//Ignore, because the auto-coupon was removed automatically (not manually by the customer)
			if ( $manual ) {
				$removed_autocoupon_codes                 = $this->get_removed_autocoupon_codes();
				$removed_autocoupon_codes[ $coupon_code ] = $coupon_code;
				$this->set_removed_autocoupon_codes( $removed_autocoupon_codes );
			}
		}

		/**
		 * Remove 'wjecf_autocoupon_removed_coupons' from the session when cart is emptied
		 *
		 * @since 2.5.4
		 */
		function action_cart_emptied() {
			$this->set_removed_autocoupon_codes( null );
		}

		/**
		 * Force the checkout cart to be refreshed upon billing email change
		 *
		 * @since 3.0.0
		 * @param array $checkout_fields
		 * @return array
		 */
		function filter_woocommerce_checkout_fields( $checkout_fields ) {
			if ( $this->get_option_autocoupon_checkout_update_on_email_change() ) {
				$checkout_fields['billing']['billing_email']['class'][] = 'update_totals_on_change';
			}
			return $checkout_fields;
		}

		/**
		 * Force the checkout cart to be refreshed upon payment method change
		 *
		 * @since 3.0.0
		 * @return void
		 */
		function action_woocommerce_review_order_before_payment() {
			if ( ! $this->get_option_autocoupon_checkout_update_on_payment_change() ) {
				return;
			}

			//Trigger 'payment_method_selected' has been introduced in WC 3.3.0
			$event = WJECF_WC()->check_woocommerce_version( '3.3' ) ? "'payment_method_selected'" : "'change', 'input[name=payment_method]'";
			?>
				<script>
					jQuery( document.body ).on( <?php echo $event; ?>, function() {
						jQuery(document.body).trigger('update_checkout');
					} );
				</script>
			<?php
		}

		/**
		 * Get the removed auto coupon-codes from the session
		 *
		 * @since 2.5.4
		 * @return array The queued coupon codes
		 */
		private function get_removed_autocoupon_codes() {
			$coupon_codes = WC()->session->get( 'wjecf_autocoupon_removed_coupons', array() );
			return $coupon_codes;
		}

		/**
		 * Save the removed auto coupon-codes in the session
		 *
		 * @since 2.5.4
		 * @param array $coupon_codes
		 * @return void
		 */
		private function set_removed_autocoupon_codes( $coupon_codes ) {
			WC()->session->set( 'wjecf_autocoupon_removed_coupons', $coupon_codes );
		}

		/**
		 * Reads the option 'autocoupon_allow_remove'
		 *
		 * @since 2.5.4
		 * @return bool
		 */
		private function get_option_autocoupon_allow_remove() {
			return WJECF()->is_pro() && WJECF()->get_option( 'autocoupon_allow_remove', false );
		}

		/**
		 * Reads the option 'autocoupon_performance_mode'
		 *
		 * @since 3.0.0
		 * @return bool
		 */
		private function get_option_autocoupon_performance_mode() {
			return WJECF()->get_option( 'autocoupon_performance_mode', true );
		}

		/**
		 * Reads the option 'autocoupon_performance_mode'
		 *
		 * @since 3.0.0
		 * @return bool
		 */
		private function get_option_autocoupon_checkout_update_on_email_change() {
			return WJECF()->get_option( 'autocoupon_checkout_update_on_email_change', false );
		}

		/**
		 * Reads the option 'autocoupon_performance_mode'
		 *
		 * @since 3.0.0
		 * @return bool
		 */
		private function get_option_autocoupon_checkout_update_on_payment_change() {
			return WJECF()->get_option( 'autocoupon_checkout_update_on_payment_change', false );
		}
		// ============ END wjecf_autocoupon_removed_coupons =======================

		/**
		 * Sets coupon transient version to current time. Will force situation update for all sessions
		 *
		 * @since 3.0.0
		 * @return void
		 */
		public function force_situation_change() {
			$this->get_situation_transient_version( true );
		}

		/**
		 * Get transient version. If version is not set, it will set version to time()
		 *
		 * @param boolean $refresh true to force a new version.
		 * @return void transient version based on time()
		 */
		private function get_situation_transient_version( $refresh = false ) {
			$transient_name  = 'wjecf_situation_transient_version';
			$transient_value = get_transient( $transient_name );

			if ( false === $transient_value || true === $refresh ) {
				//Remove caches
				$this->_autocoupons = null;

				$transient_value = time();
				set_transient( $transient_name, $transient_value );
			}
			return $transient_value;
		}

		/**
		 * Get a hash of the cart contents and applied coupons
		 *
		 * @return void
		 */
		private function get_situation_hash() {
			$situation = array(
				'cart'                        => WC()->cart->get_cart_for_session(),
				'applied_coupons'             => WC()->cart->get_applied_coupons(), //Dispite the name of the method these are coupon_codes. Not WC_Coupon objects!
				'shipping_methods'            => isset( WC()->session->chosen_shipping_methods ) ? WC()->session->chosen_shipping_methods : array(),
				'payment_method'              => isset( WC()->session->chosen_payment_method ) ? WC()->session->chosen_payment_method : array(),
				'situation_transient_version' => $this->get_situation_transient_version(),
				'date'                        => current_time( 'Y-m-d' ), //Enforce auto coupons to be refreshed when the date has changed since last refresh.
				// 'customer' => WC()->customer,
			);

			/**
			 * Auto Coupons will only be updated if the situation hash has changed.
			 * Use this filter to add additional entries to the situation.
			 * For example: Append the time if you create a rule that only allows a coupon on certain hours of the day
			 */
			$situation = apply_filters( 'wjecf_get_situation', $situation );
			return md5( wp_json_encode( $situation ) );
		}

		/**
		 * Hash of the situation (cart, coupons, shipping/payment methods...) when update_matched_autocoupons was called for the last time
		 *
		 * @var string
		 */
		private $known_situation_hash = '';
		private $busy_updating        = false;

		function action_woocommerce_cart_loaded_from_session() {
			$this->known_situation_hash = WJECF()->get_session( 'wjecf_autocoupon_situation_hash', '' );
		}

		function force_update_after_calculate() {
			//Invalidate the hash
			$this->known_situation_hash = '';
		}

		function action_woocommerce_check_cart_items() {
			$this->remove_unmatched_autocoupons();
		}

		/**
		 * Updates the matched auto coupons in the cart, but only if the situation has changed since last call of this method.
		 *
		 * Internally protected from recursive calls.
		 *
		 * @return void
		 */
		function maybe_update_matched_autocoupons() {
			if ( $this->busy_updating ) {
				return;
			}

			if ( $this->get_option_autocoupon_performance_mode() ) {
				$hash = $this->get_situation_hash();
				if ( $hash === $this->known_situation_hash ) {
					return;
				}
				$this->log( 'debug', sprintf( 'Situation hash has changed from %s to %s', substr( $this->known_situation_hash, 0, 6 ), substr( $hash, 0, 6 ) ) );
			}

			//Prevent recursive calling of woocommerce_after_calculate_totals.
			$this->busy_updating = true;
			$this->update_matched_autocoupons();
			$this->busy_updating = false;

			$this->known_situation_hash = $this->get_situation_hash();
			WJECF()->set_session( 'wjecf_autocoupon_situation_hash', $this->known_situation_hash );
		}

		/**
		 * Apply matched autocoupons and remove unmatched autocoupons.
		 * @return void
		 */
		private function update_matched_autocoupons() {

			//Allow WJECF_Pro_Coupon_Queueing to apply valid queued coupons.
			/**
			 * Called before WJECF updates the valid auto-coupons to the cart
			 */
			do_action( 'wjecf_before_update_matched_autocoupons' );

			//Get the coupons that should be in the cart
			$valid_coupons = $this->get_valid_auto_coupons();

			$valid_coupon_codes = array();
			foreach ( $valid_coupons as $coupon ) {
				$valid_coupon_codes[] = $coupon->get_code();
			}

			$this->log( 'debug', sprintf( 'Auto coupons that should be in cart: %s', implode( ', ', $valid_coupon_codes ) ) );

			$calc_needed = $this->remove_unmatched_autocoupons( $valid_coupon_codes );

			//Add valids
			foreach ( $valid_coupons as $coupon ) {
				$coupon_code = $coupon->get_code();
				if ( ! WC()->cart->has_discount( $coupon_code ) ) {
					$this->log( 'debug', sprintf( 'Applying auto coupon %s', $coupon_code ) );

					$apply_silently = $coupon->get_meta( '_wjecf_apply_silently' ) == 'yes';

					if ( $apply_silently ) {
						$new_succss_msg = ''; // no message
					} else {
						$coupon_excerpt = $coupon->get_description();
						$new_succss_msg = sprintf(
							// translators: %s: coupon code or excerpt
							__( 'Discount applied: %s', 'woocommerce-jos-autocoupon' ),
							__( empty( $coupon_excerpt ) ? $coupon_code : $coupon_excerpt, 'woocommerce-jos-autocoupon' ) // phpcs:ignore
						);
					}

					WJECF()->start_overwrite_success_message( $coupon, $new_succss_msg );
					WC()->cart->add_discount( $coupon_code ); //Causes calculation and will remove other coupons if it's an individual coupon.
					WJECF()->stop_overwrite_success_message();

					$calc_needed = false; //Already done by adding the discount
				}
			}

			$this->log( 'debug', 'Coupons in cart: ' . implode( ', ', WC()->cart->get_applied_coupons() ) . ( $calc_needed ? '. RECALC' : '' ) );

			if ( $calc_needed ) {
				WC()->cart->calculate_totals();
			}

			//Allow WJECF_Pro_Free_Products to notify changes in the free product-selection coupons
			do_action( 'wjecf_after_update_matched_autocoupons' );
		}

		/**
		 * Remove unmatched auto-coupons from the cart.
		 *
		 * @param array $valid_coupon_codes string[] Coupon codes that we know that are valid and don't need to be removed.
		 * @return bool True if one or more coupons have been removed
		 */
		private function remove_unmatched_autocoupons( $valid_coupon_codes = null ) {
			if ( is_null( $valid_coupon_codes ) ) {
				$valid_coupon_codes = $this->get_valid_auto_coupon_codes();
			}

			//Remove invalids
			$calc_needed = false;
			foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
				if ( in_array( $coupon_code, $valid_coupon_codes ) ) {
					continue;
				}

				$coupon = new WC_Coupon( $coupon_code );

				if ( ! $this->is_auto_coupon( $coupon ) && $coupon->is_valid() ) {
					continue;
				}

				/**
				 * Filter: wjecf_auto_remove_invalid_coupon
				 *
				 * Decide if a coupon should automatically be removed when it's invalid
				 * (Allows WJECF_Pro_Coupon_Queueing coupons to be removed...)
				 *
				 * @since 3.0.0
				 *
				 * @param bool        $remove                Remove the coupon?
				 * @param WC_Coupon   $coupon                The coupons
				 */
				if ( ! apply_filters( 'wjecf_auto_remove_invalid_coupon', $this->is_auto_coupon( $coupon ), $coupon ) ) {
					continue;
				}

				$this->log( 'debug', sprintf( 'Removing %s', $coupon_code ) );
				WC()->cart->remove_coupon( $coupon_code );
				$calc_needed = true;
			}

			return $calc_needed;
		}

		/**
		 * Get all auto-coupons that are valid for the current cart.
		 *
		 * @return array [ WC_Coupon, ... ]
		 */
		private function get_valid_auto_coupons() {
			$auto_coupons = $this->get_all_auto_coupons();

			//Array will only have values if option autocoupon_allow_remove == true
			$removed_autocoupon_codes = $this->get_option_autocoupon_allow_remove() ? $this->get_removed_autocoupon_codes() : array();

			$applied_coupon_codes = WC()->cart->get_applied_coupons();
			$applied_coupon_codes_not_auto = array();
			foreach( $applied_coupon_codes as $coupon_code ) {
				 if ( ! $this->is_auto_coupon( $coupon_code ) ) {
					$applied_coupon_codes_not_auto[] = $coupon_code;
				 }
			}

			$valid_auto_coupons = array();
			foreach ( $auto_coupons as $coupon_code => $coupon ) {
				if ( isset( $removed_autocoupon_codes[ $coupon_code ] ) ) {
					continue;
				}
				if ( ! $this->coupon_can_be_applied( $coupon ) ) {
					continue;
				}
				if ( ! $this->coupon_has_a_value( $coupon ) ) {
					continue;
				}

				$valid_auto_coupons[] = $coupon;
			}

			return WJECF()->coupon_combination_filter( $valid_auto_coupons, $applied_coupon_codes_not_auto );
		}

		/**
		 * Get all auto-coupons that are valid for the current cart.
		 *
		 * @return string[]
		 */
		private function get_valid_auto_coupon_codes() {
			//Get the coupons that should be in the cart
			$valid_coupons = $this->get_valid_auto_coupons();

			$valid_coupon_codes = array();
			foreach ( $valid_coupons as $coupon ) {
				$valid_coupon_codes[] = $coupon->get_code();
			}
			return $valid_coupon_codes;
		}

		/**
		 * Test whether the coupon is valid (to be auto-applied)
		 *
		 * @param WC_Coupon $coupon
		 * @return bool
		 */
		private function coupon_can_be_applied( $coupon ) {
			$can_be_applied = true;

			$cart = WC()->cart;

			//Test validity
			if ( ! $coupon->is_valid() ) {
				$can_be_applied = false;
			}

			if ( $can_be_applied && $coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
				$can_be_applied = false;
			}

			$check_emails = WJECF()->get_user_emails();

			//Check email restrictions (See WooCommerce: class-wc-cart.php function check_customer_coupons)
			if ( $can_be_applied ) {
				// Limit to defined email addresses.
				$restrictions = $coupon->get_email_restrictions();
				if ( is_array( $restrictions ) && 0 < count( $restrictions ) && ! $this->is_coupon_emails_allowed( $check_emails, $restrictions, $cart ) ) {
					$can_be_applied = false;
				}
				$this->log(
					'debug', sprintf(
						'Email restrictions: [ %s ]  User emails: [ %s ]  Match: %s',
						join( ', ', $restrictions ),
						join( ', ', $check_emails ),
						$can_be_applied ? 'yes' : 'no'
					)
				);
			}

			//Check usage limit (See WooCommerce: class-wc-cart.php function check_customer_coupons)
			//Note: Combining 'autocoupon' with 'usage limit' comes with a performance penalty
			if ( $can_be_applied ) {
				// Usage limits per user - check against billing and user email and user ID.
				$limit_per_user = $coupon->get_usage_limit_per_user();
				if ( 0 < $limit_per_user ) {
					$used_by         = $coupon->get_used_by();
					$usage_count     = 0;
					$user_id_matches = array( get_current_user_id() );

					// Check usage against emails.
					foreach ( $check_emails as $check_email ) {
						$usage_count      += count( array_keys( $used_by, $check_email, true ) );
						$user              = get_user_by( 'email', $check_email );
						$user_id_matches[] = $user ? $user->ID : 0;
					}

					// Check against billing emails of existing users.
					$users_query = new WP_User_Query(
						array(
							'fields'     => 'ID',
							'meta_query' => array(
								array(
									'key'     => '_billing_email',
									'value'   => $check_emails,
									'compare' => 'IN',
								),
							),
						)
					);

					$user_id_matches = array_unique( array_filter( array_merge( $user_id_matches, $users_query->get_results() ) ) );
					foreach ( $user_id_matches as $user_id ) {
						$usage_count += count( array_keys( $used_by, (string) $user_id, true ) );
					}

					if ( $usage_count >= $coupon->get_usage_limit_per_user() ) {
						$can_be_applied = false;
					}

					$this->log(
						'debug', sprintf(
							'Usage limit per user: %s  Usage count: %s Match: %s',
							$coupon->get_usage_limit_per_user(),
							$usage_count,
							$can_be_applied ? 'yes' : 'no'
						)
					);
				}
			}

			return apply_filters( 'wjecf_coupon_can_be_applied', $can_be_applied, $coupon );
		}

		private function is_coupon_emails_allowed( $check_emails, $restrictions, $cart ) {
			if ( is_callable( array( $cart, 'is_coupon_emails_allowed' ) ) ) {
				//Requires WC 3.4+
				return $cart->is_coupon_emails_allowed( $check_emails, $restrictions );
			}

			return sizeof( array_intersect( $check_emails, $restrictions ) ) > 0;
		}

		/**
		 * Does the coupon have a value? (autocoupon should not be applied if it has no value)
		 * @param  WC_Coupon $coupon The coupon data
		 * @return bool True if it has a value (discount, free shipping, whatever) otherwise false)
		 **/
		function coupon_has_a_value( $coupon ) {

			$has_a_value = false;

			if ( $coupon->is_type( 'free_gift' ) ) { // 'WooCommerce Free Gift Coupons'-plugin
				$has_a_value = true;
			} elseif ( $coupon->get_free_shipping() ) {
				$has_a_value = true;
			} else {
				//Test whether discount > 0
				//See WooCommerce: class-wc-cart.php function get_discounted_price
				global $woocommerce;
				foreach ( $woocommerce->cart->get_cart() as $cart_item ) {
					if ( $coupon->is_valid_for_cart() || $coupon->is_valid_for_product( $cart_item['data'], $cart_item ) ) {
						$has_a_value = $coupon->get_amount() > 0;
						break;
					}
				}
			}

			return apply_filters( 'wjecf_coupon_has_a_value', $has_a_value, $coupon );
		}

		/**
		 * Check wether the coupon is an "Auto coupon".
		 * @param  WC_Coupon|string $coupon The coupon data or coupon code
		 *
		 * @return bool true if it is an "Auto coupon"
		 */
		public function is_auto_coupon( $coupon ) {
			$coupon = WJECF_WC()->get_coupon( $coupon ); //In case a code is given instead of a WC_Coupon object.
			return $coupon->get_meta( '_wjecf_is_auto_coupon' ) == 'yes';
		}

		private function get_coupon_priority( $coupon ) {
			if ( WJECF()->is_pro() ) {
				$prio = $coupon->get_meta( '_wjecf_coupon_priority' );
				if ( ! empty( $prio ) ) {
					return intval( $prio );
				}
			}
			return 0;
		}

		/**
		 * Get an array of all auto coupons [ $coupon_code => $coupon ]
		 *
		 * @return WC_Coupon[] All auto couponss
		 */
		public function get_all_auto_coupons() {
			if ( ! is_array( $this->_autocoupons ) ) {
				$this->_autocoupons = array();

				$query_args = array(
					'posts_per_page' => -1,
					'post_type'      => 'shop_coupon',
					'post_status'    => 'publish',
					'orderby'        => array( 'title' => 'ASC' ),
					'meta_query'     => array(
						array(
							'key'     => '_wjecf_is_auto_coupon',
							'compare' => '=',
							'value'   => 'yes',
						),
					),
				);

				$query = new WP_Query( $query_args );
				foreach ( $query->posts as $post ) {
					$coupon                                    = new WC_Coupon( $post->post_title );
					$this->_autocoupons[ $coupon->get_code() ] = $coupon;
				}
				//Sort by priority
				uasort( $this->_autocoupons, array( $this, 'sort_auto_coupons' ) );

				$this->log( 'debug', 'Autocoupons: ' . implode( ', ', array_keys( $this->_autocoupons ) ) );
			}

			return $this->_autocoupons;
		}

		/**
		 * Compare function to sort coupons by priority
		 *
		 * @param WC_Coupon $coupon_a
		 * @param WC_Coupon $coupon_b
		 * @return int
		 */
		private function sort_auto_coupons( $coupon_a, $coupon_b ) {
			$prio_a = $this->get_coupon_priority( $coupon_a );
			$prio_b = $this->get_coupon_priority( $coupon_b );
			//$this->log( 'debug', "A: $prio_a B: $prio_b " );
			if ( $prio_a == $prio_b ) {
				return $coupon_a->get_code() < $coupon_b->get_code() ? -1 : 1; //By title ASC
			}

			return $prio_a > $prio_b ? -1 : 1; //By prio DESC
		}
	}
}
