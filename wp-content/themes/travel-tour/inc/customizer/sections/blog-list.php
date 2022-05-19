<?php
/**
 * Blog List Settings
 *
 * @package Travel Tour Pro
 */


add_action( 'customize_register', 'travel_tour_customize_blog_list_option' );

function travel_tour_customize_blog_list_option( $wp_customize ) {

    $wp_customize->add_section( 'travel_tour_blog_list_section', array(
        'title'          => esc_html__( 'Blog List Options', 'travel-tour' ),
        'description'    => esc_html__( 'Blog List Options', 'travel-tour' ),
        'priority'       => 10,
    ) );

    $wp_customize->add_setting( 'blog_post_layout', array(
        'capability'  => 'edit_theme_options',        
        'sanitize_callback' => 'travel_tour_sanitize_choices',
        'default'     => 'sidebar-right',
    ) );

    $wp_customize->add_control( new Travel_Tour_Radio_Buttonset_Control( $wp_customize, 'blog_post_layout', array(
        'label' => esc_html__( 'Layouts :', 'travel-tour' ),
        'section' => 'travel_tour_blog_list_section',
        'settings' => 'blog_post_layout',
        'type'=> 'radio-buttonset',
        'choices'     => array(
            'sidebar-left' => esc_html__( 'Sidebar at left', 'travel-tour' ),
            'no-sidebar'    =>  esc_html__( 'No sidebar', 'travel-tour' ),
            'sidebar-right' => esc_html__( 'Sidebar at right', 'travel-tour' ),            
        ),
    ) ) );


    $wp_customize->add_setting( 'blog_post_view', array(
        'capability'  => 'edit_theme_options',        
        'sanitize_callback' => 'travel_tour_sanitize_choices',
        'default'     => 'list-view',
    ) );

    $wp_customize->add_control( new Travel_Tour_Radio_Buttonset_Control( $wp_customize, 'blog_post_view', array(
        'label' => esc_html__( 'Post View :', 'travel-tour' ),
        'section' => 'travel_tour_blog_list_section',
        'settings' => 'blog_post_view',
        'type'=> 'radio-buttonset',
        'choices'     => array(
            'grid-view' => esc_html__( 'Grid View', 'travel-tour' ),
            'list-view' => esc_html__( 'List View', 'travel-tour' ),
            'full-width-view' => esc_html__( 'Fullwidth View', 'travel-tour' ),
        ),
    ) ) );


    $wp_customize->add_setting( 'blog_post_show_hide_details', array(
        'capability'  => 'edit_theme_options',        
        'sanitize_callback' => 'travel_tour_sanitize_array',
        'default'     => array( 'date', 'categories', 'tags' ),
    ) );

    $wp_customize->add_control( new Travel_Tour_Multi_Check_Control( $wp_customize, 'blog_post_show_hide_details', array(
        'label' => esc_html__( 'Hide / Show Details', 'travel-tour' ),
        'section' => 'travel_tour_blog_list_section',
        'settings' => 'blog_post_show_hide_details',
        'type'=> 'multi-check',
        'choices'     => array(
            'author' => esc_html__( 'Show post author', 'travel-tour' ),
            'date' => esc_html__( 'Show post date', 'travel-tour' ),     
            'categories' => esc_html__( 'Show Categories', 'travel-tour' ),
            'tags' => esc_html__( 'Show Tags', 'travel-tour' ),
            'number_of_comments' => esc_html__( 'Show number of comments', 'travel-tour' ),
        ),
    ) ) );
}