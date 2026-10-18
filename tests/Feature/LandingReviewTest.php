<?php

namespace Tests\Feature;

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

class LandingReviewTest extends TestCase
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
        Bus::fake();
        Mail::fake();
        Notification::fake();
    }

    public function test_reviews_require_verified_customer_session_owned_order_and_explicit_consent(): void
    {
        [$user, $order] = $this->purchase();
        $url = '/api/browser/account/orders/'.$order->id.'/review';
        $this->getJson($url)->assertUnauthorized();
        $this->signIn(User::factory()->create());
        $this->postJson($url, $this->payload())->assertNotFound();
        $this->signIn($user);
        $this->postJson($url, array_merge($this->payload(), ['publication_consent' => false]))->assertUnprocessable();
        $this->postJson($url, array_merge($this->payload(), ['body' => '<script>alert(1)</script>']))->assertUnprocessable();
        $this->postJson($url, $this->payload() + ['brand' => 'geo', 'user_id' => 999, 'status' => 'published'])
            ->assertOk()->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('customer_reviews', ['order_id' => $order->id, 'user_id' => $user->id, 'brand' => 'trex', 'status' => 'pending']);
        $this->assertSame([], app(LandingExperienceService::class)->publicData('trex', 'en')['reviews']);
        Mail::assertNothingSent();
    }

    public function test_review_edit_resets_moderation_and_withdrawal_removes_publication_immediately(): void
    {
        [$user, $order] = $this->purchase();
        $this->signIn($user);
        $url = '/api/browser/account/orders/'.$order->id.'/review';
        $this->postJson($url, $this->payload())->assertOk();
        $review = CustomerReview::firstOrFail();
        $review->update(['status' => 'published', 'published_at' => now(), 'moderated_at' => now()]);
        $public = app(LandingExperienceService::class)->publicData('trex', 'en')['reviews'];
        $this->assertTrue($public[0]['verified_purchase']);
        foreach (['order_id', 'user_id', 'email', 'moderation_note', 'publication_consented_at'] as $private) $this->assertArrayNotHasKey($private, $public[0]);
        $this->postJson($url, array_merge($this->payload(), ['rating' => 1, 'body' => 'The experience needed more support.']))->assertOk();
        $this->assertDatabaseCount('customer_reviews', 1);
        $this->assertSame('pending', $review->fresh()->status);
        $this->assertNull($review->fresh()->published_at);
        $review->update(['status' => 'published', 'published_at' => now()]);
        $this->deleteJson($url)->assertOk();
        $this->assertSame('withdrawn', $review->fresh()->status);
        $this->assertSame([], app(LandingExperienceService::class)->publicData('trex', 'en')['reviews']);
    }

    public function test_test_payments_missing_attribution_and_changed_payment_records_never_get_verified_badges(): void
    {
        [$user, $order, $binding] = $this->purchase();
        $this->signIn($user);
        $url = '/api/browser/account/orders/'.$order->id.'/review';
        $order->update(['payment_provider' => 'safepay_sandbox', 'payment_status' => 'test_paid']);
        $this->postJson($url, $this->payload())->assertUnprocessable();
        $order->update(['payment_provider' => 'safepay', 'payment_status' => 'paid']);
        $binding->update(['state' => ['environment' => 'sandbox', 'attribution' => ['brand' => 'trex']]]);
        $this->postJson($url, $this->payload())->assertUnprocessable();
        $binding->update(['state' => ['environment' => 'production', 'attribution' => ['brand' => 'trex']]]);
        $this->postJson($url, $this->payload())->assertOk();
        CustomerReview::firstOrFail()->update(['status' => 'published', 'published_at' => now()]);
        $order->update(['payment_status' => 'refunded']);
        $this->assertSame([], app(LandingExperienceService::class)->publicData('trex', 'en')['reviews']);
    }

    private function purchase(): array
    {
        $user = User::factory()->create();
        $order = Order::withoutEvents(fn () => Order::create(['user_id' => $user->id, 'package' => 'Isolated purchase', 'type' => 'package', 'price' => 12, 'currency' => 'USD', 'status' => 'active', 'payment_provider' => 'safepay', 'payment_status' => 'paid', 'paid_at' => now()]));
        $binding = CustomerExternalLink::create(['source' => 'opplexify', 'entity_type' => 'landing_attribution', 'external_id' => (string) Str::uuid(), 'order_id' => $order->id, 'user_id' => $user->id, 'state' => ['environment' => 'production', 'attribution' => ['brand' => 'trex']]]);
        return [$user, $order, $binding];
    }

    private function signIn(User $user): void
    {
        $this->actingAs($user, 'web')->withSession(['customer_verified_email' => hash('sha256', User::normalizeEmail($user->email))]);
    }

    private function payload(): array
    {
        return ['display_name' => 'Isolated reviewer', 'rating' => 3, 'body' => 'This is private isolated test feedback.', 'locale' => 'en', 'publication_consent' => true];
    }
}
