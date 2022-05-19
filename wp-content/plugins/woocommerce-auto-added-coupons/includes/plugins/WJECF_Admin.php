<?php /* phpcs:ignore */

if ( defined( 'ABSPATH' ) && ! class_exists( 'WJECF_Admin' ) ) {
	class WJECF_Admin extends Abstract_WJECF_Plugin {


		const URL_PURCHASE_PRO  = 'https://www.soft79.nl/product/woocommerce-extended-coupon-features/';
		const URL_DOCUMENTATION = 'https://www.soft79.nl/documentation/wjecf';

		public function __construct() {
			$this->set_plugin_data(
				array(
					'description'     => __( 'Admin interface of WooCommerce Extended Coupon Features.', 'woocommerce-jos-autocoupon' ),
					'dependencies'    => array(),
					'can_be_disabled' => false,
				)
			);
		}

		public function init_admin_hook() {

			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			//Admin hooks
			add_filter( 'plugin_row_meta', array( $this, 'wjecf_plugin_meta' ), 10, 2 );
			add_action( 'admin_head', array( $this, 'on_admin_head' ) );

			add_filter( 'woocommerce_coupon_data_tabs', array( $this, 'admin_coupon_options_tabs' ), 20, 1 );
			add_action( 'woocommerce_coupon_data_panels', array( $this, 'admin_coupon_options_panels' ), 10, 0 );
			add_action( 'woocommerce_process_shop_coupon_meta', array( $this, 'process_shop_coupon_meta' ), 10, 2 );

			add_action( 'woocommerce_coupon_options_usage_restriction', array( $this, 'on_woocommerce_coupon_options_usage_restriction' ), 20, 1 );

			add_action( 'wjecf_coupon_metabox_checkout', array( $this, 'admin_coupon_metabox_checkout' ), 10, 2 );
			add_action( 'wjecf_coupon_metabox_customer', array( $this, 'admin_coupon_metabox_customer' ), 10, 2 );
			//add_action( 'wjecf_coupon_metabox_misc', array( $this, 'admin_coupon_metabox_misc' ), 10, 2 );

			$this->add_inline_style(
				'#woocommerce-coupon-data .wjecf-not-wide { width:50% }'
			);
		}

		// ===========================================================================
		// START - ADMIN NOTICES
		// Allows notices to be displayed on the admin pages
		// ===========================================================================

		private $notices = array();

		/**
		 * Enqueue a notice to display on the admin page
		 * @param stirng $html
		 * @param string $class
		 */
		public function enqueue_notice( $html, $class = 'notice-info' /*, $dismiss_name = null */ ) {
			$this->notices[] = array(
				'class' => $class,
				'html'  => wp_kses_post( $html ),
				//'dismiss_name' => esc_attr( $dismiss_name ),
			);
		}

		public function admin_notices() {
			foreach ( $this->notices as $notice ) {
				//TODO: Dismissable notices ?
				// if ( isset( $notice['dismiss_name'] ) && get_user_meta( get_current_user_id(), 'dismissed_wjecf_' . $notice['dismiss_name'], true ) ) {
				// 	return;
				// }
				echo '<div class="notice ' . $notice['class'] . '">';
				// if ( isset( $notice['dismiss_name'] ) ) {
				// 	$dismiss_url = esc_url( wp_nonce_url( add_query_arg( 'wc-hide-notice', 'wjecf_' . $notice['dismiss_name'] ), 'woocommerce_hide_notices_nonce', '_wc_notice_nonce' ) );

				// 	echo '<a class="woocommerce-message-close notice-dismiss" style="top:0;" href="' . $dismiss_url . '">';
				// 	echo esc_html_e( 'Dismiss', 'woocommerce' );
				// 	echo '</a>';
				// }
				echo '<p><strong>WooCommerce Extended Coupon Features</strong> &#8211; ';
				echo $notice['html'];
				echo '</div>';
			}
			$this->notices = array();
		}

		// ===========================================================================
		// END - ADMIN NOTICES
		// ===========================================================================

		//2.3.6 Inline css
		private $admin_css = '';

		/**
		 * 2.3.6
		 * @return void
		 */
		function on_admin_head() {
			//Output inline style for the admin pages
			if ( ! empty( $this->admin_css ) ) {
				echo '<style type="text/css">' . $this->admin_css . '</style>';
				$this->admin_css = '';
			}

			//Enqueue scripts
			wp_enqueue_script( 'wjecf-admin', WJECF()->plugin_url() . 'assets/js/wjecf-admin.js', array( 'jquery' ), WJECF()->plugin_version() );
			wp_localize_script(
				'wjecf-admin', 'wjecf_admin_i18n', array(
					'label_and' => __( '(AND)', 'woocommerce-jos-autocoupon' ),
					'label_or'  => __( '(OR)', 'woocommerce-jos-autocoupon' ),
				)
			);
		}

		//Add tabs to the coupon option page
		public function admin_coupon_options_tabs( $tabs ) {

			$tabs['extended_features_checkout'] = array(
				'label'  => __( 'Checkout', 'woocommerce-jos-autocoupon' ),
				'target' => 'wjecf_coupondata_checkout',
				'class'  => 'wjecf_coupondata_checkout',
			);

			$tabs['extended_features_misc'] = array(
				'label'  => __( 'Miscellaneous', 'woocommerce-jos-autocoupon' ),
				'target' => 'wjecf_coupondata_misc',
				'class'  => 'wjecf_coupondata_misc',
			);

			return $tabs;
		}

		//Add panels to the coupon option page
		public function admin_coupon_options_panels() {
			global $thepostid, $post;
			$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
			?>
				<div id="wjecf_coupondata_checkout" class="panel woocommerce_options_panel">
					<?php
						do_action( 'wjecf_coupon_metabox_checkout', $thepostid, $post );
						do_action( 'wjecf_coupon_metabox_customer', $thepostid, $post );
						$this->admin_coupon_data_footer();
					?>
				</div>
				<div id="wjecf_coupondata_misc" class="panel woocommerce_options_panel">
					<?php
						//Allow other classes to inject options
						do_action( 'wjecf_woocommerce_coupon_options_extended_features', $thepostid, $post );
						do_action( 'wjecf_coupon_metabox_misc', $thepostid, $post );
						$this->admin_coupon_data_footer();
					?>
				</div>
			<?php
		}

		public function admin_coupon_data_footer() {
			$documentation_url = WJECF()->plugin_url( 'docs/index.html' );
			if ( ! WJECF()->is_pro() ) {
				$documentation_url = self::URL_DOCUMENTATION;

				echo '<h3>' . __( 'Do you like WooCommerce Extended Coupon Features?', 'woocommerce-jos-autocoupon' ) . '</h3>';
				echo '<p>' . esc_html( __( 'You will love the PRO version!', 'woocommerce-jos-autocoupon' ) ) . '</p>';
				echo '<a id="wjecf_pro_button" href="' . self::URL_PURCHASE_PRO . '" target="_blank" class="button button-primary">';
				echo esc_html( __( 'Get the PRO version', 'woocommerce-jos-autocoupon' ) );
				echo '</a><br></p>';
			}
			echo '<h3>' . __( 'Documentation', 'woocommerce-jos-autocoupon' ) . '</h3>';
			echo '<p><a href="' . $documentation_url . '" target="_blank">' .
			__( 'WooCommerce Extended Coupon Features Documentation', 'woocommerce-jos-autocoupon' ) . '</a></p>';
		}

		// //Tab 'extended features'
		//public function admin_coupon_metabox_products( $thepostid, $post ) {

		//since 2.5.0 moved to the 'Usage restriction' tab
		public function on_woocommerce_coupon_options_usage_restriction() {
			global $thepostid, $post;
			$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

			//See WooCommerce class-wc-meta-box-coupon-data.php function ouput

			echo '<h3>' . esc_html( __( 'Matching products', 'woocommerce-jos-autocoupon' ) ) . "</h3>\n";
			//=============================
			// AND instead of OR the products
			WJECF_Admin_Html::render_select_with_default(
				array(
					'id'            => '_wjecf_products_and',
					'label'         => __( 'Products Operator', 'woocommerce-jos-autocoupon' ),
					'options'       => array(
						'no'  => __( 'OR', 'woocommerce-jos-autocoupon' ),
						'yes' => __( 'AND', 'woocommerce-jos-autocoupon' ),
					),
					'default_value' => 'no',
					'class'         => 'wjecf-not-wide',
					/* translators: OLD TEXT:  'Check this box if ALL of the products (see tab \'usage restriction\') must be in the cart to use this coupon (instead of only one of the products).' */
					'description'   => __( 'Use AND if ALL of the products must be in the cart to use this coupon (instead of only one of the products).', 'woocommerce-jos-autocoupon' ),
					'desc_tip'      => true,
				)
			);

			//=============================
			// 2.2.3.1 AND instead of OR the categories
			WJECF_Admin_Html::render_select_with_default(
				array(
					'id'            => '_wjecf_categories_and',
					'label'         => __( 'Categories Operator', 'woocommerce-jos-autocoupon' ),
					'options'       => array(
						'no'  => __( 'OR', 'woocommerce-jos-autocoupon' ),
						'yes' => __( 'AND', 'woocommerce-jos-autocoupon' ),
					),
					'default_value' => 'no',
					'class'         => 'wjecf-not-wide',
					/* translators: OLD TEXT:  'Check this box if products from ALL of the categories (see tab \'usage restriction\') must be in the cart to use this coupon (instead of only one from one of the categories).' */
					'description'   => __( 'Use AND if products from ALL of the categories must be in the cart to use this coupon (instead of only one from one of the categories).', 'woocommerce-jos-autocoupon' ),
					'desc_tip'      => true,
				)
			);

			// Minimum quantity of matching products (product/category)
			woocommerce_wp_text_input(
				array(
					'id'          => '_wjecf_min_matching_product_qty',
					'label'       => __( 'Minimum quantity of matching products', 'woocommerce-jos-autocoupon' ),
					'placeholder' => __( 'No minimum', 'woocommerce' ),
					'description' => __( 'Minimum quantity of the products that match the given product or category restrictions (see tab \'usage restriction\'). If no product or category restrictions are specified, the total number of products is used.', 'woocommerce-jos-autocoupon' ),
					'data_type'   => 'decimal',
					'desc_tip'    => true,
				)
			);

			// Maximum quantity of matching products (product/category)
			woocommerce_wp_text_input(
				array(
					'id'          => '_wjecf_max_matching_product_qty',
					'label'       => __( 'Maximum quantity of matching products', 'woocommerce-jos-autocoupon' ),
					'placeholder' => __( 'No maximum', 'woocommerce' ),
					'description' => __( 'Maximum quantity of the products that match the given product or category restrictions (see tab \'usage restriction\'). If no product or category restrictions are specified, the total number of products is used.', 'woocommerce-jos-autocoupon' ),
					'data_type'   => 'decimal',
					'desc_tip'    => true,
				)
			);

			// Minimum subtotal of matching products (product/category)
			woocommerce_wp_text_input(
				array(
					'id'          => '_wjecf_min_matching_product_subtotal',
					'label'       => __( 'Minimum subtotal of matching products', 'woocommerce-jos-autocoupon' ),
					'placeholder' => __( 'No minimum', 'woocommerce' ),
					'description' => __( 'Minimum price subtotal of the products that match the given product or category restrictions (see tab \'usage restriction\').', 'woocommerce-jos-autocoupon' ),
					'data_type'   => 'price',
					'desc_tip'    => true,
				)
			);

			// Maximum subtotal of matching products (product/category)
			woocommerce_wp_text_input(
				array(
					'id'          => '_wjecf_max_matching_product_subtotal',
					'label'       => __( 'Maximum subtotal of matching products', 'woocommerce-jos-autocoupon' ),
					'placeholder' => __( 'No maximum', 'woocommerce' ),
					'description' => __( 'Maximum price subtotal of the products that match the given product or category restrictions (see tab \'usage restriction\').', 'woocommerce-jos-autocoupon' ),
					'data_type'   => 'price',
					'desc_tip'    => true,
				)
			);
		}

		public function admin_coupon_metabox_checkout( $thepostid, $post ) {

			echo '<h3>' . esc_html( __( 'Checkout', 'woocommerce-jos-autocoupon' ) ) . "</h3>\n";

			$this->render_admin_shipping_methods( $thepostid );

			//=============================
			// Payment methods
			?>
			<p class="form-field"><label for="wjecf_payment_methods"><?php _e( 'Payment methods', 'woocommerce-jos-autocoupon' ); ?></label>
			<select id="wjecf_payment_methods" name="_wjecf_payment_methods[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e( 'Any payment method', 'woocommerce-jos-autocoupon' ); ?>">
				<?php
					$coupon_payment_method_ids = WJECF()->get_coupon_payment_method_ids( $thepostid );
					//DONT USE WC()->payment_gateways->available_payment_gateways() AS IT CAN CRASH IN UNKNOWN OCCASIONS
					$payment_methods = WC()->payment_gateways->payment_gateways();
				if ( $payment_methods ) {
					foreach ( $payment_methods as $payment_method ) {
						if ( 'yes' === $payment_method->enabled ) {
							echo '<option value="' . esc_attr( $payment_method->id ) . '"' . selected( in_array( $payment_method->id, $coupon_payment_method_ids ), true, false ) . '>' . esc_html( $payment_method->title ) . '</option>';
						}
					}
				}
				?>
			</select><?php echo WJECF_Admin_Html::wc_help_tip( __( 'One of these payment methods must be selected in order for this coupon to be valid.', 'woocommerce-jos-autocoupon' ) ); ?>
			</p>
			<?php
		}

	/**
	 * Render the 'Shipping zones, instances and methods'-input
	 *
	 * @param int $postid
	 * @return void
	 */
	private function render_admin_shipping_methods( $postid ) {

		$optgroups = []; // [ 'Group title' => [ 'id' => 'Title', ], ]

		$zones = WC_Shipping_Zones::get_zones();
		if ( ! empty( $zones ) ) {
			$options = []; // id => title
			foreach( $zones as $zone_id => $zone ) {
				$options[ 'zone:' . $zone_id ] = $zone['zone_name'];
				foreach( $zone['shipping_methods'] as $method ) {
					$options[ 'instance:' . $method->get_instance_id() ] = '&nbsp; &nbsp; ' . $zone['zone_name'] . " - " . $method->get_title();
				}
			}
			$optgroups[ 'zones' ] = $options;
		}

		$shipping_methods = WC()->shipping->load_shipping_methods();
		if ( ! empty( $shipping_methods ) ) {
			$options = []; // id => title
			foreach ( $shipping_methods as $shipping_method ) {
				$options[ 'method:' . $shipping_method->id ] = $shipping_method->method_title;
			}
			$optgroups[ 'methods' ] = $options;
		}

		?>
		<p class="form-field">
		<label for="wjecf_shipping_restrictions"><?php esc_html_e( 'Shipping methods', 'woocommerce-jos-autocoupon' ); ?></label>
		<select id="wjecf_shipping_restrictions" name="_wjecf_shipping_restrictions[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php
			esc_attr_e( 'Any shipping zone or method', 'woocommerce-jos-autocoupon' );
		?>">
		<?php
			$selected = WJECF()->get_coupon_shipping_restrictions( $postid );
			$this->render_shipping_options( $optgroups, $selected );
		?>
		</select>
		<?php echo WJECF_Admin_Html::wc_help_tip( __( 'The coupon only applies to the selected zones or shipping methods.', 'woocommerce-jos-autocoupon' ) ); ?>
		</p>

		<p class="form-field">
		<label for="wjecf_excluded_shipping_restrictions"><?php esc_html_e( 'Excluded shipping methods', 'woocommerce-jos-autocoupon' ); ?></label>
		<select id="wjecf_excluded_shipping_restrictions" name="_wjecf_excluded_shipping_restrictions[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php
			esc_attr_e( 'Any shipping zone or method', 'woocommerce-jos-autocoupon' );
		?>">
		<?php
			$selected = WJECF()->get_coupon_excluded_shipping_restrictions( $postid );
			$this->render_shipping_options( $optgroups, $selected, 'disabled' );
		?>
		</select>
		<?php echo WJECF_Admin_Html::wc_help_tip( __( 'The coupon does not apply to the selected zones or shipping methods.', 'woocommerce-jos-autocoupon' ) ); ?>
		</p>
		<?php
	}

	private function render_shipping_options( $optgroups, $selected = [] ) {
		$titles = [
			'zones'   => __( 'Zones and shipping methods', 'woocommerce-jos-autocoupon' ),
			'methods' => __( 'Global shipping methods', 'woocommerce-jos-autocoupon' ),
		];
		foreach( $optgroups as $id => $options ) {
			$title = $titles[ $id ];

			$disabled = $id === 'zones' &&  ! WJECF()->is_pro();
			if ( $disabled ) {
				$title = sprintf( __( '%s (only available in PRO version)', 'woocommerce-jos-autocoupon' ), $title );
			}

			echo '<optgroup label="' . esc_attr( $title ) . '">';
			foreach( $options as $id => $title ) {
				echo '<option value="' . esc_attr( $id ) . '"'
				. selected( in_array( $id, $selected ), true, false )
				. ( $disabled ? ' disabled' : '' )
				. '>' . esc_html( $title )
				. '</option>';
			}
			echo '</optgroup>';
		}
	}

		public function admin_coupon_metabox_customer( $thepostid, $post ) {

			//=============================
			//Title: "CUSTOMER RESTRICTIONS"
			echo '<h3>' . esc_html( __( 'Customer restrictions', 'woocommerce-jos-autocoupon' ) ) . "</h3>\n";
			echo "<p><span class='description'>" . __( 'If both a customer and a role restriction are supplied, matching either one of them will suffice.', 'woocommerce-jos-autocoupon' ) . "</span></p>\n";

			//=============================
			// User ids
			?>
			<p class="form-field"><label><?php _e( 'Allowed Customers', 'woocommerce-jos-autocoupon' ); ?></label>
			<?php
				$coupon_customer_ids = WJECF()->get_coupon_customer_ids( $thepostid );
				WJECF_Admin_Html::render_admin_customer_selector( 'wjecf_customer_ids', '_wjecf_customer_ids', $coupon_customer_ids );
				echo WJECF_Admin_Html::wc_help_tip( __( 'Only these customers may use this coupon.', 'woocommerce-jos-autocoupon' ) );
			?>
			</p>
			<?php

			//=============================
			// User roles
			?>
			<p class="form-field"><label for="wjecf_customer_roles"><?php _e( 'Allowed User Roles', 'woocommerce-jos-autocoupon' ); ?></label>
			<select id="wjecf_customer_roles" name="_wjecf_customer_roles[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e( 'Any role', 'woocommerce-jos-autocoupon' ); ?>">
				<?php
					$coupon_customer_roles = WJECF()->get_coupon_customer_roles( $thepostid );

					$available_customer_roles = array_reverse( get_editable_roles() );
				foreach ( $available_customer_roles as $role_id => $role ) {
					$role_name = translate_user_role( $role['name'] );

					echo '<option value="' . esc_attr( $role_id ) . '"'
					. selected( in_array( $role_id, $coupon_customer_roles ), true, false ) . '>'
					. esc_html( $role_name ) . '</option>';
				}
				?>
			</select>
				<?php echo WJECF_Admin_Html::wc_help_tip( __( 'Only these User Roles may use this coupon.', 'woocommerce-jos-autocoupon' ) ); ?>
			</p>
			<?php

			//=============================
			// Excluded user roles
			?>
			<p class="form-field"><label for="wjecf_excluded_customer_roles"><?php _e( 'Disallowed User Roles', 'woocommerce-jos-autocoupon' ); ?></label>
			<select id="wjecf_customer_roles" name="_wjecf_excluded_customer_roles[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e( 'Any role', 'woocommerce-jos-autocoupon' ); ?>">
				<?php
					$coupon_excluded_customer_roles = WJECF()->get_coupon_excluded_customer_roles( $thepostid );

				foreach ( $available_customer_roles as $role_id => $role ) {
					$role_name = translate_user_role( $role['name'] );

					echo '<option value="' . esc_attr( $role_id ) . '"'
					. selected( in_array( $role_id, $coupon_excluded_customer_roles ), true, false ) . '>'
					. esc_html( $role_name ) . '</option>';
				}
				?>
			</select>
				<?php echo WJECF_Admin_Html::wc_help_tip( __( 'These User Roles will be specifically excluded from using this coupon.', 'woocommerce-jos-autocoupon' ) ); ?>
			</p>
			<?php
		}

		/**
		 * Returns an array with the meta_keys for this plugin and the sanitation to apply.
		 * Instead of a sanitation a callback can be supplied; which must return the meta value to save to the database
		 *
		 * e.g. [
		 *  '_wjecf_some_comma_separated_ints' => 'int,',
		 *  '_wjecf_some_callback' => [ 'callback' => [ callback ] ],
		 * ]
		 *
		 * @param null|WC_Coupon $coupon Note: Can be null
		 * @return array The fields for this plugin
		 */
		public function admin_coupon_meta_fields( $coupon = null ) {
			$fields = array(
				'_wjecf_min_matching_product_qty'      => 'int',
				'_wjecf_max_matching_product_qty'      => 'int',
				'_wjecf_min_matching_product_subtotal' => 'decimal',
				'_wjecf_max_matching_product_subtotal' => 'decimal',
				'_wjecf_products_and'                  => 'yesno',
				'_wjecf_categories_and'                => 'yesno',
				'_wjecf_shipping_methods'              => 'clean',
				'_wjecf_payment_methods'               => 'clean',
				'_wjecf_customer_ids'                  => 'int,',
				'_wjecf_customer_roles'                => 'clean',
				'_wjecf_excluded_customer_roles'       => 'clean',
				//3.2.0
				'_wjecf_shipping_restrictions'          => 'clean',
				'_wjecf_excluded_shipping_restrictions' => 'clean',
			);

			//Espagueti
			if ( WJECF()->is_pro() ) {
				$fields = array_merge( $fields, WJECF()->admin_coupon_meta_fields( $coupon ) );
			}
			return $fields;
		}

		/**
		 * Get an array with all the metafields for all the WJECF plugins
		 *
		 * @see Abstract_WJECF_Plugin::admin_coupon_meta_fields()
		 *
		 * @param type $coupon
		 * @return type
		 */
		function get_all_coupon_meta_fields( $coupon ) {
			//Collect the meta_fields of all the WJECF plugins
			$fields = array();
			foreach ( WJECF()->get_plugins() as $name => $plugin ) {
				if ( $plugin->plugin_is_enabled() ) {
					$fields = array_merge( $fields, $plugin->admin_coupon_meta_fields( $coupon ) );
				}
			}
			return $fields;
		}

		function process_shop_coupon_meta( $post_id, $post ) {
			$coupon    = WJECF_WC()->get_coupon( $post );
			$sanitizer = WJECF()->sanitizer();

			$fields = $this->get_all_coupon_meta_fields( $coupon );
			foreach ( $fields as $key => $rule ) {
				//If array contains [ 'callback' => callback, 'args' => args[] ]
				//Then that callback will be called with the given args (optional)

				if ( is_array( $rule ) && isset( $rule['callback'] ) && is_callable( $rule['callback'] ) ) {
					$args = array( 'key' => $key );
					if ( isset( $rule['args'] ) ) {
						$args = array_merge( $args, $rule['args'] );
					}

					$value = call_user_func( $rule['callback'], $args );
				} else {
					$value = $sanitizer->sanitize( isset( $_POST[ $key ] ) ? $_POST[ $key ] : null, $rule );
				}
				if ( '' === $value ) {
					$value = null; //Don't save empty entries
				}

				$coupon->update_meta_data( $key, $value ); //Always single

				//error_log(sprintf("%s => %s", $key, is_array($value) ? 'array' : $value));
			}

			$coupon->save();
		}



		/**
		 * 2.3.6
		 * Add inline style (css) to the admin page. Must be called BEFORE admin_head !
		 * @param string $css
		 * @return void
		 */
		public function add_inline_style( $css ) {
			$this->admin_css .= $css;
		}


		/**
		 *
		 * 2.3.4
		 * Parse an array or comma separated string; make sure they are valid ints and return as comma separated string
		 * @deprecated 2.5.1 Use WJECF()->sanitizer->sanitize( ..., 'int[]' ) instead
		 * @param array|string $int_array
		 * @return string comma separated int array
		 */
		public function comma_separated_int_array( $int_array ) {
			_deprecated_function( 'comma_separated_int_array', '2.5.1', 'WJECF()->sanitizer->sanitize()' );
			return WJECF()->sanitizer->sanitize( $int_array, 'int[]' );
		}

		/**
		 * Add purchase PRO-link to plugin page
		 */
		function wjecf_plugin_meta( $links, $file ) {
			if ( strpos( $file, 'woocommerce-jos-autocoupon.php' ) !== false && ! WJECF()->is_pro() ) {
				$links = array_merge( $links, array( '<a href="' . self::URL_PURCHASE_PRO . '" title="Get the PRO version" target="_blank">Get PRO</a>' ) );
			}
			return $links;
		}
	}

}
