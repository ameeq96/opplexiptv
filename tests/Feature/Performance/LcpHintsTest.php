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

    public function test_page_title_routes_preload_their_lcp_backgrounds(): void
    {
        $routes = [
            'about' => 'images/background/7.webp',
            'packages' => 'images/background/9.webp',
            'pricing' => 'images/background/7.webp',
            'faqs' => 'images/background/10.webp',
            'contact' => 'images/background/10.webp',
            'reseller-panel' => 'images/background/7.webp',
            'iptv-applications' => 'images/background/10.webp',
            'shop' => 'images/background/10.webp',
            'buynow' => 'images/background/10.webp',
            'buynowpanel' => 'images/background/10.webp',
        ];

        foreach ($routes as $route => $asset) {
            $html = $this->renderHeadForRoute($route);

            $this->assertStringContainsString('rel="preload" as="image"', $html);
            $this->assertStringContainsString('href="'.asset($asset).'"', $html);
            $this->assertStringContainsString('type="image/webp"', $html);
        }
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
        $this->assertStringContainsString('<link rel="preload" href="'.asset('css/checkout.css'), $packagesHtml);
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
