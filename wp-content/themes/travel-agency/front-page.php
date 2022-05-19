<?php
/**
 * Front Page Template
 * 
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Travel_Agency
 */

get_header(); 

$home_sections = travel_agency_get_homepage_section();

if ( 'posts' == get_option( 'show_on_front' ) ) { //Show Static Blog Page
    include( get_home_template() );
    get_sidebar();
}elseif( $home_sections ){ 
    
    //If any one section are enabled then show custom home page.
    foreach( $home_sections as $section ){
        travel_agency_get_template_part( esc_attr( $section ) );  
    }
    
}else {
    //If all section are disabled then show respective page template. 
    include( get_page_template() );
    get_sidebar();
}

get_footer();