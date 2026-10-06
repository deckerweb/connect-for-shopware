# Connect for Shopware

![Connect for Shopware](assets-github/banner-github-1280x640.png)

Display Shopware products directly in Gutenberg and Bricks.

[Deutsch](README-de.md) · [Full guide](https://github.com/deckerweb/connect-for-shopware/wiki/English) · [FAQ](https://github.com/deckerweb/connect-for-shopware/wiki/FAQ-English)

[Features](#0) · [Requirements](#1) · [Installation](#2) · [Usage](#3) · [FAQ](#4) · [Settings](#settings) · [Changelog](#5)

<a id="0"></a>

## Features

Products/variants, gallery, description, data sheets, manufacturer, Shopware prices, dynamic categories, related products, CTA and Quick View. Shared Core with cache and diagnostics. No cart/checkout or duplicate product maintenance.

Compact Connect settings with a visible connection status, collapsible technical details and the optional deckerweb plugin catalog.


<a id="1"></a>

## Requirements

WordPress ≥ 6.6 · PHP ≥ 8.1 · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

<a id="2"></a>

## Installation

Download the [plugin ZIP from the latest release](https://github.com/deckerweb/connect-for-shopware/releases/latest), upload it under Plugins → Add New → Upload Plugin and activate it. Save the public HTTPS shop address under Settings → Connect for Shopware. Provide `DW_SW_ACCESS_KEY` server-side in wp-config.php or the PHP environment, then test the connection. Updates preserve settings and product assignments.

<a id="3"></a>

## Usage

Select products in the editor. Optionally install Bricks Components and examples in Settings → Connect for Shopware. Existing Components remain untouched.

<a id="4"></a>

## FAQ

**Local pricing?** No. **Components required?** No. **Updates overwrite designs?** No. **Updates?** Published stable releases appear in WordPress updates. Automatic updates remain your choice.


## Settings

![Connect for Shopware settings](assets/screenshots/settings-en.png)

<a id="5"></a>

## Changelog

### 1.0.0 · 2026-10-06
- **New:** Read-only Shopware products and variants in native Gutenberg blocks and Bricks elements.
- **New:** Product gallery, descriptions, data sheets, manufacturer, Shopware prices and availability.
- **New:** Article-related products, dynamic product grids, product buttons and optional Quick View.
- **New:** Configurable shop connection, cache, diagnostics and optional Bricks Components.
- **New:** WordPress updates from GitHub, optional deckerweb plugin catalog and German translations.


© 2026 [David Decker – DECKERWEB](https://github.com/deckerweb) · GPL v2 or later (SPDX GPL-2.0-or-later).
