<?php
namespace Deckerweb\Shopware\Bricks;
use Deckerweb\Shopware\Plugin;
defined('ABSPATH') || exit;
/**
 * Provide native category-backed Bricks product grids and pagination.
 */
class ProductGrid extends RelatedProducts {
    public $name='dw-shopware-product-grid';
    public $icon='ti-layout-grid2';
    /**
     * Return the translated native Bricks element name.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public function get_label() {return esc_html__('Shopware Product Grid','connect-for-shopware');}
    /**
     * Declare the native Bricks controls used by this element.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public function set_controls() {
        parent::set_controls();
        foreach(['showPagination'=>__('Pagination','connect-for-shopware'),'showShopLink'=>__('More products link','connect-for-shopware')] as $key=>$label) $this->controls[$key]=['group'=>'product','type'=>'checkbox','label'=>$label];
        $this->controls['shopLinkText']=['group'=>'product','type'=>'text','label'=>__('More products link text','connect-for-shopware')];
        unset($this->controls['source']);
        $this->controls['categoryId']=['group'=>'product','type'=>'select','searchable'=>true,
            'label'=>__('Dynamic Shopware category','connect-for-shopware'),
            'optionsAjax'=>['action'=>'dw_sw_category_options'],'clearOnChange'=>['order'],
            'description'=>__('Only active categories using a dynamic product group are available. Rules remain in Shopware.','connect-for-shopware')];
        $this->controls['limit']=['group'=>'product','type'=>'number','min'=>1,'max'=>48,'default'=>6,
            'label'=>__('Maximum products','connect-for-shopware')];
        $this->controls['order']=['group'=>'product','type'=>'select','searchable'=>true,
            'label'=>__('Shopware sorting','connect-for-shopware'),
            'optionsAjax'=>['action'=>'dw_sw_sort_options','categoryId'=>'{{categoryId}}'],
            'description'=>__('Leave empty to use Shopware default sorting.','connect-for-shopware')];
    }
    /**
     * Output the category grid inside native Bricks root attributes.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public function render() {
        $content=Plugin::grid(array_merge($this->settings,['_builder'=>'bricks','gridId'=>$this->id]));
        if($content==='') return;
        $this->set_attribute('_root','class','dw-sw-related dw-sw-dynamic-grid');
        echo '<section '.$this->render_attributes('_root').'>'.$content.'</section>';
    }
}
