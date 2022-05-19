<?php

defined( 'ABSPATH' ) or die();

class WJECF_Debug_CLI extends WP_CLI_Command {


	public static function add_command() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'wjecf', __CLASS__ );
		}
	}

	public function plugin_info() {
		WP_CLI::log( sprintf( 'WJECF Version: %s', WJECF()->plugin_version() ) );
		WP_CLI::log( sprintf( 'WJECF File: %s', WJECF()->plugin_file() ) );
		WP_CLI::log( sprintf( 'WJECF Url: %s', WJECF()->plugin_url() ) );
	}

	/**
	 * Test API functions for one or more coupons
	 *
	 * ## OPTIONS
	 *
	 * [couponcode, ...]
	 * : The coupon codes for which to run the tests. If omitted the test will be executed for all auto coupons.
	 *
	 * @param array $args CLI arguments
	 */
	public function test_api( $args ) {
		require_once( 'pro/wjecf-pro-api-example.php' );

		if ( count( $args ) > 0 ) {
			$all = $args;
		} else {
			$all = WJECF_API()->get_all_auto_coupons();
		}

		foreach ( $all as $coupon ) {
			$values = WJECF_API_Test_Coupon( $coupon );
			foreach ( $values as $key => $value ) {
				WP_CLI::log( sprintf( '%s: %s', $key, print_r( $value, true ) ) );
			}
		}
	}

	/**
	 * Check whether the customer has ordered before.
	 *
	 * ## OPTIONS
	 *
	 * <customer>
	 * : The email-address or userid of the customer
	 *
	 * @param array $args CLI arguments
	 */
	public function test_first_order( $args ) {
		if ( count( $args ) <= 0 ) {
			WP_CLI::error( sprintf( 'Please provide an email-address' ) );
			return;
		}
		$customer = $args[0];
		$is_first = ! WJECF()->has_customer_ordered_before( $customer );
		WP_CLI::log( sprintf( 'First order for %s: %s', $customer, $is_first ? 'yes' : 'no' ) );
	}

	protected $tests   = 0;
	protected $fails   = 0;
	protected $passess = 0;

	protected function assert( $true, $test_description ) {
		if ( true !== $true ) {
			WP_CLI::error( $test_description );
			die();
		}
		WP_CLI::success( $test_description );
	}

	protected function assert_same( $results, $test_description ) {
		$success = true;
		foreach ( $results as $result ) {
			if ( isset( $prev_result ) && $result !== $prev_result ) {
				$success = false;
				break;
			}
			$prev_result = $result;
		}

		$this->tests++;

		if ( $success ) {
			$this->passes++;
			WP_CLI::success( $test_description );
		} else {
			$this->fails++;
			foreach ( $results as $key => $result ) {
				WP_CLI::log( sprintf( '%s : %s', $key, $this->dd( $result ) ) );
			}
			WP_CLI::error( $test_description );
		}
	}

	protected function dd( $variable ) {
		return print_r( $variable, true );
	}
}
