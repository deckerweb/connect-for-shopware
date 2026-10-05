<?php
/** Optional installation of bundled native Bricks components. */
declare(strict_types=1);
namespace Deckerweb\Shopware;
defined('ABSPATH') || exit;
final class Components {
    private const VERSION='1.1.0';
    public static function available(): bool {
        if(is_multisite() && defined('BRICKS_MULTISITE_USE_MAIN_SITE_COMPONENTS') && BRICKS_MULTISITE_USE_MAIN_SITE_COMPONENTS && get_current_blog_id()!==get_main_site_id()) return false;
        return defined('BRICKS_VERSION') && version_compare(BRICKS_VERSION,'2.4.2','>=') && class_exists('Bricks\\Component_Repository') && class_exists('Bricks\\Helpers') && defined('BRICKS_DB_TEMPLATE_SLUG');
    }
    public static function boot(): void {
        add_action('admin_post_dw_sw_components',static function(): void {
            if(!current_user_can('manage_options') || !current_user_can('edit_posts')) wp_die(esc_html__('Access denied.','connect-for-shopware'));
            check_admin_referer('dw_sw_components');
            $selection=isset($_POST['components'])&&is_array($_POST['components'])?array_map('sanitize_key',array_filter(wp_unslash($_POST['components']),'is_string')):[];
            $result=self::install($selection,!empty($_POST['examples']),!empty($_POST['copies']));
            set_transient('dw_sw_components_result_'.get_current_user_id(),$result,60);
            wp_safe_redirect(admin_url('options-general.php?page=dw-sw'));exit;
        });
    }
    private static function catalog(): array {
        $lang=str_starts_with(determine_locale(),'de')?'de':'en';
        $data=json_decode((string)file_get_contents(DW_SW_DIR.'components/components-'.$lang.'.json'),true);
        return is_array($data['components']??null)?$data['components']:[];
    }
    /** Local, explicit installation; never contacts Shopware or imports remote media. */
    public static function install(array $selection,bool $examples=false,bool $copies=false): array {
        $result=['installed'=>0,'skipped'=>0,'templates'=>0,'failed'=>false];
        if(!self::available()||!current_user_can('manage_options')||!current_user_can('edit_posts')||!\Bricks\Builder_Permissions::user_has_permission('create_components')||($examples&&!\Bricks\Builder_Permissions::user_has_permission('create_templates'))) {$result['failed']=true;return $result;}
        // Serialize our installers. Expired lock removal is conditional to preserve another owner.
        global $wpdb;
        $lock='dw_sw_components_lock';$token=wp_generate_uuid4();$value=['token'=>$token,'until'=>time()+60];
        if(!add_option($lock,$value,'',false)) {
            $old=get_option($lock);
            if(is_array($old)&&($old['until']??PHP_INT_MAX)<time()) {
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s",$lock,maybe_serialize($old)));wp_cache_delete($lock,'options');
            }
            if(!add_option($lock,$value,'',false)) {$result['failed']=true;return $result;}
        }
        $bricksLock=\Bricks\Component_Repository::acquire_lock();
        try {
            if(is_wp_error($bricksLock)) throw new \RuntimeException('Bricks import locked');
            $catalog=self::catalog();$all=\Bricks\Component_Repository::get_all();$known=array_column($all,null,'id');$reserved=[];foreach($all as $component) foreach($component['elements'] as $element) $reserved[$element['id']]=true;
            $registry=(array)get_option('dw_sw_component_copies',[]);$lang=str_starts_with(determine_locale(),'de')?'de':'en';
            foreach($catalog as $index=>$definition) {
                $original=$definition['id'];if(!in_array($original,$selection,true)) continue;
                $actual=$original;$map=[];$new=false;
                if(isset($known[$original])&&$copies) {
                    $key=$original.':'.self::VERSION;
                    if(isset($registry[$key],$known[$registry[$key]])) {$actual=$registry[$key];foreach($definition['elements'] as $position=>$element) {$map[$element['id']]=$known[$actual]['elements'][$position]['id'];}}
                    else {
                        foreach($definition['elements'] as $element) {
                            do {$id=substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'),0,6);} while(isset($reserved[$id])||in_array($id,$map,true));$reserved[$id]=true;
                            $map[$element['id']]=$id;
                        }
                        $actual=$map[$original];$registry[$key]=$actual;
                        $definition=self::remap($definition,$map);$definition['elements'][0]['label'].=' · v'.self::VERSION;
                    }
                }
                if(isset($known[$actual])) $result['skipped']++;
                else {
                    $upgraded=\Bricks\Components::upgrade_components([$definition],false);
                    $all[]=$upgraded[0];$known[$actual]=$upgraded[0];$new=true;$result['installed']++;
                }
                // Persist definitions before templates referencing them; never replace a known ID.
                if($new) {if(!\Bricks\Component_Repository::update_all($all)) throw new \RuntimeException('Component persistence failed');update_option('dw_sw_component_copies',$registry,false);\Bricks\Component_Repository::bump_design_system_version();}
                if(!$examples) continue;
                $marker='connect-for-shopware:'.$actual.':'.self::VERSION;
                $existing=get_posts(['post_type'=>BRICKS_DB_TEMPLATE_SLUG,'post_status'=>'any','numberposts'=>1,'meta_key'=>'_dw_sw_component_example','meta_value'=>$marker,'fields'=>'ids']);
                if($existing) continue;
                $filename=sprintf('%02d-%s.json',$index+1,$original);
                $template=json_decode((string)file_get_contents(DW_SW_DIR.'components/'.$lang.'/templates/'.$filename),true);
                if($map) $template=self::remap($template,$map);
                $elements=\Bricks\Helpers::generate_new_element_ids(\Bricks\Helpers::sanitize_bricks_data($template['content']));
                $post=wp_insert_post(['post_type'=>BRICKS_DB_TEMPLATE_SLUG,'post_status'=>current_user_can('publish_posts')?'publish':'pending','post_title'=>$known[$actual]['elements'][0]['label'],'meta_input'=>[BRICKS_DB_TEMPLATE_TYPE=>'section',BRICKS_DB_PAGE_CONTENT=>$elements,'_dw_sw_component_example'=>$marker]],true);
                if(is_wp_error($post)||!$post) {$result['failed']=true;continue;}
                // Bricks' metadata hooks may already generate this file. A second pass can remove it through selector deduplication.
                if(\Bricks\Database::get_setting('cssLoading')==='file' && !is_file(\Bricks\Assets::$css_dir.'/post-'.$post.'.min.css')) \Bricks\Assets_Files::generate_post_css_file($post,'content',$elements);
                $result['templates']++;
            }
        } catch(\Throwable $error) {$result['failed']=true;}
        finally {
            if(is_string($bricksLock)) \Bricks\Component_Repository::release_lock($bricksLock);
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s",$lock,maybe_serialize($value)));wp_cache_delete($lock,'options');
        }
        return $result;
    }
    private static function remap(array $data,array $map): array {
        $out=[];foreach($data as $key=>$value) {$key=is_string($key)?($map[$key]??$key):$key;$out[$key]=is_array($value)?self::remap($value,$map):(is_string($value)?($map[$value]??$value):$value);}return $out;
    }
    public static function panel(): void {
        if(!self::available()) return;
        $domain='connect-for-shopware';
        echo '<section><h2>'.esc_html__('Bricks Components',$domain).'</h2><p>'.esc_html__('Install selected reusable components. Existing definitions and customizations are preserved.',$domain).'</p>';
        $key='dw_sw_components_result_'.get_current_user_id();$result=get_transient($key);
        if(is_array($result)) {delete_transient($key);echo '<p role="status">'.esc_html(sprintf(__('Installed: %d · Existing: %d · Example templates: %d',$domain),$result['installed'],$result['skipped'],$result['templates'])).'</p>';if($result['failed']) echo '<p>'.esc_html__('Installation could not be completed. You can safely retry.',$domain).'</p>';}
        $known=array_column(\Bricks\Component_Repository::get_all(),null,'id');
        echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="dw_sw_components">';wp_nonce_field('dw_sw_components');
        foreach(self::catalog() as $component) echo '<p><label><input type="checkbox" name="components[]" value="'.esc_attr($component['id']).'"> '.esc_html($component['elements'][0]['label']).(isset($known[$component['id']])?' · '.esc_html__('Already installed',$domain):'').'</label></p>';
        echo '<p><label><input type="checkbox" name="examples" value="1"> '.esc_html__('Also install starter templates without product selections',$domain).'</label></p><p><label><input type="checkbox" name="copies" value="1"> '.esc_html__('Install this bundled version as an additional copy for existing components',$domain).'</label></p>';
        echo '<p>'.esc_html__('Starter templates have no shop-specific selections. Choose products and categories through the native element controls inside each slot.',$domain).'</p>';
        submit_button(__('Install selected components',$domain),'secondary');echo '</form></section>';
    }
}
