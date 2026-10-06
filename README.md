# Connect for Shopware

![Connect for Shopware](assets-github/banner-github-1280x640.png)

Display Shopware products directly in Gutenberg and Bricks.

[Deutsch](README-de.md) · [Full guide](docs/wiki/English.md) · [FAQ](docs/wiki/FAQ-English.md)

[Features](#0) · [Requirements](#1) · [Installation](#2) · [Usage](#3) · [FAQ](#4) · [Changelog](#5)

<a id="0"></a>

## Features

Products/variants, gallery, description, data sheets, manufacturer, Shopware prices, dynamic categories, related products, CTA and Quick View. Shared Core with cache and diagnostics. No cart/checkout or duplicate product maintenance.

Compact Connect settings with a visible connection status, collapsible technical details and the shared deckerweb Plugin Library 0.6.0.


<a id="1"></a>

## Requirements

WordPress ≥ 6.6 · PHP ≥ 8.1 · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

<a id="2"></a>

## Installation

Update the existing Connect for Shopware installation with the ZIP. In Settings → Connect for Shopware, enter and save the public HTTPS shop URL, then test the connection. A separate Store API URL is optional. No shop is preconfigured. Keep `DW_SW_ACCESS_KEY` server-side in wp-config.php or the PHP environment. Existing assignments are retained. When migrating from the old development name, deactivate that legacy plugin first.

<a id="3"></a>

## Usage

Select products in the editor. Optionally install Bricks Components and examples in Settings → Connect for Shopware. Existing Components remain untouched.

<a id="4"></a>

## FAQ

**Local pricing?** No. **Components required?** No. **Updates overwrite designs?** No. **Updates?** Published stable releases appear in WordPress updates. Development releases are installed using the ZIP.

<a id="5"></a>

## Changelog

### 1.0.0 · 2026-10-06
- **New:** First stable release: shared read-only Shopware Core for native Gutenberg blocks and Bricks elements, optional Bricks Components and configurable shop URLs.
- **Improved:** Compact Connect settings, connection status, collapsible technical details and approved matte-blue vector artwork.
- **Improved:** Original stable deckerweb Updater V2.1.0 and latest deckerweb Plugin Library 0.6.0 runtime integrated.
- **Misc:** Bilingual documentation and FAQ, installable stable-slug ZIP, complete changelogs and release checks; existing settings and product assignments preserved.


### 0.6.0-dev · 2026-10-06
- **New:** Bundled original deckerweb Plugin Library 0.6.0 with shared host registration.
- **Improved:** Compact Connect admin header, visible connection status and separate connection, cache and Bricks sections.
- **Improved:** Advanced connection settings and technical diagnostics collapse on demand; native controls and restrained matte-blue accents.
- **Improved:** Shared updater V2.1.0-dev.3 with translated host messages and existing package safeguards.
- **Misc:** Existing Shopware settings, product assignments, blocks and native Bricks elements retained.


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



© 2026 [David Decker – DECKERWEB](https://github.com/deckerweb) · GPL v2 or later (SPDX GPL-2.0-or-later).
