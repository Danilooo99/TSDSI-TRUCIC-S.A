<?php

/**
 * Footer Settings
 *
 * @package Travel Tour Pro
 */
add_action( 'customize_register', 'travel_tour_customize_register_footer_section' );
function travel_tour_customize_register_footer_section( $wp_customize )
{
    $wp_customize->add_section( 'travel_tour_footer_section', array(
        'title'    => esc_html__( 'Footer Section', 'travel-tour' ),
        'panel'    => 'travel_tour_homepage_panel',
        'priority' => 170,
    ) );
    $wp_customize->add_setting( 'copyright_edit_option', array(
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ) );
    $wp_customize->add_control( new Travel_Tour_Custom_Text( $wp_customize, 'copyright_edit_option', array(
        'label'    => esc_html__( 'Footer Copyright text can be edited in Pro Version.', 'travel-tour' ),
        'section'  => 'travel_tour_footer_section',
        'settings' => 'copyright_edit_option',
    ) ) );
}
