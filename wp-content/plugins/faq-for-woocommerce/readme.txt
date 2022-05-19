=== XPlainer - WooCommerce Product FAQ [WooCommerce Accordion FAQ Plugin] ===
Contributors: wpfeelteam, nayanchamp7, mriajur
Tags: accordion, faq, faqs, woocommerce faqs, product faq, accordian faq, question and answer, faqs sorting, accordian plugin, accordions ,vertical accordion, faq, faq widget, accordion widget, accordion menu, wordpress faq plugin, easy accordion , premium accordion, bootstrap accordion, horizontal accordion, responsive accordion, accordion jquery, css3 accordion, accordion shortcode, collapsible content, toggle, toggle accordion, wordpress accordion plugin, wordpress accordion, bootstrap collapse, post accordion, post faq ,custom post accordion ,accordion grid, accordion bar, woocommerce product accordion, woocommerce accordion, wordpress post accordion, content hide,
hidden content, expand content, FAQs list, Gutenberg FAQs, FAQ block, accordion FAQs, toggle FAQs, filtered FAQs, grouped FAQs, FAQs order, woocommerce, product tabs, repeatable, duplicate, customize, custom, tabs, product, woo, commerce, Q&A, pre sale, product enquiry, ecommerce, e-commerce, questions, answers, QnA, product tab
Requires at least: 3.6
Tested Up To: 5.9
Requires PHP: 5.6
Stable tag: 1.3.31
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ultimate FAQ and accordion plugin create Woocommerce faq lists with comment & FAQ schema support, responsive, full customization & shortcode support.

== Description ==

FAQ plugin for Woocommerce makes and shows Frequently Asked Questions about products. With FAQ for Woocommerce, You can easily make woocommerce faq lists for per product in product edit page options.

[__Documentation__](https://wpfeel.net/docs/faq-for-woocommerce/overview/) | [__Support__](https://wordpress.org/support/plugin/faq-for-woocommerce/)

For product faq list you get multiple template with relative design here. Right now we are with <strong>4 templates</strong> and more are coming soon. This plugin makes product faq list with those standard template with any themes supported.For customer satisfaction the templates are smart tools for you.

> This advanced Woocommerce Question Answer plugin can make your sales skyrocket and increase your business’s online visibility.Indeed, a tool that should not be missed for any product site.

This Wordpress Accordion Plugin makes sure to enhance more productive supports for woocommerce. You can use this spread your product for customer knowledge base as your Advanced FAQ Manager. So, you can manage Frequently Asked Questions with this quick and easy faqs plugin with Faq troubleshooting.

This plugin is fully responsive with any wordpress themes. FAQ for woocommerce is easy to make quick and advanced faqs with ultimate accordion faq features specially FAQ Schema Supports. Specially it supports Per FAQ comment.

FAQ for Woocommerce is a flexible accordion plugin to customize FAQs. You can easily customize with style settings. Woocommerce product questions and answers can be displayed with 4 standard and stylish layout.

## Instructions ##
For Access to settings page, go through **Woo FAQ > Settings**, Add faqs from **Woo FAQ > Add New**. After adding faqs question and answers, go to any product edit page and you will find faqs select dropdown from **Product Data Tab > FAQs**. Search and Select your FAQs and sort by your expected order. That's it.

## Video Introduction to the FAQ for Woocommerce Plugin ##
This tutorial is before 1.3.0 version, after 1.3.0 there has been added more advanced features in this wordpress faq plugin.

[youtube https://www.youtube.com/watch?v=3o0ETEhglwY]

## 🔧 AVAILABLE FEATURES ##
✔  Per product FAQ
✔  Unlimited FAQs
✔  FAQ Post support
✔  FAQ Comment support (since 1.3.18)
✔  Schema Support
✔  FAQ sortable feature
✔  Shortcode Support
✔  Custom Style Support
✔  Fully Responsive
✔  Media/Image support in faq answer and before/after
✔  Content before and after FAQ list
✔  HTML content - answer markup would be html
✔  Multiple Layout - standard templates for front view
✔  WP Editor for html markup, WYSIWYG popular editor
✔  Woocommerce compatible style for product information tab
✔  FAQ layout preview in setting page
✔  FAQ tab Reorder setting
✔  FAQ Answer show/hide on page load
✔  Expand/Collapse All Faqs option (since 1.3.31)

## 🚩 Product FAQs Comment ##
To enable comment support, please go through "Woo FAQ > Settings > Comment" and select enable. Comments and Comment forms are fully customizable. Customers or visitors can submit comment to every product faqs. Admin can approve comments and shows in front. Comment supports helps you to get feedback of the customers on your woocommerce products and the product faqs.

## 🚩 Shortcode ##

Display all FAQs for a random product having faqs with default template:
`
[ffw_template]
`

Display all FAQs for a specific product:

[Note: Here we used 20 as product id]

`
[ffw_template id=20]
`

Display FAQs for specific faq categories:

[Note: If the cat_ids exists then product id will be ignored, Here 32 & 33 is faq categories id, use comma separator]

[Note: You can sort faqs with 'order' and 'order_by' parameter]

Supported Values for 'order' (Default Value: 'ASC')
- 'DESC', 'ASC'

Supported Values for 'order_by' (Default Value: 'ID')
- 'ID', 'date', 'title', 'name', 'date', 'modified', 'comment_count', 'author'

`
[ffw_template cat_ids="32, 33"]
`

Display FAQs for a current product id (for single product page):

[Note: If the page is single product page you can just simply use this shortcode]

`
[ffw_template dynamic_post=true]
`

Display all FAQs for a specific product with a specific template by this Easy Accordion FAQ plugin:

Use following template ids to show faqs
- For Classic Template - 1
- For Whitish Template - 2
- For Trip Template - 3
- For Pop Template - 4

`
[ffw_template template=1 id=20]
`

## ☂️ Template Names ##
- Classic Template
- Whitish Template
- Trip Template
- Pop Template

## 💚 Loved FAQ For Woocommerce? ##

👆 Join our [Facebook Page](https://www.facebook.com/wpfeel)
⭐ Rate us on WordPress [Your Review, Our Inspiration]

== Installation ==

Before installing the plugin please make sure that

- Your php version is 5.4 or greater
- Wordpress version is 3.6 or greater
- WooCommerce version is 2.4 or greater

This section describes how to install the plugin and get it working.

= Search Way: =
1. Go to the WordPress Dashboard "Add New Plugin" section.
2. Search For "FAQ For Woocommerce".
3. Install, then Activate it.

= Manual Way: =
1. Upload `faq-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go through menu like `Woo FAQ > Settings`.
4. In product tab section you will find a menu called `FAQs`, click it, click `Add new` and a popup will come.
5. Add question and add answer.
6. Done!

== Frequently Asked Questions ==
Our plugin is user friendly, but here you can see some frequently asked
questions that can help you.

= Does it work with any theme? =

Yes, it will work with any standard WordPress theme.

= Is this plugin responsible? =

Yes, this awesome Faq WooCommerce Questions and Answers plugin is responsive with any media devices.

= Can we write html content? =

Yes, you can write html markup for answers with wp editor.

= Can I sort faq list? =

Yes,You can sort faq list easily. We have added the support since version 1.3.0

= Is it support Schema? =

Yes,We have Google Rich Schema support since version 1.2.2

= Can we use media? =

Yes, you can use media in the content.We have media support since version 1.1.5

= Can we use custom style? =

Yes, you can customize with your own styles.We have style support since version 1.2.0

= Does it support comments? =

Yes, Visitors or Customers can comment on per product faqs. Admin can customize the full comments and product questions answers comment forms. We have comment support since version 1.3.18

= How many template can we use? =

Currently we provide four standard templates, more template coming soon.

= Can I use shortcode to show faqs? =

Absolutely yes! you can use [ffw_template] to show faqs, for details please see shortcode description above.

== Screenshots ==
1. Trip Template
2. Whitish Template
3. Classic Template
4. In product tab in edit page, faq options
5. Add new and update faq Popup
6. FAQ settings from 'Woocommerce > FAQs' menu
7. Actions for update, delete faqs

Yes, This plugin works with any WordPress theme.

== Changelog ==

= 1.3.31 =
* Added: Expand/Collapse all faqs settings.
* Added: Dashboard new UI.

= 1.3.30 =
* Added: FAQ post type pages index/noindex settings.

= 1.3.29 =
* Added: FAQ schema support for faq shortcode.

= 1.3.28 =
* Fixed: FAQ tab shows empty faq - issue has been fixed.

= 1.3.27 =
* Added: Order and sorting feature for shortcode, order and orderby parameter added.

= 1.3.26 =
* Fixed: Woocommerce Tested upto 6.3 version.

= 1.3.25 =
* Fixed: WordPress Tested upto 5.9.2 version.

= 1.3.24 =
* Fixed: Fatal Error about Too few arguments has been solved.

= 1.3.23 =
* Added: FAQs by categories shortcode added, please see description to know how to add specific categories faqs by shortcode.

= 1.3.22 =
* Added: WordPress 5.9 version compatibility added.

= 1.3.21 =
* Added: Woocommerce 6.0.0 version compatibility added.
* Fixed: Undefined before & after output for fresh installation - issue has been fixed.

= 1.3.20 =
* Fixed: Undefined function for schema does not work - issue has been fixed.

= 1.3.19 =
* Added: Schema description type options and schema settings. Now user can choose to add description with HTML and without HTML.
* Fixed: New product adding page has faq broken markup - issue has been fixed.

= 1.3.18 =
* Added: Product FAQs Comments support has been added. You just have to enable the comment support from "Woo FAQs > Settings > Comment" page.

= 1.3.17 =
* Added: Show or Hide FAQs Counter in front with faqs tab. By default, the counter option is hide, you need to enable it from the settings and faqs counter will show for the current product page.
* Fixed: Performance enhancement.

= 1.3.16 =
* Added: Extra form submit button added in setting page at the top.
* Added: Documentation page link has been added in the plugin page.

= 1.3.15 =
* Fixed: During FAQ search clear an extra faq added in product edit page, the issue has been fixed.
* Fixed: Deleting the extra FAQ not working, the issue has been fixed.

= 1.3.14 =
* Added: FAQ answer text color style setting has been added.
* Added: WooCommerce 5.7.1 compatibility added.

= 1.3.13 =
* Fixed: Ajax issue of the faqs adding from search in product page has been fixed.

= 1.3.12 =
* Improved: Manages boring review notice.
* Improved: WordPress 5.8.1 compatibility tested.

= 1.3.11 =
* Added: New Settings for general and dynamic shortcode preview.

= 1.3.10 =
* Added: Dynamic product id feature for shortcode to use in single product page has been added.

= 1.3.9 =
* Added: WordPress 5.8 version compatibility tested.

= 1.3.8 =
* Fixed: FAQ sorting does not work after insert from search and quick add form - issue has been solved.
* Added: Loader added after adding faq from search options.

= 1.3.7 =
* Added: Demo and Documentation link added in readme.
* Fixed: Classic template spacing issue has been fixed.

= 1.3.6 =
* Fixed: FAQ answer page load issue fixed.
* Fixed: Code enhancement.

= 1.3.5 =
* Added: FAQ Reorder settings added.
* Added: FAQ Answers on page load settings added.

= 1.3.4 =
* Fixed: Post content as faq answer showing issue has been fixed.

= 1.3.3 =
* Fixed: Trip template Iframe issue has been solved. Thanks to @gao9099 to inform us.

= 1.3.2 =
* Fixed: Select2 undefined issue has been solved.

= 1.3.1 =
* Added: FAQs counter in product list table, counter show/hide options in settings page.
* Added: FAQs settings instruction has been added, every options are instructed.
* Added: FAQs post type access roles, admin can set roles to access ffw post type.

= 1.3.0 =
* Added: FAQs post feature.
* Added: FAQs sortable feature.
* Added: FAQs translation file.

= 1.2.8 =
* Added: FAQs width control in style.
* Added: FAQs tab label is now dynamic.
* Fixed: FAQs Schema mainEntity issue solved.

= 1.2.7 =
* Tweak: WordPress 5.7 compatibility tested.

= 1.2.6 =
* Added: Minified all the assets files.

= 1.2.5 =
* Tweak: Admin setting preview templates in apple monitor look.

= 1.2.4 =
* Fixed: Popup position wrong has been solved.

= 1.2.3 =
* Added: Setting link added as action link.

= 1.2.2 =
* Added: Schema support added.
* Fixed: Global bootstrap file loaded issue solved.

= 1.2.1 =
* Fixed: Setting page design broken issue solved.
* Fixed: Undefined function issue solved.

= 1.2.0 =
* Added: Custom styling options.
* Fixed: Popup wrong position solved.

= 1.1.7 =
* Fixed: Junk file cleaned.
* Tweak: Performance enhancement.

= 1.1.6 =
* Added: Plugin compatibility test with wordpress 5.6.

= 1.1.5 =
* Added: Media/Image support for faq answer.
* Added: Media/Image support for before/after content.

= 1.1.4 =
* Fix: Speed Optimization.
* Fix: Clean junks.

= 1.1.3 =
* Added: Shortcode support.
* Fix: Label showing when faqs empty, solved.

= 1.1.2 =
* Tweak: Admin panel preview new look.
* Fix: After insert faq answer as html.

= 1.1.1 =
* Tweak: Textdomain updated.
* Fix: Pop template animation.

= 1.1.0 =
* Added: Pop template.

= 1.0.9 =
* Fix: Setting page escaping issues.
* Tweak: Performance enhancement.

= 1.0.8 =
* Added: Trip template.
* Fix: Junk cleaned.

= 1.0.7 =
* Fix: Coding Standard solved.
* Tweak: Cleaning and enhancement.

= 1.0.6 =
* Tweak: Runtime template preview.

= 1.0.5 =
* Added: Whitish front template.

= 1.0.4 =
* Added: Product metabox loader.

= 1.0.3 =
* Tweak: FAQ options header button new style.
* Fix: FAQ options static line to dynamic.
* Fix: Junk clean up.

= 1.0.2 =
* Added: Update faq feature.
* Tweak: DOM manipulation in faq list.
* Fix: Code enhancement.

= 1.0.1 =
* Fix: Add or delete faq issue.
* Tweak: Rename Labels.

= 1.0.0 =
* First Release.