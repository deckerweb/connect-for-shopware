<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;
final class Operations {
    public static function boot(): void {
        add_action('dw_sw_cache_refreshed',[self::class,'purgePages']);
        add_action('admin_post_dw_sw_test',static function(): void {
            if(!current_user_can('manage_options')) wp_die(esc_html__('Access denied.','connect-for-shopware'));
            check_admin_referer('dw_sw_test');self::testConnection();
            wp_safe_redirect(admin_url('options-general.php?page=dw-sw&tested=1'));exit;
        });
        add_action('init',static function(): void {
            foreach(Plugin::postTypes() as $type) {
                add_filter('manage_'.$type.'_posts_columns',static function($columns): array {
                    $columns['dw_sw_products']=__('Shopware products','connect-for-shopware');return $columns;
                });
                add_action('manage_'.$type.'_posts_custom_column',[self::class,'column'],10,2);
            }
        },30);
    }
    public static function testConnection(): array {
        $start=microtime(true);$result=['checkedAt'=>time(),'ok'=>false,'code'=>'configuration'];
        try {
            $key=Plugin::accessKey();
            if($key==='') throw new \RuntimeException('configuration');
            $client=new StoreApiClient($key,new WordPressTransport(true),Configuration::shopUrl(),Configuration::apiUrl());
            $result['context']=$client->context();
            $products=$client->read('product',['limit'=>1,'includes'=>['product'=>['id']]]);
            if(!isset($products['elements'])||!is_array($products['elements'])) throw new ShopUnavailable('response');
            $result['ok']=true;$result['code']='';
        } catch(ShopUnavailable $e) {$result['code']=$e->getMessage();}
        catch(\Throwable $e) {$result['code']='configuration';}
        $result['durationMs']=(int)round((microtime(true)-$start)*1000);
        update_option('dw_sw_connection_test',$result,false);return $result;
    }
    public static function detectedCaches(): array {
        $plugins=[];
        if(function_exists('rocket_clean_domain')) $plugins[]='WP Rocket';
        if(defined('LSCWP_V')||class_exists('LiteSpeed\\Core')) $plugins[]='LiteSpeed Cache';
        if(function_exists('wp_cache_clear_cache')) $plugins[]='WP Super Cache';
        if(function_exists('w3tc_flush_posts')) $plugins[]='W3 Total Cache';
        return $plugins;
    }
    public static function purgePages(): void {
        if(!get_option('dw_sw_purge_pages',true)) return;
        $done=[];
        foreach(self::detectedCaches() as $name) {
            try {
                switch($name) {
                    case 'WP Rocket': rocket_clean_domain();break;
                    case 'LiteSpeed Cache': do_action('litespeed_purge','*');break;
                    case 'WP Super Cache': wp_cache_clear_cache();break;
                    case 'W3 Total Cache': w3tc_flush_posts();break;
                }
                $done[]=$name;
            } catch(\Throwable $e) { /* Diagnostics only, never plugin exceptions or credentials. */ }
        }
        update_option('dw_sw_page_purge',['at'=>time(),'plugins'=>$done],false);
        do_action('dw_sw_page_cache_purge_requested');
    }
    public static function references(int $postId): array {
        $refs=Presentation::associations(get_post_meta($postId,'_dw_sw_products',true));$groups=[];
        $walk=static function(array $blocks) use(&$walk,&$refs,&$groups): void {
            foreach($blocks as $block) {
                $name=$block['blockName']??'';$a=$block['attrs']??[];
                if(in_array($name,['deckerweb/shopware-product','deckerweb/shopware-product-button'],true)) $refs[]=$a;
                if($name==='deckerweb/shopware-product-grid'&&!empty($a['categoryId'])) $groups[]=$a['categoryId'];
                if(!empty($block['innerBlocks'])) $walk($block['innerBlocks']);
            }
        };
        $walk(parse_blocks((string)get_post_field('post_content',$postId)));
        return ['products'=>Presentation::associations($refs),'groups'=>array_values(array_unique($groups))];
    }
    public static function column(string $column,int $postId): void {
        if($column!=='dw_sw_products') return;
        $refs=self::references($postId);$domain='connect-for-shopware';
        if(!$refs['products']&&!$refs['groups']) {echo '—';return;}
        echo '<a href="'.esc_url(get_edit_post_link($postId)).'">'.esc_html(sprintf(__('%d products · %d dynamic grids',$domain),count($refs['products']),count($refs['groups']))).'</a>';
        $labels=[];
        foreach(array_slice($refs['products'],0,4) as $ref) {
            try {$p=Plugin::repository()->cachedProduct($ref['productId'],$ref['selectionMode']);}
            catch(\Throwable $e) {$p=null;}
            $labels[]=$p?$p['title']:'ID '.substr($ref['productId'],0,8);
        }
        if($labels) echo '<br><small>'.esc_html(implode(' · ',$labels)).'</small>';
    }
}
