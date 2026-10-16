<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomerActivity;
use App\Models\Order;
use App\Models\TrialClick;
use App\Models\User;
use App\Services\Clients\LandingInteractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingFunnelReportTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
        $this->assertSame('array', config('cache.default'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Bus::fake();
        Mail::fake();
        Notification::fake();
        Cache::flush();
    }

    public function test_funnel_counts_distinct_opted_in_sessions_and_submissions_without_implying_sales(): void
    {
        $session = (string) Str::uuid();
        $this->activity('trex', 'visit', $session);
        $this->activity('trex', 'visit', $session);
        $this->activity('trex', 'checkout_clicked', $session);
        $this->activity('trex', 'checkout_clicked', $session);
        $this->activity('trex', 'contact_requested', '12');
        $this->activity('trex', 'contact_requested', '12');
        $this->activity('geo', 'visit', (string) Str::uuid());
        $this->activity('trex', 'payment_paid', '1');
        $old = $this->activity('trex', 'visit', (string) Str::uuid());
        $old->update(['occurred_at' => now()->subDays(40)]);

        $funnel = app(LandingInteractionService::class)->funnel(null, now()->subDays(29)->toDateString(), now()->toDateString());
        $this->assertSame(['visits' => 2, 'contacts' => 2, 'checkout_clicks' => 1, 'paid_orders' => 0], $funnel['totals']);
        $this->assertSame(LandingInteractionService::PAYMENT_ATTRIBUTION_ENABLED, $funnel['payment_attribution']);
        $trex = collect($funnel['brands'])->firstWhere('brand', 'trex');
        $this->assertSame(1, $trex['visits']);
        $this->assertSame(2, $trex['contacts']);
        $this->assertArrayNotHasKey('paid', $trex);
    }

    public function test_admin_api_exposes_report_and_brand_filter_uses_history_not_latest_placement(): void
    {
        $lead = TrialClick::create(['event_id' => (string) Str::uuid(), 'phone_normalized' => '923001234567', 'placement' => 'landing_geo', 'contact_name' => 'Isolated lead', 'intent' => 'contact']);
        TrialClick::create(['event_id' => (string) Str::uuid(), 'phone_normalized' => '923001234568', 'placement' => 'landing_geo', 'intent' => 'contact']);
        $this->activity('trex', 'contact_requested', (string) $lead->id);
        $this->activity('geo', 'contact_requested', (string) $lead->id);
        $this->signIn();
        $this->getJson('/api/browser/admin/trial-clicks?landing_brand=trex')->assertOk()
            ->assertJsonPath('data.clicks.meta.total', 1)
            ->assertJsonPath('data.clicks.data.0.id', $lead->id)
            ->assertJsonPath('data.landingBrands.trex', 'Trex IPTV 8K')
            ->assertJsonPath('data.landingFunnel.totals.contacts', 1)
            ->assertJsonPath('data.landingFunnel.payment_attribution', LandingInteractionService::PAYMENT_ATTRIBUTION_ENABLED);
        $this->get('/admin/trial-clicks')->assertOk()->assertSeeText('Landing activity')->assertSeeText(
            LandingInteractionService::PAYMENT_ATTRIBUTION_ENABLED ? 'Test payments and manually marked lead statuses are excluded.' : 'Payment attribution is not connected yet.'
        );
    }

    public function test_report_requires_existing_admin_permissions_and_valid_filters(): void
    {
        $this->getJson('/api/browser/admin/trial-clicks')->assertUnauthorized();
        $this->signIn('content');
        $this->getJson('/api/browser/admin/trial-clicks')->assertForbidden();
        $this->signIn();
        $this->getJson('/api/browser/admin/trial-clicks?landing_brand=unknown')->assertUnprocessable();
        $this->getJson('/api/browser/admin/trial-clicks?from=not-a-date')->assertUnprocessable();
        $this->getJson('/api/browser/admin/trial-clicks?from=2026-10-10&to=2026-10-01')->assertUnprocessable();
        $this->getJson('/api/browser/admin/trial-clicks?to=2026-10-10')->assertOk();
        $this->getJson('/api/browser/admin/trial-clicks?to=2026-08-31')->assertOk()
            ->assertJsonPath('data.landingFunnel.from', '2026-08-02')
            ->assertJsonPath('data.landingFunnel.to', '2026-08-31');
    }

    public function test_brand_date_filter_uses_contact_history_even_if_the_lead_is_older(): void
    {
        $lead = TrialClick::create(['event_id' => (string) Str::uuid(), 'phone_normalized' => '923001234567', 'placement' => 'landing_geo', 'intent' => 'contact']);
        $lead->update(['created_at' => now()->subMonths(2)]);
        $this->activity('trex', 'contact_requested', (string) $lead->id);
        $this->signIn();
        $this->getJson('/api/browser/admin/trial-clicks?landing_brand=trex&from='.now()->toDateString().'&to='.now()->toDateString())
            ->assertOk()->assertJsonPath('data.clicks.meta.total', 1)
            ->assertJsonPath('data.landingFunnel.totals.contacts', 1);
    }

    public function test_paid_counts_require_real_production_orders_and_deduplicate_repeated_activities(): void
    {
        $user = User::factory()->create();
        $order = fn (string $provider, string $status, $paidAt) => Order::withoutEvents(fn () => Order::create([
            'user_id' => $user->id, 'package' => 'Isolated landing plan', 'type' => 'package', 'price' => 12,
            'currency' => 'USD', 'status' => 'active', 'payment_provider' => $provider, 'payment_status' => $status,
            'paid_amount' => 12, 'paid_currency' => 'USD', 'paid_at' => $paidAt,
        ]));
        $paid = $order('safepay', 'paid', now());
        $sandbox = $order('safepay_sandbox', 'test_paid', now());
        $manual = $order('manual', 'paid', now());
        $unpaid = $order('safepay', 'unpaid', null);
        foreach ([$paid, $paid, $sandbox, $manual, $unpaid] as $record) {
            $this->activity('trex', 'payment_paid', (string) $record->id)->update(['order_id' => $record->id]);
        }
        $this->activity('trex', 'payment_paid', 'missing-order');
        TrialClick::create(['event_id' => (string) Str::uuid(), 'phone_normalized' => '923001234567', 'status' => 'paid']);
        $funnel = app(LandingInteractionService::class)->funnel('trex', now()->toDateString(), now()->toDateString());
        $this->assertSame(1, $funnel['totals']['paid_orders']);
        $this->assertSame(1, $funnel['brands'][0]['paid_orders']);
        $this->assertSame(0, $funnel['totals']['visits']);
        $this->assertSame(0, $funnel['totals']['contacts']);
    }

    private function activity(string $brand, string $event, string $entityId): CustomerActivity
    {
        return CustomerActivity::create([
            'source' => 'landing_'.$brand, 'event_id' => (string) Str::uuid(), 'event_type' => 'landing.'.$event,
            'entity_type' => match ($event) { 'contact_requested' => 'trial_click', 'payment_paid' => 'order', default => 'landing_session' },
            'entity_id' => $entityId, 'payload_hash' => hash('sha256', (string) Str::uuid()),
            'occurred_at' => now(), 'user_id' => null,
        ]);
    }

    private function signIn(string $role = 'owner'): void
    {
        $admin = Admin::create(['name' => 'Isolated report admin', 'email' => Str::uuid().'@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => $role, 'auth_version' => 1, 'must_change_password' => false]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession([
            'admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id,
            'admin_sensitive_confirmed_id' => $admin->id, 'admin_sensitive_confirmed_version' => 1,
            'admin_sensitive_confirmed_at' => time(),
        ]);
    }
}
