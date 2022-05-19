<section class="banner">
<?php if( get_theme_mod( 'banner_section_display_option', true ) ) : ?>
	<?php $banner_title = get_theme_mod( 'banner_section_title' ); ?>
	<?php $banner_desc = get_theme_mod( 'banner_section_description' ); ?>
	<div class="banner-wrapper">	
		<?php if( has_header_image() ) : ?>	
			<img src="<?php echo esc_url( get_header_image() ); ?>">
		<?php endif; ?>
	</div>
	<div class="banner-text-wrapper">
		<div class="container">
			<div class="banner-block">
				<h1><?php echo esc_html( $banner_title ); ?></h1>
				<h4><?php echo esc_html( $banner_desc ); ?></h4>
				<?php if( get_theme_mod( 'search_section_display_option', true ) ) { ?>
						<div class="search-block"><?php get_search_form(); ?></div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php endif; ?>
</section>