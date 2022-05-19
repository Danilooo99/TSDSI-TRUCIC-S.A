<?php
/**
 * Homepage Settings
 *
 * @package Travel Tour Pro
 */

add_action( 'customize_register', 'travel_tour_customize_register_homepage_panel' );

function travel_tour_customize_register_homepage_panel( $wp_customize ) {
	$wp_customize->add_panel( 'travel_tour_homepage_panel', array(
	    'priority'    => 10,
	    'title'       => esc_html__( 'Travel Tour Options', 'travel-tour' ),
	    'description' => esc_html__( 'Travel Tour Options', 'travel-tour' ),
	) );
}