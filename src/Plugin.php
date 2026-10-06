<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;
/**
 * Register shared Core, native blocks, Bricks elements and presentation entry points.
 */
final class Plugin {
    private static ?ProductRepository $repository=null;
    private static string $repositoryIdentity="";
    /**
     * Register the WordPress integration callbacks for this component.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function boot(): void {
        add_action('init',[self::class,'init']);
        add_action('rest_api_init',[Rest::class,'register']);
        Admin::boot();Operations::boot();Configuration::boot();Components::boot();(new GitHubUpdates())->register();
        add_action('init',function(): void {
            if(class_exists('Bricks\\Elements')) {
                \Bricks\Elements::register_element(DW_SW_DIR.'bricks/Product.php','','Deckerweb\\Shopware\\Bricks\\Product');
                \Bricks\Elements::register_element(DW_SW_DIR.'bricks/ProductButton.php','','Deckerweb\\Shopware\\Bricks\\ProductButton');
                \Bricks\Elements::register_element(DW_SW_DIR.'bricks/RelatedProducts.php','','Deckerweb\\Shopware\\Bricks\\RelatedProducts');
                \Bricks\Elements::register_element(DW_SW_DIR.'bricks/ProductGrid.php','','Deckerweb\\Shopware\\Bricks\\ProductGrid');
            }
        },20);
        add_action('wp_ajax_dw_sw_product_options',[Rest::class,'bricksProducts']);
        add_action('wp_ajax_dw_sw_category_options',[Rest::class,'bricksCategories']);
        add_action('wp_ajax_dw_sw_sort_options',[Rest::class,'bricksSortings']);
        add_action('wp_ajax_dw_sw_variant_options',[Rest::class,'bricksVariants']);
        add_action('enqueue_block_editor_assets',[self::class,'editorAssets']);
    }
    /**
     * Load translations and register shared assets, blocks and article metadata.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function init(): void {
        load_plugin_textdomain('connect-for-shopware',false,dirname(plugin_basename(DW_SW_DIR.'connect-for-shopware.php')).'/languages');
        wp_register_style('dw-sw-product',DW_SW_URL.'assets/product.css',[],DW_SW_VERSION);
        wp_register_script('dw-sw-product',DW_SW_URL.'assets/product.js',[],DW_SW_VERSION,true);
        wp_add_inline_script('dw-sw-product','window.dwSwProduct='.wp_json_encode([
            'endpoint'=>rest_url('deckerweb-shopware/v1/quick-view'),'title'=>__('Product details','connect-for-shopware'),
            'close'=>__('Close','connect-for-shopware'),'loading'=>__('Loading product…','connect-for-shopware'),
            'error'=>__('Quick view unavailable. Please open the product in the shop.','connect-for-shopware')]).';','before');
        wp_register_script('dw-sw-editor',DW_SW_URL.'assets/editor.js',
            ['wp-blocks','wp-element','wp-block-editor','wp-components','wp-api-fetch','wp-i18n','wp-data','wp-plugins','wp-editor','wp-server-side-render'],DW_SW_VERSION,true);
        wp_set_script_translations('dw-sw-editor','connect-for-shopware',DW_SW_DIR.'languages');
        register_block_type(DW_SW_DIR.'blocks/product',['render_callback'=>[self::class,'blockProduct']]);
        register_block_type(DW_SW_DIR.'blocks/related-products',['render_callback'=>[self::class,'blockRelated']]);
        register_block_type(DW_SW_DIR.'blocks/product-button',['render_callback'=>[self::class,'blockButton']]);
        register_block_type(DW_SW_DIR.'blocks/product-grid',['render_callback'=>[self::class,'blockGrid']]);
        foreach(self::postTypes() as $type) {
            register_post_meta($type,'_dw_sw_products',['type'=>'array','single'=>true,'default'=>[],
                'sanitize_callback'=>[Presentation::class,'associations'],
                'auth_callback'=>static fn($allowed,$key,$postId)=>current_user_can('edit_post',(int)$postId),
                'show_in_rest'=>['schema'=>['type'=>'array','maxItems'=>50,'items'=>['type'=>'object',
                    'additionalProperties'=>false,'required'=>['productId','selectionMode'],
                    'properties'=>['productId'=>['type'=>'string','pattern'=>'^[a-f0-9]{32}$'],
                    'selectionMode'=>['type'=>'string','enum'=>['family','variant']]]]]]]);
        }
    }

    /**
     * Return existing editorial types selected by the public post-type filter.
     *
     * @since 1.0.0
     * @return array Normalized result data for the documented operation.
     */
    public static function postTypes(): array {
        return array_values(array_filter((array)/**
         * Filter the editorial post types that support Shopware product assignments.
         *
         * @since 1.0.0
         * @param string[] $post_types Existing registered post-type names to return.
         */
        apply_filters('dw_sw_post_types' ,['post','page']),
            static fn($type)=>is_string($type)&&post_type_exists($type)));
    }

    /**
     * Enqueue the translated block editor script and shared presentation presets.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function editorAssets(): void {wp_enqueue_script('dw-sw-editor');wp_add_inline_script('dw-sw-editor','window.dwSwEditor='.wp_json_encode(['postTypes'=>self::postTypes(),'presets'=>Presentation::presets()]).';','before');}

    /**
     * Resolve explicit site credentials or the global constant/environment without persisting them.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function accessKey(): string {
        if(is_multisite()){
            $site=get_current_blog_id();$map=defined('DW_SW_ACCESS_KEYS')?constant('DW_SW_ACCESS_KEYS'):[];
            if(is_array($map)&&isset($map[$site])&&is_string($map[$site])&&$map[$site]!=='')return $map[$site];
            $specific=getenv('DW_SW_ACCESS_KEY_'.$site);if(is_string($specific)&&$specific!=='')return $specific;
        }
        $constant=defined('DW_SW_ACCESS_KEY')?constant('DW_SW_ACCESS_KEY'):'';
        if(is_string($constant)&&$constant!=='')return $constant;
        return (string)getenv('DW_SW_ACCESS_KEY');
    }

    /**
     * Create or reuse the repository for the current shop/credential fingerprint.
     *
     * @since 1.0.0
     * @return ProductRepository Repository for the current anonymous shop context.
     */
    public static function repository(): ProductRepository {
        $key=self::accessKey();
        if($key==='') throw new \RuntimeException('Connector not configured');
        $identity=Configuration::identity();
        if(self::$repository&&self::$repositoryIdentity===$identity) return self::$repository;
        $ttl=max(900,min(3600,(int)get_option('dw_sw_cache_ttl',1800)));
        $client=new StoreApiClient($key,new WordPressTransport(),Configuration::shopUrl(),Configuration::apiUrl());
        self::$repositoryIdentity=$identity;
        self::$repository=new ProductRepository($client,new TransientCache(),new ProductMapper(Configuration::shopUrl(),[$client,'context']),$identity,$ttl);
        return self::$repository;
    }

    /**
     * Render one product selection or a safe editor-only unavailable message.
     *
     * @since 1.0.0
     * @param array $reference Normalized product UUID and family/variant mode.
     * @param array $settings Shared presentation and catalog selection controls.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function product(array $reference,array $settings=[]): string {
        if(empty($reference['productId'])) return '';
        try {
            $p=self::repository()->product((string)$reference['productId'],(string)($reference['selectionMode']??'variant'));
            return (new ProductRenderer())->render($p,$settings);
        } catch(\Throwable $e) {
            return self::isEditor()?'<p class="dw-sw-error">'.esc_html__('Product unavailable. Check the selection and connector configuration.','connect-for-shopware').'</p>':'';
        }
    }

    /**
     * Detect Gutenberg REST preview or supported Bricks editor rendering.
     *
     * @since 1.0.0
     * @return bool Whether the operation is allowed or successfully completed.
     */
    public static function isEditor(): bool {
        return current_user_can('edit_posts')&&(is_admin()||(defined('REST_REQUEST')&&REST_REQUEST)
            ||(function_exists('bricks_is_builder')&&bricks_is_builder())
            ||(function_exists('bricks_is_builder_call')&&bricks_is_builder_call()));
    }

    /**
     * Render a Gutenberg product with native wrapper attributes.
     *
     * @since 1.0.0
     * @param array $attributes Gutenberg block attributes.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function blockProduct(array $attributes): string {
        $content=self::product($attributes,$attributes);
        if($content==='') return '';
        return '<article '.get_block_wrapper_attributes(['class'=>'dw-sw-product']).'>'.$content.'</article>';
    }

    /**
     * Render a standalone shop action for a selected family or variant.
     *
     * @since 1.0.0
     * @param array $reference Normalized product UUID and family/variant mode.
     * @param array $settings Shared presentation and catalog selection controls.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function button(array $reference,array $settings=[]): string {
        try {
            $p=self::repository()->product((string)($reference['productId']??''),(string)($reference['selectionMode']??'variant'));
            return (new ProductRenderer())->button($p,array_replace(['linkStyle'=>'button'],$settings));
        } catch(\Throwable $e) {return self::isEditor()?'<p>'.esc_html__('Product unavailable. Check the selection and connector configuration.','connect-for-shopware').'</p>':'';}
    }

    /**
     * Render the Gutenberg standalone product button wrapper.
     *
     * @since 1.0.0
     * @param array $attributes Gutenberg block attributes.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function blockButton(array $attributes): string {
        $content=self::button($attributes,$attributes);
        return $content===''?'':'<div '.get_block_wrapper_attributes(['class'=>'dw-sw-cta']).'>'.$content.'</div>';
    }

    /**
     * Resolve the current article or supported template preview article.
     *
     * @since 1.0.0
     * @return int Resolved integer identifier.
     */
    public static function contextPostId(): int {
        $id=(int)get_the_ID();
        if($id&&get_post_type($id)!=='bricks_template') return $id;
        if(class_exists('Bricks\\Database')) {
            $id=(int)(\Bricks\Database::$page_data['preview_or_post_id']??0);
            if($id&&get_post_type($id)!=='bricks_template') return $id;
        }
        $id=(int)get_queried_object_id();
        return $id&&get_post_type($id)!=='bricks_template'?$id:0;
    }

    /**
     * Render assigned article products with shared cards and an optional preview selection.
     *
     * @since 1.0.0
     * @param int $postId WordPress article or preview post ID.
     * @param array $settings Shared presentation and catalog selection controls.
     * @param array|null $previewItems Optional authorized editor assignments not yet saved to the article.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function related(int $postId,array $settings=[],?array $previewItems=null): string {
        $items=Presentation::associations($previewItems??get_post_meta($postId,'_dw_sw_products',true));$cards='';
        foreach($items as $item) {
            $content=self::product($item,$settings);
            if($content!=='') $cards.='<article class="dw-sw-product">'.$content.'</article>';
        }
        if($cards==='') return '';
        $heading=sanitize_text_field((string)($settings['heading']??''));
        if($heading==='') $heading=__('Matching products for this article','connect-for-shopware');
        return '<h2 class="dw-sw-related-heading">'.esc_html($heading).'</h2><div class="dw-sw-grid">'.$cards.'</div>';
    }

    /**
     * Render a rule-backed category grid with independent pagination and optional shop link.
     *
     * @since 1.0.0
     * @param array $settings Shared presentation and catalog selection controls.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function grid(array $settings=[]): string {
        $id=(string)($settings['categoryId']??'');
        if($id==='') return self::isEditor()?'<p class="dw-sw-error">'.esc_html__('Select a dynamic Shopware category.','connect-for-shopware').'</p>':'';
        try {
            $limit=max(1,min(48,(int)($settings['limit']??6)));
            $listing=self::repository()->listing($id,$limit,1,(string)($settings['order']??''));
            $pages=max(1,min(1000,(int)ceil($listing['total']/$limit)));
            $root=Pagination::identity($settings);$query='dw_sw_page_'.$root;
            $pagination=filter_var($settings['showPagination']??false,FILTER_VALIDATE_BOOLEAN);
            $requested=(!self::isEditor()&&$pagination)?(int)($_GET[$query]??1):1;
            $page=max(1,min($pages,$requested));
            if($page>1) $listing=self::repository()->listing($id,$limit,$page,(string)($settings['order']??''));
            $cards='';$renderer=new ProductRenderer();
            $presentation=Presentation::settings($settings);
            foreach($listing['items'] as $product) {
                if($presentation['showTabs']&&$presentation['showDocumentsTab']) {
                    try {
                        $detail=self::repository()->product($product['id'],$product['isFamily']?'family':'variant');
                        $product['documents']=$detail['documents'];
                    } catch(\Throwable $ignored) { /* Keep listing prices and product representation. */ }
                }
                $cards.='<article class="dw-sw-product">'.$renderer->render($product,$settings).'</article>';
            }
            if($cards==='') return '<p>'.esc_html__('No products currently match this selection.','connect-for-shopware').'</p>';
            $heading=sanitize_text_field((string)($settings['heading']??''));
            if($heading==='') $heading=$listing['category']['name'];
            $columns=max(0,min(6,(int)($settings['gridColumns']??0)));
            $style=$columns?' style="--dw-sw-grid-columns:'.$columns.'"':'';
            $html='<div id="dw-sw-grid-'.esc_attr($root).'"><h2 class="dw-sw-related-heading">'.esc_html($heading).'</h2><div class="dw-sw-grid'.($columns?' dw-sw-fixed-columns':'').'"'.$style.'>'.$cards.'</div>';
            if($pagination&&$pages>1) $html.=Pagination::render($pages,$page,$query,$root);
            if(filter_var($settings['showShopLink']??false,FILTER_VALIDATE_BOOLEAN)) {
                $url=Configuration::shopUrl().'/navigation/'.$id;
                $html.='<div class="dw-sw-shop-link">'.(new ProductRenderer())->button(['url'=>$url],array_replace($settings,[
                    'buttonUrl'=>$url,'buttonText'=>sanitize_text_field((string)($settings['shopLinkText']??''))?:__('More products in the shop','connect-for-shopware'),'linkStyle'=>'button'])).'</div>';
            }
            return $html.'</div>';
        } catch(\Throwable $e) {
            return self::isEditor()?'<p class="dw-sw-error">'.esc_html__('Dynamic product listing unavailable. Check the category and connector configuration.','connect-for-shopware').'</p>':'';
        }
    }

    /**
     * Wrap the category grid in native Gutenberg block attributes.
     *
     * @since 1.0.0
     * @param array $attributes Gutenberg block attributes.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function blockGrid(array $attributes): string {
        $html=self::grid($attributes);
        return $html===''?'':'<section '.get_block_wrapper_attributes(['class'=>'dw-sw-related dw-sw-dynamic-grid']).'>'.$html.'</section>';
    }

    /**
     * Resolve the block article context and render its assigned products.
     *
     * @since 1.0.0
     * @param array $attributes Gutenberg block attributes.
     * @param string $content Saved block content; dynamic rendering resolves its product data.
     * @param \WP_Block $block Current WordPress block instance and article context.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function blockRelated(array $attributes,string $content,$block): string {
        $postId=(int)($block->context['postId']??self::contextPostId());
        $html=$postId?self::related($postId,$attributes):'';
        return $html===''?'':'<section '.get_block_wrapper_attributes(['class'=>'dw-sw-related']).'>'.$html.'</section>';
    }
}
