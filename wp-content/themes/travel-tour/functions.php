<?php

/**
 * travel-tour functions and definitions
 *
 * @package Travel Tour Pro
 */


if ( !function_exists( 'travel_tour_setup' ) ) {
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function travel_tour_setup()
    {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on travel-tour, use a find and replace
         * to change 'travel-tour' to the name of your theme in all the template files
         */
        load_theme_textdomain( 'travel-tour' );
        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );
        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support( 'title-tag' );
        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
         */
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'post-templates' );
        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'primary' => esc_html__( 'Primary Menu', 'travel-tour' ),
        ) );
        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption'
        ) );
        /*
         * Enable support for Post Formats.
         * See http://codex.wordpress.org/Post_Formats
         */
        add_theme_support( 'custom-logo', array(
            'height'     => 90,
            'width'      => 400,
            'flex-width' => true,
        ) );
        // Set up the WordPress core custom background feature.
        add_theme_support( 'custom-background', apply_filters( 'travel_tour_custom_background_args', array(
            'default-color' => 'ffffff',
            'default-image' => '',
        ) ) );
        add_theme_support( "custom-header", array(
            'default-color' => 'ffffff',
        ) );
        add_editor_style();
        // Define Image Sizes:
        add_image_size(
            'travel-tour-slider',
            256,
            350,
            true
        );
    }
    
    // travel_tour_setup
}

add_action( 'after_setup_theme', 'travel_tour_setup' );
/**
 * Enqueue scripts and styles.
 */
function travel_tour_scripts()
{
    $font_family = get_theme_mod( 'font_family', 'Raleway' );
    $heading_font_family = get_theme_mod( 'heading_font_family', 'Raleway' );
    wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.css' );
    wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.css' );
    wp_enqueue_style( 'animate', get_template_directory_uri() . '/css/animate.css' );
    wp_enqueue_style( 'owl', get_template_directory_uri() . '/css/owl.carousel.css' );
    wp_enqueue_style( 'travel-tour-googlefonts', '//fonts.googleapis.com/css?family=' . esc_attr( $font_family ) . ':200,300,400,500,600,700,800,900|' . $heading_font_family . ':200,300,400,500,600,700,800,900' );
    wp_enqueue_style( 'travel-tour-style', get_stylesheet_uri() );
    
    if ( is_rtl() ) {
        wp_enqueue_style( 'travel-tour-style', get_stylesheet_uri() );
        wp_style_add_data( 'travel-tour-style', 'rtl', 'replace' );
        wp_enqueue_style( 'travel-tour-css-rtl', get_template_directory_uri() . '/css/bootstrap-rtl.css' );
        wp_enqueue_script(
            'travel-tour-js-rtl',
            get_template_directory_uri() . '/js/bootstrap.rtl.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );
    }
    
    wp_enqueue_script(
        'bootstrap',
        get_template_directory_uri() . '/js/bootstrap.js',
        array( 'jquery' ),
        '5.0.0',
        true
    );
    wp_enqueue_script(
        'wow',
        get_template_directory_uri() . '/js/wow.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
    wp_enqueue_script(
        'owl',
        get_template_directory_uri() . '/js/owl.carousel.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
    wp_enqueue_script(
        'travel-tour-scripts',
        get_template_directory_uri() . '/js/script.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

add_action( 'wp_enqueue_scripts', 'travel_tour_scripts' );
/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
if ( !isset( $content_width ) ) {
    $content_width = 900;
}
function travel_tour_content_width()
{
    $GLOBALS['content_width'] = apply_filters( 'travel_tour_content_width', 640 );
}

add_action( 'after_setup_theme', 'travel_tour_content_width', 0 );
/**
* Call Widget page
**/
require get_template_directory() . '/inc/widgets/widgets.php';
/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';
/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';
require get_template_directory() . '/inc/custom-controls/custom-control.php';
/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/customizer.php';
// Register Custom Navigation Walker
require get_template_directory() . '/inc/wp_bootstrap_navwalker.php';
/**
 * Demo Content Section
 */
require get_template_directory() . '/inc/demo-content.php';
/**
 * Recommended Plugins
 */
require get_template_directory() . '/inc/tgmpa/recommended-plugins.php';
require get_template_directory() . '/inc/dynamic-css.php';
// Remove default "Category or Tags" from title
add_filter( 'get_the_archive_title', 'travel_tour_remove_defalut_tax_title' );
function travel_tour_remove_defalut_tax_title( $title )
{
    
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    }
    
    return $title;
}

// add classes for post_class function
add_filter(
    'post_class',
    'travel_tour_sticky_classes',
    10,
    3
);
function travel_tour_sticky_classes( $classes, $class, $post_id )
{
    $classes[] = 'col-sm-12 eq-blocks';
    return $classes;
}

function travel_tour_get_trip_info( $post_id )
{
    if ( !class_exists( 'Wp_Travel_Engine' ) ) {
        return;
    }
    $trip_info = get_post_meta( $post_id, 'wp_travel_engine_setting', true );
    return $trip_info;
}

function travel_tour_trip_settings()
{
    if ( !class_exists( 'Wp_Travel_Engine' ) ) {
        return;
    }
    $trip_settings = get_option( 'wp_travel_engine_settings', true );
    return $trip_settings;
}
