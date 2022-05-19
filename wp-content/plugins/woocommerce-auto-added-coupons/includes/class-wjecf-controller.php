<?php

defined( 'ABSPATH' ) or die();

/**
 * The main controller for WooCommerce Extended Coupon Features
 */
class WJECF_Controller {

	// Coupon message codes
	//NOTE: I use prefix 79 for this plugin; there's no guarantee that other plugins don't use the same values!
	const E_WC_COUPON_MIN_MATCHING_SUBTOTAL_NOT_MET = 79100;
	const E_WC_COUPON_MAX_MATCHING_SUBTOTAL_NOT_MET = 79101;
	const E_WC_COUPON_MIN_MATCHING_QUANTITY_NOT_MET = 79102;
	const E_WC_COUPON_MAX_MATCHING_QUANTITY_NOT_MET = 79103;
	const E_WC_COUPON_SHIPPING_METHOD_NOT_MET       = 79104;
	const E_WC_COUPON_PAYMENT_METHOD_NOT_MET        = 79105;
	const E_WC_COUPON_NOT_FOR_THIS_USER             = 79106;
	const E_WC_COUPON_FIRST_PURCHASE_ONLY           = 79107;
	const E_WC_COUPON_SHIPPING_ZONE_NOT_MET         = 79108;

	private $options      = null;
	private $_user_emails = null;
	private $flexible_shipping_rates = null;

	/**
	 * Singleton Instance
	 *
	 * @static
	 * @return Singleton Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = class_exists( 'WJECF_Pro_Controller' ) ? new WJECF_Pro_Controller() : new WJECF_Controller();
		}
		return self::$_instance;
	}
	protected static $_instance = null;


	public function __construct() {
		$this->options = new WJECF_Options(
			'wjecf_options',
			array(
				'db_version'              => 0, // integer
				'debug_mode'              => false, // true or false
				'disabled_plugins'        => array(), // e.g. [ 'WJECF_AutoCoupon' ]
				'autocoupon_allow_remove' => false,
			)
		);
	}

	public function start() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$this->init_plugins();
		$this->init_hooks();
	}

	public function init_hooks() {
		//Frontend hooks

		//assert_coupon_is_valid (which raises exception on invalid coupon) can only be used on WC 2.3.0 and up
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'assert_coupon_is_valid' ), 10, 3 ); //Since WC3.2 WC_Discounts is passed as a 3rd argument

		//Last check for coupons with restricted_emails (moved from WJECF_AutoCoupon since 2.5.6)
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'fetch_billing_email' ), 10 ); // AJAX One page checkout
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'fetch_billing_email' ), 10 ); // Checkout posted

		//Overwrite coupon info message
		add_filter( 'woocommerce_coupon_message', array( $this, 'filter_woocommerce_coupon_message' ), 10, 3 );
	}

	protected function init_plugins() {
		/**
		 * Fires before the WJECF plugins are initialised.
		 *
		 * Perfect hook for themes or plugins to load custom WJECF plugins.
		 *
		 * @since 2.3.7
		 **/
		do_action( 'wjecf_init_plugins' );

		//Start the plugins
		foreach ( WJECF()->get_plugins() as $name => $plugin ) {
			if ( $plugin->plugin_is_enabled() ) {
				try {
					$plugin->assert_dependencies();
				} catch ( Exception $ex ) {
					if ( is_admin() && WJECF_Admin() ) {
						/* translators: 1: plugin-name 2: exception message */
						$msg = sprintf( __( 'Failed loading plugin %1$s: %2$s.', 'woocommerce-jos-autocoupon' ), $name, $ex->getMessage() );
						WJECF_Admin()->enqueue_notice( $msg );
					}
					continue;
				}

				$plugin->init_hook();
				if ( is_admin() ) {
					$plugin->init_admin_hook();
				}
			}
		}
	}

	protected $plugins = array();

	/**
	 * Load a WJECF Plugin (class name)
	 * @param string $name The class name of the plugin
	 * @return bool True if succeeded, otherwise false
	 */
	public function add_plugin( $instance ) {
		if ( is_string( $instance ) ) {
			if ( ! class_exists( $instance ) ) {
				$this->log( 'warning', 'Unknown plugin: ' . $instance );
				return false; //Not found
			}
			$instance = new $instance();
		}

		$name = $instance->get_plugin_name();

		if ( isset( $this->plugins[ $name ] ) ) {
			$this->log( 'warning', 'Plugin already loaded: ' . $name );
			return false; //Already loaded
		}

		if ( ! ( $instance instanceof Abstract_WJECF_Plugin ) ) {
			$this->log( 'warning', 'Plugin must be an instance of Abstract_WJECF_Plugin: ' . $name );
			return false; //Invalid
		}

		$this->plugins[ $name ] = $instance;
		$this->log( 'debug', 'Loaded plugin: ' . $name );

		return true;
	}

	/**
	 * Get an array of all the plugins
	 *
	 * @return array [ $key => $plugin ]
	 */
	public function get_plugins() {
		return $this->plugins;
	}

	/**
	 * Retrieves the WJECF Plugin
	 * @param string $name Name of the plugin as yielded by $plugin->get_plugin_name() e.g. 'admin-settings'
	 * @return object|bool The plugin if found, otherwise returns false
	 */
	public function get_plugin( $name ) {
		if ( isset( $this->plugins[ $name ] ) ) {
			return $this->plugins[ $name ];
		}

		//Legacy support: E.g. allow 'WJECF_Autocoupon' instead of 'autocoupon'
		$adj_name = Abstract_WJECF_Plugin::sanitize_plugin_name( $name );
		if ( isset( $this->plugins[ $adj_name ] ) ) {
			$this->log( 'warning', sprintf( 'Plugin name %s has been changed to: %s', $name, $adj_name ) );
			return $this->plugins[ $adj_name ];
		}
		return false;
	}

	/* OPTIONS */

	public function get_options() {
		return $this->options->get();
	}

	public function get_option( $key, $default = null ) {
		return $this->options->get( $key, $default );
	}

	public function set_option( $key, $value ) {
		$this->options->set( $key, $value );
	}

	public function save_options() {
		if ( ! is_admin() ) {
			$this->log( 'error', 'WJECF Options must only be saved from admin.' );
			return;
		}
		$this->options->save();
	}

	public function sanitizer() {
		return WJECF_Sanitizer::instance();
	}

	/**
	 * Same as WordPress add_action(), but prevents the callback to be recursively called
	 *
	 * @param string $tag
	 * @param callable $function_to_add
	 * @param int $priority
	 * @param int $accepted_args
	 */
	public function safe_add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		$_recursion_limit = 5;
		WJECF_Action_Or_Filter::action( $tag, $function_to_add, $priority, $accepted_args, $_recursion_limit );
	}

	/* FRONTEND HOOKS */

	/**
	 * Extra validation rules for coupons.
	 * @param bool $valid
	 * @param WC_Coupon $coupon
	 * @param WC_Discounts $discounts
	 * @return bool True if valid; False if not valid.
	 */
	public function coupon_is_valid( $valid, $coupon, $wc_discounts = null ) {
		try {
			return $this->assert_coupon_is_valid( $valid, $coupon, $wc_discounts );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Extra validation rules for coupons. Throw an exception when not valid.
	 * @param bool $valid
	 * @param WC_Coupon $coupon
	 * @param WC_Discounts $discounts
	 * @return bool True if valid; False if already invalid on function call. In any other case an Exception will be thrown.
	 */
	public function assert_coupon_is_valid( $valid, $coupon, $wc_discounts = null ) {

		//Not valid? Then it will never validate, so get out of here
		if ( ! $valid ) {
			return false;
		}

		if ( is_null( $wc_discounts ) ) {
			$wc_discounts = new WJECF_WC_Discounts( WC()->cart );
		}

		$coupon_code = $coupon->get_code();

		//Reset multiplier to initial value of null. The validate_* routines will call limit_multiplier() to set the multiplier value
		$this->coupon_multiplier_values[ $coupon_code ] = null;
		try {
			$this->validate_products_and( $coupon, $wc_discounts );
			$this->validate_categories_and( $coupon, $wc_discounts );
			$this->validate_min_max_quantity( $coupon, $wc_discounts );
			$this->validate_min_max_subtotal( $coupon, $wc_discounts );
			$this->validate_shipping_method( $coupon );
			$this->validate_excluded_shipping_method( $coupon );
			$this->validate_payment_method( $coupon );
			$this->validate_customer( $coupon, $wc_discounts );

			if ( $this->is_pro() ) {
				$this->validate_pro( $coupon, $wc_discounts );
			}

			//We use our own filter (instead of woocommerce_coupon_is_valid) for easier compatibility management
			//e.g. WC prior to 2.3.0 can't handle Exceptions; while 2.3.0 and above require exceptions
			do_action( 'wjecf_assert_coupon_is_valid', $coupon, $wc_discounts );
		} catch ( Exception $exception ) {
			//Invalid coupon? Multiplier 0
			$this->coupon_multiplier_values[ $coupon_code ] = 0;
			throw $exception;
		}

		if ( (float) $coupon->get_minimum_amount() ) {
			$this->limit_multiplier( $coupon, floor( $this->get_subtotal( $wc_discounts ) / $coupon->get_minimum_amount() ) );
		}

		/**
		 * Filters the (product-)multiplier value of the coupon.
		 *
		 * @since 2.6.3
		 *
		 * @param float|null   $multiplier       Current multiplier value (or null if no multiplier yet known)
		 * @param WC_Coupon    $coupon           The coupon
		 * @param WC_Discounts $wc_discounts     Discounts class containing the cart items (NOTE: Will be a WJECF_WC_Discounts for WC < 3.2.0)
		 */
		$this->coupon_multiplier_values[ $coupon_code ] = apply_filters( 'wjecf_coupon_multiplier_value', $this->coupon_multiplier_values[ $coupon_code ], $coupon, $wc_discounts );

		return true;
	}

	/**
	 * Validate 'products AND'. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @param WC_Discounts $wc_discounts
	 * @return void
	 */
	private function validate_products_and( $coupon, $wc_discounts ) {
		//Test if ALL products are in the cart (if AND-operator selected instead of the default OR)
		$products_and = $coupon->get_meta( '_wjecf_products_and' ) == 'yes';
		if ( ! $products_and || sizeof( $coupon->get_product_ids() ) <= 1 ) { // We use > 1, because if size == 1, 'AND' makes no difference
			return;
		}

		//Get array of all cart product and variation ids
		$item_ids = array();
		foreach ( $wc_discounts->get_items() as $item_key => $item ) {
			if ( ! empty( $item->product ) ) {
				$item_ids[] = $item->product->get_id();
				if ( $item->product->is_type( 'variation' ) ) {
					$item_ids[] = $item->product->get_parent_id();
				}
			}
		}
		//Filter used by WJECF_WPML hook
		$item_ids = apply_filters( 'wjecf_get_product_ids', array_unique( $item_ids ) );

		//check if every single product is in the cart
		foreach ( apply_filters( 'wjecf_get_product_ids', $coupon->get_product_ids() ) as $product_id ) {
			if ( ! in_array( $product_id, $item_ids ) ) {
				throw new Exception( WC_Coupon::E_WC_COUPON_NOT_APPLICABLE );
			}
		}
	}

	/**
	 * Validate 'categories AND'. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @param WC_Discounts $wc_discounts
	 * @return void
	 */
	private function validate_categories_and( $coupon, $wc_discounts ) {
		//Test if products form ALL categories are in the cart (if AND-operator selected instead of the default OR)
		$categories_and = $coupon->get_meta( '_wjecf_categories_and' ) == 'yes';
		if ( ! $categories_and || sizeof( $coupon->get_product_categories() ) <= 1 ) { // We use > 1, because if size == 1, 'AND' makes no difference
			return;
		}

		//Get array of all cart product and variation ids
		$product_cats = array();

		foreach ( $wc_discounts->get_items() as $item_key => $item ) {
			if ( ! $item->product ) {
				continue;
			}

			$product_id = $item->product->get_id();
			if ( 'product_variation' == get_post_type( $product_id ) ) {
				$product_id = $item->product->get_parent_id();
			}
			$product_cats = array_merge( $product_cats, wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) ) );
		}

		//Filter used by WJECF_WPML hook
		$product_cats = apply_filters( 'wjecf_get_product_cat_ids', $product_cats );
		//check if every single category is in the cart
		foreach ( apply_filters( 'wjecf_get_product_cat_ids', $coupon->get_product_categories() ) as $cat_id ) {
			if ( ! in_array( $cat_id, $product_cats ) ) {
				$this->log( 'debug', $cat_id . ' is not in ' . implode( ',', $product_cats ) );
				throw new Exception( WC_Coupon::E_WC_COUPON_NOT_APPLICABLE );
			}
		}
	}

	/**
	 * Validate min/max quantity of matching products. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @param WC_Discounts $wc_discounts
	 * @return void
	 */
	private function validate_min_max_quantity( $coupon, $wc_discounts ) {
		//Test min/max quantity of matching products
		//
		//For all items in the cart:
		//  If coupon contains both a product AND category inclusion filter: the item is counted if it matches either one of them
		//  If coupon contains either a product OR category exclusion filter: the item will NOT be counted if it matches either one of them
		//  If sale items are excluded by the coupon: the item will NOT be counted if it is a sale item
		//  If no filter exist, all items will be counted

		//Validate quantity
		$min_matching_product_qty = intval( $coupon->get_meta( '_wjecf_min_matching_product_qty' ) );
		$max_matching_product_qty = intval( $coupon->get_meta( '_wjecf_max_matching_product_qty' ) );
		if ( $min_matching_product_qty <= 0 && 0 == $max_matching_product_qty ) {
			return;
		}

		//Count the products
		$qty = $this->get_quantity_of_matching_products( $coupon, $wc_discounts );
		//$this->log( 'debug', 'Quantity of matching products: ' . $qty );
		if ( $min_matching_product_qty > 0 && $qty < $min_matching_product_qty ) {
			throw new Exception(
				/* translators: 1: minimum quantity */
				sprintf( __( 'The minimum quantity of matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), $min_matching_product_qty ),
				self::E_WC_COUPON_MIN_MATCHING_QUANTITY_NOT_MET
			);
		}
		if ( $max_matching_product_qty > 0 && $qty > $max_matching_product_qty ) {
			throw new Exception(
				/* translators: 1: maximum quantity */
				sprintf( __( 'The maximum quantity of matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), $max_matching_product_qty ),
				self::E_WC_COUPON_MAX_MATCHING_QUANTITY_NOT_MET
			);
		}

		if ( $min_matching_product_qty > 0 ) {
			$this->limit_multiplier( $coupon, floor( $qty / $min_matching_product_qty ) );
		}
	}

	/**
	 * Validate min/max subtotal of matching products. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @param WC_Discounts $wc_discounts
	 * @return void
	 */
	private function validate_min_max_subtotal( $coupon, $wc_discounts ) {
		//Validate subtotal (2.2.2)
		$min_matching_product_subtotal = floatval( $coupon->get_meta( '_wjecf_min_matching_product_subtotal' ) );
		$max_matching_product_subtotal = floatval( $coupon->get_meta( '_wjecf_max_matching_product_subtotal' ) );

		if ( $min_matching_product_subtotal <= 0.0 && 0.0 === $max_matching_product_subtotal ) {
			return;
		}

		$subtotal = $this->get_subtotal_of_matching_products( $coupon, $wc_discounts );

		if ( $min_matching_product_subtotal > 0.0 ) {
			if ( $subtotal < $min_matching_product_subtotal ) {
				throw new Exception(
					/* translators: 1: minimum subtotal */
					sprintf( __( 'The minimum subtotal of the matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), wc_price( $min_matching_product_subtotal ) ),
					self::E_WC_COUPON_MIN_MATCHING_SUBTOTAL_NOT_MET
				);
			}
			$this->limit_multiplier( $coupon, floor( $subtotal / $min_matching_product_subtotal ) );
		}
		if ( $max_matching_product_subtotal > 0.0 && $subtotal > $max_matching_product_subtotal ) {
			throw new Exception(
				/* translators: 1: maximum subtotal */
				sprintf( __( 'The maximum subtotal of the matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), wc_price( $max_matching_product_subtotal ) ),
				self::E_WC_COUPON_MAX_MATCHING_SUBTOTAL_NOT_MET
			);
		}
	}

	/**
	 * Validate shipping method. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @return void
	 */
	private function validate_shipping_method( $coupon ) {
		$restrictions = $this->get_coupon_shipping_restrictions( $coupon );
		if ( empty( $restrictions ) ) {
			return;
		}

		$grouped = $this->group_shipping_restrictions( $restrictions );

		foreach( $grouped as $group => $ids ) {
			switch ( $group ) {
				case 'zone':
					if ( $this->matches_shipping_zone( $ids ) ) return;
					break;
				case 'method':
					if ( $this->matches_shipping_method( $ids ) ) return;
					break;
				case 'instance':
					if ( $this->matches_shipping_instance( $ids ) ) return;
					break;
				default:
					$this->log( 'warning', "Unknown shipping restriction type '{$group}'" );
			}
		}

		if ( array_key_exists( 'zone', $grouped ) && ! array_intersect( array_keys( $grouped ), [ 'method', 'instance' ] ) ) {
			throw new Exception(
				__( 'The coupon is not valid for your region.', 'woocommerce-jos-autocoupon' ),
				self::E_WC_COUPON_SHIPPING_ZONE_NOT_MET
			);
		}

		throw new Exception(
			__( 'The coupon is not valid for the currently selected shipping method.', 'woocommerce-jos-autocoupon' ),
			self::E_WC_COUPON_SHIPPING_METHOD_NOT_MET
		);

	}

	/**
	 * Validate shipping method. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @return void
	 */
	private function validate_excluded_shipping_method( $coupon ) {
		$restrictions = $this->get_coupon_excluded_shipping_restrictions( $coupon );
		if ( empty( $restrictions ) ) {
			return;
		}

		$grouped = $this->group_shipping_restrictions( $restrictions );

		foreach( $grouped as $group => $ids ) {
			switch ( $group ) {
				case 'zone':
					if ( $this->matches_shipping_zone( $ids ) ) {
						throw new Exception(
							__( 'The coupon is not valid for your region.', 'woocommerce-jos-autocoupon' ),
							self::E_WC_COUPON_SHIPPING_ZONE_NOT_MET
						);
					}
					break;
				case 'method':
					if ( $this->matches_shipping_method( $ids ) ) {
						throw new Exception(
							__( 'The coupon is not valid for the currently selected shipping method.', 'woocommerce-jos-autocoupon' ),
							self::E_WC_COUPON_SHIPPING_METHOD_NOT_MET
						);
					}
					break;
				case 'instance':
					if ( $this->matches_shipping_instance( $ids ) ) {
						throw new Exception(
							__( 'The coupon is not valid for the currently selected shipping method.', 'woocommerce-jos-autocoupon' ),
							self::E_WC_COUPON_SHIPPING_METHOD_NOT_MET
						);
					}
					break;
				default:
					$this->log( 'warning', "Unknown shipping restriction type '{$group}'" );
			}
		}
	}

	private function matches_shipping_method( $coupon_shipping_methods ) {
		return ! empty( array_intersect( wc_get_chosen_shipping_method_ids(), $coupon_shipping_methods ) );
	}

	private function matches_shipping_zone( $coupon_shipping_zones ) {
		$packages = WC()->cart->get_shipping_packages();
		foreach( $packages as $package ) {
			$zone_id = WC_Shipping_Zones::get_zone_matching_package( $package )->get_id();

			if ( in_array( $zone_id, $coupon_shipping_zones ) ) {
				return true;
			}
		}
		return false;
	}

	private function matches_shipping_instance( $coupon_shipping_instance_ids ) {
		$instances = WC()->session->get( 'chosen_shipping_methods', array() ); // e.g. [ 'local_pickup:1', 'flat_rate:2' ]
		if ( ! is_array( $instances ) ) {
			return false;
		}
		foreach( $instances as $instance ) {
			//"Flexible Shipping" by WP Desk compatibility
			if ( strpos( $instance, 'flexible_shipping_' ) === 0 ) {
				$instance = $this->get_flexible_shipping_instance( $instance );
			}

			//Examples where 11 is the instance id:
			//  Core shipping methods use format 'shiping_method:11'
			//  "Table Rate Shipping for WooCommerce" by Border Elements uses format 'betrs_shipping:11-1'
			//  "Table Rate Shipping" by WooCommerce uses format 'table_rate:11:4'.
			if ( ! preg_match( '/:(\\d+)/', $instance, $matches ) ) {
				continue;
			}
			$instance_id = $matches[1];
			if ( in_array( $instance_id, $coupon_shipping_instance_ids ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Flexible shipping plugin stores the shipping method as 'flexible_shipping_?_?'.
	 * By looking up the rates from ['flexible_shipping'] we can get the instance id.
	 *
	 * Returns 'flexible_shipping_?_?:?' if found (where the latter question mark is the instance id)
	 * Otherwise returns the original $instance_name
	 *
	 * @param string $instance_name
	 * @return string $instance_name with the instance id added
	 */
	private function get_flexible_shipping_instance( $instance_name ) {
		if ( $this->flexible_shipping_rates === null ) {
			$all_shipping_methods = WC()->shipping()->load_shipping_methods();
			if ( isset( $all_shipping_methods['flexible_shipping'] ) ) {
				$this->flexible_shipping_rates = $all_shipping_methods['flexible_shipping']->get_all_rates();
			} else {
				$this->flexible_shipping_rates = false;
			}
		}

		if ( isset( $this->flexible_shipping_rates[ $instance_name ]['woocommerce_method_instance_id'] ) ) {
			return $instance_name . ':' . $this->flexible_shipping_rates[ $instance_name ]['woocommerce_method_instance_id'];
		}
		return $instance_name;
	}

	/**
	 * Validate payment method. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @return void
	 */
	private function validate_payment_method( $coupon ) {
		$payment_method_ids = $this->get_coupon_payment_method_ids( $coupon );
		if ( empty( $payment_method_ids ) ) {
			return;
		}

		$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );
		if ( ! in_array( $chosen_payment_method, $payment_method_ids ) ) {
			throw new Exception(
				__( 'The coupon is not valid for the currently selected payment method.', 'woocommerce-jos-autocoupon' ),
				self::E_WC_COUPON_PAYMENT_METHOD_NOT_MET
			);
		}
	}

	/**
	 * Validate if the coupon applies to the customer. An Exception will be thrown if the coupon does not apply.
	 *
	 * @param WC_Coupon $coupon
	 * @return void
	 */
	private function validate_customer( $coupon, $wc_discounts ) {
		//NOTE: If both customer id and role restrictions are provided, the coupon matches if either the id or the role matches
		$coupon_customer_ids   = $this->get_coupon_customer_ids( $coupon );
		$coupon_customer_roles = $this->get_coupon_customer_roles( $coupon );
		if ( ! empty( $coupon_customer_ids ) || ! empty( $coupon_customer_roles ) ) {
			$user = WJECF_WC()->get_user( $wc_discounts );
			//If both fail we invalidate. Otherwise it's ok
			if ( ! $user || ( ! in_array( $user->ID, $coupon_customer_ids ) && ! array_intersect( $user->roles, $coupon_customer_roles ) ) ) {
				throw new Exception(
					/* translators: 1: coupon code */
					sprintf( __( 'Sorry, it seems the coupon "%s" is not yours.', 'woocommerce-jos-autocoupon' ), $coupon->get_code() ),
					self::E_WC_COUPON_NOT_FOR_THIS_USER
				);
			}
		}

		//============================
		//Test excluded user roles
		$coupon_excluded_customer_roles = $this->get_coupon_excluded_customer_roles( $coupon );
		if ( ! empty( $coupon_excluded_customer_roles ) ) {
			$user = WJECF_WC()->get_user( $wc_discounts );
			if ( $user && array_intersect( $user->roles, $coupon_excluded_customer_roles ) ) {
				throw new Exception(
					/* translators: 1: coupon code */
					sprintf( __( 'Sorry, it seems the coupon "%s" is not yours.', 'woocommerce-jos-autocoupon' ), $coupon->get_code() ),
					self::E_WC_COUPON_NOT_FOR_THIS_USER
				);
			}
		}
	}

	/**
	 * Limit the multiplier value for the coupon (i.e. if $multiplier_value < current multiplier value; overwrite current multiplier value)
	 *
	 * @param WC_Coupon $coupon
	 * @param int $value
	 * @return void
	 */
	private function limit_multiplier( $coupon, $multiplier_value ) {
		$coupon_code = $coupon->get_code();
		if ( isset( $this->coupon_multiplier_values[ $coupon_code ] ) ) {
			$this->coupon_multiplier_values[ $coupon_code ] = min( $this->coupon_multiplier_values[ $coupon_code ], $multiplier_value );
		} else {
			$this->coupon_multiplier_values[ $coupon_code ] = $multiplier_value;
		}
	}

	/**
	 * The amount of times the minimum spend / quantity / subtotal values are reached
	 * @return int 1 or more if coupon is valid, otherwise 0
	 */
	public function get_coupon_multiplier_value( $coupon ) {
		$coupon      = WJECF_WC()->get_coupon( $coupon );
		$coupon_code = $coupon->get_code();

		//If coupon validation was not executed, the value is unknown
		if ( ! array_key_exists( $coupon_code, $this->coupon_multiplier_values ) && ! $this->coupon_is_valid( true, $coupon ) ) {
			return 0;
			//Calling coupon_is_valid enforces $this->coupon_multiplier_values to be set; if the coupon is valid.
		}

		//null defaults to 1
		return is_null( $this->coupon_multiplier_values[ $coupon_code ] ) ? 1 : $this->coupon_multiplier_values[ $coupon_code ];
	}

	//Temporary storage
	private $coupon_multiplier_values = array();


	/**
	 * (API FUNCTION)
	 * The total amount of the products in the cart that match the coupon restrictions
	 * since 2.2.2-b3
	 */
	public function get_quantity_of_matching_products( $coupon, $wc_discounts = null ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$items  = WJECF_WC()->get_discount_items( $wc_discounts );

		$qty = 0;
		foreach ( $items as $item_key => $item ) {
			if ( $item->product && $this->coupon_is_valid_for_product( $coupon, $item->product, $item->object ) ) {
				$qty += $item->quantity;
			}
		}
		return $qty;
	}

	/**
	 * (API FUNCTION)
	 * The total value of the products in the cart that match the coupon restrictions
	 * since 2.2.2-b3
	 */
	public function get_subtotal_of_matching_products( $coupon, $wc_discounts = null ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$items  = WJECF_WC()->get_discount_items( $wc_discounts );

		$subtotal_precise = 0;
		foreach ( $items as $item_key => $item ) {
			if ( $item->product && $this->coupon_is_valid_for_product( $coupon, $item->product, $item->object ) ) {
				$subtotal_precise += $item->price;
			}
		}

		$subtotal = WJECF_WC()->wc_remove_number_precision( $subtotal_precise );
		return $subtotal;
	}

	/**
	 * The total value of the products in the cart
	 * since 2.2.2-b3
	 */
	public function get_subtotal( $wc_discounts = null ) {
		$items = WJECF_WC()->get_discount_items( $wc_discounts );

		$subtotal_precise = 0;
		foreach ( $items as $item_key => $item ) {
			if ( $item->product ) {
				$subtotal_precise += $item->price;
			}
		}

		$subtotal = WJECF_WC()->wc_remove_number_precision( $subtotal_precise );
		return $subtotal;
	}

	/**
	 * (API FUNCTION)
	 * Test if coupon is valid for the product
	 * (this function is used to count the quantity of matching products)
	 */
	public function coupon_is_valid_for_product( $coupon, $product, $values = array() ) {
		//Do not count the free products
		if ( isset( $values['_wjecf_free_product_coupon'] ) ) {
			return false;
		}

		//Get the original coupon, without values overwritten by WJECF
		$duplicate_coupon = $this->get_original_coupon( $coupon );

		//$coupon->is_valid_for_product() only works for fixed_product or percent_product discounts
		if ( ! $duplicate_coupon->is_type( WJECF_WC()->wc_get_product_coupon_types() ) ) {
			$duplicate_coupon->set_discount_type( 'fixed_product' );
		}

		$valid = $duplicate_coupon->is_valid_for_product( $product, $values );
		return $valid;
	}


	// =====================

	/**
	 * Get array of the selected shipping methods ids.
	 * @deprecated 3.2.0
	 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
	 * @return array Id's of the shipping methods or an empty array.
	 */
	public function get_coupon_shipping_method_ids( $coupon ) {
		//Get all the coupon_shipping_ids that start with 'method:' and truncate the 'method:'-part
		$restrictions = $this->get_coupon_shipping_restrictions();
		$grouped = $this->group_shipping_restrictions( $restrictions );
		return isset( $grouped['method'] ) ? $grouped['method'] : '';
	}

	/**
	 * Get array of the selected shipping zones, methods or instance ids.
	 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
	 * @return array Id's or an empty array. Id's are prefixed by 'zone:', 'instance:' or 'method:'
	 */
	public function get_coupon_shipping_restrictions( $coupon ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$value  = WJECF()->sanitizer()->sanitize( $coupon->get_meta( '_wjecf_shipping_restrictions' ), 'clean' );
		return is_array( $value ) ? $value : [];
	}

	/**
	 * Groups the restrictions by the part before the ':' of every restriction.
	 *
	 * @param string[] $restrictions E.g. [ 'zone:1', 'method:flat_rate', 'method:local_pickup' ]
	 * @return array Grouped array, e.g. [ 'zone' : ['1'], 'method' : ['flat_rate', 'local_pickup'] ]
	 */
	private function group_shipping_restrictions( $restrictions ) {
		$grouped = [];
		foreach( $restrictions as $restriction ) {
			$split = explode( ':', $restriction, 2 );
			if ( count( $split ) === 2 ) {
				$grouped[ $split[0] ][] = $split[1];
			}
		}
		return $grouped;
	}

	/**
	 * Get array of the excluded shipping zones, methods or instance ids.
	 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
	 * @return array Id's or an empty array. Id's are prefixed by 'zone:', 'instance:' or 'method:'
	 */
	public function get_coupon_excluded_shipping_restrictions( $coupon ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$value  = WJECF()->sanitizer()->sanitize( $coupon->get_meta( '_wjecf_excluded_shipping_restrictions' ), 'clean' );
		return is_array( $value ) ? $value : [];
	}

	/**
	 * Get array of the selected payment method ids.
	 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
	 * @return array  Id's of the payment methods or an empty array.
	 */
	public function get_coupon_payment_method_ids( $coupon ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$v      = $coupon->get_meta( '_wjecf_payment_methods' );
		return is_array( $v ) ? $v : array();
	}

	/**
	 * Get array of the selected customer ids.
	 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
	 * @return array  Id's of the customers (users) or an empty array.
	 */
	public function get_coupon_customer_ids( $coupon ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$v      = $coupon->get_meta( '_wjecf_customer_ids' );
		return WJECF()->sanitizer()->sanitize( $v, 'int[]' );
	}

	/**
	 * Get array of the selected customer role ids.
	 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
	 * @return array  Id's (string) of the customer roles or an empty array.
	 */
	public function get_coupon_customer_roles( $coupon ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$v      = $coupon->get_meta( '_wjecf_customer_roles' );
		return is_array( $v ) ? $v : array();
	}

	/**
	 * Get array of the excluded customer role ids.
	 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
	 * @return array  Id's (string) of the excluded customer roles or an empty array.
	 */
	public function get_coupon_excluded_customer_roles( $coupon ) {
		$coupon = WJECF_WC()->get_coupon( $coupon );
		$v      = $coupon->get_meta( '_wjecf_excluded_customer_roles' );
		return is_array( $v ) ? $v : array();
	}


	// ===========================================================================
	// User identification
	// ===========================================================================

	/**
	 * Get a list of the users' known email addresses
	 *
	 * NOTE: Also called in AutoCoupon
	 *
	 * @since 2.5.6 (Moved from WJECF_AutoCoupon)
	 * @return array The user's known email addresses
	 */
	public function get_user_emails() {
		if ( ! is_array( $this->_user_emails ) ) {
			$this->_user_emails = array();
			//Email of the logged in user
			if ( is_user_logged_in() ) {
				$current_user         = wp_get_current_user();
				$this->_user_emails[] = $current_user->user_email;
			}
		}
		$user_emails = $this->_user_emails;

		$billing_email = $this->get_session( 'billing_email', '' );
		if ( is_email( $billing_email ) ) {
			$user_emails[] = $billing_email;
		}

		$user_emails = array_map( 'strtolower', $user_emails );
		$user_emails = array_map( 'sanitize_email', $user_emails );
		$user_emails = array_filter( $user_emails, 'is_email' );
		return array_unique( $user_emails );
	}

	/**
	 * Called on action: woocommerce_checkout_update_order_review
	 *
	 * Collects billing email address from the checkout-form
	 */
	public function fetch_billing_email( $post_data ) {
		//post_data can be an array, or a query=string&like=this
		if ( ! is_array( $post_data ) ) {
			parse_str( $post_data, $posted );
		} else {
			$posted = $post_data;
		}

		if ( isset( $posted['billing_email'] ) ) {
			//$this->log('debug', 'billing:' . $posted['billing_email']);
			WJECF()->set_session( 'billing_email', $posted['billing_email'] );
		}
	}

	public function is_pro() {
		return $this instanceof WJECF_Pro_Controller;
	}

	// ===========================================================================
	// START - OVERWRITE COUPON SUCCESS MESSAGE
	// ===========================================================================

	/**
	 * 2.3.4
	 * If a 'Coupon applied' message is displayed by WooCommerce, replace it by another message (or no message)
	 * @param WC_Coupon $coupon The coupon to replace the message for
	 * @param string $new_message The new message. Set to empty string if no message must be displayed
	 */
	public function start_overwrite_success_message( $coupon, $new_message = '' ) {
		$this->overwrite_coupon_message[ $coupon->get_code() ] = array( WC_Coupon::WC_COUPON_SUCCESS => $new_message );
	}

	/**
	 * 2.3.4
	 * Stop overwriting messages
	 */
	public function stop_overwrite_success_message() {
		$this->overwrite_coupon_message = array();
	}

	private $overwrite_coupon_message = array(); /* [ 'coupon_code' => [ msg_code => 'new_message' ] ] */

	function filter_woocommerce_coupon_message( $msg, $msg_code, $coupon ) {
		if ( isset( $this->overwrite_coupon_message[ $coupon->get_code() ][ $msg_code ] ) ) {
			$msg = $this->overwrite_coupon_message[ $coupon->get_code() ][ $msg_code ];
		}
		return $msg;
	}

	// ===========================================================================
	// END - OVERWRITE COUPON SUCCESS MESSAGE
	// ===========================================================================

	/**
	 * Return an array of WC_Coupons with coupons that shouldn't cause individual use conflicts.
	 *
	 * @param WC_Coupon[] $coupons The coupons
	 * @param string[] $applied_coupon_codes Coupon codes that are considered to be in the cart. If null WC()->cart->get_applied_coupons() will be used.
	 * @return WC_Coupon[]
	 */
	public function coupon_combination_filter( $coupons, $applied_coupon_codes = null ) {

		$filtered_coupons = array();

		//Contains coupon codes that are already in cart or pending in the filtered-array
		if ( $applied_coupon_codes === null ) {
			$applied_coupon_codes = WC()->cart->get_applied_coupons();
		}

		foreach ( $coupons as $the_coupon ) {
			if ( $the_coupon->get_individual_use() && ! in_array( $the_coupon->get_code(), $applied_coupon_codes ) ) {
				//Only allow a new automatic individual use coupon if it doesn't remove coupons from the cart.
				$coupons_to_keep = apply_filters( 'woocommerce_apply_individual_use_coupon', array(), $the_coupon, $applied_coupon_codes );
				if ( count( $applied_coupon_codes ) != count( array_intersect( $applied_coupon_codes, $coupons_to_keep ) ) ) {
					continue; //skip coupon.
				}
			}

			//Check to see if an individual use coupon is already in the cart.
			foreach ( $applied_coupon_codes as $code ) {
				if ( $code === $the_coupon->get_code() ) {
					//Dont compare the coupon with itself
					continue;
				}
				$coupon = new WC_Coupon( $code );
				if ( $coupon->get_individual_use() && false === apply_filters( 'woocommerce_apply_with_individual_use_coupon', false, $the_coupon, $coupon, $applied_coupon_codes ) ) {
					continue 2; //skip coupon.
				}
			}

			/**
			 * Filter to disallow certain coupon combinations to be auto-applied together.
			 *
			 * @since 3.0.0
			 *
			 * @param WC_Coupon   $the_coupon            Coupon to apply
			 * @param string[]    $applied_coupon_codes  Codes of the coupons already in the cart
			 */
			if ( ! apply_filters( 'wjecf_apply_with_other_coupons', true, $the_coupon, $applied_coupon_codes ) ) {
				continue; //skip coupon.
			}

			$applied_coupon_codes[] = $the_coupon->get_code();
			$filtered_coupons[]     = $the_coupon;
		}

		return $filtered_coupons;
	}

	/**
	 * @since 2.4.4
	 *
	 * Get a coupon, but inhibit the woocommerce_coupon_loaded to overwrite values.
	 * @param WC_Coupon|string $coupon_code The coupon code or a WC_Coupon object
	 * @return WC_Coupon The coupon object
	 */
	public function get_original_coupon( $coupon_code ) {
		//Prevent returning the same instance
		if ( $coupon_code instanceof WC_Coupon ) {
			$coupon_code = $coupon_code->get_code();
		}
		$this->inhibit_overwrite++;
		$coupon = WJECF_WC()->get_coupon( $coupon_code );
		$this->inhibit_overwrite--;
		return $coupon;
	}

	private $inhibit_overwrite = 0;

	/**
	 * @since 2.4.4
	 *
	 * May coupon values be overwritten by this plugin upon load?
	 * @return bool
	 */
	public function allow_overwrite_coupon_values() {
		return ( 0 == $this->inhibit_overwrite ) && $this->is_request( 'frontend' );
	}

	//============

	private $_session_data = null;
	/**
	 * Read something from the session.
	 *
	 * If key is omitted; all the session data will be returned as an array
	 *
	 * @param string $key The key for identification
	 * @param any $default The default value (Default: false)
	 *
	 * @return The saved value if found, otherwise the default value
	 */
	public function get_session( $key = null, $default = false ) {
		if ( ! isset( $this->_session_data ) ) {
			if ( ! isset( WC()->session ) ) {
				$this->log( 'error', 'Trying to access WC()->session while it was not yet initialized.' );
				return null;
			}
			$this->_session_data = WC()->session->get( '_wjecf_session_data', array() );
		}

		if ( ! isset( $key ) ) {
			return $this->_session_data;
		}
		if ( ! isset( $this->_session_data[ $key ] ) ) {
			return $default;
		}
		return $this->_session_data[ $key ];
	}

	/**
	 * Save something in the session
	 *
	 * @param string $key The key for identification
	 * @param anything $value The value to store. Use 'null' to remove the value
	 */
	public function set_session( $key, $value ) {
		if ( ! isset( $this->_session_data ) ) {
			if ( ! isset( WC()->session ) ) {
				$this->log( 'error', 'Trying to access WC()->session while it was not yet initialized.' );
				return null;
			}
			$this->_session_data = WC()->session->get( '_wjecf_session_data', array() );
		}
		if ( is_null( $value ) ) {
			unset( $this->_session_data[ $key ] );
		} else {
			$this->_session_data[ $key ] = $value;
		}

		WC()->session->set( '_wjecf_session_data', $this->_session_data );
	}

	/**
	 * (Copied from class-woocommerce.php) What type of request is this?
	 *
	 * @since 2.6.2
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	// ========================
	// INFO ABOUT WJECF PLUGIN
	// ========================

	/**
	 * Filename of this plugin including the containing directory.
	 * @return string
	 */
	public function plugin_file() {
		$filename = $this->is_pro() ? 'woocommerce-jos-autocoupon-pro.php' : 'woocommerce-jos-autocoupon.php';
		return trailingslashit( basename( dirname( dirname( __FILE__ ) ) ) ) . $filename;
	}

	public function plugin_basename() {
		return plugin_basename( $this->plugin_file() );
	}


	/**
	 * url to the base directory of this plugin (wp-content/woocommerce-jos-autocoupon/) with trailing slash
	 * @return string
	 */
	public function plugin_url( $suffix = '' ) {
		return plugins_url( '/', dirname( __FILE__ ) ) . $suffix;
	}

	public function plugin_version() {
		return WJECF_VERSION;
	}

	// ========================
	// LOGGING
	// ========================


	/**
	 * Log a message for debugging.
	 *
	 * If debug_mode is false; messages with level 'debug' will be ignored.
	 *
	 * @param string $level The level of the message. e.g. 'debug' or 'warning'
	 * @param string $string The message to log
	 * @param int $skip_backtrace Defaults to 0, amount of items to skip in backtrace to fetch class and method name
	 */
	public function log( $level, $message = null, $skip_backtrace = 0 ) {
		if ( false === $this->debugger ) {
			return; //Debugger is disabled.
		}

		if ( ! isset( $this->debugger ) ) {
			if ( in_array( 'debug', $this->get_option( 'disabled_plugins' ) ) ) {
				$this->debugger = false;
				return;
			}

			$debugger = $this->get_plugin( 'debug' );
			if ( ! $debugger ) {
				//Fallback to error_log if the logger is not yet loaded.
				error_log( sprintf( 'WJECF: %s: %s', $level, $message ) );
				return;
			}

			$this->debugger = $debugger;
		}

		$this->debugger->log( $level, $message, $skip_backtrace + 1 );
	}

	/**
	 * The debugger
	 *
	 * @var WJECF_Debug|bool|null False if disabled, null if not yet loaded otherwise the WJECF_Debug instance
	 */
	private $debugger = null;
}
