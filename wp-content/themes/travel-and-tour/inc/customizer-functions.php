<?php 
/**
 * Travel Agency Customizer Functions
*/

function travel_and_tour_customizer_options( $wp_customize ){

    /**
     * Top header
     */ 
    /** Work Hour */
    $wp_customize->add_setting(
        'time',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'time',
        array(
            'label'       => __( 'Work Hour', 'travel-and-tour' ),
            'description' => __( 'Add working hour in header.', 'travel-and-tour' ),
            'section'     => 'header_misc_setting',
            'type'        => 'text',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'time', array(
        'selector'        => '.site-header .opening-time .time',
        'render_callback' => 'travel_and_tour_get_time',
    ) );

    /** Email */
    $wp_customize->add_setting(
        'email',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_email',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'email',
        array(
            'label'       => __( 'Email', 'travel-and-tour' ),
            'description' => __( 'Add email in header.', 'travel-and-tour' ),
            'section'     => 'header_misc_setting',
            'type'        => 'text',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'email', array(
        'selector'        => '.site-header .email-link .email',
        'render_callback' => 'travel_and_tour_get_email',
    ) );

    /** Phone Number  */
    $wp_customize->add_setting(
        'phone',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'phone',
        array(
            'label'       => __( 'Phone Number', 'travel-and-tour' ),
            'description' => __( 'Add phone number in header.', 'travel-and-tour' ),
            'section'     => 'header_misc_setting',
            'type'        => 'text',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'phone', array(
        'selector'        => '.site-header .header-b .phone',
        'render_callback' => 'travel_and_tour_get_header_phone',
    ) );
    
    /** Phone Label  */
    $wp_customize->add_setting(
        'phone_label',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'phone_label',
        array(
            'label'       => __( 'Phone Label', 'travel-and-tour' ),
            'description' => __( 'Add phone label in header.', 'travel-and-tour' ),
            'section'     => 'header_misc_setting',
            'type'        => 'text',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'phone_label', array(
        'selector' => '.site-header .header-b .phone-label',
        'render_callback' => 'travel_and_tour_get_phone_label',
    ) );
    

    /** Banner link one label */
    $wp_customize->add_setting(
        'banner_btn_label',
        array(
            'default'           => __( 'Get Started', 'travel-and-tour' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'banner_btn_label',
        array(
            'section'         => 'header_image',
            'label'           => __( 'Button Label', 'travel-and-tour' ),
        )
    );

    /** Enable/Disable Search Form */
    $wp_customize->add_setting(
        'ed_search',
        array(
            'default'           => false,
            'sanitize_callback' => 'travel_agency_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
        'ed_search',
        array(
            'section'     => 'header_misc_setting',
            'label'       => __( 'Search Form', 'travel-and-tour' ),
            'description' => __( 'Enable to show search form in header.', 'travel-and-tour' ),
            'type'        => 'checkbox'
        )       
    );

    // Selective refresh for banner link one label
    $wp_customize->selective_refresh->add_partial( 'banner_btn_label', array(
        'selector'            => '.banner .form-holder a.btn-banner',
        'render_callback'     => 'travel_and_tour_btn_label_selective_refresh',
        'container_inclusive' => false,
        'fallback_refresh'    => true,
    ) );

    /** Banner link one url */
    $wp_customize->add_setting(
        'banner_btn_url',
        array(
            'default'           => '#',
            'sanitize_callback' => 'esc_url_raw',
        )
    );

    $wp_customize->add_control(
        'banner_btn_url',
        array(
            'section' => 'header_image',
            'label'   => __( 'Button Url', 'travel-and-tour' ),
            'type'    => 'url',
        )
    );

    /** Enable/Disable Social Links */
    $wp_customize->add_setting(
        'ed_social_links',
        array(
            'default'           => false,
            'sanitize_callback' => 'travel_agency_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
        'ed_social_links',
        array(
            'section'     => 'social_setting',
            'label'       => __( 'Social Links', 'travel-and-tour' ),
            'description' => __( 'Enable to show social links in header.', 'travel-and-tour' ),
            'type'        => 'checkbox'
        )       
    );

}
add_action( 'customize_register', 'travel_and_tour_customizer_options',50 );
