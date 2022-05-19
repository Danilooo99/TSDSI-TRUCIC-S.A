<?php

/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Travel Tour Pro
 */
?>
	<footer class="main">
		<div class="container">
		<?php 
dynamic_sidebar( 'footer-1' );
?>
	</div>
	</footer>
		<div class="copyright text-center spacer">
			<?php 
esc_html_e( "Powered by", 'travel-tour' );
?> <a href="<?php 
echo  esc_url( 'http://wordpress.org/' ) ;
?>"><?php 
esc_html_e( "WordPress", 'travel-tour' );
?></a> | <?php 
esc_html_e( 'Theme by', 'travel-tour' );
?> <a href="<?php 
echo  esc_url( 'http://thebootstrapthemes.com/' ) ;
?>"><?php 
esc_html_e( 'TheBootstrapThemes', 'travel-tour' );
?>
		</div>
		<div class="scroll-top-wrapper"> <span class="scroll-top-inner"><i class="fa fa-2x fa-angle-up"></i></span></div> 
		
		<?php 
wp_footer();
?>
	</body>
</html>