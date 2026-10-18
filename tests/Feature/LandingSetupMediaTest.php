<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SetupMedia;
use App\Services\LandingExperienceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LandingSetupMediaTest extends TestCase
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
        $admin = Admin::create(['name' => 'Isolated editor', 'email' => 'media@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => 'content', 'auth_version' => 1, 'must_change_password' => false]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
    }

    public function test_missing_external_or_executable_media_cannot_be_registered(): void
    {
        $this->assertDatabaseCount('setup_media', 0);
        foreach (['https://example.test/images/demo.webp', 'https://opplexiptv.com/images/missing-test-media.webp', 'https://opplexiptv.com/images/../secret.webp', 'https://opplexiptv.com/images/televizo.webp?token=private', 'https://opplexiptv.com/images/demo.svg', 'https://user@opplexiptv.com/images/televizo.webp'] as $url) {
            $this->postJson('/api/browser/admin/landing-experience/media', array_merge($this->payload(), ['media_url' => $url]))->assertUnprocessable();
        }
        $this->assertDatabaseCount('setup_media', 0);
    }

    public function test_media_requires_evidence_and_permission_and_exposes_no_private_reference(): void
    {
        $url = '/api/browser/admin/landing-experience/media';
        $this->postJson($url, array_merge($this->payload(), ['evidence_reference' => '']))->assertUnprocessable();
        $this->postJson($url, array_merge($this->payload(), ['publication_permission' => false]))->assertUnprocessable();
        $this->postJson($url, array_merge($this->payload(), ['guide_slug' => 'missing-page']))->assertUnprocessable();
        $this->postJson($url, array_merge($this->payload(), ['tested_at' => now()->addDay()->toIso8601String()]))->assertUnprocessable();
        $this->postJson($url, $this->payload())->assertOk();
        $entry = SetupMedia::firstOrFail();
        $service = app(LandingExperienceService::class);
        $public = $service->publicData('trex', 'en')['media'];
        $this->assertCount(1, $public);
        $this->assertSame('Isolated device, not real testing evidence', $public[0]['device_model']);
        $this->assertArrayNotHasKey('evidence_reference', $public[0]);
        $this->assertArrayNotHasKey('updated_by_admin_id', $public[0]);
        $this->assertSame([], $service->publicData('geo', 'en')['media']);
        $entry->update(['publication_permission_at' => null]);
        $this->assertSame([], $service->publicData('trex', 'en')['media']);
    }

    private function payload(): array
    {
        return ['brand' => 'trex', 'locale' => 'en', 'guide_slug' => 'setup', 'title' => 'Isolated test media', 'description' => 'Existing image used only to test validation; not a real tutorial.', 'kind' => 'image', 'media_url' => 'https://opplexiptv.com/images/televizo.webp', 'alt_text' => 'Isolated image reference', 'device_model' => 'Isolated device, not real testing evidence', 'player_name' => 'Isolated player', 'player_version' => 'test-only', 'tested_at' => now()->subDay()->toIso8601String(), 'evidence_reference' => 'isolated-proof-only', 'publication_permission' => true, 'is_published' => true];
    }
}
