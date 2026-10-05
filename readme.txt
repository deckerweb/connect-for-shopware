=== Connect for Shopware ===
Contributors: daveshine
Tags: shopware, gutenberg, bricks, products
Requires at least: 6.6
Tested up to: 7.1.2
Requires PHP: 8.1
Stable tag: 0.5.1-dev
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Shopware products directly in Gutenberg and Bricks.

Contents: Description · Installation · Usage · FAQ · Changelog

== Description ==

Products/variants, gallery, description, data sheets, manufacturer, Shopware prices, dynamic categories, related products, CTA and Quick View. Shared Core with cache and diagnostics. No cart/checkout or duplicate product maintenance.

== Requirements ==

WordPress ≥ 6.6 · PHP ≥ 8.1 (Kunde/customer 8.3.x) · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

== Installation ==

Update the existing Connect for Shopware installation with the ZIP. In Settings → Connect for Shopware, enter and save the public HTTPS shop URL, then test the connection. A separate Store API URL is optional. No shop is preconfigured. Keep `DW_SW_ACCESS_KEY` server-side in wp-config.php or the PHP environment. Existing assignments are retained. When migrating from the old development name, deactivate that legacy plugin first.

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
Use the installable ZIP for now. GitHub release updates are prepared; publication and a live update have not been verified in this delivery.

== Changelog ==

= 0.5.1-dev · 2026-10-06 =
* Improved: User-approved Signal artwork in matte blue, with a lighter headline and paired German/English banners.
* Improved: Genuine reconstructed SVG icon and matching PNG icons in 128/256 pixels; banners in 772×250 and 1544×500 pixels.
* Improved: Versioned artwork URLs prevent browsers from retaining previous icons and banners after update.
* Improved: Editable SVG and matching PNG for every banner; dedicated GitHub banner 1280×640 in German and English.
* Misc: Export dimensions, locale artwork paths, cached updater icons and package contents verified; Connector behavior unchanged.


= 0.5.0-dev · 2026-10-06 =
* New: Configurable storefront URL and optional separate Store API endpoint; no preconfigured customer shop.
* Improved: Sales Channel, language, currency, formatting and tax labels derive from the anonymous Store API context; SEO URLs use that context.
* Improved: Shop-specific cache and outage scopes; shop changes invalidate cache, diagnostics and Quick View tickets.
* Fixed: Removed customer names, fixed domains, channel/language/product/category IDs and selections from source, documentation and starter templates.
* Misc: Server-side constant/environment credentials retained; generic multi-shop fixture tests and URL validation added.


= 0.4.0-dev · 2026-10-06 =
* New: Optional native Bricks Components installer with selection, populated examples and additional version copies.
* Improved: Connect for Shopware branding, stable slug, bundled artwork, bilingual documentation and shared updater V2.
* Fixed: Preserve existing components, assignments, settings and Gutenberg block identifiers during migration.
* Fixed: Bricks external CSS is generated once; avoid a second pass removing styles through selector deduplication.
* Misc: deckerweb standards audit and local migration/installer regression checks.


= 0.3.0-dev · 2026-10-05 =
* New: Connection test and safe diagnostics with last success, duration and neutral failure status.
* New: Optional detected page-cache purge on manual refresh for WP Rocket, LiteSpeed Cache, WP Super Cache and W3 Total Cache.
* New: Network-free product column in post/page lists and shared Gutenberg/Bricks presentation presets.
* New: Standalone Shopware Product Button Gutenberg block and native Bricks element.
* New: Optional category shop link and pagination for dynamic grids.
* New: Optional Quick View with gallery, tabs, shop button, signed read tickets and request limit.
* Improved: Outage fallback, 60-second retry pause, network budget and atomic cache locks; retained content hides expired prices and availability.
* Fixed: Gallery/tab IDs stay unique in dynamically loaded dialogs.
* Misc: Bilingual setup, cache behavior, limits and FAQ; expanded local regression checks.


= 0.2.0-dev · 2026-10-04 =
* New: Gutenberg Product Grid block and native Bricks element for dynamic product groups through active Shopware categories.
* New: Category selection, maximum product count, Shopware sorting, heading and responsive columns.
* New: Protected editor API and native Bricks AJAX category/sorting selectors.
* Improved: Shared repository and card rendering; Shopware evaluates rules and prices without duplicate rule maintenance.
* Misc: Dynamic categories verified, including pagination and price sorting. Implementation and setup documented in both languages.



Author: David Decker – DECKERWEB · https://github.com/deckerweb
Hooks: dw_sw_post_types, dw_sw_cache_refreshed, dw_sw_page_cache_purge_requested.
German translation included. Full documentation: docs/wiki/Deutsch.md / docs/wiki/English.md.
