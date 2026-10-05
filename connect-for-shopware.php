<?php
/**
 * Plugin Name: Connect for Shopware
 * Description: Read-only Shopware products for Gutenberg and native Bricks elements.
 * Version: 0.5.1-dev
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: David Decker – DECKERWEB
 * Author URI: https://github.com/deckerweb
 * Plugin URI: https://github.com/deckerweb/connect-for-shopware
 * Update URI: https://github.com/deckerweb/connect-for-shopware
 * GitHub Plugin URI: https://github.com/deckerweb/connect-for-shopware
 * SPDX-License-Identifier: GPL-2.0-or-later
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: connect-for-shopware
 * Domain Path: /languages/
 */
defined('ABSPATH') || exit;
// Preserve existing data names; never load beside the legacy development plugin.
if(in_array('deckerweb-shopware-connector/deckerweb-shopware-connector.php',(array)get_option('active_plugins',[]),true)||isset(((array)get_site_option('active_sitewide_plugins',[]))['deckerweb-shopware-connector/deckerweb-shopware-connector.php'])) {
    add_action('admin_notices',static function(): void {echo '<div class="notice notice-error"><p>'.esc_html__('Deactivate the legacy deckerweb Shopware Connector before activating Connect for Shopware. Existing settings and product assignments are preserved.','connect-for-shopware').'</p></div>';});
    return;
}
define('DW_SW_FILE',__FILE__);
define('DW_SW_VERSION','0.5.1-dev');
define('DW_SW_DIR',__DIR__.'/');
define('DW_SW_URL',plugin_dir_url(__FILE__));
foreach(['Core','WordPressBridge','Presentation','Extras','Operations','Configuration','Components','GitHubUpdates','Plugin','Admin','Rest'] as $file) require_once DW_SW_DIR.'src/'.$file.'.php';
require_once DW_SW_DIR.'src/deckerweb-changelog-v1.php';
add_action('plugins_loaded',[Deckerweb\Shopware\Plugin::class,'boot']);
