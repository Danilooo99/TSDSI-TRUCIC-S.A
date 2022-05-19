<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package Travel Tour Pro
 */

get_header(); ?>
<div class="spacer">
<section class="page-header">
  <div class="container">

      	<h1 class="text-center">
      		<?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'travel-tour' ); ?>
      	</h1>

      <div class="detail-content">


        	<div class="not-found">
          		<p class="text-center"><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'travel-tour' ); ?></p>
			         <?php get_search_form(); ?>
        	</div>
      </div>

     
  </div>
</section>
</div>
<?php get_footer(); ?>