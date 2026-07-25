<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class AboutPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_about_inlines_required_styles_and_uses_a_reflow_free_native_shell(): void
    {
        $response = $this->get('/about');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            'about-critical-styles' => Vite::content('resources/css/site-critical.css'),
            'about-document-styles' => file_get_contents(public_path('css/document-support.css')),
        ] as $styleId => $expectedCss) {
            $this->assertIsString($expectedCss);
            $this->assertSame(
                1,
                preg_match('/<style id="'.preg_quote($styleId, '/').'">(.*?)<\/style>/s', $html, $matches)
            );
            $this->assertSame(hash('sha256', $expectedCss), hash('sha256', $matches[1]));
        }

        $this->assertStringNotContainsString('id="about-page-styles"', $html);
        foreach ([
            Vite::asset('resources/css/site-critical.css'),
            asset('css/about.css'),
            asset('css/document-support.css'),
        ] as $stylesheet) {
            $this->assertStringNotContainsString('href="'.$stylesheet, $html);
        }

        $this->assertAboutDoesNotShipLegacyAssets($html);
        $this->assertAboutUsesNativeShell($html);
    }

    public function test_localized_about_uses_the_document_styles_and_native_shell(): void
    {
        app()->setLocale('es');

        $response = $this->get('/about');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            'about-critical-styles' => Vite::content('resources/css/site-critical.css'),
            'about-document-styles' => file_get_contents(public_path('css/document-support.css')),
        ] as $styleId => $expectedCss) {
            $this->assertIsString($expectedCss);
            $this->assertSame(
                1,
                preg_match('/<style id="'.preg_quote($styleId, '/').'">(.*?)<\/style>/s', $html, $matches)
            );
            $this->assertSame(hash('sha256', $expectedCss), hash('sha256', $matches[1]));
        }

        $this->assertStringNotContainsString('id="about-page-styles"', $html);
        $this->assertStringNotContainsString('href="'.asset('css/about.css'), $html);
        $this->assertStringNotContainsString('href="'.asset('css/document-support.css'), $html);
        $this->assertStringContainsString('data-native-carousel', $html);
        $this->assertStringNotContainsString('sponsors-carousel owl-carousel', $html);

        $this->assertAboutDoesNotShipLegacyAssets($html);
        $this->assertAboutUsesNativeShell($html);
    }

    private function assertAboutDoesNotShipLegacyAssets(string $html): void
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

    private function assertAboutUsesNativeShell(string $html): void
    {
        $this->assertSame(
            1,
            preg_match('/<script id="about-native-shell">(.*?)<\/script>/s', $html, $scriptMatches)
        );

        foreach ([
            'initAboutScrollUi',
            'initAboutMobileMenu',
            'initAboutDropdowns',
            'initAboutFaqs',
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
