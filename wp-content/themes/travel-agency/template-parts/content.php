<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Travel_Agency
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="https://schema.org/Blog">
	
    <?php
        /**
         * Post Thumbnail
         * 
         * @hooked travel_agency_entry_header   - 15
         * @hooked travel_agency_post_thumbnail - 20
        */
        do_action( 'travel_agency_before_entry_content' );    
    ?>
    
    <div class="text-holder">        
        <?php
            /**
             * Entry Content
             * 
             * @hooked travel_agency_entry_content - 20
             * @hooked travel_agency_entry_footer  - 25
            */
            do_action( 'travel_agency_entry_content' );        
        ?>
    </div><!-- .text-holder -->
    
</article><!-- #post-<?php the_ID(); ?>-->
