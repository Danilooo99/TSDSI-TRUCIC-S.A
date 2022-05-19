<?php
/**
 * General Settings
 *
 * @package Travel Tour Pro
 */


add_action( 'customize_register', 'travel_tour_customize_general_option' );

function travel_tour_customize_general_option( $wp_customize ) {

    $wp_customize->add_section( 'travel_tour_general_section', array(
        'title'         => esc_html__( 'General Options', 'travel-tour' ),
        'description'   => esc_html__( 'General Options', 'travel-tour' ),
        'panel'         => 'travel_tour_homepage_panel',
        'priority'      => 10,
    ) );

    $wp_customize->add_setting( 'header_search_display_option', array(
        'sanitize_callback'     =>  'travel_tour_sanitize_checkbox',
        'default'               =>  false
    ) );
            
    $wp_customize->add_control( new Travel_Tour_Toggle_Control( $wp_customize, 'header_search_display_option', array(
        'label' => esc_html__( 'Hide / Show Header Search','travel-tour' ),
        'section' => 'travel_tour_general_section',
        'settings' => 'header_search_display_option',
        'type'=> 'toggle',
    ) ) );

    $wp_customize->add_setting( 'contact_number', array(   
        'sanitize_callback' => 'sanitize_text_field',
        'default'     => '',
    ) );

    $wp_customize->add_control( 'contact_number', array(
        'label' => esc_html__( 'Contact Number', 'travel-tour' ),
        'section' => 'travel_tour_general_section',
        'settings' => 'contact_number',
        'type'=> 'text',
    ) );
}