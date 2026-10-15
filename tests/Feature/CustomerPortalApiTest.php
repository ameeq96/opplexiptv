<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\CustomerConversation;
use App\Models\CustomerExternalLink;
use App\Models\Order;
use App\Models\Package;
use App\Models\TrialClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerPortalApiTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Http::preventStrayRequests();
        config(['services.opplexify.url' => 'http://127.0.0.1:3000']);
    }

    public function test_private_portal_apis_require_verified_current_email_and_session(): void
    {
        $this->get('/api/browser/account/overview')->assertUnauthorized()->assertJsonStructure(['message']);
        $customer = User::factory()->create();
        $this->actingAs($customer, 'web')->getJson('/api/browser/account/orders')->assertUnauthorized();
        $this->customer($customer)->getJson('/api/browser/account/overview')->assertOk();
        $customer->forceFill(['email' => 'changed@example.test'])->save();
        $this->getJson('/api/browser/account/overview')->assertUnauthorized();
        $customer->forceFill(['email_verified_at' => null])->save();
        $this->customer($customer)->getJson('/api/browser/account/overview')->assertUnauthorized();
    }

    public function test_session_bootstrap_exposes_only_safe_fields_and_no_store_headers(): void
    {
        $this->getJson('/api/browser/account/session')->assertOk()->assertJsonPath('data.authenticated', false)->assertJsonPath('data.customer', null);
        $customer = User::factory()->create(['notes' => 'private note']);
        $response = $this->customer($customer)->getJson('/api/browser/account/session')->assertOk()->assertJsonPath('data.authenticated', true);
        $this->assertSame(['id', 'name', 'email'], array_keys($response->json('data.customer')));
        $this->assertNotEmpty($response->json('data.csrf_token'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringNotContainsString('private note', $response->getContent());
    }

    public function test_overview_counts_and_order_data_are_owned_and_allowlisted(): void
    {
        $customer = User::factory()->create(['notes' => 'secret customer note']);
        $own = $this->order($customer, ['status' => 'active', 'payment_status' => 'paid', 'buying_date' => '2026-01-31', 'expiry_date' => '2099-04-30', 'iptv_username' => 'private login', 'note' => 'private staff note']);
        $other = $this->order(User::factory()->create());
        $this->customer($customer)->getJson('/api/browser/account/overview')->assertOk()->assertJsonPath('data.active_count', 1)->assertJsonPath('data.pending_count', 0);
        $response = $this->getJson('/api/browser/account/orders')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $own->id);
        $this->assertStringNotContainsString('private login', $response->getContent());
        $this->assertStringNotContainsString('private staff note', $response->getContent());
        $this->assertArrayNotHasKey('user_id', $response->json('data.0'));
        $this->getJson('/api/browser/account/orders/'.$other->id)->assertNotFound();
        $this->postJson('/api/browser/account/orders/'.$other->id.'/renew')->assertNotFound();
        $this->getJson('/api/browser/account/orders/'.$own->id)->assertOk()->assertJsonPath('data.buying_date', '2026-01-31')->assertJsonPath('data.expiry_date', '2099-04-30');
        $this->assertSame('2099-04-30', $own->fresh()->expiry_date->format('Y-m-d'));
    }

    public function test_order_pagination_is_bounded_and_statuses_distinguish_test_payment(): void
    {
        $customer = User::factory()->create();
        for ($i = 0; $i < 13; $i++) $this->order($customer);
        $test = $this->order($customer, ['payment_status' => 'test_paid']);
        $this->customer($customer)->getJson('/api/browser/account/orders')->assertOk()->assertJsonCount(12, 'data')->assertJsonPath('meta.total', 14);
        $this->getJson('/api/browser/account/orders?page=2')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/browser/account/orders?page=-1')->assertUnprocessable();
        $this->getJson('/api/browser/account/orders/'.$test->id)->assertOk()->assertJsonPath('data.state.label', 'Test payment only')->assertJsonPath('data.invoice_url', null);
    }

    public function test_paid_invoice_is_available_only_to_owning_customer_and_not_in_lists(): void
    {
        $customer = User::factory()->create();
        $order = $this->order($customer, ['payment_status' => 'paid', 'payment_provider' => 'safepay']);
        $reference = 'safe-reference-for-test-123';
        CustomerExternalLink::create(['source' => 'opplexify', 'entity_type' => 'streaming_order', 'external_id' => 'external-test-order', 'user_id' => $customer->id, 'order_id' => $order->id, 'state' => ['invoice' => ['data' => ['reference' => $reference]]]]);
        $this->customer($customer)->getJson('/api/browser/account/orders/'.$order->id)->assertOk()->assertJsonPath('data.invoice_url', 'http://127.0.0.1:3000/public/safepay/invoice/'.$reference);
        $this->getJson('/api/browser/account/orders')->assertDontSee($reference);
        $this->customer(User::factory()->create())->getJson('/api/browser/account/orders/'.$order->id)->assertNotFound();
    }

    public function test_trial_requests_are_owned_idempotent_and_do_not_start_clock_or_payment(): void
    {
        $customer = User::factory()->create();
        $package = $this->package();
        $data = ['package_id' => $package->id, 'submission_id' => (string) Str::uuid(), 'user_id' => 999, 'trial_started_at' => now()->toIso8601String()];
        $this->customer($customer)->postJson('/api/browser/account/trials', $data)->assertOk();
        $this->postJson('/api/browser/account/trials', $data)->assertOk();
        $this->assertDatabaseCount('trial_clicks', 1);
        $trial = TrialClick::query()->sole();
        $this->assertSame($customer->id, $trial->user_id);
        $this->assertNull($trial->trial_started_at);
        $this->assertNull($trial->trial_expires_at);
        $this->assertDatabaseCount('orders', 0);
        $this->getJson('/api/browser/account/trials')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.status', 'pending');
        $this->customer(User::factory()->create())->postJson('/api/browser/account/trials/'.$trial->id.'/plan')->assertNotFound();
    }

    public function test_trial_catalog_respects_provider_allowlist_and_hides_unverified_leads(): void
    {
        $customer = User::factory()->create();
        $allowed = $this->package();
        $this->package(['title' => 'Unsupported provider']);
        TrialClick::create(['user_id' => $customer->id, 'event_id' => (string) Str::uuid(), 'intent' => 'trial', 'status' => 'new']);
        $this->customer($customer)->getJson('/api/browser/account/trial-packages')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $allowed->id);
        $this->getJson('/api/browser/account/trials')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_renewal_returns_signed_link_without_mutating_order_dates(): void
    {
        $customer = User::factory()->create();
        $package = $this->package();
        $order = $this->order($customer, ['package_id' => $package->id, 'buying_date' => '2026-01-31', 'expiry_date' => '2026-04-30']);
        $url = $this->customer($customer)->postJson('/api/browser/account/orders/'.$order->id.'/renew')->assertOk()->json('redirect_url');
        $this->assertStringContainsString('signature=', $url);
        $this->assertSame(parse_url(route('packages.purchase', $package->id), PHP_URL_PATH), parse_url($url, PHP_URL_PATH));
        $this->assertSame('2026-04-30', $order->fresh()->expiry_date->format('Y-m-d'));
        $this->assertDatabaseCount('orders', 1);
        Http::assertNothingSent();
    }

    public function test_preferences_only_change_renewal_preference_and_require_csrf(): void
    {
        $customer = User::factory()->create(['marketing_email_consented_at' => now()]);
        $this->customer($customer);
        $this->app->instance(VerifyCsrfToken::class, new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken {
            protected function runningUnitTests() { return false; }
        });
        $this->patchJson('/api/browser/account/preferences', ['renewal_email_enabled' => false])->assertStatus(419);
        $token = $this->getJson('/api/browser/account/session')->json('data.csrf_token');
        $this->withSession(['_token' => $token])->patchJson('/api/browser/account/preferences', ['_token' => $token, 'renewal_email_enabled' => false, 'email' => 'replacement@example.test'])->assertOk();
        $this->assertFalse($customer->fresh()->renewal_email_enabled);
        $this->assertNotNull($customer->fresh()->marketing_email_consented_at);
        $this->assertSame($customer->email, $customer->fresh()->email);
    }

    public function test_support_records_have_exact_ownership_scope_and_no_internal_messages(): void
    {
        $customer = User::factory()->create();
        $mine = $this->ticket($customer);
        $other = $this->ticket(User::factory()->create());
        $tawk = $this->ticket($customer, ['channel' => 'tawk']);
        $legacy = $this->ticket($customer, ['property_id' => 'old-portal']);
        foreach (['customer' => '<script>test</script>', 'support' => 'Public reply', 'internal' => 'Secret internal note'] as $sender => $content) {
            $mine->messages()->create(['fingerprint' => hash('sha256', (string) Str::uuid()), 'sender_type' => $sender, 'content' => $content, 'sent_at' => now()]);
        }
        $this->customer($customer)->getJson('/api/browser/account/support')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $mine->id);
        $response = $this->getJson('/api/browser/account/support/'.$mine->id.'/messages')->assertOk()->assertJsonCount(2, 'data');
        $this->assertStringNotContainsString('Secret internal note', $response->getContent());
        $this->assertSame(['id', 'sender_type', 'content', 'sent_at'], array_keys($response->json('data.0')));
        foreach ([$other, $tawk, $legacy] as $private) {
            $this->getJson('/api/browser/account/support/'.$private->id)->assertNotFound();
            $this->getJson('/api/browser/account/support/'.$private->id.'/messages')->assertNotFound();
            $this->postJson('/api/browser/account/support/'.$private->id.'/messages', $this->submission())->assertNotFound();
        }
    }

    public function test_support_create_reply_and_retry_preserve_idempotency_and_reopen(): void
    {
        $customer = User::factory()->create();
        $data = $this->submission();
        $this->customer($customer)->postJson('/api/browser/account/support', $data)->assertOk();
        $this->postJson('/api/browser/account/support', $data)->assertOk();
        $this->postJson('/api/browser/account/support', array_replace($data, ['message' => 'Different content']))->assertStatus(409);
        $ticket = CustomerConversation::query()->sole();
        $ticket->forceFill(['status' => 'resolved', 'ended_at' => now()])->save();
        $reply = $this->submission();
        $url = '/api/browser/account/support/'.$ticket->id.'/messages';
        $this->postJson($url, $reply)->assertOk();
        $this->postJson($url, $reply)->assertOk();
        $this->assertSame('open', $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->ended_at);
        $this->assertDatabaseCount('customer_messages', 2);
        $this->postJson($url, array_replace($reply, ['message' => 'Conflicting reply']))->assertStatus(409);
        $this->postJson($url, ['submission_id' => 'invalid', 'message' => ' '])->assertUnprocessable();
        Mail::assertNothingOutgoing();
        Notification::assertNothingSent();
    }

    public function test_long_support_threads_open_latest_and_preserve_history(): void
    {
        $customer = User::factory()->create();
        $ticket = $this->ticket($customer);
        for ($i = 1; $i <= 31; $i++) $ticket->messages()->create(['fingerprint' => hash('sha256', (string) Str::uuid()), 'sender_type' => 'customer', 'content' => 'Message '.$i, 'sent_at' => now()]);
        $url = '/api/browser/account/support/'.$ticket->id.'/messages';
        $this->customer($customer)->getJson($url)->assertOk()->assertJsonPath('meta.current_page', 2)->assertJsonPath('data.0.content', 'Message 31');
        $this->getJson($url.'?page=1')->assertOk()->assertJsonCount(30, 'data')->assertJsonPath('data.0.content', 'Message 1');
    }

    public function test_web_portal_renders_shells_and_not_database_records(): void
    {
        $customer = User::factory()->create(['email' => 'private-shell@example.test']);
        $order = $this->order($customer, ['package' => 'Private record package']);
        $ticket = $this->ticket($customer);
        $this->customer($customer)->get('/account')->assertOk()->assertDontSee($customer->email)->assertDontSee('Private record package')->assertSee('accountOrders');
        $this->get('/account/orders/'.$order->id)->assertOk()->assertDontSee('Private record package')->assertSee('orderDetails');
        $this->get('/account/support/'.$ticket->id)->assertOk()->assertSee('supportMessages');
        $this->postJson('/api/browser/account/logout')->assertOk()->assertJsonPath('redirect_url', route('customer.login'));
        $this->assertGuest('web');
        $this->getJson('/api/browser/account/overview')->assertUnauthorized();
    }

    private function customer(User $customer): static
    {
        return $this->actingAs($customer, 'web')->withSession(['customer_verified_email' => hash('sha256', User::normalizeEmail($customer->email))]);
    }

    private function order(User $customer, array $attributes = []): Order
    {
        return Order::withoutEvents(fn () => Order::create(array_replace(['user_id' => $customer->id, 'package' => 'Opplex', 'type' => 'package', 'price' => 15, 'currency' => 'USD', 'status' => 'pending', 'payment_status' => 'unpaid'], $attributes)));
    }

    private function package(array $attributes = []): Package
    {
        return Package::create(array_replace(['type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Opplex', 'display_price' => '$15 / 1 month', 'price_amount' => 15, 'duration_months' => 1, 'free_trial_hours' => 12, 'active' => true, 'is_available' => true], $attributes));
    }

    private function ticket(User $customer, array $attributes = []): CustomerConversation
    {
        return CustomerConversation::create(array_replace(['user_id' => $customer->id, 'channel' => 'portal', 'property_id' => 'customer-account', 'external_id' => (string) Str::uuid(), 'status' => 'open', 'started_at' => now()], $attributes));
    }

    private function submission(): array
    {
        return ['submission_id' => (string) Str::uuid(), 'message' => 'Isolated support fixture'];
    }
}
