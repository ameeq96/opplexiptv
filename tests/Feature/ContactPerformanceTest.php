<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class ContactPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_contact_links_cacheable_styles_and_uses_a_native_phone_and_page_shell(): void
    {
        $response = $this->get('/contact');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            Vite::asset('resources/css/site-critical.css'),
            asset('css/contact.css'),
            asset('css/document-support.css'),
        ] as $stylesheet) {
            $this->assertStringContainsString('href="'.$stylesheet, $html);
        }
        foreach ([
            'contact-critical-styles',
            'contact-page-styles',
            'contact-document-styles',
        ] as $styleId) {
            $this->assertStringNotContainsString('id="'.$styleId.'"', $html);
        }

        foreach ([
            'intl-tel-input',
            'intlTelInput',
            'flags.png',
            'https://code.jquery.com/jquery-1.12.4.min.js',
            'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
            'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js',
            Vite::asset('resources/js/site.js'),
        ] as $unneededAsset) {
            $this->assertStringNotContainsString($unneededAsset, $html);
        }

        $this->assertMatchesRegularExpression(
            '/<select\s+id="phone-country-code"[^>]*class="ctx-phone__country"[^>]*>/s',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<option\s+value="\+92"[^>]+data-min-digits="10"[^>]+data-max-digits="10"[^>]+selected>/s',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<option\s+value="\+39"[^>]+data-min-digits="10"[^>]+data-max-digits="13"[^>]+data-preserve-leading-zero="true"[^>]*>/s',
            $html
        );
        $this->assertStringContainsString('OTHER +', $html);
        $this->assertStringContainsString('id="phone-format-hint"', $html);
        $this->assertMatchesRegularExpression(
            '/<input[^>]+type="tel"[^>]+name="phone"[^>]+id="phone"[^>]+pattern="\[\+0-9\(\) \.\-\]\{7,25\}"[^>]+required>/s',
            $html
        );
        $this->assertSame(1, preg_match('/<form\b[^>]*id="contact-form"[^>]*>/s', $html, $formMatches));
        $this->assertStringNotContainsString('novalidate', $formMatches[0]);

        $this->assertSame(
            1,
            preg_match('/<script id="contact-native-shell">(.*?)<\/script>/s', $html, $scriptMatches)
        );

        foreach ([
            'initContactScrollUi',
            'initContactMobileMenu',
            'initContactDropdowns',
            'initContactFaqs',
            'normalizeContactPhone',
            'hasValidCountryLength',
            'findDialOption',
            'dataset.preserveLeadingZero',
            "country.value = '';",
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

        $this->assertMatchesRegularExpression(
            '/const restoreInternationalValue = \(\) => \{.*const digits = rawValue\.replace\(\/\\\\D\/g, \'\'\);.*input\.value = digits\.slice\(dialCode\.length\);/s',
            $scriptMatches[1]
        );
    }
}
