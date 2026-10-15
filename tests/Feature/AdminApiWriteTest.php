<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\User;
use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminApiWriteTest extends TestCase
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
        Http::preventStrayRequests();
        Bus::fake(); Mail::fake(); Notification::fake();
    }

    private function signIn(): Admin
    {
        $admin = Admin::create(['name' => 'API Owner', 'email' => 'owner@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => 'owner', 'auth_version' => 1, 'must_change_password' => false, 'two_factor_confirmed_at' => now()]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
        return $admin;
    }

    public function test_customer_create_update_delete_are_json_and_keep_existing_audit(): void
    {
        $this->signIn();
        $this->postJson('/api/browser/admin/clients', ['name' => 'API Customer', 'email' => 'client@example.test', 'phone' => '+923001234567', 'country' => 'Pakistan'])->assertOk()->assertJsonPath('redirect_url', '/admin/clients');
        $client = User::where('email', 'client@example.test')->firstOrFail();
        $this->putJson('/api/browser/admin/clients/'.$client->id, ['name' => 'Updated Customer', 'email' => $client->email, 'phone' => $client->phone, 'country' => 'Pakistan'])->assertOk();
        $this->assertSame('Updated Customer', $client->fresh()->name);
        $this->deleteJson('/api/browser/admin/clients/'.$client->id)->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $client->id]);
        $this->assertSame(3, AdminAuditLog::where('route_name', 'like', 'api.browser.admin.clients.%')->count());
    }

    public function test_invalid_payload_returns_422_and_preserves_records(): void
    {
        $this->signIn();
        $this->postJson('/api/browser/admin/clients', ['name' => ''])->assertUnprocessable()->assertJsonValidationErrors(['name', 'phone']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_content_mutation_uses_existing_validation_and_redirect_contract(): void
    {
        $this->signIn();
        $this->postJson('/api/browser/admin/social-links', ['platform' => 'Support', 'url' => 'https://example.test/support', 'is_active' => true])->assertOk()->assertJsonPath('redirect_url', '/admin/social-links');
        $link = SocialLink::firstOrFail();
        $this->getJson('/api/browser/admin/social-links/'.$link->id.'/edit')->assertOk()->assertJsonPath('data.link.platform', 'Support');
        $this->deleteJson('/api/browser/admin/social-links/'.$link->id)->assertOk();
        $this->assertDatabaseCount('social_links', 0);
    }

    public function test_failed_login_is_json_422_and_never_a_false_success(): void
    {
        $this->getJson('/api/browser/admin/login')->assertOk();
        $this->postJson('/api/browser/admin/login', ['email' => 'nobody@example.test', 'password' => 'Wrong!Password123', 'captcha' => '1000'])->assertUnprocessable()->assertJsonValidationErrors('captcha');
        $this->assertGuest('admin');
    }
}
