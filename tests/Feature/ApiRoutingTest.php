<?php

namespace Tests\Feature;

use App\Http\Middleware\AuditAdminAction;
use App\Http\Middleware\EnsureAdminPasswordChanged;
use App\Http\Middleware\EnsureAdminSessionIsCurrent;
use App\Http\Middleware\EnsureAdminTwoFactorAuthenticated;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\Order;
use App\Models\Package;
use App\Models\TrialClick;
use App\Models\User;
use App\Services\Clients\TawkWebhookService;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Tests\TestCase;

class ApiRoutingTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'isolated-api-routing-shared-secret';

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
        config()->set('services.opplexify.shared_secret', self::SECRET);
        config()->set('services.app.default_currency', 'USD');
    }

    public function test_landing_experience_routes_are_canonical_and_keep_browser_guards(): void
    {
        $stateless = Route::getRoutes()->match(Request::create('/api/v1/landing/resume-links', 'POST'));
        $this->assertSame('api.landing.resume-links', $stateless->getName());
        $this->assertContains('api', $stateless->gatherMiddleware());
        $this->assertNotContains('web', $stateless->gatherMiddleware());
        foreach ([['POST', '/api/browser/checkout/resume/private-token'], ['GET', '/api/browser/account/orders/17/review'],
            ['POST', '/api/browser/account/orders/17/review'], ['DELETE', '/api/browser/account/orders/17/review'],
            ['GET', '/api/browser/admin/landing-experience'], ['POST', '/api/browser/admin/landing-experience/incidents'],
            ['PUT', '/api/browser/admin/landing-experience/incidents/17'], ['POST', '/api/browser/admin/landing-experience/media'],
            ['PUT', '/api/browser/admin/landing-experience/media/17'], ['PATCH', '/api/browser/admin/landing-experience/reviews/17']] as [$method, $path]) {
            $route = Route::getRoutes()->match(Request::create($path, $method));
            $this->assertStringStartsWith('api.browser.', $route->getName());
            $this->assertContains('web', $route->gatherMiddleware());
            $this->assertNotContains(VerifyCsrfToken::class, $route->excludedMiddleware());
            if (str_contains($path, '/admin/')) {
                foreach (['auth:admin', 'admin.access', EnsureAdminSessionIsCurrent::class, EnsureAdminPasswordChanged::class, EnsureAdminTwoFactorAuthenticated::class, AuditAdminAction::class] as $guard) {
                    $this->assertContains($guard, $route->gatherMiddleware());
                }
            }
        }
        $collection = json_decode(file_get_contents(base_path('postman/OpplexIPTV.postman_collection.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Landing experience - local guarded workflows', $collection['item'][0]['name']);
        $this->assertCount(13, $collection['item'][0]['item']);
    }

    public function test_existing_json_routes_keep_their_paths_names_and_actions(): void
    {
        $routes = [
            ['GET', '/movies/movie/17/trailer', 'movies.trailer', 'HomeController@movieTrailer'],
            ['GET', '/trending', null, 'HomeController@getTrending'],
            ['GET', '/packages/providers/17/plans', 'packages.provider-plans', 'HomeController@providerPlans'],
            ['GET', '/integrations/opplexify/catalog', 'integrations.opplexify.catalog', 'HomeController@opplexifyCatalog'],
            ['GET', '/whatsapp-leads/token', 'whatsapp.leads.token', 'TrackingController@whatsappLeadToken'],
            ['POST', '/whatsapp-leads', 'whatsapp.leads.store', 'TrackingController@storeWhatsAppLead'],
            ['POST', '/tawk-leads', 'tawk.leads.store', 'TrackingController@storeTawkLead'],
            ['POST', '/track/whatsapp-trial', 'track.whatsapp.trial', 'TrackingController@whatsappTrial'],
            ['POST', '/tracking/consent', 'tracking.consent', 'TrackingConsentController@update'],
            ['POST', '/checkout/draft', 'checkout.draft', 'CheckoutDraftController@store'],
            ['POST', '/integrations/opplexify/orders/prepare', 'integrations.opplexify.orders.prepare', 'OpplexifyOrderController@prepare'],
            ['POST', '/integrations/opplexify/orders/complete', 'integrations.opplexify.orders.complete', 'OpplexifyOrderController@complete'],
            ['POST', '/integrations/opplexify/customer-events', 'integrations.opplexify.customer-events', 'OpplexifyOrderController@customerEvent'],
            ['GET', '/admin/notifications', 'admin.notifications.index', 'AdminNotificationController@index'],
            ['POST', '/admin/notifications/example/read', 'admin.notifications.read', 'AdminNotificationController@markAsRead'],
            ['POST', '/admin/notifications/read-all', 'admin.notifications.readAll', 'AdminNotificationController@markAllAsRead'],
            ['POST', '/admin/orders/17/mark-messaged', 'admin.orders.markOneMessaged', 'OrderController@markOneMessaged'],
            ['PATCH', '/admin/trial-clicks/17/status', 'admin.trial_clicks.status', 'Admin\\TrialClickController@updateStatus'],
        ];

        foreach ($routes as [$method, $path, $name, $action]) {
            $route = Route::getRoutes()->match(Request::create($path, $method));
            $this->assertSame($name, $route->getName(), $path);
            $this->assertSame('App\\Http\\Controllers\\'.$action, $route->getActionName(), $path);
            $this->assertContains('web', $route->gatherMiddleware(), $path);
            $this->assertNotContains('api', $route->gatherMiddleware(), $path);
        }

        foreach ([
            ['GET', '/api/user', null, 'auth:sanctum'],
            ['POST', '/api/payments/webhook', 'payments.webhook', 'throttle:60,1'],
            ['POST', '/api/integrations/tawk/webhook', 'integrations.tawk.webhook', 'throttle:60,1'],
        ] as [$method, $path, $name, $middleware]) {
            $route = Route::getRoutes()->match(Request::create($path, $method));
            $this->assertSame($name, $route->getName(), $path);
            $this->assertContains('api', $route->gatherMiddleware(), $path);
            $this->assertContains($middleware, $route->gatherMiddleware(), $path);
        }
    }

    public function test_canonical_aliases_have_distinct_names_and_preserve_controller_bindings(): void
    {
        $routes = [
            ['GET', '/api/movies/movie/17/trailer', 'api.movies.trailer', 'HomeController@movieTrailer'],
            ['GET', '/api/trending', 'api.trending', 'HomeController@getTrending'],
            ['GET', '/api/packages/providers/17/plans', 'api.packages.provider-plans', 'HomeController@providerPlans'],
            ['GET', '/api/integrations/opplexify/catalog', 'api.integrations.opplexify.catalog', 'HomeController@opplexifyCatalog'],
            ['POST', '/api/integrations/opplexify/orders/prepare', 'api.integrations.opplexify.orders.prepare', 'OpplexifyOrderController@prepare'],
            ['POST', '/api/integrations/opplexify/orders/complete', 'api.integrations.opplexify.orders.complete', 'OpplexifyOrderController@complete'],
            ['POST', '/api/integrations/opplexify/customer-events', 'api.integrations.opplexify.customer-events', 'OpplexifyOrderController@customerEvent'],
            ['GET', '/api/browser/whatsapp-leads/token', 'api.browser.whatsapp.leads.token', 'TrackingController@whatsappLeadToken'],
            ['POST', '/api/browser/whatsapp-leads', 'api.browser.whatsapp.leads.store', 'TrackingController@storeWhatsAppLead'],
            ['POST', '/api/browser/tawk-leads', 'api.browser.tawk.leads.store', 'TrackingController@storeTawkLead'],
            ['POST', '/api/browser/track/whatsapp-trial', 'api.browser.track.whatsapp.trial', 'TrackingController@whatsappTrial'],
            ['POST', '/api/browser/tracking/consent', 'api.browser.tracking.consent', 'TrackingConsentController@update'],
            ['POST', '/api/browser/checkout/draft', 'api.browser.checkout.draft', 'CheckoutDraftController@store'],
            ['GET', '/api/browser/admin/notifications', 'api.browser.admin.notifications.index', 'AdminNotificationController@index'],
            ['POST', '/api/browser/admin/notifications/example/read', 'api.browser.admin.notifications.read', 'AdminNotificationController@markAsRead'],
            ['POST', '/api/browser/admin/notifications/read-all', 'api.browser.admin.notifications.readAll', 'AdminNotificationController@markAllAsRead'],
            ['POST', '/api/browser/admin/orders/17/mark-messaged', 'api.browser.admin.orders.markOneMessaged', 'OrderController@markOneMessaged'],
            ['PATCH', '/api/browser/admin/trial-clicks/17/status', 'api.browser.admin.trial_clicks.status', 'Admin\\TrialClickController@updateStatus'],
        ];

        foreach ($routes as [$method, $path, $name, $action]) {
            $route = Route::getRoutes()->match(Request::create($path, $method));
            $this->assertSame($name, $route->getName(), $path);
            $this->assertSame('App\\Http\\Controllers\\'.$action, $route->getActionName(), $path);
        }

        foreach (['/admin/notifications', '/api/browser/admin/notifications'] as $path) {
            $middleware = Route::getRoutes()->match(Request::create($path))->gatherMiddleware();
            foreach ([
                'web', 'auth:admin', 'admin.access', EnsureAdminSessionIsCurrent::class,
                EnsureAdminPasswordChanged::class, EnsureAdminTwoFactorAuthenticated::class,
                AuditAdminAction::class,
            ] as $guard) {
                $this->assertContains($guard, $middleware, $path);
            }
        }

        foreach (['/tawk-leads', '/api/browser/tawk-leads'] as $path) {
            $route = Route::getRoutes()->match(Request::create($path, 'POST'));
            $this->assertSame(10, $route->locksFor());
            $this->assertSame(10, $route->waitsFor());
        }
    }

    public function test_canonical_errors_are_json_without_an_accept_header(): void
    {
        $this->get('/api/browser/admin/notifications')->assertUnauthorized()->assertJsonStructure(['message']);
        $this->get('/api/packages/providers/999999/plans')->assertNotFound()->assertJsonStructure(['message']);
        $this->get('/api/packages/providers/not-a-number/plans')->assertNotFound()->assertJsonStructure(['message']);
        $this->get('/api/movies/person/17/trailer')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_all_active_admin_actions_have_api_contracts_with_identical_guards(): void
    {
        $routes = Route::getRoutes();
        $checked = 0;
        foreach ($routes as $legacy) {
            if (!str_starts_with((string) $legacy->getName(), 'admin.') || !str_contains($legacy->getActionName(), '@')) continue;
            [$controller, $method] = explode('@', $legacy->getActionName(), 2);
            if (!method_exists($controller, $method)) continue;
            $api = $routes->getByName('api.browser.'.$legacy->getName());
            $this->assertNotNull($api, $legacy->getName());
            $this->assertSame('api/browser/'.$legacy->uri(), $api->uri());
            $this->assertSame($legacy->methods(), $api->methods());
            $this->assertSame($legacy->getActionName(), $api->getActionName());
            $this->assertSame($legacy->wheres, $api->wheres);
            $this->assertSame($legacy->gatherMiddleware(), array_values(array_diff($api->gatherMiddleware(), [
                \App\Http\Middleware\ApiJsonResponse::class, \App\Http\Responses\AdminApiResponse::class,
            ])), $legacy->getName());
            $checked++;
        }
        $this->assertGreaterThan(140, $checked);
        foreach ($routes as $route) {
            if (!str_starts_with((string) $route->getName(), 'api.')) continue;
            $action = $route->getActionName();
            if (str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action, 2);
                $this->assertTrue(method_exists($controller, $method), $route->getName());
            }
        }
    }

    public function test_media_aliases_return_the_existing_payload_without_external_requests(): void
    {
        $this->mock(TmdbService::class, function ($mock): void {
            $mock->shouldReceive('trailerUrl')->with(17, 'movie')->twice()
                ->andReturn('https://www.youtube.com/watch?v=fixture');
            $mock->shouldReceive('trending')->with('all', 'day', 2)->twice()
                ->andReturn(['results' => [], 'page' => 2, 'total_pages' => 5]);
        });

        foreach (['', '/api'] as $prefix) {
            $this->get($prefix.'/movies/movie/17/trailer')->assertOk()
                ->assertExactJson(['url' => 'https://www.youtube.com/watch?v=fixture']);
            $this->get($prefix.'/trending?page=2')->assertOk()
                ->assertExactJson(['results' => [], 'page' => 2, 'total_pages' => 5]);
        }

        Http::assertNothingSent();
    }

    public function test_provider_alias_keeps_the_valid_provider_filter_and_card_payload(): void
    {
        $package = $this->package();
        $other = $this->package(['title' => 'Removed Provider']);

        foreach (['', '/api'] as $prefix) {
            $response = $this->get($prefix.'/packages/providers/'.$package->id.'/plans');
            $response->assertOk()->assertJsonPath('provider_name', 'Opplex')->assertJsonPath('count', 1);
            $this->assertStringContainsString('data-package-id="'.$package->id.'"', $response->json('html'));
            $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
            $this->get($prefix.'/packages/providers/'.$other->id.'/plans')->assertNotFound();
        }
    }

    public function test_public_api_locale_is_validated_from_the_query_and_localizes_the_response(): void
    {
        $this->mock(TmdbService::class, function ($mock): void {
            $mock->shouldReceive('trailerUrl')->with(17, 'movie')->once()->andReturnUsing(function (): string {
                $this->assertSame('es', app()->getLocale());
                $this->assertSame('es', LaravelLocalization::getCurrentLocale());

                return 'https://www.youtube.com/watch?v=localized-fixture';
            });
            $mock->shouldReceive('trending')->with('all', 'day', 1)->once()->andReturnUsing(function (): array {
                $this->assertSame('es', app()->getLocale());
                $this->assertSame('es', LaravelLocalization::getCurrentLocale());

                return ['results' => [], 'page' => 1, 'total_pages' => 1];
            });
        });
        $package = $this->package();
        $package->translations()->create([
            'locale' => 'es', 'title' => 'Opplex - Mensual', 'features' => ['Función localizada de prueba'],
        ]);
        $paths = [
            '/api/movies/movie/17/trailer', '/api/trending', '/api/packages/providers/'.$package->id.'/plans',
        ];

        foreach ($paths as $path) {
            $this->get($path.'?locale=en-US')->assertUnprocessable()->assertJsonValidationErrors('locale');
            $this->get($path.'?locale%5B%5D=es')->assertUnprocessable()->assertJsonValidationErrors('locale');
        }

        $this->get($paths[0].'?locale=es')->assertOk()->assertHeader('Content-Language', 'es');
        $this->call('GET', $paths[1].'?locale=es', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"locale":"en-US"}')->assertOk()->assertHeader('Content-Language', 'es');
        $response = $this->get($paths[2].'?locale=es')->assertOk()->assertHeader('Content-Language', 'es');
        $this->assertStringContainsString('Función localizada de prueba', $response->json('html'));
        Http::assertNothingSent();
    }

    public function test_existing_payment_webhook_does_not_validate_extra_body_locale_as_a_public_query(): void
    {
        $secret = 'isolated-payment-webhook-secret';
        config()->set('services.payment_webhook.secret', $secret);
        config()->set('services.payment_webhook.providers', ['fixture']);
        $customer = User::factory()->create();
        $order = Order::withoutEvents(fn () => Order::create([
            'user_id' => $customer->id, 'package' => 'Opplex', 'type' => 'package',
            'price' => 15, 'currency' => 'USD', 'status' => 'pending',
            'payment_status' => 'paid', 'payment_provider' => 'fixture',
            'provider_transaction_id' => 'already_recorded_fixture',
        ]));

        foreach (['en-US', 'de'] as $locale) {
            $body = json_encode([
                'order_id' => $order->id, 'status' => 'paid', 'amount' => '15.00',
                'currency' => 'USD', 'provider' => 'fixture',
                'transaction_id' => 'already_recorded_fixture', 'locale' => $locale,
            ], JSON_THROW_ON_ERROR);
            $timestamp = (string) now()->timestamp;
            $this->call('POST', '/api/payments/webhook', [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_OPPLEX_TIMESTAMP' => $timestamp,
                'HTTP_X_OPPLEX_SIGNATURE' => hash_hmac('sha256', $timestamp.'.'.$body, $secret),
            ], $body)->assertOk()->assertJsonPath('message', 'Payment already recorded.')
                ->assertJsonPath('order_id', $order->id);
        }

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame('paid', $order->fresh()->payment_status);
        Mail::assertNothingSent();
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_existing_tawk_webhook_preserves_signed_body_with_extra_locale(): void
    {
        $secret = 'isolated-tawk-webhook-secret';
        config()->set('services.tawk.webhook_secret', $secret);
        config()->set('services.tawk.property_id', 'isolated-property');

        foreach (['en-US', 'de'] as $locale) {
            $body = json_encode([
                'event' => 'chat:start', 'time' => now()->toIso8601String(),
                'property' => ['id' => 'isolated-property'], 'chatId' => 'isolated-chat',
                'visitor' => ['name' => 'Fixture visitor', 'email' => 'visitor@example.test'],
                'locale' => $locale,
            ], JSON_THROW_ON_ERROR);
            $eventId = 'isolated-event-'.$locale;
            $this->mock(TawkWebhookService::class, function ($mock) use ($body, $eventId): void {
                $mock->shouldReceive('receive')->once()->withArgs(
                    static fn (array $data, string $receivedEventId, string $hash): bool =>
                        $data['event'] === 'chat:start'
                        && $data['property']['id'] === 'isolated-property'
                        && !array_key_exists('locale', $data)
                        && $receivedEventId === $eventId
                        && $hash === hash('sha256', $body)
                )->andReturn(true);
            });

            $this->call('POST', '/api/integrations/tawk/webhook', [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_TAWK_SIGNATURE' => hash_hmac('sha1', $body, $secret),
                'HTTP_X_HOOK_EVENT_ID' => $eventId,
            ], $body)->assertOk()->assertExactJson(['ok' => true, 'duplicate' => false]);
        }

        $this->assertDatabaseCount('customer_activities', 0);
        Http::assertNothingSent();
    }

    public function test_browser_capture_requires_csrf_and_preserves_tawk_session_deduplication(): void
    {
        $this->enforceCsrf();
        $payload = [
            'event_id' => (string) Str::uuid(), 'form_type' => 'prechat',
            'contact_name' => 'Isolated Tawk Visitor', 'email' => 'visitor@example.test',
            'phone' => '+923001234567', 'message' => 'Local fixture message',
        ];

        $this->post('/api/browser/tawk-leads', $payload)->assertStatus(419)->assertJsonStructure(['message']);
        $this->assertDatabaseCount('trial_clicks', 0);

        $token = $this->get('/api/browser/whatsapp-leads/token')->assertOk()->json('csrf_token');
        $this->assertIsString($token);
        $this->assertNotSame('', $token);
        $this->withSession(['_token' => $token]);
        $payload['_token'] = $token;
        $this->post('/api/browser/tawk-leads', $payload)->assertStatus(202)->assertJsonPath('ok', true);
        $this->post('/api/browser/tawk-leads', $payload)->assertStatus(202)->assertJsonPath('ok', true);
        $this->assertDatabaseCount('trial_clicks', 1);
        $this->assertDatabaseCount('customer_activities', 1);
        $this->assertArrayHasKey($payload['event_id'], session('tawk_lead_receipts'));

        $payload['message'] = 'Different payload with the same event ID';
        $this->post('/api/browser/tawk-leads', $payload)->assertUnprocessable()->assertJsonValidationErrors('event_id');
        $this->assertDatabaseCount('trial_clicks', 1);
    }

    public function test_browser_consent_preserves_session_effects_and_trial_beacon_exception(): void
    {
        $this->enforceCsrf();
        $this->withSession(['_token' => 'test-consent-token', 'fb.fbp' => 'fixture-attribution']);
        $this->post('/api/browser/tracking/consent', ['analytics' => false, 'marketing' => false])
            ->assertStatus(419);
        $this->assertSame('fixture-attribution', session('fb.fbp'));
        $this->post('/api/browser/tracking/consent', [
            '_token' => 'test-consent-token', 'analytics' => false, 'marketing' => false,
        ])->assertOk()->assertJsonPath('saved', true)->assertSessionMissing('fb.fbp');

        $this->post('/api/browser/track/whatsapp-trial')->assertStatus(202)
            ->assertJsonPath('tracked', false);
        $this->assertDatabaseCount('trial_clicks', 0);
    }

    public function test_admin_notification_alias_is_scoped_to_the_authenticated_admin(): void
    {
        $admin = $this->signInAdmin(Admin::ROLE_SUPPORT);
        $other = $this->admin(Admin::ROLE_OWNER);
        $mine = $admin->notifications()->create([
            'id' => (string) Str::uuid(), 'type' => 'fixture', 'data' => ['title' => 'My notification'],
        ]);
        $theirs = $other->notifications()->create([
            'id' => (string) Str::uuid(), 'type' => 'fixture', 'data' => ['title' => 'Other admin notification'],
        ]);

        $this->get('/api/browser/admin/notifications')->assertOk()->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.id', $mine->id)->assertJsonPath('unread_count', 1);
        $this->post('/api/browser/admin/notifications/'.$theirs->id.'/read')->assertNotFound();
        $this->assertNull($theirs->fresh()->read_at);
        $this->post('/api/browser/admin/notifications/'.$mine->id.'/read')->assertOk()->assertJsonPath('ok', true);
        $this->assertNotNull($mine->fresh()->read_at);
        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id, 'route_name' => 'api.browser.admin.notifications.read',
            'response_status' => 200,
        ]);
    }

    public function test_admin_alias_preserves_role_authorization_and_audits_a_permitted_mutation(): void
    {
        $admin = $this->signInAdmin(Admin::ROLE_SUPPORT);
        $trial = TrialClick::create(['event_id' => (string) Str::uuid(), 'status' => 'new']);
        $path = '/api/browser/admin/trial-clicks/'.$trial->id.'/status';
        $this->patch($path, ['status' => 'replied'])->assertOk()->assertJsonPath('status', 'replied');
        $this->assertSame('replied', $trial->fresh()->status);
        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id, 'route_name' => 'api.browser.admin.trial_clicks.status',
            'response_status' => 200,
        ]);

        $admin->forceFill(['role' => Admin::ROLE_CONTENT])->save();
        $this->patch($path, ['status' => 'paid'])->assertForbidden()->assertJsonStructure(['message']);
        $this->assertSame('replied', $trial->fresh()->status);
        $this->get('/api/browser/admin/notifications')->assertForbidden()->assertJsonStructure(['message']);
        $this->assertTrue(AdminAuditLog::query()->where('admin_id', $admin->id)
            ->where('route_name', 'api.browser.admin.trial_clicks.status')->where('response_status', 403)->exists());
    }

    public function test_admin_aliases_keep_password_two_factor_and_session_guards(): void
    {
        $admin = $this->signInAdmin(Admin::ROLE_OWNER);
        $admin->forceFill(['must_change_password' => true])->save();
        $this->get('/api/browser/admin/notifications')->assertForbidden()->assertJsonPath('code', 'password_change_required')->assertJsonPath('redirect_url', route('admin.password.change'));
        $this->get('/admin/notifications')->assertRedirect(route('admin.password.change'));

        $admin->forceFill(['must_change_password' => false, 'two_factor_confirmed_at' => null])->save();
        $this->get('/api/browser/admin/notifications')->assertForbidden()->assertJsonPath('code', 'two_factor_setup_required')->assertJsonPath('redirect_url', route('admin.two-factor.settings'));
        $this->get('/admin/notifications')->assertRedirect(route('admin.two-factor.settings'));

        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->withSession(['admin_two_factor_verified_id' => null]);
        $this->get('/api/browser/admin/notifications')->assertForbidden()->assertJsonPath('code', 'two_factor_required')->assertJsonPath('redirect_url', route('admin.two-factor.challenge'));
        $this->get('/admin/notifications')->assertRedirect(route('admin.two-factor.challenge'));

        $this->withSession(['admin_two_factor_verified_id' => $admin->id, 'admin_auth_version' => 0]);
        $this->get('/api/browser/admin/notifications')->assertUnauthorized()->assertJsonPath('code', 'session_expired')->assertJsonPath('redirect_url', route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_catalog_hmac_uses_the_exact_legacy_or_canonical_path(): void
    {
        $package = $this->package();
        $legacy = '/integrations/opplexify/catalog';
        $canonical = '/api'.$legacy;

        foreach ([$legacy, $canonical] as $path) {
            $this->signedRequest('GET', $path)->assertOk()->assertJsonFragment(['id' => $package->id]);
            $other = $path === $legacy ? $canonical : $legacy;
            $this->signedRequest('GET', $path, '{}', $other)->assertUnauthorized();
            $this->signedRequest('GET', $path, '{}', null, (string) now()->subMinutes(6)->timestamp)
                ->assertUnauthorized();
        }
    }

    public function test_signed_post_endpoints_accept_both_paths_but_reject_path_and_body_replays(): void
    {
        foreach (['orders/prepare', 'orders/complete', 'customer-events'] as $endpoint) {
            $legacy = '/integrations/opplexify/'.$endpoint;
            $canonical = '/api'.$legacy;
            foreach ([$legacy, $canonical] as $path) {
                $this->signedRequest('POST', $path)->assertUnprocessable();
                $other = $path === $legacy ? $canonical : $legacy;
                $this->signedRequest('POST', $path, '{}', $other)->assertUnauthorized();
                $this->signedRequest('POST', $path, '{}', null, null, '{"tampered":true}')
                    ->assertUnauthorized();
            }
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('checkout_drafts', 0);
        $this->assertDatabaseCount('customer_activities', 0);
    }

    public function test_prepare_is_idempotent_across_legacy_and_canonical_urls(): void
    {
        $package = $this->package();
        $baseMinor = (int) round((float) $package->price_amount * 100);
        $amountMinor = $baseMinor + array_sum(array_map(
            static fn (int $sample): int => (int) round($baseMinor * $sample / 1199),
            [71, 5, 12, 24]
        ));
        $body = json_encode([
            'reference' => 'isolated_shared_checkout_123', 'package_id' => $package->id,
            'amount_minor' => $amountMinor, 'currency' => 'USD', 'environment' => 'sandbox',
        ], JSON_THROW_ON_ERROR);

        foreach (['/integrations/opplexify/orders/prepare', '/api/integrations/opplexify/orders/prepare'] as $path) {
            $this->signedRequest('POST', $path, $body)->assertOk()->assertJsonPath('prepared', true);
        }

        $this->assertDatabaseCount('checkout_drafts', 1);
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    private function signedRequest(
        string $method,
        string $path,
        string $body = '{}',
        ?string $signedPath = null,
        ?string $timestamp = null,
        ?string $sentBody = null
    ): TestResponse {
        $timestamp ??= (string) now()->timestamp;
        $message = $method."\n".($signedPath ?? $path)."\n".$timestamp;
        if ($method === 'POST') {
            $message .= "\n".hash('sha256', $body);
        }

        return $this->call($method, $path, [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_OPPLEXIFY_TIMESTAMP' => $timestamp,
            'HTTP_X_OPPLEXIFY_SIGNATURE' => hash_hmac('sha256', $message, self::SECRET),
        ], $sentBody ?? $body);
    }

    private function enforceCsrf(): void
    {
        $this->app->instance(VerifyCsrfToken::class, new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
    }

    private function package(array $attributes = []): Package
    {
        return Package::create(array_merge([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Opplex',
            'display_price' => '$9.99 / 1 month', 'price_amount' => 9.99,
            'duration_months' => 1, 'features' => ['Fixture feature'],
            'is_available' => true, 'active' => true,
        ], $attributes));
    }

    private function admin(string $role): Admin
    {
        $admin = Admin::create([
            'name' => 'Isolated API Admin', 'email' => Str::uuid().'@example.test',
            'password' => bcrypt('isolated-test-only'), 'role' => $role,
            'must_change_password' => false, 'auth_version' => 1,
        ]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();

        return $admin;
    }

    private function signInAdmin(string $role): Admin
    {
        $admin = $this->admin($role);
        $this->actingAs($admin, 'admin')->withSession([
            'admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id,
        ]);

        return $admin;
    }
}
