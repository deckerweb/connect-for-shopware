<?php
namespace Deckerweb\Shopware\Bricks;
use Deckerweb\Shopware\Plugin;
defined('ABSPATH') || exit;
/**
 * Render products assigned to the current article in native Bricks templates.
 */
class RelatedProducts extends Product {
    public $name='dw-shopware-related-products';
    public $icon='ti-layout-grid2';
    /**
     * Return the translated native Bricks element name.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public function get_label() {return esc_html__('Shopware Related Products','connect-for-shopware');}
    /**
     * Declare the native Bricks controls used by this element.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public function set_controls() {
        $this->controls['heading']=['group'=>'product','type'=>'text','label'=>__('Section heading','connect-for-shopware')];
        $this->controls['source']=['group'=>'product','type'=>'info',
            'content'=>esc_html__('Uses Shopware products assigned to the current article. For a Single template, select a preview article with assignments.','connect-for-shopware')];
        $this->presentationControls();
        $this->controls['columns']=['group'=>'parts','type'=>'number','min'=>1,'max'=>6,'default'=>3,
            'label'=>__('Columns','connect-for-shopware'),
            'css'=>[['property'=>'grid-template-columns','selector'=>'.dw-sw-grid','value'=>'repeat(%s, minmax(0, 1fr))']]];
        $this->controls['gap']=['group'=>'parts','type'=>'number','units'=>true,'label'=>__('Grid gap','connect-for-shopware'),
            'css'=>[['property'=>'gap','selector'=>'.dw-sw-grid']]];
    }
    /**
     * Output article-related products or an editor-only empty-selection message.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public function render() {
        $postId=Plugin::contextPostId();$content=$postId?Plugin::related($postId,array_merge($this->settings,['_builder'=>'bricks'])):'';
        if($content==='') {
            if(!Plugin::isEditor()) return;
            $content='<p>'.esc_html__('No products assigned to the current article.','connect-for-shopware').'</p>';
        }
        $this->set_attribute('_root','class','dw-sw-related');
        echo '<section '.$this->render_attributes('_root').'>'.$content.'</section>';
    }
}
