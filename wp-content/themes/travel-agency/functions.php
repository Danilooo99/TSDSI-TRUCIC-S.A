<?php
/**
 * Travel Agency functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Travel_Agency
 */
$travel_agency_theme_data = wp_get_theme();

if( ! defined( 'TRAVEL_AGENCY_THEME_VERSION' ) ) define( 'TRAVEL_AGENCY_THEME_VERSION', $travel_agency_theme_data->get( 'Version' ) );
if( ! defined( 'TRAVEL_AGENCY_THEME_NAME' ) ) define( 'TRAVEL_AGENCY_THEME_NAME', $travel_agency_theme_data->get( 'Name' ) );
if( ! defined( 'TRAVEL_AGENCY_THEME_TEXTDOMAIN' ) ) define( 'TRAVEL_AGENCY_THEME_TEXTDOMAIN', $travel_agency_theme_data->get( 'TextDomain' ) );

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Custom functions for selective refresh.
 */
require get_template_directory() . '/inc/partials.php';

/**
 * Custom Functions
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Template Functions
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Widgets
 */
require get_template_directory() . '/inc/widgets.php';

/**
 * Metabox
 */
require get_template_directory() . '/inc/metabox.php';
/**
 * Getting Started
*/
require get_template_directory() . '/inc/getting-started/getting-started.php';

/**
 * Plugin Recommendation
*/
require get_template_directory() . '/inc/tgmpa/recommended-plugins.php';