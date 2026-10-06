<?php
/** Shop settings and server-side credential resolution. */
declare(strict_types=1);
namespace Deckerweb\Shopware;
defined('ABSPATH') || exit;
/**
 * Validate shop endpoints and isolate shop-specific runtime state.
 */
final class Configuration {
    /**
     * Return the validated configured storefront URL.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function shopUrl(): string {
        return ShopUrl::normalize((string)get_option('dw_sw_shop_url',''));
    }
    /**
     * Return the explicit API URL or the storefront URL with the Store API suffix.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function apiUrl(): string {
        $value=(string)get_option('dw_sw_api_url','');
        return $value!==''?ShopUrl::normalize($value):self::shopUrl().'/store-api';
    }
    /**
     * Fingerprint anonymous shop endpoints and credentials without exposing the key.
     *
     * @since 1.0.0
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function identity(): string {
        return hash('sha256',self::shopUrl().'|'.self::apiUrl().'|anonymous|'.hash('sha256',Plugin::accessKey()));
    }
    /**
     * Validate an endpoint setting while preserving its previous value on failure.
     *
     * @since 1.0.0
     * @param mixed $value Untrusted endpoint setting value.
     * @param string $option Registered endpoint option name.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public static function sanitize($value,string $option): string {
        $previous=(string)get_option($option,'');
        if(!is_string($value)) return $previous;
        if(trim($value)==='') return '';
        try {
            $url=ShopUrl::normalize($value);
            if(($option==='dw_sw_api_url'&&!str_ends_with($url,'/store-api'))||($option==='dw_sw_shop_url'&&str_ends_with($url,'/store-api'))) throw new \InvalidArgumentException('Invalid endpoint type');
            return $url;
        } catch(\Throwable $error) {
            add_settings_error('dw_sw',$option,__('Enter a public HTTPS shop URL without credentials, query parameters or fragments. The optional API URL must end in /store-api.','connect-for-shopware'));
            return $previous;
        }
    }
    /**
     * Register the WordPress integration callbacks for this component.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function boot(): void {
        add_action('admin_init',static function(): void {
            foreach(['dw_sw_shop_url','dw_sw_api_url'] as $option) register_setting('dw_sw',$option,['type'=>'string','default'=>'','sanitize_callback'=>static fn($value)=>self::sanitize($value,$option)]);
        });
        foreach(['dw_sw_shop_url','dw_sw_api_url'] as $option) {
            add_action('update_option_'.$option,[self::class,'changed'],10,2);
            add_action('add_option_'.$option,static function(): void {self::changed('',true);});
        }
    }
    /**
     * Invalidate shop-scoped runtime data after an endpoint setting changes.
     *
     * @since 1.0.0
     * @param mixed $old Previously stored option value.
     * @param mixed $new Replacement option value.
     * @return void No return value; effects are described above.
     */
    public static function changed($old,$new): void {
        if($old===$new) return;
        (new TransientCache())->refresh();
        delete_option('dw_sw_connection_test');delete_option('dw_sw_diagnostics');
        /**
             * Notify site-scoped cache integrations after a manual refresh or shop change.
             *
             * @since 1.0.0
             */
            do_action('dw_sw_cache_refreshed');
    }
    /**
     * Render the shop URL and optional advanced API endpoint controls.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public static function fields(): void {
        $domain='connect-for-shopware';
        echo '<h2>'.esc_html__('Shop connection',$domain).'</h2><p><label for="dw-sw-shop-url">'.esc_html__('Shopware shop URL',$domain).'</label><br><input class="regular-text" type="url" id="dw-sw-shop-url" name="dw_sw_shop_url" value="'.esc_attr((string)get_option('dw_sw_shop_url','')).'" placeholder="https://shop.example.org"></p>';
        echo '<p>'.esc_html__('Enter the public storefront base URL, including an installation or language path when required. No shop is preconfigured.',$domain).'</p>';
        echo '<details class="dw-sw-technical"><summary>'.esc_html__('Advanced connection settings',$domain).'</summary><p><label for="dw-sw-api-url">'.esc_html__('Store API URL (optional)',$domain).'</label><br><input class="regular-text" type="url" id="dw-sw-api-url" name="dw_sw_api_url" value="'.esc_attr((string)get_option('dw_sw_api_url','')).'" placeholder="https://shop.example.org/store-api"></p>';
        echo '<p>'.esc_html__('Leave empty to append /store-api to the shop URL. Set this only when the API uses a different base path or host. HTTPS with the standard port is required; redirects are not followed.',$domain).'</p>';
        echo '</details><p class="description">'.esc_html__('Changing the shop clears the Connector cache and connection diagnostics. Existing product selections are retained; choose products from the new shop where necessary.',$domain).'</p>';
    }
}
