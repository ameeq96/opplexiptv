<?php

namespace Tests\Feature\Performance;

use App\Services\ImageService;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class LcpHintsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function setRouteName(string $routeName): void
    {
        $route = new Route(['GET'], '/'.str_replace('.', '/', $routeName), []);
        $route->name($routeName);

        request()->setRouteResolver(fn () => $route);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function renderHeadForRoute(string $routeName, array $data = []): string
    {
        $this->setRouteName($routeName);

        return view('includes.head', array_replace([
            'displayMovies' => collect(),
            'isRtl' => false,
        ], $data))->render();
    }

    private function renderFooterForRoute(string $routeName): string
    {
        $this->setRouteName($routeName);

        return view('includes.footer', [
            'footer' => [],
            'isMobile' => false,
            'isRtl' => false,
        ])->render();
    }

    public function test_home_preloads_first_hero_image_and_skips_phone_assets(): void
    {
        Cache::put('ui:tmdb:trending:all:day:en:p1', [], now()->addMinutes(10));

        $html = $this->renderHeadForRoute('home', [
            'displayMovies' => collect([[
                'webp_image_url' => 'https://image.tmdb.org/t/p/w780/speed-check.jpg',
            ]]),
        ]);

        $this->assertStringContainsString('rel="preconnect" href="https://image.tmdb.org"', $html);
        $this->assertStringContainsString('href="https://image.tmdb.org/t/p/w780/speed-check.jpg"', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertSame(1, substr_count($html, 'href="https://image.tmdb.org/t/p/w780/speed-check.jpg"'));
        $this->assertSame(1, substr_count($html, 'href="https://image.tmdb.org"'));
        $this->assertStringNotContainsString('intl-tel-input@19.5.7', $html);
    }

    public function test_home_head_inlines_mobile_hero_lcp_visibility_rules(): void
    {
        $html = $this->renderHeadForRoute('home');

        $this->assertStringContainsString('.hero-section-mobile .description', $html);
        $this->assertStringContainsString('visibility: visible !important', $html);
        $this->assertStringContainsString('animation: none !important', $html);
    }

    public function test_critical_font_preloads_match_vite_built_font_urls(): void
    {
        $html = $this->renderHeadForRoute('home');

        foreach ([
            'public/fonts/poppins/poppins-v21-latin-regular.woff2',
            'public/fonts/poppins/poppins-v21-latin-700.woff2',
            'public/fonts/poppins/poppins-v21-latin-600.woff2',
            'public/fonts/poppins/poppins-v21-latin-500.woff2',
            'public/fonts/Linearicons-Free.woff2',
        ] as $font) {
            $this->assertStringContainsString('href="'.Vite::asset($font).'"', $html);
        }

        $this->assertStringContainsString('font-weight:400', file_get_contents(public_path('css/fonts.css')));
        $this->assertStringNotContainsString(asset('fonts/poppins/poppins-v21-latin-700.woff2'), $html);
    }

    public function test_home_hero_renders_without_skeleton_loader_on_mobile_and_desktop(): void
    {
        $mobileHtml = view('includes._slider', [
            'isMobile' => true,
            'isRtl' => false,
            'movies' => collect(),
        ])->render();

        $desktopHtml = view('includes._slider', [
            'isMobile' => false,
            'isRtl' => false,
            'useNativeCarousel' => true,
            'movies' => collect([[
                'webp_image_url' => 'https://image.tmdb.org/t/p/w780/speed-check.jpg',
                'safe_title' => 'Speed Check',
                'safe_overview' => 'Performance test slide',
            ]]),
        ])->render();

        $this->assertStringContainsString('class="hero-section-mobile"', $mobileHtml);
        $this->assertStringContainsString('class="description"', $mobileHtml);
        $this->assertStringContainsString('class="main-slider-two native-home-hero"', $desktopHtml);
        $this->assertStringNotContainsString('data-skeleton-section', $mobileHtml.$desktopHtml);
        $this->assertStringNotContainsString('section-skeleton__overlay', $mobileHtml.$desktopHtml);
        $this->assertStringNotContainsString('skeleton-section', $mobileHtml.$desktopHtml);
    }

    public function test_first_desktop_hero_image_keeps_priority_attributes_in_both_sliders(): void
    {
        foreach ([true, false] as $useNativeCarousel) {
            $html = view('includes._slider', [
                'isMobile' => false,
                'isRtl' => false,
                'useNativeCarousel' => $useNativeCarousel,
                'movies' => collect([[
                    'webp_image_url' => 'https://image.tmdb.org/t/p/w780/speed-check.jpg',
                    'safe_title' => 'Speed Check',
                    'safe_overview' => 'Performance test slide',
                ]]),
            ])->render();

            $this->assertMatchesRegularExpression(
                '/<img[^>]+width="960"[^>]+height="540"[^>]+loading="eager"[^>]+decoding="async"[^>]+fetchpriority="high"[^>]*>/s',
                $html
            );
            $this->assertStringNotContainsString('loading="lazy"', $html);
        }
    }

    public function test_non_critical_styles_are_deferred_with_noscript_fallbacks(): void
    {
        $html = $this->renderHeadForRoute('home');
        $deferredHref = Vite::asset('resources/css/site-deferred.css');
        $criticalEntry = file_get_contents(resource_path('css/site-critical.css'));
        $deferredEntry = file_get_contents(resource_path('css/site-deferred.css'));

        $this->assertStringContainsString(
            '<link rel="stylesheet" href="'.$deferredHref.'" media="print" onload="this.media=\'all\'">',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<noscript>.*<link rel="stylesheet" href="'.preg_quote($deferredHref, '/').'">.*<\/noscript>/s',
            $html
        );

        foreach ([
            'discount-wheel.css',
            'voice-assistant.css',
            'animate.css',
            'owl.css',
            'swiper.css',
            'jquery-ui.css',
            'custom-animate.css',
            'jquery.fancybox.min.css',
            'jquery.mCustomScrollbar.min.css',
        ] as $style) {
            $this->assertStringContainsString('../../public/css/'.$style, $deferredEntry);
        }

        foreach (['bootstrap.css', 'style.css', 'global.css', 'header.css', 'responsive.css', 'fonts.css'] as $style) {
            $this->assertStringContainsString('../../public/css/'.$style, $criticalEntry);
        }

        $this->assertStringNotContainsString('bootstrap@4.6.2/dist/css/bootstrap.min.css', $html);
        $this->assertStringNotContainsString('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css', $html);
    }

    public function test_voice_assistant_and_discount_wheel_scripts_are_delayed(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/default.blade.php'));
        $footer = file_get_contents(resource_path('views/includes/footer.blade.php'));

        $this->assertStringContainsString("resources/js/voice-assistant.js", $layout);
        $this->assertStringContainsString("s.type = 'module';", $layout);
        $this->assertStringContainsString('}, 4000);', $layout);
        $this->assertStringNotContainsString('<script src="{{ v(\'js/voice-assistant.js\') }}" defer></script>', $layout);

        $this->assertStringContainsString("resources/js/discount-wheel.js", $footer);
        $this->assertStringContainsString("s.type = 'module';", $footer);
        $this->assertStringContainsString('}, 5000);', $footer);
        $this->assertStringNotContainsString('<script src="{{ v(\'js/discount-wheel.js\') }}" defer></script>', $footer);
        $this->assertStringContainsString("@vite('resources/js/site.js')", $footer);
        $this->assertStringNotContainsString("v('js/nav-tool.js')", $footer);
        $this->assertStringNotContainsString("v('js/script.js')", $footer);
    }

    public function test_below_fold_home_images_use_lazy_async_decoding(): void
    {
        $unlimited = view('includes._we-provide-unlimited', [
            'isMobile' => false,
            'isRtl' => false,
            'features' => [],
        ])->render();
        $services = view('includes._services', [
            'isRtl' => false,
            'useNativeCarousel' => true,
            'serviceCards' => [[
                'title' => 'Sports',
                'description' => 'Live sports',
                'link' => route('packages'),
                'icon' => null,
            ]],
        ])->render();
        $channels = view('includes._channels-carousel', [
            'logos' => ['images/resource/5.webp'],
            'useNativeCarousel' => true,
        ])->render();
        $testimonials = view('includes._testimonials', [
            'testimonials' => [[
                'text' => 'Great service',
                'author_name' => 'Customer',
                'image' => 'images/placeholder.webp',
            ]],
            'useNativeCarousel' => true,
        ])->render();

        foreach ([$unlimited, $services, $channels, $testimonials] as $html) {
            $this->assertStringContainsString('loading="lazy" decoding="async"', $html);
        }

        $footer = $this->renderFooterForRoute('home');
        $this->assertStringContainsString('width="250" height="65" loading="lazy" decoding="async"', $footer);
    }

    public function test_initial_pricing_render_hides_inactive_vendor_cards_before_javascript_runs(): void
    {
        $html = view('includes._best-packages', [
            'isMobile' => true,
            'isRtl' => false,
            'containerClass' => 'centered',
            'pricingSection' => [
                'heading' => 'Pricing',
                'subheading' => 'Packages',
            ],
            'packages' => [
                [
                    'vendor' => 'opplex',
                    'title' => 'Monthly - $10',
                    'price' => '$10',
                    'features' => ['One connection'],
                ],
                [
                    'vendor' => 'starshare',
                    'title' => 'Monthly - $11',
                    'price' => '$11',
                    'features' => ['One connection'],
                ],
            ],
            'resellerPlans' => [],
        ])->render();

        $this->assertMatchesRegularExpression(
            '/data-type="iptv" data-vendor="starshare"\s+style="display:none!important"/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/data-type="iptv" data-vendor="opplex"\s+style="display:none!important"/',
            $html
        );
    }

    public function test_image_service_returns_generated_webp_when_it_already_exists(): void
    {
        Queue::fake();

        $imageUrl = 'https://image.tmdb.org/t/p/w780/existing-generated.jpg';
        $width = 960;
        $height = 540;
        $quality = 70;
        $webpPath = 'webp_images/' . md5($imageUrl . $width . $height . $quality) . '.webp';
        $fullPath = public_path($webpPath);
        $dir = dirname($fullPath);
        $createdDir = ! is_dir($dir);

        if ($createdDir) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, 'webp');

        try {
            $url = app(ImageService::class)->toWebp($imageUrl, $width, $height, $quality);

            $this->assertSame(asset($webpPath), $url);
            Queue::assertNothingPushed();
        } finally {
            @unlink($fullPath);

            if ($createdDir) {
                @rmdir($dir);
            }
        }
    }

    public function test_views_footer_and_css_do_not_output_skeleton_loader_hooks(): void
    {
        $paths = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($paths as $path) {
            if ($path->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($path->getPathname());

            $this->assertStringNotContainsString('data-skeleton-section', $contents, $path->getPathname());
            $this->assertStringNotContainsString('section-skeleton', $contents, $path->getPathname());
            $this->assertStringNotContainsString('skeleton-section', $contents, $path->getPathname());
        }

        $css = file_get_contents(public_path('css/style.css'));

        $this->assertStringNotContainsString('section-skeleton', $css);
        $this->assertStringNotContainsString('skeleton-section', $css);
    }

    public function test_page_title_routes_preload_their_lcp_backgrounds(): void
    {
        $routes = [
            'about' => ['images/background/7-lcp.webp', 'images/background/7-mobile.webp'],
            'packages' => ['images/background/9-lcp.webp', 'images/background/9-mobile.webp'],
            'iptv-subscription-service' => ['images/background/9-lcp.webp', 'images/background/9-mobile.webp'],
            'pricing' => ['images/background/7-lcp.webp', 'images/background/7-mobile.webp'],
            'faqs' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
            'contact' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
            'reseller-panel' => ['images/background/7-lcp.webp', 'images/background/7-mobile.webp'],
            'iptv-applications' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
            'shop' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
            'buynow' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
            'buynowpanel' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
        ];

        foreach ($routes as $route => [$desktopAsset, $mobileAsset]) {
            $html = $this->renderHeadForRoute($route);

            $this->assertStringContainsString('rel="preload" as="image"', $html);
            $this->assertStringContainsString('href="'.asset($desktopAsset).'"', $html);
            $this->assertStringContainsString('href="'.asset($mobileAsset).'"', $html);
            $this->assertStringContainsString('media="(max-width: 767px)"', $html);
            $this->assertStringContainsString('media="(min-width: 768px)"', $html);
            $this->assertStringContainsString('type="image/webp"', $html);
        }
    }

    public function test_page_title_uses_responsive_lcp_background_variants(): void
    {
        $html = view('components.page-title', [
            'title' => 'Packages',
            'breadcrumbs' => [],
            'background' => 'images/background/9.webp',
            'desktopBackground' => 'images/background/9-lcp.webp',
            'mobileBackground' => 'images/background/9-mobile.webp',
            'rtl' => false,
            'ariaLabel' => null,
        ])->render();

        $this->assertStringContainsString('--page-title-bg-desktop: url(\''.asset('images/background/9-lcp.webp').'\')', $html);
        $this->assertStringContainsString('--page-title-bg-mobile: url(\''.asset('images/background/9-mobile.webp').'\')', $html);
        $this->assertStringNotContainsString('background-image: url(\''.asset('images/background/9.webp').'\')', $html);
    }

    public function test_phone_input_assets_only_load_on_phone_form_pages(): void
    {
        $contactHead = $this->renderHeadForRoute('contact');
        $contactFooter = $this->renderFooterForRoute('contact');
        $checkoutHead = $this->renderHeadForRoute('checkout');
        $checkoutFooter = $this->renderFooterForRoute('checkout');
        $packagesHead = $this->renderHeadForRoute('packages');
        $packagesFooter = $this->renderFooterForRoute('packages');
        $configureHead = $this->renderHeadForRoute('configure');
        $configureFooter = $this->renderFooterForRoute('configure');

        $this->assertStringContainsString('intl-tel-input@19.5.7', $contactHead);
        $this->assertStringContainsString('intl-tel-input@19.5.7', $contactFooter);
        $this->assertStringContainsString('intl-tel-input@19.5.7', $checkoutHead);
        $this->assertStringContainsString('intl-tel-input@19.5.7', $checkoutFooter);

        $this->assertStringNotContainsString('intl-tel-input@19.5.7', $packagesHead);
        $this->assertStringNotContainsString('intl-tel-input@19.5.7', $packagesFooter);
        $this->assertStringNotContainsString('intl-tel-input@19.5.7', $configureHead);
        $this->assertStringNotContainsString('intl-tel-input@19.5.7', $configureFooter);
    }

    public function test_checkout_routes_keep_checkout_styles_blocking(): void
    {
        foreach (['configure', 'checkout'] as $route) {
            $html = $this->renderHeadForRoute($route);

            $this->assertStringContainsString(
                '<link rel="stylesheet" href="'.Vite::asset('resources/css/checkout.css').'" media="all">',
                $html
            );
            $this->assertStringNotContainsString('<link rel="preload" href="'.asset('css/checkout.css'), $html);
        }

        $packagesHtml = $this->renderHeadForRoute('packages');

        $this->assertStringNotContainsString(
            '<link rel="stylesheet" href="'.Vite::asset('resources/css/checkout.css').'" media="all">',
            $packagesHtml
        );
        $this->assertStringContainsString(
            '<link rel="stylesheet" href="'.Vite::asset('resources/css/checkout.css').'" media="print" onload="this.media=\'all\'">',
            $packagesHtml
        );
    }

    public function test_non_movie_routes_do_not_preconnect_to_tmdb(): void
    {
        $html = $this->renderHeadForRoute('terms-of-service');

        $this->assertStringNotContainsString('https://image.tmdb.org', $html);
    }

    public function test_service_worker_is_a_local_unregistering_worker(): void
    {
        $contents = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString('registration.unregister', $contents);
        $this->assertStringContainsString('caches.delete', $contents);
        $this->assertStringNotContainsString('importScripts', $contents);
        $this->assertStringNotContainsString('eval(', $contents);
        $this->assertStringNotContainsString('aiharsoreersu.net', $contents);
    }
}
