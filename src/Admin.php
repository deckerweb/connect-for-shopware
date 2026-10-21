<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;
/**
 * Manage Connector settings, editor product assignments and the accessible footer.
 */
final class Admin {
    /**
     * Register the WordPress integration callbacks for this component.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function boot(): void {
        add_filter('plugin_action_links_'.plugin_basename(DW_SW_FILE),[self::class,'settingsLink']);
        add_action('admin_menu',/**
 * Add the site settings page for administrators.
 *
 * @return void No return value.
 */ static function() {
            add_options_page('Connect for Shopware','Connect for Shopware','manage_options','dw-sw',[self::class,'page']);
        });
        add_action('admin_init',/**
 * Register cache preferences with bounded sanitizers.
 *
 * @return void No return value.
 */ static function() {
            register_setting('dw_sw','dw_sw_purge_pages',['type'=>'boolean','default'=>true,'sanitize_callback'=>/**
 * Normalize the page-cache purge preference.
 *
 * @param mixed $v Submitted setting value.
 * @return bool Result for the documented callback.
 */ static fn($v)=>(bool)$v]);
            register_setting('dw_sw','dw_sw_cache_ttl',['type'=>'integer','default'=>1800,
                'sanitize_callback'=>/**
 * Limit reusable product data to fifteen through sixty minutes.
 *
 * @param mixed $v Submitted setting value.
 * @return int Result for the documented callback.
 */ static fn($v)=>max(900,min(3600,(int)$v))]);
        });
        add_action('admin_post_dw_sw_refresh',/**
 * Authorize a manual refresh, invalidate cache and redirect to settings.
 *
 * @return void No return value.
 */ static function() {
            if(!current_user_can('manage_options')) wp_die(esc_html__('Access denied.','connect-for-shopware'));
            check_admin_referer('dw_sw_refresh');(new TransientCache())->refresh();/**
             * Notify site-scoped cache integrations after a manual refresh or shop change.
             *
             * @since 1.0.0
             */
            do_action('dw_sw_cache_refreshed');
            wp_safe_redirect(admin_url('options-general.php?page=dw-sw&refreshed=1'));exit;
        });
        add_action('add_meta_boxes',/**
 * Register product assignment controls on supported editorial screens.
 *
 * @return void No return value.
 */ static function() {
            foreach(Plugin::postTypes() as $type) add_meta_box('dw-sw-products',__('Shopware products','connect-for-shopware'),[self::class,'metaBox'],$type,'side','default',['__back_compat_meta_box'=>true]);
        });
        add_action('admin_menu',[self::class,'legacyUsage'],99);
        add_action('save_post',[self::class,'save']);
        add_action('admin_enqueue_scripts',[self::class,'assets']);
    }
    /**
     * Prepend the website settings link for administrators of the current site.
     *
     * @param array $links Existing plugin action links.
     * @return array Links with Settings before the activation action.
     */
    public static function settingsLink(array $links): array {
        if(current_user_can('manage_options')&&!is_network_admin()) {
            $links=['dw_sw_settings'=>'<a href="'.esc_url(admin_url('options-general.php?page=dw-sw')).'">'.esc_html__('Settings','connect-for-shopware').'</a>']+$links;
        }
        return $links;
    }
    /**
     * Enqueue and localize settings/editor assets only on supported admin screens.
     *
     * @since 1.0.0
     * @param string $hook WordPress admin screen identifier.
     * @return void No return value; effects are described above.
     */
    public static function assets(string $hook): void {
        if(in_array($hook,['post.php','post-new.php'],true)){
            $screen=function_exists('get_current_screen')?get_current_screen():null;
            if(!$screen||!in_array($screen->post_type,Plugin::postTypes(),true))return;
        }
        if(in_array($hook,['post.php','post-new.php','settings_page_dw-sw'],true)) {
            wp_enqueue_style('dw-sw-admin',DW_SW_URL.'assets/admin.css',[],DW_SW_VERSION);
            wp_enqueue_script('dw-sw-admin',DW_SW_URL.'assets/admin.js',[],DW_SW_VERSION,true);
            wp_localize_script('dw-sw-admin','dwSwAdmin',['root'=>rest_url('deckerweb-shopware/v1/'),'nonce'=>wp_create_nonce('wp_rest'),
                'labels'=>['search'=>__('Search products','connect-for-shopware'),'add'=>__('Add','connect-for-shopware'),
                    'remove'=>__('Remove','connect-for-shopware'),'family'=>__('Product family','connect-for-shopware'),
                    'variant'=>__('Product / variant','connect-for-shopware'),'variants'=>__('Choose variant','connect-for-shopware'),
                    'up'=>__('Move up','connect-for-shopware'),'down'=>__('Move down','connect-for-shopware'),
                    'next'=>__('Next results','connect-for-shopware'),'previous'=>__('Previous results','connect-for-shopware'),
                    'enabled'=>__('Enabled','connect-for-shopware'),'disabled'=>__('Disabled','connect-for-shopware'),'invalidImport'=>__('Invalid settings file. Only shop URLs and cache preferences are accepted; access keys and product assignments are excluded.','connect-for-shopware'),'refreshed'=>__('Selection refreshed. Full-page caches may still need clearing.','connect-for-shopware'),'refresh'=>__('Refresh this selection','connect-for-shopware'),'checking'=>__('Checking saved selections…','connect-for-shopware'),'checked'=>__('Check complete. Saved selections are unchanged.','connect-for-shopware'),'available'=>__('Available in the Sales Channel','connect-for-shopware'),'missing'=>__('Not found in the Sales Channel. Review this selection.','connect-for-shopware'),'unreachable'=>__('Could not be checked. Try again when the connection is available.','connect-for-shopware'),'exported'=>__('Settings downloaded without access keys or product assignments.','connect-for-shopware'),'setting'=>__('Setting','connect-for-shopware'),'before'=>__('Current value','connect-for-shopware'),'after'=>__('Imported value','connect-for-shopware'),'previewReady'=>__('Review the changes, then apply them explicitly. Nothing has been saved yet.','connect-for-shopware'),'imported'=>__('Settings imported. Test the connection with the server-side key on this site.','connect-for-shopware'),'dw_sw_shop_url'=>__('Shopware shop URL','connect-for-shopware'),'dw_sw_api_url'=>__('Store API URL (optional)','connect-for-shopware'),'dw_sw_cache_ttl'=>__('Cache lifetime in seconds','connect-for-shopware'),'dw_sw_purge_pages'=>__('Clear detected page caches on manual refresh','connect-for-shopware'),'error'=>__('Shop data unavailable.','connect-for-shopware'),'empty'=>__('No products found.','connect-for-shopware')]]);
        }
    }
    /** Resolve the requested section using a closed allowlist.
     * @return string The usage section or the default settings section.
     */
    public static function tab(): string {
        return isset($_GET['tab'])&&is_string($_GET['tab'])&&sanitize_key(wp_unslash($_GET['tab']))==='usage'?'usage':'settings';
    }
    /** Build a current-site section link without carrying unrelated request parameters.
     * @param string $tab Requested section; unknown values select settings.
     * @return string Absolute administration URL.
     */
    public static function tabUrl(string $tab='settings'): string {
        return add_query_arg(['page'=>'dw-sw','tab'=>$tab==='usage'?'usage':'settings'],admin_url('options-general.php'));
    }
    /** Preserve old usage bookmarks without registering another menu entry.
     * @return void Redirects authorized legacy GET requests and ends processing.
     */
    public static function legacyUsage(): void {
        global $pagenow;
        if($pagenow!=='options-general.php'||sanitize_key(wp_unslash($_SERVER['REQUEST_METHOD']??'GET'))!=='get'||!isset($_GET['page'])||!is_string($_GET['page'])||sanitize_key(wp_unslash($_GET['page']))!=='dw-sw-usage'||!current_user_can('manage_options'))return;
        $page=isset($_GET['usage_page'])&&is_scalar($_GET['usage_page'])?max(1,min(10000,absint($_GET['usage_page']))):1;
        wp_safe_redirect(add_query_arg('usage_page',$page,self::tabUrl('usage')));exit;
    }
    /** Output ordinary navigation links so sections also work without JavaScript.
     * @param string $active Validated active section.
     * @return void Each request renders only the selected section.
     */
    private static function tabs(string $active): void {
        echo '<nav class="nav-tab-wrapper dw-sw-tabs" aria-label="'.esc_attr__('Plugin sections','connect-for-shopware').'">';
        foreach(['settings'=>__('Settings','connect-for-shopware'),'usage'=>__('Product usage','connect-for-shopware')] as $tab=>$label){
            echo '<a class="nav-tab'.($tab===$active?' nav-tab-active':'').'" href="'.esc_url(self::tabUrl($tab)).'"'.($tab===$active?' aria-current="page"':'').'>'.esc_html($label).'</a>';
        }
        echo '</nav>';
    }
    /**
     * Output the ordered article product selector and its save nonce.
     *
     * @since 1.0.0
     * @param \WP_Post $post Article being edited.
     * @return void No return value; effects are described above.
     */
    public static function metaBox($post): void {
        wp_nonce_field('dw_sw_products','dw_sw_products_nonce');
        $items=Presentation::associations(get_post_meta($post->ID,'_dw_sw_products',true));
        echo '<div class="dw-sw-association-editor"><input type="hidden" name="dw_sw_products" value="'.esc_attr(wp_json_encode($items)).'"><div class="dw-sw-association-mount"></div></div>';
        echo '<noscript>'.esc_html__('JavaScript is required to select products. Existing assignments are preserved.','connect-for-shopware').'</noscript>';
    }
    /**
     * Persist validated article assignments after capability, nonce and autosave checks.
     *
     * @since 1.0.0
     * @param int $postId WordPress article or preview post ID.
     * @return void No return value; effects are described above.
     */
    public static function save(int $postId): void {
        if((defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)||wp_is_post_revision($postId)||!current_user_can('edit_post',$postId)) return;
        if(!in_array(get_post_type($postId),Plugin::postTypes(),true)) return;
        if(!isset($_POST['dw_sw_products_nonce'],$_POST['dw_sw_products'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dw_sw_products_nonce'])),'dw_sw_products')) return;
        $raw=is_string($_POST['dw_sw_products'])?sanitize_text_field(wp_unslash($_POST['dw_sw_products'])):null;if(!is_string($raw)||strlen($raw)>16384) return;
        $items=json_decode($raw,true);if(!is_array($items)) return;
        update_post_meta($postId,'_dw_sw_products',Presentation::associations($items));
    }
    /**
     * Output the last connection result and collapsible technical diagnostics.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    private static function diagnostics(): void {
        $state=(array)get_option('dw_sw_diagnostics',[]);$test=(array)get_option('dw_sw_connection_test',[]);
        echo '<section class="dw-sw-diagnostics"><h2>'.esc_html__('Connection and diagnostics','connect-for-shopware').'</h2>';
        if($test) echo '<p>'.esc_html(!empty($test['ok'])?__('Last connection test successful.','connect-for-shopware'):__('Last connection test failed.','connect-for-shopware')).' '.esc_html(wp_date('d.m.Y H:i:s',(int)$test['checkedAt'])).'</p>';
        echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="dw_sw_test">';wp_nonce_field('dw_sw_test');submit_button(__('Test connection now','connect-for-shopware'),'secondary');echo '</form>';

        echo '<details class="dw-sw-technical"><summary>'.esc_html__('Technical details','connect-for-shopware').'</summary>';
        global $wp_version;
        $environment=['WordPress '.(string)$wp_version,'PHP '.PHP_VERSION];if(defined('BRICKS_VERSION'))$environment[]='Bricks '.(string)BRICKS_VERSION;
        echo '<p><strong>'.esc_html__('Environment','connect-for-shopware').':</strong> '.esc_html(implode(' · ',$environment)).'</p>';
        if(!empty($test['ok'])&&!empty($test['context'])) {$c=$test['context'];echo '<p>'.esc_html__('Sales Channel context','connect-for-shopware').': '.esc_html(implode(' · ',[(string)$c['salesChannelId'],(string)$c['locale'],(string)$c['currency'],(string)$c['taxState']])).'</p>';}
        echo '<dl><dt>'.esc_html__('Last successful shop request','connect-for-shopware').'</dt><dd>'.esc_html(isset($state['lastSuccess'])?wp_date('d.m.Y H:i:s',(int)$state['lastSuccess']):__('Not recorded yet','connect-for-shopware')).'</dd>';
        echo '<dt>'.esc_html__('Last request duration','connect-for-shopware').'</dt><dd>'.esc_html(isset($state['durationMs'])?$state['durationMs'].' ms':'—').'</dd>';
        if(!empty($state['errorCode'])) {
            $labels=Diagnostics::labels();
            echo '<dt>'.esc_html__('Last connection error','connect-for-shopware').'</dt><dd>'.esc_html($labels[$state['errorCode']]??__('Check connector configuration.','connect-for-shopware')).' · '.esc_html(wp_date('d.m.Y H:i:s',(int)$state['lastError'])).'</dd>';
        }
        echo '</dl><p>'.esc_html__('Detected page caches','connect-for-shopware').': '.esc_html(implode(', ',Operations::detectedCaches())?:__('None detected. External/CDN caches must be managed separately.','connect-for-shopware')).'</p>';
        echo '<p>'.esc_html__('During a shop outage, retained product content can be shown without expired prices or availability. Retry pauses last 60 seconds.','connect-for-shopware').'</p></details></section>';
    }

    /**
     * Render settings, known connection status, optional Components and footer.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function page(): void {
        if(!current_user_can('manage_options')) return;
        $tab=self::tab();
        echo '<div class="wrap dw-sw-settings"><header class="dw-sw-header"><img src="'.esc_url(DW_SW_URL.'assets/brand/icon.svg').'" width="44" height="44" alt=""><div><h1>Connect for Shopware</h1><p>'.esc_html__('Your shop. Your content. Connected.','connect-for-shopware').'</p></div></header>';
        self::tabs($tab);
        echo '<div class="dw-sw-tab-content" id="dw-sw-section-'.esc_attr($tab).'">';
        if($tab==='usage')EditorialTools::page();
        else self::settings();
        echo '</div>';
        self::footer();
    }
    /** Render connection, cache, setup and optional Component controls only on settings.
     * @return void Does not scan editorial product usage.
     */
    private static function settings(): void {
        $configured=Plugin::accessKey()!==''&&(string)get_option('dw_sw_shop_url','')!=='';
        $shop=(string)get_option('dw_sw_shop_url','');
        $test=(array)get_option('dw_sw_connection_test',[]);$state=(array)get_option('dw_sw_diagnostics',[]);
        $lastSuccess=(int)($state['lastSuccess']??0);
        if(!empty($test['ok'])) $lastSuccess=max($lastSuccess,(int)($test['checkedAt']??0));
        $failed=$configured&&((!empty($test)&&empty($test['ok'])&&(int)($test['checkedAt']??0)>=$lastSuccess)||(!empty($state['lastError'])&&((int)$state['lastError']>$lastSuccess||(!empty($state['currentError'])&&(int)$state['lastError']===$lastSuccess))));
        $status=!$configured?__('Not configured','connect-for-shopware'):($failed?__('Connection needs attention','connect-for-shopware'):($lastSuccess?__('Last connection successful','connect-for-shopware'):__('Ready to test','connect-for-shopware')));
        $tone=!$configured?'neutral':($failed?'warning':($lastSuccess?'success':'neutral'));
        if(isset($_GET['dw_sw_imported']))echo '<div class="notice notice-success" role="status"><p>'.esc_html__('Settings imported. Test the connection with the server-side key on this site.','connect-for-shopware').'</p></div>';
        echo '<section class="dw-sw-status" aria-label="'.esc_attr__('Connection status','connect-for-shopware').'"><div><strong class="dw-sw-status-label dw-sw-status-'.esc_attr($tone).'">'.esc_html($status).'</strong>';
        if($shop!=='') echo '<p><a href="'.esc_url($shop).'">'.esc_html($shop).'</a></p>';
        else echo '<p>'.esc_html__('Enter your shop URL below to get started.','connect-for-shopware').'</p>';
        echo '</div><div><span>'.esc_html__('Last successful shop request','connect-for-shopware').'</span><p>'.esc_html($lastSuccess?wp_date('d.m.Y H:i',$lastSuccess):__('Not recorded yet','connect-for-shopware')).'</p></div></section>';
        if(isset($_GET['refreshed'])) echo '<div class="notice notice-success"><p>'.esc_html__('Connector cache cleared. Products will reload on the next request.','connect-for-shopware').'</p></div>';
        echo '<form action="options.php" method="post">';settings_fields('dw_sw');echo '<section class="dw-sw-panel">';Configuration::fields();echo '<p class="description">'.esc_html__('Provide a production Sales Channel key server-side via DW_SW_ACCESS_KEY in wp-config.php or the environment. The key is never displayed here.','connect-for-shopware').'</p></section><section class="dw-sw-panel"><h2>'.esc_html__('Cache and refresh','connect-for-shopware').'</h2>';
        $ttl=(int)get_option('dw_sw_cache_ttl',1800);$times=[900,1800,3600];if(!in_array($ttl,$times,true)) $times[]=$ttl;
        echo '<label for="dw-sw-ttl">'.esc_html__('Reuse Shopware data for','connect-for-shopware').'</label> <select id="dw-sw-ttl" name="dw_sw_cache_ttl">';
        /* translators: Placeholders identify the displayed product, image, duration or item counts in the order shown. */
        foreach($times as $seconds) echo '<option value="'.esc_attr((string)$seconds).'" '.selected($seconds,$ttl,false).'>'.esc_html(sprintf(__('%d minutes','connect-for-shopware'),(int)round($seconds/60))).'</option>';
        echo '</select><p>'.esc_html__('After expiry, the next request fetches fresh data. This is not a scheduled refresh.','connect-for-shopware').'</p>';
        echo '<input type="hidden" name="dw_sw_purge_pages" value="0"><label><input type="checkbox" name="dw_sw_purge_pages" value="1" '.checked((bool)get_option('dw_sw_purge_pages',true),true,false).'> '.esc_html__('Clear detected page caches on manual connector refresh (entire site).','connect-for-shopware').'</label>';
        echo '</section>';submit_button();echo '</form>';
        SetupTools::checklist();
        self::diagnostics();
        SetupTools::panel();
        if(Components::available()){echo '<section class="dw-sw-panel">';Components::panel();echo '</section>';}
        echo '<section class="dw-sw-panel dw-sw-refresh"><h2>'.esc_html__('Refresh cached products','connect-for-shopware').'</h2><form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="dw_sw_refresh">';wp_nonce_field('dw_sw_refresh');submit_button(__('Refresh product cache','connect-for-shopware'),'secondary');echo '</form>';
        echo '<p>'.esc_html__('Full-page or CDN caches can retain rendered prices longer. Configure their expiry and purge integration before customer use.','connect-for-shopware').'</p></section>';
    }
    /** Render the shared trademark notice and footer once after either section.
     * @return void Includes the local changelog dialog and closes the page wrapper.
     */
    private static function footer(): void {
        $lang=str_starts_with(determine_locale(),'de')?'de':'en';
        $content=require DW_SW_DIR.'includes/admin-content.php';
        $history=\Deckerweb_Changelog_Renderer_V1::render($content['history']);
        $repo='https://github.com/deckerweb/connect-for-shopware';
        $fallback=DW_SW_URL.'docs/changelog'.($lang==='de'?'-de':'').'.txt';
        echo '<section class="dw-sw-panel dw-sw-trademark" aria-labelledby="dw-sw-trademark-title"><h2 id="dw-sw-trademark-title">'.esc_html($content['notice']['title']).'</h2>';
        foreach($content['notice']['paragraphs'] as $paragraph) echo '<p>'.esc_html($paragraph).'</p>';
        echo '</section>';
        echo '<footer class="dw-sw-footer"><div><p><a href="'.esc_url($repo).'">Connect for Shopware</a> · <a href="'.esc_url($repo.'/releases').'">'.esc_html(DW_SW_VERSION).'</a> · <a href="'.esc_url($fallback).'" data-dw-sw-history>'.esc_html__('Changelog','connect-for-shopware').'</a> · <a href="'.esc_url($repo.'/wiki/'.($lang==='de'?'Deutsch':'English')).'">'.esc_html__('Documentation','connect-for-shopware').'</a></p><p>'.esc_html__('Your shop. Your content. Connected.','connect-for-shopware').'</p></div><div><p>© 2026 <a href="https://github.com/deckerweb">David Decker – DECKERWEB</a> · <a href="'.esc_url($repo).'">'.esc_html__('Plugin website','connect-for-shopware').'</a></p></div></footer>';
        echo '<dialog id="dw-sw-history" hidden aria-labelledby="dw-sw-history-title"><div class="dw-sw-modal"><h2 id="dw-sw-history-title">'.esc_html__('Changelog','connect-for-shopware').'</h2><form method="dialog"><button type="submit" class="button" data-dw-sw-close>'.esc_html__('Close','connect-for-shopware').'</button></form>'.wp_kses_post($history).'</div></dialog></div>';
    }
}
