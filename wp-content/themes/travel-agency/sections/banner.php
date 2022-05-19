<?php
/**
 * Banner Section
 * 
 * @package Travel_Agency
 */

$banner_title = get_theme_mod( 'banner_title', __( 'Find Your Best Holiday', 'travel-agency' ) );
$sub_title    = get_theme_mod( 'banner_subtitle', __( 'Find great adventure holidays and activities around the planet.', 'travel-agency' ) );
$ed_search    = get_theme_mod( 'ed_banner_search', '1' );
$banner_image = get_header_image();

if( $banner_image ){ ?>  
<div class="banner">
	<?php 
        the_custom_header_markup(); 
        
        if( $ed_search || $banner_title || $sub_title ){ ?>	
        <div class="form-holder">
    		<?php 
                if( $banner_title || $sub_title ){
                    echo '<div class="text wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.1s">';
                    if( $banner_title ) echo '<h2>' . esc_html( travel_agency_get_banner_title() ) . '</h2>';
                    if( $sub_title ) echo '<div class="banner-content">' . wp_kses_post( travel_agency_get_banner_sub_title() ) . '</div>';
            		echo '</div>';
                }
                
                if( $ed_search ){
                    echo '<div class="banner-form">';
					echo do_shortcode('[fibosearch]');
                    /*travel_agency_get_banner_search();*/ 
                    echo '</div>';
                }                
            ?>
    	</div>            
        <?php 
        }
    ?>
</div> <!-- banner ends -->
<?php
}            