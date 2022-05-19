<?php $header_text_color = get_header_textcolor(); ?>
<header>
	
<div class="pri-bg-color top-bar">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				
				<?php
				// To store only value which has links :
				if ( ! empty( $social_media ) && is_array( $social_media ) ) {
					$social_media_filtered = array();
					foreach ( $social_media as $value ) {
						if( empty( $value['social_media_link'] ) ) {
							continue;
						}
						$social_media_filtered[] = $value; 
					}
				}	
				?>

				<?php if ( ! empty( $social_media_filtered ) && is_array( $social_media_filtered ) ) : ?>
				<!-- top-bar -->
				<div class="social-icons">
				<ul class="list-inline">
					<?php foreach ( $social_media_filtered as $value ) { ?>
						<?php
							$no_space_class = str_replace( 'fa fa-', '', $value['social_media_class'] );
							$class = strtolower( $no_space_class );
						?>
				        <li class="<?php echo esc_attr( $class ); ?>"><a href="<?php echo esc_url( $value['social_media_link'] ); ?>" target="_blank"><i class="<?php echo esc_attr( $value['social_media_class'] ); ?>"></i></a></li>
				    <?php } ?>
				</ul>
				</div>
				<!-- top-bar -->
				<?php endif; ?>
			</div>
			<?php $header_search_enable = get_theme_mod( 'header_search_display_option', false ); ?>
			<?php $contact = get_theme_mod( 'contact_number', '' ); ?>
			<?php if( $header_search_enable || ! empty( $contact ) ) : ?>
				<div class="col-md-6 text-right">
					<?php if( ! empty( $contact ) ) : ?>
						<div class="call-top"><?php echo esc_htmL( $contact ); ?></div>
					<?php endif; ?>
					<?php if( $header_search_enable ) : ?>
						<div class="search-top"><?php get_search_form(); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="logo-nav">

		<!-- Brand and toggle get grouped for better mobile display -->		
			<nav class="navbar">
				<div class="container">
		      	<button type="button" class="navbar-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#bs-example-navbar-collapse-1">
			        <span class="sr-only"><?php esc_html_e( 'Toggle navigation', 'travel-tour' ); ?></span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
		      	</button>
		      	<div class="main-logo">
		      	<?php if ( has_custom_logo() ) { 
		      		the_custom_logo();
		      	} else if( display_header_text() ) { ?>
		  			<a  class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		  				<h1 class="site-title" style="color:<?php echo "#". esc_attr( $header_text_color ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		  			<h2 class="site-description" style="color:<?php echo "#". esc_attr( $header_text_color );?>"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></h2>
		  		<?php } ?>
		  		</a>
		  		</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1"> 				
					<?php
			            wp_nav_menu( array(
			                'theme_location'    => 'primary',
			                'menu-id'    => 'primary-menu',
			                'depth'             => 8,
			                'container'         => 'div',
			                'menu_class'        => 'nav navbar-nav pull-right',
			                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
			                'walker'            => new Travel_Tour_Wp_Bootstrap_Navwalker()
			            ) );
			        ?>
			    </div> <!-- /.end of collaspe navbar-collaspe -->			        
	
</div> <!-- /.end of container -->
	</nav> 
</div>



<!-- test -->



</header>


