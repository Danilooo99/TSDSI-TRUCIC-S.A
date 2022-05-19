=== WooCommerce Extended Coupon Features FREE ===
Contributors: josk79
Tags: woocommerce, coupons, discount
Requires at least: 4.9
Requires PHP: 5.6
Tested up to: 5.6.2
Stable tag: 3.2.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Additional functionality for WooCommerce Coupons: Allow discounts to be automatically applied, applying coupons via url, etc...

== Description ==

"WooCommerce Extended Coupon Features" adds functionality to the WooCommerce coupons and allows for automatic discount rules. 
Very easy to use, the functionality is conveniently integrated to the WooCommerce Edit Coupon panel.

Requires:
* WooCommerce 3.0 or newer (4.0+ recommended)
* PHP 5.6 or newer (7.2+ Recommended)
* WordPress 4.9 or newer

For compatibility with older versions of WooCommerce, please use "WooCommerce Extended Coupon Features" version 2.6.5.

Full documentation is available at [www.soft79.nl](http://www.soft79.nl/documentation/wjecf).

* *Auto coupons*: Allow coupons to be automatically added to the users cart if its restrictions are met,
* Apply coupon via an url,
* Restrict coupon by shipping method,
* Restrict coupon by payment method,
* Restrict coupon by a combination of products
* Restrict coupon to certain customer roles
* (PRO) Add *free products* to the customer's cart based on coupon rules
* (PRO) Allow a cart discount to be applied based on quantity / subtotal of matching products
* (PRO) Set Auto Coupon priorities (Useful for 'Individual Use Only'-coupons)
* (PRO) Restrict coupon by shipping zone
* (PRO) Allow coupon for first purchase only
* (PRO) API to allow developers to use functions of this plugin

For more information or the PRO version please visit [www.soft79.nl](http://www.soft79.nl)

= Example: Auto coupon =

Let the customer have a discount of $ 5.00 when the cart reaches $ 50.00. 

1. Create a coupon, let's name it *auto_50bucks* and enter a short description e.g. *$ 50.00 order discount*
2. On the General tab: Select discount type *Cart discount*, and set the coupon amount to $ 5.00
3. On the Usage restrictions tab: Set minimum spend to $ 50.00 and check the *Auto coupon*-box

Voila! The discount will be applied when the customer reaches $ 50.00 and a descriptive message will be shown.

If the restrictions are no longer met, it will silently be removed from the cart.

= Example: Apply coupon via an URL =

Apply coupon through an url like this:

1. Use the url www.example.com/url-to-shop?apply_coupon=my_coupon&fill_cart=123

Voila! Any coupon can be applied this way. Please note that an empty cart can not contain any coupons. Download the free [Cart Links for WooCommerce-plugin](https://www.soft79.nl/product/cart-links-for-woocommerce/) to handle the 'fill_cart'-part of the url.


This plugin has been tested in combination with [WP-Multilang](https://wordpress.org/plugins/wp-multilang/).

== Installation ==

1. Upload the plugin in the `/wp-content/plugins/` directory, or automatically install it through the 'New Plugin' menu in WordPress
2. Activate the plugin through the 'Plugins' menu in WordPress

= How to create an automatically added coupon? =

1. Create a coupon through the 'Coupons' menu in WooCommerce. TIP: Name it auto_'whatever' so it will be easy to recognize the auto coupons
2. Setup the coupon as you'd normally would. Make sure you enter a description for the coupon and set usage restrictions
3. In the "Miscellaneous" tab, check the box *Auto coupon*
4. Voila! That's it

== Frequently Asked Questions ==

= Is the plugin translatable? =

Yes, all string values are translatable through the supplied POT/PO/MO files. In WPML translatable items appear in the context `woocommerce-jos-autocoupon` in "String Translations".

= Why isn't my coupon applied using www.example.com?apply_coupon=my_coupon ? =

The coupon will only be applied if the url links to a WooCommerce page (e.g. product loop / cart / product detail ) and at least one product is in the cart.
An empty cart can not have any coupons. The PRO version of this plugin has a work around for this though; it will 'remember' the coupon and apply it at the moment the cart contains a product.

= The cart is not updated after changing the payment method =

On the settings page (Settings > WooCommerce Extended Coupon Features) check the box *Update order review on payment method change*.

= The cart is not updated after changing the billing email address =

On the settings page (Settings > WooCommerce Extended Coupon Features) check the box *Update order review on billing email change*.


== Screenshots ==

1. Allow a coupon to be applied automatically by checking "Auto coupon".
2. Extra restrictions. E.g. Quantity or subtotal of matching products.
3. (PRO) A free product has been applied to the cart
4. Additional restrictions based on shipping or payment method or the customer

== Changelog ==

= 3.2.7 =
*Release Date - 2021-02-13*
* FIX: User restrictions on backend orders

= 3.2.6 =
*Release Date - 2021-01-16*
* (PRO) FIX: Don't display free product selection when there are no items to choose from
* FEATURE: 'Table Rate Shipping' by WooCommerce compatibility

= 3.2.5 =
*Release Date - 2020-11-30*
* FEATURE: Defer an applied coupon if it's valid and 'allow queueing' is enabled
* FEATURE: 'Table Rate Shipping for WooCommerce' by Border Elements compatibility
* FEATURE: Support for WP-Multilang (requires patch https://github.com/VaLeXaR/wp-multilang/pull/172 in WP-Multilang)
* FIX: Typo making filter 'wjecf_first_order_statuses_to_check' useless

= 3.2.4 =
*Release Date - 2020-11-06*
* FEATURE: Flexible shipping instances compatibility

= 3.2.3 =
*Release Date - 2020-10-18*
* PERFORMANCE: (PRO) Auto update: applied caching to prevent many requests to the webserver

= 3.2.2 =
*Release Date - 2020-08-27*
* (PRO) First purchase only: Add 'Pending payment' to order statuses and apply filter wjecf_first_order_statuses_to_check to allow override of the statuses to check

= 3.2.1 =
*Release Date - 2020-08-18*
* (PRO) FEATURE: Filter 'wjecf_free_products_to_apply_for_coupon'
* (PRO) FEATURE: Filter 'wjecf_bogo_products_to_apply_for_coupon'

= 3.2.0 =
*Release Date - 2020-05-11*
* (PRO) FEATURE: Limit discount to n lowest priced items
* (PRO) FEATURE: Accept Envato license key
* FEATURE: Include/exclude shipping method, instance or zone (PRO)
* FIX: Coupon data as url: Urlencode the coupon code
* FIX: Minor fix in WJECF_Debug log_the_request()

= 3.1.4 =
*Release Date - 2020-03-28*
* (PRO) FEATURE: Restrict coupon by shipping zone

= 3.1.3 =
*Release Date - 2019-11-25*
* (PRO) FIX: Custom meta allow number or boolean (yes | no) values

= 3.1.2 =
*Release Date - 2019-09-18*
* (PRO) FEATURE: Filter wjecf_is_first_purchase

= 3.1.1 =
*Release Date - 2019-08-13*
* FIX: Auto coupon priority issue when cart content changes and another 'individual use'-coupon takes precedence
* (PRO) FIX: Free products: Only set cart item quantity when it has changed (fixes issue with woo-paypalplus which clears session at quantity change)

= 3.1.0 =
*Release Date - 2019-05-21*
* TWEAK: Remove domainname from redirect url after using ?apply_coupon=
* (PRO) FIX: Free products: Don't apply a product that is not purchasable
* (PRO) TWEAK: Call WC core add_to_cart instead of custom add_to_cart-function
* (PRO) TWEAK: Free product selection: Show variation attributes in product title

= 3.0.7 =
*Release Date - 2019-02-14*
* FIX: wjecf_dump missing meta values
* TWEAK: Changed coupon discount html overwrite of Free-product-coupons (PRO) and Auto-coupons
* (PRO) FEATURE: Custom coupon error message
* (PRO) FIX: Bug in 'Limit discount to' in combination with WC prior to 3.3.0

= 3.0.6 =
*Release Date - 2018-11-27*
* (PRO) FIX: Product custom field filter for internal meta (e.g. _sale_price)

= 3.0.5 =
*Release Date - 2018-11-10*
* FIX: Limit discount to: in combination with WC < 3.2
* FIX: Added quantity to 'woocommerce_add_cart_item_data'-filter (Fixes crash WooCommerce Product Addons)
* FIX: 'Auto coupons'-settings not visible in FREE version

= 3.0.4 =
*Release Date - 2018-10-12*
* FIX: Auto coupons: silently remove invalid coupons
* FIX: WPML compatibility issue with "CATEGORIES AND"
* (PRO) FIX: CSS of the column system (box-sizing: border-box)

= 3.0.2 =
*Release Date - 2018-09-03*
* FIX: Email restrictions compatibility with WooCommerce versions prior to 3.4
* FIX: Individual use conflict
* FIX: (FREE) Missing debug template

= 3.0.1 =
*Release Date - 2018-09-02*
* FIX: Fatal error in FREE version of the plugin
* FIX: Require PHP5.4

= 3.0.0 =
*Release Date - 2018-09-02*
* IMPORTANT: Requires WooCommerce 3.0+ WordPress 4.8+ and PHP 5.3+
* FEATURE: Filter 'wjecf_apply_with_other_coupons' to disallow certain coupon combinations
* FEATURE: Update order review on payment/billing email change on checkout page (see settings page)
* FIX: Auto-coupon check usase limits per user and respect email restriction wildcards
* FIX: Auto-coupon in combination with individual_use respects exception filters
* FIX: Coupon queueing: Case sensitive coupon code compare issue
* FIX/PERFORMANCE: Rewritten handling of auto-coupons. Performance improvement
* (PRO) ENHANCEMENT: Free products: Better handling and performance of cart ajax events
* (PRO) FIX: Duplicate notices when enqueuing a coupon
* INTERNAL: Removed code for backwards compatibility with WC versions prior to 3.0
* INTERNAL: Rewritten boot-process. Class auto-loading. Code cleanup.

= 2.6.4 =
*Release Date - 2018-08-16*
* FIX: Download JSON compatiblilty issue with WC 2.6.x
* FIX: WPML Translate coupon title

= 2.6.3 =
*Release Date - 2018-06-04*
* FIX: WJECF_Controller: Don't use wp_get_current_user() for admin-orders
* FIX: WJECF_Controller: Don't use WC()->cart->subtotal for admin-orders
* FIX: Possible division by zero when calculating multiplier value
* FEATURE: Filter 'wjecf_coupon_multiplier_value' to allow overriding the coupon's multiplier value

= 2.6.2 =
*Release Date - 2018-04-02*
* FEATURE: Auto-coupon compatibility with the 'WooCommerce Free Gift Coupons'-plugin
* FIX: CATEGORIES AND in combination with variable products
* FIX: Call to undefined function wc_add_notice()
* FIX: (PRO) First order purchase: Ignore cancelled/waiting for payment order statuses
* FIX: (PRO) Auto updater plugins_api return $def instead of false

= 2.6.1.1 =
*Release Date - 2017-12-24*
* FIX: (PRO) Possible crash on null reference in filter woocommerce_coupon_get_discount_amount

= 2.6.1 =
*Release Date - 2017-12-22*
* German translation (Thanks to, Guido Hloch)
* FIX: Use WC_Coupon::get_description (for translation plugins)
* FIX: (PRO) ADMIN - Auto update: Allow multiple license activation
* FIX: (PRO) ADMIN - Auto update: Removed invalid warning 'Invalid response block'

= 2.6.0.2 =
*Release Date - 2017-12-04*
* FIX: Typo 'impode' (Thanks to Constantine for reporting)

= 2.6.0.1 =
*Release Date - 2017-12-02*
* ADMIN: Fix: Changelog of plugin update screen

= 2.6.0 =
*Release Date - 2017-12-02*
* ADMIN: Compatibility with coupons added on the Order page from wp-admin (requires WC3.3+)
* ADMIN: Show 'settings' link on the plugin screen
* INTERNAL: Moved debugging functions to WJECF_Debug. Use template/log.php for output rendering of the log
* (PRO) FEATURE: Limit coupon to first time purchase only
* (PRO) ENHANCEMENT: Free products: Better grid layout of the free product selector
* (PRO) ENHANCEMENT: Free products: Auto submit of selection (works for inputs in container with class 'wjecf-auto-submit')
* (PRO) FIX: Free products: Respect "sold individually" when adding free products to the cart
* (PRO) FIX: Free products: WC2.6 compatibility (product->get_status())

= 2.5.5.1 = 
*Release Date - 2017-11-07*
* (PRO) FIX: 'Limit discount to' not applied correctly in combination with WC3.2.3

= 2.5.5 =
*Release Date - 2017-11-01*
* (PRO) FIX: Free products: Removed unnecessary <tr> in cart / checkout table if no free product selection applies
* (PRO) FIX: Free products: Preserve notices when updating cart after applying/removing a coupon
* (PRO) FIX: Free products: Compatibility with Subscriptions plugin (prevent trigger of calculate_totals when adding free product to the cart)
* (PRO) FIX: Free products: Compatibility with WPML plugin (translation of the free products)

= 2.5.4 =
*Release Date - 2017-09-23*
* TWEAK: Get coupon description using WC_Coupon::get_description() to respect applicable filters (for example used by Polylang)
* (PRO) FEATURE: Allow customer to remove 'Auto Coupons' from the cart (see settings page)
* (PRO) FEATURE: Filter 'wjecf_get_limit_to_options' to allow adding custom 'Limit to'-options
* (PRO) PERFORMANCE: Free products: Only load js and css when required
* (PRO) FIX: Free products: "update cart"-button not automatically enabled after selecting a free product (in combination with certain themes (e.g. Flatsome)
* (PRO) FIX: Free products: Prevent certain plugins from parsing attribute fields containing [products] as shortcode
* (PRO) FIX: Limit discount to cheapest item failed in WC3.0 in combination with a percent discount
* (PRO) FIX: Custom fields: Accept WC3.0 core fields (e.g. _price)
* (PRO) FIX: PHP Warnings in WJECF_Pro_Admin_Auto_Update

= 2.5.3 =
*Release Date - 2017-06-12*
* (PRO) FIX: Free products: Free product selection always visible on checkout-page
* (PRO) FIX: Free products: JS error when using IE / Safari
* (PRO) FIX: Free products: Cart contents was not updated when applying a coupon
* (PRO) FIX: Free products: wjecf_free_product_amount_for_coupon not called for $max_quantity variable in the template

= 2.5.2.2 =
*Release Date - 2017-06-01*
* (PRO) FIX: Documentation url
* (PRO) FIX: Forgot to raise version number, causing a permanent 'An update is available'

= 2.5.2.1 =
*Release Date - 2017-06-01*
* (PRO) FIX: Duplicate products at free product selection

= 2.5.2 =
* (PRO) FEATURE: Free product selection using checkboxes / numeric inputs
* (PRO) FIX: Free product selection fails if coupon code contains a space
* (PRO) FIX: CSS for column system used by the free product selection
* DOCUMENTATION: Added the API part to the documentation (work in progress)

= 2.5.1 =
* FIX: Draft settings not being saved
* FIX: PHP < 5.5 compatibility
* FIX: Abstract_WJECF_Plugin log function
* INTERNAL: Functions add_action_once / add_filter_once. To guarantee execution only once.
* INTERNAL: Updated the API example; also usable from CLI
* INTERNAL: Created Sanitizer for form data handling
* INTERNAL: Reorganised coupon meta handling ( Abstract_WJECF_Plugin::admin_coupon_meta_fields )

= 2.5.0 =
* (PRO) FEATURE: Auto update!
* (PRO) FEATURE: Checkbox 'Allow applying coupon when invalid'
* (PRO) FEATURE: Custom message when applying a coupon which does not yet validate
* FEATURE: Settings page
* FEATURE REMOVED: Experimental feature 'Allow when minimum spend not reached' (Use 'Allow applying coupon when invalid' instead)
* COSMETIC: Products tab is now 'Free Products' tab. Moved other items to the 'Usage restriction'-tab
* FIX: Issue with 'Allow discount on cart with excluded items'
* FIX: Notice if a free product without weight is added/removed to/from the cart
* FIX: Deprecation notice in WJECF_AutoCoupon::sort_auto_coupons
* INTERNAL: Introduced 'allow_overwrite_coupon_values'

= 2.4.3 =
* FIX: Customer selector WooCommerce 3.0.0 compatibility

= 2.4.2.1 =
* FIX: WooCommerce < 2.7 compatibility

= 2.4.2 =
* FIX: Invalid calculation of subtotal/quantity of matching product since WC 3.0.0
* FIX: Missing "PRODUCT AND/OR" selector on Admin since WC 3.0.0 (Javascript)
* FIX: WooCommerce version detection if woocommerce is not installed in /wp-content/plugins/woocommerce directory

= 2.4.1 =
* (PRO) FIX: Product selector compatability with select2 v4 (WooCommerce 3.0)

= 2.4.0 =
* FIX: WooCommerce 3.0.0 Compatibility
* INTERNAL: Also load textdomain from WP_LANG_DIR/woocommerce-jos-autocoupon/woocommerce-jos-autocoupon-LOCALE.mo

= 2.3.7.5 = 
* FIX: Limit usage to cheapest discounting the wrong product when the quantity of cheapest product was greater than 1.

= 2.3.7.4 = 
* FIX: Combining add-to-cart and apply_coupon in a single querystring

= 2.3.7.3 = 
* FIX: Invalid usage of get_plugin_data

= 2.3.7.2 = 
* FIX: Backwards compatibility with WooCommerce < 2.5.0
* INTERNAL: Introducing WJECF_WC() to maintain backwards compatibility

= 2.3.7.1 = 
* (PRO) FIX: Bug in 'Limit discount to'

= 2.3.7 =
* PERFORMANCE: Admin could hang in some occasions on a jQuery-selector
* FIX: Suppresed warnings were displayed by the Query Monitor plugin if pro files are missing
* FIX: WPML Compatibility
* FIX: Invalid textdomain in woocommerce_coupon_error (Thanks, 7o599)
* (PRO) FIX: 'Limit discount to' skipped non-matching products on cart % discount
* (PRO) FEATURE: Limit discount to every nth (matching) item in the cart

= 2.3.6 =
* FIX: Compatibility with WooCommerce < 2.3.0 for coupon by url
* COSMETIC: On the admin page, moved AND/OR selector near the product/categories input
* (PRO) FEATURE: Filter matching products by custom field.

= 2.3.5 =
* (PRO) FIX: Workaround for missing WooCommerce 2.6.3 constant WC_ROUNDING_PRECISION
* (PRO) FIX: Refresh the cart when a coupon is applied/removed by AJAX (to add/remove free products)

= 2.3.4 =
* FIX: WooCommerce 2.6 and UPS / USPS Shipping method compatibility ( those plugins use : as separator )
* FIX: Coupon by url (hook on wp_loaded instead of init)
* FIX: Admin pages invalid parsing of Chosen inputboxes WooCommerce < 2.3.0
* FIX: Free product on WooCommerce < 2.3.0
* INTERNAL: Rewritten overwrite_success_message methods
* (PRO) Ajax 'Apply coupon' support for free product selection on cart and checkout page. (OVERRIDEABLE TEMPLATE FILES UPDATED!)
* (PRO) FEATURE: Remember coupons that are not valid when applying (on the cart page) and apply them automatically when they validate

= 2.3.3 =
* FIX: limit_usage_to_x_items: Removed call to get_discount_amount from coupon_has_a_value; it is redundant and caused limit_usage_to_x_items to change
* (PRO) FEATURE: Filters wjecf_free_product_amount_for_coupon, wjecf_bogo_product_amount_for_coupon and wjecf_set_free_product_amount_in_cart
* (PRO) FEATURE: Keep track of by-url-coupons (?apply_coupon=) and apply when they validate
* (PRO) FIX: Experimental feature 'Allow discount on cart with excluded items' didn't work since 2.2.3
* (PRO) FIX: Invalid free product quantity applied when using both BOGO and FREE products in a single coupon.
* (PRO) FIX: limit_usage_to_x_items: Possible wrong discount on combination of limit_usage_to_x_items and _wjecf_apply_discount_to

= 2.3.2 =
* FEATURE: Display custom error message when coupon is invalidated by this plugin
* FIX: apply_coupon redirected to wrong url when home_url contained a subdirectory
* FIX: Remove add-to-cart when redirecting for apply_coupon
* FIX: Auto Coupon Backwards compatability for WooCommerce versions prior to 2.3.0 that don't have hook woocommerce_after_calculate_totals
* TRANSLATION: Persian. Thanks to Ehsan Shahnazi.

= 2.3.1.1 =
* TRANSLATION: Brazilian Portuguese. Thanks to Francisco.

= 2.3.1 =
* FIX: WPML Compatibility for AND Products / AND Categories
* FIX: Redirect to page without ?apply_coupon= after applying coupon by url
* FIX: Auto coupon meta_query issue (thanks to hwillson)
* FIX: Compatibility with WooCommerce prior to 2.2.9 (WC_Cart::get_cart_item)
* (PRO) FIX: Free products: Add variant attributes to cart items for variable products
* (PRO) FEATURE: Apply discount only to the cheapest product

= 2.3.0 =
* (PRO) FEATURE: Allow customer to choose a free product
* (PRO) FEATURE: Setting the priority of auto coupons (Useful for Individual use coupons)
* (PRO) FEATURE: Display extra columns on the Coupon Admin page (auto coupon, individual use, priority, free products)
* (PRO) TWEAK: Free products: Display 'Free!' as subtotal for free products, (adaptable with filter 'wjecf_free_cart_item_subtotal' )
* (PRO) FIX: Free products: Plugin wouldn't always detect the free products in cart and kept appending free products
* (PRO) Introduction of the API for developers, see wjecf-pro-api.php
* FEATURE: Filter to only display Auto Coupons on the Coupon Admin page
* FIX: Compatibilty PHP 5.4
* FIX: Rewritten and simplified Autocoupon removal/addition routine making it more robust
* FIX: Multiplier value calculation (as for now only used for Free Products)
* FIX: Coupon must never be valid for free products (_wjecf_free_product_coupon set in cart_item)
* INTERNAL: Refactoring of all classes
* INTERNAL: New log for debugging

= 2.2.5.1 =
* FIX: When checkbox 'Individual use' was ticked, Autocoupons would be removed/added multiple times

= 2.2.5 =
* (PRO) FEATURE: BOGO On all matching products
* FIX: Changed WooCommerce detection method for better Multi Site support
* (PRO) FIX: Free products: Fixed an inconsistency that could cause a loop on removal/adding of free variant products
* (PRO) TWEAK: Free products: Hooking before_calculate_totals for most cases but also on woocommerce_applied_coupon, which is required when one coupon is replaced by another
* INTERNAL: Check if classes already exist before creating them

= 2.2.4 =
* FEATURE: Online documentation added
* FEATURE: Use AND-operator for the selected categories (default is OR)
* FIX: Backwards compatibility with WooCommerce 2.3.7 (WC_Cart::is_empty)
* FIX: Backwards compatibility with WooCommerce < 2.3.0 (WC_Coupon::is_type, Chosen in stead of Select2)

= 2.2.3 =
* (PRO) FEATURE: Allow discount on cart with excluded items
* (PRO) FEATURE: Free products!
* FEATURE: Allow coupon in cart even if minimum spend not reached
* FEATURE: New coupon feature: Minimum / maximum price subtotal of matching products in the cart
* COSMETIC: Admin Extended coupon features in multiple tabs
* FIX: Create session cookie if no session was initialized when applying coupon by url
* TWEAK: Auto coupon: Use woocommerce_after_calculate_totals hook for update_matched_autocoupons
* API: New function: $wjecf_extended_coupon_features->get_quantity_of_matching_products( $coupon )
* API: New function: $wjecf_extended_coupon_features->get_subtotal_of_matching_products( $coupon )

= 2.2.1 =
* FIX: Prevent mulitple apply_coupon calls (for example through ajax)
* FIX: Don't redirect to cart when using WooCommerce's ?add-to-cart=xxx in combination with ?apply_coupon=xxx as this would prevent the application of the coupon.

= 2.2.0 =
* FIX: Lowered execution priority for apply_coupon by url for combinations with add-to-cart.
* FEATURE: New coupon feature: Excluded customer role restriction
* FEATURE: New coupon feature: Customer / customer role restriction
* FEATURE: New coupon feature: Minimum / maximum quantity of matching products in the cart
* FEATURE: New coupon feature: Allow auto coupons to be applied silently (without displaying a message)
* TWEAK: Moved all settings to the 'Extended features'-tab on the admin page.
* FIX: 2.0.0 broke compatibility with PHP versions older than 5.3
* FIX: Changed method to fetch email addresses for auto coupon with email address restriction
* FILTER: Filter wjecf_coupon_has_a_value (An auto coupon will not be applied if this returns false)
* FILTER: Filter wjecf_coupon_can_be_applied (An auto coupon will not be applied if this returns false)
* INTERNAL: db_version tracking for automatic updates
* INTERNAL: Consistent use of wjecf prefix. 
* INTERNAL: Renamed meta_key woocommerce-jos-autocoupon to _wjecf_is_auto_coupon

= 2.0.0 =
* RENAME: Renamed plugin from "WooCommerce auto added coupons" to "WooCommerce Extended Coupon Features"
* FEATURE: Restrict coupons by payment method
* FEATURE: Restrict coupons by shipping method	
* FEATURE: Use AND-operator for the selected products (default is OR)
* FIX: Validate email restrictions for auto coupons
* Norwegian translation added (Thanks to Anders Zorensen)

= 1.1.5 =
* FIX: Cart total discount amount showing wrong discount value in newer WooCommerce versions (tax)
* Performance: get_all_auto_coupons select only where meta woocommerce_jos_autocoupon = yes (Thanks to ircary)

= 1.1.4 =
* Translation support through .mo / .po files
* Included translations: Dutch, German, Spanish (Thanks to stephan.sperling for the german translation)

= 1.1.3.1 =
* FIX: Apply auto coupon if discount is 0.00 and free shipping is ticked	

= 1.1.3 =
* Don't apply coupon if the discount is 0.00
* Allow applying multiple coupons via an url using *?apply_coupon=coupon_code1,coupon_code2

= 1.1.2 =
* Minor change to make the plugin compatible with WooCommerce 2.3.1
* Loop through coupons in ascending order

= 1.1.1 =
* Tested with Wordpress 4.0

= 1.1.0 =
* Allow applying coupon via an url using *?apply_coupon=coupon_code*

= 1.0.1 =
* Don't add the coupon if *Individual use only* is checked and another coupon is already applied.

= 1.0 =
* First version ever!
