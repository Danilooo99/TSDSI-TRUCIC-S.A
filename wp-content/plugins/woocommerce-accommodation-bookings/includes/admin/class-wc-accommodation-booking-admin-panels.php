<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Sets up our "write" panels for accommodations products.
 */
class WC_Accommodation_Booking_Admin_Panels {

	/**
	 * Hook into WordPress and WooCommerce
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles_and_scripts' ) );
		add_filter( 'product_type_selector', array( $this, 'product_type_selector' ) );
		add_filter( 'product_type_options', array( $this, 'product_type_options' ), 15 );

		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			add_action( 'woocommerce_product_write_panels', array( $this, 'panels' ) );
		} else {
			add_action( 'woocommerce_product_data_panels', array( $this, 'panels' ) );
		}

		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'general_product_data' ) );

		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_tabs' ), 5 );

		add_action( 'woocommerce_process_product_meta', array( $this,'save_product_data' ), 25 );
	}

	/**
	 * Add the accommodation booking product type
	 * @return array
	 */
	public function product_type_selector( $types ) {
		$types[ 'accommodation-booking' ] = __( 'Accommodation product', 'woocommerce-accommodation-bookings' );
		return $types;
	}

	/**
	 * Displays the main accommodation booking settings/data view
	 */
	public function general_product_data() {
		global $post;
		$post_id = $post->ID;
		include( 'views/html-accommodation-booking-data.php' );
	}

	/**
	 * Loads any CSS or JS necessary for the admin
	 */
	public function admin_styles_and_scripts() {

		$screen = get_current_screen();

		// only load it on products
		if ( 'product' === $screen->id ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'wc_accommodation_bookings_writepanel_js', WC_ACCOMMODATION_BOOKINGS_PLUGIN_URL . '/assets/js/writepanel' . $suffix . '.js', array( 'jquery' ), WC_ACCOMMODATION_BOOKINGS_VERSION, true );
		}
	}

	/**
	 * Loads our panels related to accommodation bookings
	 * @version  1.0.11
	 */
	public function panels() {
		global $post, $bookable_product;
		$post_id = $post->ID;

		/**
		 * Day restrictions added to Bookings 1.10.7
		 * @todo  Remove version compare ~Aug 2018
		 */
		if ( version_compare( WC_BOOKINGS_VERSION, '1.10.7', '>=' ) ) {

			if ( empty( $bookable_product ) || $bookable_product->get_id() !== $post->ID ) {
				$bookable_product = new WC_Product_Booking( $post->ID );
			}

			$restricted_meta = $bookable_product->get_restricted_days();

			for ( $i=0; $i < 7; $i++) {

				if ( $restricted_meta && in_array( $i, $restricted_meta ) ) {
					$restricted_days[ $i ] = $i;
				} else {
					$restricted_days[ $i ] = false;
				}
			}
		}

		include( 'views/html-accommodation-booking-rates.php' );
		include( 'views/html-accommodation-booking-availability.php' );
	}


	/**
	 * Hides the "virtal" option for accommodations
	 * @param  array $options
	 * @return array
	 */
	public function product_type_options( $options ) {
		$options['virtual']['wrapper_class'] .= ' show_if_accommodation-booking';
		$options['wc_booking_has_resources']['wrapper_class'] .= ' show_if_accommodation-booking';
		$options['wc_booking_has_persons']['wrapper_class'] .= ' show_if_accommodation-booking';
		return $options;
	}

	/**
	 * Loads the HTML that is used to display the actual tab navigation
	 */
	public function add_tabs() {
		include( 'views/html-accommodation-booking-tabs.php' );
	}

	/**
	 * Saves booking / accommodation data for a product
	 *
	 * @version  1.0.11
	 * @param    int $post_id
	 */
	public function save_product_data( $post_id ) {
		global $wpdb;

		$product_type         = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );
		$has_additional_costs = false;

		if ( 'accommodation-booking' !== $product_type ) {
			return;
		}

		$meta_to_save = array(
			'_wc_booking_has_persons'                => 'issetyesno',
			'_wc_booking_person_qty_multiplier'      => 'yesno',
			'_wc_booking_person_cost_multiplier'     => 'yesno',
			'_wc_booking_min_persons_group'          => 'int',
			'_wc_booking_max_persons_group'          => 'int',
			'_wc_booking_has_person_types'           => 'yesno',
			'_wc_booking_has_resources'                            => 'issetyesno',
			'_wc_booking_resources_assignment'                     => '',
			'_wc_booking_resouce_label'                            => '',
			'_wc_accommodation_booking_calendar_display_mode'      => '',
			'_wc_accommodation_booking_requires_confirmation'      => 'yesno',
			'_wc_accommodation_booking_user_can_cancel'            => '',
			'_wc_accommodation_booking_cancel_limit'               => 'int',
			'_wc_accommodation_booking_cancel_limit_unit'          => '',
			'_wc_accommodation_booking_max_date'                   => 'max_date',
			'_wc_accommodation_booking_max_date_unit'              => 'max_date_unit',
			'_wc_accommodation_booking_min_date'                   => 'int',
			'_wc_accommodation_booking_min_date_unit'              => '',
			'_wc_accommodation_booking_qty'                        => 'int',
			'_wc_accommodation_booking_base_cost'                  => 'float',
			'_wc_accommodation_booking_display_cost'               => '',
			'_wc_accommodation_booking_min_duration'               => 'int',
			'_wc_accommodation_booking_max_duration'               => 'int',
		);

		foreach ( $meta_to_save as $meta_key => $sanitize ) {
			$value = ! empty( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : '';
			switch ( $sanitize ) {
				case 'int' :
					$value = $value ? absint( $value ) : '';
					break;
				case 'float' :
					$value = $value ? floatval( $value ) : '';
					break;
				case 'yesno' :
					$value = $value == 'yes' ? 'yes' : 'no';
					break;
				case 'issetyesno' :
					$value = $value ? 'yes' : 'no';
					break;
				case 'max_date' :
					$value = absint( $value );
					if ( $value == 0 ) {
						$value = 1;
					}
					break;
				default :
					$value = sanitize_text_field( $value );
			}

			$meta_key = str_replace( '_wc_accommodation_booking_', '_wc_booking_', $meta_key );
			update_post_meta( $post_id, $meta_key, $value );

			if ( '_wc_booking_display_cost' === $meta_key ) {
				update_post_meta( $post_id, '_wc_display_cost', $value );
			}

			if ( '_wc_booking_base_cost' === $meta_key ) {
				update_post_meta( $post_id, '_wc_booking_block_cost', $value );
			}
		}

		// Availability
		$availability = array();
		$row_size     = isset( $_POST[ 'wc_accommodation_booking_availability_type' ] ) ? sizeof( $_POST[ 'wc_accommodation_booking_availability_type' ] ) : 0;
		for ( $i = 0; $i < $row_size; $i ++ ) {
			$availability[ $i ]['type']     = wc_clean( $_POST[ 'wc_accommodation_booking_availability_type' ][ $i ] );
			$availability[ $i ]['bookable'] = wc_clean( $_POST[ 'wc_accommodation_booking_availability_bookable' ][ $i ] );
			$availability[ $i ]['priority'] = intval( $_POST[ 'wc_accommodation_booking_availability_priority' ][ $i ] );

			switch ( $availability[ $i ]['type'] ) {
				case 'custom' :
					$availability[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_availability_from_date' ][ $i ] );
					$availability[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_availability_to_date' ][ $i ] );
				break;
				case 'months' :
					$availability[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_availability_from_month' ][ $i ] );
					$availability[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_availability_to_month' ][ $i ] );
				break;
				case 'weeks' :
					$availability[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_availability_from_week' ][ $i ] );
					$availability[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_availability_to_week' ][ $i ] );
				break;
				case 'days' :
					$availability[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_availability_from_day_of_week' ][ $i ] );
					$availability[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_availability_to_day_of_week' ][ $i ] );
				break;
			}
		}
		update_post_meta( $post_id, '_wc_booking_availability', $availability );

		// Restricted days.
		update_post_meta( $post_id, '_wc_booking_has_restricted_days', isset( $_POST['_wc_accommodation_booking_has_restricted_days'] ) );
		$restricted_days = isset( $_POST['_wc_accommodation_booking_restricted_days'] ) ? wc_clean( $_POST['_wc_accommodation_booking_restricted_days'] ) : '';
		update_post_meta( $post_id, '_wc_booking_restricted_days', $restricted_days );

		// Resources
		if ( isset( $_POST['resource_id'] ) && isset( $_POST['_wc_booking_has_resources'] ) ) {
			$resource_ids         = $_POST['resource_id'];
			$resource_menu_order  = $_POST['resource_menu_order'];
			$resource_base_cost   = $_POST['resource_cost'];
			$resource_block_cost  = $_POST['resource_block_cost'];
			$max_loop             = max( array_keys( $_POST['resource_id'] ) );
			$resource_base_costs  = array();
			$resource_block_costs = array();

			for ( $i = 0; $i <= $max_loop; $i ++ ) {
				if ( ! isset( $resource_ids[ $i ] ) ) {
					continue;
				}

				$resource_id = absint( $resource_ids[ $i ] );

				$wpdb->update(
					"{$wpdb->prefix}wc_booking_relationships",
					array(
						'sort_order'  => $resource_menu_order[ $i ]
					),
					array(
						'product_id'  => $post_id,
						'resource_id' => $resource_id
					)
				);

				$resource_base_costs[ $resource_id ]  = wc_clean( $resource_base_cost[ $i ] );
				$resource_block_costs[ $resource_id ] = wc_clean( $resource_block_cost[ $i ] );

				if ( ( $resource_base_cost[ $i ] + $resource_block_cost[ $i ] ) > 0 ) {
					$has_additional_costs = true;
				}
			}

			update_post_meta( $post_id, '_resource_base_costs', $resource_base_costs );
			update_post_meta( $post_id, '_resource_block_costs', $resource_block_costs );
		}
		
		// Rates
		$pricing = array();
		$original_base_cost = abs( (float) get_post_meta( $post_id, '_wc_booking_base_cost', true ) );

		$row_size     = isset( $_POST[ 'wc_accommodation_booking_pricing_type' ] ) ? sizeof( $_POST[ 'wc_accommodation_booking_pricing_type' ] ) : 0;
		for ( $i = 0; $i < $row_size; $i ++ ) {
			$pricing[ $i ]['base_cost'] = $pricing[ $i ]['cost'] = 0;
			$pricing[ $i ]['type']          = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_type' ][ $i ] );
			$new_cost = abs( (float) wc_clean( $_POST[ 'wc_accommodation_booking_pricing_block_cost' ][ $i ] ) );
			$pricing[ $i ]['base_modifier'] = $pricing[$i]['modifier'] = $new_cost > $original_base_cost ? 'plus' : 'minus';
			$pricing[ $i ]['cost'] = abs( $new_cost - $original_base_cost );

			switch ( $pricing[ $i ]['type'] ) {
				case 'custom' :
					$pricing[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_from_date' ][ $i ] );
					$pricing[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_to_date' ][ $i ] );
				break;
				case 'months' :
					$pricing[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_from_month' ][ $i ] );
					$pricing[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_to_month' ][ $i ] );
				break;
				case 'weeks' :
					$pricing[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_from_week' ][ $i ] );
					$pricing[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_to_week' ][ $i ] );
				break;
				case 'days' :
					$pricing[ $i ]['from'] = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_from_day_of_week' ][ $i ] );
					$pricing[ $i ]['to']   = wc_clean( $_POST[ 'wc_accommodation_booking_pricing_to_day_of_week' ][ $i ] );
				break;
			}
		}
		
		// Person Types
		if ( isset( $_POST['person_id'] ) && isset( $_POST['_wc_booking_has_persons'] ) ) {
			$person_ids         = $_POST['person_id'];
			$person_menu_order  = $_POST['person_menu_order'];
			$person_name        = $_POST['person_name'];
			$person_cost        = $_POST['person_cost'];
			$person_block_cost  = $_POST['person_block_cost'];
			$person_description = $_POST['person_description'];
			$person_min         = $_POST['person_min'];
			$person_max         = $_POST['person_max'];

			$max_loop = max( array_keys( $_POST['person_id'] ) );

			for ( $i = 0; $i <= $max_loop; $i ++ ) {
				if ( ! isset( $person_ids[ $i ] ) ) {
					continue;
				}

				$person_id = absint( $person_ids[ $i ] );

				if ( empty( $person_name[ $i ] ) ) {
					$person_name[ $i ] = sprintf( __( 'Person Type #%d', 'woocommerce-bookings' ), ( $i + 1 ) );
				}

				$wpdb->update(
					$wpdb->posts,
					array(
						'post_title'   => stripslashes( $person_name[ $i ] ),
						'post_excerpt' => stripslashes( $person_description[ $i ] ),
						'menu_order'   => $person_menu_order[ $i ] ),
					array(
						'ID' => $person_id
					),
					array(
						'%s',
						'%s',
						'%d'
					),
					array( '%d' )
				);

				update_post_meta( $person_id, 'cost', wc_clean( $person_cost[ $i ] ) );
				update_post_meta( $person_id, 'block_cost', wc_clean( $person_block_cost[ $i ] ) );
				update_post_meta( $person_id, 'min', wc_clean( $person_min[ $i ] ) );
				update_post_meta( $person_id, 'max', wc_clean( $person_max[ $i ] ) );

				if ( $person_cost[ $i ] > 0 || $person_block_cost[ $i ] > 0 ) {
					$has_additional_costs = true;
				}
			}
		}

		update_post_meta( $post_id, '_wc_booking_pricing', $pricing );
		update_post_meta( $post_id, '_wc_booking_cost', '' );

		update_post_meta( $post_id, '_regular_price', '' );
		update_post_meta( $post_id, '_sale_price', '' );
		update_post_meta( $post_id, '_manage_stock', 'no' );

		// Set price so filters work - using get_base_cost()
		$product = wc_get_product( $post_id );
		update_post_meta( $post_id, '_price', $product->get_base_cost() );
	}

}

new WC_Accommodation_Booking_Admin_Panels();
