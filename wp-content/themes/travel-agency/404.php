<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Travel_Agency
 */

get_header(); ?>
    
    <h2><?php esc_html_e( 'Sorry, The Page Not Found', 'travel-agency' ); ?></h2>
	
    <p><?php esc_html_e( 'Can&rsquo;t find what you need? Take a moment and do a search below or start from our', 'travel-agency' );?> 
	
    <a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( 'Homepage', 'travel-agency' ); ?></a></p>
    
    <?php 
    
    get_search_form(); 
    
get_footer();