<?php
/**
 * Plugin Name: Connect for Shopware
 * Plugin URI: https://github.com/deckerweb/connect-for-shopware
 * Description: Read-only Shopware products for Gutenberg and native Bricks elements.
 * Version: 1.0.1
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: David Decker – DECKERWEB
 * Author URI: https://github.com/deckerweb
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: connect-for-shopware
 * Domain Path: /languages/
 * Update URI: https://github.com/deckerweb/connect-for-shopware
 * GitHub Plugin URI: https://github.com/deckerweb/connect-for-shopware
 *
 * Copyright © 2026 David Decker – DECKERWEB.
 * SPDX-License-Identifier: GPL-2.0-or-later
 */
defined('ABSPATH') || exit;
// Preserve existing data names; never load beside the legacy development plugin.
if(in_array('deckerweb-shopware-connector/deckerweb-shopware-connector.php',(array)get_option('active_plugins',[]),true)||isset(((array)get_site_option('active_sitewide_plugins',[]))['deckerweb-shopware-connector/deckerweb-shopware-connector.php'])) {
    add_action('admin_notices',static function(): void {echo '<div class="notice notice-error"><p>'.esc_html__('Deactivate the legacy deckerweb Shopware Connector before activating Connect for Shopware. Existing settings and product assignments are preserved.','connect-for-shopware').'</p></div>';});
    return;
}
define('DW_SW_FILE',__FILE__);
define('DW_SW_VERSION','1.0.1');
define('DW_SW_DIR',__DIR__.'/');
define('DW_SW_URL',plugin_dir_url(__FILE__));
foreach(['Core','Lifecycle','WordPressBridge','Presentation','Extras','Operations','Configuration','Components','GitHubUpdates','Plugin','Admin','Rest'] as $file) require_once DW_SW_DIR.'src/'.$file.'.php';
require_once DW_SW_DIR.'src/deckerweb-changelog-v1.php';
add_action('plugins_loaded',[Deckerweb\Shopware\Plugin::class,'boot']);

require_once DW_SW_DIR.'includes/deckerweb-plugin-library/bootstrap.php';
deckerweb_library_register_v2(DW_SW_FILE,[],DW_SW_DIR.'includes/deckerweb-plugin-library');
