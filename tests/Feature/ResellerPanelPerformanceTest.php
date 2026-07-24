<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class ResellerPanelPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_reseller_panel_inlines_required_styles_and_uses_native_interactions(): void
    {
        $response = $this->get('/reseller-panel');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            'reseller-panel-critical-styles' => Vite::content('resources/css/site-critical.css'),
            'reseller-panel-document-styles' => file_get_contents(public_path('css/document-commerce.css')),
        ] as $styleId => $expectedCss) {
            $this->assertIsString($expectedCss);
            $this->assertSame(
                1,
                preg_match('/<style id="'.preg_quote($styleId, '/').'">(.*?)<\/style>/s', $html, $matches)
            );
            $this->assertSame(hash('sha256', $expectedCss), hash('sha256', $matches[1]));
        }

        $this->assertStringNotContainsString('id="reseller-panel-page-styles"', $html);
        foreach ([
            Vite::asset('resources/css/site-critical.css'),
            asset('css/about.css'),
            asset('css/document-commerce.css'),
        ] as $stylesheet) {
            $this->assertStringNotContainsString('href="'.$stylesheet, $html);
        }

        $this->assertStringContainsString('data-native-carousel', $html);
        $this->assertStringContainsString('data-rtl="false"', $html);
        $this->assertStringNotContainsString('sponsors-carousel owl-carousel', $html);
        $this->assertStringContainsString('id="reseller-panel-native-control-styles"', $html);
        $this->assertStringContainsString('background: transparent;', $html);
        $this->assertMatchesRegularExpression('/<input[^>]+id="resellerToggle"[^>]+checked/', $html);
        $this->assertMatchesRegularExpression(
            '/id="normalPackages"\s+style="display:none!important"/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/id="resellerPackages" style="display:block"/',
            $html
        );
        $this->assertStringContainsString('const usePercentageCarouselOffsets = true;', $html);

        $this->assertResellerPanelDoesNotShipLegacyAssets($html);
        $this->assertResellerPanelUsesNativeShell($html);
    }

    public function test_localized_reseller_panel_inlines_its_page_styles_and_keeps_native_carousel(): void
    {
        app()->setLocale('es');

        $response = $this->get('/reseller-panel');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            'reseller-panel-critical-styles' => Vite::content('resources/css/site-critical.css'),
            'reseller-panel-page-styles' => file_get_contents(public_path('css/about.css')),
        ] as $styleId => $expectedCss) {
            $this->assertIsString($expectedCss);
            $this->assertSame(
                1,
                preg_match('/<style id="'.preg_quote($styleId, '/').'">(.*?)<\/style>/s', $html, $matches)
            );
            $this->assertSame(hash('sha256', $expectedCss), hash('sha256', $matches[1]));
        }

        $this->assertStringNotContainsString('id="reseller-panel-document-styles"', $html);
        $this->assertStringNotContainsString('href="'.asset('css/about.css'), $html);
        $this->assertStringNotContainsString('href="'.asset('css/document-commerce.css'), $html);
        $this->assertStringContainsString('data-native-carousel', $html);
        $this->assertStringContainsString('data-rtl="false"', $html);
        $this->assertStringNotContainsString('sponsors-carousel owl-carousel', $html);
        $this->assertStringContainsString('const usePercentageCarouselOffsets = true;', $html);

        $this->assertResellerPanelDoesNotShipLegacyAssets($html);
        $this->assertResellerPanelUsesNativeShell($html);

        app()->setLocale('ur');

        $rtlResponse = $this->get('/reseller-panel');
        $rtlResponse->assertOk();
        $this->assertStringContainsString('data-rtl="true"', $rtlResponse->getContent());
    }

    private function assertResellerPanelDoesNotShipLegacyAssets(string $html): void
    {
        foreach ([
            'https://code.jquery.com/jquery-1.12.4.min.js',
            'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
            'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.1/owl.carousel.min.js',
            Vite::asset('resources/js/site.js'),
        ] as $legacyAsset) {
            $this->assertStringNotContainsString($legacyAsset, $html);
        }
    }

    private function assertResellerPanelUsesNativeShell(string $html): void
    {
        $this->assertSame(
            1,
            preg_match('/<script id="reseller-panel-native-shell">(.*?)<\/script>/s', $html, $scriptMatches)
        );

        foreach ([
            'initResellerPanelScrollUi',
            'initResellerPanelMobileMenu',
            'initResellerPanelDropdowns',
            'initResellerPanelFaqs',
            'window.requestAnimationFrame(update)',
        ] as $nativeMarker) {
            $this->assertStringContainsString($nativeMarker, $scriptMatches[1]);
        }

        foreach ([
            'offsetHeight',
            'offsetWidth',
            'clientWidth',
            'clientHeight',
            'getBoundingClientRect',
            'getComputedStyle',
            'jQuery',
            'mCustomScrollbar',
        ] as $layoutOrLegacyMarker) {
            $this->assertStringNotContainsString($layoutOrLegacyMarker, $scriptMatches[1]);
        }
    }
}
