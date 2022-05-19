<?php
/**
 * Travel Agency custom functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Travel_Agency
 */

if ( ! function_exists( 'travel_agency_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function travel_agency_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Travel Agency, use a find and replace
	 * to change 'travel-agency' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'travel-agency', get_template_directory() . '/languages' );

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
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', 'travel-agency' ),
        'footer'  => esc_html__( 'Footer', 'travel-agency' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-list',
		'gallery',
		'caption',
	) );
    
    //Custom Header
    add_theme_support( 'custom-header', apply_filters( 'travel_agency_custom_header_args', array(
        'default-image'      => get_template_directory_uri() . '/images/banner-img.jpg',
        'width'              => 1920,
        'height'             => 680,
        'default-text-color' => '000',
        'header-text'        => false,
    ) ) );
    
    register_default_headers( array(
		'default-image' => array(
			'url'           => '%s/images/banner-img.jpg',
			'thumbnail_url' => '%s/images/banner-img.jpg',
			'description'   => __( 'Default Header Image', 'travel-agency' ),
		),
	) );
    
	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'travel_agency_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );
    
    /** Custom Logo */
    add_theme_support( 'custom-logo', array(    	
    	'header-text' => array( 'site-title', 'site-description' ),
    ) );
    
    /** Image Sizes */
    add_image_size( 'travel-agency-full', 1290, 540, true );
    add_image_size( 'travel-agency-popular', 630, 630, true );
    add_image_size( 'travel-agency-popular-small', 300, 300, true );
    add_image_size( 'travel-agency-blog', 410, 250, true );
    add_image_size( 'travel-agency-related', 280, 170, true );
    add_image_size( 'travel-agency-recent', 300, 170, true );
    add_image_size( 'travel-agency-schema', 600, 60 );
        
    /** Starter Content */
    $starter_content = array(
        // Specify the core-defined pages to create and add custom thumbnails to some of them.
		'posts' => array( 'home', 'blog' ),
		
        // Default to a static front page and assign the front and posts pages.
		'options' => array(
			'show_on_front' => 'page',
			'page_on_front' => '{{home}}',
			'page_for_posts' => '{{blog}}',
		),
        
        // Set up nav menus for each of the two areas registered in the theme.
		'nav_menus' => array(
			// Assign a menu to the "top" location.
			'primary' => array(
				'name' => __( 'Primary', 'travel-agency' ),
				'items' => array(
					'page_home',
					'page_blog'
				)
			)
		),
    );
    
    $starter_content = apply_filters( 'travel_agency_starter_content', $starter_content );

	add_theme_support( 'starter-content', $starter_content );
    
    // Add theme support for Responsive Videos.
	add_theme_support( 'jetpack-responsive-videos' );    

    // Add theme support for WooCommerce.
    add_theme_support( 'woocommerce' );

    if( class_exists( 'woocommerce' ) ) {
        global $woocommerce;
        
        if( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
            add_theme_support( 'wc-product-gallery-zoom' );
            add_theme_support( 'wc-product-gallery-lightbox' );
            add_theme_support( 'wc-product-gallery-slider' );
        }    
    }

    remove_theme_support( 'widgets-block-editor' );
}
endif;
add_action( 'after_setup_theme', 'travel_agency_setup' );

if( ! function_exists( 'travel_agency_content_width' ) ) :
/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function travel_agency_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'travel_agency_content_width', 910 );
}
endif;
add_action( 'after_setup_theme', 'travel_agency_content_width', 0 );

if( ! function_exists( 'travel_agency_template_redirect_content_width' ) ) :
/**
 * Adjust content_width value according to template.
 *
 * @return void
*/
function travel_agency_template_redirect_content_width(){
	// Full Width in the absence of sidebar.
	if( is_page() ){
	   $sidebar_layout = travel_agency_sidebar_layout();
       if( ( $sidebar_layout == 'no-sidebar' ) || ! is_active_sidebar( 'sidebar' ) ) $GLOBALS['content_width'] = 1290;        
	}elseif ( ! ( is_active_sidebar( 'sidebar' ) ) ) {
		$GLOBALS['content_width'] = 1290;
	}
}
endif;
add_action( 'template_redirect', 'travel_agency_template_redirect_content_width' );

if( ! function_exists( 'travel_agency_scripts' ) ) :
/**
 * Enqueue scripts and styles.
 */
function travel_agency_scripts() {
	// Use minified libraries if SCRIPT_DEBUG is turned off
    $build  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    
    wp_enqueue_style( 'animate', get_template_directory_uri(). '/css' . $build . '/animate' . $suffix . '.css', array(), TRAVEL_AGENCY_THEME_VERSION );
    wp_enqueue_style( 'travel-agency-google-fonts', travel_agency_fonts_url() );
    wp_enqueue_style( 'travel-agency-style', get_stylesheet_uri(), array(), TRAVEL_AGENCY_THEME_VERSION );

    wp_enqueue_script( 'wow', get_template_directory_uri() . '/js' . $build . '/wow' . $suffix . '.js', array( 'jquery' ), TRAVEL_AGENCY_THEME_VERSION, true );
	wp_enqueue_script( 'travel-agency-modal-accessibility', get_template_directory_uri() . '/js' . $build . '/modal-accessibility' . $suffix . '.js', array( 'jquery' ), TRAVEL_AGENCY_THEME_VERSION, true );
    wp_enqueue_script( 'all', get_template_directory_uri() . '/js' . $build . '/all' . $suffix . '.js', array( 'jquery' ), '5.6.3', true );
    wp_enqueue_script( 'v4-shims', get_template_directory_uri() . '/js' . $build . '/v4-shims' . $suffix . '.js', array( 'jquery' ), '5.6.3', true );
    wp_enqueue_script( 'travel-agency-custom', get_template_directory_uri() . '/js' . $build . '/custom' . $suffix . '.js', array( 'jquery' ), TRAVEL_AGENCY_THEME_VERSION, true );
    
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
endif;
add_action( 'wp_enqueue_scripts', 'travel_agency_scripts' );

if( ! function_exists( 'travel_agency_admin_scripts' ) ) :
/**
 * Enqueue admin scripts and styles.
*/
function travel_agency_admin_scripts(){
    wp_enqueue_style( 'travel-agency-admin', get_template_directory_uri() . '/inc/css/admin.css', '', TRAVEL_AGENCY_THEME_VERSION );
}
endif; 
add_action( 'admin_enqueue_scripts', 'travel_agency_admin_scripts' );

if( ! function_exists( 'travel_agency_body_classes' ) ) :
/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function travel_agency_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
    
    // Adds a class of custom-background-image to sites with a custom background image.
	if ( get_background_image() ) {
		$classes[] = 'custom-background-image custom-background';
	}
    
    // Adds a class of custom-background-color to sites with a custom background color.
    if ( get_background_color() != 'ffffff' ) {
		$classes[] = 'custom-background-color custom-background';
	}
    
    if( ! is_active_sidebar( 'sidebar' ) && ! is_singular( 'trip' ) ){
        $classes[] = 'full-width'; 
    }
    
    if( is_page() ){
        $sidebar_layout = travel_agency_sidebar_layout();
        if( $sidebar_layout == 'right-sidebar' ){
            $classes[] = 'rightsidebar';
        }elseif( $sidebar_layout == 'no-sidebar' ){
            $classes[] = 'full-width';
        }
    }    
    
    if( is_post_type_archive( 'trip' ) ){
        $classes[] = 'full-width';
    } 

    if( is_post_type_archive( 'trip' ) ){
        $classes[] = 'full-width';
    } 
    if ( has_nav_menu( 'footer' ) && get_privacy_policy_url() ) {
        $classes[] = 'footer-menu-privacy';
    }
	return $classes;
}
endif;
add_filter( 'body_class', 'travel_agency_body_classes' );

if( ! function_exists( 'travel_agency_post_classes' ) ) :
/**
 * Adds custom class in post class1
*/
function travel_agency_post_classes( $classes ){
    if( is_search() ){
        $classes[] = 'post';
    }
    
    return $classes;    
}
endif;
add_filter( 'post_class', 'travel_agency_post_classes' );

if( ! function_exists( 'travel_agency_pingback_header' ) ) :
/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function travel_agency_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
endif;
add_action( 'wp_head', 'travel_agency_pingback_header' );

if ( ! function_exists( 'travel_agency_excerpt_more' ) ) :
/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... 
 */
function travel_agency_excerpt_more( $more ) {
	return is_admin() ? $more : ' &hellip; ';
}
endif;
add_filter( 'excerpt_more', 'travel_agency_excerpt_more' );

if ( ! function_exists( 'travel_agency_excerpt_length' ) ) :
/**
 * Changes the default 55 character in excerpt 
*/
function travel_agency_excerpt_length( $length ) {
	return is_admin() ? $length : 45;    
}
endif;
add_filter( 'excerpt_length', 'travel_agency_excerpt_length', 999 );

if( ! function_exists( 'travel_agency_change_comment_form_default_fields' ) ) :
/**
 * Change Comment form default fields i.e. author, email & url.
 * https://blog.josemcastaneda.com/2016/08/08/copy-paste-hurting-theme/
*/
function travel_agency_change_comment_form_default_fields( $fields ){    
    // get the current commenter if available
    $commenter = wp_get_current_commenter();
 
    // core functionality
    $req      = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );
    $required = ( $req ? " required" : '' );
    $author   = ( $req ? __( 'Name*', 'travel-agency' ) : __( 'Name', 'travel-agency' ) );
    $email    = ( $req ? __( 'Email*', 'travel-agency' ) : __( 'Email', 'travel-agency' ) );
 
    // Change just the author field
    $fields['author'] = '<p class="comment-form-author"><label class="screen-reader-text" for="author">' . esc_html__( 'Name', 'travel-agency' ) . '<span class="required">*</span></label><input id="author" name="author" placeholder="' . esc_attr( $author ) . '" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . $required . ' /></p>';
    
    $fields['email'] = '<p class="comment-form-email"><label class="screen-reader-text" for="email">' . esc_html__( 'Email', 'travel-agency' ) . '<span class="required">*</span></label><input id="email" name="email" placeholder="' . esc_attr( $email ) . '" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . $required. ' /></p>';
    
    $fields['url'] = '<p class="comment-form-url"><label class="screen-reader-text" for="url">' . esc_html__( 'Website', 'travel-agency' ) . '</label><input id="url" name="url" placeholder="' . esc_attr__( 'Website', 'travel-agency' ) . '" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>'; 
    
    return $fields;    
}
endif;
add_filter( 'comment_form_default_fields', 'travel_agency_change_comment_form_default_fields' );

if( ! function_exists( 'travel_agency_change_comment_form_defaults' ) ) :
/**
 * Change Comment Form defaults
 * https://blog.josemcastaneda.com/2016/08/08/copy-paste-hurting-theme/
*/
function travel_agency_change_comment_form_defaults( $defaults ){    
    $defaults['comment_field'] = '<p class="comment-form-comment"><label class="screen-reader-text" for="comment">' . esc_html__( 'Comment', 'travel-agency' ) . '</label><textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Comment', 'travel-agency' ) . '" cols="45" rows="8" aria-required="true" required></textarea></p>';
    
    return $defaults;    
}
endif;
add_filter( 'comment_form_defaults', 'travel_agency_change_comment_form_defaults' );

if( ! function_exists( 'travel_agency_popular_post_img_size' ) ) :
/**
 * Popular Post Image size
*/
function travel_agency_popular_post_img_size(){
    return 'travel-agency-recent';
}
endif;
add_filter( 'popular_post_size', 'travel_agency_popular_post_img_size' );

if( ! function_exists( 'travel_agency_recent_post_img_size' ) ) :
/**
 * Recent Post Image size
*/
function travel_agency_recent_post_img_size(){
    return 'travel-agency-recent';
}
endif;
add_filter( 'recent_img_size', 'travel_agency_recent_post_img_size' );

if( ! function_exists( 'travel_agency_featured_post_img_size' ) ) :
/**
 * Featured Post Image size
*/
function travel_agency_featured_post_img_size(){
    return 'travel-agency-recent';
}
endif;
add_filter( 'featured_img_size', 'travel_agency_featured_post_img_size' );

if( ! function_exists( 'travel_agency_tax_img_size' ) ) :
/**
 * Featured Post Image size
*/
function travel_agency_tax_img_size(){
    return 'travel-agency-full';
}
endif;
add_filter( 'wp_travel_engine_template_banner_size', 'travel_agency_tax_img_size' );

if( ! function_exists( 'travel_agency_single_post_schema' ) ) :
/**
 * Single Post Schema
 *
 * @return string
 */
function travel_agency_single_post_schema() {
    if ( is_singular( 'post' ) ) {
        global $post;
        $custom_logo_id = get_theme_mod( 'custom_logo' );

        $site_logo   = wp_get_attachment_image_src( $custom_logo_id , 'travel-agency-pro-schema' );
        $images      = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
        $excerpt     = travel_agency_escape_text_tags( $post->post_excerpt );
        $content     = $excerpt === "" ? mb_substr( travel_agency_escape_text_tags( $post->post_content ), 0, 110 ) : $excerpt;
        $schema_type = ! empty( $custom_logo_id ) && has_post_thumbnail( $post->ID ) ? "BlogPosting" : "Blog";

        $args = array(
            "@context"  => "https://schema.org",
            "@type"     => $schema_type,
            "mainEntityOfPage" => array(
                "@type" => "WebPage",
                "@id"   => get_permalink( $post->ID )
            ),
            "headline"  => esc_html( get_the_title( $post->ID ) ),
            "datePublished" => get_the_time( DATE_ISO8601, $post->ID ),
            "dateModified"  => get_post_modified_time(  DATE_ISO8601, __return_false(), $post->ID ),
            "author"        => array(
                "@type"     => "Person",
                "name"      => travel_agency_escape_text_tags( get_the_author_meta( 'display_name', $post->post_author ) )
            ),
            "description" => ( class_exists('WPSEO_Meta') ? WPSEO_Meta::get_value( 'metadesc' ) : $content )
        );

        if ( has_post_thumbnail( $post->ID ) ) :
            $args['image'] = array(
                "@type"  => "ImageObject",
                "url"    => $images[0],
                "width"  => $images[1],
                "height" => $images[2]
            );
        endif;

        if ( ! empty( $custom_logo_id ) ) :
            $args['publisher'] = array(
                "@type"       => "Organization",
                "name"        => get_bloginfo( 'name' ),
                "description" => get_bloginfo( 'description' ),
                "logo"        => array(
                    "@type"   => "ImageObject",
                    "url"     => $site_logo[0],
                    "width"   => $site_logo[1],
                    "height"  => $site_logo[2]
                )
            );
        endif;

        echo '<script type="application/ld+json">';
        if ( version_compare( PHP_VERSION, '5.4.0' , '>=' ) ) {
            echo wp_json_encode( $args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        } else {
            echo wp_json_encode( $args );
        }
        echo '</script>';
    }
}
endif;
add_action( 'wp_head', 'travel_agency_single_post_schema' );

if( ! function_exists( 'travel_agency_inline_script_style' ) ) :
/*
This function helps to remove the extra gap that comes from site-title and site-description
*/
function travel_agency_inline_script_style() {
    if( ! current_theme_supports( 'custom-header', 'header-text' ) && get_theme_support( 'custom-logo', 'header-text' ) && ! get_theme_mod( 'header_text', true ) ){ 
        $custom_css = "
            .site-title, .site-description{
                    display: none;
            }";        
        
        wp_add_inline_style( 'travel-agency-style', $custom_css );
    }

    if( travel_agency_is_wpte_tr_activated() && is_post_type_archive( 'trip' ) ){
        $custom_js = 'jQuery(document).ready(function($){';
        
        $args = array( 
            'post_type' => 'trip',
            'numberposts' => -1
        );
        $trips = get_posts( $args );
        
        foreach( $trips as $trip ){
            $trip_comments = get_comments( array(
                'post_id' => $trip->ID,
                'status' => 'approve',
            ) );
            if( $trip_comments ){
                $sum = 0;
                $i = 0;
                foreach( $trip_comments as $t_comment ){
                    $rating = get_comment_meta( $t_comment->comment_ID, 'stars', true );
                    $sum = $sum+$rating;
                    $i++;
                }
                $aggregate = $sum/$i;
                $aggregate = round( $aggregate, 2 );
                $custom_js .= '$("#agg-rating-'. absint( $trip->ID ) .'").rateYo({
                    rating: '. floatval( $aggregate ) .'
                });';
            }
        }
        $custom_js .= '});';

        wp_add_inline_script( 'travel-agency-custom', $custom_js );
    }
}
endif;
add_action( 'wp_enqueue_scripts', 'travel_agency_inline_script_style' );

if( ! function_exists( 'travel_agency_admin_notice' ) ) :
/**
 * Addmin notice for getting started page
*/
function travel_agency_admin_notice(){
    global $pagenow;
    $theme_args      = wp_get_theme();
    $meta            = get_option( 'travel_agency_admin_notice' );
    $name            = $theme_args->__get( 'Name' );
    $current_screen  = get_current_screen();
    
    if( 'themes.php' == $pagenow && !$meta ){
        
        if( $current_screen->id !== 'dashboard' && $current_screen->id !== 'themes' ){
            return;
        }

        if( is_network_admin() ){
            return;
        }

        if( ! current_user_can( 'manage_options' ) ){
            return;
        } ?>

        <div class="welcome-message notice notice-info">
            <div class="notice-wrapper">
                <div class="notice-text">
                    <h3><?php esc_html_e( 'Congratulations!', 'travel-agency' ); ?></h3>
                    <p><?php printf( __( '%1$s is now installed and ready to use. Click below to see theme documentation, plugins to install and other details to get started.', 'travel-agency' ), esc_html( $name ) ); ?></p>
                    <p><a href="<?php echo esc_url( admin_url( 'themes.php?page=travel-agency-getting-started' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Go to the getting started.', 'travel-agency' ); ?></a></p>
                    <p class="dismiss-link"><strong><a href="?travel_agency_admin_notice=1"><?php esc_html_e( 'Dismiss', 'travel-agency' ); ?></a></strong></p>
                </div>
            </div>
        </div>
    <?php }
}
endif;
add_action( 'admin_notices', 'travel_agency_admin_notice' );

if( ! function_exists( 'travel_agency_update_admin_notice' ) ) :
/**
 * Updating admin notice on dismiss
*/
function travel_agency_update_admin_notice(){
    if ( isset( $_GET['travel_agency_admin_notice'] ) && $_GET['travel_agency_admin_notice'] = '1' ) {
        update_option( 'travel_agency_admin_notice', true );
    }
}
endif;
add_action( 'admin_init', 'travel_agency_update_admin_notice' );

/*
Removal of Tax Description and duplicate page title from the archive-trips
*/
add_filter( 'wte_trip_archive_description_below_title','__return_false' );
add_filter( 'wte_trip_archive_description_page_header','__return_false' );

