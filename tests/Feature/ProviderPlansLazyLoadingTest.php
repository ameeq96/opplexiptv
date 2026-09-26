<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProviderPlansLazyLoadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Package::query()->delete();
        Cache::flush();
    }

    public function test_initial_packages_html_contains_only_featured_provider_cards_and_all_provider_options(): void
    {
        $legacy = Package::create([
            'type' => 'iptv',
            'vendor' => 'opplex',
            'title' => 'Monthly',
            'display_price' => '$2.99 / 1 month',
            'price_amount' => 2.99,
            'duration_months' => 1,
            'sort_order' => 1,
            'is_featured' => true,
            'is_available' => true,
            'active' => true,
        ]);
        $alpha = $this->createProvider('Alpha IPTV', false, 10);
        $featured = $this->createProvider('Featured IPTV', true, 20);
        $reseller = Package::create([
            'type' => 'reseller',
            'vendor' => 'opplex',
            'title' => 'Starter Reseller Package',
            'display_price' => '$20 / 20 Credits',
            'price_amount' => 20,
            'credits' => 20,
            'features' => ['Reseller feature'],
            'is_available' => true,
            'active' => true,
        ]);

        $response = $this->get('/packages');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertSame(4, substr_count($html, 'data-type="iptv" data-vendor'));
        $this->assertStringContainsString('value="Alpha IPTV"', $html);
        $this->assertStringContainsString('value="Featured IPTV"', $html);
        $this->assertStringNotContainsString('value="Opplex"', $html);
        $this->assertStringNotContainsString('data-package-id="' . $legacy->id . '"', $html);
        $this->assertStringContainsString('data-package-id="' . $featured->id . '"', $html);
        $this->assertStringNotContainsString('data-package-id="' . $alpha->id . '"', $html);
        $this->assertStringContainsString('data-package-id="' . $reseller->id . '"', $html);
        $this->assertStringContainsString('data-type="reseller"', $html);
        $this->assertStringContainsString(
            route('packages.provider-plans', ['package' => $alpha->id]),
            $html
        );
    }

    public function test_provider_endpoint_returns_at_most_four_localized_server_rendered_cards(): void
    {
        $provider = $this->createProvider('Alpha IPTV', false, 10, 5);
        $provider->translations()->create([
            'locale' => 'es',
            'title' => 'IPTV Alfa - Mensual',
            'features' => ['Función localizada'],
        ]);
        app()->setLocale('es');
        $this->withoutMiddleware([
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        ]);

        $response = $this->get(route('packages.provider-plans', ['package' => $provider->id]));

        $response->assertOk()
            ->assertJsonPath('provider_name', 'Alpha IPTV')
            ->assertJsonPath('count', 4);
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $html = (string) $response->json('html');
        $this->assertSame(4, substr_count($html, 'data-type="iptv" data-vendor'));
        $this->assertSame(4, substr_count($html, 'data-service="Alpha IPTV"'));
        $this->assertStringNotContainsString('data-service="IPTV Alfa', $html);
        $this->assertStringContainsString('Función localizada', $html);
        $this->assertStringContainsString('data-whatsapp-placement="pricing_card"', $html);
        $this->assertStringContainsString('package_id=' . $provider->id, html_entity_decode($html));
    }

    public function test_legacy_duration_plans_remain_available_when_no_catalog_provider_exists(): void
    {
        $legacy = null;
        foreach (Package::DURATION_PLAN_TITLES as $index => $title) {
            $package = Package::create([
                'type' => 'iptv',
                'vendor' => 'opplex',
                'title' => $title,
                'display_price' => '$' . (3 + $index),
                'price_amount' => 3 + $index,
                'duration_months' => [1, 3, 6, 12][$index],
                'sort_order' => $index,
                'is_available' => true,
                'active' => true,
            ]);
            $legacy ??= $package;
        }

        $response = $this->get('/packages');

        $response->assertOk();
        $html = $response->getContent();
        $this->assertSame(4, substr_count($html, 'class="price-block scroll-item pkg-item'));
        $this->assertStringContainsString('value="Opplex"', $html);
        $this->assertStringContainsString(
            route('packages.provider-plans', ['package' => $legacy->id]),
            $html
        );
    }

    public function test_provider_endpoint_rejects_inactive_and_reseller_packages(): void
    {
        $inactive = Package::create([
            'type' => 'iptv',
            'vendor' => 'opplex',
            'title' => 'Inactive IPTV',
            'display_price' => '$5 / 1 month',
            'price_amount' => 5,
            'duration_months' => 1,
            'active' => false,
        ]);
        $reseller = Package::create([
            'type' => 'reseller',
            'vendor' => 'opplex',
            'title' => 'Reseller Package',
            'display_price' => '$20',
            'price_amount' => 20,
            'credits' => 20,
            'active' => true,
        ]);

        $this->get(route('packages.provider-plans', ['package' => $inactive->id]))->assertNotFound();
        $this->get(route('packages.provider-plans', ['package' => $reseller->id]))->assertNotFound();
        $this->get('/packages/providers/999999/plans')->assertNotFound();
    }

    private function createProvider(
        string $service,
        bool $featured,
        int $sortOrder,
        int $planCount = 4
    ): Package {
        $durations = [1, 3, 6, 12, 24];
        $first = null;

        foreach (array_slice($durations, 0, $planCount) as $index => $months) {
            $suffix = match ($months) {
                3 => ' - 3 Months',
                6 => ' - Half Yearly',
                12 => ' - Yearly',
                24 => ' - 24 Months',
                default => '',
            };
            $package = Package::create([
                'type' => 'iptv',
                'vendor' => 'opplex',
                'title' => $service . $suffix,
                'display_price' => '$' . (5 + $index) . ' / ' . $months . ' months',
                'price_amount' => 5 + $index,
                'duration_months' => $months,
                'sort_order' => ($sortOrder * 10) + $index,
                'features' => ['Feature ' . ($index + 1)],
                'badge_key' => $months === 6 ? 'most_popular' : null,
                'is_featured' => $featured,
                'is_available' => true,
                'active' => true,
            ]);
            $first ??= $package;
        }

        return $first;
    }
}
