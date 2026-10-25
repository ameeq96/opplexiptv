<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\FooterSetting;
use App\Models\LandingPageContent;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LandingPageContentTest extends TestCase
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
        Bus::fake();
        Mail::fake();
        Notification::fake();
    }

    private function signIn(string $role = 'owner'): Admin
    {
        $admin = Admin::create([
            'name' => 'Landing editor', 'email' => 'landing-editor@example.test',
            'password' => bcrypt('Isolated!Password123'), 'role' => $role,
            'auth_version' => 1, 'must_change_password' => false,
        ]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession([
            'admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id,
        ]);

        return $admin;
    }

    private function payload(string $brand = '5g'): array
    {
        $page = LandingPageContent::where('brand', $brand)->where('locale', 'en')->firstOrFail();

        return $page->publicContent() + ['locale' => 'en', 'is_published' => true];
    }

    public function test_four_original_guides_are_seeded_without_touching_package_records(): void
    {
        $this->assertDatabaseCount('landing_page_contents', 4);
        $headings = [];
        foreach (['trex', 'opplex', 'geo', '5g'] as $brand) {
            $response = $this->getJson('/api/v1/site/brands/'.$brand.'/content?locale=en')->assertOk()
                ->assertJsonPath('data.brand', $brand)->assertJsonPath('data.locale', 'en');
            $headings[] = $response->json('data.content.hero_heading');
            $this->assertNotEmpty($response->json('data.content.guide_sections'));
            $this->assertNotEmpty($response->json('data.content.faqs'));
            $this->assertNotEmpty($response->json('data.content.highlights'));
        }
        $this->assertCount(4, array_unique($headings));
        $this->assertStringContainsString('not a mobile network', $this->payload()['guide_intro']);
        $this->assertStringContainsString('not a promise', $this->payload('trex')['faqs'][0]['answer']);
    }

    public function test_translations_seed_all_brand_languages_without_overwriting_existing_rows(): void
    {
        $english = LandingPageContent::orderBy('id')->get()->toJson();
        $packages = Package::orderBy('id')->get()->toJson();
        $this->seed(\Database\Seeders\LandingPageTranslationSeeder::class);
        $this->assertDatabaseCount('landing_page_contents', 40);
        $this->assertSame($english, LandingPageContent::where('locale', 'en')->orderBy('id')->get()->toJson());
        $this->assertSame($packages, Package::orderBy('id')->get()->toJson());
        foreach (['trex', 'opplex', 'geo', '5g'] as $brand) {
            foreach (['es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'] as $locale) {
                $page = LandingPageContent::where('brand', $brand)->where('locale', $locale)->firstOrFail();
                $this->getJson('/api/v1/site/brands/'.$brand.'/content?locale='.$locale)->assertOk()
                    ->assertJsonPath('data.locale', $locale)
                    ->assertJsonPath('data.content', $page->publicContent());
            }
        }
        $page = LandingPageContent::where('brand', 'geo')->where('locale', 'ur')->firstOrFail();
        $page->update(['hero_heading' => 'Existing editor text', 'is_published' => false]);
        $snapshot = LandingPageContent::orderBy('id')->get()->toJson();
        $this->seed(\Database\Seeders\LandingPageTranslationSeeder::class);
        $this->assertSame($snapshot, LandingPageContent::orderBy('id')->get()->toJson());
        $this->assertSame($packages, Package::orderBy('id')->get()->toJson());
    }

    public function test_missing_translation_reports_fallback_but_unpublished_translation_stays_private(): void
    {
        $this->getJson('/api/v1/site/brands/geo/content?locale=ur')->assertOk()
            ->assertJsonPath('data.locale', 'en')->assertJsonPath('data.content.hero_heading', $this->payload('geo')['hero_heading']);
        $this->seed(\Database\Seeders\LandingPageTranslationSeeder::class);
        LandingPageContent::where('brand', 'geo')->where('locale', 'ur')->update([
            'hero_heading' => 'PRIVATE TRANSLATED DRAFT', 'is_published' => false,
        ]);
        $response = $this->getJson('/api/v1/site/brands/geo/content?locale=ur')->assertOk()
            ->assertJsonPath('data.locale', 'ur')->assertJsonPath('data.content', null);
        $this->assertStringNotContainsString('PRIVATE TRANSLATED DRAFT', $response->getContent());
    }

    public function test_language_selector_and_admin_save_target_only_the_selected_language(): void
    {
        $this->seed(\Database\Seeders\LandingPageTranslationSeeder::class);
        $this->signIn('content');
        $this->get('/admin/landing-pages/geo/edit?locale=ar')->assertOk()
            ->assertSee('id="landing-content-locale"', false)
            ->assertSee('value="ar" selected', false)->assertSee('dir="rtl"', false);
        $otherRows = LandingPageContent::whereNot(fn ($query) => $query->where('brand', 'geo')->where('locale', 'ar'))->orderBy('id')->get()->toJson();
        $page = LandingPageContent::where('brand', 'geo')->where('locale', 'ar')->firstOrFail();
        $payload = $page->publicContent() + ['locale' => 'ar', 'is_published' => true];
        $payload['hero_heading'] = 'GEO IPTV — عنوان تجريبي';
        $this->putJson('/api/browser/admin/landing-pages/geo', $payload)->assertOk()
            ->assertJsonPath('redirect_url', '/admin/landing-pages/geo/edit?locale=ar');
        $this->assertSame($payload['hero_heading'], $page->fresh()->hero_heading);
        $this->assertSame($otherRows, LandingPageContent::whereNot(fn ($query) => $query->where('brand', 'geo')->where('locale', 'ar'))->orderBy('id')->get()->toJson());
    }

    public function test_public_packages_project_numeric_facts_not_legacy_marketing_fields(): void
    {
        Package::query()->where('title', 'like', '5G IPTV%')->delete();
        $package = Package::create([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => '5G IPTV', 'duration_months' => 1,
            'price_amount' => 7.25, 'display_price' => '$1 misleading display', 'max_devices' => 2,
            'features' => ['UNVERIFIED CLAIM'], 'badge_key' => 'most_popular', 'free_trial_hours' => 24,
            'instant_activation' => true, 'active' => true, 'is_available' => true,
        ]);
        $package->translations()->create(['locale' => 'en', 'title' => 'UNVERIFIED TITLE', 'features' => ['UNVERIFIED TRANSLATION']]);
        config()->set('services.app.default_currency', 'EUR');
        $response = $this->getJson('/api/v1/site/brands/5g/content')->assertOk()
            ->assertJsonPath('data.count', 1)->assertJsonPath('data.packages.0.id', $package->id)
            ->assertJsonPath('data.packages.0.price_amount', 7.25)
            ->assertJsonPath('data.packages.0.currency', 'EUR')
            ->assertJsonPath('data.packages.0.duration_months', 1)
            ->assertJsonPath('data.packages.0.max_devices', 2)
            ->assertJsonPath('data.packages.0.title', 'Monthly plan');
        foreach (['price', 'features', 'badge_key', 'is_featured', 'free_trial_hours', 'instant_activation', 'connection_prices'] as $field) {
            $response->assertJsonMissingPath('data.packages.0.'.$field);
        }
        $this->assertStringNotContainsString('UNVERIFIED', $response->getContent());
        $this->assertStringNotContainsString('misleading display', $response->getContent());
        $this->assertSame(['UNVERIFIED CLAIM'], $package->fresh()->features);
        $package->update(['features' => [], 'max_devices' => 0]);
        $response = $this->getJson('/api/v1/site/brands/5g/content')->assertOk()
            ->assertJsonPath('data.packages.0.max_devices', null)->assertJsonMissingPath('data.packages.0.features');
        $this->assertStringNotContainsString('Buffer', $response->getContent());
    }

    public function test_brand_price_selection_remains_fail_closed(): void
    {
        Package::query()->where('title', 'like', '5G IPTV%')->delete();
        foreach ([
            ['title' => '5G IPTV', 'duration_months' => 1, 'active' => false],
            ['title' => '5G IPTV - 3 Months', 'duration_months' => 3, 'is_available' => false],
            ['title' => '5G IPTV - Half Yearly', 'duration_months' => 12],
            ['title' => 'Other brand', 'duration_months' => 1],
            ['title' => '5G IPTV - Yearly', 'duration_months' => 12, 'price_amount' => 0],
        ] as $values) {
            Package::create($values + ['type' => 'iptv', 'vendor' => 'opplex', 'price_amount' => 10, 'active' => true, 'is_available' => true]);
        }
        $this->getJson('/api/v1/site/brands/5g/content')->assertOk()->assertJsonPath('data.packages', [])->assertJsonPath('data.count', 0);
        $this->getJson('/api/v1/site/brands/unknown/content')->assertNotFound();
        $this->getJson('/api/v1/site/brands/5g/content?locale=invalid')->assertUnprocessable();
    }

    public function test_main_site_contacts_and_relative_policy_links_exclude_private_configuration(): void
    {
        FooterSetting::create(['email' => 'help@example.test', 'address' => 'PRIVATE ADDRESS']);
        config()->set('services.whatsapp.number', '+1 (555) 123-4567');
        config()->set('services.whatsapp.display', '+1 (555) 123-4567');
        config()->set('services.opplexify.shared_secret', 'PRIVATE SECRET');
        $response = $this->getJson('/api/v1/site/brands/geo/content')->assertOk()
            ->assertJsonPath('data.support.phone', '15551234567')
            ->assertJsonPath('data.support.email', 'help@example.test')
            ->assertJsonPath('data.links.account', '/account')
            ->assertJsonPath('data.links.setup', '/account/setup');
        $this->assertSame(['/terms-of-service', '/privacy-policy', '/refund-policy'], array_column($response->json('data.links.legal'), 'url'));
        $this->assertStringNotContainsString('PRIVATE', $response->getContent());
    }

    public function test_unpublished_or_missing_content_does_not_leak_or_create_rows(): void
    {
        $page = LandingPageContent::where('brand', 'geo')->firstOrFail();
        $page->update(['is_published' => false, 'hero_heading' => 'PRIVATE DRAFT']);
        $response = $this->getJson('/api/v1/site/brands/geo/content')->assertOk()->assertJsonPath('data.content', null)->assertJsonPath('data.updated_at', null);
        $this->assertStringNotContainsString('PRIVATE DRAFT', $response->getContent());
        $page->delete();
        $this->getJson('/api/v1/site/brands/geo/content')->assertOk()->assertJsonPath('data.content', null);
        $this->assertDatabaseCount('landing_page_contents', 3);
    }

    public function test_owner_and_content_editor_can_save_one_brand_with_audit_and_without_package_changes(): void
    {
        $admin = $this->signIn('content');
        $packageSnapshot = Package::orderBy('id')->get()->toJson();
        $otherSnapshot = LandingPageContent::where('brand', 'geo')->firstOrFail()->toJson();
        $this->getJson('/api/browser/admin/landing-pages/5g/edit')->assertOk()->assertJsonPath('data.brand', '5g')
            ->assertJsonPath('data.section.hero_heading', '5G Live IPTV. Your setup.');
        $payload = $this->payload();
        $payload['hero_heading'] = '5G Live IPTV. Check your connection.';
        $this->putJson('/api/browser/admin/landing-pages/5g', $payload)->assertOk()
            ->assertJsonPath('redirect_url', '/admin/landing-pages/5g/edit?locale=en');
        $this->getJson('/api/v1/site/brands/5g/content')->assertOk()->assertJsonPath('data.content.hero_heading', $payload['hero_heading']);
        $this->assertSame($packageSnapshot, Package::orderBy('id')->get()->toJson());
        $this->assertSame($otherSnapshot, LandingPageContent::where('brand', 'geo')->firstOrFail()->toJson());
        $this->assertDatabaseHas('admin_audit_logs', ['admin_id' => $admin->id, 'route_name' => 'api.browser.admin.landing-pages.update', 'response_status' => 200]);
        $admin->update(['role' => 'owner']);
        $this->get('/admin/landing-pages/5g/edit')->assertOk()->assertSeeText('Landing Page Content')->assertSeeText('Save 5G Live IPTV content');
    }

    public function test_blank_slots_are_normalized_but_partial_sections_are_rejected(): void
    {
        $this->signIn();
        $payload = $this->payload();
        $payload['guide_sections'][] = ['heading' => '', 'body' => ''];
        $payload['faqs'][] = ['question' => '', 'answer' => ''];
        $payload['highlights'][] = '';
        $this->putJson('/api/browser/admin/landing-pages/5g', $payload)->assertOk();
        $page = LandingPageContent::where('brand', '5g')->firstOrFail();
        $this->assertCount(3, $page->guide_sections);
        $this->assertCount(3, $page->faqs);
        $this->assertCount(3, $page->highlights);
        $payload['guide_sections'][0]['body'] = '';
        $this->putJson('/api/browser/admin/landing-pages/5g', $payload)->assertUnprocessable()->assertJsonValidationErrors('guide_sections.0.body');
    }

    public function test_html_oversized_and_unexpected_nested_fields_are_rejected(): void
    {
        $this->signIn();
        $payload = $this->payload();
        $payload['hero_heading'] = '<script>alert(1)</script>';
        $payload['guide_sections'][0]['url'] = 'https://untrusted.example.test';
        $payload['faqs'][0]['answer'] = str_repeat('a', 2001);
        $payload['highlights'][0] = '<img src=x onerror=alert(1)>';
        $this->putJson('/api/browser/admin/landing-pages/5g', $payload)->assertUnprocessable()
            ->assertJsonValidationErrors(['hero_heading', 'guide_sections.0', 'faqs.0.answer', 'highlights.0']);
        $this->assertSame('5G Live IPTV. Your setup.', LandingPageContent::where('brand', '5g')->firstOrFail()->hero_heading);
        $payload = $this->payload();
        $payload['guide_sections'] = array_fill(0, 9, ['heading' => 'Title', 'body' => 'Text']);
        $this->putJson('/api/browser/admin/landing-pages/5g', $payload)->assertUnprocessable()->assertJsonValidationErrors('guide_sections');
    }

    public function test_guest_sales_and_support_cannot_edit_landing_content(): void
    {
        $this->getJson('/api/browser/admin/landing-pages/5g/edit')->assertUnauthorized();
        $this->putJson('/api/browser/admin/landing-pages/5g', $this->payload())->assertUnauthorized();
        $admin = $this->signIn('sales');
        foreach (['sales', 'support'] as $role) {
            $admin->update(['role' => $role]);
            $this->getJson('/api/browser/admin/landing-pages/5g/edit')->assertForbidden();
            $this->putJson('/api/browser/admin/landing-pages/5g', $this->payload())->assertForbidden();
        }
    }

    public function test_admin_csrf_and_current_session_are_required(): void
    {
        $this->signIn();
        $this->app->bind(VerifyCsrfToken::class, LandingContentEnforcedCsrf::class);
        $this->putJson('/api/browser/admin/landing-pages/5g', $this->payload())->assertStatus(419);
        $this->withSession(['admin_auth_version' => 0]);
        $this->getJson('/api/browser/admin/landing-pages/5g/edit')->assertUnauthorized();
        $this->assertSame('5G Live IPTV. Your setup.', LandingPageContent::where('brand', '5g')->firstOrFail()->hero_heading);
    }

    public function test_password_and_two_factor_requirements_are_preserved(): void
    {
        $admin = $this->signIn();
        $admin->update(['must_change_password' => true]);
        $this->assertContains($this->getJson('/api/browser/admin/landing-pages/5g/edit')->status(), [403, 428]);
        $admin->update(['must_change_password' => false]);
        $this->withSession(['admin_two_factor_verified_id' => null]);
        $this->assertContains($this->getJson('/api/browser/admin/landing-pages/5g/edit')->status(), [403, 428]);
    }
}

class LandingContentEnforcedCsrf extends VerifyCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
