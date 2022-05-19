<?php
/**
 * Simple_Share_Buttons_Adder
 *
 * @package SimpleShareButtonsAdder
 */

namespace SimpleShareButtonsAdder;

/**
 * Simple Share Buttons Adder Class
 *
 * @package SimpleShareButtonsAdder
 */
class Simple_Share_Buttons_Adder {


	/**
	 * Plugin instance.
	 *
	 * @var object
	 */
	public $plugin;

	/**
	 * Class constructor.
	 *
	 * @param object $plugin Plugin class.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Get the SSBA option settings.
	 *
	 * @action init
	 * @return array
	 */
	public function get_ssba_settings() {
		if ( SSBA_VERSION < 8 ) {
			$this->convert_settings();
		}

		$ssba_settings = get_option( 'ssba_settings', true );

		// Decode and return settings.
		return $ssba_settings;
	}

	/**
	 * Convert settings to non JSON if they exist.
	 */
	private function convert_settings() {
		// On update convert settings to non-json.
		// Only update if ssba_settings exist already.
		if ( true === empty( get_option( 'convert_json_ssba_settings' ) )
			&& false === empty( get_option( 'ssba_settings' ) )
		) {
			$convert_settings = json_decode( get_option( 'ssba_settings' ), true );

			update_option( 'ssba_settings', $convert_settings );
			update_option( 'convert_json_ssba_settings', true );
		} elseif ( empty( get_option( 'ssba_settings' ) ) ) {
			update_option( 'convert_json_ssba_settings', true );
		}

		// On update convert settings to non-json.
		// Only update if ssba_settings exist already.
		if ( empty( get_option( 'convert_json_ssba_buttons' ) ) && ! empty( get_option( 'ssba_buttons' ) ) ) {
			$convert_buttons = json_decode( get_option( 'ssba_buttons' ), true );

			update_option( 'ssba_buttons', $convert_buttons );
			update_option( 'convert_json_ssba_buttons', true );

			wp_safe_redirect( '/' );
		} elseif ( empty( get_option( 'ssba_buttons' ) ) ) {
			update_option( 'convert_json_ssba_buttons', true );
		}
	}

	/**
	 * Update an array of options.
	 *
	 * @param array $arr_options The options array.
	 */
	public function ssba_update_options( $arr_options ) {
		// If not given an array.
		if ( false === is_array( $arr_options ) ) {
			return esc_html__( 'Value parsed not an array', 'simple-share-buttons-adder' );
		}

		// Get ssba settings.
		$ssba_settings = get_option( 'ssba_settings', true );

		// Loop through array given.
		foreach ( $arr_options as $name => $value ) {
			// Update/add the option in the array.
			$ssba_settings[ $name ] = $value;
		}

		// Update the option in the db.
		update_option( 'ssba_settings', $ssba_settings );
	}

	/**
	 * Add setting link to plugin page.
	 *
	 * @param array $links Links array.
	 *
	 * @return array Modified links array.
	 */
	public function add_action_links( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'options-general.php?page=simple-share-buttons-adder' ) . '">Settings</a>',
		);

		return array_merge( $links, $mylinks );
	}
}
