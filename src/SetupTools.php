<?php
/** Connection checklist and secret-free settings transfer with a mandatory preview. */
declare(strict_types=1);
namespace Deckerweb\Shopware;
defined('ABSPATH') || exit;

/** Keep connection setup explicit and bind each import approval to a site, user and settings snapshot. */
final class SetupTools {
    private const OPTIONS=['dw_sw_shop_url','dw_sw_api_url','dw_sw_cache_ttl','dw_sw_purge_pages'];
    /** Register administrator-only configuration transfer routes.
     * @return void Does not register any public or frontend transfer API.
     */
    public static function boot(): void {add_action('rest_api_init',[self::class,'routes']);}
    /** Restrict connection transfers to the current site's administrators.
     * @return bool Whether site configuration may be managed.
     */
    public static function permission(): bool {return current_user_can('manage_options');}
    /** Register export, preview and explicitly approved apply operations.
     * @return void Request bodies are bounded and validated before any write.
     */
    public static function routes(): void {
        register_rest_route('deckerweb-shopware/v1','/settings/export',['methods'=>'GET','permission_callback'=>[self::class,'permission'],'callback'=>[self::class,'export']]);
        foreach(['preview','apply'] as $action)register_rest_route('deckerweb-shopware/v1','/settings/'.$action,['methods'=>'POST','permission_callback'=>[self::class,'permission'],'callback'=>[self::class,$action]]);
    }
    /** Return the four transferable settings, never secrets or editorial associations.
     * @return array Explicitly allowlisted normalized option values.
     */
    public static function values(): array {
        return ['dw_sw_shop_url'=>(string)get_option('dw_sw_shop_url',''),'dw_sw_api_url'=>(string)get_option('dw_sw_api_url',''),
            'dw_sw_cache_ttl'=>max(900,min(3600,(int)get_option('dw_sw_cache_ttl',1800))),'dw_sw_purge_pages'=>(bool)get_option('dw_sw_purge_pages',true)];
    }
    /** Export connection addresses and cache preferences only.
     * @return \WP_REST_Response Downloadable data without keys, site identifiers or product selections.
     */
    public static function export(): \WP_REST_Response {
        return new \WP_REST_Response(['plugin'=>'connect-for-shopware','schema'=>1,'settings'=>self::values()],200,['Cache-Control'=>'no-store']);
    }
    /** Strictly validate a complete transfer bundle without contacting the destination shop.
     * @param mixed $input Decoded configuration bundle.
     * @return array Validated and normalized option values.
     * @throws \InvalidArgumentException For unknown fields, types or unsafe URLs.
     */
    public static function validate(mixed $input): array {
        if(!is_array($input)||array_diff(array_keys($input),['plugin','schema','settings'])||($input['plugin']??'')!=='connect-for-shopware'||($input['schema']??null)!==1||!is_array($input['settings']??null))throw new \InvalidArgumentException('Invalid bundle');
        $s=$input['settings'];$keys=array_keys($s);sort($keys);$expected=self::OPTIONS;sort($expected);
        if($keys!==$expected||!is_string($s['dw_sw_shop_url'])||!is_string($s['dw_sw_api_url'])||!is_int($s['dw_sw_cache_ttl'])||$s['dw_sw_cache_ttl']<900||$s['dw_sw_cache_ttl']>3600||!is_bool($s['dw_sw_purge_pages']))throw new \InvalidArgumentException('Invalid settings');
        foreach(['dw_sw_shop_url','dw_sw_api_url'] as $key){
            if(strlen($s[$key])>2048)throw new \InvalidArgumentException('Invalid URL length');
            if($s[$key]==='')continue;
            $s[$key]=ShopUrl::normalize($s[$key]);
            if(($key==='dw_sw_api_url'&&!str_ends_with($s[$key],'/store-api'))||($key==='dw_sw_shop_url'&&str_ends_with($s[$key],'/store-api')))throw new \InvalidArgumentException('Wrong URL kind');
        }
        return $s;
    }
    /** Read a bounded JSON request without persisting uploaded material.
     * @param \WP_REST_Request $request Authenticated JSON request.
     * @return array Parsed top-level request object.
     * @throws \InvalidArgumentException For oversized or invalid bodies.
     */
    private static function body($request): array {
        if(strlen($request->get_body())>16384)throw new \InvalidArgumentException('Body too large');
        $data=$request->get_json_params();if(!is_array($data))throw new \InvalidArgumentException('Invalid JSON');return $data;
    }
    /** Bind import consent to the proposed values and the unmodified current-site settings.
     * @param array $settings Proposed validated settings.
     * @param string $snapshot Current settings fingerprint from the preview.
     * @param int $expires Absolute preview expiry timestamp.
     * @return string HMAC bound to this user and site.
     */
    private static function signature(array $settings,string $snapshot,int $expires): string {
        $ordered=[];foreach(self::OPTIONS as $key)$ordered[$key]=$settings[$key];
        return hash_hmac('sha256',get_current_blog_id().'|'.get_current_user_id().'|'.$expires.'|'.$snapshot.'|'.wp_json_encode($ordered),wp_salt('auth'));
    }
    /** Preview validated changes without changing any WordPress option.
     * @param \WP_REST_Request $request Authenticated request containing a bundle.
     * @return \WP_REST_Response|\WP_Error Proposed differences and a short-lived approval token.
     */
    public static function preview($request) {
        try{
            $body=self::body($request);$settings=self::validate($body['bundle']??null);$current=self::values();$snapshot=hash('sha256',wp_json_encode($current));$expires=time()+600;$changes=[];
            foreach(self::OPTIONS as $key)$changes[]=['key'=>$key,'before'=>$current[$key],'after'=>$settings[$key],'changed'=>$current[$key]!==$settings[$key]];
            return new \WP_REST_Response(['bundle'=>['plugin'=>'connect-for-shopware','schema'=>1,'settings'=>$settings],'changes'=>$changes,'snapshot'=>$snapshot,'expires'=>$expires,'token'=>self::signature($settings,$snapshot,$expires)],200,['Cache-Control'=>'no-store']);
        }catch(\Throwable $error){return new \WP_Error('dw_sw_import',__('Invalid settings file. Only shop URLs and cache preferences are accepted; access keys and product assignments are excluded.','connect-for-shopware'),['status'=>400]);}
    }
    /** Apply exactly the configuration approved in a recent unchanged preview.
     * @param \WP_REST_Request $request Bundle and user/site-bound preview evidence.
     * @return \WP_REST_Response|\WP_Error Result; rejects tampering, expiry and changed settings before writing.
     */
    public static function apply($request) {
        try{
            $body=self::body($request);$settings=self::validate($body['bundle']??null);$snapshot=$body['snapshot']??'';$token=$body['token']??'';$expires=$body['expires']??0;
            if(!is_string($snapshot)||!is_string($token)||!is_int($expires)||$expires<time()||$expires>time()+600||!hash_equals(hash('sha256',wp_json_encode(self::values())),$snapshot)||!hash_equals(self::signature($settings,$snapshot,$expires),$token))throw new \InvalidArgumentException('Preview no longer valid');
            foreach(self::OPTIONS as $key)update_option($key,$settings[$key],false);
            return new \WP_REST_Response(['saved'=>true],200,['Cache-Control'=>'no-store']);
        }catch(\Throwable $error){return new \WP_Error('dw_sw_import_preview',__('The preview is no longer valid. Preview the file again before applying it.','connect-for-shopware'),['status'=>409]);}
    }
    /** Display a read-only checklist using the last explicit connection probe.
     * @return void No automatic external requests; key presence is exposed only as a boolean.
     */
    public static function checklist(): void {
        $test=(array)get_option('dw_sw_connection_test',[]);$hasUrl=false;
        try{$hasUrl=Configuration::shopUrl()!=='';}catch(\Throwable $ignored){}
        $checks=[__('Shop URL saved','connect-for-shopware')=>$hasUrl,__('Server-side key recognized','connect-for-shopware')=>Plugin::accessKey()!=='',__('Connection test successful','connect-for-shopware')=>!empty($test['ok'])];
        echo '<section class="dw-sw-panel"><h2>'.esc_html__('Setup checklist','connect-for-shopware').'</h2><ul class="dw-sw-checklist">';
        foreach($checks as $label=>$ok)echo '<li><strong>'.esc_html($ok?__('Complete','connect-for-shopware'):__('Not yet complete','connect-for-shopware')).'</strong> · '.esc_html($label).'</li>';
        echo '</ul><p>'.esc_html__('Use Test connection now below to check the shop. Verify the reported language, currency and tax context against the intended Sales Channel. This checklist is not live monitoring.','connect-for-shopware').'</p>';
        if(!empty($test['ok'])&&!empty($test['context'])){$c=$test['context'];echo '<p>'.esc_html(implode(' · ',[(string)($c['locale']??''),(string)($c['currency']??''),(string)($c['taxState']??'')])).'</p>';}
        echo '</section>';
    }
    /** Render the explicit export/import workflow below the main settings.
     * @return void Import remains disabled until a successful preview is shown.
     */
    public static function panel(): void {
        echo '<section class="dw-sw-panel dw-sw-transfer"><h2>'.esc_html__('Transfer connection settings','connect-for-shopware').'</h2><p>'.esc_html__('Export shop URLs and cache preferences. Access keys, product assignments, installed Components and Library settings are never included.','connect-for-shopware').'</p><button type="button" class="button" data-dw-sw-export>'.esc_html__('Download settings','connect-for-shopware').'</button>';
        echo '<p><label for="dw-sw-import">'.esc_html__('Paste exported settings JSON','connect-for-shopware').'</label></p><textarea id="dw-sw-import" rows="5" maxlength="16384" class="large-text code" spellcheck="false"></textarea><p><button type="button" class="button" data-dw-sw-preview>'.esc_html__('Preview changes','connect-for-shopware').'</button> <button type="button" class="button button-primary" data-dw-sw-apply disabled>'.esc_html__('Apply previewed settings','connect-for-shopware').'</button></p><div class="dw-sw-transfer-status" role="status" aria-live="polite"></div><div class="dw-sw-transfer-preview"></div><noscript>'.esc_html__('JavaScript is required for settings transfer. The normal settings form remains available.','connect-for-shopware').'</noscript></section>';
    }
}
