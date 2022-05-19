
<section class="about-us spacer"<?php if(has_post_thumbnail()) { ?> style="background-image: url( <?php echo esc_url(the_post_thumbnail_url()); ?> );" <?php } else {} ?>>
<div class="overlay"></div>
	<div class="container">
      <div class="inside-wrapper">
          
                <h2><?php the_title(); ?></h2>
                <p><?php the_excerpt(); ?></p> 
                <a href="<?php echo esc_url( get_permalink( $about_ID ) ); ?>" title="<?php esc_attr_e( 'Read More', 'travel-tour' ); ?>" class="readmore"><?php esc_html_e( 'Read More', 'travel-tour' ); ?></a>
         
      </div>
    </div>
</section>