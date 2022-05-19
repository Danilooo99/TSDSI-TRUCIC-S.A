<?php
/**
 * Home Page Settings
 *
 * @package Travel_Agency
 */

function travel_agency_customize_register_home( $wp_customize ) {
    
    $wp_customize->add_panel( 'home_page_setting', array(
        'title'      => __( 'Front Page Settings', 'travel-agency' ),
        'priority'   => 30,
        'capability' => 'edit_theme_options',
    ) );
    
    $wp_customize->get_section( 'header_image' )->panel    = 'home_page_setting';
    $wp_customize->get_section( 'header_image' )->title    = __( 'Banner Section', 'travel-agency' );
    $wp_customize->get_section( 'header_image' )->priority = 10;
    $wp_customize->remove_control( 'header_textcolor' );
    
    /** Enable/Disable Banner Section */
    $wp_customize->add_setting(
        'ed_banner',
        array(
            'default'           => true,
            'sanitize_callback' => 'travel_agency_sanitize_checkbox',
            
            
        )
    );
    
    $wp_customize->add_control(
		'ed_banner',
		array(
			'section'  => 'header_image',
			'label'	   => __( 'Enable Banner Section', 'travel-agency' ),
            'type'     => 'checkbox',
            'priority' => 5,
		)		
	);
    
    /** Enable/Disable Search Form */
    $wp_customize->add_setting(
        'ed_banner_search',
        array(
            'default'           => true,
            'sanitize_callback' => 'travel_agency_sanitize_checkbox',
            'transport'         => 'postMessage',
            
        )
    );
    
    $wp_customize->add_control(
		'ed_banner_search',
		array(
			'section'	  => 'header_image',
			'label'		  => __( 'Search Form', 'travel-agency' ),
			'description' => __( 'Enable to show search form in banner.', 'travel-agency' ),
            'type'        => 'checkbox',
		)		
	);
    
    $wp_customize->selective_refresh->add_partial( 'ed_banner_search', array(
        'selector' => '.banner .banner-form',
        'render_callback' => 'travel_agency_get_banner_search',
    ) );
    
    /** Title */
    $wp_customize->add_setting(
        'banner_title',
        array(
            'default'           => __( 'Find Your Best Holiday', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'banner_title',
        array(
            'label'    => __( 'Title', 'travel-agency' ),
            'section'  => 'header_image',
            'type'     => 'text',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'banner_title', array(
        'selector' => '.banner .form-holder .text h2',
        'render_callback' => 'travel_agency_get_banner_title',
    ) );
    
    /** Sub Title */
    $wp_customize->add_setting(
        'banner_subtitle',
        array(
            'default'           => __( 'Find great adventure holidays and activities around the planet.', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'banner_subtitle',
        array(
            'label'    => __( 'Sub Title', 'travel-agency' ),
            'section'  => 'header_image',
            'type'     => 'text',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'banner_subtitle', array(
        'selector' => '.banner .form-holder .text .banner-content',
        'render_callback' => 'travel_agency_get_banner_sub_title',
    ) );

    /** Search Section */   
    $wp_customize->add_section( 'search_section', array(
        'title'    => __( 'Search Section', 'travel-agency' ),
        'priority' => 11,
        'panel'    => 'home_page_setting',
    ) ); 
    
    if( travel_agency_is_wte_advanced_search_active() ){
        /** Enable Search Bar */
        $wp_customize->add_setting(
            'ed_search_bar',
            array(
                'default'           => true,
                'sanitize_callback' => 'travel_agency_sanitize_checkbox',
            )
        );
        
        $wp_customize->add_control(
            'ed_search_bar',
            array(
                'section'     => 'search_section',
                'label'       => __( 'Enable Search Bar', 'travel-agency' ),
                'type'        => 'checkbox'
            )
        );
    }
    
    /** Blog Section */   
    $wp_customize->add_section( 'blog_section', array(
        'title'    => __( 'Blog Section', 'travel-agency' ),
        'priority' => 100,
        'panel'    => 'home_page_setting',
    ) ); 
    
    /** Enable/Disable BreadCrumb */
    $wp_customize->add_setting(
        'ed_blog_section',
        array(
            'default'           => true,
            'sanitize_callback' => 'travel_agency_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		'ed_blog_section',
		array(
			'section' => 'blog_section',
			'label'	  => __( 'Enable Blog Section', 'travel-agency' ),
            'type'    => 'checkbox'
		)		
	);
    
    /** Title */
    $wp_customize->add_setting(
        'blog_section_title',
        array(
            'default'           => __( 'Latest Articles', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'blog_section_title',
        array(
            'label'   => __( 'Title', 'travel-agency' ),
            'section' => 'blog_section',
            'type'    => 'text',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'blog_section_title', array(
        'selector' => '.blog-section .section-header .section-title',
        'render_callback' => 'travel_agency_get_blog_section_title',
    ) );
    
    /** Sub Title */
    $wp_customize->add_setting(
        'blog_section_subtitle',
        array(
            'default'           => __( 'Show your latest blog posts here. You can modify this section from Appearance > Customize > Home Page Settings > Blog Section.', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'blog_section_subtitle',
        array(
            'label'   => __( 'Sub Title', 'travel-agency' ),
            'section' => 'blog_section',
            'type'    => 'text',
        )
    );    
    
    $wp_customize->selective_refresh->add_partial( 'blog_section_subtitle', array(
        'selector' => '.blog-section .section-header .section-content',
        'render_callback' => 'travel_agency_get_blog_section_sub_title',
    ) );
    
    /** View All Label */
    $wp_customize->add_setting(
        'blog_view_all',
        array(
            'default'           => __( 'View All Posts', 'travel-agency' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'blog_view_all',
        array(
            'label'           => __( 'View All label', 'travel-agency' ),
            'section'         => 'blog_section',
            'type'            => 'text',
            'active_callback' => 'travel_agency_blog_view_all_ac'
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'blog_view_all', array(
        'selector' => '.blog-section .btn-holder .btn-more',
        'render_callback' => 'travel_agency_get_blog_view_all_btn',
    ) );
        
}
add_action( 'customize_register', 'travel_agency_customize_register_home' );

function travel_agency_blog_view_all_ac(){
    $blog = get_option( 'page_for_posts' );
    if( $blog ) return true;
    
    return false; 
}