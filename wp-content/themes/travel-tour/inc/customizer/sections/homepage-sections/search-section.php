<?php

/**
 * Search Settings
 *
 * @package Travel Tour Pro
 */
add_action( 'customize_register', 'chic_lifestyle_customize_search_section' );
function chic_lifestyle_customize_search_section( $wp_customize )
{
    $wp_customize->add_section( 'travel_tour_search_sections', array(
        'title'       => esc_html__( 'Search Section', 'travel-tour' ),
        'description' => esc_html__( 'Search Section :', 'travel-tour' ),
        'panel'       => 'travel_tour_homepage_panel',
        'priority'    => 160,
    ) );
    $wp_customize->add_setting( 'search_section_display_option', array(
        'sanitize_callback' => 'travel_tour_sanitize_checkbox',
        'default'           => true,
    ) );
    $wp_customize->add_control( new Travel_Tour_Toggle_Control( $wp_customize, 'search_section_display_option', array(
        'label'    => esc_html__( 'Hide / Show default search', 'travel-tour' ),
        'section'  => 'travel_tour_search_sections',
        'settings' => 'search_section_display_option',
        'type'     => 'toggle',
        'priority' => 1,
    ) ) );
    if ( class_exists( 'Wp_Travel_Engine' ) && function_exists( 'run_wte_advanced_search' ) ) {
    }
}
