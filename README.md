# Connect for Shopware

![Connect for Shopware](assets-github/banner-github-1280x640.png)

Display Shopware products directly in Gutenberg and Bricks.

[Deutsch](README-de.md) · [Full guide](docs/wiki/English.md) · [FAQ](docs/wiki/FAQ-English.md)

[Features](#0) · [Requirements](#1) · [Installation](#2) · [Usage](#3) · [FAQ](#4) · [Changelog](#5)

<a id="0"></a>

## Features

Products/variants, gallery, description, data sheets, manufacturer, Shopware prices, dynamic categories, related products, CTA and Quick View. Shared Core with cache and diagnostics. No cart/checkout or duplicate product maintenance.

<a id="1"></a>

## Requirements

WordPress ≥ 6.6 · PHP ≥ 8.1 (Kunde/customer 8.3.x) · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

<a id="2"></a>

## Installation

Update the existing Connect for Shopware installation with the ZIP. In Settings → Connect for Shopware, enter and save the public HTTPS shop URL, then test the connection. A separate Store API URL is optional. No shop is preconfigured. Keep `DW_SW_ACCESS_KEY` server-side in wp-config.php or the PHP environment. Existing assignments are retained. When migrating from the old development name, deactivate that legacy plugin first.

<a id="3"></a>

## Usage

Select products in the editor. Optionally install Bricks Components and examples in Settings → Connect for Shopware. Existing Components remain untouched.

<a id="4"></a>

## FAQ

**Local pricing?** No. **Components required?** No. **Updates overwrite designs?** No. **Updates?** Through WordPress after a stable GitHub release is published. Development releases are installed using the ZIP.

<a id="5"></a>

## Changelog

### 0.5.1-dev · 2026-10-06
- **Improved:** User-approved Signal artwork in matte blue, with a lighter headline and paired German/English banners.
- **Improved:** Genuine reconstructed SVG icon and matching PNG icons in 128/256 pixels; banners in 772×250 and 1544×500 pixels.
- **Improved:** Versioned artwork URLs prevent browsers from retaining previous icons and banners after update.
- **Improved:** Editable SVG and matching PNG for every banner; dedicated GitHub banner 1280×640 in German and English.
- **Misc:** Export dimensions, locale artwork paths, cached updater icons and package contents verified; Connector behavior unchanged.


### 0.5.0-dev · 2026-10-06
- **New:** Configurable storefront URL and optional separate Store API endpoint; no preconfigured customer shop.
- **Improved:** Sales Channel, language, currency, formatting and tax labels derive from the anonymous Store API context; SEO URLs use that context.
- **Improved:** Shop-specific cache and outage scopes; shop changes invalidate cache, diagnostics and Quick View tickets.
- **Fixed:** Removed customer names, fixed domains, channel/language/product/category IDs and selections from source, documentation and starter templates.
- **Misc:** Server-side constant/environment credentials retained; generic multi-shop fixture tests and URL validation added.


### 0.4.0-dev · 2026-10-06
- **New:** Optional native Bricks Components installer with selection, populated examples and additional version copies.
- **Improved:** Connect for Shopware branding, stable slug, bundled artwork, bilingual documentation and shared updater V2.
- **Fixed:** Preserve existing components, assignments, settings and Gutenberg block identifiers during migration.
- **Fixed:** Bricks external CSS is generated once; avoid a second pass removing styles through selector deduplication.
- **Misc:** deckerweb standards audit and local migration/installer regression checks.


### 0.3.0-dev · 2026-10-05
- **New:** Connection test and safe diagnostics with last success, duration and neutral failure status.
- **New:** Optional detected page-cache purge on manual refresh for WP Rocket, LiteSpeed Cache, WP Super Cache and W3 Total Cache.
- **New:** Network-free product column in post/page lists and shared Gutenberg/Bricks presentation presets.
- **New:** Standalone Shopware Product Button Gutenberg block and native Bricks element.
- **New:** Optional category shop link and pagination for dynamic grids.
- **New:** Optional Quick View with gallery, tabs, shop button, signed read tickets and request limit.
- **Improved:** Outage fallback, 60-second retry pause, network budget and atomic cache locks; retained content hides expired prices and availability.
- **Fixed:** Gallery/tab IDs stay unique in dynamically loaded dialogs.
- **Misc:** Bilingual setup, cache behavior, limits and FAQ; expanded local regression checks.


### 0.2.0-dev · 2026-10-04
- **New:** Gutenberg Product Grid block and native Bricks element for dynamic product groups through active Shopware categories.
- **New:** Category selection, maximum product count, Shopware sorting, heading and responsive columns.
- **New:** Protected editor API and native Bricks AJAX category/sorting selectors.
- **Improved:** Shared repository and card rendering; Shopware evaluates rules and prices without duplicate rule maintenance.
- **Misc:** Dynamic categories verified, including pagination and price sorting. Implementation and setup documented in both languages.



© 2026 [David Decker – DECKERWEB](https://github.com/deckerweb) · GPL v2 or later (SPDX GPL-2.0-or-later).
