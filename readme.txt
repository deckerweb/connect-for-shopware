=== Connect for Shopware ===
Contributors: daveshine
Tags: shopware, gutenberg, bricks, products
Requires at least: 6.6
Tested up to: 7.1.2
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Shopware products directly in Gutenberg and Bricks.

Contents: Description · Installation · Usage · FAQ · Screenshots · Changelog

== Description ==

Products/variants, gallery, description, data sheets, manufacturer, Shopware prices, dynamic categories, related products, CTA and Quick View. Shared Core with cache and diagnostics. No cart/checkout or duplicate product maintenance.

== Requirements ==

WordPress ≥ 6.6 · PHP ≥ 8.1 · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

== Installation ==

Download the plugin ZIP from the latest GitHub release, upload it under Plugins → Add New → Upload Plugin and activate it. Save the public HTTPS shop address under Settings → Connect for Shopware. Provide DW_SW_ACCESS_KEY server-side in wp-config.php or the PHP environment, then test the connection. Updates preserve settings and product assignments.

== Usage ==

Select products in the editor. Optionally install Bricks Components and examples in Settings → Connect for Shopware. Existing Components remain untouched.

== Frequently Asked Questions ==

= Does WordPress calculate prices? =
No. Shopware supplies all prices and discounts.

= Do I need Bricks Components? =
No. Native elements work immediately; Components are optional.

= Do updates overwrite component designs? =
No. Existing definitions are skipped; version copies are optional.

= How do I obtain updates? =
Install the ZIP from the GitHub release. Published stable updates appear in the normal WordPress update system. Automatic updates remain your choice.

== Screenshots ==

1. Settings: shop connection, cache and diagnostics.

== Changelog ==

= 1.0.0 · 2026-10-06 =
* New: Read-only Shopware products and variants in native Gutenberg blocks and Bricks elements.
* New: Product gallery, descriptions, data sheets, manufacturer, Shopware prices and availability.
* New: Article-related products, dynamic product grids, product buttons and optional Quick View.
* New: Configurable shop connection, cache, diagnostics and optional Bricks Components.
* New: WordPress updates from GitHub, optional deckerweb plugin catalog and German translations.


Author: David Decker – DECKERWEB · https://github.com/deckerweb
Hooks: dw_sw_post_types, dw_sw_cache_refreshed, dw_sw_page_cache_purge_requested.
German translation included. Full documentation: docs/wiki/Deutsch.md / docs/wiki/English.md.
