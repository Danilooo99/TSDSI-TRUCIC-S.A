<?php if( get_theme_mod( 'about_display_option', true ) ) : ?>
  <?php
    $aboutPage = get_page_by_path(get_theme_mod('about_page'));
    
    if(!empty($aboutPage)):
    global $post;
    $post = get_post($aboutPage);
    setup_postdata( $post );
?>


<?php
  set_query_var( 'about_ID', $post->ID );
?>

<?php
  $layout = get_theme_mod( 'about_section_layout', 'one' );
  if( $layout == 'one' ) {
    get_template_part( 'layouts/homepage/about/about-layout', 'one' );
  }
  if( $layout == 'two' ) {
    get_template_part( 'layouts/homepage/about/about-layout', 'two' );
  }
  if( $layout == 'three' ) {
    get_template_part( 'layouts/homepage/about/about-layout', 'three' );
  } 
?>

<?php wp_reset_postdata();  endif; endif;