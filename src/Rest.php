<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;
final class Rest {
    public static function permission(): bool {return current_user_can('edit_posts');}
    public static function register(): void {
        QuickView::register();
        $base='deckerweb-shopware/v1';
        register_rest_route($base,'/products',['methods'=>'GET','permission_callback'=>[self::class,'permission'],
            'callback'=>static fn($r)=>self::respond(static fn()=>self::search((string)$r->get_param('search'),max(1,(int)$r->get_param('page')))),
            'args'=>['search'=>['required'=>true,'type'=>'string','minLength'=>1,'maxLength'=>200],
                'page'=>['type'=>'integer','minimum'=>1,'maximum'=>1000,'default'=>1]]]);
        register_rest_route($base,'/products/(?P<id>[a-f0-9]{32})',['methods'=>'GET','permission_callback'=>[self::class,'permission'],
            'callback'=>static fn($r)=>self::respond(static fn()=>self::dto(Plugin::repository()->product($r['id'],$r->get_param('mode')?:'variant'))),
            'args'=>['mode'=>['type'=>'string','enum'=>['family','variant'],'default'=>'variant']]]);
        register_rest_route($base,'/products/(?P<id>[a-f0-9]{32})/variants',['methods'=>'GET','permission_callback'=>[self::class,'permission'],
            'callback'=>static fn($r)=>self::respond(static fn()=>array_map([self::class,'dto'],Plugin::repository()->variants($r['id'])))]);
        register_rest_route($base,'/categories',['methods'=>'GET','permission_callback'=>[self::class,'permission'],
            'callback'=>static fn()=>self::respond(static fn()=>array_map(static fn($c)=>['id'=>$c['id'],'name'=>sanitize_text_field($c['name'])],Plugin::repository()->categories()))]);
        register_rest_route($base,'/categories/(?P<id>[a-f0-9]{32})/listing',['methods'=>'GET','permission_callback'=>[self::class,'permission'],
            'args'=>['limit'=>['type'=>'integer','minimum'=>1,'maximum'=>48,'default'=>6],
                'order'=>['type'=>'string','maxLength'=>100,'default'=>'']],
            'callback'=>static fn($r)=>self::respond(static function() use($r): array {
                $listing=Plugin::repository()->listing($r['id'],(int)$r->get_param('limit'),1,(string)$r->get_param('order'));
                $listing['items']=array_map([self::class,'dto'],$listing['items']);
                foreach($listing['sortings'] as &$sorting) $sorting['label']=sanitize_text_field($sorting['label']);
                $listing['category']['name']=sanitize_text_field($listing['category']['name']);
                return $listing;
            })]);
        register_rest_route($base,'/related-preview',['methods'=>'POST',
            'permission_callback'=>static fn($r)=>current_user_can('edit_post',(int)$r->get_param('postId')),
            'args'=>['postId'=>['required'=>true,'type'=>'integer','minimum'=>1],
                'products'=>['required'=>true,'type'=>'array','maxItems'=>50,'items'=>['type'=>'object','required'=>['productId','selectionMode'],
                    'properties'=>['productId'=>['type'=>'string','pattern'=>'^[a-f0-9]{32}$'],'selectionMode'=>['type'=>'string','enum'=>['family','variant']]]]],
                'settings'=>['type'=>'object','default'=>new \stdClass()]],
            'callback'=>static fn($r)=>self::respond(static fn()=>['html'=>Plugin::related((int)$r->get_param('postId'),(array)$r->get_param('settings'),(array)$r->get_param('products'))])]);
        register_rest_route($base,'/cache/refresh',['methods'=>'POST','permission_callback'=>static fn()=>current_user_can('manage_options'),
            'callback'=>static function() {
                (new TransientCache())->refresh();do_action('dw_sw_cache_refreshed');
                return new \WP_REST_Response(['refreshed'=>true],200,['Cache-Control'=>'no-store']);
            }]);
    }
    private static function respond(callable $action): mixed {
        try {return new \WP_REST_Response($action(),200,['Cache-Control'=>'no-store']);}
        catch(\InvalidArgumentException $e) {return new \WP_Error('dw_sw_invalid',__('Invalid product selection.','connect-for-shopware'),['status'=>400]);}
        catch(\Throwable $e) {return new \WP_Error('dw_sw_unavailable',__('Shop data unavailable. Check the connector configuration.','connect-for-shopware'),['status'=>503]);}
    }
    public static function dto(array $p): array {
        $p['title']=sanitize_text_field($p['title']);$p['summary']=sanitize_text_field($p['summary']);
        $p['descriptionHtml']=wp_kses_post($p['descriptionHtml']);
        // Mapper already excludes custom fields, stock counts and context credentials.
        // Media projection avoids exposing raw translated/custom field metadata.
        $p['images']=array_map(static function(array $m): array {
            return ['id'=>$m['id'],'url'=>esc_url_raw($m['url']),'alt'=>sanitize_text_field($m['alt']),
                'thumbnails'=>array_map(static fn($t)=>['url'=>esc_url_raw($t['url']??''),'width'=>(int)($t['width']??0),'height'=>(int)($t['height']??0)],$m['thumbnails'])];
        },$p['images']);
        if(isset($p['manufacturer'])) {
            $p['manufacturer']['name']=sanitize_text_field($p['manufacturer']['name']);
            $p['manufacturer']['descriptionHtml']=wp_kses_post($p['manufacturer']['descriptionHtml']);
            foreach(['url','logo'] as $key) $p['manufacturer'][$key]=esc_url_raw($p['manufacturer'][$key],['http','https']);
        }
        $p['documents']=array_map(static fn($d)=>['id'=>$d['id'],'url'=>esc_url_raw($d['url'],['http','https']),'title'=>sanitize_text_field($d['title']),'fileSize'=>(int)$d['fileSize']],$p['documents']??[]);
        $p['gallery']=array_map(static function($item): array {
            foreach(['url','poster'] as $key) if(isset($item[$key])) $item[$key]=esc_url_raw($item[$key],['http','https']);
            $item['alt']=sanitize_text_field($item['alt']??'');
            if(isset($item['thumbnails'])) $item['thumbnails']=array_map(static fn($t)=>['url'=>esc_url_raw($t['url']??''),'width'=>(int)($t['width']??0),'height'=>(int)($t['height']??0)],$item['thumbnails']);
            return $item;
        },$p['gallery']??[]);
        return $p;
    }
    public static function search(string $term,int $page=1): array {
        $r=Plugin::repository()->search($term,$page);$r['items']=array_map([self::class,'dto'],$r['items']);return $r;
    }
    private static function bricksAuth(): void {
        if(!current_user_can('edit_posts')||!check_ajax_referer('bricks-nonce-builder','nonce',false)) {
            wp_send_json_error(['message'=>__('Access denied.','connect-for-shopware')],403);
        }
    }
    private static function label(array $p,string $mode): string {
        return sanitize_text_field(($mode==='family'?__('Product family','connect-for-shopware'):__('Product / variant','connect-for-shopware')).' · '.$p['title'].' · '.$p['productNumber'].($p['variantText']?' · '.$p['variantText']:''));
    }
    public static function bricksCategories(): void {
        self::bricksAuth();
        try {
            $term=sanitize_text_field(wp_unslash((string)($_GET['search']??'')));$options=[];
            foreach(Plugin::repository()->categories() as $category) {
                if($term!==''&&stripos($category['name'],$term)===false&&!in_array($category['id'],(array)($_GET['include']??[]),true)) continue;
                $options[$category['id']]=sanitize_text_field($category['name']);
            }
            wp_send_json_success($options);
        } catch(\Throwable $e) {wp_send_json_error(['message'=>__('Shop data unavailable.','connect-for-shopware')],503);}
    }
    public static function bricksSortings(): void {
        self::bricksAuth();
        try {
            $id=sanitize_text_field(wp_unslash((string)($_GET['categoryId']??'')));
            $options=[];
            if($id!=='') foreach(Plugin::repository()->listing($id)['sortings'] as $sort) $options[$sort['key']]=sanitize_text_field($sort['label']);
            wp_send_json_success($options);
        } catch(\Throwable $e) {wp_send_json_error(['message'=>__('Shop data unavailable.','connect-for-shopware')],503);}
    }
    public static function bricksProducts(): void {
        self::bricksAuth();$options=[];
        try {
            $term=sanitize_text_field(wp_unslash((string)($_GET['search']??'')));
            if($term!=='') {
                foreach(self::search($term)['items'] as $p) {
                    $options[($p['isFamily']?'family':'variant').':'.$p['id']]=self::label($p,$p['isFamily']?'family':'variant');
                    if($p['parentId']) {
                        try {
                            $parent=Plugin::repository()->product($p['parentId'],'family');
                            $options['family:'.$parent['id']]=self::label($parent,'family');
                        } catch(\Throwable $ignored) { /* The visible variant remains selectable. */ }
                    }
                }
            }
            foreach(array_slice((array)($_GET['include']??[]),0,10) as $ref) {
                if(!is_string($ref)) continue;
                $r=Presentation::reference($ref);$p=Plugin::repository()->product($r['productId'],$r['selectionMode']);
                $options[$ref]=self::label($p,$r['selectionMode']);
            }
            wp_send_json_success($options);
        } catch(\Throwable $e) {wp_send_json_error(['message'=>__('Shop data unavailable.','connect-for-shopware')],503);}
    }
    public static function bricksVariants(): void {
        self::bricksAuth();
        try {
            $ref=Presentation::reference(sanitize_text_field(wp_unslash((string)($_GET['productRef']??''))));
            $p=Plugin::repository()->product($ref['productId'],$ref['selectionMode']);$parent=$p['parentId']?:$p['id'];
            $term=sanitize_text_field(wp_unslash((string)($_GET['search']??'')));$options=[];
            foreach(Plugin::repository()->variants($parent) as $v) {
                $label=self::label($v,'variant');
                if($term===''||stripos($label,$term)!==false) $options['variant:'.$v['id']]=$label;
            }
            wp_send_json_success($options);
        } catch(\Throwable $e) {wp_send_json_error(['message'=>__('Shop data unavailable.','connect-for-shopware')],503);}
    }
}
