<?php

namespace Tests\Feature;

use App\Models\Package;
use Tests\TestCase;

class CustomerExperienceUiTest extends TestCase
{
    public function test_setup_guides_are_optional_and_available_without_customer_login(): void
    {
        $response = $this->get(route('customer.guides'));

        $response->assertOk()
            ->assertSee('you do not need to select a device to pay or renew')
            ->assertSee('Android / Android TV')
            ->assertSee('Fire TV / Firestick')
            ->assertSee('Smart TV')
            ->assertSee('iPhone / iPad')
            ->assertSee('Windows / Mac')
            ->assertDontSee('name="device_id"', false);
    }

    public function test_package_comparison_carries_only_the_existing_connection_limit(): void
    {
        $package = new Package([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Example Service',
            'price_amount' => 12, 'duration_months' => 1, 'max_devices' => 2,
            'features' => ['Example supported player'], 'is_available' => true,
        ]);
        $package->setRelation('translations', collect());

        $card = $package->toIptvArray();
        $this->assertSame(2, $card['max_devices']);
        $html = view('includes._iptv-package-cards', [
            'displayPackages' => [$card], 'activeEventPromotion' => [],
        ])->render();
        $this->assertStringContainsString('data-connections="2"', $html);
        $this->assertStringContainsString('Example supported player', $html);
        $this->assertStringContainsString('data-price="12.00"', $html);
    }

    public function test_unknown_connection_limit_is_not_presented_as_an_invented_allowance(): void
    {
        $html = view('includes._iptv-package-cards', [
            'displayPackages' => [[
                'title' => 'Example Service', 'service' => 'Example Service',
                'price_amount' => 12, 'duration_months' => 1,
                'features' => ['<script>alert("not markup")</script>'],
            ]],
            'activeEventPromotion' => [],
        ])->render();

        $this->assertStringContainsString('data-connections=""', $html);
        $this->assertStringNotContainsString('<script>alert(', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_mobile_chat_uses_the_official_widget_api_and_keeps_lead_capture(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/default.blade.php'));

        foreach (['id="mobileSupportLauncher"', 'aria-label="Open live chat"',
            'Tawk_API.onLoad', 'Tawk_API.onChatMinimized', 'Tawk_API.hideWidget()',
            'Tawk_API.showWidget()', 'Tawk_API.maximize()',
            "if (!mobileChat.matches) loadWidget();", 'Tawk_API.onPrechatSubmit',
            'Tawk_API.onOfflineSubmit', "route('tawk.leads.store'",
        ] as $expected) {
            $this->assertStringContainsString($expected, $layout);
        }
    }

    public function test_mobile_purchase_popups_are_suppressed_without_changing_desktop_data(): void
    {
        $view = file_get_contents(resource_path('views/includes/recent-purchase-popup.blade.php'));

        $this->assertStringContainsString('@media (max-width: 768px)', $view);
        $this->assertStringContainsString('.recent-purchase-toast { display: none !important; }', $view);
        $this->assertStringContainsString('return mobileViewport.matches || document.hidden', $view);
        $this->assertStringContainsString('RecentPurchaseService::class)->items()', $view);
    }

    public function test_checkout_preserves_selection_and_provides_accessible_feedback(): void
    {
        $view = file_get_contents(resource_path('views/pages/checkout/step1.blade.php'));

        $this->assertStringContainsString('id="checkoutErrors" role="alert" tabindex="-1"', $view);
        $this->assertStringContainsString('id="checkoutProgress"', $view);
        $this->assertStringContainsString('if (checkoutSubmitting)', $view);
        $this->assertStringContainsString("window.addEventListener('pageshow'", $view);
        $this->assertStringContainsString('name="package_id" value="{{ $selectedPackageId }}"', $view);
        $this->assertStringContainsString("old('email')", $view);
    }
}
