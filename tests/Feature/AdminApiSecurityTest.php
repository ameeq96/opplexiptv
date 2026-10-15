<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminApiSecurityTest extends TestCase
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

    private function signIn(string $role = 'owner'): Admin
    {
        $admin = Admin::create(['name' => 'API Admin', 'email' => 'admin@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => $role, 'auth_version' => 1, 'must_change_password' => false, 'two_factor_confirmed_at' => now()]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
        return $admin;
    }

    public function test_guest_and_external_read_tokens_cannot_access_admin_data_or_writes(): void
    {
        $this->getJson('/api/browser/admin/clients')->assertUnauthorized();
        $admin = Admin::create(['name' => 'Token owner', 'email' => 'token@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => 'owner', 'auth_version' => 1, 'must_change_password' => false]);
        $token = $admin->createToken('isolated read token', ['data-api', 'auth-version:1', 'customers:read'], now()->addDay());
        $this->withToken($token->plainTextToken)->getJson('/api/browser/admin/clients')->assertUnauthorized();
        $this->postJson('/api/browser/admin/clients', ['name' => 'Do not create'])->assertUnauthorized();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_support_and_content_permissions_remain_separate(): void
    {
        $admin = $this->signIn('support');
        $this->getJson('/api/browser/admin/clients')->assertOk();
        $this->getJson('/api/browser/admin/packages')->assertForbidden();
        $client = User::factory()->create();
        $this->deleteJson('/api/browser/admin/clients/'.$client->id)->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $client->id]);
        $admin->update(['role' => 'content']);
        $this->getJson('/api/browser/admin/packages')->assertOk();
        $this->getJson('/api/browser/admin/clients')->assertForbidden();
    }

    public function test_sensitive_admin_management_still_requires_recent_confirmation(): void
    {
        $this->signIn();
        $response = $this->getJson('/api/browser/admin/admin-users');
        $this->assertContains($response->status(), [403, 428]);
        $response->assertJsonStructure(['message', 'redirect_url']);
    }

    public function test_stale_session_password_change_and_two_factor_are_not_bypassed(): void
    {
        $admin = $this->signIn();
        $admin->update(['must_change_password' => true]);
        $response = $this->getJson('/api/browser/admin/clients');
        $this->assertContains($response->status(), [403, 428]);
        $admin->update(['must_change_password' => false]);
        $this->withSession(['admin_two_factor_verified_id' => null]);
        $response = $this->getJson('/api/browser/admin/clients');
        $this->assertContains($response->status(), [403, 428]);
        $this->withSession(['admin_two_factor_verified_id' => $admin->id, 'admin_auth_version' => 0]);
        $this->getJson('/api/browser/admin/clients')->assertUnauthorized();
    }

    public function test_admin_csrf_is_required_even_with_an_authenticated_session(): void
    {
        $this->signIn();
        $this->app->bind(VerifyCsrfToken::class, AdminApiEnforcedCsrf::class);
        $this->postJson('/api/browser/admin/clients', ['name' => 'Blocked', 'phone' => '+923001234567'])->assertStatus(419);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_standalone_security_pages_load_the_protected_api_client(): void
    {
        $admin = $this->signIn();
        $admin->update(['must_change_password' => true]);
        $this->get('/admin/password/change')->assertOk()
            ->assertSee('name="csrf-token"', false)
            ->assertSee('js/api-browser.js')->assertSee('js/admin-api.js');
        $this->getJson('/api/browser/admin/password/change')->assertOk()
            ->assertJsonPath('view', 'auth.change-password');

        $admin->update(['must_change_password' => false]);
        $this->withSession(['admin_two_factor_verified_id' => null]);
        $this->get('/admin/two-factor-challenge')->assertOk()
            ->assertSee('name="csrf-token"', false)
            ->assertSee('js/api-browser.js')->assertSee('js/admin-api.js');
        $this->getJson('/api/browser/admin/two-factor-challenge')->assertOk()
            ->assertJsonPath('view', 'auth.two-factor-challenge');
    }
}

class AdminApiEnforcedCsrf extends VerifyCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}
