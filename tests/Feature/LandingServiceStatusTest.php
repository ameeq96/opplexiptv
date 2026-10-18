<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ServiceIncident;
use App\Services\LandingExperienceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LandingServiceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests(); Bus::fake(); Mail::fake(); Notification::fake();
    }

    public function test_no_seeded_status_and_only_published_matching_language_incidents_are_returned(): void
    {
        $service = app(LandingExperienceService::class);
        $this->assertDatabaseCount('service_incidents', 0);
        $this->assertSame(['incidents' => [], 'reviews' => [], 'media' => []], $service->publicData('trex', 'en'));
        $row = ['brand' => 'trex', 'locale' => 'en', 'title' => 'Isolated update', 'body' => 'Test incident only, never published live.', 'state' => 'investigating', 'started_at' => now()->subHour(), 'is_published' => true, 'published_at' => now()];
        ServiceIncident::create($row);
        ServiceIncident::create(array_merge($row, ['brand' => 'geo']));
        ServiceIncident::create(array_merge($row, ['locale' => 'ar']));
        ServiceIncident::create(array_merge($row, ['is_published' => false]));
        $data = $service->publicData('trex', 'en');
        $this->assertCount(1, $data['incidents']);
        $this->assertSame('investigating', $data['incidents'][0]['state']);
        $this->assertArrayNotHasKey('healthy', $data);
        $this->assertArrayNotHasKey('uptime', $data);
    }

    public function test_incident_management_rejects_false_resolution_dates_html_and_future_starts(): void
    {
        $this->signIn();
        $url = '/api/browser/admin/landing-experience/incidents';
        $payload = ['brand' => 'trex', 'locale' => 'en', 'title' => 'Isolated maintenance', 'body' => 'Controlled test update only.', 'state' => 'investigating', 'started_at' => now()->subHour()->toIso8601String(), 'is_published' => false];
        $this->postJson($url, array_merge($payload, ['state' => 'resolved']))->assertUnprocessable();
        $this->postJson($url, array_merge($payload, ['started_at' => now()->addHour()->toIso8601String()]))->assertUnprocessable();
        $this->postJson($url, array_merge($payload, ['body' => '<b>Not plain</b>']))->assertUnprocessable();
        $this->postJson($url, $payload)->assertOk();
        $incident = ServiceIncident::firstOrFail();
        $this->assertFalse($incident->is_published);
        $this->putJson($url.'/'.$incident->id, array_merge($payload, ['state' => 'resolved', 'resolved_at' => now()->subMinute()->toIso8601String(), 'is_published' => true]))->assertOk();
        $this->assertSame('resolved', app(LandingExperienceService::class)->publicData('trex', 'en')['incidents'][0]['state']);
    }

    private function signIn(): void
    {
        $admin = Admin::create(['name' => 'Isolated editor', 'email' => 'status@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => 'content', 'auth_version' => 1, 'must_change_password' => false]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
    }
}
