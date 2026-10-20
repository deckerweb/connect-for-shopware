<?php
/** Editorial usage, scoped refresh and explicit catalog checks. */
declare(strict_types=1);
namespace Deckerweb\Shopware;
defined('ABSPATH') || exit;

/** Find product references in current-site content without contacting Shopware. */
final class EditorialTools {
    /** Register the authenticated editorial helper routes.
     * @return void No separate administration menu is registered.
     */
    public static function boot(): void {
        add_action('rest_api_init',[self::class,'routes']);
    }
    /** Collect saved block, article and native Bricks references without remote reads.
     * @param int $postId Current-site article or template identifier.
     * @param array $visited Reusable block identifiers already followed.
     * @return array Normalized product selections and dynamic category identifiers.
     */
    public static function references(int $postId,array &$visited=[]): array {
        if(isset($visited[$postId])||count($visited)>=128)return ['products'=>[],'groups'=>[]];
        $visited[$postId]=true;$refs=Operations::references($postId);
        self::blocks(parse_blocks((string)get_post_field('post_content',$postId)),$refs,$visited);
        foreach(['_bricks_page_content_2','_bricks_page_header_2','_bricks_page_footer_2'] as $key)self::bricks(get_post_meta($postId,$key,true),$refs);
        return ['products'=>Presentation::associations($refs['products']),'groups'=>array_values(array_unique(array_filter($refs['groups'],[self::class,'uuid'])))];
    }
    /** Validate a Shopware identifier before exposing or reading it.
     * @param mixed $id Candidate identifier.
     * @return bool Whether it is a lowercase Shopware UUID.
     */
    public static function uuid(mixed $id): bool {return is_string($id)&&preg_match('/^[a-f0-9]{32}$/D',$id)===1;}
    /** Follow reusable block definitions with a bounded cycle guard.
     * @param array $blocks Parsed block tree.
     * @param array $refs Collected references, updated in place.
     * @param array $visited Reusable definitions already followed.
     * @return void No remote reads or content changes.
     */
    private static function blocks(array $blocks,array &$refs,array &$visited): void {
        foreach($blocks as $block){
            if(($block['blockName']??'')==='core/block'&&!empty($block['attrs']['ref'])){
                $id=(int)$block['attrs']['ref'];
                if(get_post_type($id)==='wp_block'&&current_user_can('edit_post',$id)){
                    $nested=self::references($id,$visited);$refs['products']=array_merge($refs['products'],$nested['products']);$refs['groups']=array_merge($refs['groups'],$nested['groups']);
                }
            }
            if(!empty($block['innerBlocks']))self::blocks($block['innerBlocks'],$refs,$visited);
        }
    }
    /** Inspect saved native Bricks element settings without executing templates.
     * @param mixed $tree Saved Bricks element data.
     * @param array $refs Collected references, updated in place.
     * @param int $depth Current traversal depth.
     * @return void Ignores unknown or malformed element data.
     */
    private static function bricks(mixed $tree,array &$refs,int $depth=0): void {
        if(!is_array($tree)||$depth>12)return;
        if(isset($tree['name'])&&in_array($tree['name'],['dw-shopware-product','dw-shopware-product-button','dw-shopware-product-grid'],true)){
            $s=(array)($tree['settings']??[]);
            if($tree['name']==='dw-shopware-product-grid')$refs['groups'][]=$s['categoryId']??'';
            else try{$refs['products'][]=Presentation::reference((string)($s['variantRef']??'')?:((string)($s['productRef']??'')));}catch(\InvalidArgumentException $ignored){}
        }
        foreach($tree as $value)if(is_array($value))self::bricks($value,$refs,$depth+1);
    }
    /** Read a bounded page of candidate content in the current site only.
     * @param int $page One-based candidate page.
     * @return array Rows with permitted articles plus a continuation flag.
     */
    public static function usage(int $page=1): array {
        global $wpdb;$types=array_values(array_unique(array_merge(Plugin::postTypes(),['wp_block'],post_type_exists('bricks_template')?['bricks_template']:[])));
        $page=max(1,min(10000,$page));$placeholders=implode(',',array_fill(0,count($types),'%s'));
        $sql="SELECT DISTINCT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON p.ID=m.post_id AND m.meta_key IN ('_dw_sw_products','_bricks_page_content_2','_bricks_page_header_2','_bricks_page_footer_2') WHERE p.post_type IN ($placeholders) AND p.post_status NOT IN ('trash','auto-draft','inherit') AND (p.post_content LIKE %s OR p.post_content LIKE %s OR (m.meta_key='_dw_sw_products' AND m.meta_value NOT IN ('','a:0:{}')) OR m.meta_value LIKE %s) ORDER BY p.ID DESC LIMIT 51 OFFSET %d";
        $ids=$wpdb->get_col($wpdb->prepare($sql,...array_merge($types,['%'.$wpdb->esc_like('deckerweb/shopware-').'%','%'.$wpdb->esc_like('wp:block').'%','%'.$wpdb->esc_like('dw-shopware-').'%',($page-1)*50])));
        $more=count($ids)>50;$items=[];
        foreach(array_slice($ids,0,50) as $id){
            if(!current_user_can('edit_post',(int)$id))continue;
            $refs=self::references((int)$id);if(!$refs['products']&&!$refs['groups'])continue;
            $items[]=['id'=>(int)$id,'title'=>get_the_title($id),'status'=>get_post_status($id),'type'=>get_post_type($id),'edit'=>get_edit_post_link($id,'raw'),'references'=>$refs];
        }
        return ['items'=>$items,'more'=>$more,'page'=>$page];
    }
    /** Format a cached product title and number without starting a shop request.
     * @param array $reference Validated saved product reference.
     * @return string Human-readable cached label, falling back to the product identifier.
     */
    private static function productLabel(array $reference): string {
        try {
            $p=Plugin::repository()->cachedProduct($reference['productId'],$reference['selectionMode']);
            if($p)return implode(' · ',array_filter([(string)$p['title'],(string)$p['productNumber'],$reference['productId']]));
        }catch(\Throwable $ignored){}
        return $reference['productId'];
    }
    /** Render the bounded, local-only usage overview.
     * @return void No remote product lookup occurs while rendering this page.
     */
    public static function page(): void {
        if(!current_user_can('manage_options'))return;
        $page=isset($_GET['usage_page'])&&is_scalar($_GET['usage_page'])?max(1,min(10000,absint($_GET['usage_page']))):1;$data=self::usage($page);
        echo '<h2>'.esc_html__('Shopware product usage','connect-for-shopware').'</h2><p>'.esc_html__('Saved article assignments, Gutenberg blocks, reusable blocks and native Bricks elements on this site. Check a row to request fresh shop data. Nothing is removed or replaced automatically.','connect-for-shopware').'</p>';
        echo '<div class="dw-sw-usage-scroll" role="region" tabindex="0" aria-label="'.esc_attr__('Shopware product usage','connect-for-shopware').'"><table class="widefat striped dw-sw-usage"><thead><tr><th scope="col">'.esc_html__('Content','connect-for-shopware').'</th><th scope="col">'.esc_html__('Saved selections','connect-for-shopware').'</th><th scope="col">'.esc_html__('Check availability','connect-for-shopware').'</th></tr></thead><tbody>';
        foreach($data['items'] as $row){
            echo '<tr><td><a href="'.esc_url($row['edit']).'">'.esc_html($row['title']?:__('Untitled','connect-for-shopware')).'</a><br><small>'.esc_html($row['type'].' · '.$row['status']).'</small></td><td><ul>';
            foreach($row['references']['products'] as $ref)echo '<li>'.esc_html(($ref['selectionMode']==='family'?__('Product family','connect-for-shopware'):__('Product / variant','connect-for-shopware')).' · '.self::productLabel($ref)).'</li>';
            foreach($row['references']['groups'] as $id)echo '<li>'.esc_html__('Dynamic Shopware category','connect-for-shopware').' · '.esc_html($id).'</li>';
            echo '</ul></td><td><button type="button" class="button" data-dw-sw-check="'.esc_attr((string)$row['id']).'">'.esc_html__('Check saved selections','connect-for-shopware').'</button><div class="dw-sw-check-results" role="status" aria-live="polite"></div></td></tr>';
        }
        if(!$data['items'])echo '<tr><td colspan="3">'.esc_html__('No saved selections on this page.','connect-for-shopware').'</td></tr>';
        echo '</tbody></table></div><p>';
        if($page>1)echo '<a class="button" href="'.esc_url(add_query_arg(['usage_page'=>$page-1],Admin::tabUrl('usage'))).'">'.esc_html__('Previous results','connect-for-shopware').'</a> ';
        if($data['more'])echo '<a class="button" href="'.esc_url(add_query_arg(['usage_page'=>$page+1],Admin::tabUrl('usage'))).'">'.esc_html__('Next results','connect-for-shopware').'</a>';
        echo '</p><p class="description dw-sw-usage-help">'.esc_html__('Edit the linked content to replace a selection. Template references are shown at their saved source; dynamic runtime values and global Bricks Component definitions are not expanded.','connect-for-shopware').'</p>';
    }
    /** Register authenticated editorial helper routes.
     * @return void All mutations require cookie REST nonces or another Core authentication method.
     */
    public static function routes(): void {
        register_rest_route('deckerweb-shopware/v1','/selection/refresh',['methods'=>'POST','permission_callback'=>[Rest::class,'permission'],'callback'=>[self::class,'refresh'],'args'=>[
            'kind'=>['required'=>true,'type'=>'string','enum'=>['product','category']],'id'=>['required'=>true,'type'=>'string','pattern'=>'^[a-f0-9]{32}$']]]);
        register_rest_route('deckerweb-shopware/v1','/usage/check',['methods'=>'POST','permission_callback'=>[self::class,'checkPermission'],'callback'=>[self::class,'check'],'args'=>[
            'postId'=>['required'=>true,'type'=>'integer','minimum'=>1],'cursor'=>['type'=>'integer','minimum'=>0,'maximum'=>1000,'default'=>0]]]);
    }
    /** Require site administration and access to the particular content being checked.
     * @param \WP_REST_Request $request Validated request containing the article identifier.
     * @return bool Whether both authorization gates pass.
     */
    public static function checkPermission($request): bool {return current_user_can('manage_options')&&current_user_can('edit_post',(int)$request['postId']);}
    /** Invalidate one selection without purging the whole Connector or page cache.
     * @param \WP_REST_Request $request Validated selection kind and identifier.
     * @return \WP_REST_Response|\WP_Error Safe result without credentials.
     */
    public static function refresh($request) {
        try {Plugin::repository()->refreshSelection($request['kind'],$request['id']);return new \WP_REST_Response(['refreshed'=>true],200,['Cache-Control'=>'no-store']);}
        catch(\Throwable $error){return new \WP_Error('dw_sw_refresh',__('Selection could not be refreshed. Check the connection.','connect-for-shopware'),['status'=>503]);}
    }
    /** Explicitly check at most three saved selections; retain all editorial data.
     * @param \WP_REST_Request $request Authorized article and cursor.
     * @return \WP_REST_Response|\WP_Error Fresh status or a safe connection error.
     */
    public static function check($request) {
        $refs=self::references((int)$request['postId']);$items=[];
        foreach($refs['products'] as $ref)$items[]=['kind'=>'product','id'=>$ref['productId'],'mode'=>$ref['selectionMode']];
        foreach($refs['groups'] as $id)$items[]=['kind'=>'category','id'=>$id,'mode'=>''];
        $cursor=(int)$request['cursor'];$results=[];
        foreach(array_slice($items,$cursor,3) as $item){
            $status='available';$title=$item['id'];
            try{
                $repo=Plugin::repository();$repo->refreshSelection($item['kind'],$item['id']);
                $p=$item['kind']==='product'?$repo->product($item['id'],$item['mode']):$repo->listing($item['id'],1);
                if(!empty($p['_stale']))$status='unreachable';
                $title=$item['kind']==='product'?$p['title']:$p['category']['name'];
            }catch(ResourceMissing $error){$status='missing';}catch(\Throwable $error){$status='unreachable';}
            $results[]=$item+['status'=>$status,'title'=>sanitize_text_field((string)$title)];
        }
        return new \WP_REST_Response(['items'=>$results,'next'=>$cursor+count($results),'done'=>$cursor+count($results)>=count($items)],200,['Cache-Control'=>'no-store']);
    }
}
