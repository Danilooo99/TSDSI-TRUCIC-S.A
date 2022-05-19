<?php if( class_exists( 'Wp_Travel_Engine' ) ) : ?>
  <?php if( get_theme_mod( 'best_tours_display_option', true ) ) : ?>
    <!-- banner-news -->
    <?php
      $category_id = absint( get_theme_mod( 'best_tours_category', '' ) );
      $title = get_theme_mod( 'best_tours_title', '' );
      $number_of_posts = get_theme_mod( 'number_of_best_tours', 8 );

      $args = array(
        'post_type' => 'trip',
        'posts_per_page' => $number_of_posts,
       );

       if( ! empty( $category_id ) ) {
        
        $args[ 'tax_query' ] = array(
            array(
              'taxonomy' => 'trip_types',
              'field'    => 'id',
              'terms'    => $category_id
            ),
        );
      }

      $query = new WP_Query( $args );

      set_query_var( 'query', $query );
      set_query_var( 'title', $title );
    ?>

    <?php if ( $query->have_posts() ) : ?>

      <?php
        $layout = get_theme_mod( 'best_tours_layout', 'one' );
        if( $layout == 'one' ) {
          get_template_part( 'layouts/homepage/trips/trip-layout', 'one' );
        }
        if( $layout == 'two' ) {
          get_template_part( 'layouts/homepage/trips/trip-layout', 'two' );
        }
        if( $layout == 'three' ) {
          get_template_part( 'layouts/homepage/trips/trip-layout', 'three' );
        } 
      ?>
    <?php endif; ?>
    <!-- banner-news -->
  <?php endif; ?>
<?php endif;