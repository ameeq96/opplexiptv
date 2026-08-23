<?php

namespace Tests\Feature;

use App\Models\Digital\DigitalProduct;
use App\Models\ShopProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class EnglishDocumentPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider documentPageProvider
     * @param array<int,string> $needles
     */
    public function test_english_document_pages_render_their_main_folds(string $path, array $needles): void
    {
        Cache::flush();
        Cache::put('ui:tmdb:v2:trending:all:day:en:p1', [], now()->addMinutes(10));

        $response = $this->get($path);

        $response->assertOk();
        foreach ($needles as $needle) {
            $response->assertSeeText($needle);
        }

    }

    /** @return array<string,array{string,array<int,string>}> */
    public static function documentPageProvider(): array
    {
        return [
            'packages' => ['/packages', [
                'Most In-Demand IPTV Subscriptions Service',
                'How to Buy an IPTV Subscription: 3 Easy Steps',
                'Try Our IPTV Free Trial',
            ]],
            'pricing' => ['/pricing', [
                'IPTV Subscription Pricing',
                'Not Sure Which Plan to Pick?',
                'Paying Is Safe and Straightforward',
            ]],
            'reseller' => ['/reseller-panel', [
                'Build Your Own IPTV Business with Opplex',
                'How the Credit System Works',
                'What Comes with Your Reseller Account',
            ]],
            'applications' => ['/iptv-applications', [
                'Best IPTV Apps for Android, iOS, Windows & Smart TV',
                'Best IPTV Apps for Android',
                'Common Questions About IPTV Apps',
            ]],
            'movies' => ['/movies', [
                '50,000+ Movies and Series',
                "What's in the Library",
                'How to Browse and Watch Movies on Opplex',
            ]],
            'subscription' => ['/iptv-subscription-service', [
                'Everything You Want to Watch, Starting at $2.99',
                'Three Steps from Signup to Streaming',
                'IPTV vs Cable',
            ]],
            'faqs' => ['/faqs', [
                'Frequently Asked Questions',
                'About Opplex IPTV',
                'Streaming Quality & Performance',
            ]],
            'about' => ['/about', [
                'We Started Opplex Because We Were Tired of Bad IPTV Too',
                'Four Years of Keeping the Channels On',
                'How Opplex Got Here',
            ]],
            'contact' => ['/contact', [
                'Get in Touch',
                'Not Sure What to Write?',
                'When You Can Expect a Reply',
            ]],
        ];
    }

    public function test_packages_links_document_styles_and_uses_the_native_shell(): void
    {
        Cache::flush();
        Cache::put('ui:tmdb:v2:trending:all:day:en:p1', [], now()->addMinutes(10));

        $response = $this->get('/packages');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString(
            'href="'.asset('css/document-commerce.css'),
            $html
        );
        $this->assertStringNotContainsString('id="packages-document-styles"', $html);

        foreach ([
            'https://code.jquery.com/jquery-1.12.4.min.js',
            'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
            'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js',
            Vite::asset('resources/js/site.js'),
        ] as $legacyAsset) {
            $this->assertStringNotContainsString($legacyAsset, $html);
        }

        $this->assertStringContainsString(Vite::asset('resources/js/native-shell.js'), $html);

        foreach ([
            'data-native-shell-config',
            'data-scroll-behavior="smooth"',
            'data-initial-scroll-update="immediate"',
            'data-manage-dropdown-display="false"',
            'data-enrich-faq-aria="false"',
        ] as $configMarker) {
            $this->assertStringContainsString($configMarker, $html);
        }

        $nativeShell = file_get_contents(resource_path('js/native-shell.js'));
        $this->assertIsString($nativeShell);

        foreach ([
            'initMobileMenu',
            'initFaqs',
            'initScrollUi',
            'window.requestAnimationFrame(update)',
        ] as $nativeMarker) {
            $this->assertStringContainsString($nativeMarker, $nativeShell);
        }

        foreach (['offsetHeight', 'offsetWidth', 'clientWidth', 'getBoundingClientRect'] as $layoutRead) {
            $this->assertStringNotContainsString($layoutRead, $nativeShell);
        }
    }

    public function test_shop_prioritises_all_eight_document_products_on_the_first_page(): void
    {
        Cache::flush();

        $devices = [
            ['B08CRV62C4', 'Android TV Box 4GB RAM 32GB ROM'],
            ['B0BP9SNVH9', 'Amazon Fire TV Stick 4K Max'],
            ['B0DXXYS4BJ', 'Roku Streaming Stick HD 2025'],
            ['B00SFSU53G', 'Mounting Dream TV Wall Mount MD2380'],
        ];
        foreach ($devices as $index => [$asin, $name]) {
            ShopProduct::query()->create([
                'name' => $name,
                'asin' => $asin,
                'link' => 'https://example.com/' . strtolower($asin),
                'image' => $asin . '.webp',
                'sort_order' => 100 + $index,
                'is_active' => true,
            ]);
        }

        for ($index = 0; $index < 6; $index++) {
            ShopProduct::query()->create([
                'name' => 'Extra Device ' . ($index + 1),
                'asin' => 'EXTRA' . $index,
                'link' => 'https://example.com/extra-' . $index,
                'image' => 'placeholder.webp',
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        foreach ([
            ['netflix', 'Netflix', 2.29],
            ['prime-video', 'Prime Video', 1.54],
            ['hbo-max-premium', 'HBO Max Premium', 1.89],
            ['nordvpn', 'NordVPN', 1.89],
        ] as $index => [$slug, $title, $price]) {
            DigitalProduct::query()->create([
                'title' => $title,
                'slug' => $slug,
                'price' => $price,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 100 + $index,
                'delivery_type' => 'manual',
            ]);
        }

        $response = $this->get('/shop');

        $response->assertOk();
        $response->assertSeeText('Android TV Box (4GB RAM, 32GB ROM, 6K, Wi-Fi 5.0, Bluetooth 5.0)');
        $response->assertSeeText('Amazon Fire TV Stick 4K Max (Wi-Fi 6E)');
        $response->assertSeeText('Roku Streaming Stick HD 2025');
        $response->assertSeeText('Mounting Dream TV Wall Mount');
        $response->assertSeeText('From $2.29');
        $response->assertSeeText('From $1.54');
        $response->assertSeeText('Full HBO Max Premium access');
        $response->assertSeeText('NordVPN is one of the more reliable VPN options');
        $response->assertDontSeeText('Extra Device 1');
    }
}
