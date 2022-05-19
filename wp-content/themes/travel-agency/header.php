<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Travel_Agency
 */

    /**
     * Doctype Hook
     * 
     * @hooked travel_agency_doctype
    */
    do_action( 'travel_agency_doctype' );   
?>
<head itemscope itemtype="https://schema.org/WebSite">

<?php     
    /**
     * Before wp_head
     * 
     * @hooked travel_agency_head
    */
    do_action( 'travel_agency_before_wp_head' );
    
    wp_head(); 
?>

</head>

<body <?php body_class(); ?> itemscope itemtype="https://schema.org/WebPage">
	
<?php
    wp_body_open();
    
    /**
     * Before Header
     * 
     * @hooked travel_agency_page_start - 20 
    */
    do_action( 'travel_agency_before_header' );
    
    /**
     * Header
     * 
     * @hooked travel_agency_header - 20     
    */
    do_action( 'travel_agency_header' );
    
    /**
     * Before Content
     * 
     * @hooked travel_agency_breadcrumb - 20
    */
    do_action( 'travel_agency_after_header' );
    
    /**
     * Content
     * 
     * @hooked travel_agency_content_start
    */
    do_action( 'travel_agency_content' );