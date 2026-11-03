<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomerActivity;
use App\Models\CustomerExternalLink;
use App\Models\FooterSetting;
use App\Models\LandingPageContent;
use App\Models\Package;
use App\Services\Clients\LandingInteractionService;
use Database\Seeders\CheapAccountsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CheapAccountsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'isolated-cheap-accounts-bridge-secret';

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests(); Mail::fake(); Notification::fake(); Bus::fake(); Cache::flush();
        Package::query()->update(['active' => false]);
        config()->set('services.app.default_currency', 'USD');
        config()->set('services.opplexify.url', 'https://checkout.example.test');
        config()->set('services.opplexify.shared_secret', self::SECRET);
    }

    public function test_catalog_uses_current_opplex_filex_facts_and_existing_central_support(): void
    {
        $opplex = $this->plan('Opplex iptv', 'opplex', 7.25);
        $filex = $this->plan('FILEX IPTV', 'starshare', 9.50);
        $filex->update(['max_devices' => null]);
        $this->plan('TREX IPTV - Yearly', 'opplex', 71, 12);
        $this->plan('ChatGPT account', 'opplex', 10);
        FooterSetting::create(['email' => 'central-support@example.test']);
        $packages = Package::orderBy('id')->get()->toJson();
        $existing = $this->getJson('/api/v1/site/brands/getiptv/plans?locale=en')->assertOk()->json('data.packages');
        $response = $this->getJson('/api/v1/site/brands/cheapaccounts/plans')->assertOk()
            ->assertJsonPath('data.brand', 'cheapaccounts')->assertJsonPath('data.provider_name', 'Cheap Accounts')
            ->assertJsonPath('data.count', 2);
        $this->assertSame([$opplex->id, $filex->id], array_column($response->json('data.packages'), 'id'));
        $this->assertSame(['Opplex', 'Filex'], array_column($response->json('data.packages'), 'service'));
        $this->seed(CheapAccountsContentSeeder::class);
        foreach (config('app.locales') as $locale) {
            $content = $this->getJson('/api/v1/site/brands/cheapaccounts/content?locale='.$locale)->assertOk()
                ->assertJsonPath('data.locale', $locale)->assertJsonPath('data.brand', 'cheapaccounts')
                ->assertJsonPath('data.support.email', 'central-support@example.test');
            $this->assertSame([7.25, 9.5], array_map('floatval', array_column($content->json('data.packages'), 'price_amount')));
            $this->assertSame([1, null], array_column($content->json('data.packages'), 'max_devices'));
            $this->assertNotEmpty($content->json('data.content.hero_heading'));
            $content->assertJsonMissingPath('data.packages.0.instant_activation');
        }
        $this->assertSame($packages, Package::orderBy('id')->get()->toJson());
        $this->assertSame($existing, $this->getJson('/api/v1/site/brands/getiptv/plans?locale=en')->assertOk()->json('data.packages'));
        foreach (['pages', 'pages/setup', 'blogs', 'blogs/unknown'] as $path) {
            $this->getJson('/api/v1/site/brands/cheapaccounts/'.$path)->assertNotFound();
        }
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_origin_and_consent_checks_keep_store_leads_in_the_existing_crm(): void
    {
        $event = ['brand' => 'cheapaccounts', 'event_id' => (string) Str::uuid(),
            'session_id' => (string) Str::uuid(), 'event' => 'visit', 'analytics_consent' => true, 'website' => ''];
        foreach (['', 'https://getiptv.store', 'https://cheapaccounts.shop.foreign.test'] as $origin) {
            $this->postJson('/api/v1/landing/events', $event, ['Origin' => $origin])->assertForbidden();
        }
        $this->postJson('/api/v1/landing/events', $event, ['Origin' => 'https://cheapaccounts.shop'])->assertStatus(202);
        $this->postJson('/api/v1/landing/events', $event + ['locale' => 'en', 'page' => 'products/opplex-iptv'],
            ['Origin' => 'https://cheapaccounts.shop'])->assertUnprocessable();
        $lead = ['brand' => 'cheapaccounts', 'event_id' => (string) Str::uuid(), 'name' => 'Isolated shopper',
            'email' => 'isolated-shopper@example.test', 'phone' => '+923001234565', 'message' => 'Please check this device.',
            'channel' => 'whatsapp', 'contact_consent' => true, 'website' => ''];
        $this->postJson('/api/v1/landing/leads', $lead, ['Origin' => 'https://www.cheapaccounts.shop'])->assertStatus(202);
        $activity = CustomerActivity::where('source', 'landing_cheapaccounts')->where('event_type', 'landing.contact_requested')->sole();
        $this->assertFalse($activity->details['contact_verified']);
        $this->assertFalse($activity->details['analytics_consent']);
        $this->assertNull($activity->user_id);
        $this->assertDatabaseCount('trial_clicks', 1);
        $this->assertDatabaseCount('orders', 0);
        $service = app(LandingInteractionService::class);
        $service->assertOrigin('cheapaccounts', 'http://127.0.0.1:8107');
        $this->app['env'] = 'production';
        try {
            $service->assertOrigin('cheapaccounts', 'http://127.0.0.1:8107');
            $this->fail('Production accepted the local preview origin.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        } finally {
            $this->app['env'] = 'testing';
        }
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_signed_checkout_and_paid_attribution_preserve_the_selected_provider(): void
    {
        $plans = [[$this->plan('Opplex iptv', 'opplex', 7.25), 793], [$this->plan('FILEX IPTV', 'starshare', 9.50), 1039]];
        $unrelated = $this->plan('TREX IPTV - Yearly', 'opplex', 71, 12);
        foreach ($plans as [$plan, $payableMinor]) {
            $attribution = ['brand' => 'cheapaccounts', 'session_id' => (string) Str::uuid()];
            $response = $this->getJson('/api/browser/checkout?'.http_build_query([
                'package_id' => $plan->id, 'utm_source' => 'cheapaccounts', 'utm_medium' => 'landing',
                'landing_session' => $attribution['session_id'], 'landing_consent' => '1',
            ]))->assertOk();
            $this->assertSame('checkout.example.test', parse_url($response->json('redirect_url'), PHP_URL_HOST));
            parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
            $this->assertSame(hash_hmac('sha256', $query['selection'], self::SECRET), $query['signature']);
            $selection = json_decode(base64_decode(strtr($query['selection'], '-_', '+/')), true);
            $this->assertSame($attribution, $selection['attribution']);
            $this->assertSame($plan->id, $selection['package_id']);
            $this->assertArrayNotHasKey('amount_minor', $selection);
            $payload = ['reference' => 'isolated_'.Str::random(24), 'package_id' => $plan->id,
                'currency' => 'USD', 'environment' => 'production', 'attribution' => $attribution, 'amount_minor' => $payableMinor];
            $this->bridge('prepare', array_replace($payload, ['amount_minor' => $payableMinor + 1]))->assertConflict();
            $this->bridge('prepare', $payload)->assertOk();
            $changed = $payload; $changed['attribution']['brand'] = 'getiptv';
            $this->bridge('prepare', $changed)->assertConflict();
            $payment = $payload + ['tracker' => 'track_'.$payload['reference'], 'customer' => [
                'first_name' => 'Isolated', 'last_name' => 'Payer', 'email' => 'isolated-payer@example.test', 'phone' => '+923001234567']];
            $this->bridge('complete', $payment)->assertOk();
            $this->bridge('complete', $payment)->assertOk()->assertJsonPath('created', false);
            $binding = CustomerExternalLink::where('entity_type', 'landing_attribution')->where('external_id', $payload['reference'])->sole();
            $this->assertSame($attribution, $binding->state['attribution']);
            $this->assertDatabaseHas('customer_activities', ['source' => 'landing_cheapaccounts',
                'event_type' => 'landing.payment_paid', 'order_id' => $binding->order_id]);
        }
        $this->getJson('/api/browser/checkout?'.http_build_query([
            'package_id' => $unrelated->id, 'utm_source' => 'cheapaccounts', 'utm_medium' => 'landing',
        ]))->assertUnprocessable();
        $this->assertSame(2, CustomerActivity::where('event_type', 'landing.payment_paid')->count());
        Http::assertNothingSent();
    }

    public function test_saved_selection_rechecks_price_and_keeps_the_store_source(): void
    {
        $plan = $this->plan('Opplex iptv', 'opplex', 7.25);
        $url = $this->postJson('/api/v1/landing/resume-links', ['brand' => 'cheapaccounts',
            'package_id' => $plan->id, 'locale' => 'en', 'website' => ''], ['Origin' => 'https://cheapaccounts.shop'])
            ->assertCreated()->assertJsonPath('data.email_sent', false)->json('data.url');
        $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer');
        $path = '/api/browser'.parse_url($url, PHP_URL_PATH);
        $this->postJson($path, ['confirm_selection' => true, 'amount_minor' => 724, 'currency' => 'USD'])->assertConflict();
        $response = $this->postJson($path, ['confirm_selection' => true, 'amount_minor' => 725, 'currency' => 'USD'])->assertOk();
        parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
        $this->assertSame('cheapaccounts', $query['utm_source']);
        $this->assertSame((string) $plan->id, $query['package_id']);
        $this->assertDatabaseCount('orders', 0); Mail::assertNothingSent();
    }

    public function test_content_seeding_is_idempotent_and_stops_before_inserting_on_a_mismatch(): void
    {
        $old = LandingPageContent::orderBy('id')->get()->toJson();
        $packages = Package::orderBy('id')->get()->toJson();
        $this->seed(CheapAccountsContentSeeder::class);
        $this->assertSame(10, LandingPageContent::where('brand', 'cheapaccounts')->count());
        $this->assertSame($old, LandingPageContent::where('brand', '!=', 'cheapaccounts')->orderBy('id')->get()->toJson());
        $snapshot = LandingPageContent::orderBy('id')->get()->toJson();
        $this->seed(CheapAccountsContentSeeder::class);
        $this->assertSame($snapshot, LandingPageContent::orderBy('id')->get()->toJson());
        LandingPageContent::where('brand', 'cheapaccounts')->where('locale', 'en')->update(['hero_heading' => 'Existing owner edit']);
        LandingPageContent::where('brand', 'cheapaccounts')->where('locale', 'hi')->delete();
        $before = LandingPageContent::orderBy('id')->get()->toJson();
        try {
            $this->seed(CheapAccountsContentSeeder::class);
            $this->fail('Mismatched existing content did not stop seeding.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('differs', $exception->getMessage());
        }
        $this->assertSame($before, LandingPageContent::orderBy('id')->get()->toJson());
        $this->assertSame($packages, Package::orderBy('id')->get()->toJson());
    }

    public function test_existing_protected_admin_editor_accepts_only_the_selected_store_language(): void
    {
        $this->seed(CheapAccountsContentSeeder::class);
        $this->getJson('/api/browser/admin/landing-pages/cheapaccounts/edit')->assertUnauthorized();
        $admin = Admin::create(['name' => 'Isolated content editor', 'email' => 'cheap-editor@example.test',
            'password' => bcrypt('Isolated!Password123'), 'role' => 'content', 'auth_version' => 1, 'must_change_password' => false]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
        $this->get('/admin/landing-pages/cheapaccounts/edit?locale=it')->assertOk();
        $this->getJson('/api/browser/admin/landing-pages/cheapaccounts/edit?locale=it')->assertOk()->assertJsonPath('data.brand', 'cheapaccounts');
        $page = LandingPageContent::where('brand', 'cheapaccounts')->where('locale', 'it')->firstOrFail();
        $others = LandingPageContent::where('id', '!=', $page->id)->orderBy('id')->get()->toJson();
        $data = $page->publicContent() + ['locale' => 'it', 'is_published' => true];
        $data['hero_heading'] = 'Titolo modificato nel test';
        $this->putJson('/api/browser/admin/landing-pages/cheapaccounts', $data)->assertOk();
        $this->assertSame($data['hero_heading'], $page->fresh()->hero_heading);
        $this->assertSame($others, LandingPageContent::where('id', '!=', $page->id)->orderBy('id')->get()->toJson());
    }

    private function plan(string $title, string $vendor, float $amount, int $months = 1): Package
    {
        return Package::create(['type' => 'iptv', 'vendor' => $vendor, 'title' => $title, 'price_amount' => $amount,
            'duration_months' => $months, 'max_devices' => 1, 'features' => ['Isolated fixture'], 'active' => true, 'is_available' => true]);
    }

    private function bridge(string $action, array $data): \Illuminate\Testing\TestResponse
    {
        $path = '/api/integrations/opplexify/orders/'.$action;
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        return $this->call('POST', $path, [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_OPPLEXIFY_TIMESTAMP' => $timestamp,
            'HTTP_X_OPPLEXIFY_SIGNATURE' => hash_hmac('sha256', "POST\n".$path."\n".$timestamp."\n".hash('sha256', $body), self::SECRET)], $body);
    }
}
