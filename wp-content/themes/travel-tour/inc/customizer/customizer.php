<?php
/**
 * travel-tour Theme Customizer
 *
 * @package Travel Tour Pro
 */

$panels   = array( 'homepage-panel', 'colors-fonts-panel' );

add_action( 'customize_register', 'travel_tour_change_homepage_settings_options' );
function travel_tour_change_homepage_settings_options( $wp_customize)  {
    
	$wp_customize->get_section('title_tagline')->priority = 12;
	$wp_customize->get_section('static_front_page')->priority = 13;

	$wp_customize->remove_control('header_textcolor');

    $wp_customize->add_setting( 'travel_tour_logo_size', array(
        'default'           => 60,
        'sanitize_callback' => 'absint'
    ) );

    $wp_customize->add_control( new Travel_Tour_Slider_Control( $wp_customize, 'travel_tour_logo_size', array(
        'section' => 'title_tagline',
        'settings' => 'travel_tour_logo_size',
        'label'   => esc_html__( 'Logo Size', 'travel-tour' ),
        'choices'     => array(
            'min'   => 40,
            'max'   => 100,
            'step'  => 1,
        )
    ) ) );
}

$homepage_sections = array( 'banner-section', 'search-section', 'slider-section', 'trips-section', 'about-section', 'blog-section' );

if( ! empty( $panels ) ) {
	foreach( $panels as $panel ){
	    require get_template_directory() . '/inc/customizer/panels/' . $panel . '.php';
	}
}

if( ! empty( $homepage_sections ) ) {
	foreach( $homepage_sections as $section ) {
	    require get_template_directory() . '/inc/customizer/sections/homepage-sections/' . $section . '.php';
	}
}

require get_template_directory() . '/inc/customizer/sections/sort-homepage-section.php';

require get_template_directory() . '/inc/customizer/sections/footer-section.php';

require get_template_directory() . '/inc/customizer/sections/blog-list.php';

require get_template_directory() . '/inc/customizer/sections/general.php';

require get_template_directory() . '/inc/customizer/sections/colors-fonts.php';

require get_template_directory() . '/inc/customizer/sections/social-media.php';


/**
 * Sanitization Functions
*/
require get_template_directory() . '/inc/customizer/sanitization-functions.php';