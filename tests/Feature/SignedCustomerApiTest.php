<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\CustomerAccountAccessMail;
use App\Models\MarketingDelivery;
use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use App\Services\EventPromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class SignedCustomerApiTest extends TestCase
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
        Storage::fake('local');
    }

    public function test_access_request_is_json_generic_and_email_link_stays_legacy_compatible(): void
    {
        $this->postJson('/api/browser/account/access', ['email' => 'FIRST@example.test'])->assertOk()->assertJsonStructure(['message'])->assertJsonMissingPath('token');
        Mail::assertSent(CustomerAccountAccessMail::class, function ($mail) {
            $this->assertTrue(URL::hasValidSignature(Request::create($mail->accessUrl)));
            $this->assertSame('/account/access', parse_url($mail->accessUrl, PHP_URL_PATH));
            return $mail->hasTo('first@example.test');
        });
        $this->assertGuest('web');
        $this->postJson('/api/browser/account/access', ['email' => 'not-an-email'])->assertUnprocessable();
    }

    public function test_legacy_access_page_generates_independently_signed_api_action_without_consuming(): void
    {
        $customer = User::factory()->unverified()->create();
        $token = Password::broker('users')->createToken($customer);
        $expires = now()->addMinutes(8);
        $legacy = URL::temporarySignedRoute('customer.access', $expires, ['email' => $customer->email, 'token' => $token]);
        $response = $this->get($legacy)->assertOk();
        $action = $response->viewData('action');
        $this->assertSame('/api/browser/account/access/confirm', parse_url($action, PHP_URL_PATH));
        $this->assertTrue(URL::hasValidSignature(Request::create($action)));
        parse_str(parse_url($action, PHP_URL_QUERY), $parameters);
        $this->assertSame($expires->timestamp, (int) $parameters['expires']);
        $this->assertTrue(Password::broker('users')->tokenExists($customer, $token));
        $this->assertNull($customer->fresh()->email_verified_at);
        $this->postJson($action)->assertOk()->assertJsonPath('redirect_url', route('customer.account'));
        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertNotNull($customer->fresh()->email_verified_at);
        $this->assertFalse(Password::broker('users')->tokenExists($customer, $token));
        $this->postJson($action)->assertUnprocessable()->assertJsonValidationErrors('access');
    }

    public function test_api_login_identity_is_signed_query_not_body_and_old_post_still_works(): void
    {
        $customer = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();
        $token = Password::broker('users')->createToken($customer);
        $otherToken = Password::broker('users')->createToken($other);
        $action = URL::temporarySignedRoute('api.browser.customer.access.consume', now()->addMinutes(10), ['email' => $customer->email, 'token' => $token]);
        $this->postJson($action, ['email' => $other->email, 'token' => $otherToken])->assertOk();
        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertNull($other->fresh()->email_verified_at);
        Auth::guard('web')->logout();
        $legacy = URL::temporarySignedRoute('customer.access.consume', now()->addMinutes(10), ['email' => $other->email, 'token' => $otherToken]);
        $this->post($legacy)->assertRedirect(route('customer.account'));
        $this->assertAuthenticatedAs($other, 'web');
    }

    public function test_signature_path_tampering_expiry_and_csrf_are_not_bypassed(): void
    {
        $customer = User::factory()->unverified()->create();
        $token = Password::broker('users')->createToken($customer);
        $legacy = URL::temporarySignedRoute('customer.access.consume', now()->addMinutes(10), ['email' => $customer->email, 'token' => $token]);
        $this->postJson(str_replace('/account/access/confirm', '/api/browser/account/access/confirm', $legacy))->assertForbidden();
        $expired = URL::temporarySignedRoute('api.browser.customer.access.consume', now()->subSecond(), ['email' => $customer->email, 'token' => $token]);
        $this->postJson($expired)->assertForbidden();
        $this->app->instance(VerifyCsrfToken::class, new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken {
            protected function runningUnitTests() { return false; }
        });
        $valid = URL::temporarySignedRoute('api.browser.customer.access.consume', now()->addMinutes(10), ['email' => $customer->email, 'token' => $token]);
        $this->postJson($valid)->assertStatus(419);
        $this->assertTrue(Password::broker('users')->tokenExists($customer, $token));
        $this->assertGuest('web');
    }

    public function test_signed_status_page_is_data_free_and_its_api_is_allowlisted_and_expires(): void
    {
        $order = $this->order(['package' => 'Private signed package', 'iptv_username' => 'secret-login', 'note' => 'secret staff note']);
        $expires = now()->addMinutes(7);
        $legacy = URL::temporarySignedRoute('orders.status', $expires, ['orderId' => $order->id]);
        $shell = $this->get($legacy)->assertOk()->assertDontSee('Private signed package');
        $url = $shell->viewData('statusApiUrl');
        $this->assertTrue(URL::hasValidSignature(Request::create($url)));
        $response = $this->getJson($url)->assertOk()->assertJsonPath('data.id', $order->id)->assertJsonPath('data.summary.package', 'Private signed package');
        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('secret-login', $response->getContent());
        $this->assertStringNotContainsString('secret staff note', $response->getContent());
        $this->assertArrayNotHasKey('pictures', $response->json('data'));
        parse_str(parse_url($response->json('data.proof_upload_url'), PHP_URL_QUERY), $proofParameters);
        $this->assertLessThanOrEqual($expires->timestamp, (int) $proofParameters['expires']);
        $this->getJson('/api/browser/orders/'.$order->id.'/status')->assertForbidden();
        $this->getJson(str_replace('/orders/', '/api/browser/orders/', $legacy))->assertForbidden();
        $this->travel(8)->minutes();
        $this->getJson($url)->assertForbidden();
    }

    public function test_payment_proof_upload_stays_private_and_does_not_confirm_payment(): void
    {
        $order = $this->order();
        $url = URL::temporarySignedRoute('api.browser.orders.payment-proof', now()->addMinutes(10), ['orderId' => $order->id]);
        $this->postJson($url, ['payment_proof' => UploadedFile::fake()->image('proof.png')])->assertOk()->assertJsonStructure(['message', 'redirect_url']);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $picture = $order->pictures()->sole();
        $this->assertStringStartsWith('private/payment-proofs/', $picture->path);
        Storage::disk('local')->assertExists($picture->path);
        $this->assertNull($order->fresh()->paid_at);
        $this->postJson($url, ['payment_proof' => UploadedFile::fake()->create('proof.txt', 1, 'text/plain')])->assertUnprocessable();
        $this->postJson($url, ['payment_proof' => UploadedFile::fake()->image('large.png')->size(5121)])->assertUnprocessable();
        $this->assertSame(1, $order->pictures()->count());
    }

    public function test_status_shell_bridges_matching_checkout_flash_once_and_expires_context(): void
    {
        $order = $this->order();
        $legacy = URL::temporarySignedRoute('orders.status', now()->addMinutes(10), ['orderId' => $order->id]);
        $shell = $this->withSession(['order_summary' => ['id' => $order->id, 'vendor' => 'opplex'], 'checkout_completed' => true, 'success' => 'Fixture order saved'])->get($legacy)->assertOk();
        session()->forget(['order_summary', 'checkout_completed', 'success']);
        $url = $shell->viewData('statusApiUrl');
        $this->getJson($url)->assertOk()->assertJsonPath('data.checkout_completed', true)->assertJsonPath('data.summary.vendor', 'opplex')->assertJsonPath('data.message', 'Fixture order saved');
        $this->getJson($url)->assertOk()->assertJsonPath('data.checkout_completed', false)->assertJsonPath('data.message', null);
        $this->withSession(['customer_status_context.'.$order->id => ['summary' => ['id' => $order->id, 'vendor' => 'expired'], 'expires_at' => now()->subSecond()->timestamp, 'checkout_completed' => true]])->getJson($url)->assertOk()->assertJsonPath('data.checkout_completed', false)->assertJsonPath('data.summary.vendor', null);
    }

    public function test_payment_proof_limits_and_paid_order_guards_apply_to_api_and_legacy(): void
    {
        $order = $this->order();
        $url = URL::temporarySignedRoute('api.browser.orders.payment-proof', now()->addMinutes(10), ['orderId' => $order->id]);
        for ($i = 0; $i < 3; $i++) $this->postJson($url, ['payment_proof' => UploadedFile::fake()->image('proof.png')])->assertOk();
        $this->postJson($url, ['payment_proof' => UploadedFile::fake()->image('proof.png')])->assertUnprocessable();
        $this->assertSame(3, $order->pictures()->count());
        $paid = $this->order(['payment_status' => 'paid']);
        $paidUrl = URL::temporarySignedRoute('api.browser.orders.payment-proof', now()->addMinutes(10), ['orderId' => $paid->id]);
        $this->postJson($paidUrl, ['payment_proof' => UploadedFile::fake()->image('proof.png')])->assertUnprocessable();
        $legacyOrder = $this->order();
        $legacy = URL::temporarySignedRoute('orders.payment-proof', now()->addMinutes(10), ['orderId' => $legacyOrder->id]);
        $this->post($legacy, ['payment_proof' => UploadedFile::fake()->image('legacy.png')])->assertStatus(429);
        $this->travel(11)->minutes();
        $legacy = URL::temporarySignedRoute('orders.payment-proof', now()->addMinutes(10), ['orderId' => $legacyOrder->id]);
        $this->post($legacy, ['payment_proof' => UploadedFile::fake()->image('legacy.png')])->assertRedirect();
        $this->assertSame(1, $legacyOrder->pictures()->count());
    }

    public function test_renewal_unsubscribe_get_only_confirms_and_api_changes_only_signed_recipient(): void
    {
        $customer = User::factory()->create(['renewal_email_enabled' => true, 'marketing_email_consented_at' => now()]);
        $other = User::factory()->create(['renewal_email_enabled' => true]);
        $delivery = $this->delivery($customer, ['workflow' => 'renewal']);
        $legacy = URL::temporarySignedRoute('customer.renewal.unsubscribe', now()->addMinutes(10), ['user' => $customer->id]);
        $action = $this->get($legacy)->assertOk()->viewData('action');
        $this->assertTrue($customer->fresh()->renewal_email_enabled);
        $this->postJson($action, ['user' => $other->id])->assertOk()->assertJsonPath('data.confirmed', true);
        $this->assertFalse($customer->fresh()->renewal_email_enabled);
        $this->assertTrue($other->fresh()->renewal_email_enabled);
        $this->assertNotNull($customer->fresh()->marketing_email_consented_at);
        $this->assertNotNull($delivery->fresh()->failed_at);
    }

    public function test_marketing_unsubscribe_cannot_replace_signed_user_or_channel_with_body(): void
    {
        $customer = User::factory()->create(['renewal_email_enabled' => true]);
        $other = User::factory()->create();
        $delivery = $this->delivery($customer);
        $legacy = URL::temporarySignedRoute('marketing.unsubscribe', now()->addMinutes(10), ['user' => $customer->id, 'channel' => 'email']);
        $action = $this->get($legacy)->assertOk()->viewData('signedAction');
        $this->assertNull($customer->fresh()->marketing_email_opted_out_at);
        $this->postJson($action, ['user' => $other->id, 'channel' => 'all'])->assertOk();
        $this->assertNotNull($customer->fresh()->marketing_email_opted_out_at);
        $this->assertNull($customer->fresh()->marketing_whatsapp_opted_out_at);
        $this->assertNull($other->fresh()->marketing_email_opted_out_at);
        $this->assertTrue($customer->fresh()->renewal_email_enabled);
        $this->assertNotNull($delivery->fresh()->failed_at);
        $this->postJson('/api/browser/marketing/unsubscribe?user='.$customer->id.'&channel=email')->assertForbidden();
    }

    public function test_referral_api_preserves_capture_and_rejects_used_code(): void
    {
        $order = $this->order();
        $referral = Referral::create(['code' => 'ApiReferralFixture', 'referrer_user_id' => $order->user_id, 'source_order_id' => $order->id, 'status' => 'available']);
        $this->postJson('/api/browser/referrals/'.$referral->code)->assertOk()->assertJsonPath('redirect_url', route('pricing'))->assertSessionHas('referral_code', $referral->code);
        $this->get(route('referrals.capture', $referral->code))->assertRedirect(route('pricing'));
        $referral->update(['status' => 'rewarded']);
        $this->postJson('/api/browser/referrals/'.$referral->code)->assertNotFound();
    }

    public function test_promotion_api_requires_its_own_signature_and_does_not_leak_delivery_payload(): void
    {
        $delivery = $this->delivery(User::factory()->create(), ['payload' => ['internal' => 'private campaign payload']]);
        $this->mock(EventPromotionService::class, function ($mock) use ($delivery) {
            $mock->shouldReceive('activate')->once()->withArgs(fn ($record) => $record->id === $delivery->id)->andReturn(['name' => 'Fixture promotion']);
        });
        $url = URL::temporarySignedRoute('api.browser.event-promotions.activate', now()->addMinutes(10), ['delivery' => $delivery->id]);
        $response = $this->postJson($url)->assertOk()->assertJsonPath('data.discount_percent', 10);
        $this->assertStringNotContainsString('private campaign payload', $response->getContent());
        $legacy = URL::temporarySignedRoute('event-promotions.activate', now()->addMinutes(10), ['delivery' => $delivery->id]);
        $this->postJson(str_replace('/event-promotion/', '/api/browser/event-promotion/', $legacy))->assertForbidden();
    }

    private function order(array $attributes = []): Order
    {
        return Order::withoutEvents(fn () => Order::create(array_replace(['user_id' => User::factory()->create()->id, 'package' => 'Opplex', 'type' => 'package', 'price' => 15, 'currency' => 'USD', 'status' => 'pending', 'payment_status' => 'unpaid', 'payment_method' => 'card'], $attributes)));
    }

    private function delivery(User $customer, array $attributes = []): MarketingDelivery
    {
        return MarketingDelivery::create(array_replace(['dedupe_key' => (string) Str::uuid(), 'workflow' => 'promotion', 'stage' => 'fixture', 'channel' => 'email', 'user_id' => $customer->id, 'scheduled_at' => now()], $attributes));
    }
}
