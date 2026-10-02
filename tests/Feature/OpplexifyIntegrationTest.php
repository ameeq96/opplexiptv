<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OpplexifyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-opplexify-shared-secret';

    protected function setUp(): void
    {
        parent::setUp();

        Package::query()->delete();
        config()->set('services.opplexify.url', 'https://opplexify.com');
        config()->set('services.opplexify.shared_secret', self::SECRET);
    }

    public function test_signed_purchase_route_redirects_with_a_verifiable_short_lived_selection(): void
    {
        $package = $this->package();
        $url = URL::temporarySignedRoute(
            'packages.purchase',
            now()->addMinutes(15),
            ['package' => $package->id]
        );

        $response = $this->get($url);

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://opplexify.com/checkout?', $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $selection = (string) ($query['selection'] ?? '');
        $this->assertSame(
            hash_hmac('sha256', $selection, self::SECRET),
            $query['signature'] ?? null
        );

        $padding = (4 - strlen($selection) % 4) % 4;
        $payload = json_decode(base64_decode(strtr($selection, '-_', '+/') . str_repeat('=', $padding)), true);
        $this->assertSame(1, $payload['v'] ?? null);
        $this->assertSame($package->id, $payload['package_id'] ?? null);
        $this->assertGreaterThan(now()->timestamp, $payload['exp'] ?? 0);
        $this->assertLessThanOrEqual(now()->addMinutes(10)->timestamp, $payload['exp'] ?? 0);

        $this->get(route('packages.purchase', ['package' => $package->id]))->assertForbidden();
    }

    public function test_purchase_route_rejects_unavailable_packages(): void
    {
        $package = $this->package(['is_available' => false]);
        $url = URL::temporarySignedRoute(
            'packages.purchase',
            now()->addMinutes(15),
            ['package' => $package->id]
        );

        $this->get($url)->assertNotFound();
    }

    public function test_catalog_requires_a_fresh_valid_signature_and_returns_only_purchasable_packages(): void
    {
        $iptv = $this->package(['title' => 'Alpha IPTV']);
        $reseller = $this->package([
            'type' => 'reseller',
            'title' => 'Starter Reseller Package',
            'duration_months' => null,
            'credits' => 20,
        ]);
        $this->package(['title' => 'Unavailable', 'is_available' => false]);
        $this->package(['title' => 'Inactive', 'active' => false]);
        $legacy = $this->package(['title' => 'Monthly']);
        $annualMonthly = $this->package([
            'title' => 'Annual Service',
            'features' => ['Yearly subscription only (monthly plan unavailable)'],
        ]);
        $annualYearly = $this->package([
            'title' => 'Annual Service - Yearly',
            'duration_months' => 12,
            'features' => ['Yearly subscription only (monthly plan unavailable)'],
        ]);

        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac(
            'sha256',
            "GET\n/integrations/opplexify/catalog\n{$timestamp}",
            self::SECRET
        );

        $response = $this->withHeaders([
            'X-Opplexify-Timestamp' => $timestamp,
            'X-Opplexify-Signature' => $signature,
        ])->get('/integrations/opplexify/catalog');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment([
                'id' => $iptv->id,
                'type' => 'iptv',
                'price_amount' => 9.99,
                'currency' => 'USD',
            ])
            ->assertJsonFragment([
                'id' => $reseller->id,
                'type' => 'reseller',
                'credits' => 20,
            ])
            ->assertJsonFragment([
                'id' => $annualYearly->id,
                'duration_months' => 12,
            ]);
        $returnedIds = collect($response->json('data'))->pluck('id');
        $this->assertFalse($returnedIds->contains($legacy->id));
        $this->assertFalse($returnedIds->contains($annualMonthly->id));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $this->withHeaders([
            'X-Opplexify-Timestamp' => '',
            'X-Opplexify-Signature' => '',
        ])->get('/integrations/opplexify/catalog')->assertUnauthorized();
        $staleTimestamp = (string) now()->subMinutes(6)->timestamp;
        $staleSignature = hash_hmac(
            'sha256',
            "GET\n/integrations/opplexify/catalog\n{$staleTimestamp}",
            self::SECRET
        );
        $this->withHeaders([
            'X-Opplexify-Timestamp' => $staleTimestamp,
            'X-Opplexify-Signature' => $staleSignature,
        ])->get('/integrations/opplexify/catalog')->assertUnauthorized();
    }

    private function package(array $overrides = []): Package
    {
        return Package::create(array_merge([
            'type' => 'iptv',
            'vendor' => 'opplex',
            'title' => 'Monthly',
            'display_price' => '$9.99 / 1 month',
            'price_amount' => 9.99,
            'duration_months' => 1,
            'features' => ['Feature'],
            'is_available' => true,
            'active' => true,
        ], $overrides));
    }
}
