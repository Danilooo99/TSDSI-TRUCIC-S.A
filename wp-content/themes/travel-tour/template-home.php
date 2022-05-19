<?php

// Template Name: Home
get_header();
$sections = get_theme_mod( 'travel_tour_sort_homepage', array(
    'banner-section',
    'slider-section',
    'trips-section',
    'about-section',
    'blog-section',
    'testimonial-section'
) );
if ( !empty($sections) && is_array( $sections ) ) {
    foreach ( $sections as $section ) {
        get_template_part( 'template-parts/home-sections/' . $section, $section );
    }
}
get_footer();