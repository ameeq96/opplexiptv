<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Package;
use App\Models\HomeService;
use App\Models\Testimonial;
use App\Models\MenuItem;
use App\Models\PricingSection;
use App\Models\FooterSetting;
use App\Models\FooterLink;
use App\Models\SocialLink;
use App\Services\{CaptchaService, ImageService, LocaleService, TmdbService};
use Illuminate\Support\{Arr, Collection, Str};
use Illuminate\Support\Facades\{Cache, Lang};
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;

class UiData
{
    public function __construct(
        private Agent $agent,
        private ImageService $images,
        private LocaleService $locale,
        private TmdbService $tmdb,
        private CaptchaService $captcha,
        private Request $request,
    ) {}

    /**
     * Build the entire UI data payload.
     *
     * @return array<string,mixed>
     */
    public function build(): array
    {
        $isMobile       = $this->agent->isMobile();
        $isRtl          = $this->locale->isRtl();
        $containerClass = $isMobile ? 'centered' : 'sec-title centered';

        $logos = Cache::remember('ui:logos', now()->addDay(), function () {
            $dbLogos = app(\App\Services\ProductCatalogService::class)->getLogos();
            return !empty($dbLogos) ? $dbLogos : $this->images->logos();
        });

        ['num1' => $a, 'num2' => $b] = $this->captcha->generate();
        session(['captcha_sum' => $a + $b]);

        $page  = max(1, (int) $this->request->input('page', 1));
        $query = trim((string) $this->request->input('search', ''));

        if ($query !== '') {
            $payload    = $this->searchTmdb($query, $page);
            $results    = $payload['results'] ?? [];
            $totalPages = max(1, (int) ($payload['total_pages'] ?? 1));
        } else {
            $results    = $this->fetchTrendingMovies($page);
            $totalPages = 10;
        }

        $rawLimited     = \array_slice($results, 0, 10);
        $prepared       = $this->prepareMovies($rawLimited, $isMobile);
        $movies         = $prepared->values();
        $displayMovies  = $isMobile ? $movies->take(3)->values() : $movies;

        $normalized     = $this->normalizeMovies($movies);
        $filteredMovies = [
            'movies'   => $normalized->where('media_type', 'movie')->values(),
            'series'   => $normalized->where('media_type', 'tv')->values(),
            'cartoons' => $normalized->filter(static fn($m) => \in_array(16, $m['genre_ids'] ?? [], true))->values(),
        ];

        $pagination = $this->computePagination(
            page: $page,
            totalPages: $totalPages,
            window: 5
        );

        $features      = $this->features();
        $serviceCards  = $this->serviceCards();
        $menuItems     = $this->menuItems();
        $pricingSection = $this->pricingSection();
        $footer = $this->footerData();
        $packages      = $this->packages();
        $resellerPlans = $this->resellerPlans();
        $testimonials  = $this->testimonials();
        $faqs          = $this->faqs();

        $platforms = $this->enrichPlatforms($this->platforms());
        [$packagesDropdown, $resellerPanelPackagesDropdown] = $this->dropdowns();

        $seoServices = array_values((array) Lang::get('messages.seo_services'));

        return [
            'isMobile'       => $isMobile,
            'isRtl'          => $isRtl,
            'containerClass' => $containerClass,
            'logos'          => $logos,

            'features'       => $features,
            'serviceCards'   => $serviceCards,
            'menuItems'      => $menuItems,
            'pricingSection' => $pricingSection,
            'footer'         => $footer,
            'packages'       => $packages,
            'resellerPlans'  => $resellerPlans,
            'testimonials'   => $testimonials,
            'faqs'           => $faqs,
            'platforms'      => $platforms,

            'num1' => $a,
            'num2' => $b,

            'movies'         => $movies,
            'displayMovies'  => $displayMovies,
            'filteredMovies' => $filteredMovies,

            'page'       => $pagination['page'],
            'totalPages' => $pagination['total'],
            'pageStart'  => $pagination['start'],
            'pageEnd'    => $pagination['end'],
            'query'      => $query,

            'seoServices'                   => $seoServices,
            'packagesDropdown'              => $packagesDropdown,
            'resellerPanelPackagesDropdown' => $resellerPanelPackagesDropdown,
        ];
    }

    /**
     * Cache TMDB trending to reduce latency & rate.
     *
     * @return array<int,array<string,mixed>>
     */
    private function fetchTrendingMovies(int $page = 1): array
    {
        $locale = app()->getLocale();
        $cacheKey = "ui:tmdb:trending:all:day:{$locale}:p{$page}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($page): array {
            try {
                $payload = $this->tmdb->trending('all', 'day', $page);
                return Arr::get($payload, 'results', []) ?: [];
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function searchTmdb(string $query, int $page = 1): array
    {
        $locale = app()->getLocale();
        $key = "ui:tmdb:search:" . md5($locale . '|' . $query) . ":p{$page}";

        return Cache::remember($key, now()->addMinutes(10), function () use ($query, $page) {
            try {
                $payload = $this->tmdb->searchMulti($query, $page);
                return [
                    'results'      => Arr::get($payload, 'results', []) ?: [],
                    'total_pages'  => (int) (Arr::get($payload, 'total_pages', 1) ?: 1),
                ];
            } catch (\Throwable) {
                return ['results' => [], 'total_pages' => 1];
            }
        });
    }

    /**
     * Add safe fields and generate responsive WEBP URLs.
     *
     * @param array<int,array<string,mixed>> $raw
     * @return Collection<int,array<string,mixed>>
     */
    private function prepareMovies(array $raw, bool $isMobile): Collection
    {
        // Keep slider images under ~100 KB by reducing dimensions + quality
        $imgWidth  = $isMobile ? 420 : 960;
        $imgHeight = $isMobile ? 220 : 540;

        return collect($raw)->map(function (array $m) use ($isMobile, $imgWidth, $imgHeight) {
            if (!empty($m['backdrop_path'])) {
                $src = $this->images->tmdbImage($m['backdrop_path'], $isMobile ? 'w342' : 'w780');
                $m['webp_image_url'] = $this->images->toWebp($src, $imgWidth, $imgHeight, 70);
            }

            if (!empty($m['poster_path'])) {
                // Use smaller poster size to reduce bytes on card grids
                $src = $this->images->tmdbImage($m['poster_path'], 'w342');
                $m['webp_poster_url'] = $this->images->toWebp($src, 308, 462);
            }

            $m['safe_title']    = $m['title'] ?? $m['name'] ?? 'Featured IPTV Content';
            $m['safe_overview'] = isset($m['overview'])
                ? Str::limit((string) $m['overview'], 150)
                : __('messages.no_overview');

            return $m;
        })->values();
    }

    /**
     * Normalize for cards/filters and add trailer URL.
     *
     * @param Collection<int,array<string,mixed>> $movies
     * @return Collection<int,array<string,mixed>>
     */
    private function normalizeMovies(Collection $movies): Collection
    {
        return $movies->map(function (array $m) {
            $mediaType = $m['media_type']
                ?? (isset($m['title']) ? 'movie' : (isset($m['name']) ? 'tv' : 'movie'));

            $title   = $m['title'] ?? $m['name'] ?? '';
            $dateRaw = $m['release_date'] ?? $m['first_air_date'] ?? '';
            $year    = $dateRaw ? substr((string) $dateRaw, 0, 4) : '—';
            $poster  = $m['poster_path'] ?? null;

            $posterUrl = $poster
                ? "https://image.tmdb.org/t/p/w342{$poster}"
                : asset('images/placeholders/poster.webp');

            $vote = isset($m['vote_average'])
                ? number_format((float) $m['vote_average'], 1)
                : '—';

            $id = $m['id'] ?? null;
            $trailerUrl = null;
            if ($id !== null) {
                try {
                    $trailerUrl = $this->tmdb->trailerUrl($id, $mediaType);
                } catch (\Throwable) {
                    $trailerUrl = null;
                }
            }

            return [
                'id'          => $id,
                'media_type'  => $mediaType,
                'title'       => $title,
                'year'        => $year,
                'poster_url'  => $posterUrl,
                'vote'        => $vote,
                'genre_ids'   => $m['genre_ids'] ?? [],
                'trailer_url' => $trailerUrl,
            ];
        })->values();
    }

    /**
     * Centered pagination window.
     *
     * @return array{page:int,start:int,end:int,total:int}
     */
    private function computePagination(int $page, int $totalPages, int $window = 5): array
    {
        $page       = max(1, $page);
        $totalPages = max(1, $totalPages);

        $half  = intdiv($window, 2);
        $start = max(1, $page - $half);
        $end   = min($totalPages, $start + $window - 1);

        if (($end - $start + 1) < $window) {
            $start = max(1, $end - $window + 1);
        }

        return [
            'page'  => $page,
            'start' => $start,
            'end'   => $end,
            'total' => $totalPages,
        ];
    }

    /** @return array<int,array<string,string>> */
    private function features(): array
    {
        return [
            [
                'icon'        => 'flaticon-swimming-pool',
                'title'       => __('messages.features.hd_quality.title'),
                'description' => __('messages.features.hd_quality.description'),
                'link'        => route('packages'),
            ],
            [
                'icon'        => 'flaticon-5g',
                'title'       => __('messages.features.flexible_packages.title'),
                'description' => __('messages.features.flexible_packages.description'),
                'link'        => route('packages'),
            ],
            [
                'icon'        => 'flaticon-8k',
                'title'       => __('messages.features.reliable_service.title'),
                'description' => __('messages.features.reliable_service.description'),
                'link'        => route('packages'),
            ],
            [
                'icon'        => 'flaticon-customer-service',
                'title'       => __('messages.features.easy_setup.title'),
                'description' => __('messages.features.easy_setup.description'),
                'link'        => route('contact'),
            ],
        ];
    }

    /** @return array<int,array<string,string>> */
    private function serviceCards(): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('home_services')) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale');

            return HomeService::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->with(['translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', array_unique([$locale, $fallback]));
                }])
                ->get(['id', 'title', 'description', 'link', 'icon'])
                ->map(function (HomeService $s) {
                    $t = $s->translation();
                    return [
                        'title' => $t?->title ?: $s->title,
                        'description' => $t?->description ?: $s->description,
                        'link' => $s->link,
                        'icon' => $s->icon,
                    ];
                })
                ->toArray();
        }

        return [];
    }

    /** @return array<int,array<string,mixed>> */
    private function menuItems(): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('menu_items')) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale');

            return MenuItem::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with(['children' => function ($q) {
                    $q->where('is_active', true);
                }, 'translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', array_unique([$locale, $fallback]));
                }, 'children.translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', array_unique([$locale, $fallback]));
                }])
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(function (MenuItem $item) {
                    $t = $item->translation();
                    return [
                        'id' => $item->id,
                        'label' => $t?->label ?: $item->label,
                        'url' => $item->url,
                        'open_new_tab' => $item->open_new_tab,
                        'children' => $item->children->map(function (MenuItem $child) {
                            $tc = $child->translation();
                            return [
                                'id' => $child->id,
                                'label' => $tc?->label ?: $child->label,
                                'url' => $child->url,
                                'open_new_tab' => $child->open_new_tab,
                            ];
                        })->toArray(),
                    ];
                })
                ->toArray();
        }

        return [];
    }

    private function pricingSection(): ?array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('pricing_sections')) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale');
            $section = PricingSection::query()
                ->with(['translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', array_unique([$locale, $fallback]));
                }])
                ->latest()
                ->first();

            if (!$section) {
                return null;
            }

            $t = $section->translation();
            return [
                'heading' => $t?->heading ?: $section->heading,
                'subheading' => $t?->subheading ?: $section->subheading,
                'show_reseller_label' => $t?->show_reseller_label ?: $section->show_reseller_label,
                'credit_info' => $t?->credit_info ?: $section->credit_info,
            ];
        }

        return null;
    }

    private function footerData(): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('footer_settings')) {
            return [];
        }

        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        $settings = FooterSetting::query()
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', array_unique([$locale, $fallback]));
            }])
            ->latest()
            ->first();

        $settingsArr = null;
        if ($settings) {
            $t = $settings->translation();
            $settingsArr = [
                'brand_text' => $t?->brand_text ?: $settings->brand_text,
                'crypto_note' => $t?->crypto_note ?: $settings->crypto_note,
                'phone' => $settings->phone,
                'email' => $settings->email,
                'address' => $t?->address ?: $settings->address,
                'rights_text' => $t?->rights_text ?: $settings->rights_text,
                'legal_note' => $t?->legal_note ?: $settings->legal_note,
            ];
        }

        $links = FooterLink::query()
            ->where('is_active', true)
            ->orderBy('group')
            ->orderBy('sort_order')
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', array_unique([$locale, $fallback]));
            }])
            ->get()
            ->map(function (FooterLink $link) {
                $t = $link->translation();
                return [
                    'group' => $link->group,
                    'label' => $t?->label ?: $link->label,
                    'url' => $link->url,
                ];
            })
            ->groupBy('group')
            ->toArray();

        $socials = SocialLink::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', array_unique([$locale, $fallback]));
            }])
            ->get()
            ->map(function (SocialLink $link) {
                $t = $link->translation();
                return [
                    'platform' => $t?->platform ?: $link->platform,
                    'url' => $link->url,
                    'icon_class' => $link->icon_class,
                ];
            })
            ->toArray();

        return [
            'settings' => $settingsArr,
            'links' => $links,
            'socials' => $socials,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function packages(): array
    {
        return \App\Models\Package::query()
            ->where('active', true)
            // Only IPTV rows; show both vendors
            ->where('type', 'iptv')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->orderByRaw("FIELD(vendor,'opplex','starshare'), COALESCE(sort_order, duration_months, id)")
            ->with('translations')
            ->get()
            ->map(fn($p) => $p->toIptvArray())
            ->values()
            ->all();
    }

    /** @return array<int,array<string,mixed>> */
    private function resellerPlans(): array
    {
        return \App\Models\Package::query()
            ->where('active', true)
            // Only Reseller rows; show both vendors
            ->where('type', 'reseller')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->orderByRaw("FIELD(vendor,'opplex','starshare'), COALESCE(sort_order, credits, id)")
            ->with('translations')
            ->get()
            ->map(fn($p) => $p->toResellerArray())
            ->values()
            ->all();
    }
    
    /** @return array<int,array<string,string>> */
    private function testimonials(): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('testimonials')) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale');

            return Testimonial::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->with(['translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', array_unique([$locale, $fallback]));
                }])
                ->get(['id', 'text', 'author_name', 'image'])
                ->map(function (Testimonial $t) {
                    $tr = $t->translation();
                    return [
                        'text' => $tr?->text ?: $t->text,
                        'author_name' => $tr?->author_name ?: $t->author_name,
                        'image' => $t->image,
                    ];
                })
                ->toArray();
        }

        return [
            ['text' => __('messages.testimonial_1'),  'author_name' => 'Amaan Khalid', 'image' => 'images/img-test-2.webp'],
            ['text' => __('messages.testimonial_2'),  'author_name' => 'Nouman Shahid', 'image' => 'images/img-test-3.webp'],
            ['text' => __('messages.testimonial_3'),  'author_name' => 'Michael',      'image' => 'images/resource/author-1.webp'],
            ['text' => __('messages.testimonial_4'),  'author_name' => 'Sarah',        'image' => 'images/resource/author-2.webp'],
            ['text' => __('messages.testimonial_5'),  'author_name' => 'Ameeq Khan',   'image' => 'images/img-test.webp'],
            ['text' => __('messages.testimonial_6'),  'author_name' => 'Luc Dubois',   'image' => 'images/resource/author-3.webp'],
            ['text' => __('messages.testimonial_7'),  'author_name' => 'Giulia Romano', 'image' => 'images/resource/author-5.webp'],
            ['text' => __('messages.testimonial_8'),  'author_name' => 'Oliver Smith', 'image' => 'images/resource/author-6.webp'],
            ['text' => __('messages.testimonial_9'),  'author_name' => 'Fatima B.',    'image' => 'images/resource/author-7.webp'],
            ['text' => __('messages.testimonial_10'), 'author_name' => 'Marco L.',     'image' => 'images/resource/author-8.webp'],
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function faqs(): array
    {
        return [
            [
                'question' => __('messages.faq.q1'),
                'answer'   => __('messages.faq.a1'),
                'images'   => [],
            ],
            [
                'question' => __('messages.faq.q2'),
                'answer'   => __('messages.faq.a2'),
                'images'   => [
                    ['url' => 'images/resource/samsung-tv-2.webp', 'caption' => __('messages.faq.samsung')],
                    ['url' => 'images/resource/mobileimg1.webp',   'caption' => __('messages.faq.mobile')],
                    ['url' => 'images/resource/onmobile.webp',     'caption' => __('messages.faq.front')],
                    ['url' => 'images/resource/onmobile2.webp',    'caption' => __('messages.faq.movies')],
                    ['url' => 'images/resource/onmobile3.webp',    'caption' => __('messages.faq.live')],
                    ['url' => 'images/resource/onmobile4.webp',    'caption' => __('messages.faq.play')],
                    ['url' => 'images/resource/onmobile5.webp',    'caption' => __('messages.faq.login_way')],
                    ['url' => 'images/resource/onmobile6.webp',    'caption' => __('messages.faq.login_page')],
                    ['url' => 'images/resource/onmobile7.webp',    'caption' => __('messages.faq.news')],
                    ['url' => 'images/resource/onmobile8.webp',    'caption' => __('messages.faq.settings')],
                    ['url' => 'images/resource/onmobile9.webp',    'caption' => __('messages.faq.series_play')],
                    ['url' => 'images/resource/onmobile10.webp',   'caption' => __('messages.faq.series_section')],
                    ['url' => 'images/resource/onmobile11.webp',   'caption' => __('messages.faq.on_screen')],
                    ['url' => 'images/resource/onmobile12.webp',   'caption' => __('messages.faq.series_playlist')],
                    ['url' => 'images/resource/alldevices.webp',   'caption' => __('messages.faq.devices')],
                ],
            ],
            [
                'question' => __('messages.faq.q3'),
                'answer'   => __('messages.faq.a3'),
                'images'   => [
                    ['url' => 'images/resource/loginguide.webp', 'caption' => __('messages.faq.login_guide')],
                ],
            ],
            ['question' => __('messages.faq.q4'),  'answer' => __('messages.faq.a4'),  'images' => []],
            ['question' => __('messages.faq.q5'),  'answer' => __('messages.faq.a5'),  'images' => []],
            ['question' => __('messages.faq.q6'),  'answer' => __('messages.faq.a6'),  'images' => []],
            ['question' => __('messages.faq.q7'),  'answer' => __('messages.faq.a7'),  'images' => []],
            ['question' => __('messages.faq.q8'),  'answer' => __('messages.faq.a8'),  'images' => []],
            ['question' => __('messages.faq.q9'),  'answer' => __('messages.faq.a9'),  'images' => []],
            ['question' => __('messages.faq.q10'), 'answer' => __('messages.faq.a10'), 'images' => []],
            ['question' => __('messages.faq.q11'), 'answer' => __('messages.faq.a11'), 'images' => []],
            ['question' => __('messages.faq.q12'), 'answer' => __('messages.faq.a12'), 'images' => []],
            ['question' => __('messages.faq.q13'), 'answer' => __('messages.faq.a13'), 'images' => []],
            ['question' => __('messages.faq.q14'), 'answer' => __('messages.faq.a14'), 'images' => []],
            ['question' => __('messages.faq.q15'), 'answer' => __('messages.faq.a15'), 'images' => []],
        ];
    }

    /**
     * Base platform matrix (raw).
     *
     * @return array<string, array<int, array<string,string>>>
     */
    private function platforms(): array
    {
        return [
            'android' => [
                ['version' => __('messages.app.iptv_smarters_pro'),            'file' => 'iptv_smarter_pro.apk',                  'image' => 'iptv_smarter.webp', 'keywords' => 'latest-IPTV-smarters-pro IPTV-app-download IPTV-APK-Download iptv-Smarters'],
                ['version' => __('messages.app.iptv_smarters_pro_3151'),       'file' => 'IPTV Smarters Pro_version-3.1.5.1.apk', 'image' => 'iptv_smarter.webp', 'keywords' => 'latest-IPTV-smarters-pro IPTV-app-download IPTV-APK-Download iptv-Smarters'],
                ['version' => __('messages.app.iptv_smarters_403'),            'file' => 'iptv-smarters-4.0.3.apk',               'image' => 'iptv_smarter.webp', 'keywords' => 'latest-IPTV-smarters-android IPTV-smarters-android-download IPTV-smarters-mobile IPTV-smarters-LCD IPTV-APK-download IPTV-smarters-for-Android IPTV-smarters-for-TV latest-IPTV-APK IPTV-for-Android-TV iptv-samsung-apk'],
                ['version' => __('messages.app.iptv_smarters_pro_403_latest'), 'file' => 'iptv-smarters-pro-4-0-3.apk',           'image' => 'iptv_smarter.webp', 'keywords' => 'latest-IPTV-smarters-android IPTV-smarters-android-download IPTV-smarters-mobile IPTV-smarters-LCD IPTV-APK-download IPTV-smarters-for-Android IPTV-smarters-for-TV latest-IPTV-APK IPTV-for-Android-TV iptv-samsung-apk'],
                ['version' => __('messages.app.opplex_app'),                    'file' => 'OPPLEXTV3.0.apk',                       'image' => 'opplextv.webp',     'keywords' => 'Opplex-TV-App Opplex-TV-APK-Download Opplex-IPTV-App Opplex-TV-for-Android Opplex-TV-Mobile-Streaming Android-IPTV-Opplex-App'],
                ['version' => __('messages.app.xtv_app'),                       'file' => 'XTVPLAYER3.0.apk',                      'image' => 'xtv.webp',          'keywords' => 'latest-XTV-live-iptv XTV-live-iptv-download XTV-live-iptv-APK XTV-IPTV-app-download XTV-live-APK-download XTV-live-streaming-app XTV-live-TV-APK free-XTV-iptv-APK XTV-iptv-pro-APK latest-XTV-IPTV-app'],
                ['version' => '9Xtream Player & Downloader',                    'file' => 'https://play.google.com/store/apps/details?id=com.divergentftb.xtreamplayeranddownloader', 'image' => '9xtream.webp', 'keywords' => '9Xtream-Android-App IPTV-9Xtream-Player Android-IPTV-Player-Download Xtream-Downloader-App'],
                ['version' => 'IBO Player (Android)',                           'file' => 'https://iboplayer.com/app_downloads/ibop.apk', 'image' => 'ibo.webp', 'keywords' => 'IBO-Player-Android IPTV-Player-APK IPTV-Player-for-Android IPTV-App-Download'],
                ['version' => 'Star Share New (Android)',                       'file' => 'starsharenew.apk',                      'image' => 'starshare.webp',    'keywords' => 'Star-Share-IPTV-App Star-Share-Android-APK IPTV-StarShare-Download'],
            ],
            'ios' => [
                ['version' => __('messages.app.smarters_player_ios'), 'file' => 'https://apps.apple.com/us/app/smarters-player-lite/id1628995509', 'image' => 'smarterlite.webp', 'keywords' => 'latest-IPTV-smarters-iOS IPTV-smarters-iOS-download IPTV-iOS-app ...'],
                ['version' => __('messages.app.player_000'),          'file' => 'https://apps.apple.com/app/000-player/id1665441224',              'image' => '000.webp',          'keywords' => '000-Player-iOS-download 000-Player-App-Store ...'],
                ['version' => '9Xtream Download & Play IPTV',         'file' => 'https://apps.apple.com/us/app/9xtream-download-play-iptv/id6504282945', 'image' => '9xtream.webp', 'keywords' => '9Xtream-iOS-App IPTV-Player-iPhone IPTV-Player-iPad ...'],
            ],
            'windows' => [
                ['version' => __('messages.app.iptv_smarters_windows'), 'file' => 'iptv-smarters-pro-1-1-1.exe',                  'image' => 'iptv_smarter.webp', 'keywords' => 'latest-IPTV-smarters-windows ...'],
                ['version' => 'IBO Player (Windows x64 10+)',           'file' => 'https://iboplayer.com/app_downloads/ibo_installer.exe', 'image' => 'ibo.webp', 'keywords' => 'IBO-Player-Windows IPTV-Player-for-PC ...'],
            ],
            'macos' => [
                ['version' => 'IBO Player (MacOS Intel x64)', 'file' => 'https://iboplayer.com/app_downloads/iboPlayer.dmg', 'image' => 'ibo.webp', 'keywords' => 'IBO-Player-Mac IPTV-Player-for-MacOS ...'],
            ],
            'linux' => [
                ['version' => 'IBO Player (Linux Debian/Ubuntu)', 'file' => 'https://iboplayer.com/app_downloads/ibo-player_1.0.0_amd64.snap', 'image' => 'ibo.webp', 'keywords' => 'IBO-Player-Linux IPTV-Player-Debian ...'],
            ],
        ];
    }

    /**
     * Add redirect + asset image URLs.
     *
     * @param array<string, array<int, array<string,string>>> $platforms
     * @return array<string, array<int, array<string,string>>>
     */
    private function enrichPlatforms(array $platforms): array
    {
        foreach ($platforms as &$apps) {
            foreach ($apps as &$app) {
                $isExternal  = filter_var($app['file'], FILTER_VALIDATE_URL);
                $downloadUrl = $isExternal ? $app['file'] : asset('downloads/' . $app['file']);

                $app['href']      = route('redirect.ad', ['target' => $downloadUrl]);
                $app['image_url'] = asset('images/' . $app['image']);
            }
            unset($app);
        }
        unset($apps);

        return $platforms;
    }

    /**
     * @return array{0:array<int,array<string,string>>,1:array<int,array<string,string>>}
     */
    private function dropdowns(): array
    {
        $packagesDropdown = [
            ['value' => 'monthly_USD_2.99',      'label' => __('messages.buynow.packages.monthly')],
            ['value' => 'half_yearly_USD_14.99', 'label' => __('messages.buynow.packages.half_yearly')],
            ['value' => 'yearly_USD_23.99',      'label' => __('messages.buynow.packages.yearly')],
        ];

        $resellerPanelPackagesDropdown = [
            ['value' => '20_credits_USD_16.99',   'label' => 'Starter Reseller Package 20 Credits - $16.99'],
            ['value' => '50_credits_USD_40.99',   'label' => 'Essential Reseller Bundle 50 Credits - $40.99'],
            ['value' => '100_credits_USD_77.99',  'label' => 'Pro Reseller Suite 100 Credits - $77.99'],
            ['value' => '200_credits_USD_149.99', 'label' => 'Advanced Reseller Toolkit 200 Credits - $149.99'],
        ];

        return [$packagesDropdown, $resellerPanelPackagesDropdown];
    }
}
