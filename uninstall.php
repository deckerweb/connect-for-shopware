<?php
/** Library lifecycle only; retain Connector settings and content assignments. */
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
require_once __DIR__.'/includes/deckerweb-plugin-library/lifecycle.php';
deckerweb_library_uninstall_v2(__DIR__.'/connect-for-shopware.php');
