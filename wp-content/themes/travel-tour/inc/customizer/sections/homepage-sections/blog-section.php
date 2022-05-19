<?php
/**
 * Blog Settings
 *
 * @package Travel Tour Pro
 */

add_action( 'customize_register', 'travel_tour_customize_register_blog_post' );

function travel_tour_customize_register_blog_post( $wp_customize ) {
	$wp_customize->add_section( 'travel_tour_blog_post_sections', array(
	    'title'          => esc_html__( 'Blog Posts', 'travel-tour' ),
	    'description'    => esc_html__( 'Blog Posts :', 'travel-tour' ),
	    'panel'          => 'travel_tour_homepage_panel',
	    'priority'       => 160,
	) );

    $wp_customize->add_setting( 'blog_post_display_option', array(
      'sanitize_callback'     =>  'travel_tour_sanitize_checkbox',
      'default'               =>  true
    ) );

    $wp_customize->add_control( new Travel_Tour_Toggle_Control( $wp_customize, 'blog_post_display_option', array(
      'label' => esc_html__( 'Hide / Show','travel-tour' ),
      'section' => 'travel_tour_blog_post_sections',
      'settings' => 'blog_post_display_option',
      'type'=> 'toggle',
    ) ) );

    $wp_customize->add_setting( 'blog_post_category', array(
        'capability'  => 'edit_theme_options',        
        'sanitize_callback' => 'sanitize_text_field',
        'default'     => '',
    ) );

    $wp_customize->add_control( new Travel_Tour_Customize_Dropdown_Taxonomies_Control( $wp_customize, 'blog_post_category', array(
        'label' => esc_html__( 'Choose Category', 'travel-tour' ),
        'section' => 'travel_tour_blog_post_sections',
        'settings' => 'blog_post_category',
        'type'=> 'dropdown-taxonomies',
        'taxonomy'  =>  'category'
    ) ) );

    $wp_customize->add_setting( 'blog_post_section_title', array(
        'sanitize_callback'     =>  'sanitize_text_field',
        'default'               =>  ''
    ) );

    $wp_customize->add_control( 'blog_post_section_title', array(
        'label' => esc_html__( 'Title', 'travel-tour' ),
        'section' => 'travel_tour_blog_post_sections',
        'settings' => 'blog_post_section_title',
        'type'=> 'text',
    ) );

    $wp_customize->add_setting( 'number_of_blog_post', array(
        'sanitize_callback'     =>  'sanitize_text_field',
        'default'               =>  3
    ) );

    $wp_customize->add_control( 'number_of_blog_post', array(
        'label' => esc_html__( 'Number of posts', 'travel-tour' ),
        'section' => 'travel_tour_blog_post_sections',
        'settings' => 'number_of_blog_post',
        'type'=> 'text',
        'description'   =>  'put -1 for unlimited'
    ) );

}