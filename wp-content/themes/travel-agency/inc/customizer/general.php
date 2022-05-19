<?php
/**
 * General Settings
 *
 * @package Travel_Agency
 */

function travel_agency_customize_register_general( $wp_customize ) {
	
    $wp_customize->add_panel( 'general_settings', array(
        'title'      => __( 'General Settings', 'travel-agency' ),
        'priority'   => 60,
        'capability' => 'edit_theme_options',
    ) );
    
    /** Archive Page */
    $wp_customize->add_section(
        'archive_settings',
        array(
            'title'    => __( 'Archive Page Settings', 'travel-agency' ),
            'priority' => 15,
            'panel'    => 'general_settings',
        )
    );
    
    /** Read More label */
    $wp_customize->add_setting(
        'readmore',
        array(
            'default'           => __( 'Read More', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
		'readmore',
		array(
			'section' => 'archive_settings',
			'label'	  => __( 'Read More Label', 'travel-agency' ),
            'type'    => 'text'
		)		
	);
    
    $wp_customize->selective_refresh->add_partial( 'readmore', array(
        'selector'        => '.site-main .entry-footer .btn-holder .btn-more',
        'render_callback' => 'travel_agency_get_readmore_btn',
    ) );
    
    /** Post Page */
    $wp_customize->add_section(
        'post_page_settings',
        array(
            'title'    => __( 'Post Page Settings', 'travel-agency' ),
            'priority' => 20,
            'panel'    => 'general_settings',
        )
    );
    
    /** Enable/Disable Related Posts */
    $wp_customize->add_setting(
        'ed_related',
        array(
            'default'           => true,
            'sanitize_callback' => 'travel_agency_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		'ed_related',
		array(
			'section'	  => 'post_page_settings',
			'label'		  => __( 'Related Posts', 'travel-agency' ),
			'description' => __( 'Enable to show related posts in single post page.', 'travel-agency' ),
            'type'        => 'checkbox'
		)		
	);
    
    /** Related Title */
    $wp_customize->add_setting(
        'related_title',
        array(
            'default'           => __( 'You may also like...', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
		'related_title',
		array(
			'section' => 'post_page_settings',
			'label'	  => __( 'Related Post Title', 'travel-agency' ),
            'type'    => 'text'
		)		
	);
    
    $wp_customize->selective_refresh->add_partial( 'related_title', array(
        'selector'        => '.site-main .related-post .title',
        'render_callback' => 'travel_agency_get_related_title',
    ) );
    
    /** SEO Settings */
    $wp_customize->add_section(
        'breadcrumb_settings',
        array(
            'title'    => __( 'SEO Settings', 'travel-agency' ),
            'priority' => 25,
            'panel'    => 'general_settings',
        )
    );

    /** Enable/Disable BreadCrumb */
    $wp_customize->add_setting(
        'ed_breadcrumb',
        array(
            'default'           => true,
            'sanitize_callback' => 'travel_agency_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		'ed_breadcrumb',
		array(
			'section'	  => 'breadcrumb_settings',
			'label'		  => __( 'Enable Breadcrumb', 'travel-agency' ),
            'type'        => 'checkbox'
		)		
	);
    
    /** Home Text */
    $wp_customize->add_setting(
        'breadcrumb_home_text',
        array(
            'default'           => __( 'Home', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    
    $wp_customize->add_control(
        'breadcrumb_home_text',
        array(
            'label'   => __( 'Breadcrumb Home Text', 'travel-agency' ),
            'section' => 'breadcrumb_settings',
            'type'    => 'text',
        )
    );
    
    /** Breadcrumb Separator */
    $wp_customize->add_setting(
        'breadcrumb_separator',
        array(
            'default'           => __( '>', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    
    $wp_customize->add_control(
        'breadcrumb_separator',
        array(
            'label'   => __( 'Breadcrumb Separator', 'travel-agency' ),
            'section' => 'breadcrumb_settings',
            'type'    => 'text',
        )
    );
    
    $wp_customize->get_section( 'colors' )->panel           = 'general_settings';
    $wp_customize->get_section( 'background_image' )->panel = 'general_settings';
    
}
add_action( 'customize_register', 'travel_agency_customize_register_general' );