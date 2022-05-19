<?php
/**
 * Recommended Trips Settings
 *
 * @package Travel Tour Pro
 */

if( class_exists( 'Wp_Travel_Engine' ) ) : 
    add_action( 'customize_register', 'travel_tour_customize_register_recommended_activities_section' );
endif;

function travel_tour_customize_register_recommended_activities_section( $wp_customize ) {
    
	$wp_customize->add_section( 'travel_tour_recommended_activities_sections', array(
	    'title'          => esc_html__( 'Recommended Activities', 'travel-tour' ),
	    'description'    => esc_html__( 'Recommended Activities :', 'travel-tour' ),
	    'panel'          => 'travel_tour_homepage_panel',
	    'priority'       => 160,
	) );

    $wp_customize->add_setting( 'recommended_activities_display_option', array(
        'sanitize_callback'     =>  'travel_tour_sanitize_checkbox',
        'default'               =>  true
    ) );

    $wp_customize->add_control( new Travel_Tour_Toggle_Control( $wp_customize, 'recommended_activities_display_option', array(
        'label' => esc_html__( 'Hide / Show','travel-tour' ),
        'section' => 'travel_tour_recommended_activities_sections',
        'settings' => 'recommended_activities_display_option',
        'type'=> 'toggle',
    ) ) );

    $wp_customize->add_setting( 'recommended_activities_category', array(
        'capability'  => 'edit_theme_options',        
        'sanitize_callback' => 'sanitize_text_field',
        'default'     => 'activities',
    ) );

    $wp_customize->add_control( new Travel_Tour_Dropdown_Posttype_Taxonomies( $wp_customize, 'recommended_activities_category', array(
        'label' => esc_html__( 'Choose category','travel-tour' ),
        'section' => 'travel_tour_recommended_activities_sections',
        'settings' => 'recommended_activities_category',
        'type'=> 'posttype-taxonomies',
        'posttype'  =>  'trip'
    ) ) );

    $wp_customize->add_setting( 'recommended_activities_title', array(
        'sanitize_callback'     =>  'sanitize_text_field',
        'default'               =>  ''
    ) );

    $wp_customize->add_control( 'recommended_activities_title', array(
        'label' => esc_html__( 'Title', 'travel-tour' ),
        'section' => 'travel_tour_recommended_activities_sections',
        'settings' => 'recommended_activities_title',
        'type'=> 'text',
    ) );

    $wp_customize->add_setting( 'number_of_recommended_activities', array(
        'sanitize_callback'     =>  'sanitize_text_field',
        'default'               =>  4
    ) );

    $wp_customize->add_control( 'number_of_recommended_activities', array(
        'label' => esc_html__( 'Number of posts', 'travel-tour' ),
        'section' => 'travel_tour_recommended_activities_sections',
        'settings' => 'number_of_recommended_activities',
        'type'=> 'text',
        'description'   =>  'put -1 for unlimited'
    ) );


    $wp_customize->add_setting( 'recommended_activities_slider_layout', array(  
        'sanitize_callback' => 'travel_tour_sanitize_choices',
        'default'     => 'one',
    ) );
    

    $wp_customize->add_control( new Travel_Tour_Radio_Image_Control( $wp_customize, 'recommended_activities_slider_layout', array(
        'label' => esc_html__( 'Slider Layout','travel-tour' ),
        'section' => 'travel_tour_recommended_activities_sections',
        'settings' => 'recommended_activities_slider_layout',
        'type'=> 'radio-image',
        'choices'     => array(
            'one'   => get_template_directory_uri() . '/images/homepage/slider-layouts/one.jpg',
        ),
    ) ) );

}