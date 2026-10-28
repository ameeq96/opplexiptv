<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Admin;
use App\Models\Blog;
use App\Models\BlogGenerationRun;
use App\Models\BlogPublishDelivery;
use App\Models\BlogSiteSetting;
use App\Services\Blogs\BlogPublicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class BlogAutomationAdminTest extends TestCase
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
        Http::preventStrayRequests(); Bus::fake(); Mail::fake(); Notification::fake(); Cache::flush();
    }

    public function test_roles_owner_only_preferences_and_audit_are_preserved(): void
    {
        $url = '/api/browser/admin/blogs/automation';
        $this->getJson($url)->assertUnauthorized();
        $this->signIn('sales');
        $this->getJson($url)->assertForbidden();
        $this->signIn('support');
        $this->getJson($url)->assertForbidden();
        $this->signIn('content');
        $this->getJson($url)->assertOk()->assertJsonCount(7, 'data.settings')
            ->assertJsonPath('data.canManage', false);
        $this->patchJson($url.'/geo', ['enabled' => true])->assertForbidden();

        $owner = $this->signIn('owner');
        $this->patchJson($url.'/geo', ['enabled' => true, 'timezone' => 'UTC', 'locales' => ['en']])->assertOk();
        $geo = BlogSiteSetting::where('site_key', 'geo')->firstOrFail();
        $this->assertTrue($geo->enabled);
        $this->assertSame('Asia/Karachi', $geo->timezone);
        $this->assertSame(config('blog-publishing.supported_locales'), $geo->locales);
        $this->assertSame(1, BlogSiteSetting::where('enabled', true)->count());
        $this->assertDatabaseHas('admin_audit_logs', ['admin_id' => $owner->id,
            'route_name' => 'api.browser.admin.blogs.automation.update', 'response_status' => 200]);
        $this->get('/admin/blogs/automation')->assertOk()->assertSeeText('Daily articles across your IPTV websites');
        Http::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_existing_main_manual_writes_keep_partial_translations_future_dates_and_slug_edits(): void
    {
        $admin = $this->signIn('content');
        $future = now()->addDays(3)->startOfMinute();
        $data = ['status' => 'published', 'published_at' => $future->toDateTimeString(),
            'translations' => ['en' => ['title' => 'Isolated main manual article', 'slug' => 'isolated-main-manual', 'content' => '<p>Isolated editorial content.</p>']]];
        $this->postJson('/api/browser/admin/blogs', $data)->assertOk();
        $blog = Blog::firstOrFail();
        $this->assertSame('main', $blog->site_key);
        $this->assertSame('published', $blog->status);
        $this->assertTrue($blog->published_at->equalTo($future));
        $this->assertNull($blog->author_id);
        $this->assertNotSame($admin->id, $blog->author_id);
        $this->assertSame(1, $blog->translations()->count());
        $this->assertDatabaseCount('blog_generation_runs', 0);

        $data['translations']['en']['slug'] = 'isolated-main-manual-edited';
        $this->putJson('/api/browser/admin/blogs/'.$blog->id, $data)->assertOk();
        $this->assertSame('isolated-main-manual-edited', $blog->translations()->first()->slug);
        $this->assertDatabaseCount('blog_generation_runs', 0);
    }

    public function test_requested_landing_publication_is_saved_as_draft_and_only_queued(): void
    {
        $this->signIn('content');
        $this->mock(BlogPublicationService::class, function ($mock) {
            $mock->shouldReceive('queueExisting')->once()->andReturnUsing(function (Blog $blog) {
                $this->assertSame('geo', $blog->site_key);
                $this->assertSame('draft', $blog->status);
                $this->assertNull($blog->author_id);
                return new BlogGenerationRun(['site_key' => 'geo', 'blog_id' => $blog->id, 'status' => 'ready']);
            });
        });
        $this->postJson('/api/browser/admin/blogs', ['site_key' => 'geo', 'status' => 'published',
            'translations' => ['en' => ['title' => 'Isolated Geo draft', 'slug' => 'geo-isolated-draft', 'content' => '<p>Isolated draft content.</p>']]])
            ->assertOk()->assertJsonPath('redirect_url', '/admin/blogs');
        $this->assertDatabaseHas('blogs', ['site_key' => 'geo', 'status' => 'draft', 'author_id' => null]);
        Http::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_landing_site_and_existing_slug_cannot_be_moved_by_an_edit(): void
    {
        $this->signIn('content');
        $blog = Blog::create(['site_key' => 'geo', 'status' => 'draft']);
        $blog->translations()->create(['locale' => 'en', 'title' => 'Isolated existing Geo article', 'slug' => 'geo-original-slug', 'content' => '<p>Isolated draft.</p>']);
        $data = ['site_key' => 'main', 'status' => 'draft', 'translations' => ['en' => ['title' => 'Isolated edited title', 'slug' => 'geo-original-slug', 'content' => '<p>Edited isolated draft.</p>']]];
        $this->putJson('/api/browser/admin/blogs/'.$blog->id, $data)->assertUnprocessable()->assertJsonValidationErrors('site_key');
        $data['site_key'] = 'geo'; $data['translations']['en']['slug'] = 'geo-a-different-slug';
        $this->putJson('/api/browser/admin/blogs/'.$blog->id, $data)->assertUnprocessable()->assertJsonValidationErrors('translations.en.slug');
        $data['translations']['en']['slug'] = null;
        $this->putJson('/api/browser/admin/blogs/'.$blog->id, $data)->assertOk();
        $this->assertSame('geo', $blog->fresh()->site_key);
        $this->assertSame('geo-original-slug', $blog->translations()->first()->slug);
    }

    public function test_retry_is_owner_only_and_api_contract_does_not_expose_import_payloads(): void
    {
        $run = BlogGenerationRun::create(['site_key' => 'geo', 'topic' => 'Isolated failed publication', 'status' => 'failed',
            'revision' => hash('sha256', 'isolated-revision'), 'payload' => ['private_fixture' => 'must-not-appear'], 'errors' => ['isolated_failure']]);
        BlogPublishDelivery::create(['run_id' => $run->id, 'channel' => 'indexnow', 'revision' => $run->revision,
            'status' => 'failed', 'http_status' => 429, 'error_code' => 'rate_limited']);
        $this->mock(BlogPublicationService::class, function ($mock) use ($run) {
            $mock->shouldReceive('retry')->once()->with(\Mockery::on(fn ($candidate) => $candidate->id === $run->id))->andReturnNull();
            $mock->shouldReceive('initializeSettings')->once()->andReturnNull();
        });
        $this->signIn('content');
        $this->postJson('/api/browser/admin/blogs/automation/runs/'.$run->id.'/retry')->assertForbidden();
        $this->signIn('owner');
        $this->postJson('/api/browser/admin/blogs/automation/runs/'.$run->id.'/retry')->assertOk();
        $this->getJson('/api/browser/admin/blogs/automation')->assertOk()
            ->assertJsonMissingPath('data.runs.0.payload')->assertJsonPath('data.runs.0.deliveries.0.error_code', 'rate_limited')
            ->assertDontSee('must-not-appear')->assertDontSee('blog-google-service-account.json');
        Http::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_csrf_session_password_and_two_factor_checks_remain_required(): void
    {
        $admin = $this->signIn('owner');
        $url = '/api/browser/admin/blogs/automation';
        $this->app->bind(VerifyCsrfToken::class, BlogAutomationEnforcedCsrf::class);
        $this->patchJson($url.'/geo', ['enabled' => true])->assertStatus(419);
        $admin->update(['must_change_password' => true]);
        $this->assertContains($this->getJson($url)->status(), [403, 428]);
        $admin->update(['must_change_password' => false]);
        $this->withSession(['admin_two_factor_verified_id' => null]);
        $this->assertContains($this->getJson($url)->status(), [403, 428]);
        $this->withSession(['admin_two_factor_verified_id' => $admin->id, 'admin_auth_version' => 0]);
        $this->getJson($url)->assertUnauthorized();
        $this->assertDatabaseCount('blog_site_settings', 0);
    }

    private function signIn(string $role): Admin
    {
        $admin = Admin::create(['name' => 'Isolated blog editor', 'email' => Str::uuid().'@example.test',
            'password' => bcrypt('Isolated!Password123'), 'role' => $role, 'auth_version' => 1, 'must_change_password' => false]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id]);
        return $admin;
    }
}

class BlogAutomationEnforcedCsrf extends VerifyCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
