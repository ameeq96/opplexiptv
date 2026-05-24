<?php

namespace Tests\Feature\Performance;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
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
        $this->assertStringNotContainsString('intl-tel-input@19.5.7', $html);
    }

    public function test_home_head_inlines_mobile_hero_lcp_visibility_rules(): void
    {
        $html = $this->renderHeadForRoute('home');

        $this->assertStringContainsString('.hero-section-mobile .description', $html);
        $this->assertStringContainsString('visibility: visible !important', $html);
        $this->assertStringContainsString('animation: none !important', $html);
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

            $this->assertMatchesRegularExpression(
                '/<link rel="stylesheet" href="[^"]*\/css\/checkout\.css[^"]*" media="all">/',
                $html
            );
            $this->assertStringNotContainsString('<link rel="preload" href="'.asset('css/checkout.css'), $html);
        }

        $packagesHtml = $this->renderHeadForRoute('packages');

        $this->assertDoesNotMatchRegularExpression(
            '/<link rel="stylesheet" href="[^"]*\/css\/checkout\.css[^"]*" media="all">/',
            $packagesHtml
        );
        $this->assertMatchesRegularExpression(
            '/<link rel="stylesheet" href="[^"]*\/css\/checkout\.css[^"]*" media="print" onload="this\.media=\'all\'">/',
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
