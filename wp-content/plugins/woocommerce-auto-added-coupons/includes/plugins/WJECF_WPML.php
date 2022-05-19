<?php /* phpcs:ignore */

if ( defined( 'ABSPATH' ) && ! class_exists( 'WJECF_WPML' ) ) {

	/**
	 * Class to make WJECF compatible with WPML
	 */
	class WJECF_WPML extends Abstract_WJECF_Plugin {


		public function __construct() {
			$this->set_plugin_data(
				array(
					'description'     => __( 'Compatiblity with WPML.', 'woocommerce-jos-autocoupon' ),
					'dependencies'    => array(),
					'can_be_disabled' => true,
				)
			);
		}

		public function init_hook() {
			global $sitepress;
			if ( isset( $sitepress ) ) {
				//WJECF_Controller hooks
				add_filter( 'wjecf_get_product_id', array( $this, 'filter_get_product_id' ), 10 );
				add_filter( 'wjecf_get_product_ids', array( $this, 'filter_get_product_ids' ), 10 );
				add_filter( 'wjecf_get_product_cat_id', array( $this, 'filter_get_product_cat_id' ), 10 );
				add_filter( 'wjecf_get_product_cat_ids', array( $this, 'filter_get_product_cat_ids' ), 10 );
				add_filter( 'woocommerce_coupon_get_description', array( $this, 'filter_get_coupon_description' ), 10, 2 );
			}
		}

		//HOOKS

		public function filter_get_product_ids( $product_ids ) {
			return $this->get_translated_object_ids( $product_ids, 'product' );
		}

		public function filter_get_product_cat_ids( $product_cat_ids ) {
			return $this->get_translated_object_ids( $product_cat_ids, 'product_cat' );
		}

		public function filter_get_product_id( $product_id ) {
			return $this->get_translated_object_id( $product_id, 'product' );
		}

		public function filter_get_coupon_description( $description, $object ) {
			/* phpcs:ignore */
			$description = __( $description, 'woocommerce-jos-autocoupon' );
			return $description;
		}

		//FUNCTIONS


		/**
		 * Get the ids of all the translations. Otherwise return the original array
		 *
		 * @param int|array $product_ids The product_ids to find the translations for
		 * @return array The product ids of all translations
		 *
		 */
		public function get_translated_object_ids( $object_ids, $object_type ) {
			//Make sure it's an array
			if ( ! is_array( $object_ids ) ) {
				$object_ids = array( $object_ids );
			}

			$translated_object_ids = array();
			foreach ( $object_ids as $object_id ) {
				$translated_object_ids[] = apply_filters( 'wpml_object_id', $object_id, $object_type, true ); //true: return original if missing.
			}
			$this->log( 'debug', 'Translated ' . $object_type . ': ' . implode( ',', $object_ids ) . ' to: ' . implode( ',', $translated_object_ids ) );
			return $translated_object_ids;
		}

		/**
		 * Get translated object id
		 * @param int $object_id
		 * @param string $object_type
		 * @return int|bool false if not found
		 */
		public function get_translated_object_id( $object_id, $object_type ) {
			$translated_object_ids = $this->get_translated_object_ids( array( $object_id ), $object_type );
			if ( empty( $translated_object_ids ) ) {
				return false;
			}
			return reset( $translated_object_ids );
		}
	}
}
