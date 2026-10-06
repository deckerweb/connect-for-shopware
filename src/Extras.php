<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;
/**
 * Build independent, bounded pagination links for product grids.
 */
final class Pagination {
    /**
     * Derive a stable grid identifier used to isolate pagination parameters.
     *
     * @since 1.0.0
     * @param array $settings Shared presentation and catalog selection controls.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function identity(array $settings): string {
        $id=(string)($settings['gridId']??'');
        return substr(hash('sha256',$id!==''?$id:wp_json_encode([$settings['categoryId']??'',$settings['limit']??6,$settings['order']??''])),0,12);
    }
    /**
     * Render bounded links while preserving only other Connector grid parameters.
     *
     * @since 1.0.0
     * @param int $pages Total available page count.
     * @param int $current Current one-based result page.
     * @param string $query Scoped query-string key for this grid.
     * @param string $root Stable rendered grid identifier.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function render(int $pages,int $current,string $query,string $root): string {
        $url=get_permalink(Plugin::contextPostId());
        if(!$url) $url=home_url('/');
        // Preserve other connector grids only; never reflect arbitrary query parameters.
        $other=[];
        foreach($_GET as $key=>$value) if(is_string($key)&&preg_match('/^dw_sw_page_[a-f0-9]{12}$/D',$key)&&is_scalar($value)) $other[$key]=max(1,min(1000,(int)$value));
        $url=add_query_arg($other,$url);
        $link=static function(int $page,string $label) use($url,$query,$root): string {
            return '<a href="'.esc_url(add_query_arg($query,$page,$url).'#dw-sw-grid-'.$root).'">'.esc_html($label).'</a>';
        };
        $html='<nav class="dw-sw-pagination" aria-label="'.esc_attr__('Product pages','connect-for-shopware').'">';
        if($current>1) $html.=$link($current-1,__('Previous page','connect-for-shopware'));
        $numbers=array_unique(array_merge([1,$pages],range(max(1,$current-2),min($pages,$current+2))));sort($numbers);$previous=0;
        foreach($numbers as $n) {
            if($previous&&$n>$previous+1) $html.='<span aria-hidden="true">…</span>';
            $html.=$n===$current?'<span aria-current="page">'.esc_html((string)$n).'</span>':$link($n,(string)$n);$previous=$n;
        }
        if($current<$pages) $html.=$link($current+1,__('Next page','connect-for-shopware'));
        return $html.'</nav>';
    }
}

/**
 * Serve on-demand product details using signed tickets and rate limits.
 */
final class QuickView {

    /**
     * Sign a product selection, shop fingerprint and bounded expiry timestamp.
     *
     * @since 1.0.0
     * @param string $id Shopware product or category UUID.
     * @param string $mode Selection mode: family or variant.
     * @param int|null $expires Absolute ticket expiry timestamp, or null for the bounded default.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function ticket(string $id,string $mode,?int $expires=null): string {
        // Daily bucket keeps cached markup reusable; tickets expire after at most seven days.
        $expires??=(int)(floor(time()/DAY_IN_SECONDS)*DAY_IN_SECONDS+7*DAY_IN_SECONDS);
        return $expires.'.'.hash_hmac('sha256',Configuration::identity().'|'.$id.'|'.$mode.'|'.$expires,wp_salt('nonce'));
    }

    /**
     * Validate the requested UUID, mode, expiry and constant-time ticket signature.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Incoming WordPress REST request.
     * @return bool Whether the operation is allowed or successfully completed.
     */
    public static function allowed($request): bool {
        $id=$request->get_param('id');$mode=$request->get_param('mode');$ticket=$request->get_param('ticket');
        if(!is_string($id)||!is_string($mode)||!is_string($ticket)) return false;
        if(!preg_match('/^[a-f0-9]{32}$/D',$id)||!in_array($mode,['family','variant'],true)||!preg_match('/^([0-9]{10})\.[a-f0-9]{64}$/D',$ticket,$m)) return false;
        $expires=(int)$m[1];
        return $expires>=time()&&$expires<=time()+7*DAY_IN_SECONDS&&hash_equals(self::ticket($id,$mode,$expires),$ticket);
    }

    /**
     * Render the progressively enhanced Quick View trigger with a signed read ticket.
     *
     * @since 1.0.0
     * @param array $p Mapped product used to build a signed on-demand trigger.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function trigger(array $p): string {
        wp_enqueue_style('wp-block-buttons');wp_enqueue_style('wp-block-button');
        $mode=$p['isFamily']?'family':'variant';
        return '<button type="button" hidden class="dw-sw-quick-view" data-dw-sw-quick data-product="'.esc_attr($p['id']).'" data-mode="'.$mode.'" data-ticket="'.esc_attr(self::ticket($p['id'],$mode)).'" data-shop-url="'.esc_url($p['url']).'">'.esc_html__('Quick view','connect-for-shopware').'</button>';
    }

    /**
     * Atomically enforce a per-site, hashed-client request allowance.
     *
     * @since 1.0.0
     * @return bool Whether the operation is allowed or successfully completed.
     */
    private static function rateAllowed(): bool {
        // IP is hashed with a site secret; neither raw IP nor credentials are stored.
        $key='dw_sw_quick_rate_'.substr(hash_hmac('sha256',(string)($_SERVER['REMOTE_ADDR']??'local'),wp_salt('auth')),0,32);
        $cache=new TransientCache();
        if(!$cache->acquire($key)) return false;
        try {
            $state=(array)get_transient($key);$now=time();
            if(($state['until']??0)<=$now) $state=['until'=>$now+60,'count'=>0];
            if(($state['count']??0)>=60) return false;
            $state['count']++;RuntimeCache::set($key,$state,max(1,$state['until']-$now));return true;
        } finally {$cache->release($key);}
    }

    /**
     * Register the scoped routes or WordPress hooks for this component.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function register(): void {
        register_rest_route('deckerweb-shopware/v1','/quick-view',['methods'=>'POST','permission_callback'=>[self::class,'allowed'],
            'args'=>['id'=>['required'=>true,'type'=>'string','pattern'=>'^[a-f0-9]{32}$'],
                'mode'=>['required'=>true,'type'=>'string','enum'=>['family','variant']],
                'ticket'=>['required'=>true,'type'=>'string','maxLength'=>75]],
            'callback'=>static function($r) {
                if(!self::rateAllowed()) return new \WP_Error('dw_sw_rate',__('Please try again shortly.','connect-for-shopware'),['status'=>429]);
                try {
                    $p=Plugin::repository()->product($r['id'],$r['mode']);
                    $html=(new ProductRenderer())->render($p,['showTabs'=>true,'showText'=>false,'imageMode'=>'gallery','linkStyle'=>'button','headingTag'=>'h2']);
                    return new \WP_REST_Response(['html'=>$html],200,['Cache-Control'=>'no-store']);
                } catch(\Throwable $e) {return new \WP_Error('dw_sw_unavailable',__('Quick view unavailable. Please open the product in the shop.','connect-for-shopware'),['status'=>503]);}
            }]);
    }
}
