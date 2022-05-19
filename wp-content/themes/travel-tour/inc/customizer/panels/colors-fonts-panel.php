<?php
/**
 * Color and Fonts Settings
 *
 * @package Travel Tour Pro
 */


add_action( 'customize_register', 'travel_tour_change_colors_panel_title' );


function travel_tour_change_colors_panel_title( $wp_customize)  {
	$wp_customize->get_section('colors')->title = esc_html__( 'Colors and Fonts', 'travel-tour' );
	$wp_customize->get_section('colors')->priority = 10;
}