<?php if( get_theme_mod( 'blog_post_display_option', true ) ) : ?>
    <?php
        $paged = ( get_query_var('page') ) ? get_query_var('page') : 1;
        $posts_per_page = get_theme_mod( 'number_of_blog_post', 4 );
        $cat = get_theme_mod( 'blog_post_category' ) ;
        $args = array(
            'post_type' => 'post',
            'category_name'       =>  $cat ,
            'posts_per_page'    =>  $posts_per_page,
            'paged'     => $paged
        );
        $query = new WP_Query( $args );
        $max_pages = $query->max_num_pages;
    ?>

    <?php
        
        if ( $query->have_posts() ) { ?>
            <div class="home-archive inside-page post-list">
                <div class="container">        
                    <div class="row">

                        <div class="col-sm-12">
                            <?php $archive_title = get_theme_mod( 'blog_post_section_title' ); ?>
                              <?php if( ! empty( $archive_title ) ) { ?><h2 class="news-heading"><?php echo esc_html( $archive_title ); ?></h2><?php } ?>
                        	<div class="grid-view row">                                         
	                          
	                              <?php /* Start the Loop */ ?>
	                              <?php while ( $query->have_posts() ) : $query->the_post(); ?>
	                                  <?php

	                                      /*
	                                       * Include the Post-Format-specific template for the content.
	                                       * If you want to override this in a child theme, then include a file
	                                       * called content-___.php (where ___ is the Post Format name) and that will be used instead.
	                                       */
	                                      get_template_part( 'template-parts/content' );
	                                  ?>
	                              <?php endwhile; ?>                                       
	                          
	                           
	                          <?php wp_reset_postdata(); ?>
	                        </div>
                        </div>

                    </div>
                </div>
            </div>
            
            
        <?php } ?>
    
<?php endif; ?>