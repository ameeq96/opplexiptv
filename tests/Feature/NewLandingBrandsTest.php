<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomerActivity;
use App\Models\CustomerExternalLink;
use App\Models\LandingPageContent;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewLandingBrandsTest extends TestCase
{
    use RefreshDatabase;

    private const BRANDS = ['iptvsubscription' => 'iptvsubscription.shop', 'getiptv' => 'getiptv.store'];
    private const SECRET = 'isolated-new-landing-secret';
    private const GUIDES = [
        'iptvsubscription' => ['choosing-an-iptv-subscription', 'iptv-subscription-renewal-checklist', 'iptv-device-and-player-costs'],
        'getiptv' => ['how-to-get-iptv-on-your-device', 'iptv-setup-before-checkout', 'troubleshoot-an-iptv-connection'],
    ];

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

    public function test_both_catalogs_preserve_real_opplex_and_filex_plans_at_the_same_duration(): void
    {
        $opplex = $this->plan('Opplex iptv', 'opplex', 1, 7.25);
        $filex = $this->plan('FILEX IPTV', 'starshare', 1, 9.5);
        foreach ([
            ['title' => 'TREX IPTV - Yearly', 'duration_months' => 12],
            ['title' => 'Unapproved provider'], ['title' => 'Opplex iptv - Yearly', 'active' => false],
            ['title' => 'FILEX IPTV - Yearly', 'is_available' => false],
            ['title' => 'Opplex iptv - 3 Months', 'price_amount' => 0],
            ['title' => 'FILEX IPTV - 3 Months', 'type' => 'reseller'],
            ['title' => 'Opplex iptv - Half Yearly', 'vendor' => 'unapproved'],
            ['title' => 'FILEX IPTV - Half Yearly', 'duration_months' => 0],
        ] as $values) {
            Package::create($values + ['type' => 'iptv', 'vendor' => 'opplex', 'duration_months' => 1,
                'price_amount' => 10, 'features' => [], 'active' => true, 'is_available' => true]);
        }
        $snapshot = Package::orderBy('id')->get()->toJson();
        foreach (self::BRANDS as $brand => $domain) {
            $response = $this->getJson('/api/v1/site/brands/'.$brand.'/plans')->assertOk()
                ->assertJsonPath('data.count', 2);
            $this->assertSame([$opplex->id, $filex->id], array_column($response->json('data.packages'), 'id'));
            $this->assertSame(['Opplex', 'Filex'], array_column($response->json('data.packages'), 'service'));
            foreach (config('app.locales') as $locale) {
                $content = $this->getJson('/api/v1/site/brands/'.$brand.'/content?locale='.$locale)->assertOk();
                $this->assertSame([$opplex->id, $filex->id], array_column($content->json('data.packages'), 'id'));
                $this->assertSame([7.25, 9.5], array_map('floatval', array_column($content->json('data.packages'), 'price_amount')));
                $this->assertSame(['Opplex', 'Filex'], array_column($content->json('data.packages'), 'service'));
                $this->assertSame([1, 1], array_column($content->json('data.packages'), 'max_devices'));
                $content->assertJsonMissingPath('data.packages.0.features')->assertJsonMissingPath('data.packages.0.instant_activation');
            }
        }
        $this->assertSame($snapshot, Package::orderBy('id')->get()->toJson());
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_annual_only_rule_does_not_remove_the_other_providers_monthly_option(): void
    {
        $this->plan('Opplex iptv', 'opplex', 1, 5);
        $annual = $this->plan('Opplex iptv - Yearly', 'opplex', 12, 50);
        $annual->update(['features' => ['Yearly subscription only (monthly plan unavailable)']]);
        $filex = $this->plan('FILEX IPTV', 'starshare', 1, 8);
        foreach (array_keys(self::BRANDS) as $brand) {
            $response = $this->getJson('/api/v1/site/brands/'.$brand.'/plans')->assertOk();
            $this->assertEqualsCanonicalizing([$annual->id, $filex->id], array_column($response->json('data.packages'), 'id'));
        }
    }

    public function test_named_current_offers_replace_legacy_aliases_without_inventing_connection_allowances(): void
    {
        $legacyOpplex = $this->plan('Monthly', 'opplex', 1, 7.25);
        $legacyOpplex->update(['max_devices' => null]);
        $legacyFilex = $this->plan('Monthly', 'starshare', 1, 9.5);
        $legacyFilex->update(['max_devices' => null]);
        $opplex = $this->plan('Opplex iptv', 'opplex', 1, 7.25);
        $filex = $this->plan('FILEX IPTV', 'starshare', 1, 9.5);
        $filex->update(['max_devices' => null]);
        $differentPrice = $this->plan('Monthly', 'opplex', 1, 8.75);
        $differentAllowance = $this->plan('Monthly', 'opplex', 1, 7.25);
        $differentAllowance->update(['max_devices' => 2]);
        foreach (array_keys(self::BRANDS) as $brand) {
            $response = $this->getJson('/api/v1/site/brands/'.$brand.'/content')->assertOk()->assertJsonPath('data.count', 4);
            $plans = collect($response->json('data.packages'))->keyBy('id');
            $this->assertEqualsCanonicalizing([$opplex->id, $filex->id, $differentPrice->id, $differentAllowance->id], $plans->keys()->all());
            $this->assertSame('Opplex', $plans[$opplex->id]['service']);
            $this->assertSame(1, $plans[$opplex->id]['max_devices']);
            $this->assertSame('Filex', $plans[$filex->id]['service']);
            $this->assertNull($plans[$filex->id]['max_devices']);
            $this->assertSame(2, $plans[$differentAllowance->id]['max_devices']);
            $this->assertSame(8.75, (float) $plans[$differentPrice->id]['price_amount']);
        }
        $this->assertNull($legacyOpplex->fresh()->max_devices);
        $this->assertNull($filex->fresh()->max_devices);
    }

    public function test_all_localized_pages_and_guides_are_available_without_cross_brand_guide_aliases(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        foreach (self::BRANDS as $brand => $domain) {
            foreach (config('app.locales') as $locale) {
                $response = $this->getJson('/api/v1/site/brands/'.$brand.'/pages?locale='.$locale)->assertOk()
                    ->assertJsonPath('data.brand', $brand)->assertJsonPath('data.locale', $locale)->assertJsonCount(18, 'data.pages');
                $slugs = array_column($response->json('data.pages'), 'slug');
                $this->assertContains('packages', $slugs);
                $this->assertNotContains('pricing', $slugs);
                foreach (self::GUIDES[$brand] as $slug) {
                    $this->assertContains('blogs/'.$slug, $slugs);
                    $this->getJson('/api/v1/site/brands/'.$brand.'/pages/blogs/'.$slug.'?locale='.$locale)->assertOk();
                }
            }
            $other = $brand === 'getiptv' ? 'iptvsubscription' : 'getiptv';
            $this->getJson('/api/v1/site/brands/'.$brand.'/pages/blogs/'.self::GUIDES[$other][0])->assertNotFound();
            $this->getJson('/api/v1/site/brands/'.$brand.'/pages?locale=unknown')->assertUnprocessable();
        }
    }

    public function test_new_origins_are_brand_bound_and_contacts_stay_in_existing_crm(): void
    {
        foreach (self::BRANDS as $brand => $domain) {
            $payload = ['brand' => $brand, 'event_id' => (string) Str::uuid(), 'session_id' => (string) Str::uuid(),
                'event' => 'visit', 'analytics_consent' => true, 'locale' => 'it', 'page' => 'setup', 'website' => ''];
            $other = $brand === 'getiptv' ? 'iptvsubscription' : 'getiptv';
            foreach (['', 'https://'.self::BRANDS[$other], 'https://'.$domain.'.foreign.test'] as $origin) {
                $this->postJson('/api/v1/landing/events', $payload, ['Origin' => $origin])->assertForbidden();
            }
            $this->postJson('/api/v1/landing/events', $payload, ['Origin' => 'https://www.'.$domain])->assertStatus(202);
            $lead = ['brand' => $brand, 'event_id' => (string) Str::uuid(), 'name' => 'Isolated visitor',
                'email' => $brand.'@example.test', 'phone' => $brand === 'getiptv' ? '+923001234561' : '+923001234562',
                'message' => 'Please check this device.', 'channel' => 'whatsapp', 'contact_consent' => true, 'website' => ''];
            $this->postJson('/api/v1/landing/leads', $lead, ['Origin' => 'https://'.$domain])->assertStatus(202);
            $activity = CustomerActivity::where('source', 'landing_'.$brand)->where('event_type', 'landing.contact_requested')->sole();
            $this->assertFalse($activity->details['contact_verified']);
            $this->assertFalse($activity->details['analytics_consent']);
            $this->assertNull($activity->user_id);
        }
        $this->assertDatabaseCount('trial_clicks', 2);
        $this->assertDatabaseCount('orders', 0);
        foreach (['iptvsubscription' => 8105, 'getiptv' => 8106] as $brand => $port) {
            $service = app(\App\Services\Clients\LandingInteractionService::class);
            $service->assertOrigin($brand, 'http://127.0.0.1:'.$port);
            $this->app['env'] = 'production';
            try {
                $service->assertOrigin($brand, 'http://127.0.0.1:'.$port);
                $this->fail('Production accepted a local landing origin.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            } finally {
                $this->app['env'] = 'testing';
            }
        }
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched(); Http::assertNothingSent();
    }

    public function test_checkout_and_paid_attribution_keep_each_original_provider_package(): void
    {
        $plans = [$this->plan('Opplex iptv', 'opplex', 1, 7.25), $this->plan('FILEX IPTV', 'starshare', 1, 9.5)];
        $unrelated = $this->plan('TREX IPTV - Yearly', 'opplex', 12, 71);
        foreach (self::BRANDS as $brand => $domain) {
            foreach ($plans as $plan) {
                $attribution = ['brand' => $brand, 'session_id' => (string) Str::uuid(), 'locale' => 'it',
                    'page' => 'blogs/'.self::GUIDES[$brand][0]];
                $response = $this->getJson('/api/browser/checkout?'.http_build_query([
                    'package_id' => $plan->id, 'utm_source' => $brand, 'utm_medium' => 'landing',
                    'landing_session' => $attribution['session_id'], 'landing_consent' => '1',
                    'landing_locale' => 'it', 'landing_page' => $attribution['page'],
                ]))->assertOk();
                $this->assertSame('checkout.example.test', parse_url($response->json('redirect_url'), PHP_URL_HOST));
                parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
                $this->assertSame(hash_hmac('sha256', $query['selection'], self::SECRET), $query['signature']);
                $selection = json_decode(base64_decode(strtr($query['selection'], '-_', '+/')), true);
                $this->assertSame($attribution, $selection['attribution']);
                $this->assertSame($plan->id, $selection['package_id']);
                $base = (int) round((float) $plan->price_amount * 100);
                $payload = ['reference' => 'isolated_'.Str::random(24), 'package_id' => $plan->id,
                    'currency' => 'USD', 'environment' => 'production', 'attribution' => $attribution,
                    'amount_minor' => $base + array_sum(array_map(static fn ($sample) => (int) round($base * $sample / 1199), [71, 5, 12, 24]))];
                $this->bridge('prepare', $payload)->assertOk();
                $changed = $payload;
                $changed['attribution']['brand'] = $brand === 'getiptv' ? 'iptvsubscription' : 'getiptv';
                $changed['attribution']['page'] = 'setup';
                $this->bridge('prepare', $changed)->assertConflict();
                $payment = $payload + ['tracker' => 'track_'.$payload['reference'],
                    'customer' => ['first_name' => 'Isolated', 'last_name' => 'Payer',
                        'email' => 'isolated-payer@example.test', 'phone' => '+923001234567']];
                $this->bridge('complete', $payment)->assertOk();
                $this->bridge('complete', $payment)->assertOk()->assertJsonPath('created', false);
                $binding = CustomerExternalLink::where('entity_type', 'landing_attribution')->where('external_id', $payload['reference'])->sole();
                $this->assertSame($attribution, $binding->state['attribution']);
                $this->assertDatabaseHas('customer_activities', ['source' => 'landing_'.$brand,
                    'event_type' => 'landing.payment_paid', 'order_id' => $binding->order_id]);
            }
            $this->getJson('/api/browser/checkout?'.http_build_query([
                'package_id' => $unrelated->id, 'utm_source' => $brand, 'utm_medium' => 'landing',
            ]))->assertUnprocessable();
        }
        $this->assertSame(4, CustomerActivity::where('event_type', 'landing.payment_paid')->count());
        Http::assertNothingSent();
    }

    public function test_saved_selections_return_to_the_correct_brand_without_sending_email(): void
    {
        $plans = [$this->plan('Opplex iptv', 'opplex', 1, 7.25), $this->plan('FILEX IPTV', 'starshare', 1, 9.5)];
        foreach (self::BRANDS as $brand => $domain) {
            foreach ($plans as $plan) {
                $url = $this->postJson('/api/v1/landing/resume-links', [
                    'brand' => $brand, 'package_id' => $plan->id, 'locale' => 'it', 'website' => '',
                ], ['Origin' => 'https://'.$domain])->assertCreated()->assertJsonPath('data.email_sent', false)->json('data.url');
                $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
                    ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
                $response = $this->postJson('/api/browser'.parse_url($url, PHP_URL_PATH), [
                    'confirm_selection' => true, 'amount_minor' => (int) round((float) $plan->price_amount * 100), 'currency' => 'USD',
                ])->assertOk();
                parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
                $this->assertSame($brand, $query['utm_source']);
                $this->assertSame((string) $plan->id, $query['package_id']);
            }
        }
        $this->assertDatabaseCount('orders', 0); $this->assertDatabaseCount('trial_clicks', 0);
        Mail::assertNothingSent(); Http::assertNothingSent();
    }

    public function test_seed_and_admin_edits_are_scoped_to_the_new_brand_language(): void
    {
        $this->seed(\Database\Seeders\LandingPageTranslationSeeder::class);
        $old = LandingPageContent::orderBy('id')->get()->toJson();
        $packages = Package::orderBy('id')->get()->toJson();
        $this->seed(\Database\Seeders\NewLandingBrandsSeeder::class);
        $this->assertDatabaseCount('landing_page_contents', 60);
        $snapshot = LandingPageContent::orderBy('id')->get()->toJson();
        $this->seed(\Database\Seeders\NewLandingBrandsSeeder::class);
        $this->assertSame($snapshot, LandingPageContent::orderBy('id')->get()->toJson());
        $this->assertSame($old, LandingPageContent::whereNotIn('brand', array_keys(self::BRANDS))->orderBy('id')->get()->toJson());
        $this->assertSame($packages, Package::orderBy('id')->get()->toJson());
        foreach (array_keys(self::BRANDS) as $brand) {
            $this->getJson('/api/browser/admin/landing-pages/'.$brand.'/edit')->assertUnauthorized();
        }
        $admin = Admin::create(['name' => 'Isolated editor', 'email' => 'new-landing-editor@example.test',
            'password' => bcrypt('Isolated!Password123'), 'role' => 'content', 'auth_version' => 1, 'must_change_password' => false]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
        foreach (array_keys(self::BRANDS) as $brand) {
            $this->get('/admin/landing-pages/'.$brand.'/edit?locale=it')->assertOk();
            $this->getJson('/api/browser/admin/landing-pages/'.$brand.'/edit?locale=it')->assertOk()->assertJsonPath('data.brand', $brand);
            $page = LandingPageContent::where('brand', $brand)->where('locale', 'it')->firstOrFail();
            $others = LandingPageContent::where('id', '!=', $page->id)->orderBy('id')->get()->toJson();
            $data = $page->publicContent() + ['locale' => 'it', 'is_published' => true];
            $data['hero_heading'] = 'Isolated edited heading';
            $this->putJson('/api/browser/admin/landing-pages/'.$brand, $data)->assertOk();
            $this->assertSame($data['hero_heading'], $page->fresh()->hero_heading);
            $this->assertSame($others, LandingPageContent::where('id', '!=', $page->id)->orderBy('id')->get()->toJson());
        }
    }

    public function test_indexnow_accepts_new_domains_only_after_public_page_validation(): void
    {
        foreach (self::BRANDS as $brand => $domain) {
            $origin = 'https://'.$domain;
            $url = $origin.'/packages/';
            Http::fake([
                $origin.'/sitemap.xml' => Http::response('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>'.$url.'</loc></url></urlset>', 200),
                $origin.'/indexnow-key.txt' => Http::response('isolated-new-brand-key-0123456789', 200),
                $url => Http::response('<!doctype html><html><head><title>Plans</title><link rel="canonical" href="'.$url.'"></head><body><main><h1>Current plans</h1></main></body></html>', 200, ['Content-Type' => 'text/html']),
            ]);
            $this->assertSame(0, Artisan::call('landing:indexnow', ['--domain' => $domain, '--url' => [$url]]));
            $this->assertStringContainsString('Dry run', Artisan::output());
            $this->assertSame(1, Artisan::call('landing:indexnow', ['--domain' => $domain, '--url' => [$origin.'/account/']]));
        }
        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST');
    }

    private function plan(string $title, string $vendor, int $months, float $amount): Package
    {
        return Package::create(['type' => 'iptv', 'vendor' => $vendor, 'title' => $title, 'price_amount' => $amount,
            'duration_months' => $months, 'max_devices' => 1, 'features' => ['Isolated fixture'], 'active' => true, 'is_available' => true]);
    }

    private function bridge(string $action, array $data): \Illuminate\Testing\TestResponse
    {
        $path = '/api/integrations/opplexify/orders/'.$action;
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        return $this->call('POST', $path, [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_OPPLEXIFY_TIMESTAMP' => $timestamp,
            'HTTP_X_OPPLEXIFY_SIGNATURE' => hash_hmac('sha256', "POST\n".$path."\n".$timestamp."\n".hash('sha256', $body), self::SECRET),
        ], $body);
    }
}
