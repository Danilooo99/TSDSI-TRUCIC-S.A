<?php
/**
 * Rearrange Sections
 *
 * @package Travel Tour Pro
 */
add_action( 'customize_register', 'travel_tour_sort_homepage_sections' );

function travel_tour_sort_homepage_sections( $wp_customize ) {

	$wp_customize->add_section( 'travel_tour_sort_homepage_sections', array(
	    'title'          => esc_html__( 'Rearrange Home Sections', 'travel-tour' ),
	    'description'    => esc_html__( 'Rearrange Home Sections', 'travel-tour' ),
	    'panel'          => '',
	    'priority'       => 10,
	) );

	if( class_exists( 'Wp_Travel_Engine' ) ) :
		$default = array( 'banner-section', 'search-section', 'slider-section', 'trips-section', 'about-section', 'blog-section' );
		$choices = array(			
			'banner-section' => esc_html__( 'Banner Section', 'travel-tour' ),
			'search-section' => esc_html__( 'Search Section', 'travel-tour' ),
			'slider-section' => esc_html__( 'Slider Section', 'travel-tour' ),
			'trips-section' => esc_html__( 'Trips Section', 'travel-tour' ),
			'about-section' => esc_html__( 'About Section', 'travel-tour' ),
			'blog-section' => esc_html__( 'Blog Section', 'travel-tour' ),
		);
	else :
		$default = array( 'banner-section', 'about-section', 'blog-section' );
		$choices = array(			
			'banner-section' => esc_html__( 'Banner Section', 'travel-tour' ),
			'about-section' => esc_html__( 'About Section', 'travel-tour' ),
			'blog-section' => esc_html__( 'Blog Section', 'travel-tour' ),
		);
	endif;

	$wp_customize->add_setting( 'travel_tour_sort_homepage', array(
        'capability'  => 'edit_theme_options',
        'sanitize_callback'	=> 'travel_tour_sanitize_array',
        'default'     => $default
    ) );

    $wp_customize->add_control( new Travel_Tour_Control_Sortable( $wp_customize, 'travel_tour_sort_homepage', array(
        'label' => esc_html__( 'Drag and Drop Sections to rearrange.', 'travel-tour' ),
        'section' => 'travel_tour_sort_homepage_sections',
        'settings' => 'travel_tour_sort_homepage',
        'type'=> 'sortable',
        'choices'     => $choices
    ) ) );

}