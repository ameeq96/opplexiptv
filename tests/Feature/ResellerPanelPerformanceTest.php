<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class ResellerPanelPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_reseller_panel_links_cacheable_styles_and_uses_native_interactions(): void
    {
        $response = $this->get('/reseller-panel');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            Vite::asset('resources/css/site-critical.css'),
            asset('css/document-commerce.css'),
        ] as $stylesheet) {
            $this->assertStringContainsString('href="'.$stylesheet, $html);
        }
        foreach ([
            'reseller-panel-critical-styles',
            'reseller-panel-document-styles',
            'reseller-panel-page-styles',
        ] as $styleId) {
            $this->assertStringNotContainsString('id="'.$styleId.'"', $html);
        }
        $this->assertStringNotContainsString('href="'.asset('css/about.css'), $html);

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

    public function test_localized_reseller_panel_links_document_styles_and_keeps_native_carousel(): void
    {
        app()->setLocale('es');

        $response = $this->get('/reseller-panel');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            Vite::asset('resources/css/site-critical.css'),
            asset('css/document-commerce.css'),
        ] as $stylesheet) {
            $this->assertStringContainsString('href="'.$stylesheet, $html);
        }
        foreach ([
            'reseller-panel-critical-styles',
            'reseller-panel-document-styles',
            'reseller-panel-page-styles',
        ] as $styleId) {
            $this->assertStringNotContainsString('id="'.$styleId.'"', $html);
        }
        $this->assertStringNotContainsString('href="'.asset('css/about.css'), $html);
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
        $this->assertStringContainsString(Vite::asset('resources/js/native-shell.js'), $html);

        foreach ([
            'data-native-shell-config',
            'data-scroll-behavior="instant"',
            'data-initial-scroll-update="animation-frame"',
            'data-manage-dropdown-display="true"',
            'data-enrich-faq-aria="true"',
        ] as $configMarker) {
            $this->assertStringContainsString($configMarker, $html);
        }

        $nativeShell = file_get_contents(resource_path('js/native-shell.js'));
        $this->assertIsString($nativeShell);

        foreach ([
            'initScrollUi',
            'initMobileMenu',
            'initDropdowns',
            'initFaqs',
            'window.requestAnimationFrame(update)',
        ] as $nativeMarker) {
            $this->assertStringContainsString($nativeMarker, $nativeShell);
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
            $this->assertStringNotContainsString($layoutOrLegacyMarker, $nativeShell);
        }
    }
}
