<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;
final class Admin {
    public static function boot(): void {
        add_action('admin_menu',static function() {
            add_options_page('Connect for Shopware','Connect for Shopware','manage_options','dw-sw',[self::class,'page']);
        });
        add_action('admin_init',static function() {
            register_setting('dw_sw','dw_sw_purge_pages',['type'=>'boolean','default'=>true,'sanitize_callback'=>static fn($v)=>(bool)$v]);
            register_setting('dw_sw','dw_sw_cache_ttl',['type'=>'integer','default'=>1800,
                'sanitize_callback'=>static fn($v)=>max(900,min(3600,(int)$v))]);
        });
        add_action('admin_post_dw_sw_refresh',static function() {
            if(!current_user_can('manage_options')) wp_die(esc_html__('Access denied.','connect-for-shopware'));
            check_admin_referer('dw_sw_refresh');(new TransientCache())->refresh();do_action('dw_sw_cache_refreshed');
            wp_safe_redirect(admin_url('options-general.php?page=dw-sw&refreshed=1'));exit;
        });
        add_action('add_meta_boxes',static function() {
            foreach(Plugin::postTypes() as $type) add_meta_box('dw-sw-products',__('Shopware products','connect-for-shopware'),[self::class,'metaBox'],$type,'side','default',['__back_compat_meta_box'=>true]);
        });
        add_action('save_post',[self::class,'save']);
        add_action('admin_enqueue_scripts',[self::class,'assets']);
    }
    public static function assets(string $hook): void {
        if(in_array($hook,['post.php','post-new.php','settings_page_dw-sw'],true)) {
            wp_enqueue_style('dw-sw-admin',DW_SW_URL.'assets/admin.css',[],DW_SW_VERSION);
            wp_enqueue_script('dw-sw-admin',DW_SW_URL.'assets/admin.js',[],DW_SW_VERSION,true);
            wp_localize_script('dw-sw-admin','dwSwAdmin',['root'=>rest_url('deckerweb-shopware/v1/'),'nonce'=>wp_create_nonce('wp_rest'),
                'labels'=>['search'=>__('Search products','connect-for-shopware'),'add'=>__('Add','connect-for-shopware'),
                    'remove'=>__('Remove','connect-for-shopware'),'family'=>__('Product family','connect-for-shopware'),
                    'variant'=>__('Product / variant','connect-for-shopware'),'variants'=>__('Choose variant','connect-for-shopware'),
                    'up'=>__('Move up','connect-for-shopware'),'down'=>__('Move down','connect-for-shopware'),
                    'next'=>__('Next results','connect-for-shopware'),'previous'=>__('Previous results','connect-for-shopware'),
                    'error'=>__('Shop data unavailable.','connect-for-shopware'),'empty'=>__('No products found.','connect-for-shopware')]]);
        }
    }
    public static function metaBox($post): void {
        wp_nonce_field('dw_sw_products','dw_sw_products_nonce');
        $items=Presentation::associations(get_post_meta($post->ID,'_dw_sw_products',true));
        echo '<div class="dw-sw-association-editor"><input type="hidden" name="dw_sw_products" value="'.esc_attr(wp_json_encode($items)).'"><div class="dw-sw-association-mount"></div></div>';
        echo '<noscript>'.esc_html__('JavaScript is required to select products. Existing assignments are preserved.','connect-for-shopware').'</noscript>';
    }
    public static function save(int $postId): void {
        if((defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)||wp_is_post_revision($postId)||!current_user_can('edit_post',$postId)) return;
        if(!in_array(get_post_type($postId),Plugin::postTypes(),true)) return;
        if(!isset($_POST['dw_sw_products_nonce'],$_POST['dw_sw_products'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dw_sw_products_nonce'])),'dw_sw_products')) return;
        $raw=wp_unslash($_POST['dw_sw_products']);if(!is_string($raw)||strlen($raw)>16384) return;
        $items=json_decode($raw,true);if(!is_array($items)) return;
        update_post_meta($postId,'_dw_sw_products',Presentation::associations($items));
    }
    private static function diagnostics(): void {
        $domain='connect-for-shopware';$state=(array)get_option('dw_sw_diagnostics',[]);$test=(array)get_option('dw_sw_connection_test',[]);
        echo '<section class="dw-sw-diagnostics"><h2>'.esc_html__('Connection and diagnostics',$domain).'</h2>';
        if($test) echo '<p>'.esc_html(!empty($test['ok'])?__('Last connection test successful.',$domain):__('Last connection test failed.',$domain)).' '.esc_html(wp_date('d.m.Y H:i:s',(int)$test['checkedAt'])).'</p>';
        echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="dw_sw_test">';wp_nonce_field('dw_sw_test');submit_button(__('Test connection now',$domain),'secondary');echo '</form>';

        echo '<details class="dw-sw-technical"><summary>'.esc_html__('Technical details',$domain).'</summary>';
        if(!empty($test['ok'])&&!empty($test['context'])) {$c=$test['context'];echo '<p>'.esc_html__('Sales Channel context','connect-for-shopware').': '.esc_html(implode(' · ',[(string)$c['salesChannelId'],(string)$c['locale'],(string)$c['currency'],(string)$c['taxState']])).'</p>';}
        echo '<dl><dt>'.esc_html__('Last successful shop request',$domain).'</dt><dd>'.esc_html(isset($state['lastSuccess'])?wp_date('d.m.Y H:i:s',(int)$state['lastSuccess']):__('Not recorded yet',$domain)).'</dd>';
        echo '<dt>'.esc_html__('Last request duration',$domain).'</dt><dd>'.esc_html(isset($state['durationMs'])?$state['durationMs'].' ms':'—').'</dd>';
        if(!empty($state['errorCode'])) {
            $labels=Diagnostics::labels();
            echo '<dt>'.esc_html__('Last connection error',$domain).'</dt><dd>'.esc_html($labels[$state['errorCode']]??__('Check connector configuration.',$domain)).' · '.esc_html(wp_date('d.m.Y H:i:s',(int)$state['lastError'])).'</dd>';
        }
        echo '</dl><p>'.esc_html__('Detected page caches',$domain).': '.esc_html(implode(', ',Operations::detectedCaches())?:__('None detected. External/CDN caches must be managed separately.',$domain)).'</p>';
        echo '<p>'.esc_html__('During a shop outage, retained product content can be shown without expired prices or availability. Retry pauses last 60 seconds.',$domain).'</p></details></section>';
    }
    public static function page(): void {
        if(!current_user_can('manage_options')) return;
        $configured=Plugin::accessKey()!==''&&(string)get_option('dw_sw_shop_url','')!=='';
        $domain='connect-for-shopware';$shop=(string)get_option('dw_sw_shop_url','');
        $test=(array)get_option('dw_sw_connection_test',[]);$state=(array)get_option('dw_sw_diagnostics',[]);
        $lastSuccess=(int)($state['lastSuccess']??0);
        if(!empty($test['ok'])) $lastSuccess=max($lastSuccess,(int)($test['checkedAt']??0));
        $failed=$configured&&((!empty($test)&&empty($test['ok'])&&(int)($test['checkedAt']??0)>=$lastSuccess)||(!empty($state['lastError'])&&((int)$state['lastError']>$lastSuccess||(!empty($state['currentError'])&&(int)$state['lastError']===$lastSuccess))));
        $status=!$configured?__('Not configured',$domain):($failed?__('Connection needs attention',$domain):($lastSuccess?__('Last connection successful',$domain):__('Ready to test',$domain)));
        $tone=!$configured?'neutral':($failed?'warning':($lastSuccess?'success':'neutral'));
        echo '<div class="wrap dw-sw-settings"><header class="dw-sw-header"><img src="'.esc_url(DW_SW_URL.'assets/brand/icon.svg').'" width="44" height="44" alt=""><div><h1>Connect for Shopware</h1><p>'.esc_html__('Your shop. Your content. Connected.',$domain).'</p></div></header>';
        echo '<section class="dw-sw-status" aria-label="'.esc_attr__('Connection status',$domain).'"><div><strong class="dw-sw-status-label dw-sw-status-'.$tone.'">'.esc_html($status).'</strong>';
        if($shop!=='') echo '<p><a href="'.esc_url($shop).'">'.esc_html($shop).'</a></p>';
        else echo '<p>'.esc_html__('Enter your shop URL below to get started.',$domain).'</p>';
        echo '</div><div><span>'.esc_html__('Last successful shop request',$domain).'</span><p>'.esc_html($lastSuccess?wp_date('d.m.Y H:i',$lastSuccess):__('Not recorded yet',$domain)).'</p></div></section>';
        if(isset($_GET['refreshed'])) echo '<div class="notice notice-success"><p>'.esc_html__('Connector cache cleared. Products will reload on the next request.','connect-for-shopware').'</p></div>';
        echo '<form action="options.php" method="post">';settings_fields('dw_sw');settings_errors('dw_sw');echo '<section class="dw-sw-panel">';Configuration::fields();echo '<p class="description">'.esc_html__('Provide a production Sales Channel key server-side via DW_SW_ACCESS_KEY in wp-config.php or the environment. The key is never displayed here.',$domain).'</p></section><section class="dw-sw-panel"><h2>'.esc_html__('Cache and refresh',$domain).'</h2>';
        $ttl=(int)get_option('dw_sw_cache_ttl',1800);$times=[900,1800,3600];if(!in_array($ttl,$times,true)) $times[]=$ttl;
        echo '<label for="dw-sw-ttl">'.esc_html__('Reuse Shopware data for','connect-for-shopware').'</label> <select id="dw-sw-ttl" name="dw_sw_cache_ttl">';
        foreach($times as $seconds) echo '<option value="'.esc_attr((string)$seconds).'" '.selected($seconds,$ttl,false).'>'.esc_html(sprintf(__('%d minutes','connect-for-shopware'),(int)round($seconds/60))).'</option>';
        echo '</select><p>'.esc_html__('After expiry, the next request fetches fresh data. This is not a scheduled refresh.','connect-for-shopware').'</p>';
        echo '<input type="hidden" name="dw_sw_purge_pages" value="0"><label><input type="checkbox" name="dw_sw_purge_pages" value="1" '.checked((bool)get_option('dw_sw_purge_pages',true),true,false).'> '.esc_html__('Clear detected page caches on manual connector refresh (entire site).','connect-for-shopware').'</label>';
        echo '</section>';submit_button();echo '</form>';
        self::diagnostics();
        echo '<section class="dw-sw-panel">';Components::panel();echo '</section>';
        echo '<section class="dw-sw-panel dw-sw-refresh"><h2>'.esc_html__('Refresh cached products',$domain).'</h2><form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="dw_sw_refresh">';wp_nonce_field('dw_sw_refresh');submit_button(__('Refresh product cache','connect-for-shopware'),'secondary');echo '</form>';
        echo '<p>'.esc_html__('Full-page or CDN caches can retain rendered prices longer. Configure their expiry and purge integration before customer use.','connect-for-shopware').'</p></section>';
        $lang=str_starts_with(determine_locale(),'de')?'de':'en';
        $history=\Deckerweb_Changelog_Renderer_V1::render((string)file_get_contents(DW_SW_DIR.'docs/changelog'.($lang==='de'?'-de':'').'.txt'));
        $repo='https://github.com/deckerweb/connect-for-shopware';
        $fallback=DW_SW_URL.'docs/changelog'.($lang==='de'?'-de':'').'.txt';
        echo '<footer class="dw-sw-footer"><div><p><a href="'.esc_url($repo).'">Connect for Shopware</a> · <a href="'.esc_url($repo.'/releases').'">'.esc_html(DW_SW_VERSION).'</a> · <a href="'.esc_url($fallback).'" data-dw-sw-history>'.esc_html__('Changelog','connect-for-shopware').'</a> · <a href="'.esc_url($repo.'/wiki/'.($lang==='de'?'Deutsch':'English')).'">'.esc_html__('Documentation','connect-for-shopware').'</a></p><p>'.esc_html__('Your shop. Your content. Connected.','connect-for-shopware').'</p></div><div><p>© 2026 <a href="https://github.com/deckerweb">David Decker – DECKERWEB</a> · <a href="'.esc_url($repo).'">'.esc_html__('Plugin website','connect-for-shopware').'</a></p></div></footer>';
        echo '<dialog id="dw-sw-history" aria-labelledby="dw-sw-history-title"><div class="dw-sw-modal"><h2 id="dw-sw-history-title">'.esc_html__('Changelog','connect-for-shopware').'</h2><button type="button" class="button" data-dw-sw-close>'.esc_html__('Close','connect-for-shopware').'</button>'.$history.'</div></dialog></div>';
    }
}
