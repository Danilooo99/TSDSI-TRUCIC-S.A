<?php
/**
 * Banner Settings
 *
 * @package Travel Tour Pro
 */
add_action( 'customize_register', 'chic_lifestyle_customize_banner_section' );

function chic_lifestyle_customize_banner_section( $wp_customize ) {

  $wp_customize->get_section( 'header_image' )->panel = "travel_tour_homepage_panel";
  $wp_customize->get_section( 'header_image' )->title = esc_html__( "Header / Banner", 'travel-tour' );

  $wp_customize->add_setting( 'banner_section_display_option', array(
      'sanitize_callback'     =>  'travel_tour_sanitize_checkbox',
      'default'               =>  true
  ) );

  $wp_customize->add_control( new Travel_Tour_Toggle_Control( $wp_customize, 'banner_section_display_option', array(
      'label' => esc_html__( 'Hide / Show','travel-tour' ),
      'section' => 'header_image',
      'settings' => 'banner_section_display_option',
      'type'=> 'toggle',
      'priority'  =>  1
  ) ) );

  $wp_customize->add_setting( 'banner_section_title', array(
      'sanitize_callback'     =>  'sanitize_text_field',
      'default'               =>  ''
  ) );

  $wp_customize->add_control( 'banner_section_title', array(
      'label' => esc_html__( 'Banner Title','travel-tour' ),
      'section' => 'header_image',
      'settings' => 'banner_section_title',
      'type'=> 'text',
      'priority'  =>  2
  ) );

  $wp_customize->add_setting( 'banner_section_description', array(
      'sanitize_callback'     =>  'sanitize_text_field',
      'default'               =>  ''
  ) );

  $wp_customize->add_control( 'banner_section_description', array(
      'label' => esc_html__( 'Description','travel-tour' ),
      'section' => 'header_image',
      'settings' => 'banner_section_description',
      'type'=> 'text',
      'priority'  =>  3
  ) );

}