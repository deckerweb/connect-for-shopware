<?php
/** Uninstall only Connector temporary data and the shared Library lifecycle. */
if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) { exit; }
require_once __DIR__.'/src/Lifecycle.php';
if (!\Deckerweb\Shopware\Lifecycle::is_uninstall_context()) { return; }
\Deckerweb\Shopware\Lifecycle::uninstall();
require_once __DIR__.'/includes/deckerweb-plugin-library/lifecycle.php';
deckerweb_library_uninstall_v4(__DIR__.'/connect-for-shopware.php');
