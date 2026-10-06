<?php
/** Uninstall only Connector temporary data and the shared Library lifecycle. */
if (!defined('WP_UNINSTALL_PLUGIN') || WP_UNINSTALL_PLUGIN !== 'connect-for-shopware/connect-for-shopware.php') { exit; }
require_once __DIR__.'/src/Lifecycle.php';
\Deckerweb\Shopware\Lifecycle::uninstall();
require_once __DIR__.'/includes/deckerweb-plugin-library/lifecycle.php';
deckerweb_library_uninstall_v2(__DIR__.'/connect-for-shopware.php');
