<?php

namespace DgoraWcas\Integrations\Themes\ShopIsle;

use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ShopIsle {

	private $themeSlug = 'shop-isle';

	private $themeName = 'Shop Isle';

	public function __construct() {
		$this->maybeOverwriteSearch();

		add_filter( 'dgwt/wcas/settings', array( $this, 'registerSettings' ) );
	}

	/**
	 * Add settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function registerSettings( $settings ) {
		$key = 'dgwt_wcas_basic';

		$settings[ $key ][10] = array(
			'name'  => $this->themeSlug . '_main_head',
			'label' => sprintf( __( 'Replace %s search bar', 'ajax-search-for-woocommerce' ), $this->themeName ),
			'type'  => 'head',
			'class' => 'dgwt-wcas-sgs-header'
		);

		$settings[ $key ][52] = array(
			'name'  => $this->themeSlug . '_settings_head',
			'label' => sprintf( __( '%s Theme', 'ajax-search-for-woocommerce' ), $this->themeName ),
			'type'  => 'desc',
			'desc'  => Helpers::embeddingInThemeHtml(),
			'class' => 'dgwt-wcas-sgs-themes-label',
		);

		$img = DGWT_WCAS()->themeCompatibility->getThemeImageSrc();
		if ( ! empty( $img ) ) {
			$settings[ $key ][52]['label'] = '<img src="' . $img . '">';
		}

		$settings[ $key ][55] = array(
			'name'    => $this->themeSlug . '_replace_search',
			'label'   => __( 'Replace', 'ajax-search-for-woocommerce' ),
			'desc'    => sprintf( __( 'Replace all %s search bars with the %s.', 'ajax-search-for-woocommerce' ), $this->themeName, DGWT_WCAS_NAME ),
			'type'    => 'checkbox',
			'default' => 'off',
		);

		$settings[ $key ][90] = array(
			'name'  => $this->themeSlug . '_othersways__head',
			'label' => __( 'Alternative ways to embed a search bar', 'ajax-search-for-woocommerce' ),
			'type'  => 'head',
			'class' => 'dgwt-wcas-sgs-header'
		);

		return $settings;
	}

	/**
	 * Check if can replace the native search form with the FiboSearch form.
	 *
	 * @return bool
	 */
	private function canReplaceSearch() {
		$canIntegrate = false;

		if ( DGWT_WCAS()->settings->getOption( $this->themeSlug . '_replace_search', 'off' ) === 'on' ) {
			$canIntegrate = true;
		}

		return $canIntegrate;
	}

	/**
	 * Overwrite search
	 *
	 * @return void
	 */
	private function maybeOverwriteSearch() {
		if ( $this->canReplaceSearch() ) {
			$this->applyCSS();

			add_action( 'init', function () {
				remove_action( 'shop_isle_header', 'shop_isle_primary_navigation', 50 );
			} );

			add_action( 'shop_isle_header', array( $this, 'replaceSearchForm' ), 60 );
		}
	}

	/**
	 * Apply custom CSS
	 *
	 * @return void
	 */
	private function applyCSS() {

		add_action( 'wp_head', function () {
			?>
			<style>
				.dgwt-wcas-ico-magnifier-handler {
					fill: #cbc7c2;
					max-width: 16px;
					margin-top: 3px;
				}

				.dgwt-wcas-ico-magnifier-handler:hover {
					fill: #ffffff;
				}

				.dgwt-wcas-is-mobile .dgwt-wcas-ico-magnifier-handler {
					max-width: 20px;
				}
			</style>
			<?php
		} );

	}

	/**
	 * Replace search from
	 */
	public function replaceSearchForm() {
		if ( function_exists( 'shop_isle_primary_navigation' ) ) {
			ob_start();
			shop_isle_primary_navigation();
			$html = ob_get_clean();
			// https://regex101.com/r/AvkuEr/1/
			$re    = '/(.*<div class="header-search">)(.*<\/form>\s*<\/div>\s*)(<\/div>.*)/s';
			$subst = '$1' . do_shortcode( '[wcas-search-form layout="icon"]' ) . '$3';
			echo preg_replace( $re, $subst, $html );
		}
	}
}
