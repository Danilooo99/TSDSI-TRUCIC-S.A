<?php
/**
 * Trips Settings
 *
 * @package Travel Tour Pro
 */

if( class_exists( 'Wp_Travel_Engine' ) ) : 
    add_action( 'customize_register', 'travel_tour_customize_register_best_tours_section' );
endif;

function travel_tour_customize_register_best_tours_section( $wp_customize ) {
    
    $wp_customize->add_section( 'travel_tour_best_tours_sections', array(
        'title'          => esc_html__( 'Best Tours', 'travel-tour' ),
        'description'    => esc_html__( 'Best Tours :', 'travel-tour' ),
        'panel'          => 'travel_tour_homepage_panel',
        'priority'       => 160,
    ) );

    $wp_customize->add_setting( 'best_tours_display_option', array(
        'sanitize_callback'     =>  'travel_tour_sanitize_checkbox',
        'default'               =>  true
    ) );

    $wp_customize->add_control( new Travel_Tour_Toggle_Control( $wp_customize, 'best_tours_display_option', array(
        'label' => esc_html__( 'Hide / Show','travel-tour' ),
        'section' => 'travel_tour_best_tours_sections',
        'settings' => 'best_tours_display_option',
        'type'=> 'toggle',
    ) ) );

    $wp_customize->add_setting( 'best_tours_category', array(
        'capability'  => 'edit_theme_options',        
        'sanitize_callback' => 'travel_tour_sanitize_category',
        'default'     => '',
    ) );

    $wp_customize->add_control( new Travel_Tour_Customize_Dropdown_Taxonomies_Control( $wp_customize, 'best_tours_category', array(
        'label' => esc_html__( 'Choose category','travel-tour' ),
        'section' => 'travel_tour_best_tours_sections',
        'settings' => 'best_tours_category',
        'type'=> 'dropdown-taxonomies',
        'taxonomy'  =>  'trip_types'
    ) ) );

    $wp_customize->add_setting( 'best_tours_title', array(
        'sanitize_callback'     =>  'sanitize_text_field',
        'default'               =>  ''
    ) );

    $wp_customize->add_control( 'best_tours_title', array(
        'label' => esc_html__( 'Title', 'travel-tour' ),
        'section' => 'travel_tour_best_tours_sections',
        'settings' => 'best_tours_title',
        'type'=> 'text',
    ) );

    $wp_customize->add_setting( 'number_of_best_tours', array(
        'sanitize_callback'     =>  'sanitize_text_field',
        'default'               =>  8
    ) );

    $wp_customize->add_control( 'number_of_best_tours', array(
        'label' => esc_html__( 'Number of posts', 'travel-tour' ),
        'section' => 'travel_tour_best_tours_sections',
        'settings' => 'number_of_best_tours',
        'type'=> 'text',
        'description'   =>  'put -1 for unlimited'
    ) );

    $wp_customize->add_setting( 'best_tours_layout', array(  
        'sanitize_callback' => 'travel_tour_sanitize_choices',
        'default'     => 'one',
    ) );

    $wp_customize->add_control( new Travel_Tour_Radio_Image_Control( $wp_customize, 'best_tours_layout', array(
        'label' => esc_html__( 'Trip Layouts','travel-tour' ),
        'section' => 'travel_tour_best_tours_sections',
        'settings' => 'best_tours_layout',
        'type'=> 'radio-image',
        'choices'     => array(
            'one'   => get_template_directory_uri() . '/images/homepage/trip-layouts/one.jpg',
            'two'   => get_template_directory_uri() . '/images/homepage/trip-layouts/two.jpg',
            'three'   => get_template_directory_uri() . '/images/homepage/trip-layouts/three.jpg',
        ),
    ) ) );

}