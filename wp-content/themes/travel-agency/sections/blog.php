<?php
/**
 * Blog Section
 * 
 * @package Travel_Agency
 */

$ed_blog   = get_theme_mod( 'ed_blog_section', true );
$btitle    = get_theme_mod( 'blog_section_title', __( 'Latest Articles', 'travel-agency' ) );
$sub_title = get_theme_mod( 'blog_section_subtitle', __( 'Show your latest blog posts here. You can modify this section from Appearance > Customize > Home Page Settings > Blog Section.', 'travel-agency' ) );
$readmore  = get_theme_mod( 'blog_readmore', __( 'Read More', 'travel-agency' ) );
$blog      = get_option( 'page_for_posts' );
$label     = get_theme_mod( 'blog_view_all', __( 'View All Posts', 'travel-agency' ) );
    
$args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => 3,
    'ignore_sticky_posts' => true
);

$qry = new WP_Query( $args );

if( $btitle || $sub_title || ( $ed_blog && $qry->have_posts() ) ){ ?>

<section id="blog_section" class="blog-section">
	<div class="container">
        
        <?php if( $btitle || $sub_title ){ ?>
            <header class="section-header wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.1s">	
                <?php 
                    if( $btitle ) echo '<h2 class="section-title">' . esc_html( travel_agency_get_blog_section_title() ) . '</h2>';
                    if( $sub_title ) echo '<div class="section-content">' . wp_kses_post( wpautop( travel_agency_get_blog_section_sub_title() ) ) . '</div>'; 
                ?>
    		</header>
        <?php } ?>
        
        <?php if( $ed_blog && $qry->have_posts() ){ ?>
            <div class="grid wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.1s">
    			<?php 
                while( $qry->have_posts() ){
                    $qry->the_post(); ?>
                    <div class="post">
                        <div class="holder">
            				<div class="img-holder">
                                <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                                <?php 
                                    if( has_post_thumbnail() ){
                                        the_post_thumbnail( 'travel-agency-blog', array( 'itemprop' => 'image' ) );
                                    }else{
                                        travel_agency_get_fallback_svg( 'travel-agency-blog' );
                                    }                            
                                ?>                        
                                </a>
                                <?php travel_agency_categories(); ?>
                            </div>
            				<div class="text-holder">
            					<header class="entry-header">
            						<span class="posted-on"><time><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_date() ); ?></a></time></span>
            						<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            					</header>
            					<div class="entry-content">
            						<?php the_excerpt(); ?>
            					</div>        					
            				</div>
                            <div class="entry-footer">
        						<?php
                                    travel_agency_posted_by();
                                    travel_agency_comment_count();
                                ?>    						
        					</div>
                        </div>
        			</div>			
        			<?php 
                }
                wp_reset_postdata();
                ?>
    		</div>
    		
            <?php if( $blog && $label ){ ?>
                <div class="btn-holder">
        			<a href="<?php the_permalink( $blog ); ?>" class="btn-more"><?php echo esc_html( travel_agency_get_blog_view_all_btn() ); ?></a>
        		</div>
            <?php } ?>
        
        <?php } ?>
	</div>
</section>
<?php 
}
