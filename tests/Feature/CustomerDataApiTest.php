<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomerConversation;
use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\TrialClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerDataApiTest extends TestCase
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
        Queue::fake();
        Http::preventStrayRequests();
    }

    public function test_customer_serialization_has_an_explicit_allowlist(): void
    {
        $customer = User::factory()->create([
            'name' => 'API customer', 'email' => 'api-customer@example.test', 'phone' => '+923001234567',
            'notes' => 'private internal notes', 'marketing_consent_ip_hash' => str_repeat('a', 64),
        ]);
        $this->withToken($this->token(['customers:read']));

        $data = $this->getJson('/api/v1/customers/'.$customer->id)->assertOk()->json('data');

        $this->assertSame([
            'id', 'name', 'email', 'phone', 'country', 'email_verified_at', 'renewal_email_enabled',
            'renewal_email_updated_at', 'created_at', 'updated_at',
        ], array_keys($data));
        $this->assertSame($customer->email, $data['email']);
        $this->assertSame($customer->phone, $data['phone']);
        $this->assertNull($data['renewal_email_enabled']);
        $this->getJson('/api/v1/customers')->assertOk()->assertJsonPath('data.0', $data);
    }

    public function test_orders_never_serialize_credentials_payment_links_or_internal_fields(): void
    {
        $order = $this->orderRecord(User::factory()->create(), [
            'iptv_username' => 'private-account', 'note' => 'private payment payload',
            'checkout_key' => (string) Str::uuid(), 'provider_transaction_id' => 'private-tracker',
            'cost_price' => 4, 'sell_price' => 15, 'profit' => 11,
            'buying_date' => '2026-01-31', 'expiry_date' => '2026-04-30',
            'paid_amount' => '15.00', 'paid_currency' => 'USD',
        ]);
        $this->withToken($this->token(['orders:read']));

        $data = $this->getJson('/api/v1/orders/'.$order->id)->assertOk()->json('data');

        $this->assertSame([
            'id', 'customer_id', 'type', 'package_id', 'package', 'status', 'payment_status', 'price',
            'currency', 'paid_amount', 'paid_currency', 'paid_at', 'buying_date', 'expiry_date', 'created_at', 'updated_at',
        ], array_keys($data));
        $this->assertSame('15.00', $data['price']);
        $this->assertSame('15.00', $data['paid_amount']);
        $this->assertSame('2026-01-31', $data['buying_date']);
        $this->assertSame('2026-04-30', $data['expiry_date']);
        $this->getJson('/api/v1/orders')->assertOk()->assertJsonPath('data.0', $data);
    }

    public function test_subscriptions_only_read_paid_iptv_orders_without_recalculating_dates(): void
    {
        $customer = User::factory()->create();
        $paid = $this->orderRecord($customer, ['payment_status' => 'paid', 'buying_date' => '2026-01-31', 'expiry_date' => '2026-04-30']);
        $sandbox = $this->orderRecord($customer, ['payment_status' => 'test_paid']);
        $unpaid = $this->orderRecord($customer, ['payment_status' => 'unpaid']);
        $reseller = $this->orderRecord($customer, ['payment_status' => 'paid', 'type' => 'reseller']);
        $refunded = $this->orderRecord($customer, ['payment_status' => 'refunded']);
        $this->withToken($this->token(['orders:read']));

        $this->getJson('/api/v1/subscriptions')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_id', $paid->id)
            ->assertJsonPath('data.0.buying_date', '2026-01-31')->assertJsonPath('data.0.expiry_date', '2026-04-30');
        $data = $this->getJson('/api/v1/subscriptions/'.$paid->id)->assertOk()->json('data');
        $this->assertSame([
            'id', 'order_id', 'customer_id', 'package_id', 'package', 'status', 'payment_status',
            'buying_date', 'expiry_date', 'created_at', 'updated_at',
        ], array_keys($data));
        foreach ([$sandbox, $unpaid, $reseller, $refunded] as $order) {
            $this->getJson('/api/v1/subscriptions/'.$order->id)->assertNotFound();
        }
        $this->assertSame('2026-04-30', $paid->fresh()->expiry_date->format('Y-m-d'));
    }

    public function test_subscription_filters_preserve_null_dates_and_stored_status(): void
    {
        $customer = User::factory()->create();
        $order = $this->orderRecord($customer, ['payment_status' => 'paid', 'status' => 'pending', 'buying_date' => null, 'expiry_date' => null]);
        $this->orderRecord(User::factory()->create(), ['payment_status' => 'paid', 'status' => 'active']);
        $this->withToken($this->token(['orders:read']));

        $this->getJson('/api/v1/subscriptions?customer_id='.$customer->id.'&status=pending')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $order->id)
            ->assertJsonPath('data.0.buying_date', null)->assertJsonPath('data.0.expiry_date', null)
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_nested_customer_lists_are_bound_to_the_named_customer(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->orderRecord($customer);
        $this->orderRecord($other);
        $trial = $this->trialRecord($customer);
        $this->trialRecord($other);
        $this->trialRecord($customer, ['intent' => 'purchase']);
        $conversation = $this->conversationRecord($customer);
        $this->conversationRecord($other);
        $this->conversationRecord(null);
        $this->withToken($this->token());

        $this->getJson('/api/v1/customers/'.$customer->id.'/orders')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order->id);
        $this->getJson('/api/v1/customers/'.$customer->id.'/trials')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $trial->id);
        $this->getJson('/api/v1/customers/'.$customer->id.'/conversations')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $conversation->id);
        $this->getJson('/api/v1/customers/'.$customer->id.'/orders?customer_id='.$other->id)
            ->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/customers/'.$customer->id.'/conversations?customer_id='.$other->id)
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_trial_serialization_excludes_analytics_and_contact_destination_secrets(): void
    {
        $customer = User::factory()->create();
        $trial = $this->trialRecord($customer, [
            'message' => 'Please help me set up my trial', 'destination' => 'https://private.test/token',
            'ip' => '192.0.2.10', 'fbp' => 'private-fbp', 'consent_ip_hash' => str_repeat('a', 64),
        ]);
        $this->withToken($this->token(['customers:read']));

        $data = $this->getJson('/api/v1/customers/'.$customer->id.'/trials')->assertOk()->json('data.0');

        $this->assertSame([
            'id', 'customer_id', 'contact_name', 'contact_email', 'phone', 'message', 'package_id',
            'package_name', 'status', 'trial_status', 'trial_started_at', 'trial_expires_at',
            'trial_owner_verified_at', 'created_at', 'updated_at',
        ], array_keys($data));
        $this->assertSame($trial->message, $data['message']);
        $this->assertSame('pending', $data['trial_status']);
    }

    public function test_conversations_and_messages_use_explicit_allowlists_and_parent_binding(): void
    {
        $conversation = $this->conversationRecord(User::factory()->create());
        $other = $this->conversationRecord(null, ['channel' => 'tawk']);
        $message = $this->messageRecord($conversation, ['content' => 'Can you help?', 'sender_type' => 'customer']);
        $this->messageRecord($other, ['content' => 'Other conversation']);
        $this->withToken($this->token(['support:read']));

        $data = $this->getJson('/api/v1/support/conversations/'.$conversation->id)->assertOk()->json('data');
        $this->assertSame([
            'id', 'customer_id', 'channel', 'status', 'visitor_name', 'visitor_email', 'visitor_phone',
            'started_at', 'ended_at', 'created_at', 'updated_at',
        ], array_keys($data));
        $this->getJson('/api/v1/support/conversations?channel=portal&status=open')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0', $data);
        $response = $this->getJson('/api/v1/support/conversations/'.$conversation->id.'/messages')->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame([
            'id', 'conversation_id', 'sender_type', 'sender_name', 'content', 'sent_at', 'created_at', 'updated_at',
        ], array_keys($response->json('data.0')));
        $response->assertJsonPath('data.0.id', $message->id)->assertJsonPath('data.0.content', 'Can you help?');
        $this->getJson('/api/v1/support/conversations/'.$conversation->id.'/messages?sender_type=agent')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_list_filters_are_exact_paginated_and_incremental(): void
    {
        $first = User::factory()->create(['email' => 'first@example.test', 'phone' => '+923001234567', 'updated_at' => '2026-01-01 00:00:00']);
        $second = User::factory()->create(['email' => 'second@example.test', 'updated_at' => '2026-02-01 00:00:00']);
        $third = User::factory()->create(['updated_at' => '2026-02-02 00:00:00']);
        $this->withToken($this->token(['customers:read']));

        $this->getJson('/api/v1/customers?per_page=1&page=2')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $second->id)->assertJsonPath('meta', ['current_page' => 2, 'per_page' => 1, 'total' => 3, 'last_page' => 3]);
        $this->getJson('/api/v1/customers?email=FIRST%40EXAMPLE.TEST')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $first->id);
        $this->getJson('/api/v1/customers?phone=%2B923001234567')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $first->id);
        $this->getJson('/api/v1/customers?updated_since=2026-02-01T00%3A00%3A00Z')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $second->id)->assertJsonPath('data.1.id', $third->id);
        $this->getJson('/api/v1/customers?page=10&per_page=1')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_order_filters_do_not_expose_other_customers(): void
    {
        $customer = User::factory()->create();
        $order = $this->orderRecord($customer, ['payment_status' => 'paid', 'status' => 'active']);
        $this->orderRecord($customer, ['payment_status' => 'unpaid']);
        $this->orderRecord(User::factory()->create(), ['payment_status' => 'paid', 'status' => 'active']);
        $this->withToken($this->token(['orders:read']));

        $this->getJson('/api/v1/orders?customer_id='.$customer->id.'&payment_status=paid&status=active&type=package')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $order->id);
    }

    public function test_invalid_filter_values_receive_json_validation_errors(): void
    {
        $this->withToken($this->token());
        foreach (['per_page=0', 'per_page=101', 'per_page=all', 'page=0', 'updated_since=not-a-date', 'email=not-an-email', 'phone=invalid'] as $query) {
            $this->getJson('/api/v1/customers?'.$query)->assertUnprocessable();
        }
        $this->getJson('/api/v1/orders?status=anything')->assertUnprocessable();
        $this->getJson('/api/v1/subscriptions?customer_id=-1')->assertUnprocessable();
        $this->getJson('/api/v1/support/conversations?channel=private')->assertUnprocessable();
    }

    public function test_nonexistent_resources_and_parents_return_json_404(): void
    {
        $this->withToken($this->token());
        foreach ([
            '/customers/999999', '/customers/999999/orders', '/customers/999999/trials', '/customers/999999/conversations',
            '/orders/999999', '/subscriptions/999999', '/support/conversations/999999', '/support/conversations/999999/messages',
        ] as $path) {
            $this->getJson('/api/v1'.$path)->assertNotFound()->assertJsonStructure(['message']);
        }
    }

    public function test_missing_bad_and_session_only_authentication_cannot_read_data(): void
    {
        $this->getJson('/api/v1/customers')->assertUnauthorized();
        $this->withToken('not-a-token')->getJson('/api/v1/customers')->assertUnauthorized();
        $this->withoutToken()->actingAs($this->admin(), 'admin')->getJson('/api/v1/customers')->assertUnauthorized();
        $this->actingAs(User::factory()->create(), 'web')->getJson('/api/v1/customers')->assertUnauthorized();
    }

    public function test_customer_and_non_owner_tokens_cannot_read_customer_data(): void
    {
        $customer = User::factory()->create();
        $token = $customer->createToken('customer token', ['data-api', 'auth-version:1', 'customers:read'], now()->addDay())->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/customers')->assertUnauthorized();
        $admin = $this->admin(['role' => Admin::ROLE_SUPPORT]);
        $this->withToken($this->token(['customers:read'], $admin))->getJson('/api/v1/customers')->assertUnauthorized();
    }

    public function test_admin_data_token_cannot_expand_the_legacy_user_endpoint_to_admin_profiles(): void
    {
        $this->withToken($this->token(['customers:read']))->getJson('/api/user')->assertUnauthorized()
            ->assertJsonMissingPath('email')->assertJsonMissingPath('role')->assertJsonMissingPath('password');
    }

    public function test_legacy_user_endpoint_still_accepts_customer_tokens_without_hidden_credentials(): void
    {
        $customer = User::factory()->create(['phone' => '+923001234567']);
        $token = $customer->createToken('legacy customer client')->plainTextToken;

        $this->withToken($token)->getJson('/api/user')->assertOk()
            ->assertJsonPath('id', $customer->id)->assertJsonPath('email', $customer->email)
            ->assertJsonPath('phone', $customer->phone)
            ->assertJsonMissingPath('password')->assertJsonMissingPath('remember_token')
            ->assertJsonMissingPath('email_normalized')->assertJsonMissingPath('phone_normalized');
    }

    public function test_scopes_are_enforced_for_each_resource_family(): void
    {
        $customer = User::factory()->create();
        $conversation = $this->conversationRecord($customer);
        $this->withToken($this->token(['customers:read']));
        $this->getJson('/api/v1/customers')->assertOk();
        $this->getJson('/api/v1/orders')->assertForbidden();
        $this->getJson('/api/v1/subscriptions')->assertForbidden();
        $this->getJson('/api/v1/support/conversations')->assertForbidden();
        $this->getJson('/api/v1/customers/'.$customer->id.'/orders')->assertForbidden();
        $this->getJson('/api/v1/customers/'.$customer->id.'/conversations')->assertForbidden();
        $this->withToken($this->token(['orders:read']));
        $this->getJson('/api/v1/orders')->assertOk();
        $this->getJson('/api/v1/customers')->assertForbidden();
        $this->getJson('/api/v1/customers/'.$customer->id.'/trials')->assertForbidden();
        $this->getJson('/api/v1/support/conversations/'.$conversation->id.'/messages')->assertForbidden();
    }

    public function test_expired_revoked_and_nonexpiring_tokens_are_rejected(): void
    {
        $admin = $this->admin();
        $abilities = ['data-api', 'auth-version:'.$admin->auth_version, 'customers:read'];
        $expired = $admin->createToken('expired', $abilities, now()->subSecond());
        $this->withToken($expired->plainTextToken)->getJson('/api/v1/customers')->assertUnauthorized();
        $nonexpiring = $admin->createToken('nonexpiring', $abilities);
        $this->withToken($nonexpiring->plainTextToken)->getJson('/api/v1/customers')->assertUnauthorized();
        $revoked = $admin->createToken('revoked', $abilities, now()->addDay());
        $revoked->accessToken->delete();
        $this->withToken($revoked->plainTextToken)->getJson('/api/v1/customers')->assertUnauthorized();
    }

    public function test_auth_version_password_requirement_and_role_are_rechecked(): void
    {
        $admin = $this->admin();
        $token = $this->token(['customers:read'], $admin);
        $this->withToken($token)->getJson('/api/v1/customers')->assertOk();
        $admin->forceFill(['must_change_password' => true])->save();
        $this->getJson('/api/v1/customers')->assertUnauthorized();
        $admin->forceFill(['must_change_password' => false, 'role' => Admin::ROLE_SALES])->save();
        $this->getJson('/api/v1/customers')->assertUnauthorized();
        $admin->forceFill(['role' => Admin::ROLE_OWNER, 'auth_version' => $admin->auth_version + 1])->save();
        $this->getJson('/api/v1/customers')->assertUnauthorized();
    }

    public function test_unmarked_wrong_version_and_overlong_tokens_are_rejected(): void
    {
        $admin = $this->admin();
        foreach ([['customers:read'], ['data-api', 'auth-version:999', 'customers:read']] as $abilities) {
            $token = $admin->createToken('invalid data token', $abilities, now()->addDay());
            $this->withToken($token->plainTextToken)->getJson('/api/v1/customers')->assertUnauthorized();
        }
        $token = $admin->createToken('overlong token', ['data-api', 'auth-version:'.$admin->auth_version, 'customers:read'], now()->addDays(31));
        $this->withToken($token->plainTextToken)->getJson('/api/v1/customers')->assertUnauthorized();
    }

    public function test_get_requests_do_not_change_application_records_or_send_messages(): void
    {
        $customer = User::factory()->create();
        $order = $this->orderRecord($customer, ['payment_status' => 'paid']);
        $this->trialRecord($customer);
        $conversation = $this->conversationRecord($customer);
        $this->messageRecord($conversation);
        $this->withToken($this->token());
        $tables = ['users', 'orders', 'trial_clicks', 'customer_conversations', 'customer_messages', 'customer_activities', 'customer_external_links', 'marketing_deliveries', 'personal_access_tokens'];
        $before = [];
        foreach ($tables as $table) $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();

        foreach ([
            '/customers', '/customers/'.$customer->id, '/customers/'.$customer->id.'/orders', '/customers/'.$customer->id.'/trials',
            '/customers/'.$customer->id.'/conversations', '/orders', '/orders/'.$order->id, '/subscriptions', '/subscriptions/'.$order->id,
            '/support/conversations', '/support/conversations/'.$conversation->id, '/support/conversations/'.$conversation->id.'/messages',
        ] as $path) {
            $this->getJson('/api/v1'.$path)->assertOk();
        }

        foreach ($tables as $table) $this->assertSame($before[$table], DB::table($table)->orderBy('id')->get()->toJson(), $table.' changed during read');
        Mail::assertNothingSent();
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
        Http::assertNothingSent();
    }

    public function test_data_api_has_no_write_endpoints(): void
    {
        $this->withToken($this->token());
        $this->postJson('/api/v1/customers', ['email' => 'unauthorized-create@example.test'])->assertStatus(405);
        $this->deleteJson('/api/v1/orders/1')->assertStatus(405);
        $this->patchJson('/api/v1/support/conversations/1', ['status' => 'closed'])->assertStatus(405);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_private_headers_are_present_for_success_and_authentication_errors(): void
    {
        $unauthorized = $this->getJson('/api/v1/customers')->assertUnauthorized();
        $this->assertStringContainsString('no-store', $unauthorized->headers->get('Cache-Control'));
        $this->assertStringContainsString('noindex', $unauthorized->headers->get('X-Robots-Tag'));
        $this->withToken($this->token(['customers:read']));
        $success = $this->getJson('/api/v1/customers')->assertOk();
        $this->assertStringContainsString('private', $success->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $success->headers->get('Cache-Control'));
        $this->assertStringContainsString('noindex', $success->headers->get('X-Robots-Tag'));
        $forbidden = $this->getJson('/api/v1/orders')->assertForbidden();
        $this->assertStringContainsString('no-store', $forbidden->headers->get('Cache-Control'));
    }

    public function test_console_issue_creates_only_explicit_scopes_with_expiry_and_hashed_token(): void
    {
        $admin = $this->admin();
        $this->artisan('data-api:token', [
            'admin' => $admin->email, '--name' => 'Isolated client', '--abilities' => 'customers:read,orders:read', '--days' => '7',
        ])->assertExitCode(0);

        $token = $admin->tokens()->sole();
        $this->assertSame('Isolated client', $token->name);
        $this->assertSame(['customers:read', 'orders:read', 'data-api', 'auth-version:1'], $token->abilities);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token->token);
        $this->assertTrue($token->expires_at->isFuture());
        $this->assertTrue($token->expires_at->lte($token->created_at->copy()->addDays(7)));
    }

    public function test_console_issue_rejects_invalid_scopes_lifetime_and_non_owner(): void
    {
        $admin = $this->admin();
        $base = ['admin' => (string) $admin->id, '--name' => 'Isolated client', '--abilities' => 'customers:read', '--days' => '7'];
        foreach ([['--abilities' => '*'], ['--abilities' => ''], ['--abilities' => 'orders:write'], ['--days' => '0'], ['--days' => '31'], ['--name' => '']] as $invalid) {
            $this->artisan('data-api:token', array_replace($base, $invalid))->assertExitCode(1);
        }
        $admin->forceFill(['role' => Admin::ROLE_SUPPORT])->save();
        $this->artisan('data-api:token', $base)->assertExitCode(1);
        $admin->forceFill(['role' => Admin::ROLE_OWNER, 'must_change_password' => true])->save();
        $this->artisan('data-api:token', $base)->assertExitCode(1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_console_revocation_is_owner_bound_and_does_not_revoke_unrelated_tokens(): void
    {
        $admin = $this->admin();
        $other = $this->admin();
        $plain = $this->token(['customers:read'], $admin);
        $dataToken = $admin->tokens()->sole();
        $unrelated = $admin->createToken('unrelated token', ['other-purpose'], now()->addDay());
        $this->artisan('data-api:token', ['admin' => (string) $other->id, '--revoke' => (string) $dataToken->id])->assertExitCode(1);
        $this->artisan('data-api:token', ['admin' => (string) $admin->id, '--revoke' => (string) $unrelated->accessToken->id])->assertExitCode(1);
        $this->assertDatabaseCount('personal_access_tokens', 2);
        $admin->forceFill(['role' => Admin::ROLE_SUPPORT])->save();
        $this->artisan('data-api:token', ['admin' => (string) $admin->id, '--revoke' => (string) $dataToken->id])->assertExitCode(0);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $unrelated->accessToken->id]);
        $this->withToken($plain)->getJson('/api/v1/customers')->assertUnauthorized();
    }

    private function admin(array $attributes = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'API owner', 'email' => Str::uuid().'@example.test', 'password' => bcrypt('Isolated-test-password-123!'),
            'role' => Admin::ROLE_OWNER, 'must_change_password' => false, 'auth_version' => 1,
        ], $attributes));
    }

    private function token(array $scopes = ['customers:read', 'orders:read', 'support:read'], ?Admin $admin = null): string
    {
        $admin ??= $this->admin();

        return $admin->createToken('isolated API test', array_merge(['data-api', 'auth-version:'.$admin->auth_version], $scopes), now()->addDay())->plainTextToken;
    }

    private function orderRecord(User $customer, array $attributes = []): Order
    {
        return Order::withoutEvents(fn () => Order::create(array_merge([
            'user_id' => $customer->id, 'package' => 'Opplex', 'type' => 'package', 'price' => 15,
            'currency' => 'USD', 'status' => 'pending', 'payment_status' => 'unpaid',
        ], $attributes)));
    }

    private function trialRecord(User $customer, array $attributes = []): TrialClick
    {
        return TrialClick::create(array_merge([
            'user_id' => $customer->id, 'event_id' => (string) Str::uuid(), 'intent' => 'trial',
            'contact_name' => $customer->name, 'contact_email' => $customer->email, 'status' => 'new',
        ], $attributes));
    }

    private function conversationRecord(?User $customer, array $attributes = []): CustomerConversation
    {
        return CustomerConversation::create(array_merge([
            'user_id' => $customer?->id, 'channel' => 'portal', 'property_id' => 'customer-account',
            'external_id' => (string) Str::uuid(), 'visitor_name' => $customer?->name,
            'visitor_email' => $customer?->email, 'visitor_phone' => $customer?->phone, 'status' => 'open', 'started_at' => now(),
        ], $attributes));
    }

    private function messageRecord(CustomerConversation $conversation, array $attributes = []): CustomerMessage
    {
        return CustomerMessage::create(array_merge([
            'conversation_id' => $conversation->id, 'fingerprint' => hash('sha256', Str::uuid()),
            'sender_type' => 'customer', 'sender_name' => 'Test customer', 'content' => 'Test message', 'sent_at' => now(),
        ], $attributes));
    }
}
