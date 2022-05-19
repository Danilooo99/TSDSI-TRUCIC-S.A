<?php

namespace DgoraWcas\Integrations;

use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Brands
 * @package DgoraWcas\Integrations
 *
 * Support for plugins:
 * 1. WooCommerce Brands since v1.6.9 by WooCommerce
 * 2. YITH WooCommerce Brands Add-on since v1.3.3 by YITH
 * 3. Perfect WooCommerce Brands since v1.8.3 by Alberto de Vera Sevilla
 * 4. Martfury Addons since v2.2.2 by drfuri.com
 * 5. Brands for WooCommerce since v3.5.2 by BeRocket
 * 6. WP Bingo by wpbingo
 */
class Brands {
	/**
	 * Brands plugin metadata
	 *
	 * @var array
	 */
	private $pluginInfo = array();

	/**
	 * Brands plugin slug
	 *
	 * @var string
	 */
	private $pluginSlug = '';

	/**
	 * Brand taxonomy name
	 *
	 * @var string
	 */
	private $brandTaxonomy = '';

	public function __construct() {
	}

	public function init() {
		$this->setPluginInfo();
		$this->setBrandTaxonomy();
		$this->addSettings();

		add_filter( 'dgwt/wcas/suggestion_details/taxonomy/headline', array( $this, 'rebuildDetailsHeader' ), 10, 4 );
	}

	/**
	 * Set current brands vendor plugin
	 *
	 * @return void
	 */
	private function setPluginInfo() {
		foreach ( $this->getBrandsPlugins() as $pluginPath ) {
			if ( is_plugin_active( $pluginPath ) ) {

				$file = WP_PLUGIN_DIR . '/' . $pluginPath;

				if ( file_exists( $file ) ) {
					$this->pluginInfo = get_plugin_data( $file );
					$this->pluginSlug = $pluginPath;
				}

				break;
			}
		}
	}

	/**
	 * Set brand taxonomy name
	 *
	 * @return void
	 */
	private function setBrandTaxonomy() {
		$brandTaxonomy = 'product_brand';

		if ( $this->hasBrands() ) {
			switch ( $this->pluginSlug ) {
				case 'yith-woocommerce-brands-add-on-premium/init.php':
				case 'yith-woocommerce-brands-add-on/init.php':
					$brandTaxonomy = 'yith_product_brand';
					break;
				case 'perfect-woocommerce-brands/main.php':
				case 'perfect-woocommerce-brands/perfect-woocommerce-brands.php':
					$brandTaxonomy = 'pwb-brand';
					break;
				case 'brands-for-woocommerce/woocommerce-brand.php':
					$brandTaxonomy = 'berocket_brand';
					break;
				case 'wpbingo/wpbingo.php':
					$brandTaxonomy = 'product_brand';
					break;
			}
		}

		$brandTaxonomy = apply_filters( 'dgwt/wcas/brands/taxonomy', $brandTaxonomy );

		$this->brandTaxonomy = $brandTaxonomy;
	}

	/**
	 * Get all supported brands plugins files
	 *
	 * @return array
	 */
	public function getBrandsPlugins() {
		return array(
			'woocommerce-brands/woocommerce-brands.php',
			'yith-woocommerce-brands-add-on/init.php',
			'yith-woocommerce-brands-add-on-premium/init.php',
			'perfect-woocommerce-brands/main.php',
			'perfect-woocommerce-brands/perfect-woocommerce-brands.php',
			'martfury-addons/martfury-addons.php',
			'brands-for-woocommerce/woocommerce-brand.php',
			'wpbingo/wpbingo.php',
		);
	}

	/**
	 * Check if some brands plugin is enabled
	 *
	 * @return bool
	 */
	public function hasBrands() {
		return apply_filters( 'dgwt/wcas/brands/has_brands', ! empty( $this->pluginInfo ) );
	}

	/**
	 * Get brand taxonomy
	 *
	 * @return string
	 */
	public function getBrandTaxonomy() {
		return ! empty( $this->brandTaxonomy ) ? sanitize_key( $this->brandTaxonomy ) : '';
	}

	/**
	 * Get the name of the plugin vendor
	 *
	 * @return static
	 */
	public function getPluginName() {
		return ! empty( $this->pluginInfo['Name'] ) ? sanitize_text_field( $this->pluginInfo['Name'] ) : '';
	}

	/**
	 * Get the name of the plugin vendor
	 *
	 * @return static
	 */
	public function getPluginVersion() {
		return ! empty( $this->pluginInfo['Version'] ) ? sanitize_text_field( $this->pluginInfo['Version'] ) : '';
	}

	/**
	 * Register settings
	 *
	 * @return void
	 */
	private function addSettings() {
		if ( $this->hasBrands() ) {
			add_filter( 'dgwt/wcas/settings/section=search', function ( $settingsScope ) {

				$desc = '';

				$pluginName = $this->getPluginName();

				if(!empty($pluginName)){
					$pluginInfo = $pluginName . ' v' . $this->getPluginVersion();
					$desc = sprintf( __( 'based on the plugin %s', 'ajax-search-for-woocommerce' ), $pluginInfo );
				}

				$settingsScope[220] = array(
					'name'    => 'search_in_brands',
					'label'   => __( 'Search in brands', 'ajax-search-for-woocommerce' ),
					'class'   => 'dgwt-wcas-premium-only',
					'type'    => 'checkbox',
					'default' => 'off',
					'desc'    => $desc
				);

				return $settingsScope;
			} );

			add_filter( 'dgwt/wcas/settings/section=autocomplete', function ( $settingsScope ) {

				$desc = '';

				$pluginName = $this->getPluginName();

				if(!empty($pluginName)){
					$pluginInfo = $pluginName . ' v' . $this->getPluginVersion();
					$desc = sprintf( __( 'based on the plugin %s', 'ajax-search-for-woocommerce' ), $pluginInfo );
				}

				$settingsScope[1260] = array(
					'name'    => 'show_matching_brands',
					'label'   => __( 'Show brands', 'ajax-search-for-woocommerce' ),
					'class'   => 'dgwt-wcas-premium-only',
					'type'    => 'checkbox',
					'default' => 'off',
					'desc'    => $desc
				);

				return $settingsScope;
			} );


		}
	}

	/**
	 * Rebuild details panel header for brands
	 *
	 * @param $title
	 * @param $termID
	 * @param $taxonomy
	 * @param $termName
	 *
	 * @return string
	 */
	public function rebuildDetailsHeader( $title, $termID, $taxonomy, $termName ) {

		if ( ! empty( $taxonomy ) && $taxonomy === $this->getBrandTaxonomy() ) {

			$title = '<span class="dgwt-wcas-datails-title">';
			$title .= '<span class="dgwt-wcas-details-title-tax">';
			$title .= Helpers::getLabel( 'brand' ) . ': ';
			$title .= '</span>';
			$title .= $termName;
			$title .= '</span>';
		}

		return $title;

	}

}
