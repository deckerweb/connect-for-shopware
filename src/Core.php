<?php
/** Shared read-only Shopware core. */
declare(strict_types=1);
namespace Deckerweb\Shopware;

/**
 * Expose read-only catalog operations independently of WordPress transport.
 */
interface ReadClient {
    /**
     * Read an allowlisted Store API route with optional read-only criteria.
     *
     * @since 1.0.0
     * @param string $route Allowlisted Store API resource route.
     * @param array|null $criteria Optional read criteria; never an administrative write payload.
     * @return array Normalized result data for the documented operation.
     */
    public function read(string $route, ?array $criteria = null): array; }
/**
 * Define reusable product data storage and explicit invalidation.
 */
interface Cache {
    /**
     * Read reusable product data, returning null on a cache miss.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return ?array Result data, or null when no cached value exists.
     */
    public function get(string $key): ?array;
    /**
     * Persist product data for the requested lifetime and retain an outage backup.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @param array $value Mapped catalog value to retain.
     * @param int $ttl Reusable data lifetime in seconds.
     * @return void No return value; effects are described above.
     */
    public function set(string $key, array $value, int $ttl): void;
    /**
     * Invalidate current cache generation without altering catalog assignments.
     *
     * @since 1.0.0
     * @return void No return value; effects are described above.
     */
    public function refresh(): void;
}

/**
 * Signal a temporary transport, budget or upstream failure.
 */
final class ShopUnavailable extends \RuntimeException {}
/**
 * Signal an explicitly missing resource that must not retain stale output.
 */
final class ResourceMissing extends \RuntimeException {}
/**
 * Extend cache storage with outage backups and regeneration ownership.
 */
interface RecoverableCache extends Cache {
    /**
     * Read retained content intended for a temporary shop outage.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return ?array Result data, or null when no cached value exists.
     */
    public function stale(string $key): ?array;
    /**
     * Remove a backup when Shopware explicitly reports the resource missing.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return void No return value; effects are described above.
     */
    public function forgetStale(string $key): void;
    /**
     * Atomically claim regeneration ownership for one cache key.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return bool Whether the operation is allowed or successfully completed.
     */
    public function acquire(string $key): bool;
    /**
     * Release only the regeneration lock owned by this instance.
     *
     * @since 1.0.0
     * @param string $key Connector-owned cache or operation identifier.
     * @return void No return value; effects are described above.
     */
    public function release(string $key): void;
}

/**
 * Normalize and validate public HTTPS storefront and API endpoints.
 */
final class ShopUrl {
    /**
     * Reject unsupported or nonpublic endpoints and return a normalized HTTPS URL.
     *
     * @since 1.0.0
     * @param string $url Public HTTPS endpoint or repository metadata URL.
     * @return string Validated string, label or escaped HTML for the documented operation.
     * @throws \InvalidArgumentException When the endpoint, identifier, route or lifetime is unsupported.
     */
    public static function normalize(string $url): string {
        $url=trim($url);$parts=parse_url($url);
        if(!is_array($parts)||strtolower($parts['scheme']??'')!=='https'||empty($parts['host'])||isset($parts['user'],$parts['pass'])||isset($parts['user'])||isset($parts['pass'])||isset($parts['query'])||isset($parts['fragment'])||(isset($parts['port'])&&$parts['port']!==443)) throw new \InvalidArgumentException('Invalid shop URL');
        $host=strtolower($parts['host']);
        if(!preg_match('/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/D',$host)||str_ends_with($host,'.local')||str_ends_with($host,'.localhost')||str_ends_with($host,'.internal')||str_ends_with($host,'.test')||str_ends_with($host,'.invalid')||str_ends_with($host,'.example')||filter_var($host,FILTER_VALIDATE_IP)) throw new \InvalidArgumentException('Invalid shop host');
        $path=$parts['path']??'';
        if(preg_match('~(?:^|/)\.\.?(?:/|$)~',rawurldecode($path))) throw new \InvalidArgumentException('Invalid shop path');
        if(preg_match('/[\x00-\x20\x7f\\\\]/',$url)||preg_match('~(?:^|/)(?:\.\.?|%2e(?:%2e)?)(?:/|$)~i',$path)||preg_match('/%(?:2f|5c|00|0a|0d)/i',$path)) throw new \InvalidArgumentException('Invalid shop path');
        return 'https://'.$host.rtrim($path,'/');
    }
}
/**
 * Read anonymous Store API data through a bounded transport callable.
 */
final class StoreApiClient implements ReadClient {
    private $transport;
    private string $token='';
    private ?array $session=null;
    private string $apiBase;
    /**
     * Initialize the validated dependencies used by this component.
     *
     * @since 1.0.0
     * @param string $accessKey Server-side Sales Channel credential; never output or logged.
     * @param callable $transport Callable accepting method, URL, headers and serialized read criteria.
     * @param string $shopUrl Public storefront base URL.
     * @param string $apiUrl Optional complete Store API base URL.
     * @return void No return value; effects are described above.
     */
    public function __construct(private string $accessKey, callable $transport,string $shopUrl,string $apiUrl='') {
        $this->transport=$transport;
        $this->apiBase=$apiUrl!==''?ShopUrl::normalize($apiUrl):ShopUrl::normalize($shopUrl).'/store-api';
    }
    /**
     * Return the lazy anonymous Sales Channel currency, language and tax context.
     *
     * @since 1.0.0
     * @return array Normalized result data for the documented operation.
     */
    public function context(): array {
        if($this->session===null) $this->read('context');
        return $this->session;
    }
    /**
     * Resolve an allowlisted anonymous Store API read and validate its response.
     *
     * @since 1.0.0
     * @param string $route Allowlisted Store API resource route.
     * @param array|null $criteria Optional read criteria; never an administrative write payload.
     * @return array Normalized result data for the documented operation.
     * @throws \InvalidArgumentException When the endpoint, identifier, route or lifetime is unsupported.
     */
    public function read(string $route, ?array $criteria=null): array {
        if(!preg_match('~^(context|media|category|product-listing/[a-f0-9]{32}|search|product(?:/[a-f0-9]{32})?)$~D',$route)) throw new \InvalidArgumentException('Read route not allowed');
        if($route==='context'&&$criteria!==null) throw new \InvalidArgumentException('Context mutation forbidden');
        if($route!=='context') $this->context();
        $headers=['sw-access-key'=>$this->accessKey,'Accept'=>'application/json','Content-Type'=>'application/json','sw-include-seo-urls'=>'true'];
        if($this->token!=='') $headers['sw-context-token']=$this->token;
        $data=($this->transport)($criteria===null?'GET':'POST',$this->apiBase.'/'.$route,$headers,$criteria===null?null:json_encode($criteria,JSON_THROW_ON_ERROR));
        if($route==='context') {
            $currency=$data['currency']??[];$tax=$data['context']['taxState']??$data['taxState']??'';
            $channel=$data['salesChannel']['id']??'';$token=$data['token']??'';
            if(!empty($data['customer'])||!is_string($token)||$token===''||!preg_match('/^[a-f0-9]{32}$/D',(string)$channel)||!preg_match('/^[A-Z]{3}$/D',(string)($currency['isoCode']??''))||!in_array($tax,['gross','net','free'],true)) throw new ShopUnavailable('response');
            $language=$data['context']['languageIdChain'][0]??$data['languageId']??$data['salesChannel']['languageId']??'';
            $this->session=['salesChannelId'=>$channel,'languageId'=>preg_match('/^[a-f0-9]{32}$/D',(string)$language)?$language:'','locale'=>(string)($data['languageInfo']['localeCode']??''),'currency'=>$currency['isoCode'],'currencySymbol'=>(string)($currency['symbol']??$currency['isoCode']),'currencyDecimals'=>max(0,min(8,(int)($currency['itemRounding']['decimals']??2))),'taxState'=>$tax];
            $this->token=$token;
        }
        return $data;
    }
}

/**
 * Map Sales Channel entities to a frontend-neutral product projection.
 */
final class ProductMapper {
    private $contextProvider;
    private string $shopUrl;
    /**
     * Initialize the validated dependencies used by this component.
     *
     * @since 1.0.0
     * @param string $shopUrl Public storefront base URL.
     * @param callable $contextProvider Callable returning the anonymous Sales Channel formatting context.
     * @return void No return value; effects are described above.
     */
    public function __construct(string $shopUrl,callable $contextProvider) {$this->shopUrl=ShopUrl::normalize($shopUrl);$this->contextProvider=$contextProvider;}

    /**
     * Normalize available manufacturer data and safe links.
     *
     * @since 1.0.0
     * @param array $m Manufacturer entity from the Sales Channel response.
     * @return array Normalized result data for the documented operation.
     */
    private function manufacturer(array $m): array {
        $t=$m['translated']??[];
        return ['name'=>$t['name']??$m['name']??'', 'descriptionHtml'=>$t['description']??$m['description']??'',
            'url'=>$t['link']??$m['link']??'', 'logo'=>$m['media']['url']??''];
    }
    /**
     * Map inherited product fields, calculated prices, media and context-matching SEO URLs.
     *
     * @since 1.0.0
     * @param array $p Sales Channel product entity including inherited fields.
     * @return array Normalized result data for the documented operation.
     */
    public function map(array $p): array {
        $context=($this->contextProvider)();
        $translated = $p['translated'] ?? [];
        $options = array_map(static function(array $o): array {
            return ['id' => $o['id'], 'name' => $o['translated']['name'] ?? $o['name'] ?? '',
                'groupId' => $o['groupId'],
                'group' => $o['group']['translated']['name'] ?? $o['group']['name'] ?? ''];
        }, $p['options'] ?? []);
        $seoPath = null;
        foreach ($p['seoUrls'] ?? [] as $seo) {
            if (($seo['isCanonical'] ?? false) && !($seo['isDeleted'] ?? false)
                && ($seo['routeName'] ?? '') === 'frontend.detail.page'
                && ($seo['foreignKey'] ?? '') === $p['id']
                && ($seo['salesChannelId'] ?? '') === ($context['salesChannelId']??'')
                && ($seo['languageId'] ?? '') === ($context['languageId']??'') && !empty($context['languageId'])) {
                $seoPath = $seo['seoPathInfo'] ?? null;
                if ($seoPath) break;
            }
        }
        // Encode path segments and keep all links on the configured shop origin.
        $path = $seoPath ? implode('/', array_map('rawurlencode', explode('/', trim($seoPath, '/'))))
            : 'detail/' . $p['id'];
        $images = [];
        foreach (array_merge([$p['cover'] ?? []], $p['media'] ?? []) as $item) {
            $m = $item['media'] ?? [];
            if (empty($m['url']) || strpos($m['mimeType'] ?? '', 'image/') !== 0) continue;
            $images[$m['id']] = ['id' => $m['id'], 'url' => $m['url'],
                'alt' => $m['translated']['alt'] ?? $m['alt'] ?? $translated['name'] ?? '',
                'thumbnails' => $m['thumbnails'] ?? []];
        }
        $gallery=[];
        foreach($images as $image) $gallery[]=['type'=>'image']+$image;
        // The installed Shopware video extension supplies its storefront gallery here.
        $combined=$p['extensions']['solidPvCombinedMedia']['combinedMedia']??[];
        foreach(is_array($combined)?$combined:[] as $item) {
            if(!is_array($item)) continue;
            $m=$item['media']??[];
            if(!empty($m['id'])&&!isset($images[$m['id']])&&!empty($m['url'])&&str_starts_with($m['mimeType']??'','image/')) {
                $image=['id'=>$m['id'],'url'=>$m['url'],'alt'=>$m['translated']['alt']??$m['alt']??$translated['name']??'','thumbnails'=>$m['thumbnails']??[]];
                $images[$m['id']]=$image;$gallery[]=['type'=>'image']+$image;
            }
            $id=$item['videoId']??'';$source=$item['source']??'';
            $url='';
            if($source==='youtube'&&is_string($id)&&preg_match('/^[a-zA-Z0-9_-]{11}$/D',$id)) $url='https://www.youtube.com/watch?v='.$id;
            if($source==='vimeo'&&is_string($id)&&preg_match('/^[0-9]+$/D',$id)) $url='https://vimeo.com/'.$id;
            if($url) $gallery[]=['type'=>'video','url'=>$url,'provider'=>$source==='youtube'?'YouTube':'Vimeo',
                'poster'=>$item['thumbnailMedia']['url']??'','alt'=>$translated['name']??''];
        }
        $family = empty($p['parentId']) && ($p['childCount'] ?? 0) > 0;
        return ['id' => $p['id'], 'parentId' => $p['parentId'] ?? null,
            'productNumber' => $p['productNumber'] ?? '',
            'title' => $translated['name'] ?? $p['name'] ?? '',
            'descriptionHtml' => $translated['description'] ?? $p['description'] ?? '',
            // metaDescription is an SEO summary, not a dedicated short-description field.
            'summary' => $translated['metaDescription'] ?? $p['metaDescription'] ?? '',
            'images' => array_values($images), 'gallery'=>$gallery, 'options' => $options,
            'manufacturer' => $this->manufacturer($p['manufacturer'] ?? []),
            'documents' => $p['_documents'] ?? [],
            'variantText' => implode(' · ', array_column($options, 'name')),
            'isFamily' => $family, 'childCount' => $p['childCount'] ?? 0,
            'price' => $p['calculatedPrice'] ?? null,
            'tierPrices' => $p['calculatedPrices'] ?? [],
            'cheapestPrice' => $p['calculatedCheapestPrice'] ?? null,
            // Never substitute a family cheapest price for a concrete variant price.
            'displayPrice' => $family ? ($p['calculatedCheapestPrice'] ?? null) : ($p['calculatedPrice'] ?? null),
            'priceLabel' => $family ? 'ab' : '',
            'available' => $p['available'] ?? null,
            'deliveryTime' => $p['deliveryTime']['translated']['name'] ?? $p['deliveryTime']['name'] ?? '',
            'url' => $this->shopUrl.'/' . $path,
            'urlSource' => $seoPath ? 'seo' : 'technical',
            'currency'=>$context['currency'],'currencySymbol'=>$context['currencySymbol'],'currencyDecimals'=>$context['currencyDecimals'],'taxState'=>$context['taxState'],'locale'=>$context['locale']];
    }
}


/**
 * Resolve catalog selections with context-scoped cache and safe outage fallback.
 */
final class ProductRepository {

    /**
     * Initialize the validated dependencies used by this component.
     *
     * @since 1.0.0
     * @param ReadClient $client Read-only Store API client.
     * @param Cache $cache Reusable and optionally recoverable cache implementation.
     * @param ProductMapper $mapper Frontend-neutral product field mapper.
     * @param string $contextFingerprint Anonymous shop/credential cache namespace fingerprint.
     * @param int $ttl Reusable data lifetime in seconds.
     * @return void No return value; effects are described above.
     */
    public function __construct(private ReadClient $client, private Cache $cache,
        private ProductMapper $mapper, private string $contextFingerprint, private int $ttl = 1800) {
        if ($ttl < 900 || $ttl > 3600) throw new \InvalidArgumentException('TTL must be 15–60 minutes');
    }

    /**
     * Define the catalog associations needed by the shared product projection.
     *
     * @since 1.0.0
     * @return array Normalized result data for the documented operation.
     */
    public static function associations(): array {
        return ['cover' => ['associations' => ['media' => new \stdClass()]],
            'media' => ['associations' => ['media' => new \stdClass()]],
            'options' => ['associations' => ['group' => new \stdClass()]],
            'manufacturer' => ['associations' => ['media' => new \stdClass()]],
            'seoUrls' => new \stdClass(), 'unit' => new \stdClass(), 'deliveryTime' => new \stdClass()];
    }

    /**
     * Reject product or category identifiers outside the Shopware UUID format.
     *
     * @since 1.0.0
     * @param string $id Shopware product or category UUID.
     * @return void No return value; effects are described above.
     * @throws \InvalidArgumentException When the endpoint, identifier, route or lifetime is unsupported.
     */
    private function uuid(string $id): void {
        if (!preg_match('/^[a-f0-9]{32}$/D', $id)) throw new \InvalidArgumentException('Invalid product ID');
    }

    /**
     * Scope a cache operation to the anonymous shop/context fingerprint.
     *
     * @since 1.0.0
     * @param string $operation Cache operation name and read criteria fingerprint.
     * @return string Validated string, label or escaped HTML for the documented operation.
     */
    private function cacheKey(string $operation): string {
        return hash('sha256','mapping-v5|'.$this->contextFingerprint.'|'.$operation);
    }

    /**
     * Read a cached product without making a transport request.
     *
     * @since 1.0.0
     * @param string $id Shopware product or category UUID.
     * @param string $mode Selection mode: family or variant.
     * @return ?array Result data, or null when no cached value exists.
     */
    public function cachedProduct(string $id,string $mode='variant'): ?array {
        $this->uuid($id);
        if(!in_array($mode,['family','variant'],true)) throw new \InvalidArgumentException('Invalid mode');
        return $this->cache->get($this->cacheKey("product|$mode|$id"));
    }

    /**
     * Remove expired prices and availability from retained product content.
     *
     * @since 1.0.0
     * @param array $value Retained product content with potentially expired price data.
     * @return array Normalized result data for the documented operation.
     */
    public static function safeFallback(array $value): array {
        $value['_stale']=true;
        if(isset($value['items'])) $value['items']=array_map([self::class,'safeFallback'],$value['items']);
        if(isset($value['displayPrice'])||array_key_exists('price',$value)) {
            foreach(['price','displayPrice','cheapestPrice'] as $key) $value[$key]=null;
            $value['tierPrices']=[];$value['available']=null;$value['deliveryTime']='';
        }
        return $value;
    }

    /**
     * Resolve cache data under a regeneration lock and apply safe outage fallback.
     *
     * @since 1.0.0
     * @param string $operation Cache operation name and read criteria fingerprint.
     * @param callable $load Callable loading fresh data on a cache miss.
     * @return array Normalized result data for the documented operation.
     */
    private function cached(string $operation,callable $load): array {
        $key=$this->cacheKey($operation);$hit=$this->cache->get($key);
        if($hit!==null) return $hit;
        $recover=$this->cache instanceof RecoverableCache;
        $fallbackAllowed=str_starts_with($operation,'product|')||str_starts_with($operation,'listing|');
        if($recover&&!$this->cache->acquire($key)) {
            $old=$fallbackAllowed?$this->cache->stale($key):null;
            if($old!==null) return self::safeFallback($old);
            throw new ShopUnavailable('busy');
        }
        try {
            // A previous worker may have finished between the first read and our lock.
            if($recover&&($hit=$this->cache->get($key))!==null) return $hit;
            $value=$load();
            $this->cache->set($key,$value,$this->ttl);
            return $value;
        } catch(ShopUnavailable $e) {
            $old=$recover&&$fallbackAllowed?$this->cache->stale($key):null;
            if($old!==null) return self::safeFallback($old);
            throw $e;
        } catch(ResourceMissing $e) {
            if($recover) $this->cache->forgetStale($key);
            throw $e;
        } finally {
            if($recover) $this->cache->release($key);
        }
    }

    /**
     * Resolve a product family or exact variant and load its presentation data.
     *
     * @since 1.0.0
     * @param string $id Shopware product or category UUID.
     * @param string $mode Selection mode: family or variant.
     * @return array Normalized result data for the documented operation.
     */
    public function product(string $id, string $mode = 'variant'): array {
        $this->uuid($id);
        if (!in_array($mode, ['variant', 'family'], true)) throw new \InvalidArgumentException('Invalid mode');
        return $this->cached("product|$mode|$id", function() use ($id, $mode): array {
            if ($mode === 'family') {
                // Detail route resolves parent requests to a variant in Shopware 6.7.10.2.
                $result = $this->client->read('product', ['ids' => [$id], 'associations' => self::associations()]);
                $p = $result['elements'][0] ?? null;
            } else {
                $result = $this->client->read('product/' . $id, ['associations' => self::associations()]);
                $p = $result['product'] ?? null;
            }
            if (!$p || $p['id'] !== $id) throw new ResourceMissing('missing');
            if ($mode === 'family' && !empty($p['parentId'])) throw new \RuntimeException('Expected main product');
            $fields=$p['translated']['customFields']??$p['customFields']??[];
            $ids=[];
            for($i=1;$i<=3;$i++) {
                $mediaId=$fields['custom_product_downloads_'.$i]??'';
                if(is_string($mediaId)&&preg_match('/^[a-f0-9]{32}$/D',$mediaId)) $ids[]=$mediaId;
            }
            $p['_documents']=[];
            if($ids) {
                // Document failure must not hide otherwise usable product data.
                try {
                    $media=$this->cached('media|'.implode('|',$ids),fn()=> $this->client->read('media',['ids'=>array_values(array_unique($ids))]));
                    $byId=[];
                    foreach($media as $m) if(is_array($m)&&!empty($m['id'])) $byId[$m['id']]=$m;
                    foreach(array_unique($ids) as $id) {
                        $m=$byId[$id]??[];
                        if(($m['mimeType']??'')!=='application/pdf'||!empty($m['private'])||empty($m['url'])) continue;
                        $p['_documents'][]=['id'=>$id,'url'=>$m['url'],'title'=>$m['translated']['title']??$m['title']??$m['fileName']??'PDF','fileSize'=>$m['fileSize']??0];
                    }
                } catch(\Throwable $e) { /* Retry when the product cache expires or is refreshed. */ }
            }
            return $this->mapper->map($p);
        });
    }

    /**
     * Search products by name or number using bounded catalog pagination.
     *
     * @since 1.0.0
     * @param string $term Product search text.
     * @param int $page One-based result page.
     * @return array Normalized result data for the documented operation.
     */
    public function search(string $term, int $page = 1): array {
        $term = trim($term);
        if ($term === '' || strlen($term) > 200 || $page < 1) throw new \InvalidArgumentException('Invalid search');
        return $this->cached('search|' . $page . '|' . $term, function() use ($term, $page): array {
            $r = $this->client->read('search', ['search' => $term, 'limit' => 20, 'page' => $page,
                'total-count-mode' => 1, 'associations' => self::associations()]);
            return ['total' => $r['total'] ?? 0, 'page' => $page,
                'items' => array_map([$this->mapper, 'map'], $r['elements'] ?? [])];
        });
    }

    /**
     * Return active categories backed by Shopware dynamic product groups.
     *
     * @since 1.0.0
     * @return array Normalized result data for the documented operation.
     */
    public function categories(): array {
        return $this->cached('dynamic-categories',function(): array {
            $items=[];$page=1;
            do {
                $r=$this->client->read('category',['limit'=>100,'page'=>$page++,
                    'sort'=>[['field'=>'name','order'=>'ASC']],
                    'filter'=>[['type'=>'equals','field'=>'active','value'=>true],
                        ['type'=>'equals','field'=>'productAssignmentType','value'=>'product_stream']]]);
                $batch=$r['elements']??[];
                foreach($batch as $c) {
                    if(empty($c['active'])||($c['productAssignmentType']??'')!=='product_stream'||($c['type']??'page')!=='page') continue;
                    $id=$c['id']??'';$this->uuid($id);
                    $items[$id]=['id'=>$id,'name'=>$c['translated']['name']??$c['name']??''];
                }
                if($page>21) throw new \RuntimeException('Category pagination safety limit');
            } while(count($batch)===100);
            return array_values($items);
        });
    }

    /**
     * Read a category listing with server-side sorting, limits and pagination.
     *
     * @since 1.0.0
     * @param string $categoryId Active dynamic category UUID.
     * @param int $limit Maximum products requested per result page.
     * @param int $page One-based result page.
     * @param string $order Shopware sorting key or empty string for the shop default.
     * @return array Normalized result data for the documented operation.
     */
    public function listing(string $categoryId,int $limit=6,int $page=1,string $order=''): array {
        $this->uuid($categoryId);
        if($limit<1||$limit>48||$page<1||$page>1000||strlen($order)>100) throw new \InvalidArgumentException('Invalid listing settings');
        return $this->cached('listing|'.$categoryId.'|'.$limit.'|'.$page.'|'.$order,function() use($categoryId,$limit,$page,$order): array {
            $category=null;
            foreach($this->categories() as $c) if($c['id']===$categoryId) {$category=$c;break;}
            if(!$category) throw new ResourceMissing('category');
            $criteria=['limit'=>$limit,'page'=>$page,'total-count-mode'=>1,'associations'=>self::associations()];
            // Obtain advertised Shopware sort keys; never reconstruct sorting in WordPress.
            if($order!=='') {
                $default=$this->listing($categoryId,$limit,$page);
                if(!in_array($order,array_column($default['sortings'],'key'),true)) throw new \InvalidArgumentException('Unsupported Shopware sorting');
                $criteria['order']=$order;
            }
            $r=$this->client->read('product-listing/'.$categoryId,$criteria);
            $sortings=[];
            foreach($r['availableSortings']??[] as $sort) {
                $key=$sort['key']??'';
                if(!is_string($key)||$key==='') continue;
                $sortings[]=['key'=>$key,'label'=>$sort['translated']['label']??$sort['label']??$key];
            }
            return ['category'=>$category,'total'=>(int)($r['total']??0),'page'=>$page,'limit'=>$limit,
                'sorting'=>$r['sorting']??'','sortings'=>$sortings,
                'items'=>array_map([$this->mapper,'map'],$r['elements']??[])];
        });
    }

    /**
     * Read available variants and their option labels for a product family.
     *
     * @since 1.0.0
     * @param string $parentId Product family UUID used to enumerate variants.
     * @return array Normalized result data for the documented operation.
     */
    public function variants(string $parentId): array {
        $this->uuid($parentId);
        return $this->cached('variants|' . $parentId, function() use ($parentId): array {
            $items = []; $page = 1;
            do {
                $r = $this->client->read('product', ['limit' => 100, 'page' => $page++,
                    'sort' => [['field' => 'id', 'order' => 'ASC']],
                    'filter' => [['type' => 'equals', 'field' => 'parentId', 'value' => $parentId]],
                    'associations' => self::associations()]);
                $batch = $r['elements'] ?? [];
                foreach ($batch as $p) $items[$p['id']] = $this->mapper->map($p);
                if ($page > 101) throw new \RuntimeException('Variant pagination safety limit');
            } while (count($batch) === 100);
            return array_values($items);
        });
    }
}
