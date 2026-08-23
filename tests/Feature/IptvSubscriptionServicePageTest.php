<?php

namespace Tests\Feature;

use App\Support\UiData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Vite;
use ReflectionMethod;
use Tests\TestCase;

class IptvSubscriptionServicePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_iptv_subscription_service_page_renders_with_meta_and_core_sections(): void
    {
        $response = $this->get(route('iptv-subscription-service'));

        $response->assertOk();
        $response->assertSee('Everything You Want to Watch, Starting at $2.99', false);
        $response->assertSee('IPTV Subscription Service | Everything You Want from $2.99', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('id="pricing-section"', false);
        $response->assertSee('trial-cta', false);
        $response->assertDontSee('messages.iptv_subscription_service', false);
        $response->assertDontSee('meta.iptv-subscription-service', false);
    }

    public function test_iptv_subscription_service_links_cacheable_styles_and_uses_a_native_page_shell(): void
    {
        $response = $this->get(route('iptv-subscription-service'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertSame(1, preg_match('/<head\b.*?<\/head>/s', $html, $headMatches));
        $headHtml = $headMatches[0];

        foreach ([
            Vite::asset('resources/css/site-critical.css'),
            asset('css/iptv-service.css'),
            asset('css/document-product.css'),
        ] as $stylesheet) {
            $this->assertStringContainsString('href="'.$stylesheet, $headHtml);
        }
        foreach ([
            'iptv-subscription-critical-styles',
            'iptv-subscription-page-styles',
            'iptv-subscription-document-styles',
        ] as $styleId) {
            $this->assertStringNotContainsString('id="'.$styleId.'"', $headHtml);
        }

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

        $this->assertStringContainsString('id="iptv-subscription-channel-grid"', $html);
        $this->assertStringNotContainsString('sponsors-carousel owl-carousel', $html);
        $this->assertStringNotContainsString('class="native-carousel', $html);
        $this->assertStringContainsString('id="resellerToggle"', $html);
        $this->assertStringContainsString('function renderIptv()', $html);
        $this->assertStringContainsString('function renderReseller()', $html);

        $this->assertStringContainsString(Vite::asset('resources/js/native-shell.js'), $html);

        foreach ([
            'data-native-shell-config',
            'data-scroll-behavior="smooth"',
            'data-initial-scroll-update="immediate"',
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
            "button.setAttribute('role', 'button')",
            "submenu.style.removeProperty('display')",
            "submenu.style.display = willOpen ? 'block' : ''",
            'window.requestAnimationFrame(update)',
        ] as $nativeMarker) {
            $this->assertStringContainsString($nativeMarker, $nativeShell);
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
            $this->assertStringNotContainsString($layoutOrLegacyMarker, $nativeShell);
        }
    }

    public function test_existing_more_menu_gets_virtual_iptv_subscription_service_child(): void
    {
        $uiData = app(UiData::class);
        $method = new ReflectionMethod($uiData, 'withIptvSubscriptionMenuItem');
        $method->setAccessible(true);

        $menuItems = $method->invoke($uiData, [[
            'id' => 1,
            'label' => 'More',
            'url' => '#',
            'open_new_tab' => false,
            'children' => [],
        ]]);

        $more = collect($menuItems)->firstWhere('label', 'More');

        $this->assertNotNull($more);

        $child = collect($more['children'])->first(function (array $item): bool {
            return str_contains((string) ($item['url'] ?? ''), '/iptv-subscription-service');
        });

        $this->assertNotNull($child);
        $this->assertSame(__('messages.nav_iptv_subscription_service'), $child['label']);
    }
}
