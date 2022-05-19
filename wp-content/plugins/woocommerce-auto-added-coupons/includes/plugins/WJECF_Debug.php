<?php /* phpcs:ignore */

if ( defined( 'ABSPATH' ) && ! class_exists( 'WJECF_Debug' ) ) {

	/**
	 * Debugging functions for wjecf
	 *
	 * @since 2.6.0
	 */
	class WJECF_Debug extends Abstract_WJECF_Plugin {


		private $enable_logging = false;

		public function __construct() {
			$this->set_plugin_data(
				array(
					'description'     => __( 'Debugging methods for WooCommerce Extended Coupon Features.', 'woocommerce-jos-autocoupon' ),
					'dependencies'    => array(),
					'can_be_disabled' => false,
					'hidden'          => false,
				)
			);
		}

		public function init_hook() {
			$this->enable_logging = true;

			$this->log_the_request();

			add_action( 'wp_loaded', array( $this, 'handle_querystring' ), 90 );
			add_action( 'wp_footer', array( $this, 'render_log' ) ); //Log
		}

		public function init_admin_hook() {
			if ( current_user_can( 'manage_options' ) && $this->debug_mode() ) {
				$wjecf_admin = WJECF()->get_plugin( 'admin' );
				if ( $wjecf_admin ) {
					$msg                  = __( 'Debug mode is enabled. Please disable it when you\'re done debugging.', 'woocommerce-jos-autocoupon' );
					$wjecf_admin_settings = WJECF()->get_plugin( 'admin-settings' );
					if ( $wjecf_admin_settings ) {
						$msg .= ' <a href="' . $wjecf_admin_settings->get_settings_page_url() . '">' . __( 'Go to settings page', 'woocommerce-jos-autocoupon' ) . '</a>';
					}
					$wjecf_admin->enqueue_notice( $msg, 'notice-warning' );
				}
				add_action( 'wjecf_coupon_metabox_misc', array( $this, 'wjecf_coupon_metabox_misc' ), 20, 2 );
			}
		}

		// ========================
		// ACTION HOOKS
		// ========================

		public function wjecf_coupon_metabox_misc( $thepostid, $post ) {
			echo '<h3>' . esc_html( __( 'Debug', 'woocommerce-jos-autocoupon' ) ) . "</h3>\n";

			$coupon_code = WJECF_WC()->get_coupon( $post )->get_code();
			$url         = add_query_arg( array( 'wjecf_dump' => urlencode( $coupon_code ) ), get_site_url() );
			$text        = __( 'Coupon data as json', 'woocommerce-jos-autocoupon' );

			printf( '<p><a href="%s" target="_blank">%s</a></p>', esc_attr( $url ), $text );
		}

		/**
		 * Output the log as html
		 */
		public function render_log() {
			if ( ! $this->debug_mode() && ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! WJECF()->get_session( 'wjecf_log' ) ) {
				return;
			}

			$this->log( 'debug', 'Session: ' . print_r( WJECF()->get_session(), true ) );
			$this->log( 'debug', 'Current coupons in cart: ' . implode( ', ', WC()->cart->applied_coupons ) );

			if ( $this->log_output ) {
				if ( WJECF()->is_pro() ) {
					WJECF()->include_template( 'debug/log.php', array( 'log' => $this->log_output ) );
				} else {
					//FREE version has no templating.
					$log = $this->log_output;
					?>
						<style>
							.soft79_wjecf_log { font-size:11px; }
						</style>
						<table class='soft79_wjecf_log'>
							<tr>
								<th>Time</th>
								<th>Level</th>
								<th>Filter / Action</th>
								<th>Function</th>
								<th>Message</th>
							</tr>
							<?php
							foreach ( $log as $log_item ) {
								echo '<tr>';
								echo '<td>' . date( 'H:i:s', $log_item['time'] ) . '</td>';
								echo '<td>' . esc_html( $log_item['level'] ) . '</td>';
								echo '<td>' . esc_html( $log_item['filter'] ) . '</td>';
								echo '<td>' . esc_html( $log_item['class'] . '::' . $log_item['function'] ) . '</td>';
								echo '<td>' . esc_html( $log_item['message'] ) . '</td>';
								echo "</tr>\n";
							}
							?>
						</table>
					<?php
				}
			}
		}

		/**
		 * Query argument 'wjecf_debug' toggles rendering of the debug-log for any user (only allowed when debug mode is enabled)
		 * @return type
		 */
		public function handle_querystring() {
			$this->handle_querystring_wjecf_log();
			$this->handle_querystring_wjecf_dump_coupon();
		}

		public function handle_querystring_wjecf_log() {
			// wjecf_log=1 / 0  Enable log on the frontend for guest users
			if ( isset( $_GET['wjecf_log'] ) ) {
				WJECF()->set_session( 'wjecf_log', $_GET['wjecf_log'] ? true : null );
			}
		}

		public function handle_querystring_wjecf_dump_coupon() {
			// wjecf_dump_coupon=coupon_code    Dump the coupon data on the frontend
			if ( ! current_user_can( 'manage_options' ) && ! $this->debug_mode() ) {
				return;
			}

			if ( isset( $_GET['wjecf_dump'] ) ) {
				$coupon_code = $_GET['wjecf_dump'];
				$coupon      = new WC_Coupon( $coupon_code );
				$coupon_id   = $coupon->get_id();

				if ( $coupon_id ) {
					$array = array(
						'result'  => 'ok',
						'coupons' => array( $coupon_id => $coupon->get_data() ),
					);
				} else {
					$array = array( 'result' => 'error' );
				}
				header( 'Content-Type: application/json' );
				echo json_encode( $array );
				die();
			}
		}

		/**
		 * Is debug mode enabled?
		 *
		 * @since 2.6.0
		 * @return bool
		 */
		public function debug_mode() {
			return WJECF()->get_option( 'debug_mode' );
		}

		// ========================
		// LOGGING
		// ========================

		private function log_the_request() {
			if ( defined( 'WP_CLI' ) ) {
				$request_type = 'cli';
			} elseif ( WJECF()->is_request( 'cron' ) ) {
				$request_type = 'cron';
			} elseif ( WJECF()->is_request( 'ajax' ) ) {
				$request_type = 'ajax';
			} elseif ( WJECF()->is_request( 'admin' ) ) {
				$request_type = 'admin';
			} elseif ( WJECF()->is_request( 'frontend' ) ) {
				$request_type = 'frontend';
			} else {
				$request_type = 'api';
			}

			$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '?';
			$request_uri    = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '?';
			$this->log( 'debug', '======== ' . $request_method . ': ' . $request_type . ' ' . $request_uri . ' ========' );
		}

		private $debug_mode = false;
		private $log_output = array();

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
			if ( ! $this->enable_logging || ( ! $this->debug_mode() && 'debug' === $level ) ) {
				return;
			}

			//Backwards compatibility; $level was introduced in 2.4.4
			if ( is_null( $message ) ) {
				$message = $level;
				$level   = 'debug';
			} elseif ( is_int( $message ) && ! $this->is_valid_log_level( $level ) ) {
				$skip_backtrace = $message;
				$message        = $level;
				$level          = 'debug';
			}

			if ( ! $this->debug_mode() && 'debug' === $level ) {
				return;
			}

			$nth      = 1 + $skip_backtrace;
			$bt       = debug_backtrace();
			$class    = isset( $bt[ $nth ]['class'] ) ? $bt[ $nth ]['class'] : '';
			$function = $bt[ $nth ]['function'];

			$row = array(
				'level'    => $level,
				'time'     => time(),
				'class'    => $class,
				'function' => $function,
				'filter'   => current_filter(),
				'message'  => $message,
			);

			$nice_str = $row['filter'] . '   ' . $row['class'] . '::' . $row['function'] . '   ' . $row['message'];

			//Since WC2.7. Note: Might not have be loaded yet when calling this method, so always test if it exists
			if ( function_exists( 'wc_get_logger' ) ) {
				$logger  = wc_get_logger();
				$context = array( 'source' => 'WooCommerce Extended Coupon Features' );
				$logger->log( $level, $nice_str, $context );
				if ( 'debug' !== $level ) {
					error_log( 'WooCommerce Extended Coupon Features ' . $level . ': ' . $row['message'] );
				}
			} else {
				//Legacy
				error_log( $level . ': ' . $nice_str );
			}

			$this->log_output[] = $row;
		}

		private function is_valid_log_level( $level ) {
			return in_array( $level, array( 'debug', 'informational', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ) );
		}
	}
}
