<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;

/**
 * Persist safe connection status without credentials or raw transport errors.
 */
final class Diagnostics {
    /**
     * Store request status and duration, never credentials or raw exceptions.
     *
     * @since 1.0.0
     * @param bool $ok Whether the transport operation succeeded.
     * @param string $code Recognized safe status code; not raw server error content.
     * @param int $duration Request duration in milliseconds.
     * @return void No return value; effects are described above.
     */
    public static function record(bool $ok,string $code,int $duration): void {
        $state=(array)get_option('dw_sw_diagnostics',[]);
        $state['durationMs']=$duration;
        if($ok) {$state['lastSuccess']=time();$state['currentError']='';}
        else {$state['lastError']=time();$state['errorCode']=$code;$state['currentError']=$code;}
        update_option('dw_sw_diagnostics',$state,false);
    }
    /**
     * Return translated safe explanations for recognized transport status codes.
     *
     * @since 1.0.0
     * @return array Normalized result data for the documented operation.
     */
    public static function labels(): array {
        $d='connect-for-shopware';
        return ['network'=>__('Connection interrupted.',$d),'response'=>__('Invalid shop response.',$d),
            'http_401'=>__('Sales Channel key rejected.',$d),'http_403'=>__('Shop access denied.',$d),
            'http_404'=>__('Shop resource unavailable.',$d),'upstream'=>__('Shop temporarily unavailable.',$d),
            'budget'=>__('Request time budget reached.',$d),'busy'=>__('Product data is being refreshed.',$d)];
    }
}
/**
 * Bound safe HTTP requests and pause repeated failures per shop identity.
 */
final class WordPressTransport {
    private static float $spent=0;
    /**
     * Initialize the validated dependencies used by this component.
     *
     * @since 1.0.0
     * @param bool $probe Whether to bypass the current outage pause for an explicit test.
     * @return void No return value; effects are described above.
     */
    public function __construct(private bool $probe=false) {}
    /**
     * Send a bounded safe HTTP read, update diagnostics and enforce outage backoff.
     *
     * @since 1.0.0
     * @param string $method Allowlisted HTTP method for a Store API read.
     * @param string $url Public HTTPS endpoint or repository metadata URL.
     * @param array $headers Transport headers including the server-side Sales Channel key.
     * @param string|null $body Serialized read criteria, or null for bodyless reads.
     * @return array Normalized result data for the documented operation.
     */
    public function __invoke(string $method,string $url,array $headers,?string $body): array {
        $breaker='dw_sw_pause_'.substr(hash('sha256',preg_replace('~/store-api/.*$~','/store-api',$url).'|'.(string)($headers['sw-access-key']??'')),0,24);
        if(!$this->probe&&get_transient($breaker)) throw new ShopUnavailable('pause');
        $remaining=8-self::$spent;
        if($remaining<.3) throw new ShopUnavailable('budget');
        $args=['method'=>$method,'headers'=>$headers,'timeout'=>min(4,$remaining),
            'redirection'=>0,'sslverify'=>true,'limit_response_size'=>4*1024*1024,
            'user-agent'=>'Connect-for-Shopware/'.DW_SW_VERSION];
        if($body!==null) $args['body']=$body;
        $start=microtime(true);
        $response=wp_safe_remote_request($url,$args);
        $duration=(int)round((microtime(true)-$start)*1000);self::$spent+=$duration/1000;
        $status=is_wp_error($response)?0:wp_remote_retrieve_response_code($response);
        if($status===404) {Diagnostics::record(false,'http_404',$duration);throw new ResourceMissing('missing');}
        if(is_wp_error($response)||$status!==200) {
            $code=$status===0?'network':(in_array($status,[401,403],true)?'http_'.$status:'upstream');
            Diagnostics::record(false,$code,$duration);RuntimeCache::set($breaker,true,60);
            throw new ShopUnavailable($code);
        }
        try {$data=json_decode(wp_remote_retrieve_body($response),true,512,JSON_THROW_ON_ERROR);}
        catch(\JsonException $e) {$data=null;}
        if(!is_array($data)) {
            Diagnostics::record(false,'response',$duration);RuntimeCache::set($breaker,true,60);throw new ShopUnavailable('response');
        }
        delete_transient($breaker);Diagnostics::record(true,'', $duration);
        return $data;
    }
}
/**
 * Store scoped product data, outage backups and atomic regeneration locks.
 */
final class TransientCache implements RecoverableCache {
    private array $owners=[];
    /**
     * Prefix a cache operation with the current site cache generation.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    private function key(string $key): string {return 'dw_sw_'.get_option('dw_sw_cache_generation','1').'_'.$key;}
    /**
     * Read reusable product data, returning null on a cache miss.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return ?array Result data, or null when no cached value exists.
     */
    public function get(string $key): ?array {$value=get_transient($this->key($key));return is_array($value)?$value:null;}
    /**
     * Persist product data for the requested lifetime and retain an outage backup.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @param array $value Mapped product/cache response to store.
     * @param int $ttl Reusable data lifetime in seconds.
     * @return void No return value; effects are described above.
     */
    public function set(string $key,array $value,int $ttl): void {
        RuntimeCache::set($this->key($key),$value,$ttl);
        RuntimeCache::set('dw_sw_backup_'.$key,$value,DAY_IN_SECONDS);
    }
    /**
     * Read retained content intended for a temporary shop outage.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return ?array Result data, or null when no cached value exists.
     */
    public function stale(string $key): ?array {$v=get_transient('dw_sw_backup_'.$key);return is_array($v)?$v:null;}
    /**
     * Remove a backup when Shopware explicitly reports the resource missing.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return void No return value; effects are described above.
     */
    public function forgetStale(string $key): void {delete_transient('dw_sw_backup_'.$key);}
    /**
     * Atomically claim regeneration ownership for one cache key.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return bool Whether the operation is allowed or successfully completed.
     */
    public function acquire(string $key): bool {
        global $wpdb;
        $name='dw_sw_lock_'.substr(hash('sha256',$this->key($key)),0,40);
        $owner=wp_generate_uuid4();$value=(time()+30).'|'.$owner;
        // Atomic DB insert works with or without an external object cache.
        $existing=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name=%s",$name));
        if(is_string($existing)&&(int)$existing<=time()) $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",$name,$existing));
        $insert=$wpdb->query($wpdb->prepare("INSERT IGNORE INTO $wpdb->options (option_name,option_value,autoload) VALUES (%s,%s,'off')",$name,$value));
        if($insert!==1) return false;
        $this->owners[$key]=[$name,$value];return true;
    }
    /**
     * Release only the regeneration lock owned by this instance.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return void No return value; effects are described above.
     */
    public function release(string $key): void {
        global $wpdb;
        if(!isset($this->owners[$key])) return;
        [$name,$value]=$this->owners[$key];unset($this->owners[$key]);
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",$name,$value));
    }
    /**
     * Invalidate current cache generation without altering catalog assignments.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public function refresh(): void {update_option('dw_sw_cache_generation',wp_generate_uuid4(),false);}
}
