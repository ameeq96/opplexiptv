<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\CustomerExternalLink;
use App\Models\CustomerReview;
use App\Models\Order;
use App\Models\User;
use App\Services\LandingExperienceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingExperienceAdminTest extends TestCase
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

    public function test_management_requires_existing_admin_roles_and_preserves_audit(): void
    {
        $url = '/api/browser/admin/landing-experience';
        $this->getJson($url)->assertUnauthorized();
        $this->signIn('sales');
        $this->getJson($url)->assertForbidden();
        $this->signIn('support');
        $this->getJson($url)->assertForbidden();
        $admin = $this->signIn('content');
        $this->getJson($url)->assertOk()->assertJsonPath('data.incidents', [])->assertJsonPath('data.media', [])->assertJsonPath('data.reviews', []);
        $this->get('/admin/landing-experience')->assertOk()->assertSeeText('No customer reviews have been submitted.');
        $payload = ['brand' => 'geo', 'locale' => 'en', 'title' => 'Isolated unpublished update', 'body' => 'This is only isolated test data.', 'state' => 'investigating', 'started_at' => now()->subHour()->toIso8601String(), 'is_published' => false];
        $this->postJson($url.'/incidents', $payload)->assertOk();
        $this->assertDatabaseHas('admin_audit_logs', ['admin_id' => $admin->id, 'route_name' => 'api.browser.admin.landing-experience.incidents.store', 'response_status' => 200]);
    }

    public function test_moderation_does_not_rewrite_feedback_or_override_withdrawn_consent(): void
    {
        $this->signIn('owner');
        $user = User::factory()->create();
        $order = Order::withoutEvents(fn () => Order::create(['user_id' => $user->id, 'package' => 'Isolated purchase', 'type' => 'package', 'price' => 12, 'currency' => 'USD', 'status' => 'active', 'payment_provider' => 'safepay', 'payment_status' => 'paid', 'paid_at' => now()]));
        CustomerExternalLink::create(['source' => 'opplexify', 'entity_type' => 'landing_attribution', 'external_id' => (string) Str::uuid(), 'order_id' => $order->id, 'user_id' => $user->id, 'state' => ['environment' => 'production', 'attribution' => ['brand' => 'opplex']]]);
        $review = CustomerReview::create(['user_id' => $user->id, 'order_id' => $order->id, 'brand' => 'opplex', 'locale' => 'en', 'display_name' => 'Isolated reviewer', 'rating' => 1, 'body' => 'Negative feedback uses the same moderation process.', 'status' => 'pending', 'publication_consented_at' => now()]);
        $url = '/api/browser/admin/landing-experience/reviews/'.$review->id;
        $this->patchJson($url, ['action' => 'publish', 'body' => 'Unauthorized rewrite', 'moderation_note' => 'Isolated permission checked'])->assertOk();
        $this->assertSame(1, $review->fresh()->rating);
        $this->assertSame('Negative feedback uses the same moderation process.', $review->fresh()->body);
        $this->assertCount(1, app(LandingExperienceService::class)->publicData('opplex', 'en')['reviews']);
        $review->update(['publication_consented_at' => null, 'withdrawn_at' => now(), 'status' => 'withdrawn', 'published_at' => null]);
        $this->patchJson($url, ['action' => 'publish'])->assertUnprocessable();
        $this->assertSame('withdrawn', $review->fresh()->status);
    }

    public function test_csrf_current_session_password_and_two_factor_requirements_remain_in_force(): void
    {
        $admin = $this->signIn('owner');
        $url = '/api/browser/admin/landing-experience';
        $this->app->bind(VerifyCsrfToken::class, LandingExperienceEnforcedCsrf::class);
        $this->postJson($url.'/incidents', [])->assertStatus(419);
        $admin->update(['must_change_password' => true]);
        $this->assertContains($this->getJson($url)->status(), [403, 428]);
        $admin->update(['must_change_password' => false]);
        $this->withSession(['admin_two_factor_verified_id' => null]);
        $this->assertContains($this->getJson($url)->status(), [403, 428]);
        $this->withSession(['admin_two_factor_verified_id' => $admin->id, 'admin_auth_version' => 0]);
        $this->getJson($url)->assertUnauthorized();
        $this->assertDatabaseCount('service_incidents', 0);
    }

    private function signIn(string $role): Admin
    {
        $admin = Admin::create(['name' => 'Isolated editor', 'email' => Str::uuid().'@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => $role, 'auth_version' => 1, 'must_change_password' => false]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
        return $admin;
    }
}

class LandingExperienceEnforcedCsrf extends VerifyCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
