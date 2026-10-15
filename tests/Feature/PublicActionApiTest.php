<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Services\ContactService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicActionApiTest extends TestCase
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
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Bus::fake();
    }

    public function test_contact_challenge_and_submission_are_json_and_captcha_is_single_use(): void
    {
        $this->mock(ContactService::class, function ($mock) {
            $mock->shouldReceive('contact')->once()->with(\Mockery::on(fn ($data) => $data['email'] === 'customer@example.test'));
        });
        $challenge = $this->get('/api/browser/contact/challenge')->assertOk()
            ->assertJsonStructure(['data' => ['num1', 'num2']])->assertJsonMissingPath('data.captcha_sum');
        $payload = $this->contactPayload();
        $payload['captcha'] = $challenge->json('data.num1') + $challenge->json('data.num2');
        $this->post('/api/browser/contact', $payload)->assertOk()->assertJsonStructure(['message']);
        $this->post('/api/browser/contact', $payload)->assertUnprocessable()->assertJsonValidationErrors('captcha');
    }

    public function test_contact_validation_remains_json_and_does_not_send_on_failure(): void
    {
        $this->mock(ContactService::class, function ($mock) {
            $mock->shouldNotReceive('contact');
        });
        $this->post('/api/browser/contact', [])->assertUnprocessable()->assertJsonValidationErrors(['username', 'email', 'phone', 'message', 'captcha']);
        $this->withSession(['captcha_sum' => 12])->post('/api/browser/contact', $this->contactPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('captcha');
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_legacy_contact_form_still_redirects_and_new_form_uses_api(): void
    {
        $this->mock(ContactService::class, function ($mock) {
            $mock->shouldReceive('contact')->once();
        });
        $this->withSession(['captcha_sum' => 3])->from('/contact')->post('/send-email', $this->contactPayload())
            ->assertRedirect('/contact')->assertSessionHas('success');
        $this->get('/contact')->assertOk()
            ->assertSee('data-api-form', false)
            ->assertSee('/api/browser/contact', false);
    }

    public function test_subscribe_retains_validation_and_returns_json(): void
    {
        $this->mock(ContactService::class, function ($mock) {
            $mock->shouldReceive('subscribe')->once()->with('customer@example.test');
        });
        $this->post('/api/browser/subscribe', ['email' => 'customer@example.test'])->assertOk()->assertJsonStructure(['message']);
        $this->post('/api/browser/subscribe', ['email' => 'not-an-email'])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_configure_checkout_returns_signed_opplexify_selection_without_order_or_payment_mutation(): void
    {
        config()->set('services.opplexify.shared_secret', 'isolated-public-api-secret');
        config()->set('services.opplexify.url', 'https://opplexify.com');
        $package = Package::create([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Opplex', 'active' => true,
            'is_available' => true, 'duration_months' => 3, 'price_amount' => 15,
        ]);
        $before = Order::count();
        $response = $this->get('/api/browser/checkout?package_id='.$package->id)->assertOk();
        $url = $response->json('redirect_url');
        $this->assertStringStartsWith('https://opplexify.com/checkout?', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame(hash_hmac('sha256', $query['selection'], 'isolated-public-api-secret'), $query['signature']);
        $selection = json_decode(base64_decode(strtr($query['selection'], '-_', '+/')), true);
        $this->assertSame($package->id, $selection['package_id']);
        $this->assertGreaterThan(now()->timestamp, $selection['exp']);
        $this->assertSame($before, Order::count());
        $this->get('/checkout?package_id='.$package->id)->assertRedirect();
        $this->get('/configure')->assertOk()->assertSee('/api/browser/checkout', false)->assertSee('data-api-form', false);
    }

    public function test_missing_or_unavailable_checkout_selection_returns_validation_in_json(): void
    {
        $this->get('/api/browser/checkout')->assertUnprocessable()->assertJsonValidationErrors('package_id');
        $this->get('/api/browser/checkout?package_id=999999')->assertUnprocessable()->assertJsonValidationErrors('package_id');
        $this->get('/api/browser/checkout?package_id[]=1')->assertUnprocessable();
    }

    private function contactPayload(): array
    {
        return [
            'username' => 'Test customer', 'email' => 'customer@example.test',
            'phone' => '+12025550101', 'message' => 'Please help with setup.', 'captcha' => 3,
        ];
    }
}
