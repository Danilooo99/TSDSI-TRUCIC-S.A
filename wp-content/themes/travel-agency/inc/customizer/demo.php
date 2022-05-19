<?php
/**
 * Travel Agency Demo Content Information
 *
 * @package lawyer_landing_page
 */

function travel_agency_customizer_demo_content( $wp_customize ) {
	
    $wp_customize->add_section( 
        'theme_demo_content',
        array(
            'title'    => __( 'Demo Content Import', 'travel-agency' ),
            'priority' => 7,
		)
    );
        
    $wp_customize->add_setting(
		'demo_content_instruction',
		array(
			'sanitize_callback' => 'wp_kses_post'
		)
	);

	$demo_content_description = '<div class="customizer-custom">';
    $demo_content_description .= '<span class="sticky_info_row"><label class="row-element">' . __( 'Demo Import Tutorial', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://rarathemes.com/blog/import-demo-content-rara-themes/' ) . '" target="_blank">' . __( 'here', 'travel-agency' ) . '</a></span>';
	$demo_content_description .= '</div>';
    
	$wp_customize->add_control(
		new Travel_Agency_Info_Text( 
			$wp_customize,
			'demo_content_instruction',
			array(
				'label'       => __( 'About Demo Import' , 'travel-agency' ),
				'section'     => 'theme_demo_content',
				'description' => $demo_content_description
			)
		)
	);
    
	$theme_demo_content_desc = '<div class="customizer-custom">';

	if( ! class_exists( 'RDDI_init' ) ){
		$theme_demo_content_desc .= '<span class="sticky_info_row"><label class="row-element">' . __( 'Plugin required', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://wordpress.org/plugins/rara-one-click-demo-import/' ) . '" target="_blank">' . __( 'Rara One Click Demo Import', 'travel-agency' ) . '</a></span><br />';
	}

	$theme_demo_content_desc .= '<span class="sticky_info_row download-link"><label class="row-element">' . __( 'Download Demo Content Zip File', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://docs.rarathemes.com/docs/travel-agency/theme-installation-activation/how-to-import-demo-content/' ) . '" target="_blank" rel="no-follow">' . __( 'here', 'travel-agency' ) . '</a></span><br />';

	$theme_demo_content_desc .= '</div>';
	$wp_customize->add_setting( 
        'theme_demo_content_info',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
		)
    );

	// Demo content 
	$wp_customize->add_control( 
        new Travel_Agency_Info_Text( 
            $wp_customize,
            'theme_demo_content_info',
            array(
                'section'     => 'theme_demo_content',
                'description' => $theme_demo_content_desc
    		)
        )
    );

}
add_action( 'customize_register', 'travel_agency_customizer_demo_content' );