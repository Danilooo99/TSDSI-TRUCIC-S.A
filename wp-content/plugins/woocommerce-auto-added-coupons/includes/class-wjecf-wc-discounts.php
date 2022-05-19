<?php

defined( 'ABSPATH' ) or die();


/**
 * Class that replaces the get_items() a WC_Discounts (WC3.2.0) object for WC < 3.2.0
 *
 * @since 2.6.0
 */
class WJECF_WC_Discounts {

	public function __construct( $object = array() ) {
		if ( is_a( $object, 'WC_Cart' ) ) {
			$this->set_items_from_cart( $object );
			return;
		}
		throw new Exception( 'WJECF_WC_Discounts must be passed a WC_Cart object' );
	}

	/**
	 * Get items.
	 *
	 * @since  3.2.0
	 * @return object[]
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Normalise cart items which will be discounted.
	 *
	 * @since 3.2.0
	 * @param WC_Cart $cart Cart object.
	 */
	public function set_items_from_cart( $cart ) {
		$this->items     = array();
		$this->discounts = array();

		if ( ! is_a( $cart, 'WC_Cart' ) ) {
			return;
		}

		$this->object = $cart;

		foreach ( $cart->get_cart() as $key => $cart_item ) {
			$this->items[ $key ] = WJECF_WC()->cart_item_to_discount_item( $cart_item, $key );
		}

		uasort( $this->items, array( $this, 'sort_by_price' ) );
	}

	/**
	 * Sort by price.
	 *
	 * @since  3.2.0
	 * @param  array $a First element.
	 * @param  array $b Second element.
	 * @return int
	 */
	protected function sort_by_price( $a, $b ) {
		$price_1 = $a->price * $a->quantity;
		$price_2 = $b->price * $b->quantity;
		if ( $price_1 === $price_2 ) {
			return 0;
		}
		return ( $price_1 < $price_2 ) ? 1 : -1;
	}
}
