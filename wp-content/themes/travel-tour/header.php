<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <header>
 *
 * @package Travel Tour Pro
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="<?php echo esc_url( 'http://gmpg.org/xfn/11' ); ?>">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<?php wp_body_open(); ?>

<?php $menu_sticky = get_theme_mod( 'travel_tour_header_sticky_menu_option', false ); ?>

<?php
	// Default values for 'travel_tour_social_media' theme mod.
	$defaults = "";
	$social_media = get_theme_mod( 'travel_tour_social_media', $defaults );
?>



<?php
	set_query_var( 'menu_sticky', $menu_sticky );
	set_query_var( 'social_media', $social_media );
	
	get_template_part( 'layouts/header/header-layout', 'one' );
	
?>