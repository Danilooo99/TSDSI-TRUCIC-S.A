<?php
/**
 * Travel and Tour Functions
*/
 // For Getting Started
 define( 'TRAVEL_AGENCY_THEME_TEXTDOMAIN', 'travel-and-tour' );
/**
 * After setup theme hook
 */
function travel_and_tour_theme_setup(){
    /*
     * Make chile theme available for translation.
     * Translations can be filed in the /languages/ directory.
     */
    load_child_theme_textdomain( 'travel-and-tour', get_stylesheet_directory() . '/languages' );

    /**
     * Register default header image
     */
    register_default_headers( array(
        'child' => array(
            'url'           => '%2$s/images/banner-img.jpg',
            'thumbnail_url' => '%2$s/images/banner-img.jpg',
            'description'   => __( 'Default Header Image', 'travel-and-tour' ),
        ),
    ) );

}
add_action( 'after_setup_theme', 'travel_and_tour_theme_setup' );

/**
 * Custom header
 */
function travel_and_tour_custom_header(){

    $header_args = array(
        'default-image' => get_stylesheet_directory_uri() . '/images/banner-img.jpg',
        'width'         => 1920,
        'height'        => 680,
        'header-text'   => false 
    );

    return $header_args;
}
add_filter( 'travel_agency_custom_header_args', 'travel_and_tour_custom_header' );

/**
 * Load assets.
 */
function travel_and_tour_enqueue_styles() {
    $my_theme = wp_get_theme();
    $version = $my_theme['Version'];
    
    wp_enqueue_style( 'travel-and-tour-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'travel-and-tour-style', get_stylesheet_directory_uri() . '/style.css', array( 'travel-agency' ), $version );
}
add_action( 'wp_enqueue_scripts', 'travel_and_tour_enqueue_styles' );

/**
 * Remove action from parent
*/
function travel_and_tour_remove_action(){
    remove_action( 'customize_register', 'travel_agency_customizer_theme_info' );
    remove_action( 'customize_register', 'travel_agency_customizer_demo_content' );    
}
add_action( 'init', 'travel_and_tour_remove_action' );


function travel_and_tour_customizer_theme_info( $wp_customize ){
    $wp_customize->add_section( 'theme_info', array(
        'title'       => __( 'Information Links' , 'travel-and-tour' ),
        'priority'    => 6,
    ) );
    
    /** Important Links */
    $wp_customize->add_setting( 'theme_info_theme',
        array(
            'default' => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    
    $theme_info = '<div class="customizer-custom">';
    $theme_info .= '<h3 class="sticky_title">' . __( 'Need help?', 'travel-and-tour' ) . '</h3>';
    $theme_info .= '<span class="sticky_info_row"><label class="row-element">' . __( 'View demo', 'travel-and-tour' ) . ': </label><a href="' . esc_url( 'https://rarathemes.com/previews/?theme=travel-and-tour/' ) . '" target="_blank">' . __( 'here', 'travel-and-tour' ) . '</a></span>';
    $theme_info .= '<span class="sticky_info_row"><label class="row-element">' . __( 'View documentation', 'travel-and-tour' ) . ': </label><a href="' . esc_url( 'https://docs.rarathemes.com/docs/travel-and-tour/' ) . '" target="_blank">' . __( 'here', 'travel-and-tour' ) . '</a></span>';
    $theme_info .= '<span class="sticky_info_row"><label class="row-element">' . __( 'Support ticket', 'travel-and-tour' ) . ': </label><a href="' . esc_url( 'https://rarathemes.com/support-ticket/' ) . '" target="_blank">' . __( 'here', 'travel-and-tour' ) . '</a></span>';
    $theme_info .= '<span class="sticky_info_row"><label class="more-detail row-element">' . __( 'More Details', 'travel-and-tour' ) . ': </label><a href="' . esc_url( 'https://rarathemes.com/wordpress-themes/' ) . '" target="_blank">' . __( 'here', 'travel-and-tour' ) . '</a></span>';
    $theme_info .= '</div>';

    $wp_customize->add_control( new Travel_Agency_Info_Text( $wp_customize,
        'theme_info_theme', 
        array(
            'label' => __( 'About Tour Package' , 'travel-and-tour' ),
            'section'     => 'theme_info',
            'description' => $theme_info
        )
        )
    );
    
    /** Demo Content Import */
    $wp_customize->add_section( 
        'theme_demo_content',
        array(
            'title'    => __( 'Demo Content Import', 'travel-and-tour' ),
            'priority' => 7,
        )
    );
    
    $wp_customize->add_setting(
        'demo_content_instruction',
        array(
            'sanitize_callback' => 'wp_kses_post'
        )
    );

    $demo_content_description = sprintf( __( 'Travel and Tour comes with demo content import feature. You can import the demo content with just one click. For step-by-step video tutorial, %1$sClick here%2$s', 'travel-and-tour' ), '<a class="documentation" href="' . esc_url( 'https://rarathemes.com/blog/import-demo-content-rara-themes/' ) . '" target="_blank">', '</a>' );

    $wp_customize->add_control(
        new Travel_Agency_Info_Text( 
            $wp_customize,
            'demo_content_instruction',
            array(
                'label'       => __( 'About Demo Import' , 'travel-and-tour' ),
                'section'     => 'theme_demo_content',
                'description' => $demo_content_description,
            )
        )
    );
    
    $theme_demo_content_desc = '<div class="customizer-custom">';

    if( ! class_exists( 'RDDI_init' ) ){
        $theme_demo_content_desc .= '<span><label class="row-element">' . __( 'Plugin required', 'travel-and-tour' ) . ': </label><a href="' . esc_url( 'https://wordpress.org/plugins/rara-one-click-demo-import/' ) . '" target="_blank">' . __( 'Rara One Click Demo Import', 'travel-and-tour' ) . '</a></span><br />';
    }

    $theme_demo_content_desc .= '<span><label class="row-element">' . __( 'Download Demo Content', 'travel-and-tour' ) . ': </label><a href="' . esc_url( 'https://docs.rarathemes.com/docs/travel-and-tour/theme-activation-and-installation/how-to-import-demo-content/' ) . '" target="_blank" rel="nofollow noopener">' . __( 'Click here', 'travel-and-tour' ) . '</a></span><br />';

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
                'description' => $theme_demo_content_desc,
            )
        )
    );

}
add_action( 'customize_register', 'travel_and_tour_customizer_theme_info' );


/**
 * Prints Time
*/
/**
 * Prints phone number in header
*/
function travel_and_tour_get_header_phone(){
    return esc_html( get_theme_mod( 'phone', '' ) );
}

/**
 * Prints phone label
*/
function travel_and_tour_get_phone_label(){
    return esc_html( get_theme_mod( 'phone_label', '' ) );
}

/**
 * Selective refresh for header email 
 */
function travel_and_tour_get_email(){
    return esc_html( get_theme_mod( 'email', '' ) );
}

/**
 * Selective refresh for banner button 
 */
function travel_and_tour_btn_label_selective_refresh(){
    return esc_html( get_theme_mod( 'banner_btn_label', esc_html__( 'Get Started', 'travel-and-tour') ) );
}

/**
 * Prints Time
*/
function travel_and_tour_get_time(){
    return esc_html( get_theme_mod( 'time', '' ) );
}

/**
 * Header Time
*/
function travel_and_tour_header_time(){
    $time = get_theme_mod( 'time' );
    if( $time ) echo '<div class="opening-time"><i class="fa fa-clock-o"></i><span class="time">' . esc_html( $time ) . '</span></div>';
}

/**
 * Header Email
*/
function travel_and_tour_header_email(){
    $email = get_theme_mod( 'email', '' );
    if( is_email( $email ) ) echo '<a href="' . esc_url( 'mailto:' .  $email ) . '" class="email-link"><i class="fa fa-envelope-open-o"></i><span class="email">' . esc_html( $email ) . '</span></a>';
}


require get_stylesheet_directory() . '/inc/customizer-functions.php';

require get_stylesheet_directory() . '/inc/pluggable-functions.php';