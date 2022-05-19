<?php
/**
 * Partials for Selective Refresh
 *
 * @package Travel_Agency
 */

if( ! function_exists( 'travel_agency_get_header_phone' ) ) :
/**
 * Prints phone number in header
*/
function travel_agency_get_header_phone(){
    return esc_html( get_theme_mod( 'phone', __( '(888) 123-45678', 'travel-agency' ) ) );
}
endif;

if( ! function_exists( 'travel_agency_get_phone_label' ) ) :
/**
 * Prints phone label
*/
function travel_agency_get_phone_label(){
    return esc_html( get_theme_mod( 'phone_label', __( 'Call us, we are open 24/7', 'travel-agency' ) ) );
}
endif;

if( ! function_exists( 'travel_agency_get_banner_title' ) ) :
/**
 * Display Banner title
*/
function travel_agency_get_banner_title(){
    return esc_html( get_theme_mod( 'banner_title', __( 'Find Your Best Holiday', 'travel-agency' ) ) );
}
endif;

if( ! function_exists( 'travel_agency_get_banner_sub_title' ) ) :
/**
 * Display Banner sub-title
*/
function travel_agency_get_banner_sub_title(){
   return wpautop( wp_kses_post( get_theme_mod( 'banner_subtitle', __( 'Find great adventure holidays and activities around the planet.', 'travel-agency' ) ) ) );
    
}
endif;

if( ! function_exists( 'travel_agency_get_banner_search' ) ) :
/**
 * Display search form in banner
*/
function travel_agency_get_banner_search(){
    $ed_search = get_theme_mod( 'ed_banner_search', true );
    if( $ed_search ) get_search_form();
}
endif;

if( ! function_exists( 'travel_agency_get_blog_section_title' ) ) :
/**
 * Display blog section title
*/
function travel_agency_get_blog_section_title(){
    return esc_html( get_theme_mod( 'blog_section_title', __( 'Latest Articles', 'travel-agency' ) ) );
}
endif;

if( ! function_exists( 'travel_agency_get_blog_section_sub_title' ) ) :
/**
 * Display blog section sub-title
*/
function travel_agency_get_blog_section_sub_title(){
    return wpautop( wp_kses_post( get_theme_mod( 'blog_section_subtitle', __( 'Show your latest blog posts here. You can modify this section from Appearance > Customize > Home Page Settings > Blog Section.', 'travel-agency' ) ) ) );
    
}
endif;

if( ! function_exists( 'travel_agency_get_blog_view_all_btn' ) ) :
/**
 * Display blog view all button
*/
function travel_agency_get_blog_view_all_btn(){
    return esc_html( get_theme_mod( 'blog_view_all', __( 'View All Posts', 'travel-agency' ) ) );
}
endif;

if( ! function_exists( 'travel_agency_get_related_title' ) ) :
/**
 * Display blog view all button
*/
function travel_agency_get_related_title(){
    return esc_html( get_theme_mod( 'related_title', __( 'You may also like...', 'travel-agency' ) ) );
}
endif;

if( ! function_exists( 'travel_agency_get_readmore_btn' ) ) :
/**
 * Display blog view all button
*/
function travel_agency_get_readmore_btn(){
    return esc_html( get_theme_mod( 'readmore', __( 'Read More', 'travel-agency' ) ) );
}
endif;

if( ! function_exists( 'travel_agency_get_footer_copyright' ) ) :
/**
 * Prints footer copyright
*/
function travel_agency_get_footer_copyright(){
    $copyright = get_theme_mod( 'footer_copyright' );
    echo '<span class="copyright">';
    if( $copyright ){
        echo wp_kses_post( $copyright );
    }else{
        esc_html_e( '&copy; Copyright ', 'travel-agency' ); 
        echo date_i18n( esc_html__( 'Y', 'travel-agency' ) );
        echo ' <a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>. ';    
    }    
    echo '</span>';
}
endif; 