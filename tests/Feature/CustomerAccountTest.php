<?php

namespace Tests\Feature;

use App\Mail\CustomerAccountAccessMail;
use App\Models\CustomerExternalLink;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
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
        Mail::fake();
        Notification::fake();
        Http::preventStrayRequests();
        config(['services.opplexify.url' => 'http://127.0.0.1:3000']);
    }

    public function test_requesting_account_access_sends_a_private_link_to_the_normalized_email(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'owner@example.test']);

        $response = $this->post(route('customer.access.request'), ['email' => 'OWNER@example.test']);

        $response->assertRedirect(route('customer.login'))->assertSessionHas('status');
        Mail::assertSent(CustomerAccountAccessMail::class, function ($mail) {
            parse_str(parse_url($mail->accessUrl, PHP_URL_QUERY), $query);
            return $mail->hasTo('owner@example.test')
                && $query['email'] === 'owner@example.test'
                && strlen($query['token']) === 64
                && isset($query['signature'], $query['expires']);
        });
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertGuest('web');
    }

    public function test_opening_a_link_does_not_consume_it_but_confirming_signs_in_once(): void
    {
        $user = User::factory()->unverified()->create();
        $token = Password::broker('users')->createToken($user);
        $url = $this->accessLink($user, $token);

        $response = $this->get($url)->assertOk()->assertSee('Open my account');
        $this->assertTrue(Password::broker('users')->tokenExists($user, $token));
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertGuest('web');
        $this->get($url)->assertOk();
        $action = $response->viewData('action');

        $this->postJson($action)->assertOk()->assertJsonPath('redirect_url', route('customer.account'))
            ->assertSessionHas('customer_verified_email', hash('sha256', User::normalizeEmail($user->email)));
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertFalse(Password::broker('users')->tokenExists($user, $token));
        $this->postJson($action)->assertUnprocessable()->assertJsonValidationErrors('access');
    }

    public function test_signed_identity_cannot_be_replaced_by_post_body_fields(): void
    {
        $owner = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();
        $token = Password::broker('users')->createToken($owner);
        $otherToken = Password::broker('users')->createToken($other);
        $url = $this->accessLink($owner, $token, true);

        $this->post($url, ['email' => $other->email, 'token' => $otherToken, 'user_id' => $other->id])
            ->assertRedirect(route('customer.account'));

        $this->assertAuthenticatedAs($owner, 'web');
        $this->assertNull($other->fresh()->email_verified_at);
        $this->assertTrue(Password::broker('users')->tokenExists($other, $otherToken));
    }

    public function test_invalid_or_expired_signatures_cannot_verify_an_account(): void
    {
        $user = User::factory()->unverified()->create();
        $token = Password::broker('users')->createToken($user);
        $valid = $this->accessLink($user, $token);
        $tampered = str_replace('token='.$token, 'token='.str_repeat('a', 64), $valid);
        $expired = URL::temporarySignedRoute('customer.access.consume', now()->subMinute(), [
            'email' => $user->email, 'token' => $token,
        ]);

        $this->get($tampered)->assertForbidden();
        $this->post($expired)->assertForbidden();
        $this->assertGuest('web');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertTrue(Password::broker('users')->tokenExists($user, $token));
    }

    public function test_a_bad_or_expired_broker_token_cannot_sign_in_even_with_valid_signature(): void
    {
        $user = User::factory()->unverified()->create();
        $token = Password::broker('users')->createToken($user);
        $this->postJson($this->accessLink($user, str_repeat('f', 64), true))
            ->assertUnprocessable()->assertJsonValidationErrors('access');
        DB::table('password_reset_tokens')->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(65)]);

        $this->postJson($this->accessLink($user, $token, true))
            ->assertUnprocessable()->assertJsonValidationErrors('access');

        $this->assertGuest('web');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_replayed_html_link_returns_to_login_instead_of_a_post_only_action(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('users')->createToken($user);
        $action = $this->accessLink($user, $token, true);
        $this->post($action)->assertRedirect(route('customer.account'));
        $this->post(route('customer.logout'))->assertRedirect(route('customer.login'));

        $this->from($action)->post($action)->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors('access');
        $this->assertGuest('web');
    }

    public function test_ambiguous_email_records_do_not_send_an_account_link(): void
    {
        User::factory()->create(['email' => 'duplicate@example.test']);
        User::factory()->create(['email' => ' duplicate@example.test ']);

        $this->post(route('customer.access.request'), ['email' => 'duplicate@example.test'])
            ->assertRedirect(route('customer.login'))->assertSessionHas('status');

        Mail::assertNothingSent();
        $this->assertGuest('web');
    }

    public function test_only_an_account_path_is_used_as_the_post_login_destination(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('users')->createToken($user);
        $this->withSession(['customer_intended_path' => 'https://outside.example.test/account'])
            ->post($this->accessLink($user, $token, true))->assertRedirect(route('customer.account'));
        Auth::guard('web')->logout();
        $token = Password::broker('users')->createToken($user);

        $this->withSession(['customer_intended_path' => '/account/support'])
            ->post($this->accessLink($user, $token, true))->assertRedirect('/account/support');
    }

    public function test_account_requires_both_current_email_verification_and_verified_session(): void
    {
        $this->get(route('customer.account'))->assertRedirect(route('customer.login'));
        $this->getJson(route('customer.account'))->assertUnauthorized();
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->get(route('customer.account'))->assertRedirect(route('customer.login'));
        $this->customer($user)->get(route('customer.account'))->assertOk();
        $user->forceFill(['email' => 'changed@example.test'])->save();
        $this->get(route('customer.account'))->assertRedirect(route('customer.login'));
        $user->forceFill(['email_verified_at' => null])->save();
        $this->customer($user)->get(route('customer.account'))->assertRedirect(route('customer.login'));
    }

    public function test_order_and_invoice_records_are_scoped_to_the_signed_in_customer(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownOrder = $this->order($owner, ['package' => 'My private service']);
        $otherOrder = $this->order($other, ['package' => 'Other private service']);
        $reference = 'own_invoice_reference_12345';
        $this->invoiceLink($ownOrder, $reference);
        $this->invoiceLink($otherOrder, 'other_invoice_reference_99999');

        $this->customer($owner)->get(route('customer.account'))->assertOk()
            ->assertDontSee('My private service')->assertDontSee('Other private service');
        $this->getJson(route('api.browser.customer.orders.index'))->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownOrder->id)->assertJsonPath('data.0.package', 'My private service')
            ->assertDontSee('Other private service');
        $this->getJson(route('api.browser.customer.orders.show', $ownOrder))->assertOk()
            ->assertJsonPath('data.invoice_url', 'http://127.0.0.1:3000/public/safepay/invoice/'.$reference)
            ->assertDontSee('other_invoice_reference_99999');
        $this->getJson(route('api.browser.customer.orders.show', $otherOrder))->assertNotFound();
        $this->postJson(route('api.browser.customer.orders.renew', $otherOrder))->assertNotFound();
        $this->get(route('customer.orders.show', $otherOrder))->assertNotFound();
        $this->get(route('customer.orders.renew', $otherOrder))->assertNotFound();
    }

    public function test_unpaid_test_ambiguous_and_mismatched_invoice_links_are_not_exposed(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->customer($owner);
        foreach (['pending', 'test_paid'] as $status) {
            $order = $this->order($owner, ['payment_status' => $status]);
            $this->invoiceLink($order, 'not_paid_reference_12345');
            $this->getJson(route('api.browser.customer.orders.show', $order))->assertOk()
                ->assertJsonStructure(['data' => ['invoice_url']])->assertJsonPath('data.invoice_url', null);
        }
        $order = $this->order($owner);
        $this->invoiceLink($order, 'not_my_reference_12345', ['user_id' => $other->id]);
        $this->getJson(route('api.browser.customer.orders.show', $order))->assertOk()
            ->assertJsonStructure(['data' => ['invoice_url']])->assertJsonPath('data.invoice_url', null);
        $this->invoiceLink($order, '../invalid-reference');
        $this->getJson(route('api.browser.customer.orders.show', $order))->assertOk()
            ->assertJsonStructure(['data' => ['invoice_url']])->assertJsonPath('data.invoice_url', null);
        $this->invoiceLink($order, 'first_valid_reference_12345');
        $this->invoiceLink($order, 'second_valid_reference_12345');
        $this->getJson(route('api.browser.customer.orders.show', $order))->assertOk()
            ->assertJsonStructure(['data' => ['invoice_url']])->assertJsonPath('data.invoice_url', null);
    }

    public function test_activation_and_payment_states_are_distinct_and_test_payments_do_not_count(): void
    {
        $owner = User::factory()->create();
        $active = $this->order($owner, ['status' => 'active']);
        $pending = $this->order($owner, ['status' => 'pending']);
        $test = $this->order($owner, ['status' => 'active', 'payment_status' => 'test_paid']);
        $unpaid = $this->order($owner, ['status' => 'active', 'payment_status' => 'pending']);
        $expired = $this->order($owner, ['status' => 'active', 'expiry_date' => now()->subDay()]);
        $expiredPending = $this->order($owner, ['status' => 'pending', 'expiry_date' => now()->subDay()]);

        $this->customer($owner)->getJson(route('api.browser.customer.overview'))->assertOk()
            ->assertJsonPath('data.active_count', 1)->assertJsonPath('data.pending_count', 1);
        $response = $this->getJson(route('api.browser.customer.orders.index'))->assertOk()->assertJsonCount(6, 'data');
        $states = collect($response->json('data'))->mapWithKeys(fn ($order) => [$order['id'] => $order['state']])->all();
        $this->assertSame('Service active', $states[$active->id]['label']);
        $this->assertSame('Activation pending', $states[$pending->id]['label']);
        $this->assertSame('Test payment only', $states[$test->id]['label']);
        $this->assertSame(0, $states[$test->id]['step']);
        $this->assertSame('Payment not confirmed', $states[$unpaid->id]['label']);
        $this->assertSame('Service expired', $states[$expired->id]['label']);
        $this->assertSame('Service expired', $states[$expiredPending->id]['label']);
    }

    public function test_local_customer_account_does_not_open_production_checkout_or_invoice(): void
    {
        $owner = User::factory()->create();
        $order = $this->order($owner, ['package_id' => $this->package()->id]);
        $this->invoiceLink($order, 'production_invoice_reference_12345');
        $this->app->instance('env', 'local');
        config(['services.opplexify.url' => 'https://opplexify.com']);

        $this->customer($owner)->getJson(route('api.browser.customer.orders.show', $order))->assertOk()
            ->assertJsonStructure(['data' => ['invoice_url']])->assertJsonPath('data.invoice_url', null);
        $this->get(route('customer.orders.renew', $order))
            ->assertRedirect(route('customer.orders.show', $order))->assertSessionHasErrors('renewal');
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_reviewing_a_local_renewal_keeps_existing_order_dates_and_payment_unchanged(): void
    {
        $owner = User::factory()->create();
        $package = $this->package();
        $order = $this->order($owner, ['package_id' => $package->id, 'status' => 'active']);
        $original = $order->fresh()->getRawOriginal();

        $response = $this->customer($owner)->get(route('customer.orders.renew', $order))->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith(route('packages.purchase', ['package' => $package->id]), $location);
        $this->assertStringContainsString('signature=', $location);
        $this->assertStringNotContainsString('opplexify.com', $location);
        $this->assertSame($original, $order->fresh()->getRawOriginal());
        $this->assertDatabaseCount('orders', 1);
        Http::assertNothingSent();
    }

    public function test_sensitive_account_responses_are_private_non_cacheable_and_noindex(): void
    {
        $owner = User::factory()->create();
        $token = Password::broker('users')->createToken($owner);
        foreach ([route('customer.login'), $this->accessLink($owner, $token), route('customer.guides')] as $url) {
            $this->assertPrivate($this->get($url)->assertOk());
        }
        $this->assertPrivate($this->customer($owner)->get(route('customer.account'))->assertOk());
    }

    public function test_signed_unsubscribe_cannot_change_another_user_supplied_in_the_body(): void
    {
        $owner = User::factory()->create(['renewal_email_enabled' => true]);
        $other = User::factory()->create(['renewal_email_enabled' => true]);
        $url = URL::temporarySignedRoute('customer.renewal.unsubscribe', now()->addDay(), ['user' => $owner->id]);

        $this->get($url)->assertOk();
        $this->assertTrue($owner->fresh()->renewal_email_enabled);
        $this->post($url, ['user' => $other->id])->assertOk();
        $this->assertFalse($owner->fresh()->renewal_email_enabled);
        $this->assertTrue($other->fresh()->renewal_email_enabled);
    }

    public function test_logout_clears_customer_access_and_the_verified_email_marker(): void
    {
        $owner = User::factory()->create();
        $this->customer($owner)->post(route('customer.logout'))->assertRedirect(route('customer.login'))
            ->assertSessionMissing('customer_verified_email');
        $this->assertGuest('web');
        $this->get(route('customer.account'))->assertRedirect(route('customer.login'));
    }

    private function customer(User $user): static
    {
        return $this->actingAs($user, 'web')->withSession([
            'customer_verified_email' => hash('sha256', User::normalizeEmail($user->email)),
        ]);
    }

    private function accessLink(User $user, string $token, bool $consume = false): string
    {
        return URL::temporarySignedRoute($consume ? 'customer.access.consume' : 'customer.access', now()->addMinutes(15), [
            'email' => User::normalizeEmail($user->email), 'token' => $token,
        ]);
    }

    private function order(User $user, array $attributes = []): Order
    {
        return Order::create(array_replace([
            'user_id' => $user->id, 'package' => 'Example service', 'price' => 12,
            'currency' => 'USD', 'status' => 'pending', 'payment_status' => 'paid',
            'payment_provider' => 'safepay', 'paid_amount' => 12, 'paid_currency' => 'USD',
            'paid_at' => now(), 'buying_date' => now()->startOfDay(),
            'expiry_date' => now()->addMonth()->startOfDay(),
        ], $attributes));
    }

    private function package(): Package
    {
        return Package::create([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Example renewal service',
            'price_amount' => 12, 'duration_months' => 1, 'active' => true, 'is_available' => true,
        ]);
    }

    private function invoiceLink(Order $order, string $reference, array $attributes = []): CustomerExternalLink
    {
        return CustomerExternalLink::create(array_replace([
            'source' => 'opplexify', 'entity_type' => 'streaming_order', 'external_id' => (string) Str::uuid(),
            'user_id' => $order->user_id, 'order_id' => $order->id,
            'state' => ['invoice' => ['data' => ['reference' => $reference]]],
        ], $attributes));
    }

    private function assertPrivate($response): void
    {
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }
}
