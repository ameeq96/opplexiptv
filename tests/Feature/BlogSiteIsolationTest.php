<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogGenerationRun;
use App\Services\Clients\LandingInteractionService;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BlogSiteIsolationTest extends TestCase
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
        $this->mock(TmdbService::class, fn ($mock) => $mock->shouldReceive('configured')->andReturn(false));
    }

    public function test_main_blog_lists_detail_fallback_and_related_do_not_expose_other_sites(): void
    {
        $main = $this->createPost('main', 'main-isolated-guide');
        $geo = $this->createPost('geo', 'geo-isolated-guide');
        $geo->update(['is_featured' => true]);
        $this->getJson('/api/v1/site/blogs?locale=en')->assertOk()
            ->assertJsonCount(1, 'data.articles')->assertJsonPath('data.articles.0.id', $main->id)
            ->assertJsonPath('data.featured', null);
        $this->getJson('/api/v1/site/blogs/main-isolated-guide?locale=en')->assertOk()
            ->assertJsonPath('data.article.id', $main->id)->assertJsonCount(0, 'data.related');
        $this->getJson('/api/v1/site/blogs/geo-isolated-guide?locale=en')->assertNotFound();
        $this->getJson('/api/v1/site/blogs/geo-isolated-guide?locale=it')->assertNotFound();
        $this->followingRedirects()->get('/blogs/geo-isolated-guide')->assertNotFound();
    }

    public function test_main_sitemap_keeps_landing_posts_out_without_writing_a_real_file(): void
    {
        $this->createPost('main', 'main-sitemap-guide');
        $this->createPost('geo', 'geo-sitemap-guide');
        $xml = '';
        File::shouldReceive('put')->once()->with(public_path('sitemap.xml'), \Mockery::on(function ($value) use (&$xml) {
            $xml = $value;
            return is_string($value);
        }))->andReturn(1);
        $this->artisan('sitemap:generate')->assertSuccessful();
        $this->assertStringContainsString('/blogs/main-sitemap-guide', $xml);
        $this->assertStringNotContainsString('geo-sitemap-guide', $xml);
    }

    public function test_landing_feed_and_article_are_scoped_and_keep_draft_future_and_archived_posts_private(): void
    {
        $geo = $this->createPost('geo', 'geo-published-guide');
        $this->publishedRun($geo);
        $other = $this->createPost('getiptv', 'getiptv-published-guide');
        $this->publishedRun($other);
        $draft = $this->createPost('geo', 'geo-draft-guide', 'draft');
        $this->publishedRun($draft, 'ready');
        $future = $this->createPost('geo', 'geo-future-guide');
        $this->publishedRun($future, 'published', now()->addDay());
        $archived = $this->createPost('geo', 'geo-archived-guide', 'archived');
        $this->publishedRun($archived);

        $feed = $this->getJson('/api/v1/site/brands/geo/blogs')->assertOk()
            ->assertJsonPath('data.site_key', 'geo')->assertJsonCount(1, 'data.posts')
            ->assertJsonPath('data.posts.0.id', $geo->id)->assertJsonPath('data.pagination.total', 1);
        $this->getJson('/api/v1/site/brands/geo/blogs/geo-published-guide?locale=it')->assertOk()
            ->assertJsonPath('data.locale', 'it')->assertJsonPath('data.post.slug', 'geo-published-guide');
        foreach (['getiptv-published-guide', 'geo-draft-guide', 'geo-future-guide', 'geo-archived-guide'] as $slug) {
            $this->getJson('/api/v1/site/brands/geo/blogs/'.$slug)->assertNotFound();
        }
        $this->getJson('/api/v1/site/brands/geo/blogs?revision='.str_repeat('0', 64))->assertStatus(409);
        $this->getJson('/api/v1/site/brands/geo/blogs?revision='.$feed->json('data.revision'))->assertOk();
        $this->getJson('/api/v1/site/brands/main/blogs')->assertNotFound();
        $this->getJson('/api/v1/site/brands/geo/blogs?per_page=26')->assertUnprocessable();
        $this->getJson('/api/v1/site/brands/geo/blogs/geo-published-guide?locale=xx')->assertUnprocessable();
    }

    public function test_cms_page_measurement_accepts_only_real_brand_articles_and_archive_pages(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            $this->publishedRun($this->createPost('geo', 'geo-guide-'.$i));
        }
        $service = app(LandingInteractionService::class);
        $this->assertTrue($service->validPage('geo', 'setup'));
        $this->assertTrue($service->validPage('geo', 'blogs/geo-guide-1', 'it'));
        $this->assertTrue($service->validPage('geo', 'blogs/page/2', 'it'));
        $this->assertFalse($service->validPage('geo', 'blogs/page/3'));
        $this->assertFalse($service->validPage('geo', 'blogs/page/1'));
        $this->assertFalse($service->validPage('getiptv', 'blogs/geo-guide-1'));
        $this->assertFalse($service->validPage('geo', 'blogs/not-a-real-post'));
    }

    private function createPost(string $site, string $slug, string $status = 'published'): Blog
    {
        $blog = Blog::create(['site_key' => $site, 'status' => $status, 'published_at' => now()->subHour()]);
        $blog->translations()->create(['locale' => 'en', 'slug' => $slug, 'title' => 'Isolated '.$slug,
            'excerpt' => 'A private fixture for isolated blog routing assertions.', 'content' => '<p>Isolated blog content.</p>']);
        return $blog;
    }

    private function publishedRun(Blog $blog, string $status = 'published', mixed $publishedAt = null): BlogGenerationRun
    {
        $slug = $blog->translations()->first()->slug;
        $revision = hash('sha256', $slug);
        $translations = [];
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $translations[$locale] = ['title' => 'Isolated '.$locale.' article', 'excerpt' => 'Isolated article summary for API routing.',
                'seo_title' => 'Isolated '.$locale.' article', 'seo_description' => 'An isolated description for public blog feed assertions.',
                'sections' => [['heading' => 'Isolated checks', 'paragraphs' => ['This fixture is never published to a real website.'], 'items' => [], 'links' => []]]];
        }
        $post = ['id' => $blog->id, 'slug' => $slug, 'revision' => $revision,
            'published_at' => ($publishedAt ?? now()->subHour())->toIso8601String(), 'updated_at' => now()->subHour()->toIso8601String(),
            'author' => config('blog-publishing.publisher'), 'image' => 'https://'.config('blog-publishing.sites.'.$blog->site_key.'.domain').'/share-card.png',
            'translations' => $translations];
        return BlogGenerationRun::create(['site_key' => $blog->site_key, 'blog_id' => $blog->id, 'topic' => 'Isolated blog fixture',
            'status' => $status, 'revision' => $revision, 'payload' => ['post' => $post], 'published_at' => $publishedAt ?? now()->subHour()]);
    }
}
