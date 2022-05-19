<?php
/**
 * Widgets
 *
 * @package Travel_Agency
 */

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function travel_agency_widgets_init() {
	
    $sidebars = array(
        'sidebar'   => array(
            'name'        => __( 'Sidebar', 'travel-agency' ),
            'id'          => 'sidebar', 
            'description' => __( 'Default Sidebar', 'travel-agency' ),
        ),
        'footer-one'=> array(
            'name'        => __( 'Footer One', 'travel-agency' ),
            'id'          => 'footer-one', 
            'description' => __( 'Add footer one widgets here.', 'travel-agency' ),
        ),
        'footer-two'=> array(
            'name'        => __( 'Footer Two', 'travel-agency' ),
            'id'          => 'footer-two', 
            'description' => __( 'Add footer two widgets here.', 'travel-agency' ),
        ),
        'footer-three'=> array(
            'name'        => __( 'Footer Three', 'travel-agency' ),
            'id'          => 'footer-three', 
            'description' => __( 'Add footer three widgets here.', 'travel-agency' ),
        ),
        'footer-four'=> array(
            'name'        => __( 'Footer Four', 'travel-agency' ),
            'id'          => 'footer-four', 
            'description' => __( 'Add footer four widgets here.', 'travel-agency' ),
        )
    );
    
    foreach( $sidebars as $sidebar ){
        register_sidebar( array(
    		'name'          => esc_html( $sidebar['name'] ),
    		'id'            => esc_attr( $sidebar['id'] ),
    		'description'   => esc_html( $sidebar['description'] ),
    		'before_widget' => '<section id="%1$s" class="widget %2$s">',
    		'after_widget'  => '</section>',
    		'before_title'  => '<h2 class="widget-title">',
    		'after_title'   => '</h2>',
    	) );
    }
    
}
add_action( 'widgets_init', 'travel_agency_widgets_init' );