<?php
namespace Deckerweb\Shopware\Bricks;
use Deckerweb\Shopware\Plugin;
defined('ABSPATH') || exit;
/**
 * Provide a native standalone Bricks shop link or product button.
 */
class ProductButton extends Product {
    public $name='dw-shopware-product-button';
    public $icon='ti-link';
    /**
     * Return the translated native Bricks element name.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public function get_label() {return esc_html__('Shopware Product Button','connect-for-shopware');}
    /**
     * Declare the native Bricks controls used by this element.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public function set_controls() {
        parent::set_controls();
        foreach(array_keys($this->controls) as $key) {
            if(!in_array($key,['productRef','variantRef','linkStyle','linkTarget','buttonText','buttonUrl','buttonTypography','buttonBackground','buttonPadding','buttonBorder'],true)) unset($this->controls[$key]);
        }
        $this->controls['linkStyle']['default']='button';
    }
    /**
     * Return the semantic wrapper tag for this element.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    protected function containerTag(): string {return 'div';}
    /**
     * Return the scoped CSS class for this element wrapper.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    protected function containerClass(): string {return 'dw-sw-cta';}
    /**
     * Render the selected product using this element instance settings.
     *
     * @since 1.0.0
     * @param array $ref Normalized product selection used by the native element.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    protected function productContent(array $ref): string {return Plugin::button($ref,array_replace(['linkStyle'=>'button'],$this->settings,['_builder'=>'bricks']));}
}
