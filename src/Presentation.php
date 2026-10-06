<?php
declare(strict_types=1);
namespace Deckerweb\Shopware;
/**
 * Normalize product selections and shared Gutenberg/Bricks display controls.
 */
final class Presentation {
    public const FLAGS = ['showImage','showTitle','showText','showPrice','showListPrice','showAvailability','showVariant','showButton','showTabs','showDescriptionTab','showDocumentsTab','showManufacturerTab','showQuickView'];
    /**
     * Return conservative shared product display defaults.
     *
     * @since 1.0.0
     * @return array Normalized result data for the documented operation.
     */
    public static function defaults(): array {
        return ['showImage'=>true,'showTitle'=>true,'showText'=>false,'showPrice'=>true,
            'showListPrice'=>true,'showAvailability'=>true,'showVariant'=>true,'showButton'=>true,
            'showQuickView'=>false,'showTabs'=>false,'showDescriptionTab'=>true,'showDocumentsTab'=>true,'showManufacturerTab'=>true,
            'infoPosition'=>'right','imageMode'=>'cover','buttonText'=>'','buttonUrl'=>'','textMode'=>'summary','customText'=>'','linkStyle'=>'link','linkTarget'=>'same','headingTag'=>'h3'];
    }
    /**
     * Return reusable compact, description and details presentation presets.
     *
     * @since 1.0.0
     * @return array Normalized result data for the documented operation.
     */
    public static function presets(): array {
        return ['compact'=>['showText'=>false,'showTabs'=>false,'infoPosition'=>'below','imageMode'=>'cover','linkStyle'=>'button'],
            'description'=>['showText'=>true,'textMode'=>'description','showTabs'=>false,'infoPosition'=>'right','imageMode'=>'cover'],
            'details'=>['showText'=>false,'showTabs'=>true,'infoPosition'=>'right','imageMode'=>'gallery','linkStyle'=>'button']];
    }
    /**
     * Validate and normalize display controls from blocks or builder instances.
     *
     * @since 1.0.0
     * @param array $input Untrusted presentation controls to normalize.
     * @return array Normalized result data for the documented operation.
     */
    public static function settings(array $input): array {
        $input=array_replace(self::presets()[$input['preset']??'']??[],$input);
        $settings=self::defaults();
        foreach(self::FLAGS as $key) {
            if(array_key_exists($key,$input)) $settings[$key]=filter_var($input[$key],FILTER_VALIDATE_BOOLEAN);
        }
        $settings['buttonText']=sanitize_text_field((string)($input['buttonText']??''));
        $settings['buttonUrl']=esc_url_raw((string)($input['buttonUrl']??''),['https','http']);
        $settings['textMode']=in_array($input['textMode']??'', ['summary','description','custom'],true)?$input['textMode']:'summary';
        $settings['customText']=sanitize_textarea_field((string)($input['customText']??''));
        $settings['linkStyle']=($input['linkStyle']??'')==='button'?'button':'link';
        foreach(['infoPosition'=>['left','right','below'],'imageMode'=>['cover','gallery']] as $key=>$values) {
            $settings[$key]=in_array($input[$key]??'', $values,true)?$input[$key]:$settings[$key];
        }
        $settings['linkTarget']=($input['linkTarget']??'')==='new'?'new':'same';
        $tag=$input['headingTag']??'h3';
        $settings['headingTag']=in_array($tag,['h2','h3','h4','h5','h6','p'],true)?$tag:'h3';
        return $settings;
    }
    /**
     * Decode a family/variant selection reference without accepting arbitrary IDs.
     *
     * @since 1.0.0
     * @param string $value Encoded family:UUID or variant:UUID reference.
     * @return array Normalized result data for the documented operation.
     */
    public static function reference(string $value): array {
        if(!preg_match('/^(family|variant):([a-f0-9]{32})$/D',$value,$m)) throw new \InvalidArgumentException('Invalid reference');
        return ['productId'=>$m[2],'selectionMode'=>$m[1]];
    }
    /**
     * Normalize ordered article product assignments and remove invalid duplicates.
     *
     * @since 1.0.0
     * @param mixed $items Ordered product assignments supplied by metadata or editor controls.
     * @return array Normalized result data for the documented operation.
     */
    public static function associations(mixed $items): array {
        $result=[];
        foreach(is_array($items)?array_slice($items,0,50):[] as $item) {
            if(!is_array($item)) continue;
            $id=$item['productId']??'';$mode=$item['selectionMode']??'';
            if(!is_string($id)||!preg_match('/^[a-f0-9]{32}$/D',$id)||!in_array($mode,['family','variant'],true)) continue;
            $result[$mode.':'.$id]=['productId'=>$id,'selectionMode'=>$mode];
        }
        return array_values($result);
    }
}
/**
 * Render escaped product cards, galleries, details and shop actions.
 */
final class ProductRenderer {
    /**
     * Format a supplied Shopware amount using the context currency and precision.
     *
     * @since 1.0.0
     * @param float $amount Calculated Shopware amount to format without recalculation.
     * @param array $product Mapped product with currency, precision and tax context.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    private function money(float $amount,array $product): string {
        $decimals=max(0,min(8,(int)($product['currencyDecimals']??2)));
        $symbol=(string)($product['currencySymbol']??$product['currency']??'');
        return number_format_i18n($amount,$decimals).' '.$symbol;
    }

    /**
     * Render a product projection with safe media, calculated prices and selected controls.
     *
     * @since 1.0.0
     * @param array $p Mapped product projection from the shared repository.
     * @param array $input Untrusted presentation controls to normalize.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public function render(array $p,array $input=[]): string {
        wp_enqueue_style('dw-sw-product');
        $s=Presentation::settings($input);
        if(!empty($p['_stale'])) {$s['showPrice']=false;$s['showAvailability']=false;}
        $html='';$media='';$text='';$domain='connect-for-shopware';
        if($s['showImage']&&!empty($p['images'])) {
            $items=$s['imageMode']==='gallery'?($p['gallery']??[]):[['type'=>'image']+$p['images'][0]];
            if(!$items) $items=array_map(static fn($m)=>['type'=>'image']+$m,$p['images']);
            $media='<div class="dw-sw-media">';
            $uid='dw-sw-gallery-'.wp_generate_uuid4().'-';
            foreach($items as $i=>$m) {
                $id=esc_attr($uid.$i);
                if($m['type']==='video') {
                    $media.='<div id="'.$id.'" data-gallery-item class="dw-sw-video">';
                    if(!empty($m['poster'])) $media.='<img class="dw-sw-image" loading="lazy" src="'.esc_url($m['poster']).'" alt="'.esc_attr($m['alt']).'">';
                    $media.='<p><a href="'.esc_url($m['url']).'" target="_blank" rel="noopener noreferrer">'.esc_html(sprintf(__('Watch video on %s',$domain),$m['provider'])).'</a></p></div>';
                } else $media.='<img id="'.$id.'" data-gallery-item class="dw-sw-image" loading="lazy" decoding="async" src="'.esc_url($m['url']).'" alt="'.esc_attr($m['alt']).'">';
            }
            if(count($items)>1) {
                $media.='<div class="dw-sw-thumbnails">';
                foreach($items as $i=>$m) {
                    $video=$m['type']==='video';$thumb=$video?($m['poster']??''):$m['url'];
                    $label=$video?__('Show video',$domain):sprintf(__('Show image %d',$domain),$i+1);
                    $media.='<a href="#'.esc_attr($uid.$i).'" data-gallery-index="'.$i.'" aria-label="'.esc_attr($label).'">';
                    if($thumb!=='') $media.='<img loading="lazy" src="'.esc_url($thumb).'" alt="">';
                    if($video) $media.='<span class="dw-sw-video-label">'.esc_html__('Video',$domain).'</span>';
                    $media.='</a>';
                }
                $media.='</div>';
            }
            $media.='</div>';
        }
        if($s['showTitle']) $html.='<'.$s['headingTag'].' class="dw-sw-title">'.esc_html($p['title']).'</'.$s['headingTag'].'>';
        if($s['showText']) $text.='<div class="dw-sw-text">'.($s['textMode']==='custom'?nl2br(esc_html($s['customText'])):($s['textMode']==='description'?wp_kses_post($p['descriptionHtml']):esc_html($p['summary']))).'</div>';
        if($s['showVariant']&&$p['variantText']!=='') $html.='<p class="dw-sw-variant">'.esc_html($p['variantText']).'</p>';
        $price=$p['displayPrice'];
        if($s['showPrice']&&isset($price['unitPrice'])) {
            $label=$p['isFamily']?__('From',$domain).' ':'';
            $html.='<p class="dw-sw-price">'.esc_html($label.$this->money((float)$price['unitPrice'],$p));
            if($s['showListPrice']&&isset($price['listPrice']['price'])) {
                $html.=' <del class="dw-sw-list-price" aria-label="'.esc_attr__('List price',$domain).'">'.esc_html($this->money((float)$price['listPrice']['price'],$p)).'</del>';
                if(isset($price['listPrice']['percentage'])) $html.=' <span class="dw-sw-discount">'.esc_html(number_format_i18n((float)$price['listPrice']['percentage'],2).' % '.__('discount',$domain)).'</span>';
            }
            $tax=match($p['taxState']??'') {'gross'=>__('Including VAT; shipping costs apply in the shop.',$domain),'net'=>__('Excluding VAT; shipping costs apply in the shop.',$domain),'free'=>__('Tax-free; shipping costs apply in the shop.',$domain),default=>__('Shipping costs apply in the shop.',$domain)};
            $html.='</p><p class="dw-sw-tax">'.esc_html($tax).'</p>';
            if(isset($price['referencePrice']['price'])) {
                $r=$price['referencePrice'];
                $html.='<p class="dw-sw-reference-price">'.esc_html($this->money((float)$r['price'],$p).' / '.$r['referenceUnit'].' '.$r['unitName']).'</p>';
            }
        }
        if($s['showAvailability']&&$p['available']!==null) {
            $html.='<p class="dw-sw-availability">'.esc_html($p['available']?__('Available',$domain):__('Currently unavailable',$domain));
            if($p['available']&&$p['deliveryTime']!=='') $html.=' · '.esc_html($p['deliveryTime']);
            $html.='</p>';
        }
        if($s['showButton']) $html.=$this->button($p,$input);
        if($s['showQuickView']) $html.=QuickView::trigger($p);
        if(!empty($p['_stale'])) $html.='<p class="dw-sw-status-notice">'.esc_html__('Please check current prices and availability in the shop.','connect-for-shopware').'</p>';
        $html='<div class="dw-sw-overview dw-sw-info-'.esc_attr($s['infoPosition']).($media===''?' dw-sw-no-media':'').'">'.$media.'<div class="dw-sw-info">'.$html.'</div></div>'.$text;
        if($s['showTabs']) $html.=$this->tabs($p,$s);
        wp_enqueue_style('dw-sw-product');
        wp_enqueue_script('dw-sw-product');
        return $html;
    }

    /**
     * Render the selected text link or native-style button with safe target relations.
     *
     * @since 1.0.0
     * @param array $p Mapped product or category shop-link projection.
     * @param array $input Untrusted presentation controls to normalize.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    public function button(array $p,array $input=[]): string {
        wp_enqueue_style('dw-sw-product');
        $s=Presentation::settings($input);$button=$s['linkStyle']==='button';$bricks=($input['_builder']??'')==='bricks';
        $classes='dw-sw-button '.($button?($bricks?'bricks-button':'wp-block-button__link wp-element-button dw-sw-theme-button'):'dw-sw-text-link');
        $target=$s['linkTarget']==='new'?' target="_blank" rel="noopener noreferrer"':'';
        $link='<a'.$target.' class="'.esc_attr($classes).'" href="'.esc_url($s['buttonUrl']?:$p['url']).'">'.esc_html($s['buttonText']?:__('View product','connect-for-shopware')).'</a>';
        if($button&&!$bricks) {
            wp_enqueue_style('wp-block-buttons');wp_enqueue_style('wp-block-button');
            $link='<div class="wp-block-buttons"><div class="wp-block-button">'.$link.'</div></div>';
        }
        wp_enqueue_style('dw-sw-product');return $link;
    }

    /**
     * Render available description, PDF and manufacturer panels with unique IDs.
     *
     * @since 1.0.0
     * @param array $p Mapped product containing available details/documents/manufacturer.
     * @param array $s Normalized shared presentation controls.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    private function tabs(array $p,array $s): string {
        $domain='connect-for-shopware';$tabs=[];
        if($s['showDescriptionTab']&&!empty($p['descriptionHtml'])) $tabs[__('Description',$domain)]=wp_kses_post($p['descriptionHtml']);
        if($s['showDocumentsTab']&&!empty($p['documents'])) {
            $list='<ul class="dw-sw-documents">';
            foreach($p['documents'] as $d) $list.='<li><a href="'.esc_url($d['url']).'">'.esc_html($d['title']).'</a> <span>(PDF)</span></li>';
            $tabs[__('Data sheets',$domain)]=$list.'</ul>';
        }
        $m=$p['manufacturer']??[];
        if($s['showManufacturerTab']&&!empty($m['name'])) {
            $content='';
            if(!empty($m['logo'])) $content.='<img class="dw-sw-manufacturer-logo" loading="lazy" src="'.esc_url($m['logo']).'" alt="'.esc_attr($m['name']).'">';
            $content.='<h3>'.esc_html($m['name']).'</h3>'.wp_kses_post($m['descriptionHtml']??'');
            if(!empty($m['url'])) $content.='<p><a href="'.esc_url($m['url']).'">'.esc_html__('Manufacturer website',$domain).'</a></p>';
            $tabs[__('Manufacturer',$domain)]=$content;
        }
        if(!$tabs) return '';
        $uid='dw-sw-tabs-'.wp_generate_uuid4().'-';$out='<div class="dw-sw-tabs"><div class="dw-sw-tablist" aria-label="'.esc_attr__('Product details',$domain).'">';$i=0;
        foreach($tabs as $label=>$content) $out.='<a id="'.esc_attr($uid.'tab-'.$i).'" href="#'.esc_attr($uid.'panel-'.$i++).'">'.esc_html($label).'</a>';
        $out.='</div>';$i=0;
        foreach($tabs as $label=>$content) $out.='<section id="'.esc_attr($uid.'panel-'.$i).'" class="dw-sw-tabpanel" aria-labelledby="'.esc_attr($uid.'tab-'.$i++).'"><h3 class="dw-sw-tab-heading">'.esc_html($label).'</h3>'.$content.'</section>';
        return $out.'</div>';
    }

}
