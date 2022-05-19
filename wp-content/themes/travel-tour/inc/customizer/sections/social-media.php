<?php
/**
 * Social Media Sections
 *
 * @package Travel Tour Pro
 */
add_action( 'customize_register', 'travel_tour_social_media_sections' );

function travel_tour_social_media_sections( $wp_customize ) {

	$wp_customize->add_section( 'travel_tour_social_media_sections', array(
	    'title'          => esc_html__( 'Social Media', 'travel-tour' ),
	    'description'    => esc_html__( 'Social Media', 'travel-tour' ),
	    'panel'         => 'travel_tour_homepage_panel',
	    'priority'       => 10,
	) );

	$wp_customize->add_setting( new Travel_Tour_Repeater_Setting( $wp_customize, 'travel_tour_social_media', array(
        'default'     => '',
		'sanitize_callback' => array( 'Travel_Tour_Repeater_Setting', 'sanitize_repeater_setting' ),
    ) ) );
    
    $wp_customize->add_control( new Travel_Tour_Control_Repeater( $wp_customize, 'travel_tour_social_media', array(
		'section' => 'travel_tour_social_media_sections',
		'settings'    => 'travel_tour_social_media',
		'label'	  => esc_html__( 'Social Links', 'travel-tour' ),
		'fields' => array(
			'social_media_title' => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Social Media Title', 'travel-tour' ),
				'description' => esc_html__( 'This will be the label.', 'travel-tour' ),
				'default'     => '',
			),
			'social_media_class' => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Social Media Class', 'travel-tour' ),
				'default'     => '',
			),
			'social_media_link' => array(
				'type'      => 'url',
				'label'     => esc_html__( 'Social Media Links', 'travel-tour' ),
		        'default'   => '',
			),			
		),
        'row_label' => array(
			'type'  => 'field',
			'value' => esc_html__('Social Media', 'travel-tour' ),
			'field' => 'social_media_title',
		),                        
	) ) );
	
}