<?php
namespace Deckerweb\Shopware\Bricks;
use Deckerweb\Shopware\Plugin;
defined('ABSPATH') || exit;
class ProductButton extends Product {
    public $name='dw-shopware-product-button';
    public $icon='ti-link';
    public function get_label() {return esc_html__('Shopware Product Button','connect-for-shopware');}
    public function set_controls() {
        parent::set_controls();
        foreach(array_keys($this->controls) as $key) {
            if(!in_array($key,['productRef','variantRef','linkStyle','linkTarget','buttonText','buttonUrl','buttonTypography','buttonBackground','buttonPadding','buttonBorder'],true)) unset($this->controls[$key]);
        }
        $this->controls['linkStyle']['default']='button';
    }
    protected function containerTag(): string {return 'div';}
    protected function containerClass(): string {return 'dw-sw-cta';}
    protected function productContent(array $ref): string {return Plugin::button($ref,array_replace(['linkStyle'=>'button'],$this->settings,['_builder'=>'bricks']));}
}
