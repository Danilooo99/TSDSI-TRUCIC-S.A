<?php

/**
 * About Settings
 *
 * @package Travel Tour Pro
 */
add_action( 'customize_register', 'chic_lifestyle_customize_register_about_section' );
function chic_lifestyle_customize_register_about_section( $wp_customize )
{
    $wp_customize->add_section( 'travel_tour_about_sections', array(
        'title'       => esc_html__( 'About Section', 'travel-tour' ),
        'description' => esc_html__( 'About section :', 'travel-tour' ),
        'panel'       => 'travel_tour_homepage_panel',
        'priority'    => 160,
    ) );
    $wp_customize->add_setting( 'about_display_option', array(
        'sanitize_callback' => 'travel_tour_sanitize_checkbox',
        'default'           => true,
    ) );
    $wp_customize->add_control( new Travel_Tour_Toggle_Control( $wp_customize, 'about_display_option', array(
        'label'    => esc_html__( 'Hide / Show', 'travel-tour' ),
        'section'  => 'travel_tour_about_sections',
        'settings' => 'about_display_option',
        'type'     => 'toggle',
    ) ) );
    
    $wp_customize->add_setting('about_page', array(
        'sanitize_callback' => 'travel_tour_sanitize_select',
        'default' => ''
    ));
    $page_query = get_pages();
    $pages = array();
    $pages[''] = esc_attr__("-- Select --", 'travel-tour');
    foreach ($page_query as $page) {
        $pages[ $page->post_name ] = $page->post_title;
    }

    $wp_customize->add_control(new Travel_Tour_Select_Control($wp_customize, 'about_page', array(
        'label' => esc_html__('Select a page :', 'travel-tour'),
        'section' => 'travel_tour_about_sections',
        'settings' => 'about_page',
        'type' => 'select',
        'choices' => $pages,
    )));

    $choices = array(
        'one' => get_template_directory_uri() . '/images/homepage/about-layouts/one.jpg',
    );
    $wp_customize->add_setting( 'about_section_layout', array(
        'sanitize_callback' => 'travel_tour_sanitize_choices',
        'default'           => 'one',
    ) );
    $wp_customize->add_control( new Travel_Tour_Radio_Image_Control( $wp_customize, 'about_section_layout', array(
        'label'    => esc_html__( 'About Section Layout:', 'travel-tour' ),
        'section'  => 'travel_tour_about_sections',
        'settings' => 'about_section_layout',
        'type'     => 'radio-image',
        'choices'  => $choices,
    ) ) );
}
