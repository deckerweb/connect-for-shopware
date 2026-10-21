<?php
/** Generated localized admin content from the shared editorial source. */
defined( 'ABSPATH' ) || exit;
return [
    'history' =>
        '= 1.1.1 · 2026-10-22 =
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Updated the shared plugin catalog to Library 1.1.0 and the GitHub updater to 2.2.1.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Use the bundled updater reliably alongside older updater copies and clean its repository cache during uninstall.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Made admin translations consistently extractable and tightened input and output handling without changing saved selections.', 'connect-for-shopware' ).'
' .
        '
' .
        '= 1.1.0 · 2026-10-20 =
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Find saved product selections in the Product usage tab, open their source content and check availability without changing assignments.', 'connect-for-shopware' ).'
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Refresh individual products and dynamic grids in Gutenberg and the classic article selector without clearing unrelated product caches.', 'connect-for-shopware' ).'
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Export shop URLs and cache preferences without secrets; review and confirm changes before importing them.', 'connect-for-shopware' ).'
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Use the setup checklist to review connection settings and the last tested Sales Channel context.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Manage settings and product usage on one admin page with directly linkable tabs and clearer spacing. Existing usage bookmarks remain supported.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Identify products in Gutenberg and the classic selector using preview images, article numbers and family or variant labels.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Reset Gutenberg display controls while preserving product selections, custom content and native block styles.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Reuse product results within each page request to avoid repeated cache reads and shop requests.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Update the embedded Plugin Library to 1.0.0 and improve shared-data cleanup during batch uninstall.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Include a permanently visible trademark notice in settings and matching local notice documents.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Provide blue vector artwork with consistent banner crops and locally available assets.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Validate plugin identity and platform requirements during bulk updates.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Keep dialogs closed until opened and provide keyboard, close and documentation fallbacks.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Load classic product search and variant previews correctly with plain WordPress permalinks.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Hide the empty Components panel when the required Bricks integration is unavailable.', 'connect-for-shopware' ).'
' .
        '
' .
        '= 1.0.1 · 2026-10-06 =
' .
        '* '.__( 'Improved', 'connect-for-shopware' ).': '.__( 'Load product styles only for rendered blocks and elements.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Complete German formal translations for settings, editor controls and update messages.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Remove Connector temporary caches and locks on uninstall while preserving settings, assigned products and installed Components.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Fixed', 'connect-for-shopware' ).': '.__( 'Resolve explicit server-side credentials per Multisite site while retaining the global-key fallback.', 'connect-for-shopware' ).'
' .
        '* '.__( 'Misc', 'connect-for-shopware' ).': '.__( 'Complete bilingual documentation, release metadata and shared component integration.', 'connect-for-shopware' ).'
' .
        '
' .
        '= 1.0.0 · 2026-10-06 =
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Read-only Shopware products and variants in native Gutenberg blocks and Bricks elements.', 'connect-for-shopware' ).'
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Product gallery, descriptions, data sheets, manufacturer, Shopware prices and availability.', 'connect-for-shopware' ).'
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Article-related products, dynamic product grids, product buttons and optional Quick View.', 'connect-for-shopware' ).'
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'Configurable shop connection, cache, diagnostics and optional Bricks Components.', 'connect-for-shopware' ).'
' .
        '* '.__( 'New', 'connect-for-shopware' ).': '.__( 'WordPress updates from GitHub, optional deckerweb plugin catalog and German translations.', 'connect-for-shopware' ).'
' .
        '
',
    'notice' => [
        'title' => __( 'Trademark notice and scope of integration', 'connect-for-shopware' ),
        'paragraphs' => [__( '“Shopware” is a registered trademark of shopware AG. Connect for Shopware is an independently developed WordPress plugin by deckerweb. It is not published, sponsored, endorsed or supported by shopware AG. The name “Shopware” is used solely to identify the supported integration.', 'connect-for-shopware' ), __( 'The plugin is installed exclusively in WordPress. It uses the publicly documented Shopware Store API with a Sales Channel access key provided by the shop operator to retrieve product data available through the respective sales channel and display it in WordPress.', 'connect-for-shopware' ), __( 'The plugin does not modify product data, settings or program files in the connected Shopware shop, nor does it install an extension there. Product management, price calculation, cart and checkout remain in Shopware.', 'connect-for-shopware' )],
    ],
];
