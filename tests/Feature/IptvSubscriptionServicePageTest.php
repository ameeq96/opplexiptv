<?php

namespace Tests\Feature;

use App\Support\UiData;
use Illuminate\Support\Facades\Cache;
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
        $response->assertSee('Premium IPTV Subscription Service for Live TV, Sports, Movies and 4K Streaming', false);
        $response->assertSee('IPTV Subscription Service | Premium 4K IPTV Plans', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('id="pricing-section"', false);
        $response->assertSee('trial-cta', false);
        $response->assertDontSee('messages.iptv_subscription_service', false);
        $response->assertDontSee('meta.iptv-subscription-service', false);
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
