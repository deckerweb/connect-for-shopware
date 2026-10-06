# Connect for Shopware · Guide

[Deutsch](Deutsch.md) · [FAQ](FAQ-English.md) · [Changelog](Changelog-English.md)

## Purpose and requirements

Version 1.0.0 displays Shopware products in WordPress without importing them. Configure one Shopware 6 shop per WordPress installation. No domain, Sales Channel, currency or customer product is preconfigured. Project API compatibility is tested against Shopware 6.7. WordPress 6.6+, PHP 8.1+ and the Store API are required. The previous release was user-confirmed on PHP 8.3.x; local tests use PHP 8.4.5. Native Bricks integrations are tested with 2.4.2. Gutenberg works without Bricks. Elementor pages can host WordPress output; no separate Elementor widget is included.

## Installation and migration

Deactivate the legacy deckerweb Shopware Connector first. Install the new connect-for-shopware ZIP and activate it. Do not run both entry points at once. The plugin protects against the active legacy version. Existing DW_SW_ACCESS_KEY configuration, options, article product assignments, native Bricks element names and Gutenberg block identifiers stay compatible. Remove the inactive legacy plugin afterwards. No uninstall routine deletes content.

Set the Sales Channel access key server-side in wp-config.php as DW_SW_ACCESS_KEY or in an environment variable of that name. Do not insert it in a block or template. Settings → Connect for Shopware shows configuration status without displaying the key. Test connection there. The anonymous context must match the intended currency, language and tax state.

## Products and presentation

Search by product name or number in the Gutenberg block or native Bricks element. Select a product family or an exact variant/size/colour. Changing the family clears the variant selection; the server also validates the variant relationship. The product card can show image/gallery, title, summary/description/custom text, variant label, current price, list price, discount, reference price, availability and shop link. Prices and discounts come from Shopware; WordPress never recomputes them. Family prices are marked “from”.

Image and main product information can be arranged left, right or below. Description/tabs follow the main information. Cover-only and complete available gallery are optional. Video previews link to their provider and do not automatically load a third-party player. Tabs can display description, available PDF documents and manufacturer data. Gallery entries and documents depend on the Sales Channel associations and available source data. Inherited covers may depict a different size.

Choose a text link or native styled button and current/new tab. New-tab links use noopener noreferrer. Product URLs prefer Shopware SEO URLs; missing SEO data uses a technical product URL. Quick View is optional, loads details only on demand, and uses signed read tickets with request limits. Escape closes the dialog and focus returns to its opener. Shop links remain usable if Quick View cannot load.

## Article products and dynamic grids

Assign ordered products in the article's Shopware Products document panel or the Related Products block inspector; save the article for frontend output. Related Products reads these assignments. In a Bricks Single template, choose a preview article with assignments. Product Button renders a standalone shop CTA.

Dynamic grids select an active Shopware category backed by a dynamic product group. Shopware evaluates its rules; WordPress does not replicate them. Choose maximum count and available Shopware sorting; empty sorting uses the shop default. Optional pagination and a category shop link are available. Multiple grids retain independent page parameters. Compact, description and details presentation presets are shared across Gutenberg and Bricks.

## Optional Bricks Components

With Bricks 2.4.2+ active, settings show the Components installer. Select individual components: product card, product details, related products, dynamic grid and product button. Optional starter templates with native elements but no product selection make slots immediately usable. Nothing is installed automatically.

Existing component IDs are skipped. Customized definitions and example templates are preserved. To try a bundled version separately, select the additional version-copy option. Repeating that same version does not create duplicate copies. Plugin updates do not replace installed definitions. Copying remaps root/slot references. The installer honors Bricks create-components/create-templates permissions and native import locks. In multisite with shared main-site Components, installation is available only on the main site.

Four definitions use native slots. Expand the slot in the structure panel and select its Shopware element to use normal AJAX product/category search. A newly inserted empty slot needs an element; alternatively insert an installed starter template and choose its product. Shared wrapper settings are edited centrally; slot element settings belong to each instance. Related Products exposes heading, button text and link target as connected component properties. Starter templates contain no product/category IDs. Choose selections from your own shop.

## Cache, diagnostics and safety

Default cache lifetime is 30 minutes; settings offer 15/30/60. Expiry means the next access requests fresh data, not a scheduled background fetch. Manual refresh clears the Connector cache. Optional integration clears the entire site's detected WP Rocket, LiteSpeed, WP Super Cache or W3 Total Cache page cache. Without one of those plugins, no page cache is purged. Server/CDN caches require separate coordination so visible prices do not remain cached beyond the intended interval.

The Core uses per-request network limits, atomic regeneration locks, a 60-second retry pause after failure, and retained content for temporary outages. Retained content hides expired price and availability; an explicit missing product removes its backup. Diagnostics record safe status and duration without raw credentials, context tokens or exception text. No WordPress cart/checkout, product import, remote write operation or Shopware extension is required.

## Documentation, updates and extension hooks

English source UI and a bundled German PHP/JavaScript translation are included. The standard footer opens the complete local structured changelog; without JavaScript its link opens the local text file. Documentation links follow the administrator language.

The public repository is https://github.com/deckerweb/connect-for-shopware. Stable releases include an installable ZIP on GitHub; bilingual documentation and FAQs are published in the Wiki. The shared GitHub updater V2.1.0 integrates with normal WordPress updates. Candidate identity, offered version and WordPress/PHP requirements are validated before replacement. Automatic updates are not enabled by the plugin.

Public hooks: dw_sw_post_types filters supported editorial types (post/page by default); dw_sw_cache_refreshed signals manual cache refresh; dw_sw_page_cache_purge_requested supports additional page-cache adapters. Existing hook names remain compatible.

## Verification and remaining acceptance

Local mapping, WordPress/Bricks rendering, AJAX permissions, cache fallback, gallery/tabs, CTA, pagination and Quick View regressions passed. Components installation, repeated installation, preservation of customizations, version copies, example references and external CSS generation are checked locally. The admin installation flow and changelog Escape/focus behavior are also browser-tested. Full licensed Bricks builder editing and responsive visual acceptance remain customer-test steps. PHP 8.3 execution was not performed locally for this version. No customer deployment or shop change was made.

© 2026 [David Decker – DECKERWEB](https://github.com/deckerweb). GPL v2 or later · SPDX GPL-2.0-or-later.

## User acceptance · 6 October 2026

The user confirms successful installation and full functionality of 0.4.0-dev on PHP 8.3.x, with the legacy plugin disabled. Previously pending PHP 8.3 acceptance is now user-confirmed; no local PHP 8.3 execution was performed. Individual Bricks builder/responsive checks were not separately described.

## Shop URL and server-side key since 0.5.0

After updating, enter the public storefront URL in Settings → Connect for Shopware. Existing installations must do this once; there is no legacy domain fallback. Save, then run the connection test. Shop URL and Sales Channel key are both required. The Store API URL defaults to the shop URL plus /store-api. For a language path such as /en or a separate API host, provide the actual complete API base URL in the optional field. Both URLs require public HTTPS on the standard port, without credentials, query strings or fragments. Local/private HTTP destinations and redirects are not supported; WordPress's safe HTTP transport checks destinations at request time.

The anonymous Sales Channel defaults determine language, currency and tax state. The storefront URL or language path does not itself switch that context. Verify the reported context against the intended storefront. Product/search operations remain read-only; there is no context PATCH or customer login. Formatting uses the supplied currency symbol and decimal precision; net/free/gross labels reflect the actual tax state. Unknown SEO language data falls back to the technical product URL rather than using another language's URL.

Changing shop/API URL clears Connector cache and diagnostics and invalidates previous Quick View tickets. Optional detected page-cache purge also runs. Existing product assignments and installed Components are intentionally retained; review selections when moving to a different catalog. Shop and key fingerprint separate cache entries. Previously customized Components are never silently modified; bundled Components 1.1.0 provide neutral starters as optional extra copies.

DW_SW_ACCESS_KEY can be supplied in two supported ways: a WordPress constant, usually defined in wp-config.php, or a PHP environment variable set by the host/server. A nonempty constant takes precedence. The hosting service must pass the environment variable to PHP; creating an arbitrary .env file is not sufficient and this plugin does not load .env files. An externally stored secret PHP file can define the same constant when explicitly included by wp-config.php; place that file outside the public web root and restrict access. This is optional server configuration, not a plugin-managed secret store. The key is not saved to WordPress options or displayed in the UI. Neither method guarantees safety if the server or backups are exposed; configure access appropriately. A secret manager can supply the same supported constant/environment interface.

[Shopware: prices and tax context](https://developer.shopware.com/frontends/frontends-recipes/catalog/prices.html)

## Approved artwork since 0.5.1

The user selected Signal in matte blue. Matching genuine SVG/PNG icons and German/English banners are bundled locally and used by the readmes and updater. This release changes artwork/version documentation only; shop configuration and Connector behavior are unchanged. Banner originals were refined/localized with the built-in imagegen tool. The icon was reconstructed as a vector; required raster sizes were exported from these sources.


## Connect settings

A compact header and status area show the shop address and last successful request. Status reflects the last known result, not continuous live monitoring. Run the connection test to check the current state. API overrides and technical diagnostics are collapsible. Connection, cache and optional Bricks Components use separate sections. The shared deckerweb Plugin Library adds the central catalog; the Connector remains independent of other deckerweb plugins. Shopware credentials stay server-side. The updated updater uses the public repository mode; no additional GitHub credentials are required.
