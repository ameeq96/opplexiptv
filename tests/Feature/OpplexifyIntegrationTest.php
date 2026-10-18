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

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Package::query()->delete();
        config()->set('services.opplexify.url', 'https://opplexify.com');
        config()->set('services.opplexify.shared_secret', self::SECRET);
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Notification::fake();
        \Illuminate\Support\Facades\Mail::fake();
        \Illuminate\Support\Facades\Bus::fake();
        config()->set('services.app.default_currency', 'USD');
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
        $iptv = $this->package(['title' => 'Opplex iptv']);
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
            'title' => 'Filex',
            'features' => ['Yearly subscription only (monthly plan unavailable)'],
        ]);
        $annualYearly = $this->package([
            'title' => 'Filex - Yearly',
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

    public function test_landing_checkout_binds_only_matching_brand_and_consented_session(): void
    {
        $package = $this->package(['title' => 'Opplex iptv']);
        $session = (string) \Illuminate\Support\Str::uuid();
        foreach ([false, true] as $consented) {
            $response = $this->getJson('/api/browser/checkout?'.http_build_query([
                'package_id' => $package->id, 'utm_source' => 'opplexiptv-store', 'utm_medium' => 'landing',
                'landing_session' => $session, 'landing_consent' => $consented ? '1' : '0',
            ]))->assertOk();
            parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
            $payload = json_decode(base64_decode(strtr($query['selection'], '-_', '+/')), true);
            $this->assertSame(hash_hmac('sha256', $query['selection'], self::SECRET), $query['signature']);
            $this->assertSame($consented ? ['brand' => 'opplex', 'session_id' => $session] : ['brand' => 'opplex'], $payload['attribution']);
            $this->assertSame($package->id, $payload['package_id']);
        }
        $this->getJson('/api/browser/checkout?'.http_build_query([
            'package_id' => $package->id, 'utm_source' => 'geoiptv', 'utm_medium' => 'landing',
        ]))->assertUnprocessable();
        $response = $this->getJson('/api/browser/checkout?'.http_build_query([
            'package_id' => $package->id, 'utm_source' => 'unknown', 'utm_medium' => 'landing',
        ]))->assertOk();
        parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
        $this->assertArrayNotHasKey('attribution', json_decode(base64_decode(strtr($query['selection'], '-_', '+/')), true));
    }

    public function test_verified_paid_source_is_immutable_idempotent_and_belongs_to_actual_payer(): void
    {
        $package = $this->package(['title' => 'Opplex iptv']);
        $data = $this->bridgePayload($package);
        $data['attribution'] = ['brand' => 'opplex', 'session_id' => (string) \Illuminate\Support\Str::uuid()];
        $lead = \App\Models\TrialClick::create([
            'event_id' => (string) \Illuminate\Support\Str::uuid(), 'phone_normalized' => '923001234567',
            'contact_email' => 'original-enquirer@example.test', 'intent' => 'contact', 'placement' => 'landing_opplex',
        ]);
        $anonymous = \App\Models\CustomerActivity::create([
            'source' => 'landing_opplex', 'event_id' => (string) \Illuminate\Support\Str::uuid(),
            'event_type' => 'landing.contact_requested', 'entity_type' => 'trial_click', 'entity_id' => (string) $lead->id,
            'payload_hash' => hash('sha256', 'isolated lead'), 'occurred_at' => now(), 'user_id' => null,
            'contact_phone' => '+923001234567', 'contact_email' => 'original-enquirer@example.test',
            'details' => ['brand' => 'opplex', 'session_id' => $data['attribution']['session_id']],
        ]);
        $this->bridge('prepare', $data)->assertOk();
        $this->bridge('prepare', $data)->assertOk();
        $reordered = $data;
        $reordered['attribution'] = ['session_id' => strtoupper($data['attribution']['session_id']), 'brand' => 'opplex'];
        $this->bridge('prepare', $reordered)->assertOk();
        $changed = $data;
        $changed['attribution']['session_id'] = (string) \Illuminate\Support\Str::uuid();
        $this->bridge('prepare', $changed)->assertConflict();
        unset($changed['attribution']);
        $this->bridge('prepare', $changed)->assertConflict();
        $payment = $this->completedPayload($data);
        unset($payment['attribution']);
        $this->bridge('complete', $payment)->assertOk()->assertJsonPath('created', true);
        $this->bridge('complete', $payment)->assertOk()->assertJsonPath('created', false);
        $activity = \App\Models\CustomerActivity::where('event_type', 'landing.payment_paid')->sole();
        $order = \App\Models\Order::findOrFail($activity->order_id);
        $this->assertSame('landing_opplex', $activity->source);
        $this->assertSame('paid:'.$order->id, $activity->event_id);
        $this->assertSame($order->user_id, $activity->user_id);
        $this->assertSame('actual-payer@example.test', $order->user->email);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame($data['attribution']['session_id'], $activity->details['session_id']);
        $binding = \App\Models\CustomerExternalLink::where('entity_type', 'landing_attribution')->sole();
        $this->assertSame($order->id, $binding->order_id);
        $this->assertSame($data['attribution'], $binding->state['attribution']);
        $this->assertNull($lead->fresh()->user_id);
        $this->assertNull($anonymous->fresh()->user_id);
        \Illuminate\Support\Facades\Http::assertNothingSent();
        \Illuminate\Support\Facades\Mail::assertNothingSent();
    }

    public function test_prepare_rejects_unsigned_invalid_or_mismatched_attribution(): void
    {
        $data = $this->bridgePayload($this->package(['title' => 'Opplex iptv']));
        $this->postJson('/api/integrations/opplexify/orders/prepare', $data)->assertUnauthorized();
        foreach ([[], ['brand' => 'unknown'], ['brand' => 'geo'], ['brand' => 'opplex', 'session_id' => 'not-a-uuid'], ['brand' => 'opplex', 'email' => 'private@example.test']] as $attribution) {
            $this->bridge('prepare', array_merge($data, ['attribution' => $attribution]))->assertUnprocessable();
        }
        $this->assertDatabaseCount('checkout_drafts', 0);
        $this->assertDatabaseCount('customer_external_links', 0);
    }

    public function test_sandbox_and_unattributed_payments_do_not_create_landing_paid_events(): void
    {
        $package = $this->package(['title' => 'Opplex iptv']);
        foreach (['sandbox', 'production'] as $environment) {
            $data = $this->bridgePayload($package, $environment);
            if ($environment === 'sandbox') {
                $data['attribution'] = ['brand' => 'opplex'];
            }
            $this->bridge('prepare', $data)->assertOk();
            if ($environment === 'production') {
                $this->bridge('prepare', array_merge($data, ['attribution' => ['brand' => 'opplex']]))->assertConflict();
            }
            $this->bridge('complete', $this->completedPayload($data))->assertOk();
        }
        $this->assertSame(0, \App\Models\CustomerActivity::where('event_type', 'landing.payment_paid')->count());
    }

    public function test_completion_cannot_change_the_prepared_environment(): void
    {
        $package = $this->package(['title' => 'Opplex iptv']);
        foreach (['sandbox' => 'production', 'production' => 'sandbox'] as $prepared => $completed) {
            $data = $this->bridgePayload($package, $prepared);
            $data['attribution'] = ['brand' => 'opplex'];
            $this->bridge('prepare', $data)->assertOk();
            $payment = $this->completedPayload($data);
            $payment['environment'] = $completed;
            $this->bridge('complete', $payment)->assertConflict();
        }
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_historical_draft_and_order_cannot_receive_retroactive_attribution(): void
    {
        $package = $this->package(['title' => 'Opplex iptv']);
        foreach ([false, true] as $completed) {
            $data = $this->bridgePayload($package);
            $this->bridge('prepare', $data)->assertOk();
            if ($completed) {
                $this->bridge('complete', $this->completedPayload($data))->assertOk();
                \App\Models\CheckoutDraft::where('completed_order_id', \App\Models\Order::sole()->id)->delete();
            }
            \App\Models\CustomerExternalLink::where('entity_type', 'landing_attribution')->where('external_id', $data['reference'])->delete();
            $this->bridge('prepare', array_merge($data, ['attribution' => ['brand' => 'opplex']]))->assertConflict();
            $this->bridge('prepare', $data)->assertOk();
            $this->assertNull(\App\Models\CustomerExternalLink::where('external_id', $data['reference'])->sole()->state['attribution']);
        }
        $this->assertSame(0, \App\Models\CustomerActivity::where('event_type', 'landing.payment_paid')->count());
    }

    public function test_consented_page_language_are_signed_and_immutable_through_paid_attribution(): void
    {
        $package = $this->package(['title' => 'Opplex iptv']);
        $session = (string) \Illuminate\Support\Str::uuid();
        foreach (['setup', ''] as $page) {
            foreach ([false, true] as $consented) {
                $response = $this->getJson('/api/browser/checkout?'.http_build_query([
                    'package_id' => $package->id, 'utm_source' => 'opplexiptv-store', 'utm_medium' => 'landing',
                    'landing_session' => $session, 'landing_consent' => $consented ? '1' : '0', 'landing_locale' => 'ur', 'landing_page' => $page,
                ]))->assertOk();
                parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
                $selection = json_decode(base64_decode(strtr($query['selection'], '-_', '+/')), true);
                $expected = $consented ? ['brand' => 'opplex', 'session_id' => $session, 'locale' => 'ur', 'page' => $page] : ['brand' => 'opplex'];
                $this->assertSame($expected, $selection['attribution']);
                $this->assertSame(hash_hmac('sha256', $query['selection'], self::SECRET), $query['signature']);
            }
            $payload = $this->bridgePayload($package);
            $payload['attribution'] = ['brand' => 'opplex', 'session_id' => $session, 'locale' => 'ur', 'page' => $page];
            $this->bridge('prepare', $payload)->assertOk();
            $this->bridge('prepare', $payload)->assertOk();
            $changed = $payload; $changed['attribution']['locale'] = 'fr';
            $this->bridge('prepare', $changed)->assertConflict();
            $this->bridge('complete', $this->completedPayload($payload))->assertOk();
            $activity = \App\Models\CustomerActivity::where('event_type', 'landing.payment_paid')->latest('id')->firstOrFail();
            $this->assertSame('ur', $activity->details['locale']);
            $this->assertSame($page, $activity->details['page']);
        }
    }

    public function test_page_language_require_a_consented_session_and_exact_matching_page_pair(): void
    {
        $data = $this->bridgePayload($this->package(['title' => 'Opplex iptv']));
        $base = ['brand' => 'opplex', 'session_id' => (string) \Illuminate\Support\Str::uuid(), 'locale' => 'en', 'page' => 'setup'];
        $invalid = [array_diff_key($base, ['session_id' => true]), array_diff_key($base, ['page' => true]), array_diff_key($base, ['locale' => true]),
            array_merge($base, ['locale' => 'xx']), array_merge($base, ['page' => 'setup?email=private']), array_merge($base, ['page' => 'https://foreign.example/'])];
        foreach ($invalid as $attribution) $this->bridge('prepare', array_merge($data, ['attribution' => $attribution]))->assertUnprocessable();
        $this->assertDatabaseCount('checkout_drafts', 0);
    }

    private function bridgePayload(Package $package, string $environment = 'production'): array
    {
        $base = (int) round((float) $package->price_amount * 100);
        return [
            'reference' => 'isolated_'.str_replace('-', '', (string) \Illuminate\Support\Str::uuid()),
            'package_id' => $package->id, 'currency' => 'USD', 'environment' => $environment,
            'amount_minor' => $base + array_sum(array_map(static fn (int $sample): int => (int) round($base * $sample / 1199), [71, 5, 12, 24])),
        ];
    }

    private function completedPayload(array $data): array
    {
        return array_merge($data, [
            'tracker' => 'track_'.$data['reference'],
            'customer' => ['first_name' => 'Actual', 'last_name' => 'Payer', 'email' => 'actual-payer@example.test', 'phone' => '+923001234567'],
        ]);
    }

    private function bridge(string $action, array $data): \Illuminate\Testing\TestResponse
    {
        $path = '/api/integrations/opplexify/orders/'.$action;
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        return $this->call('POST', $path, [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_OPPLEXIFY_TIMESTAMP' => $timestamp,
            'HTTP_X_OPPLEXIFY_SIGNATURE' => hash_hmac('sha256', "POST\n".$path."\n".$timestamp."\n".hash('sha256', $body), self::SECRET),
        ], $body);
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
