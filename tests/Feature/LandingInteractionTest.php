<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomerActivity;
use App\Models\TrialClick;
use App\Models\User;
use App\Services\Clients\ClientQueryService;
use App\Services\Clients\CustomerIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingInteractionTest extends TestCase
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
        Mail::fake();
        Notification::fake();
        Bus::fake();
        Cache::flush();
    }

    public function test_contact_is_saved_in_existing_crm_without_marketing_or_verified_identity(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.test', 'phone' => '+923001234567']);
        $original = $user->fresh()->getAttributes();
        $payload = $this->lead();
        $response = $this->postJson('/api/v1/landing/leads', $payload, $this->origin());
        $response->assertStatus(202)->assertExactJson(['ok' => true]);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $lead = TrialClick::sole();
        $this->assertSame('Landing visitor', $lead->contact_name);
        $this->assertSame('visitor@example.test', $lead->contact_email);
        $this->assertSame('923001234567', $lead->phone_normalized);
        $this->assertSame('Help me compare these plans.', $lead->message);
        $this->assertNull($lead->user_id);
        $activity = CustomerActivity::sole();
        $this->assertSame('landing_trex', $activity->source);
        $this->assertSame((string) $lead->id, $activity->entity_id);
        $this->assertFalse($activity->details['contact_verified']);
        $this->assertFalse($activity->details['analytics_consent']);
        $this->assertNull($activity->details['session_id']);
        $this->assertNull($activity->user_id);
        $this->assertNull($lead->ip);
        $this->assertNull($lead->fbp);
        $this->assertSame($original, $user->fresh()->getAttributes());
        Mail::assertNothingSent();
        Notification::assertNothingSent();
        Bus::assertNothingDispatched();
        Http::assertNothingSent();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_same_request_is_idempotent_and_reusing_its_identity_with_other_contents_fails(): void
    {
        $payload = $this->lead();
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertStatus(202);
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertStatus(202);
        $this->assertDatabaseCount('trial_clicks', 1);
        $this->assertDatabaseCount('customer_activities', 1);
        $this->assertSame(1, TrialClick::sole()->click_count);
        $payload['message'] = 'Changed contents';
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertConflict();
        $this->assertSame('Help me compare these plans.', TrialClick::sole()->message);
    }

    public function test_a_later_brand_contact_preserves_immutable_history_and_verified_lead_fields(): void
    {
        $this->postJson('/api/v1/landing/leads', $this->lead(), $this->origin())->assertStatus(202);
        $lead = TrialClick::sole();
        $user = User::factory()->create();
        $lead->update(['user_id' => $user->id, 'contact_name' => 'Verified lead', 'status' => 'paid', 'trial_owner_verified_at' => now()]);
        $payload = $this->lead('geo');
        $payload['name'] = 'Different unverified person';
        $payload['channel'] = 'tawk';
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin('geo'))->assertStatus(202);
        $this->assertDatabaseCount('trial_clicks', 1);
        $this->assertDatabaseCount('customer_activities', 2);
        $this->assertSame('Verified lead', $lead->fresh()->contact_name);
        $this->assertSame('paid', $lead->fresh()->status);
        $this->assertSame($user->id, $lead->fresh()->user_id);
        $this->assertSame(2, $lead->fresh()->click_count);
        $this->assertDatabaseHas('customer_activities', ['source' => 'landing_geo', 'contact_name' => 'Different unverified person', 'user_id' => null]);
        $this->assertDatabaseHas('customer_activities', ['source' => 'landing_trex', 'contact_name' => 'Landing visitor', 'user_id' => null]);
    }

    public function test_contacts_require_contact_consent_but_not_analytics_consent(): void
    {
        $payload = $this->lead();
        $payload['contact_consent'] = false;
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertUnprocessable()->assertJsonValidationErrors('contact_consent');
        $payload['contact_consent'] = true;
        $payload['analytics_consent'] = false;
        $payload['session_id'] = (string) Str::uuid();
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertUnprocessable()->assertJsonValidationErrors('session_id');
        unset($payload['session_id']);
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertStatus(202);
        $this->assertDatabaseCount('customer_activities', 1);
    }

    public function test_tawk_contact_does_not_create_or_overwrite_whatsapp_consent(): void
    {
        $payload = $this->lead();
        $payload['channel'] = 'tawk';
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertStatus(202);
        $lead = TrialClick::sole();
        $this->assertNull($lead->whatsapp_contact_consented_at);
        $this->assertNull($lead->consent_version);
        $this->assertNull($lead->consent_source);
        $this->assertTrue(CustomerActivity::sole()->details['contact_consent']);
        $this->assertSame('tawk', CustomerActivity::sole()->details['channel']);

        $this->postJson('/api/v1/landing/leads', $this->lead(), $this->origin())->assertStatus(202);
        $consent = $lead->fresh()->only(['whatsapp_contact_consented_at', 'consent_version', 'consent_source', 'consent_locale']);
        $this->assertNotNull($consent['whatsapp_contact_consented_at']);
        $this->travel(2)->hours();
        $payload['event_id'] = (string) Str::uuid();
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertStatus(202);
        $this->assertEquals($consent, $lead->fresh()->only(array_keys($consent)));
        $this->travelBack();
    }

    public function test_later_identity_sync_cannot_auto_link_unverified_landing_contacts_but_admin_review_can(): void
    {
        $this->postJson('/api/v1/landing/leads', $this->lead(), $this->origin())->assertStatus(202);
        $lead = TrialClick::sole();
        $user = User::factory()->create(['email' => 'other-owner@example.test', 'phone' => '+923001234567']);
        $identity = app(CustomerIdentityService::class);
        $identity->linkUser($user);
        $this->assertNull($lead->fresh()->user_id);
        $this->assertNull(CustomerActivity::sole()->user_id);
        $user->update(['email' => 'visitor@example.test']);
        $identity->linkUser($user);
        $this->assertNull($lead->fresh()->user_id);

        $admin = Admin::create(['name' => 'Isolated reviewer', 'email' => 'reviewer@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => 'owner']);
        $preview = $identity->previewRecord('trial_click', $lead->fresh());
        $identity->linkReviewed('trial_click', $lead->id, $user->id, $preview['fingerprint'], $admin->id);
        $this->assertSame($user->id, $lead->fresh()->user_id);

        $legacy = TrialClick::create(['event_id' => (string) Str::uuid(), 'phone_normalized' => '923009999999']);
        $legacyUser = User::factory()->create(['phone' => '+923009999999']);
        $identity->linkUser($legacyUser);
        $this->assertSame($legacyUser->id, $legacy->fresh()->user_id);
    }

    public function test_events_require_opt_in_and_cannot_report_a_payment_or_store_arbitrary_personal_fields(): void
    {
        $payload = $this->event();
        $payload['analytics_consent'] = false;
        $this->postJson('/api/v1/landing/events', $payload, $this->origin())->assertUnprocessable();
        $payload['analytics_consent'] = true;
        $payload['event'] = 'paid';
        $this->postJson('/api/v1/landing/events', $payload, $this->origin())->assertUnprocessable();
        $payload['event'] = 'visit';
        $payload['email'] = 'private@example.test';
        $payload['page'] = 'https://trexiptv8k.store/?private=secret';
        $payload['user_id'] = 22;
        $this->postJson('/api/v1/landing/events', $payload, $this->origin())->assertUnprocessable();
        unset($payload['page']);
        $this->postJson('/api/v1/landing/events', $payload, $this->origin())->assertStatus(202);
        $activity = CustomerActivity::sole();
        $this->assertNull($activity->user_id);
        $this->assertNull($activity->contact_email);
        $this->assertStringNotContainsString('private', json_encode($activity->getAttributes()));
        $this->assertDatabaseCount('trial_clicks', 0);
    }

    public function test_page_language_dimensions_require_consent_and_exact_public_brand_routes(): void
    {
        $payload = $this->event() + ['locale' => 'ar', 'page' => 'setup'];
        $this->postJson('/api/v1/landing/events', $payload, $this->origin())->assertStatus(202);
        $details = CustomerActivity::sole()->details;
        $this->assertSame('ar', $details['locale']);
        $this->assertSame('setup', $details['page']);
        foreach ([['locale' => 'de'], ['page' => '../account'], ['page' => 'setup?email=private'], ['page' => 'pricing']] as $invalid) {
            $request = array_merge($payload, $invalid, ['event_id' => (string) Str::uuid()]);
            $this->postJson('/api/v1/landing/events', $request, $this->origin())->assertUnprocessable();
        }
        $home = array_merge($payload, ['event_id' => (string) Str::uuid(), 'page' => '']);
        $this->postJson('/api/v1/landing/events', $home, $this->origin())->assertStatus(202);
        $this->assertSame('', CustomerActivity::latest('id')->first()->details['page']);
        $lead = $this->lead() + ['locale' => 'en', 'page' => 'contact'];
        $this->postJson('/api/v1/landing/leads', $lead, $this->origin())->assertUnprocessable();
        $lead['analytics_consent'] = true;
        $this->postJson('/api/v1/landing/leads', $lead, $this->origin())->assertUnprocessable();
        $lead['session_id'] = (string) Str::uuid();
        $this->postJson('/api/v1/landing/leads', $lead, $this->origin())->assertStatus(202);
        $this->assertSame('contact', CustomerActivity::latest('id')->first()->details['page']);
    }

    public function test_origin_brand_map_rejects_missing_cross_brand_and_lookalike_origins(): void
    {
        foreach (['', 'null', 'https://geoiptv.store', 'https://trexiptv8k.store.attacker.test', 'http://127.0.0.1:8104'] as $origin) {
            $this->postJson('/api/v1/landing/events', $this->event(), ['Origin' => $origin])->assertForbidden();
        }
        $this->assertDatabaseCount('customer_activities', 0);
        foreach (['trex', 'opplex', 'geo', '5g'] as $brand) {
            $this->postJson('/api/v1/landing/events', $this->event($brand), $this->origin($brand))->assertStatus(202);
        }
    }

    public function test_local_origins_only_work_in_local_or_testing_environment(): void
    {
        $this->postJson('/api/v1/landing/events', $this->event('geo'), ['Origin' => 'http://localhost:8103'])->assertStatus(202);
        $this->app['env'] = 'production';
        $this->postJson('/api/v1/landing/events', $this->event('geo'), ['Origin' => 'http://localhost:8103'])->assertForbidden();
        $this->postJson('/api/v1/landing/events', $this->event('geo'), ['Origin' => 'https://www.geoiptv.store'])->assertStatus(202);
        $this->app['env'] = 'testing';
    }

    public function test_invalid_contact_fields_honeypot_and_non_json_requests_do_not_write(): void
    {
        $payload = $this->lead();
        $payload['phone'] = '123';
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertUnprocessable()->assertJsonValidationErrors('phone');
        $payload = $this->lead();
        $payload['email'] = 'not an email';
        $payload['message'] = str_repeat('a', 2001);
        $payload['website'] = 'spam.test';
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertUnprocessable()->assertJsonValidationErrors(['email', 'message', 'website']);
        $this->post('/api/v1/landing/leads', $this->lead(), $this->origin())->assertStatus(415);
        $this->postJson('/api/v1/landing/leads', $this->lead() + ['padding' => str_repeat('a', 9000)], $this->origin())->assertStatus(413);
        $this->assertDatabaseCount('trial_clicks', 0);
        $this->assertDatabaseCount('customer_activities', 0);
    }

    public function test_rate_limit_blocks_contact_spam_without_inserting_duplicate_leads(): void
    {
        $payload = $this->lead();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertStatus(202);
        }
        $this->postJson('/api/v1/landing/leads', $payload, $this->origin())->assertStatus(429);
        $this->assertDatabaseCount('customer_activities', 1);
        $this->assertDatabaseCount('trial_clicks', 1);
    }

    public function test_anonymous_events_are_not_customer_matching_tasks_but_contacts_remain_reviewable(): void
    {
        $this->postJson('/api/v1/landing/events', $this->event(), $this->origin())->assertStatus(202);
        $event = $this->event();
        $event['event'] = 'checkout_clicked';
        $this->postJson('/api/v1/landing/events', $event, $this->origin())->assertStatus(202);
        $this->postJson('/api/v1/landing/leads', $this->lead(), $this->origin())->assertStatus(202);
        $this->assertSame(1, app(CustomerIdentityService::class)->unlinkedQuery('activity')->count());
        $this->assertSame(1, app(ClientQueryService::class)->unlinkedActivityCounts()['customer_activities']);
    }

    private function lead(string $brand = 'trex'): array
    {
        return [
            'brand' => $brand, 'event_id' => (string) Str::uuid(), 'name' => 'Landing visitor',
            'email' => 'visitor@example.test', 'phone' => '+92 300 1234567',
            'message' => 'Help me compare these plans.', 'channel' => 'whatsapp',
            'contact_consent' => true, 'website' => '',
        ];
    }

    private function event(string $brand = 'trex'): array
    {
        return ['brand' => $brand, 'event_id' => (string) Str::uuid(), 'session_id' => (string) Str::uuid(), 'event' => 'visit', 'analytics_consent' => true, 'website' => ''];
    }

    private function origin(string $brand = 'trex'): array
    {
        $domains = ['trex' => 'trexiptv8k.store', 'opplex' => 'opplexiptv.store', 'geo' => 'geoiptv.store', '5g' => '5gliveiptv.store'];

        return ['Origin' => 'https://'.$domains[$brand]];
    }
}
