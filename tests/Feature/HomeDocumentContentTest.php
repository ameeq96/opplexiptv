<?php

namespace Tests\Feature;

use App\Models\ShopProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class HomeDocumentContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        ShopProduct::create([
            'name' => 'Streaming Device',
            'asin' => 'TESTDEVICE',
            'link' => 'https://example.com/device',
            'image' => 'B0F7RXTN1Y.webp',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_english_home_follows_the_document_copy_and_section_order(): void
    {
        Cache::put('ui:tmdb:v2:trending:all:day:en:p1', [], now()->addMinutes(10));

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Best IPTV Subscription Service Provider | Live TV, Sports & 4K Streaming');
        $response->assertSeeText('Watch What You Want, When You Want');
        $response->assertSeeText('Smart IPTV Plans. No Hidden Fees. Enjoy 4K Streaming & Free Trial');
        $response->assertSeeText('Everything You Need from an IPTV Service');
        $response->assertSeeText('Works on Every Device You Already Own');
        $response->assertSeeText('Hear What Viewers Say About Opplex IPTV');
        $response->assertSeeText('Alex McCarthy');
        $response->assertSeeText('What exactly is Opplex IPTV?');
        $response->assertSeeText('Numbers That Speak for Themselves!');
        $response->assertSeeText('Try Opplex IPTV For Free | No Commitment Needed');

        $html = $response->getContent();
        $homeSplitImage = $this->homeSplitImageTag($html);

        $this->assertStringContainsString('<style id="home-document-styles">', $html);
        $this->assertStringContainsString('.home-document-devices', $html);
        $this->assertStringNotContainsString('/css/home-document.css', $html);
        foreach ([
            'movie-night-tv-480.webp',
            'movie-night-tv-720.webp',
            'movie-night-tv-1024.webp',
            'movie-night-tv-1280.webp',
            'sizes="(min-width: 1340px) 640px, (min-width: 992px) calc(50vw - 30px), calc(100vw - 30px)"',
            'loading="eager"',
            'fetchpriority="high"',
            'decoding="async"',
            'width="1024" height="1024"',
        ] as $expected) {
            $this->assertStringContainsString($expected, $homeSplitImage);
        }
        $this->assertSame(
            1,
            substr_count($html, '<h1>Best IPTV Subscription Service Provider | Live TV, Sports &amp; 4K Streaming</h1>'),
            'The English document hero should render once, even when several slider images are available.'
        );
        $this->assertMarkersAreOrdered($html, [
            '<section class="main-slider-two native-home-hero',
            '<section class="home-split-section',
            '<section class="pricing-section style-two',
            '<div class="home-products-shell">',
            '<section class="network-section unlimited-showcase',
            '<section class="services-section-two',
            '<section class="home-document-devices"',
            '<section class="testimonial-section',
            '<section class="faq-section"',
            '<section class="home-document-stats"',
            '<section class="home-map-section',
            '<section class="trial-cta home-document-trial',
        ]);
    }

    public function test_non_english_home_keeps_the_existing_flow(): void
    {
        Cache::put('ui:tmdb:v2:trending:all:day:es:p1', [], now()->addMinutes(10));
        app()->setLocale('es');

        $route = new Route(['GET'], '/', []);
        $route->name('home');
        request()->setRouteResolver(fn () => $route);

        $html = view('pages.home', [
            'homeProducts' => collect(),
            'homeAffiliateProducts' => collect(),
            'isRtl' => false,
        ])->render();
        $homeSplitImage = $this->homeSplitImageTag($html);

        $this->assertStringNotContainsString('home-document-devices', $html);
        $this->assertStringNotContainsString('home-document-stats', $html);
        $this->assertStringNotContainsString('home-document-trial', $html);
        $this->assertStringNotContainsString('home-document-styles', $html);
        $this->assertStringNotContainsString('home-document.css', $html);
        $this->assertStringNotContainsString('Watch What You Want, When You Want', $html);
        $this->assertStringContainsString('loading="lazy"', $homeSplitImage);
        $this->assertStringContainsString('fetchpriority="low"', $homeSplitImage);
        $this->assertLessThan(
            strpos($html, 'home-split-section'),
            strpos($html, 'pricing-section style-two'),
            'The non-English homepage should retain pricing before the split section.'
        );
    }

    public function test_home_uses_a_native_shell_without_the_legacy_jquery_stack(): void
    {
        Cache::put('ui:tmdb:v2:trending:all:day:en:p1', [], now()->addMinutes(10));

        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            'https://code.jquery.com/jquery-1.12.4.min.js',
            'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
            'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-appear/0.1/jquery.appear.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/paroller.js/1.4.6/jquery.paroller.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.1/owl.carousel.min.js',
            Vite::asset('resources/js/site.js'),
        ] as $legacyAsset) {
            $this->assertStringNotContainsString($legacyAsset, $html);
        }

        $this->assertSame(
            1,
            preg_match('/<script id="home-native-shell">(.*?)<\/script>/s', $html, $scriptMatches)
        );

        foreach ([
            'initHomeScrollUi',
            'initHomeMobileMenu',
            'initHomeDropdowns',
            'initHomeFaqs',
            "submenu.style.removeProperty('display')",
            "submenu.style.display = willOpen ? 'block' : ''",
            "button.setAttribute('role', 'button')",
            'window.requestAnimationFrame(update)',
            'window.scrollTo(0, 0)',
        ] as $nativeMarker) {
            $this->assertStringContainsString($nativeMarker, $scriptMatches[1]);
        }

        foreach ([
            'offsetHeight',
            'offsetWidth',
            'offsetTop',
            'clientWidth',
            'clientHeight',
            'getBoundingClientRect',
            'getComputedStyle',
            'jQuery',
            'mCustomScrollbar',
            'owlCarousel',
        ] as $layoutOrLegacyMarker) {
            $this->assertStringNotContainsString($layoutOrLegacyMarker, $scriptMatches[1]);
        }

        $this->assertStringContainsString('const usePercentageCarouselOffsets = true;', $html);
        $this->assertStringContainsString("window.matchMedia('(max-width: 767px)').matches", $html);
        $this->assertStringContainsString('.mobile-menu .dropdown-btn', $html);
        $this->assertStringContainsString(
            'const offsetPercent = index * (100 / visibleItems);',
            $html
        );
    }

    public function test_english_affiliate_introduction_remains_when_the_catalog_is_empty(): void
    {
        ShopProduct::query()->delete();
        Cache::put('ui:tmdb:v2:trending:all:day:en:p1', [], now()->addMinutes(10));

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Gear Up for Better Streaming');
        $response->assertSeeText('The right device makes a real difference.');
    }

    /** @param array<int,string> $markers */
    private function assertMarkersAreOrdered(string $html, array $markers): void
    {
        $previousPosition = -1;

        foreach ($markers as $marker) {
            $position = strpos($html, $marker);
            $this->assertNotFalse($position, "Missing homepage marker: {$marker}");
            $this->assertGreaterThan($previousPosition, $position, "Homepage marker is out of order: {$marker}");
            $previousPosition = $position;
        }
    }

    private function homeSplitImageTag(string $html): string
    {
        $matched = preg_match('/<img[^>]+movie-night-tv-1024\.webp[^>]*>/s', $html, $matches);

        $this->assertSame(1, $matched, 'The responsive homepage split image is missing.');

        return $matches[0];
    }
}
