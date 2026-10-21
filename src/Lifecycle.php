<?php
/** Connector-owned temporary storage and uninstall lifecycle. */
declare(strict_types=1);
namespace Deckerweb\Shopware;
defined('ABSPATH') || exit;

/**
 * Record site-scoped transient names for cleanup without retaining product payloads.
 */
final class RuntimeCache {
    /** @var array<int,array<string,int>> Site-scoped expiry timestamps awaiting the shutdown write. */
    private static array $pending=[];
    /** @var bool Whether the request has registered its shutdown flush. */
    private static bool $registered=false;

    /**
     * Store a site-scoped transient and record its name for object-cache cleanup.
     *
     * @since 1.0.1
     * @param string $key Connector-owned cache or operation identifier.
     * @param mixed $value Opaque runtime value passed to WordPress.
     * @param int $expiration Transient lifetime in seconds; must be positive.
     * @return bool Whether the operation is allowed or successfully completed.
     * @throws \InvalidArgumentException When the endpoint, identifier, route or lifetime is unsupported.
     */
    public static function set(string $key,mixed $value,int $expiration): bool {
        if(!str_starts_with($key,'dw_sw_')||$expiration<1) throw new \InvalidArgumentException('Invalid Connector transient.');
        self::$pending[get_current_blog_id()][$key]=time()+$expiration;
        if(!self::$registered){add_action('shutdown',[self::class,'flush']);self::$registered=true;}
        return set_transient($key,$value,$expiration);
    }

    /**
     * Batch-write pending key/expiry names to their sites without storing product payloads.
     *
     * @since 1.0.1
     * @return void No return value; effects are described above.
     */
    public static function flush(): void {
        if(!self::$pending)return;
        $pending=self::$pending;self::$pending=[];$original=get_current_blog_id();
        foreach($pending as $blogId=>$entries){
            if((int)$blogId!==$original)switch_to_blog((int)$blogId);
            try{
                // One registry write per cache-generating site/request, never per read.
                self::persist($entries);
            }finally{if((int)$blogId!==$original)restore_current_blog();}
        }
    }
    /**
     * Merge one site's pending registry keys using bounded compare-and-swap.
     *
     * @since 1.0.1
     * @param array<string,int> $entries Cache names and absolute expiry timestamps.
     * @return void Invalidates only this non-autoloaded option's cache.
     */
    private static function persist(array $entries): void {
        global $wpdb;
        for($attempt=0;$attempt<5;$attempt++){
            $old=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",'dw_sw_runtime_keys'));
            $keys=is_string($old)?maybe_unserialize($old):[];$keys=is_array($keys)?$keys:[];$now=time();
            foreach($keys as $key=>$expires)if(!is_string($key)||!str_starts_with($key,'dw_sw_')||(int)$expires<=$now)unset($keys[$key]);
            $value=maybe_serialize(array_replace($keys,$entries));
            if($old===null){$changed=$wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->options} (option_name,option_value,autoload) VALUES (%s,%s,'off')",'dw_sw_runtime_keys',$value));}
            else{$changed=$wpdb->query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value=%s WHERE option_name=%s AND option_value=%s",$value,'dw_sw_runtime_keys',$old));}
            if($changed===1||$old===$value){wp_cache_delete('dw_sw_runtime_keys','options');wp_cache_delete('notoptions','options');return;}
        }
    }

}

/**
 * Clean Connector temporary state while preserving configuration and article content.
 */
final class Lifecycle {
    /**
     * Clean each site and this repository update cache during a valid uninstall.
     *
     * @since 1.0.1
     * @return void No return value; effects are described above.
     */
    public static function uninstall(): void {
        if(!defined('WP_UNINSTALL_PLUGIN')||WP_UNINSTALL_PLUGIN!=='connect-for-shopware/connect-for-shopware.php')return;
        $updater_class = require DW_SW_DIR . 'includes/deckerweb-github-release-updater-v2.php';
        RuntimeCache::flush();
        if(is_multisite()){
            $offset=0;
            do{
                $ids=get_sites(['fields'=>'ids','number'=>100,'offset'=>$offset]);
                foreach($ids as $id){switch_to_blog((int)$id);try{self::site();}finally{restore_current_blog();}}
                $offset+=count($ids);
            }while(count($ids)===100);
            foreach(get_networks(['fields'=>'ids','number'=>0]) as $networkId){
                // WordPress switches the blog, but not the network used by site transients.
                $original_network=$GLOBALS['current_site'];
                switch_to_blog((int)get_main_site_id((int)$networkId));
                try {
                    $GLOBALS['current_site']=get_network((int)$networkId);
                    $updater_class::clear_repository_cache('https://github.com/deckerweb/connect-for-shopware');
                } finally {
                    $GLOBALS['current_site']=$original_network;
                    restore_current_blog();
                }
            }
        }else{
            self::site();$updater_class::clear_repository_cache('https://github.com/deckerweb/connect-for-shopware');
        }
    }

    /**
     * Remove only Connector temporary names in the current site database and object cache.
     *
     * @since 1.0.1
     * @return void No return value; effects are described above.
     */
    private static function site(): void {
        global $wpdb;
        $registered=(array)get_option('dw_sw_runtime_keys',[]);
        foreach($registered as $key=>$expires)if(is_string($key)&&str_starts_with($key,'dw_sw_'))delete_transient($key);
        // Also detect database-backed caches created before the key registry existed.
        $prefix=$wpdb->esc_like('_transient_dw_sw_').'%';
        $names=$wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$prefix));
        foreach($names as $name){$key=substr($name,strlen('_transient_'));if(str_starts_with($key,'dw_sw_'))delete_transient($key);}
        $timeouts=$wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('_transient_timeout_dw_sw_').'%'));
        foreach($timeouts as $name)delete_option($name);
        $locks=$wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('dw_sw_lock_').'%'));
        foreach($locks as $name)if(preg_match('/^dw_sw_lock_[a-f0-9]{40}$/D',$name))delete_option($name);
        foreach(['dw_sw_runtime_keys','dw_sw_diagnostics','dw_sw_connection_test','dw_sw_page_purge','dw_sw_cache_generation'] as $name)delete_option($name);
    }
}
