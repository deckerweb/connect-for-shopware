<?php
namespace Deckerweb\Shopware\Bricks;
use Deckerweb\Shopware\{Plugin,Presentation};
defined('ABSPATH') || exit;
class Product extends \Bricks\Element {
    public $category='general';
    public $name='dw-shopware-product';
    public $icon='ti-package';
    public function get_label() {return esc_html__('Shopware Product','connect-for-shopware');}
    public function set_control_groups() {
        $this->control_groups['product']=['title'=>esc_html__('Product','connect-for-shopware'),'tab'=>'content'];
        $this->control_groups['content']=['title'=>esc_html__('Content','connect-for-shopware'),'tab'=>'content'];
        $this->control_groups['parts']=['title'=>esc_html__('Product styling','connect-for-shopware'),'tab'=>'content'];
    }
    public function set_controls() {
        $this->controls['productRef']=['group'=>'product','type'=>'select','label'=>esc_html__('Search products','connect-for-shopware'),
            'searchable'=>true,'clearOnChange'=>['variantRef'],'optionsAjax'=>['action'=>'dw_sw_product_options'],
            'description'=>esc_html__('Select a product family or a concrete product. Search by name or number.','connect-for-shopware')];
        $this->controls['variantRef']=['group'=>'product','type'=>'select','label'=>esc_html__('Choose variant','connect-for-shopware'),
            'searchable'=>true,'optionsAjax'=>['action'=>'dw_sw_variant_options','productRef'=>'{{productRef}}'],
            'required'=>['productRef','!=',''],
            'description'=>esc_html__('Optional: select a complete size and colour combination. Clear this when changing the main product.','connect-for-shopware')];
        $this->presentationControls();
    }
    protected function presentationControls(): void {
        $this->controls['preset']=['group'=>'content','type'=>'select','label'=>__('Display preset','connect-for-shopware'),
            'options'=>['compact'=>__('Compact recommendation','connect-for-shopware'),'description'=>__('Product description','connect-for-shopware'),'details'=>__('Full product details','connect-for-shopware')],
            'clearOnChange'=>['showText','showTabs','infoPosition','imageMode','textMode','linkStyle']];
        $labels=['showQuickView'=>__('Quick view','connect-for-shopware'),'showImage'=>__('Image','connect-for-shopware'),'showTitle'=>__('Title','connect-for-shopware'),
            'showText'=>__('Text','connect-for-shopware'),'showPrice'=>__('Price','connect-for-shopware'),
            'showListPrice'=>__('List price','connect-for-shopware'),'showAvailability'=>__('Availability','connect-for-shopware'),
            'showVariant'=>__('Variant text','connect-for-shopware'),'showButton'=>__('Button','connect-for-shopware'),'showTabs'=>__('Product details tabs','connect-for-shopware'),'showDescriptionTab'=>__('Description tab','connect-for-shopware'),'showDocumentsTab'=>__('Data sheets tab','connect-for-shopware'),'showManufacturerTab'=>__('Manufacturer tab','connect-for-shopware')];
        foreach($labels as $key=>$label) $this->controls[$key]=['group'=>'content','type'=>'select','label'=>$label,
            'options'=>['yes'=>__('Show','connect-for-shopware'),'no'=>__('Hide','connect-for-shopware')],
            'default'=>Presentation::defaults()[$key]?'yes':'no','inline'=>true];
        $this->controls['headingTag']=['group'=>'content','type'=>'select','label'=>__('Heading tag','connect-for-shopware'),
            'options'=>array_combine(['h2','h3','h4','h5','h6','p'],['H2','H3','H4','H5','H6','P']),'default'=>'h3'];
        $this->controls['infoPosition']=['group'=>'content','type'=>'select','label'=>__('Product information position','connect-for-shopware'),'options'=>['right'=>__('Right of image','connect-for-shopware'),'left'=>__('Left of image','connect-for-shopware'),'below'=>__('Below image','connect-for-shopware')],'default'=>'right'];
        $this->controls['imageMode']=['group'=>'content','type'=>'select','label'=>__('Images','connect-for-shopware'),'options'=>['cover'=>__('Cover image','connect-for-shopware'),'gallery'=>__('Product gallery','connect-for-shopware')],'default'=>'cover'];
        $this->controls['textMode']=['group'=>'content','type'=>'select','label'=>__('Text source','connect-for-shopware'),
            'options'=>['summary'=>__('SEO summary','connect-for-shopware'),'description'=>__('Description','connect-for-shopware'),'custom'=>__('Custom text','connect-for-shopware')],'default'=>'summary'];
        $this->controls['customText']=['group'=>'content','type'=>'textarea','label'=>__('Custom text','connect-for-shopware'),'required'=>['textMode','=','custom']];
        $this->controls['linkStyle']=['group'=>'content','type'=>'select','label'=>__('Product link style','connect-for-shopware'),'options'=>['link'=>__('Text link','connect-for-shopware'),'button'=>__('Button','connect-for-shopware')],'default'=>'link'];
        $this->controls['linkTarget']=['group'=>'content','type'=>'select','label'=>__('Open product link in','connect-for-shopware'),'options'=>['same'=>__('Current tab','connect-for-shopware'),'new'=>__('New tab','connect-for-shopware')],'default'=>'same'];
        $this->controls['buttonText']=['group'=>'content','type'=>'text','label'=>__('Button text','connect-for-shopware')];
        $this->controls['buttonUrl']=['group'=>'content','type'=>'text','label'=>__('Custom button URL (optional)','connect-for-shopware')];
        $partLabels=['title'=>$labels['showTitle'],'text'=>$labels['showText'],'price'=>$labels['showPrice'],'list-price'=>$labels['showListPrice'],
            'reference-price'=>__('Reference price','connect-for-shopware'),'availability'=>$labels['showAvailability'],'variant'=>$labels['showVariant'],'button'=>$labels['showButton']];
        foreach(array_keys($partLabels) as $part) {
            $this->controls[$part.'Typography']=['group'=>'parts','type'=>'typography','label'=>$partLabels[$part],
                'css'=>[['property'=>'font','selector'=>'.dw-sw-'.$part]]];
        }
        $this->controls['imageWidth']=['group'=>'parts','type'=>'number','units'=>true,'label'=>__('Image width','connect-for-shopware'),
            'css'=>[['property'=>'width','selector'=>'.dw-sw-image']]];
        $this->controls['imageMaxHeight']=['group'=>'parts','type'=>'number','units'=>true,'label'=>__('Image maximum height','connect-for-shopware'),
            'css'=>[['property'=>'max-height','selector'=>'.dw-sw-image']]];
        $this->controls['imageBorder']=['group'=>'parts','type'=>'border','label'=>__('Image border','connect-for-shopware'),
            'css'=>[['property'=>'border','selector'=>'.dw-sw-image']]];
        $this->controls['buttonBackground']=['group'=>'parts','type'=>'color','label'=>__('Button background','connect-for-shopware'),
            'css'=>[['property'=>'background-color','selector'=>'.dw-sw-button']]];
        $this->controls['buttonPadding']=['group'=>'parts','type'=>'dimensions','label'=>__('Button padding','connect-for-shopware'),
            'css'=>[['property'=>'padding','selector'=>'.dw-sw-button']]];
        $this->controls['buttonBorder']=['group'=>'parts','type'=>'border','label'=>__('Button border','connect-for-shopware'),
            'css'=>[['property'=>'border','selector'=>'.dw-sw-button']]];
    }
    protected function containerTag(): string {return 'article';}
    protected function containerClass(): string {return 'dw-sw-product';}
    protected function productContent(array $ref): string {return Plugin::product($ref,array_merge($this->settings,['_builder'=>'bricks']));}
    public function enqueue_scripts() {wp_enqueue_style('dw-sw-product');}
    public function render() {
        try {
            $ref=Presentation::reference((string)($this->settings['productRef']??''));
            if(!empty($this->settings['variantRef'])) {
                $override=Presentation::reference((string)$this->settings['variantRef']);
                if($override['selectionMode']!=='variant') throw new \RuntimeException('Invalid variant');
                $base=Plugin::repository()->product($ref['productId'],$ref['selectionMode']);
                $variant=Plugin::repository()->product($override['productId'],'variant');
                if(($base['parentId']?:$base['id'])!==$variant['parentId']) throw new \RuntimeException('Variant belongs to a different family');
                $ref=$override;
            }
            $content=$this->productContent($ref);
        } catch(\Throwable $e) {
            $content=Plugin::isEditor()?'<p>'.esc_html__('Select a product and a matching variant.','connect-for-shopware').'</p>':'';
        }
        if($content==='') return;
        $this->set_attribute('_root','class',$this->containerClass());$tag=$this->containerTag();
        echo '<'.$tag.' '.$this->render_attributes('_root').'>'.$content.'</'.$tag.'>';
    }
}
