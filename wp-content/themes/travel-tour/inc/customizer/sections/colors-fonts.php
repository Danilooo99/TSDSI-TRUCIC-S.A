<?php

/**
 * Colors and Fonts Settings
 *
 * @package Travel Tour Pro
 */
require get_template_directory() . '/inc/google-fonts.php';
add_action( 'customize_register', 'travel_tour_customize_colors' );
function travel_tour_customize_colors( $wp_customize )
{
    $wp_customize->add_setting( 'more_color_options', array(
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ) );
    $wp_customize->add_control( new Travel_Tour_Custom_Text( $wp_customize, 'more_color_options', array(
        'label'    => esc_html__( 'More color options available in Pro Version.', 'travel-tour' ),
        'section'  => 'colors',
        'settings' => 'more_color_options',
        'type'     => 'customtext',
    ) ) );
}

add_action( 'customize_register', 'travel_tour_customize_font_family' );
function travel_tour_customize_font_family( $wp_customize )
{
    $wp_customize->add_setting( 'font_family', array(
        'capability'        => 'edit_theme_options',
        'default'           => 'Montserrat',
        'sanitize_callback' => 'travel_tour_sanitize_google_fonts',
    ) );
    $wp_customize->add_control( 'font_family', array(
        'settings' => 'font_family',
        'label'    => esc_html__( 'Choose Font Family For Your Site', 'travel-tour' ),
        'section'  => 'colors',
        'type'     => 'select',
        'choices'  => google_fonts(),
        'priority' => 100,
    ) );
}

add_action( 'customize_register', 'travel_tour_customize_font_size' );
function travel_tour_customize_font_size( $wp_customize )
{
    $wp_customize->add_setting( 'font_size', array(
        'capability'        => 'edit_theme_options',
        'default'           => '13px',
        'sanitize_callback' => 'travel_tour_sanitize_select',
    ) );
    $wp_customize->add_control( 'font_size', array(
        'settings' => 'font_size',
        'label'    => esc_html__( 'Choose Font Size', 'travel-tour' ),
        'section'  => 'colors',
        'type'     => 'select',
        'default'  => '13px',
        'choices'  => array(
        '13px' => '13px',
        '14px' => '14px',
        '15px' => '15px',
        '16px' => '16px',
        '17px' => '17px',
        '18px' => '18px',
    ),
        'priority' => 101,
    ) );
}

add_action( 'customize_register', 'travel_tour_font_weight' );
function travel_tour_font_weight( $wp_customize )
{
    $wp_customize->add_setting( 'travel_tour_font_weight', array(
        'default'           => 400,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( new Travel_Tour_Slider_Control( $wp_customize, 'travel_tour_font_weight', array(
        'section'  => 'colors',
        'settings' => 'travel_tour_font_weight',
        'label'    => esc_html__( 'Font Weight', 'travel-tour' ),
        'priority' => 102,
        'choices'  => array(
        'min'  => 100,
        'max'  => 900,
        'step' => 100,
    ),
    ) ) );
}

add_action( 'customize_register', 'travel_tour_line_height' );
function travel_tour_line_height( $wp_customize )
{
    $wp_customize->add_setting( 'travel_tour_line_height', array(
        'default'           => 22,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( new Travel_Tour_Slider_Control( $wp_customize, 'travel_tour_line_height', array(
        'section'  => 'colors',
        'settings' => 'travel_tour_line_height',
        'label'    => esc_html__( 'Line Height', 'travel-tour' ),
        'priority' => 102,
        'choices'  => array(
        'min'  => 13,
        'max'  => 53,
        'step' => 1,
    ),
    ) ) );
}

add_action( 'customize_register', 'travel_tour_heading_options' );
function travel_tour_heading_options( $wp_customize )
{
    $wp_customize->add_setting( 'heading_options_text', array(
        'default'           => '',
        'type'              => 'customtext',
        'capability'        => 'edit_theme_options',
        'transport'         => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( new Travel_Tour_Custom_Text( $wp_customize, 'heading_options_text', array(
        'label'    => esc_html__( 'Heading Options :', 'travel-tour' ),
        'section'  => 'colors',
        'settings' => 'heading_options_text',
        'priority' => 103,
    ) ) );
}

add_action( 'customize_register', 'travel_tour_heading_font_family' );
function travel_tour_heading_font_family( $wp_customize )
{
    $wp_customize->add_setting( 'heading_font_family', array(
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'travel_tour_sanitize_google_fonts',
        'default'           => 'Montserrat',
    ) );
    $wp_customize->add_control( 'heading_font_family', array(
        'settings' => 'heading_font_family',
        'label'    => esc_html__( 'Font Family', 'travel-tour' ),
        'section'  => 'colors',
        'type'     => 'select',
        'choices'  => google_fonts(),
        'priority' => 103,
    ) );
}

add_action( 'customize_register', 'travel_tour_heading_font_weight' );
function travel_tour_heading_font_weight( $wp_customize )
{
    $wp_customize->add_setting( 'heading_font_weight', array(
        'default'           => 400,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( new Travel_Tour_Slider_Control( $wp_customize, 'heading_font_weight', array(
        'section'  => 'colors',
        'settings' => 'heading_font_weight',
        'label'    => esc_html__( 'Font Weight', 'travel-tour' ),
        'priority' => 103,
        'choices'  => array(
        'min'  => 100,
        'max'  => 900,
        'step' => 100,
    ),
    ) ) );
}

add_action( 'customize_register', 'travel_tour_heading_font_style' );
function travel_tour_heading_font_style( $wp_customize )
{
    $default_size = array(
        '1' => 35,
        '2' => 32,
        '3' => 18,
        '4' => 17,
        '5' => 16,
        '6' => 12,
    );
    for ( $i = 1 ;  $i <= 6 ;  $i++ ) {
        $wp_customize->add_setting( 'travel_tour_heading_' . $i . '_size', array(
            'default'           => $default_size[$i],
            'sanitize_callback' => 'absint',
        ) );
        $wp_customize->add_control( 'travel_tour_heading_' . $i . '_size', array(
            'type'        => 'number',
            'section'     => 'colors',
            'label'       => esc_html__( 'Heading ', 'travel-tour' ) . $i . esc_html__( ' Size', 'travel-tour' ),
            'priority'    => 104,
            'input_attrs' => array(
            'min'  => 10,
            'max'  => 50,
            'step' => 1,
        ),
        ) );
    }
}
