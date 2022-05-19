<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Travel_Agency
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
    <?php
        /**
         * Post Thumbnail
         * 
         * @hooked travel_agency_post_thumbnail
        */
        do_action( 'travel_agency_before_entry_content' );
    ?>
    
    <div class="text-holder">
    	<?php
            /**
             * Entry Content
             * 
             * @hooked travel_agency_entry_content - 15
             * @hooked travel_agency_entry_footer  - 20
            */
            do_action( 'travel_agency_page_entry_content' );        
        ?>
    </div><!-- .text-holder -->
    
</article><!-- #post-<?php the_ID(); ?> -->
