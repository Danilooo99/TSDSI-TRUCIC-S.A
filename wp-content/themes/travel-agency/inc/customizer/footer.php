<?php
/**
 * Footer Settings
 *
 * @package Travel_Agency
 */

function travel_agency_customize_register_footer( $wp_customize ) {
    
    $wp_customize->add_section(
        'footer_settings',
        array(
            'title'      => __( 'Footer Settings', 'travel-agency' ),
            'priority'   => 500,
            'capability' => 'edit_theme_options',
        )
    );
    
    /** Footer Copyright */
    $wp_customize->add_setting(
        'footer_copyright',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'footer_copyright',
        array(
            'label'   => __( 'Footer Copyright', 'travel-agency' ),
            'section' => 'footer_settings',
            'type'    => 'textarea',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'footer_copyright', array(
        'selector' => '.footer-b .copyright',
        'render_callback' => 'travel_agency_get_footer_copyright',
    ) );
    
}
add_action( 'customize_register', 'travel_agency_customize_register_footer' );