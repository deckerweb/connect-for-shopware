=== Connect for Shopware ===
Contributors: daveshine
Tags: shopware, gutenberg, bricks, products
Requires at least: 6.6
Tested up to: 7.1.3
Requires PHP: 8.1
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Shopware products in your WordPress content using native Gutenberg blocks and Bricks elements. Shopware supplies the product data and prices.

== Description ==

- Products and exact variants, gallery, descriptions, data sheets and manufacturer.
- Shopware prices, reference/list prices, availability and direct product links.
- Article-related products, dynamic groups, standalone buttons and optional Quick View.
- Shared display controls for Gutenberg and Bricks; optional Bricks Components.
- Site-scoped connection, diagnostics and 15/30/60-minute cache.

**Version:** 1.1.1 · WordPress ≥ 6.6 · PHP ≥ 8.1 · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

== Installation ==

1. Download the plugin ZIP from the latest release: https://github.com/deckerweb/connect-for-shopware/releases/latest and upload it under Plugins → Add New → Upload Plugin.
2. Save the public HTTPS shop URL under Settings → Connect for Shopware.
3. Provide DW_SW_ACCESS_KEY through wp-config.php or the PHP environment and test the connection.

== Frequently Asked Questions ==

= Does WordPress calculate prices? =
No. Shopware supplies prices and discounts; WordPress displays them.

= What does the cache duration mean? =
Data is reused for 15, 30 or 60 minutes. The next request after expiry fetches fresh data.

= Do I need Bricks Components? =
No. Native elements work directly; Components are optional reusable layouts.

= Can I connect several shops? =
Configure one shop per WordPress site. Each shop needs its own matching server-side key.

= Does the plugin work in Multisite? =
Yes. Each site configures its own shop, cache and article assignments. Library settings are shared per network.

= What happens when I deactivate or uninstall? =
Settings, article assignments and Components remain. Uninstall clears temporary Connector data; shared Library data follows its own settings.

= How do I obtain updates? =
Stable GitHub releases appear in the normal WordPress update system. Automatic updates remain your choice.

Complete FAQ by topic: https://github.com/deckerweb/connect-for-shopware/wiki/FAQ-English

== Screenshots ==

1. Settings: connection, cache, diagnostics and optional Bricks Components.

== Changelog ==

= 1.1.1 · 2026-10-22 =
* Improved: Updated the shared plugin catalog to Library 1.1.0 and the GitHub updater to 2.2.1.
* Fixed: Use the bundled updater reliably alongside older updater copies and clean its repository cache during uninstall.
* Fixed: Made admin translations consistently extractable and tightened input and output handling without changing saved selections.

= 1.1.0 · 2026-10-20 =
* New: Find saved product selections in the Product usage tab, open their source content and check availability without changing assignments.
* New: Refresh individual products and dynamic grids in Gutenberg and the classic article selector without clearing unrelated product caches.
* New: Export shop URLs and cache preferences without secrets; review and confirm changes before importing them.
* New: Use the setup checklist to review connection settings and the last tested Sales Channel context.
* Improved: Manage settings and product usage on one admin page with directly linkable tabs and clearer spacing. Existing usage bookmarks remain supported.
* Improved: Identify products in Gutenberg and the classic selector using preview images, article numbers and family or variant labels.
* Improved: Reset Gutenberg display controls while preserving product selections, custom content and native block styles.
* Improved: Reuse product results within each page request to avoid repeated cache reads and shop requests.
* Improved: Update the embedded Plugin Library to 1.0.0 and improve shared-data cleanup during batch uninstall.
* Improved: Include a permanently visible trademark notice in settings and matching local notice documents.
* Improved: Provide blue vector artwork with consistent banner crops and locally available assets.
* Fixed: Validate plugin identity and platform requirements during bulk updates.
* Fixed: Keep dialogs closed until opened and provide keyboard, close and documentation fallbacks.
* Fixed: Load classic product search and variant previews correctly with plain WordPress permalinks.
* Fixed: Hide the empty Components panel when the required Bricks integration is unavailable.

= 1.0.1 · 2026-10-06 =
* Improved: Load product styles only for rendered blocks and elements.
* Fixed: Complete German formal translations for settings, editor controls and update messages.
* Fixed: Remove Connector temporary caches and locks on uninstall while preserving settings, assigned products and installed Components.
* Fixed: Resolve explicit server-side credentials per Multisite site while retaining the global-key fallback.
* Misc: Complete bilingual documentation, release metadata and shared component integration.

= 1.0.0 · 2026-10-06 =
* New: Read-only Shopware products and variants in native Gutenberg blocks and Bricks elements.
* New: Product gallery, descriptions, data sheets, manufacturer, Shopware prices and availability.
* New: Article-related products, dynamic product grids, product buttons and optional Quick View.
* New: Configurable shop connection, cache, diagnostics and optional Bricks Components.
* New: WordPress updates from GitHub, optional deckerweb plugin catalog and German translations.


== Credits and licenses ==

© 2026 David Decker – DECKERWEB. Shared deckerweb Updater, Plugin Library and active artwork: GPL-2.0-or-later.

== Support ==

https://github.com/deckerweb/connect-for-shopware/issues
https://github.com/deckerweb/connect-for-shopware/security/advisories/new
https://ko-fi.com/deckerweb
https://buymeacoffee.com/daveshine
https://paypal.me/deckerweb

== Trademark notice and scope of integration ==

“Shopware” is a registered trademark of shopware AG. Connect for Shopware is an independently developed WordPress plugin by deckerweb. It is not published, sponsored, endorsed or supported by shopware AG. The name “Shopware” is used solely to identify the supported integration.

The plugin is installed exclusively in WordPress. It uses the publicly documented Shopware Store API with a Sales Channel access key provided by the shop operator to retrieve product data available through the respective sales channel and display it in WordPress.

The plugin does not modify product data, settings or program files in the connected Shopware shop, nor does it install an extension there. Product management, price calculation, cart and checkout remain in Shopware.
