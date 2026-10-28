<?php

namespace App\Services\Api;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTranslation;
use App\Models\Device;
use App\Models\Digital\DigitalProduct;
use App\Models\FooterSetting;
use App\Models\LandingPageContent;
use App\Models\Package;
use App\Models\ShopProduct;
use App\Services\BlogCoverImageService;
use App\Services\EventPromotionService;
use App\Services\LocaleService;
use App\Services\RecentPurchaseService;
use App\Services\ResponsiveProductImageService;
use App\Services\UnifiedProductService;
use App\Support\UiData;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicSiteDataService
{
    public const PAGES = [
        'home', 'about', 'contact', 'pricing', 'movies', 'packages', 'shop',
        'iptv-subscription-service', 'reseller-panel', 'iptv-applications', 'faqs',
        'activate', 'activate-info', 'terms-of-service', 'privacy-policy', 'refund-policy',
    ];

    private const LANDING_BRANDS = [
        'trex' => ['name' => 'TREX IPTV', 'plans' => ['TREX IPTV - Yearly' => 12]],
        'opplex' => ['name' => 'Opplex iptv', 'plans' => [
            'Opplex iptv' => 1, 'Opplex iptv - 3 Months' => 3,
            'Opplex iptv - Half Yearly' => 6, 'Opplex iptv - Yearly' => 12,
        ]],
        'geo' => ['name' => 'GEO World IPTV', 'plans' => [
            'GEO World IPTV' => 1, 'GEO World IPTV - 3 Months' => 3,
            'GEO World IPTV - Half Yearly' => 6, 'GEO World IPTV - Yearly' => 12,
        ]],
        '5g' => ['name' => '5G IPTV', 'plans' => [
            '5G IPTV' => 1, '5G IPTV - 3 Months' => 3,
            '5G IPTV - Half Yearly' => 6, '5G IPTV - Yearly' => 12,
        ]],
        'iptvsubscription' => ['name' => 'IPTV Subscription', 'plans' => [], 'selectable_providers' => true, 'email' => 'support@iptvsubscription.shop'],
        'getiptv' => ['name' => 'Get IPTV', 'plans' => [], 'selectable_providers' => true, 'email' => 'support@getiptv.store'],
    ];

    public function __construct(
        private UiData $uiData,
        private UnifiedProductService $unifiedProducts,
        private LocaleService $locale,
        private ResponsiveProductImageService $responsiveProductImages,
        private EventPromotionService $eventPromotions,
    ) {}

    public function pageUrl(string $name, array $parameters = []): string
    {
        return $this->uiData->pageUrl($name, $parameters);
    }

    public function pageData(?string $page = null, bool $browserContext = true): array
    {
        $data = $this->uiData->build($page, $browserContext);
        $data['recentPurchases'] = app(RecentPurchaseService::class)->items();
        $data['eventPromotionCampaigns'] = $this->eventPromotions->whatsappClickCampaigns();
        $data['activeEventPromotion'] = $browserContext && request()->hasSession()
            ? $this->eventPromotions->activeForSession()
            : null;

        return $data;
    }

    public function publicPage(string $page): array
    {
        abort_unless(in_array($page, self::PAGES, true), 404);
        $data = $this->pageData($page, false);
        unset($data['assistant'], $data['num1'], $data['num2'], $data['activeEventPromotion']);
        if ($page === 'home') {
            $data = array_merge($data, $this->home());
        }
        $meta = trans('meta.'.$page);
        $data['page_name'] = $page;
        $data['locale'] = app()->getLocale();
        $data['meta'] = [
            'title' => is_array($meta) ? ($meta['title'] ?? '') : '',
            'description' => is_array($meta) ? ($meta['description'] ?? '') : '',
            'keywords' => is_array($meta) ? ($meta['keywords'] ?? '') : '',
            'canonical' => $this->pageUrl($page),
        ];
        $contentKeys = [
            'home' => 'document_home',
            'about' => 'document_support.about',
            'contact' => 'document_support.contact',
            'faqs' => 'document_support.faq',
            'packages' => 'document_commerce.packages',
            'pricing' => 'document_commerce.pricing',
            'reseller-panel' => 'document_commerce.reseller',
            'iptv-applications' => 'document_product.applications',
            'iptv-subscription-service' => 'document_product.subscription',
            'movies' => 'document_product.movies',
            'shop' => 'document_product.shop',
            'activate' => 'interface.activation',
            'activate-info' => 'interface.activation',
            'terms-of-service' => 'terms.terms',
            'privacy-policy' => 'privacy',
            'refund-policy' => 'refund',
        ];
        $data['content'] = trans($contentKeys[$page], [
            'phone' => config('services.whatsapp.display'),
        ]);

        return $data;
    }

    public function browserContext(): array
    {
        $promotion = request()->hasSession() ? $this->eventPromotions->activeForSession() : null;

        return [
            'promotion' => $promotion ? array_intersect_key($promotion, array_flip([
                'id', 'name', 'discount_percent', 'ends_at',
            ])) : null,
        ];
    }

    public function home(): array
    {
        $allProducts = $this->localizedProducts();

        $homeProducts = $allProducts
            ->where('type', 'digital')
            ->values();
        $homeAffiliateProducts = $allProducts
            ->where('type', 'affiliate')
            ->values();

        return [
            'homeProducts' => $homeProducts,
            'homeAffiliateProducts' => $homeAffiliateProducts,
            'isRtl'        => $this->locale->isRtl(),
        ];
    }

    public function shop(?string $typeFilter = null, int $perPage = 8): array
    {
        $usesDocumentLayout = in_array(app()->getLocale(), config('app.locales', ['en']), true);
        $type = $usesDocumentLayout ? 'all' : 'affiliate';
        $allProducts = $this->localizedProducts();

        if ($usesDocumentLayout) {
            $documentProductPriority = [
                'affiliate:B08CRV62C4' => 0,
                'affiliate:B0BP9SNVH9' => 1,
                'affiliate:B0DXXYS4BJ' => 2,
                'affiliate:B00SFSU53G' => 3,
                'digital:netflix' => 4,
                'digital:prime-video' => 5,
                'digital:hbo-max-premium' => 6,
                'digital:nordvpn' => 7,
            ];
            $documentProductKey = static function (array $product): string {
                $type = strtolower((string) data_get($product, 'type', 'affiliate'));
                $identifier = (string) data_get(
                    $product,
                    'identifier',
                    $type === 'digital'
                        ? data_get($product, 'slug', '')
                        : data_get($product, 'asin', '')
                );

                return $type . ':' . $identifier;
            };

            $documentProducts = $allProducts
                ->filter(static fn (array $product) => isset($documentProductPriority[$documentProductKey($product)]))
                ->sortBy(static fn (array $product) => $documentProductPriority[$documentProductKey($product)])
                ->values();
            $remainingProducts = $allProducts
                ->reject(static fn (array $product) => isset($documentProductPriority[$documentProductKey($product)]))
                ->values();
            $products = $documentProducts->concat($remainingProducts)->values();
        } else {
            $products = $allProducts->where('type', 'affiliate')->values();
        }

        if ($typeFilter !== null && $typeFilter !== 'all') {
            $products = $products->where('type', $typeFilter)->values();
            $type = $typeFilter;
        }
        $isRtl = $this->locale->isRtl();

        // --- PAGINATION (array-based) ---
        $page = Paginator::resolveCurrentPage('page');
        $perPage = max(1, min(100, $perPage));

        $collection = collect($products);
        $items = $collection->forPage($page, $perPage)->values();

        $paginatedProducts = new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        return [
            'products' => $paginatedProducts,
            'selectedType' => $type,
            'isRtl'    => $isRtl,
        ];
    }

    private function localizedProducts(): \Illuminate\Support\Collection
    {
        return $this->unifiedProducts->frontendProducts()->map(function (array $product): array {
            $product['share_url'] = $this->pageUrl('products.share', ['type' => $product['type'], 'id' => $product['id']]);
            return $product;
        });
    }

    public function product(string $type, int $id): array
    {
        $type = strtolower($type);

        if (!in_array($type, ['digital', 'affiliate'], true)) {
            abort(404);
        }

        $shareLandingUrl = $this->pageUrl('products.share', ['type' => $type, 'id' => $id]);

        if ($type === 'digital') {
            $product = DigitalProduct::query()
                ->where('id', $id)
                ->where('is_active', true)
                ->firstOrFail();

            $name = $product->title;
            $imagePath = $product->image ? 'images/digital-products/' . $product->image : 'images/placeholder.webp';
            $image = asset($imagePath);
            $price = (string) $product->currency . ' ' . number_format((float) $product->price, 2);
            $actionUrl = 'https://wa.me/' . config('services.whatsapp.number') . '?text=' . rawurlencode(
                __('interface.product.digital_purchase_message', [
                    'name' => $name,
                    'price' => $price,
                    'url' => $shareLandingUrl,
                ])
            );
            $actionLabel = __('interface.product.buy');
            $description = __('interface.product.digital_description', [
                'name' => $name,
                'price' => $price,
            ]);
            $badge = __('interface.product.digital_badge');
        } else {
            $product = ShopProduct::query()
                ->with('translations')
                ->where('id', $id)
                ->where('is_active', true)
                ->firstOrFail();

            $name = $product->translation()?->name ?: $product->name;
            $imagePath = $product->image ? 'images/shop/' . $product->image : 'images/placeholder.webp';
            $image = asset($imagePath);
            $price = null;
            $actionUrl = 'https://wa.me/' . config('services.whatsapp.number') . '?text=' . rawurlencode(
                __('interface.product.affiliate_purchase_message', [
                    'name' => $name,
                    'url' => $shareLandingUrl,
                ])
            );
            $actionLabel = __('interface.product.buy');
            $description = __('interface.product.affiliate_description', ['name' => $name]);
            $badge = __('interface.product.affiliate_badge');
        }

        return [
            'productName' => $name,
            'productImage' => $image,
            'productImageSources' => $this->responsiveProductImages->metadata($imagePath),
            'productPrice' => $price,
            'productType' => $type,
            'productTypeLabel' => $badge,
            'productDescription' => $description,
            'productActionUrl' => $actionUrl,
            'productActionLabel' => $actionLabel,
            'isRtl' => $this->locale->isRtl(),
            'pageMetaTitle' => $name . ' | Opplex IPTV',
            'pageMetaDescription' => $description,
            'pageMetaImage' => $image,
            'pageCanonical' => $shareLandingUrl,
            'pageOgTitle' => $name . ' | Opplex IPTV',
            'pageOgDescription' => $description,
        ];
    }

    public function configure(Request $request, bool $browserContext = true): array
    {
        // Devices
        $devices = Device::query()
            ->orderBy('name')
            ->get(['id', 'name', 'icon']);

        // IPTV packages (type = iptv, vendor = opplex|starshare)
        $iptvRows = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->where('type', 'iptv')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)
            ->orderByRaw("CASE vendor WHEN 'opplex' THEN 0 WHEN 'starshare' THEN 1 ELSE 2 END")
            ->orderByRaw("COALESCE(sort_order, duration_months, id)")
            ->with('translations')
            ->get(['id', 'type', 'vendor', 'title', 'price_amount', 'duration_months', 'icon', 'features'])
            ->filter(fn (Package $package) => $package->isSelectableIptvProvider())
            ->values();

        $annualOnlyServices = $iptvRows
            ->filter(static fn (Package $package) => in_array(
                'Yearly subscription only (monthly plan unavailable)',
                $package->features ?? [],
                true
            ))
            ->map(fn (Package $package) => Str::lower($this->packageServiceName($package)))
            ->unique();
        $iptvRows = $iptvRows
            ->reject(fn (Package $package) => $annualOnlyServices->contains(
                Str::lower($this->packageServiceName($package))
            ) && (int) $package->duration_months !== 12)
            ->values();

        $iptvPackages = [];
        foreach ($iptvRows as $r) {
            $dur = (int) ($r->duration_months ?: 1);

            $iptvPackages[] = [
                'id'     => $r->id,                           // <--- ID SEND HO RAHA
                'vendor' => strtolower($r->vendor),           // opplex | starshare
                'service' => $this->packageServiceName($r),
                'title'  => $this->packageDisplayTitle($r),
                'old'    => 0.00,
                'price'  => (float) $r->price_amount,
                'duration_months' => $dur,
                'unit'   => $dur === 1
                    ? __('interface.checkout.month_one')
                    : __('interface.checkout.months', ['count' => $dur]),
                'icon'   => $r->icon ?: 'bi-router',
            ];
        }

        // Reseller packages (type = reseller, vendor = opplex|starshare)
        $resellerRows = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->where('type', 'reseller')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)
            ->orderByRaw("CASE vendor WHEN 'opplex' THEN 0 WHEN 'starshare' THEN 1 ELSE 2 END")
            ->orderByRaw('COALESCE(sort_order, credits, id)')
            ->with('translations')
            ->get(['id', 'type', 'vendor', 'title', 'price_amount', 'credits', 'icon']);

        $resellerPackages = [];
        foreach ($resellerRows as $r) {
            $credits = (int) ($r->credits ?: 0);

            $resellerPackages[] = [
                'id'     => $r->id,                           // <--- ID SEND HO RAHA
                'vendor' => strtolower($r->vendor),
                'title'  => $this->packageDisplayTitle($r),
                'old'    => 0.00,
                'price'  => (float) $r->price_amount,
                'unit'   => $credits > 0
                    ? __('interface.checkout.credits', ['count' => $credits])
                    : __('interface.checkout.credits_label'),
                'icon'   => $r->icon ?: 'bi-router',
            ];
        }

        $prePrice    = $request->query('price');
        $iptvVendors = ['Opplex', 'starshare'];
        $eventPromotion = $browserContext && $request->hasSession()
            ? $this->eventPromotions->activeForSession()
            : null;

        return compact(
            'devices',
            'iptvPackages',
            'resellerPackages',
            'prePrice',
            'iptvVendors',
            'eventPromotion'
        );
    }

    public function providerPlans(Package $package): array
    {
        abort_unless(
            $package->active
                && $package->type === 'iptv'
                && in_array($package->vendor, ['opplex', 'starshare'], true)
                && $package->isSelectableIptvProvider()
                && (float) $package->price_amount > 0,
            404
        );

        $providerName = $this->packageServiceName($package);
        $providerTitles = $package->isDurationPlan()
            ? Package::DURATION_PLAN_TITLES
            : [
                $providerName,
                $providerName . ' - Monthly',
                $providerName . ' - 1 Month',
                $providerName . ' - 3 Months',
                $providerName . ' - Half Yearly',
                $providerName . ' - Yearly',
            ];
        $candidates = Package::query()
            ->where('active', true)
            ->where('type', 'iptv')
            ->where('vendor', $package->vendor)
            ->whereIn('title', $providerTitles)
            ->where('price_amount', '>', 0)
            ->orderByRaw('COALESCE(sort_order, duration_months, id)')
            ->with('translations')
            ->get();
        if ($candidates->contains(static fn (Package $candidate) => in_array(
            'Yearly subscription only (monthly plan unavailable)',
            $candidate->features ?? [],
            true
        ))) {
            $candidates = $candidates
                ->where('duration_months', 12)
                ->values();
        }
        $packages = $candidates
            ->take(4)
            ->map(function (Package $candidate): array {
                $card = $candidate->toIptvArray();
                $card['service'] = $this->packageServiceName($candidate);

                return $card;
            })
            ->values()
            ->all();

        abort_if(empty($packages), 404);

        return [
            'provider_name' => $providerName,
            'count' => count($packages),
            'packages' => $packages,
        ];
    }

    public function landingPlans(string $brand): array
    {
        abort_unless(isset(self::LANDING_BRANDS[$brand]), 404);
        $definition = self::LANDING_BRANDS[$brand];
        if (!empty($definition['selectable_providers'])) {
            return $this->selectableLandingPlans($brand, $definition['name']);
        }
        $packages = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->where('type', 'iptv')
            ->where('vendor', 'opplex')
            ->where('price_amount', '>', 0)
            ->whereIn('title', array_keys($definition['plans']))
            ->with('translations')
            ->orderBy('duration_months')
            ->orderBy('id')
            ->get()
            ->filter(fn (Package $package) => ($definition['plans'][$package->title] ?? null) === (int) $package->duration_months)
            ->unique('duration_months')
            ->map(function (Package $package) use ($definition): array {
                $card = $package->toIptvArray();
                $card['service'] = $definition['name'];

                return $card;
            })
            ->values()
            ->all();

        return [
            'brand' => $brand,
            'provider_name' => $definition['name'],
            'count' => count($packages),
            'packages' => $packages,
        ];
    }

    private function selectableLandingPlans(string $brand, string $name): array
    {
        $rows = Package::query()->where('active', true)->where('is_available', true)
            ->where('type', 'iptv')->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)->where('duration_months', '>', 0)
            ->with('translations')->orderBy('duration_months')->orderBy('id')->get()
            ->filter(fn (Package $package) => $package->isSelectableIptvProvider());
        $provider = fn (Package $package) => in_array(Str::lower($this->packageServiceName($package)), ['filex', 'filex iptv'], true)
            ? 'Filex' : 'Opplex';
        $offer = fn (Package $package) => json_encode([$provider($package), (int) $package->duration_months, (float) $package->price_amount], JSON_THROW_ON_ERROR);
        $named = $rows->reject(fn (Package $package) => $package->isDurationPlan())->groupBy($offer);
        $annualOnly = $rows->filter(static fn (Package $package) => in_array(
            'Yearly subscription only (monthly plan unavailable)', $package->features ?? [], true
        ))->map(fn (Package $package) => Str::lower($this->packageServiceName($package)))->unique();
        $packages = $rows->reject(fn (Package $package) => $annualOnly->contains(
            Str::lower($this->packageServiceName($package))
        ) && (int) $package->duration_months !== 12)->reject(fn (Package $package) => $package->isDurationPlan()
            && $named->has($offer($package)) && $named->get($offer($package))->contains(fn (Package $canonical) =>
                $package->max_devices === null || $package->max_devices === $canonical->max_devices))
            ->map(function (Package $package) use ($provider): array {
                $card = $package->toIptvArray();
                $card['service'] = $provider($package);

                return $card;
            })->unique(fn (array $card) => json_encode([$card['service'], $card['duration_months'], (float) $card['price_amount'], $card['max_devices']], JSON_THROW_ON_ERROR))
            ->values()->all();

        return ['brand' => $brand, 'provider_name' => $name, 'count' => count($packages), 'packages' => $packages];
    }

    public function landingContent(string $brand): array
    {
        $catalog = $this->landingPlans($brand);
        $currency = strtoupper((string) config('services.app.default_currency', 'USD'));
        if (!preg_match('/^[A-Z]{3}$/D', $currency)) {
            $currency = 'USD';
        }
        $packages = collect($catalog['packages'])->map(function (array $package) use ($currency, $catalog): array {
            $months = (int) $package['duration_months'];
            $devices = (int) ($package['max_devices'] ?? 0);

            return [
                'id' => (int) $package['id'],
                'type' => 'iptv',
                'title' => $months === 1 ? 'Monthly plan' : $months.'-month plan',
                'service' => !empty(self::LANDING_BRANDS[$catalog['brand']]['selectable_providers'])
                    ? $package['service'] : $catalog['provider_name'],
                'price_amount' => (float) $package['price_amount'],
                'duration_months' => $months,
                'currency' => $currency,
                'max_devices' => $devices > 0 ? $devices : null,
                'is_available' => true,
            ];
        })->values()->all();
        $locale = app()->getLocale();
        $content = LandingPageContent::query()->where('brand', $brand)->where('locale', $locale)->first();
        if (!$content && $locale !== 'en') {
            $content = LandingPageContent::query()->where('brand', $brand)->where('locale', 'en')->first();
        }
        $email = trim((string) (self::LANDING_BRANDS[$brand]['email'] ?? FooterSetting::query()->latest()->value('email')));
        $phone = preg_replace('/\D/', '', (string) config('services.whatsapp.number'));
        $phoneDisplay = trim((string) config('services.whatsapp.display'));
        $document = json_decode(file_get_contents(resource_path('content/landing-pages/'.$locale.'.json')), true, 64, JSON_THROW_ON_ERROR);

        return [
            'brand' => $brand,
            'provider_name' => $catalog['provider_name'],
            'locale' => $content?->locale ?? $locale,
            'currency' => $currency,
            'count' => count($packages),
            'packages' => $packages,
            'content' => $content?->is_published ? $content->publicContent() : null,
            'experience' => array_merge([
                'ui' => $document['experience']['ui'] ?? [],
                'help' => $document['experience']['help'][$brand] ?? null,
            ], app(\App\Services\LandingExperienceService::class)->publicData($brand, $locale)),
            'support' => [
                'phone' => preg_match('/^[0-9]{7,15}$/D', $phone) ? $phone : null,
                'phone_display' => $phoneDisplay !== '' ? $phoneDisplay : null,
                'email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null,
            ],
            'links' => [
                'account' => route('customer.account', [], false),
                'setup' => route('customer.guides', [], false),
                'contact' => route('contact', [], false),
                'legal' => [
                    ['label' => 'Terms of service', 'url' => route('terms-of-service', [], false)],
                    ['label' => 'Privacy policy', 'url' => route('privacy-policy', [], false)],
                    ['label' => 'Refund policy', 'url' => route('refund-policy', [], false)],
                ],
            ],
            'updated_at' => $content?->is_published ? $content->updated_at?->toIso8601String() : null,
        ];
    }

    public function landingPages(string $brand): array
    {
        abort_unless(isset(self::LANDING_BRANDS[$brand]), 404);
        $locale = app()->getLocale();
        abort_unless(preg_match('/^[a-z]{2}$/D', $locale) && in_array($locale, config('app.locales', ['en']), true), 404);
        $path = resource_path('content/landing-pages/'.$locale.'.json');
        abort_unless(is_file($path) && is_readable($path), 404);
        $document = json_decode(file_get_contents($path), true);
        abort_unless(is_array($document), 503);
        abort_unless(($document['locale'] ?? null) === $locale, 404);
        $labels = $document['labels'] ?? null;
        $pages = $document['brands'][$brand]['pages'] ?? null;
        abort_unless(is_array($labels) && is_array($pages) && array_is_list($pages), 503);
        foreach ($labels as $label) {
            abort_unless(is_string($label) && trim($label) !== '', 503);
        }
        $seen = [];
        $pages = array_map(function ($page) use (&$seen): array {
            abort_unless(is_array($page), 503);
            foreach (['slug', 'title', 'description', 'intro', 'kind'] as $field) {
                abort_unless(isset($page[$field]) && is_string($page[$field]) && trim($page[$field]) !== '', 503);
            }
            $slug = $page['slug'];
            abort_unless(preg_match('~^(?:blogs/)?[a-z0-9]+(?:-[a-z0-9]+)*$~D', $slug), 503);
            abort_if(isset($seen[$slug]) || in_array($slug, ['pricing', 'iptv-subscription-service'], true), 503);
            $seen[$slug] = true;
            abort_unless(isset($page['sections']) && is_array($page['sections']) && array_is_list($page['sections']), 503);
            $sections = array_map(function ($section): array {
                abort_unless(is_array($section) && isset($section['heading'], $section['body'])
                    && is_string($section['heading']) && is_string($section['body'])
                    && trim($section['heading']) !== '' && trim($section['body']) !== '', 503);

                $content = ['heading' => $section['heading'], 'body' => $section['body']];
                if (array_key_exists('links', $section)) {
                    $content['links'] = $this->landingSourceLinks($section['links']);
                }

                return $content;
            }, $page['sections']);

            return [
                'slug' => $slug,
                'title' => $page['title'],
                'description' => $page['description'],
                'intro' => $page['intro'],
                'kind' => $page['kind'],
                'sections' => $sections,
            ];
        }, $pages);

        return ['brand' => $brand, 'locale' => $locale, 'labels' => $labels, 'pages' => $pages];
    }

    private function landingSourceLinks(mixed $links): array
    {
        abort_unless(is_array($links) && array_is_list($links) && count($links) <= 3, 503);

        return array_map(function ($link): array {
            abort_unless(is_array($link) && isset($link['label'], $link['url'])
                && is_string($link['label']) && trim($link['label']) !== ''
                && mb_strlen($link['label']) <= 160 && strip_tags($link['label']) === $link['label']
                && ! str_contains($link['label'], "\0")
                && is_string($link['url']) && strlen($link['url']) <= 2048
                && ! preg_match('/[\x00-\x20\x7f]/', $link['url'])
                && filter_var($link['url'], FILTER_VALIDATE_URL), 503);
            $url = parse_url($link['url']);
            abort_unless(is_array($url) && ($url['scheme'] ?? null) === 'https'
                && in_array($url['host'] ?? null, ['support.google.com', 'support.apple.com', 'docs.videolan.me', 'www.videolan.org'], true)
                && ! isset($url['user']) && ! isset($url['pass'])
                && (! isset($url['port']) || $url['port'] === 443), 503);

            return ['label' => $link['label'], 'url' => $link['url']];
        }, $links);
    }

    public function landingPage(string $brand, string $page): array
    {
        abort_unless(preg_match('~^(?:blogs/)?[a-z0-9]+(?:-[a-z0-9]+)*$~D', $page), 404);
        $data = $this->landingPages($brand);
        $selected = collect($data['pages'])->firstWhere('slug', $page);
        abort_unless($selected, 404);

        return ['brand' => $brand, 'locale' => $data['locale'], 'labels' => $data['labels'], 'page' => $selected];
    }

    private function isLandingBrandPackage(Package $package): bool
    {
        if ($package->type !== 'iptv' || $package->vendor !== 'opplex') {
            return false;
        }

        foreach (self::LANDING_BRANDS as $definition) {
            if (($definition['plans'][$package->title] ?? null) === (int) $package->duration_months) {
                return true;
            }
        }

        return false;
    }

    public function catalog(): array
    {
        $currency = strtoupper((string) config('services.app.default_currency', 'USD'));
        $iptvRows = Package::query()
            ->where('active', true)
            ->where('type', 'iptv')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('is_available', true)
            ->where('price_amount', '>', 0)
            ->sorted()
            ->get()
            ->filter(fn (Package $package) => $package->isSelectableIptvProvider() || $this->isLandingBrandPackage($package))
            ->values();

        if ($iptvRows->contains(fn (Package $package) => ! $package->isDurationPlan() && $package->isSelectableIptvProvider())) {
            $iptvRows = $iptvRows
                ->reject(fn (Package $package) => $package->isDurationPlan())
                ->values();
        }

        $annualOnlyServices = $iptvRows
            ->filter(static fn (Package $package) => in_array(
                'Yearly subscription only (monthly plan unavailable)',
                $package->features ?? [],
                true
            ))
            ->map(fn (Package $package) => Str::lower($this->packageServiceName($package)))
            ->unique();
        $iptvRows = $iptvRows
            ->reject(fn (Package $package) => $annualOnlyServices->contains(
                Str::lower($this->packageServiceName($package))
            ) && (int) $package->duration_months !== 12)
            ->values();

        $resellerRows = Package::query()
            ->where('active', true)
            ->where('type', 'reseller')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('is_available', true)
            ->where('price_amount', '>', 0)
            ->sorted()
            ->get();

        $packages = $iptvRows
            ->concat($resellerRows)
            ->map(function (Package $package) use ($currency): array {
                return [
                    'id' => (int) $package->getKey(),
                    'type' => (string) $package->type,
                    'vendor' => (string) $package->vendor,
                    'provider_name' => $this->packageServiceName($package),
                    'title' => (string) $package->title,
                    'display_price' => $package->display_price,
                    'price_amount' => (float) $package->price_amount,
                    'currency' => $currency,
                    'duration_months' => $package->duration_months,
                    'credits' => $package->credits,
                    'features' => array_values($package->features ?? []),
                    'badge_key' => $package->badge_key,
                    'is_featured' => (bool) $package->is_featured,
                    'is_available' => true,
                    'free_trial_hours' => (int) ($package->free_trial_hours ?? 0),
                    'instant_activation' => (bool) $package->instant_activation,
                    'sort_order' => (int) ($package->sort_order ?? 0),
                ];
            })
            ->values();

        $devices = Device::query()
            ->orderBy('name')
            ->get(['id', 'name', 'icon'])
            ->map(static fn (Device $device): array => [
                'id' => (int) $device->getKey(),
                'name' => (string) $device->name,
                'icon' => (string) ($device->icon ?? ''),
            ])
            ->values();

        return [
            'data' => $packages,
            'devices' => $devices,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function blogs(Request $request, BlogCoverImageService $coverImages): array
    {
        $locale = app()->getLocale();
        $search = trim((string) $request->query('q', ''));
        $categorySlug = trim((string) $request->query('category', ''));

        $featuredQuery = Blog::published()->forSite('main')
            ->where('is_featured', true)
            ->whereHas('translations', function ($q) use ($locale) {
                $q->where('locale', $locale);
            })
            ->with(['author', 'translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }, 'categories.translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }]);

        if ($categorySlug !== '') {
            $featuredQuery->whereHas('categories.translations', function ($q) use ($categorySlug, $locale) {
                $q->where('locale', $locale)->where('slug', $categorySlug);
            });
        }

        $featured = $featuredQuery->orderByDesc('published_at')->first();
        $featuredCoverImage = $featured?->cover_image
            ? $coverImages->featured($featured->cover_image)
            : null;

        $blogsQuery = Blog::published()->forSite('main')
            ->whereHas('translations', function ($q) use ($locale) {
                $q->where('locale', $locale);
            })
            ->with(['author', 'translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }, 'categories.translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }]);

        if ($featured) {
            $blogsQuery->where('id', '!=', $featured->id);
        }

        if ($search !== '') {
            $blogsQuery->whereHas('translations', function ($q) use ($search, $locale) {
                $q->where('locale', $locale)
                    ->where(function ($qq) use ($search) {
                        $qq->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%");
                    });
            });
        }

        if ($categorySlug !== '') {
            $blogsQuery->whereHas('categories.translations', function ($q) use ($categorySlug, $locale) {
                $q->where('locale', $locale)->where('slug', $categorySlug);
            });
        }

        $blogs = $blogsQuery->orderByDesc('published_at')->paginate(12)->withQueryString();

        $categories = BlogCategory::query()
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }])
            ->orderBy('id')
            ->get();

        $pageMetaTitle = trans('meta.blogs.index.title');
        $pageMetaDescription = trans('meta.blogs.index.description');
        $pageMetaKeywords = trans('meta.blogs.index.keywords');

        return compact(
            'blogs',
            'featured',
            'featuredCoverImage',
            'search',
            'categories',
            'categorySlug',
            'pageMetaTitle',
            'pageMetaDescription',
            'pageMetaKeywords'
        );
    }

    public function blog(string $slug, BlogCoverImageService $coverImages): array
    {
        $locale = app()->getLocale();

        $translation = BlogTranslation::where('slug', $slug)
            ->where('locale', $locale)
            ->whereHas('blog', fn ($query) => $query->forSite('main'))
            ->first();

        if (!$translation) {
            $matches = BlogTranslation::where('slug', $slug)
                ->whereIn('locale', array_keys(config('laravellocalization.supportedLocales', [])))
                ->whereHas('blog', fn ($query) => $query->published()->forSite('main'))
                ->limit(2)
                ->get();

            abort_unless($matches->count() === 1, 404);

            $match = $matches->first();
            $target = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(
                $match->locale,
                url('/blogs/'.rawurlencode($match->slug)),
                [],
                false
            );

            return ['redirect_url' => $target];
        }

        $blog = Blog::with(['translations', 'author'])->forSite('main')->findOrFail($translation->blog_id);

        if ($blog->status !== 'published' || ($blog->published_at && $blog->published_at->isFuture())) {
            abort(404);
        }

        $currentTranslation = $blog->translation($translation->locale) ?? $translation;
        $articleCoverImage = $blog->cover_image
            ? $coverImages->featured($blog->cover_image)
            : null;

        $related = Blog::published()->forSite('main')
            ->where('id', '!=', $blog->id)
            ->whereHas('translations', function ($q) use ($locale) {
                $q->where('locale', $locale);
            })
            ->with(['author', 'translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }, 'categories.translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $pageMetaTitle = $currentTranslation->seo_title ?: $currentTranslation->title;
        $pageMetaDescription = $currentTranslation->seo_description ?: Str::limit(strip_tags($currentTranslation->excerpt ?: $currentTranslation->content), 155);
        $pageMetaKeywords = $currentTranslation->seo_keywords ?: trans('meta.blogs.show.keywords');

        $pageOgTitle = $currentTranslation->og_title ?: $pageMetaTitle;
        $pageOgDescription = $currentTranslation->og_description ?: $pageMetaDescription;

        $imagePath = $currentTranslation->og_image ?: $blog->cover_image;
        $pageMetaImage = $imagePath ? url(Storage::url($imagePath)) : null;

        $pageCanonical = $currentTranslation->canonical_url ?: $this->pageUrl('blogs.show', ['slug' => $currentTranslation->slug]);

        $pageOgType = 'article';
        $jsonLd = $currentTranslation->schema_json ?: [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $currentTranslation->title,
            'description' => $pageMetaDescription,
            'inLanguage' => $translation->locale,
            'datePublished' => optional($blog->published_at)->toIso8601String(),
            'dateModified' => optional($blog->updated_at)->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => optional($blog->author)->name ?: config('app.name'),
            ],
            'image' => $pageMetaImage ? [$pageMetaImage] : [],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $pageCanonical,
            ],
        ];

        return compact(
            'blog',
            'currentTranslation',
            'articleCoverImage',
            'related',
            'pageMetaTitle',
            'pageMetaDescription',
            'pageMetaKeywords',
            'pageMetaImage',
            'pageCanonical',
            'pageOgType',
            'pageOgTitle',
            'pageOgDescription',
            'jsonLd'
        );
    }

    private function packageServiceName(Package $package): string
    {
        if ($package->type !== 'iptv' || $package->isDurationPlan()) {
            return strtolower((string) $package->vendor) === 'starshare' ? 'Filex' : 'Opplex';
        }

        $title = trim((string) $package->title);
        $service = preg_replace(
            '/\s*-\s*(?:3\s*Months?|Half\s*Yearly|Yearly|Monthly|1\s*Month)\s*$/iu',
            '',
            $title
        );

        return trim((string) $service) ?: $title;
    }

    private function packageDisplayTitle(Package $package): string
    {
        if ($package->type === 'iptv' && $package->isDurationPlan()) {
            $planKey = match ((int) $package->duration_months) {
                1 => 'monthly',
                3 => 'three_months',
                6 => 'half_yearly',
                12 => 'yearly',
                default => null,
            };

            if ($planKey !== null) {
                $translationKey = 'document_commerce.packages.pricing.plans.' . $planKey . '.title';
                $localizedTitle = __($translationKey);
                if ($localizedTitle !== $translationKey) {
                    return $localizedTitle;
                }
            }
        } elseif ($package->type === 'reseller') {
            $translationKey = match ($package->title) {
                'Starter Reseller Package' => 'messages.starter_reseller',
                'Essential Reseller Bundle' => 'messages.essential_reseller',
                'Pro Reseller Suite' => 'messages.pro_reseller',
                'Advanced Reseller Toolkit' => 'messages.advanced_reseller',
                default => null,
            };

            if ($translationKey !== null && __($translationKey) !== $translationKey) {
                return __($translationKey);
            }
        }

        $title = (string) ($package->translation()?->title ?: $package->title);

        if ($package->type === 'iptv') {
            return Str::startsWith($title, 'messages.') || trim($title) === ''
                ? (string) $package->title
                : trim($title);
        }

        $title = (string) preg_replace('/\s*\([^)]*\)/u', '', $title);
        $title = trim((string) preg_replace('/\s*-\s*\$?\d+(?:[.,]\d+)?/u', '', $title, 1));

        return Str::startsWith($title, 'messages.') || $title === ''
            ? (string) $package->title
            : $title;
    }

}

