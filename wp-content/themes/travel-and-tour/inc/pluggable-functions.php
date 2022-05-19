<?php 
/**
 * Travel Agency Pluggable Functions
 * Overriding it on travel and tour 
*/

function travel_agency_fonts_url() {
    $fonts_url = '';

    /*
        * translators: If there are characters in your language that are not supported
        * by Poppins, translate this to 'off'. Do not translate into your own language.
        */
    $pmukta = _x( 'on', 'Mukta font: on or off', 'travel-and-tour' );
    
    /*
        * translators: If there are characters in your language that are not supported
        * by Montserrat, translate this to 'off'. Do not translate into your own language.
        */
    $lato = _x( 'on', 'Lato font: on or off', 'travel-and-tour' );

    if ( 'off' !== $pmukta || 'off' !== $lato ) {
        $font_families = array();

        if( 'off' !== $pmukta ){
            $font_families[] = 'Mukta:200,300,400,500,600,700,800,800i';
        }

        if( 'off' !== $lato ){
            $font_families[] = 'Lato:100,100i,200,200i,300,300i,400,400i,700,700i,900,900i';
        }

        $query_args = array(
            'family'  => urlencode( implode( '|', $font_families ) ),
            'display' => urlencode( 'fallback' ),
        );

        $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
    }

    return esc_url( $fonts_url );
}

function travel_agency_header(){     
    $phone       = get_theme_mod( 'phone' );
    $phone_label = get_theme_mod( 'phone_label' );
    $ed_social   = get_theme_mod( 'ed_social_links', false );
    $ed_search   = get_theme_mod( 'ed_search', false ); 
    ?>
    <header id="masthead" class="site-header header-four" itemscope itemtype="https://schema.org/WPHeader">
		
        <div class="header-holder">			
            <?php if( $ed_social || $ed_search ){ ?>
            <div class="header-t">
				<div class="container">
                    <div class="left">
                        <?php
                            travel_and_tour_header_time();
                            travel_and_tour_header_email();
                        ?>
                    </div><!-- .left -->
                    <div class="right">
                        <?php if( $ed_social ) travel_agency_social_links(); ?>	
                    </div><!-- .right -->                  
				</div>
			</div> <!-- header-t ends -->
            <?php } ?>
            
            <div class="header-b">
				<div class="container">
					<div class="site-branding" itemscope itemtype="https://schema.org/Organization">
						<?php 
                	        if( function_exists( 'has_custom_logo' ) && has_custom_logo() ){
                                the_custom_logo();
                            } 
                        ?>
                        <div class="text-logo">
							<?php if ( is_front_page() ) : ?>
                                <h1 class="site-title" itemprop="name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" itemprop="url"><?php bloginfo( 'name' ); ?></a></h1>
                            <?php else : ?>
                                <p class="site-title" itemprop="name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" itemprop="url"><?php bloginfo( 'name' ); ?></a></p>
                            <?php endif;
                			$description = get_bloginfo( 'description', 'display' );
                			if ( $description || is_customize_preview() ) : ?>
                				<p class="site-description" itemprop="description"><?php echo esc_html( $description ); /* WPCS: xss ok. */ ?></p>
                			<?php
                			endif; ?>
                        </div>
            		</div><!-- .site-branding -->
                    
                    <?php if( $phone_label || $phone ){ ?>
                    <div class="right">
                        <?php
    						if( $phone_label ) echo '<span class="phone-label">' . esc_html( travel_agency_get_phone_label() ) . '</span>';
                            if( $phone ) echo '<a href="' . esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $phone ) ) . '" class="tel-link"><span class="phone">' . esc_html( travel_agency_get_header_phone() ) . '</span></a>';
                        ?>
                    </div>
                    <?php } ?>
                    
				</div>
			</div> <!-- header-b ends -->
                        
		</div> <!-- header-holder ends -->
		
        <div class="nav-holder">
			<div class="container">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="home-link"><i class="fa fa-home"></i></a>

                <div class="mobile-menu-wrapper">
                    <button id="primary-toggle-button" data-toggle-target=".main-menu-modal" data-toggle-body-class="showing-main-menu-modal" aria-expanded="false" data-set-focus=".close-main-nav-toggle"><?php _e( 'MENU', 'travel-and-tour' );?><i class="fa fa-bars"></i></button>

                    <nav id="mobile-site-navigation" class="main-navigation mobile-navigation">        
                        <div class="primary-menu-list main-menu-modal cover-modal" data-modal-target-string=".main-menu-modal">
                            <button class="close close-main-nav-toggle" data-toggle-target=".main-menu-modal" data-toggle-body-class="showing-main-menu-modal" aria-expanded="false" data-set-focus=".main-menu-modal">
                                <?php _e( 'CLOSE', 'travel-and-tour'); ?>
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="mobile-menu" aria-label="<?php esc_attr_e( 'Mobile', 'travel-and-tour' ); ?>">
                                <?php
                                    wp_nav_menu( array(
                                        'theme_location' => 'primary',
                                        'menu_id'        => 'mobile-primary-menu',
                                        'menu_class'     => 'nav-menu main-menu-modal',
                                        'fallback_cb'    => 'travel_agency_primary_menu_fallback',
                                    ) );
                                ?>
                            </div>
                        </div>
                    </nav><!-- #mobile-site-navigation -->
                </div>

                <nav id="site-navigation" class="main-navigation" itemscope itemtype="https://schema.org/SiteNavigationElement">
        			<?php
        				wp_nav_menu( array(
        					'theme_location' => 'primary',
        					'menu_id'        => 'primary-menu',
                            'fallback_cb'    => 'travel_agency_primary_menu_fallback',
        				) );
        			?>
        		</nav><!-- #site-navigation --> 
                <?php if( $ed_search ) travel_agency_get_header_search(); ?>
			</div>
		</div> <!-- nav-holder ends -->
        
	</header> <!-- header ends -->
    <?php
}

/**
 * Footer Bottom
*/
function travel_agency_footer_bottom(){ ?>
    <div class="footer-b">
        <div class="site-info">
            <?php
                travel_agency_get_footer_copyright();
                echo esc_html__( 'Travel and Tour | Developed By ', 'travel-and-tour' );
                echo '<a href="' . esc_url( 'https://rarathemes.com/' ) .'" rel="nofollow" target="_blank">' . esc_html__( 'Rara Theme', 'travel-and-tour' ) . '</a>';
                
                printf( esc_html__( ' Powered by %s', 'travel-and-tour' ), '<a href="'. esc_url( __( 'https://wordpress.org/', 'travel-and-tour' ) ) .'" target="_blank">WordPress</a> .' );
            ?>                              
        </div>
        <?php 
        if ( function_exists( 'the_privacy_policy_link' ) ) {
            the_privacy_policy_link();
        }
        ?>
        <nav class="footer-navigation">
            <?php
                wp_nav_menu( array(
                    'theme_location' => 'footer',
                    'menu_id'        => 'footer-menu',
                    'fallback_cb'    => false,
                ) );
            ?>
        </nav><!-- .footer-navigation -->
    </div>
    <?php
}
