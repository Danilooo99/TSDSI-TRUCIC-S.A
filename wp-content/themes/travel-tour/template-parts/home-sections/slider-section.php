<?php if( class_exists( 'Wp_Travel_Engine' ) ) : ?>

  <?php if( get_theme_mod( 'recommended_activities_display_option', true ) ) : ?>
    <!-- banner-news -->
    <?php
      $tax = get_theme_mod( 'recommended_activities_category', 'activities' );
      $title = get_theme_mod( 'recommended_activities_title', '' );
 
      $terms = get_terms( array(
        'taxonomy' => $tax,
        'hide_empty' => false,
      ) );
      set_query_var( 'terms', $terms );
      set_query_var( 'title', $title );
    ?>

    <?php if ( ! empty( $terms ) ) : ?>
      <?php
        $layout = get_theme_mod( 'recommended_activities_slider_layout', 'one' );
        if( $layout == 'one' ) {
          get_template_part( 'layouts/homepage/slider/slider-layout', 'one' );
        }
        if( $layout == 'two' ) {
          get_template_part( 'layouts/homepage/slider/slider-layout', 'two' );
        }
        if( $layout == 'three' ) {
          get_template_part( 'layouts/homepage/slider/slider-layout', 'three' );
        }     
      ?>
    <?php endif; ?>
    <!-- banner-news -->
  <?php endif; ?>

<?php endif;