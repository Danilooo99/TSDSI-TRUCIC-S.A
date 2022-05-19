<?php if ( is_active_sidebar( 'testimonials' ) ) : ?>
	<section class="testimonials spacer">
		<h5 class="text-center"><?php esc_html_e( 'Whats our clients has to say about us', 'travel-tour' ); ?></h5>
			<div class="container"><div id="owl-testimonials" class="owl-carousel">
			<?php dynamic_sidebar( 'testimonials' ); ?>	
			</div></div>
	</section>
<?php endif;