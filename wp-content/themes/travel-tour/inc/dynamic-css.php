<?php

function travel_tour_dynamic_css()
{
    wp_enqueue_style( 'dynamic-css', get_template_directory_uri() . '/css/dynamic.css' );
    $font_family = esc_attr( get_theme_mod( 'font_family', 'Montserrat' ) );
    $font_size = esc_attr( get_theme_mod( 'font_size', '14px' ) );
    $font_weight = absint( get_theme_mod( 'travel_tour_font_weight', 400 ) );
    $line_height = absint( get_theme_mod( 'travel_tour_line_height', 22 ) );
    $logo_size = absint( get_theme_mod( 'travel_tour_logo_size', 60 ) );
    $logo_font_size = absint( $logo_size / 10 );
    $heading_font_family = esc_attr( get_theme_mod( 'heading_font_family', 'Montserrat' ) );
    $heading_font_weight = esc_attr( get_theme_mod( 'heading_font_weight', 400 ) );
    $default_size = array(
        '1' => 35,
        '2' => 32,
        '3' => 18,
        '4' => 17,
        '5' => 16,
        '6' => 12,
    );
    for ( $i = 1 ;  $i <= 6 ;  $i++ ) {
        $heading[$i] = absint( get_theme_mod( 'travel_tour_heading_' . $i . '_size', absint( $default_size[$i] ) ) );
    }
    $dynamic_css = "\n                body{ font: {$font_weight} {$font_size}/{$line_height}" . "px {$font_family};}\n                section.logo img{ height: {$logo_size}" . "px; }\n                section.logo h1{ font-size: {$logo_font_size}" . "px; }\n                \n                h1{ font: {$heading_font_weight} {$heading[1]}" . "px {$heading_font_family} }\n                h2{ font: {$heading_font_weight} {$heading[2]}" . "px {$heading_font_family} }\n                h3{ font: {$heading_font_weight} {$heading[3]}" . "px {$heading_font_family} }\n                h4{ font: {$heading_font_weight} {$heading[4]}" . "px {$heading_font_family} }\n                h5{ font: {$font_weight} {$heading[5]}" . "px {$font_family} }\n                h6{ font: {$font_weight} {$heading[6]}" . "px {$font_family} }              \n        ";
    wp_add_inline_style( 'dynamic-css', $dynamic_css );
}

add_action( 'wp_enqueue_scripts', 'travel_tour_dynamic_css' );