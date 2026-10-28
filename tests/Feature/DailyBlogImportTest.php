<?php

namespace Tests\Feature;

use App\Console\Commands\BlogPublishing;
use App\Models\Blog;
use App\Models\BlogGenerationRun;
use App\Models\BlogSiteSetting;
use App\Models\User;
use App\Services\Blogs\BlogContentService;
use App\Services\Blogs\BlogPublicationService;
use App\Services\Blogs\BlogQualityService;
use App\Services\TmdbService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;
use Throwable;

class DailyBlogImportTest extends TestCase
{
    use RefreshDatabase;

    private const DAY = '2026-10-28';

    private ?array $routingEnvironment = null;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
    }

    protected function setUp(): void
    {
        if ($this->name() === 'test_main_article_renders_the_actual_canonical_title_and_heading_for_all_languages') {
            $this->routingEnvironment = ['process' => getenv('ROUTING_LOCALE'),
                'env_exists' => array_key_exists('ROUTING_LOCALE', $_ENV), 'env' => $_ENV['ROUTING_LOCALE'] ?? null,
                'server_exists' => array_key_exists('ROUTING_LOCALE', $_SERVER), 'server' => $_SERVER['ROUTING_LOCALE'] ?? null];
            $locale = $this->providedData()[0];
            $forced = $locale === 'en' ? '' : $locale;
            putenv('ROUTING_LOCALE='.$forced); $_ENV['ROUTING_LOCALE'] = $forced; $_SERVER['ROUTING_LOCALE'] = $forced;
        }
        parent::setUp();
        Carbon::setTestNow('2026-10-27T19:05:00+00:00');
        CarbonImmutable::setTestNow('2026-10-27T19:05:00+00:00');
        Http::preventStrayRequests(); Bus::fake(); Mail::fake(); Notification::fake(); Cache::flush();
        $this->mock(TmdbService::class, fn ($mock) => $mock->shouldReceive('configured')->andReturn(false));
    }

    protected function tearDown(): void
    {
        try {
            Http::assertNothingSent(); Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
        } finally {
            Carbon::setTestNow(); CarbonImmutable::setTestNow();
            try {
                parent::tearDown();
            } finally {
                if ($this->routingEnvironment !== null) {
                    $previous = $this->routingEnvironment;
                    putenv($previous['process'] === false ? 'ROUTING_LOCALE' : 'ROUTING_LOCALE='.$previous['process']);
                    if ($previous['env_exists']) $_ENV['ROUTING_LOCALE'] = $previous['env']; else unset($_ENV['ROUTING_LOCALE']);
                    if ($previous['server_exists']) $_SERVER['ROUTING_LOCALE'] = $previous['server']; else unset($_SERVER['ROUTING_LOCALE']);
                }
            }
        }
    }

    public function test_setup_creates_seven_disabled_preferences_without_replacing_existing_records(): void
    {
        $legacy = Blog::create(['status' => 'published', 'published_at' => now()->subDay()]);
        $legacy->translations()->create(['locale' => 'en', 'title' => 'Existing isolated main article', 'slug' => 'existing-isolated-main', 'content' => '<p>Existing isolated content.</p>']);
        $before = $legacy->fresh()->toArray();
        $service = app(BlogPublicationService::class);
        $service->initializeSettings();
        $this->assertDatabaseCount('blog_site_settings', 7);
        $this->assertSame(0, BlogSiteSetting::where('enabled', true)->count());
        foreach (BlogSiteSetting::all() as $setting) {
            $this->assertSame('Asia/Karachi', $setting->timezone);
            $this->assertSame('10:00', $setting->publish_time);
            $this->assertSame(config('blog-publishing.supported_locales'), $setting->locales);
        }
        $geo = BlogSiteSetting::where('site_key', 'geo')->firstOrFail();
        $geo->update(['enabled' => true, 'publish_time' => '12:34']);
        $service->initializeSettings();
        $this->assertDatabaseCount('blog_site_settings', 7);
        $this->assertTrue($geo->fresh()->enabled);
        $this->assertSame('12:34', $geo->fresh()->publish_time);
        $this->assertSame($before, $legacy->fresh()->toArray());
        $this->assertDatabaseCount('blog_generation_runs', 0);
    }

    public function test_complete_import_is_private_idempotent_and_rejects_a_second_daily_article(): void
    {
        $service = $this->enabledService('geo');
        $reserved = $service->reserve('geo', self::DAY);
        $payload = $this->payload('geo', 'alpha');
        $run = $service->import('geo', self::DAY, $payload);
        $this->assertSame($reserved->id, $run->id);
        $this->assertSame('ready', $run->status);
        $this->assertSame('draft', $run->blog->status);
        $this->assertNull($run->blog->author_id);
        $this->assertSame(10, $run->blog->translations()->count());
        $this->assertSame(4, $run->deliveries()->where('status', 'pending')->count());
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        $this->getJson('/api/v1/site/brands/geo/blogs/'.$payload['slug'])->assertNotFound();

        $again = $service->import('geo', self::DAY, $payload);
        $this->assertSame($run->id, $again->id);
        $this->assertSame($run->fingerprint, $again->fingerprint);
        $this->assertSame(1, $again->attempts);
        $this->assertDatabaseCount('blogs', 1);
        $this->assertDatabaseCount('blog_generation_runs', 1);
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $this->payload('geo', 'beta')), 409);
        $this->assertDatabaseCount('blogs', 1);
    }

    public function test_preflight_reads_existing_settings_without_initializing_or_mutating_records(): void
    {
        $payload = $this->payload();
        $raw = "\xEF\xBB\xBF  ".json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)."\r\n";
        [$exit, $result] = $this->command('preflight', ['--site' => 'geo', '--date' => self::DAY], $raw);
        $this->assertSame(1, $exit);
        $this->assertSame('publishing_http_422', $result['error_code']);
        $this->assertDatabaseCount('blog_site_settings', 0);
        BlogSiteSetting::create(['site_key' => 'geo', 'enabled' => true, 'timezone' => 'Asia/Karachi',
            'publish_time' => '10:00', 'locales' => config('blog-publishing.supported_locales')]);
        DB::enableQueryLog();
        try {
            [$exit, $result] = $this->command('preflight', ['--site' => 'geo', '--date' => self::DAY], $raw);
            $writes = array_filter(DB::getQueryLog(), fn ($query) => preg_match('/^\s*(?:insert|update|delete|replace|create|alter|drop)\b/i', $query['query']));
            $this->assertSame([], array_values($writes));
        } finally {
            DB::disableQueryLog();
        }
        $normalized = app(BlogQualityService::class)->normalize($payload, 'geo');
        $this->assertSame(0, $exit);
        $this->assertSame(['valid' => true, 'site_key' => 'geo', 'publishing_date' => self::DAY, 'slug' => $payload['slug'],
            'fingerprint' => hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)),
            'source_sha256' => hash('sha256', $raw)], $result['data']);
        $this->assertDatabaseCount('blog_site_settings', 1);
        $this->assertDatabaseCount('blog_generation_runs', 0);
        $this->assertDatabaseCount('blogs', 0);
        $this->assertDatabaseCount('blog_publish_deliveries', 0);
    }

    public function test_preflight_requires_an_enabled_current_day_and_complete_dated_payload(): void
    {
        $service = $this->enabledService('geo');
        $raw = json_encode($this->payload(), JSON_THROW_ON_ERROR);
        foreach (['', '2026-10-27', '2026-10-29', '2026-02-30'] as $date) {
            [$exit, $result] = $this->command('preflight', ['--site' => 'geo', '--date' => $date], $raw);
            $this->assertSame(1, $exit);
            $this->assertSame('validation_failed', $result['error_code']);
        }
        foreach (['missing-date', 'wrong-date', 'missing-language', 'raw-html'] as $fault) {
            $input = $this->payload();
            if ($fault === 'missing-date') unset($input['publishing_date']);
            if ($fault === 'wrong-date') $input['publishing_date'] = '2026-10-27';
            if ($fault === 'missing-language') unset($input['translations']['ar']);
            if ($fault === 'raw-html') $input['translations']['en']['sections'][0]['paragraphs'][0] .= ' <b>invalid HTML</b>';
            [$exit, $result] = $this->command('preflight', ['--site' => 'geo', '--date' => self::DAY], json_encode($input, JSON_THROW_ON_ERROR));
            $this->assertSame(1, $exit);
            $this->assertSame('validation_failed', $result['error_code']);
        }
        BlogSiteSetting::where('site_key', 'geo')->update(['enabled' => false]);
        $this->assertHttpRejected(fn () => $service->preflight('geo', self::DAY, $this->payload(), hash('sha256', $raw)), 422);
        $this->assertDatabaseCount('blog_generation_runs', 0);
        $this->assertDatabaseCount('blogs', 0);
        $this->assertDatabaseCount('blog_translations', 0);
    }

    public function test_preflight_checks_history_slugs_and_existing_daily_fingerprint_without_writes(): void
    {
        $service = $this->enabledService('geo', 'getiptv');
        $input = $this->payload();
        $run = $service->import('geo', self::DAY, $input);
        $before = $run->fresh()->getAttributes();
        $same = $service->preflight('geo', self::DAY, $input, hash('sha256', json_encode($input)));
        $this->assertSame($run->fingerprint, $same['fingerprint']);
        $this->assertSame($before, $run->fresh()->getAttributes());
        $different = $this->payload('geo', 'different-daily-source');
        $this->assertHttpRejected(fn () => $service->preflight('geo', self::DAY, $different, hash('sha256', json_encode($different))), 409);
        foreach (['topic', 'body'] as $fault) {
            $duplicate = $this->payload('getiptv', $fault === 'body' ? 'alpha' : 'distinct-body');
            if ($fault === 'topic') $duplicate['topic'] = strtoupper($input['topic']);
            $this->assertValidationRejected(fn () => $service->preflight('getiptv', self::DAY, $duplicate, hash('sha256', json_encode($duplicate))), 'originality');
        }
        $this->assertSame($before, $run->fresh()->getAttributes());
        $this->assertDatabaseCount('blog_generation_runs', 1);
        $this->assertDatabaseCount('blogs', 1);
        $this->assertDatabaseCount('blog_translations', 10);
    }

    public function test_preflight_rejects_an_existing_immutable_slug_without_reserving_a_run(): void
    {
        $service = $this->enabledService('geo');
        $input = $this->payload();
        $existing = Blog::create(['site_key' => 'geo', 'status' => 'draft']);
        $existing->translations()->create(['locale' => 'en', 'title' => 'Existing isolated slug fixture', 'slug' => $input['slug'], 'content' => '<p>Existing fixture.</p>']);
        $this->assertHttpRejected(fn () => $service->preflight('geo', self::DAY, $input, hash('sha256', json_encode($input))), 422);
        $this->assertDatabaseCount('blogs', 1);
        $this->assertDatabaseCount('blog_generation_runs', 0);
    }

    public function test_git_import_persists_exact_source_provenance_and_is_idempotent(): void
    {
        $this->enabledService('geo');
        $input = $this->payload();
        $raw = "\xEF\xBB\xBF".json_encode($input, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)."\r\n";
        $source = $this->gitSource($input, $raw);
        $options = ['--site' => 'geo', '--date' => self::DAY, '--source-commit' => $source['commit'],
            '--source-sha256' => $source['sha256'], '--source-fingerprint' => $source['fingerprint']];
        [$exit, $result] = $this->command('import', $options, $raw);
        $this->assertSame(0, $exit);
        $this->assertSame($source, $result['data']['run']['git_source']);
        $run = BlogGenerationRun::firstOrFail();
        $this->assertSame($source, $run->payload['git_source']);
        $this->assertSame('draft', $run->blog->status);
        $before = $run->getAttributes();
        [$exit, $result] = $this->command('import', $options, $raw);
        $this->assertSame(0, $exit);
        $this->assertSame($run->id, $result['data']['run']['id']);
        $this->assertSame($before, $run->fresh()->getAttributes());
        [$exit, $result, $status] = $this->command('status', ['--site' => 'geo', '--run' => $run->id]);
        $this->assertSame(0, $exit);
        $this->assertSame($source, $result['data']['run']['git_source']);
        $this->assertStringNotContainsString('deliberately synthetic English test text', $status);
        $this->assertDatabaseCount('blogs', 1);
        $this->assertDatabaseCount('blog_translations', 10);
        $this->assertDatabaseCount('blog_generation_runs', 1);
    }

    public function test_git_import_mismatched_raw_hash_or_fingerprint_never_creates_records(): void
    {
        $service = $this->enabledService('geo');
        $input = $this->payload();
        $raw = json_encode($input, JSON_THROW_ON_ERROR);
        $source = $this->gitSource($input, $raw);
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $source, hash('sha256', $raw."\n")), 409);
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $source), 409);
        $wrongFingerprint = $source; $wrongFingerprint['fingerprint'] = str_repeat('0', 64);
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $wrongFingerprint, hash('sha256', $raw)), 409);
        $wrongPath = $source; $wrongPath['path'] = 'resources/content/blog-publications/getiptv/'.self::DAY.'.json';
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $wrongPath, hash('sha256', $raw)), 422);
        $wrongCase = $source; $wrongCase['commit'] = str_repeat('A', 40);
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $wrongCase, hash('sha256', $raw)), 422);
        [$exit, $result] = $this->command('import', ['--site' => 'geo', '--date' => self::DAY,
            '--source-commit' => $source['commit'], '--source-sha256' => $source['sha256'], '--source-fingerprint' => $source['fingerprint']], $raw."\n");
        $this->assertSame(1, $exit);
        $this->assertSame('publishing_http_409', $result['error_code']);
        $this->assertDatabaseCount('blog_generation_runs', 0);
        $this->assertDatabaseCount('blogs', 0);
        $this->assertDatabaseCount('blog_translations', 0);
        $this->assertDatabaseCount('blog_publish_deliveries', 0);
    }

    public function test_git_import_cannot_replace_valid_source_or_attach_origin_to_a_non_git_article(): void
    {
        $service = $this->enabledService('geo');
        $input = $this->payload();
        $raw = json_encode($input, JSON_THROW_ON_ERROR);
        $source = $this->gitSource($input, $raw);
        $run = $service->import('geo', self::DAY, $input, $source, hash('sha256', $raw));
        $before = $run->fresh()->getAttributes();
        $anotherCommit = $source; $anotherCommit['commit'] = str_repeat('b', 64);
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $anotherCommit, hash('sha256', $raw)), 409);
        $otherRaw = $source; $otherRaw['sha256'] = hash('sha256', $raw."\n");
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $otherRaw, hash('sha256', $raw."\n")), 409);
        $this->assertSame($before, $run->fresh()->getAttributes());
        $payload = $run->payload; unset($payload['git_source']);
        $run->update(['payload' => $payload, 'status' => 'published', 'published_at' => now()]);
        $legacy = $run->fresh()->getAttributes();
        $this->assertHttpRejected(fn () => $service->import('geo', self::DAY, $input, $source, hash('sha256', $raw)), 409);
        $this->assertSame($legacy, $run->fresh()->getAttributes());
        $this->assertSame($run->id, $service->import('geo', self::DAY, $input)->id);
        $this->assertArrayNotHasKey('git_source', $run->fresh()->payload);
        $this->assertDatabaseCount('blogs', 1);
        $this->assertDatabaseCount('blog_translations', 10);
    }

    public function test_source_flags_are_all_or_none_and_rejected_on_unsupported_actions_before_setup(): void
    {
        $valid = ['--source-commit' => str_repeat('a', 40), '--source-sha256' => str_repeat('b', 64), '--source-fingerprint' => str_repeat('c', 64)];
        foreach ([['import', ['--source-commit' => str_repeat('a', 40)]], ['activate', ['--source-sha256' => str_repeat('b', 64)]],
            ['import', array_replace($valid, ['--source-commit' => str_repeat('A', 40)])],
            ['import', array_replace($valid, ['--source-sha256' => 'invalid'])],
            ['import', array_replace($valid, ['--source-fingerprint' => ''])],
            ['setup', $valid], ['sites', $valid], ['status', $valid], ['validate', $valid], ['preflight', $valid], ['snapshot', $valid], ['notify', $valid]] as [$action, $flags]) {
            [$exit, $result] = $this->command($action, ['--site' => 'geo', '--date' => self::DAY] + $flags, json_encode($this->payload()));
            $this->assertSame(1, $exit, $action);
            $this->assertSame('publishing_http_422', $result['error_code'], $action);
        }
        $this->assertDatabaseCount('blog_site_settings', 0);
        $this->assertDatabaseCount('blog_generation_runs', 0);
        $this->assertDatabaseCount('blogs', 0);
        $input = $this->payload();
        $raw = json_encode($input, JSON_THROW_ON_ERROR);
        $source = $this->gitSource($input, $raw);
        foreach (['import', 'activate'] as $action) {
            [$exit, $result] = $this->command($action, ['--site' => 'geo', '--date' => self::DAY,
                '--source-commit' => $source['commit'], '--source-sha256' => $source['sha256'], '--source-fingerprint' => $source['fingerprint']], $raw);
            $this->assertSame(1, $exit);
            $this->assertFalse($result['ok']);
        }
        $this->assertDatabaseCount('blog_site_settings', 0);
        $this->assertDatabaseCount('blog_generation_runs', 0);
    }

    public function test_a_topic_cannot_be_republished_under_a_different_brand(): void
    {
        $service = $this->enabledService('geo', 'getiptv');
        $first = $this->payload('geo', 'original-topic');
        $service->import('geo', self::DAY, $first);
        $second = $this->payload('getiptv', 'different-body');
        $second['topic'] = strtoupper($first['topic']);
        $this->assertValidationRejected(fn () => $service->import('getiptv', self::DAY, $second), 'originality');
        $this->assertDatabaseCount('blogs', 1);
    }

    public function test_replacing_brand_names_does_not_make_a_duplicate_body_original(): void
    {
        $service = $this->enabledService('geo', 'getiptv');
        $service->import('geo', self::DAY, $this->payload('geo', 'same-body'));
        $second = $this->payload('getiptv', 'same-body');
        $second['topic'] = 'A completely different topic label for a repeated fixture';
        $this->assertValidationRejected(fn () => $service->import('getiptv', self::DAY, $second), 'originality');
        $this->assertDatabaseCount('blogs', 1);
    }

    public function test_duplicate_failures_leave_the_reserved_run_private_with_editorial_errors(): void
    {
        $service = $this->enabledService('geo', 'getiptv');
        $original = $this->payload('geo', 'reserved-duplicate');
        $service->import('geo', self::DAY, $original);
        $reserved = $service->reserve('getiptv', self::DAY);
        foreach (['topic', 'body'] as $fault) {
            $rejected = $this->payload('getiptv', 'reserved-duplicate');
            $rejected['topic'] = $fault === 'topic' ? $original['topic'] : 'Another topic label for the same rejected mock body';
            $this->assertValidationRejected(fn () => $service->import('getiptv', self::DAY, $rejected), 'originality');
            $failed = $reserved->fresh();
            $this->assertSame('draft', $failed->status);
            $this->assertNull($failed->blog_id);
            $this->assertNull($failed->payload);
            $this->assertNull($failed->fingerprint);
            $this->assertNull($failed->revision);
            $this->assertNotEmpty($failed->errors);
            $this->assertNotSame($rejected['topic'], $failed->topic);
        }
        [$exit, $result] = $this->command('import', ['--site' => 'getiptv', '--date' => self::DAY], json_encode($rejected, JSON_THROW_ON_ERROR));
        $this->assertSame(1, $exit);
        $this->assertSame('validation_failed', $result['error_code']);
        $this->assertArrayHasKey('originality', $result['errors']);
        $this->assertSame(3, $reserved->fresh()->attempts);
        $this->assertDatabaseCount('blog_generation_runs', 2);
        $this->assertDatabaseCount('blogs', 1);
        $this->assertDatabaseCount('blog_translations', 10);
        $this->assertDatabaseCount('blog_publish_deliveries', 4);
        $this->assertSame([], app(BlogContentService::class)->snapshot('getiptv')['posts']);
    }

    public function test_rejected_imports_never_replace_an_existing_valid_run_or_its_article(): void
    {
        $service = $this->enabledService('geo');
        $run = $service->import('geo', self::DAY, $this->payload('geo', 'valid-preserved'));
        $beforeRun = $run->fresh()->getAttributes();
        $beforeBlog = $run->blog->fresh()->getAttributes();
        $beforeTranslations = $run->blog->translations()->orderBy('id')->get()->map->getAttributes()->all();
        $beforeDeliveries = $run->deliveries()->orderBy('id')->get()->map->getAttributes()->all();
        $rejected = $this->payload('geo', 'different-invalid-input');
        unset($rejected['translations']['hi']);
        $this->assertValidationRejected(fn () => $service->import('geo', self::DAY, $rejected));
        $this->assertSame($beforeRun, $run->fresh()->getAttributes());
        $this->assertSame($beforeBlog, $run->blog->fresh()->getAttributes());
        $this->assertSame($beforeTranslations, $run->blog->translations()->orderBy('id')->get()->map->getAttributes()->all());
        $this->assertSame($beforeDeliveries, $run->deliveries()->orderBy('id')->get()->map->getAttributes()->all());

        $run->blog->delete();
        $historicalRun = $run->fresh()->getAttributes();
        $this->assertNull($run->fresh()->blog_id);
        $this->assertValidationRejected(fn () => $service->import('geo', self::DAY, $rejected));
        $this->assertSame($historicalRun, $run->fresh()->getAttributes());
    }

    public static function invalidArticles(): array
    {
        return array_combine([
            'missing-language', 'verbatim-translations', 'raw-html', 'external-host', 'relative-url', 'credential-url',
            'private-account-link', 'sections-dictionary', 'paragraphs-dictionary', 'items-dictionary',
            'links-dictionary', 'sources-dictionary', 'translation-list', 'unsupported-claim', 'unverified-filex-allowance',
            'keyword-stuffing', 'wrong-site', 'unsupported-field', 'empty-section', 'missing-paragraphs',
            'scalar-translation', 'scalar-section', 'scalar-reference',
        ], array_map(fn ($fault) => [$fault], [
            'missing-language', 'verbatim-translations', 'raw-html', 'external-host', 'relative-url', 'credential-url',
            'private-account-link', 'sections-dictionary', 'paragraphs-dictionary', 'items-dictionary',
            'links-dictionary', 'sources-dictionary', 'translation-list', 'unsupported-claim', 'unverified-filex-allowance',
            'keyword-stuffing', 'wrong-site', 'unsupported-field', 'empty-section', 'missing-paragraphs',
            'scalar-translation', 'scalar-section', 'scalar-reference',
        ]));
    }

    #[DataProvider('invalidArticles')]
    public function test_invalid_or_unsubstantiated_articles_remain_private_with_editorial_errors(string $fault): void
    {
        $input = $this->payload('geo', 'invalid-'.$fault);
        switch ($fault) {
            case 'missing-language': unset($input['translations']['hi']); break;
            case 'verbatim-translations': $input['translations']['it'] = $input['translations']['en']; break;
            case 'raw-html': $input['translations']['en']['sections'][0]['paragraphs'][0] .= ' <script>doNotExecute()</script>'; break;
            case 'external-host': $input['sources'][0]['url'] = 'https://unapproved.example.test/reference'; break;
            case 'relative-url': $input['sources'][0]['url'] = '/private-reference'; break;
            case 'credential-url': $input['sources'][0]['url'] = 'https://name:password@support.google.com/reference'; break;
            case 'private-account-link': $input['sources'][0]['url'] = 'https://opplexiptv.com/account'; break;
            case 'sections-dictionary': $input['translations']['en']['sections'] = ['first' => $input['translations']['en']['sections'][0], 'second' => $input['translations']['en']['sections'][1]]; break;
            case 'paragraphs-dictionary': $input['translations']['en']['sections'][0]['paragraphs'] = ['named' => $input['translations']['en']['sections'][0]['paragraphs'][0]]; break;
            case 'items-dictionary': $input['translations']['en']['sections'][0]['items'] = ['named' => 'A synthetic checklist item for an isolated test.']; break;
            case 'links-dictionary': $input['translations']['en']['sections'][0]['links'] = ['named' => $input['sources'][0]]; break;
            case 'sources-dictionary': $input['sources'] = ['named' => $input['sources'][0]]; break;
            case 'translation-list': $input['translations'] = array_values($input['translations']); break;
            case 'unsupported-claim': $input['translations']['en']['sections'][0]['paragraphs'][0] .= ' We tested every service and our tests prove perfect results.'; break;
            case 'unverified-filex-allowance': $input['translations']['en']['sections'][0]['paragraphs'][0] .= ' Filex includes two simultaneous connections.'; break;
            case 'keyword-stuffing': $input['translations']['en']['sections'][0]['paragraphs'][0] .= ' '.str_repeat('Geo IPTV ', 30); break;
            case 'wrong-site': $input['site_key'] = 'trex'; break;
            case 'unsupported-field': $input['send_email'] = true; break;
            case 'empty-section': $input['translations']['en']['sections'][0]['paragraphs'] = []; $input['translations']['en']['sections'][0]['items'] = []; break;
            case 'missing-paragraphs': unset($input['translations']['en']['sections'][0]['paragraphs']); break;
            case 'scalar-translation': $input['translations']['en'] = 'not a translation object'; break;
            case 'scalar-section': $input['translations']['en']['sections'][0] = 'not a section object'; break;
            case 'scalar-reference': $input['sources'][0] = 'https://support.google.com/reference'; break;
        }
        $this->assertValidationRejected(fn () => app(BlogPublicationService::class)->import('geo', self::DAY, $input));
        $this->assertDatabaseCount('blogs', 0);
        $this->assertDatabaseCount('blog_generation_runs', 1);
        $failed = BlogGenerationRun::firstOrFail();
        $this->assertSame('draft', $failed->status);
        $this->assertNull($failed->blog_id);
        $this->assertNull($failed->payload);
        $this->assertNotEmpty($failed->errors);
        $this->assertDatabaseCount('blog_translations', 0);
    }

    public function test_publication_day_is_bound_to_pakistan_midnight_and_payload_date(): void
    {
        $service = $this->enabledService('geo');
        $this->assertSame('2026-10-27', now('UTC')->toDateString());
        $this->assertSame(self::DAY, now('Asia/Karachi')->toDateString());
        foreach (['2026-10-27', '2026-10-29', '2026-02-30', 'not-a-date'] as $date) {
            $this->assertValidationRejected(fn () => $service->reserve('geo', $date), 'publishing_date');
            $this->assertValidationRejected(fn () => $service->import('geo', $date, $this->payload()), 'publishing_date');
        }
        $mismatch = $this->payload(); $mismatch['publishing_date'] = '2026-10-27';
        $this->assertValidationRejected(fn () => $service->import('geo', self::DAY, $mismatch), 'publishing_date');
        $run = $service->import('geo', self::DAY, $this->payload());
        $this->assertSame(self::DAY, $run->publishing_date->toDateString());
    }

    public function test_paused_sites_can_hold_drafts_but_cannot_reserve_or_activate(): void
    {
        $service = app(BlogPublicationService::class);
        $this->assertHttpRejected(fn () => $service->reserve('geo', self::DAY), 422);
        $run = $service->import('geo', self::DAY, $this->payload());
        $this->assertSame('blocked', $run->status);
        $this->assertSame(['publishing_paused'], $run->errors);
        $this->assertSame('draft', $run->blog->status);
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $this->assertHttpRejected(fn () => $service->activate($run, $run->revision, $snapshot['revision']), 422);
        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
    }

    public function test_cli_setup_sites_and_status_emit_safe_raw_json(): void
    {
        User::factory()->create(['email' => 'private-customer@example.test', 'notes' => 'PRIVATE CUSTOMER CONTENT']);
        $legacy = Blog::create(['status' => 'draft']);
        $legacy->translations()->create(['locale' => 'en', 'title' => 'Private legacy draft', 'slug' => 'private-legacy-draft', 'content' => '<p>PRIVATE LEGACY BODY</p>']);
        foreach (['setup', 'sites'] as $action) {
            [$exit, $result, $raw] = $this->command($action);
            $this->assertSame(0, $exit);
            $this->assertTrue($result['ok']);
            $this->assertCount(7, $result['data']['sites']);
            $this->assertFalse($result['data']['paid_generation_api']);
            $this->assertStringNotContainsString('PRIVATE', $raw);
        }
        $run = BlogGenerationRun::create(['site_key' => 'geo', 'publishing_date' => self::DAY,
            'topic' => 'LOCAL TEST <info>literal output</info>', 'status' => 'reserved', 'payload' => ['do_not_show' => 'PRIVATE IMPORT PAYLOAD']]);
        [$exit, $result, $raw] = $this->command('status', ['--site' => 'geo', '--run' => $run->id]);
        $this->assertSame(0, $exit);
        $this->assertSame($run->topic, $result['data']['run']['topic']);
        $this->assertArrayNotHasKey('payload', $result['data']['run']);
        foreach (['PRIVATE', 'private-customer@example.test', 'blog-google-service-account.json', 'private_key'] as $secret) {
            $this->assertStringNotContainsString($secret, $raw);
        }
        $this->assertStringStartsWith('{', $raw);
        $this->assertStringEndsWith('}'.PHP_EOL, $raw);
    }

    public function test_cli_accepts_json_only_from_stdin_and_rejects_wrong_site_or_run_arguments(): void
    {
        $payload = $this->payload();
        [$exit, $result] = $this->command('validate', ['--site' => 'geo'], "\xEF\xBB\xBF".json_encode($payload, JSON_THROW_ON_ERROR));
        $this->assertSame(0, $exit);
        $this->assertTrue($result['data']['valid']);
        [$exit, $result] = $this->command('validate', ['--site' => 'geo', '--payload' => 'C:/not-a-readable-payload.json'], json_encode($payload));
        $this->assertSame(1, $exit);
        $this->assertSame('publishing_http_422', $result['error_code']);
        foreach (['[]', '{invalid json', str_repeat('x', 2 * 1024 * 1024 + 1)] as $invalidJson) {
            [$exit, $result] = $this->command('validate', ['--site' => 'geo'], $invalidJson);
            $this->assertSame(1, $exit);
            $this->assertFalse($result['ok']);
        }
        [$exit, $result] = $this->command('validate', ['--site' => 'getiptv'], json_encode($payload));
        $this->assertSame(1, $exit);
        $this->assertSame('validation_failed', $result['error_code']);
        $run = BlogGenerationRun::create(['site_key' => 'geo', 'topic' => 'Isolated argument binding', 'status' => 'reserved']);
        foreach ([['--site' => 'getiptv', '--run' => $run->id], ['--site' => 'geo', '--run' => '0'], ['--site' => 'geo', '--run' => '../1']] as $options) {
            [$exit, $result] = $this->command('retry', $options);
            $this->assertSame(1, $exit);
            $this->assertFalse($result['ok']);
        }
        $this->assertSame('reserved', $run->fresh()->status);
        $this->assertDatabaseCount('blogs', 0);
    }

    public static function articleLocales(): array
    {
        $locales = ['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];
        return array_combine($locales, array_map(fn ($locale) => [$locale], $locales));
    }

    #[DataProvider('articleLocales')]
    public function test_main_article_renders_the_actual_canonical_title_and_heading_for_all_languages(string $locale): void
    {
        $input = $this->payload('main', 'real-blade');
        $run = app(BlogPublicationService::class)->import('main', self::DAY, $input);
        $run->blog->update(['status' => 'published']);
        $content = app(BlogContentService::class);
        $canonical = $content->canonical('main', $locale, $input['slug']);
        $response = $this->followingRedirects()->get($canonical);
        $this->assertSame(200, $response->status(), $locale.' real Blade route '.$canonical.'; selected locale '.app()->getLocale());
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET | LIBXML_HTML_NODEFDTD);
        } finally {
            libxml_clear_errors(); libxml_use_internal_errors($previous);
        }
        $canonicals = [];
        foreach ($document->getElementsByTagName('link') as $link) {
            if ($link->getAttribute('rel') === 'canonical') $canonicals[] = $link->getAttribute('href');
        }
        $this->assertSame([$canonical], $canonicals, $locale.' canonical');
        $this->assertSame($input['translations'][$locale]['seo_title'], trim($document->getElementsByTagName('title')->item(0)->textContent), $locale.' actual Blade title');
        $this->assertSame(1, $document->getElementsByTagName('h1')->length, $locale.' one visible title');
        $this->assertSame($input['translations'][$locale]['title'], trim($document->getElementsByTagName('h1')->item(0)->textContent), $locale.' actual article heading');
    }

    public function test_optional_private_snapshots_for_local_next_builds(): void
    {
        $requested = getenv('BLOG_TEST_SNAPSHOT_DIR');
        if (!is_string($requested) || $requested === '') {
            $this->markTestSkipped('Set BLOG_TEST_SNAPSHOT_DIR explicitly to export private LOCAL-MOCK preview snapshots.');
        }
        $temp = realpath(sys_get_temp_dir());
        $parent = realpath(dirname($requested));
        $this->assertIsString($temp); $this->assertIsString($parent);
        $normalized = fn ($value) => strtolower(str_replace('\\', '/', rtrim($value, '\\/')));
        $this->assertTrue($normalized($parent) === $normalized($temp) || str_starts_with($normalized($parent).'/', $normalized($temp).'/'));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_.-]+$/D', basename($requested));
        if (!is_dir($requested)) $this->assertTrue(mkdir($requested, 0700));
        $directory = realpath($requested);
        $this->assertIsString($directory);
        $this->assertStringStartsWith($normalized($temp).'/', $normalized($directory).'/');
        $legacy = Blog::create(['status' => 'draft']);
        $legacy->translations()->create(['locale' => 'en', 'title' => 'Untouched main fixture', 'slug' => 'untouched-main-fixture', 'content' => '<p>Existing fixture.</p>']);
        $before = $legacy->fresh()->toArray();
        $content = app(BlogContentService::class);
        $quality = app(BlogQualityService::class);
        foreach (array_diff(array_keys(config('blog-publishing.sites')), ['main']) as $site) {
            $this->exportSnapshot($directory.'/'.$site.'-empty.json', $content->snapshot($site));
            for ($number = 1; $number <= 13; $number++) {
                $article = $this->payload($site, 'local-mock-preview-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT));
                unset($article['publishing_date']);
                $blog = Blog::create(['site_key' => $site, 'status' => 'published', 'published_at' => now()->subMinutes($number)]);
                $post = $content->makePost($blog, $article, now()->subMinutes($number)->toIso8601String());
                foreach ($article['translations'] as $locale => $row) {
                    $blog->translations()->create(['locale' => $locale, 'slug' => $article['slug'], 'title' => $row['title'],
                        'excerpt' => $row['excerpt'], 'content' => $quality->render($row), 'seo_title' => $row['seo_title'],
                        'seo_description' => $row['seo_description'], 'canonical_url' => $content->canonical($site, $locale, $article['slug'])]);
                }
                BlogGenerationRun::create(['site_key' => $site, 'blog_id' => $blog->id, 'topic' => $article['topic'],
                    'primary_keyword' => $article['primary_keyword'], 'status' => 'published', 'revision' => $post['revision'],
                    'payload' => ['article' => $article, 'post' => $post], 'published_at' => now()->subMinutes($number)]);
            }
            $snapshot = $content->snapshot($site);
            $this->assertCount(13, $snapshot['posts']);
            $this->assertSame(330, 190 + count($snapshot['posts']) * 10 + 10);
            $this->exportSnapshot($directory.'/'.$site.'.json', $snapshot);
        }
        $this->assertSame($before, $legacy->fresh()->toArray());
        $this->assertDatabaseCount('blogs', 79);
        $this->assertDatabaseCount('blog_generation_runs', 78);
    }

    private function payload(string $site = 'geo', string $subject = 'alpha'): array
    {
        $name = config('blog-publishing.sites.'.$site.'.name');
        $source = ['label' => 'Official Google TV troubleshooting reference', 'url' => 'https://support.google.com/googletv/answer/10070482'];
        $translations = [];
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $translations[$locale] = [
                'title' => 'LOCAL-MOCK '.$locale.' '.$name.' '.$subject.' article',
                'excerpt' => 'LOCAL-MOCK '.$locale.' '.$subject.' is a synthetic preview slot, not a real translation or a verified customer guide.',
                'seo_title' => 'LOCAL-MOCK '.$locale.' '.$name.' '.$subject.' preview',
                'seo_description' => 'LOCAL-MOCK '.$locale.' '.$subject.' metadata is only for isolated pipeline tests and local previews; this content must never reach a production website.',
                'sections' => [
                    ['heading' => 'LOCAL-MOCK '.$locale.' '.$subject.' starting point',
                        'paragraphs' => ['This '.$locale.' language slot for '.$name.' and '.$subject.' is deliberately synthetic English test text, not a real translation or a claim of device testing. The isolated record identifies a model, an application version and the connection used for a controlled example. It describes a question rather than promising an outcome. No customer account, private playlist or actual purchase is involved. The preview exists to exercise safe structured rendering and article navigation without altering a production page or sending a support message.'],
                        'items' => ['LOCAL-MOCK '.$locale.' '.$subject.' checklist item, only for a private test.'], 'links' => [$source]],
                    ['heading' => 'LOCAL-MOCK '.$locale.' '.$subject.' comparison notes',
                        'paragraphs' => ['For the '.$locale.' '.$subject.' fixture, keep a short description of the first simulated failing step and the one condition being compared. This paragraph provides enough plain text to exercise the bounded import contract, while remaining visibly labelled as a mock. It is not evidence of availability, licensing, customer experience or playback quality. A real article would need useful original guidance and verified references appropriate to its language and audience before publication. This test does not activate a service or submit a URL to a search engine.'],
                        'items' => [], 'links' => []],
                ],
            ];
        }
        return ['version' => 1, 'site_key' => $site, 'publishing_date' => self::DAY,
            'topic' => 'LOCAL-MOCK '.$site.' '.$subject.' isolated article topic', 'primary_keyword' => $name,
            'slug' => $site.'-'.$subject, 'translations' => $translations, 'sources' => [$source]];
    }

    private function enabledService(string ...$sites): BlogPublicationService
    {
        $service = app(BlogPublicationService::class);
        $service->initializeSettings();
        BlogSiteSetting::whereIn('site_key', $sites)->update(['enabled' => true]);
        return $service;
    }

    private function gitSource(array $input, string $raw): array
    {
        $article = app(BlogQualityService::class)->normalize($input, $input['site_key']);
        return ['commit' => str_repeat('a', 40),
            'path' => 'resources/content/blog-publications/'.$input['site_key'].'/'.self::DAY.'.json',
            'sha256' => hash('sha256', $raw),
            'fingerprint' => hash('sha256', json_encode($article, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE))];
    }

    private function command(string $action, array $options = [], string $stdin = ''): array
    {
        $command = app(BlogPublishing::class);
        $command->setLaravel($this->app);
        $input = new ArrayInput(['action' => $action, '--json' => true] + $options, $command->getDefinition());
        $stream = fopen('php://temp/maxmemory:3145728', 'r+');
        fwrite($stream, $stdin); rewind($stream); $input->setStream($stream);
        $output = new BufferedOutput();
        try {
            $exit = $command->run($input, $output);
            $raw = $output->fetch();
            $this->assertJson($raw);
            return [$exit, json_decode($raw, true, 64, JSON_THROW_ON_ERROR), $raw];
        } finally {
            fclose($stream);
        }
    }

    private function assertValidationRejected(callable $operation, ?string $field = null): void
    {
        $error = null;
        try { $operation(); } catch (Throwable $caught) { $error = $caught; }
        $this->assertInstanceOf(ValidationException::class, $error);
        if ($field !== null) $this->assertArrayHasKey($field, $error->errors());
    }

    private function assertHttpRejected(callable $operation, int $status): void
    {
        $error = null;
        try { $operation(); } catch (Throwable $caught) { $error = $caught; }
        $this->assertInstanceOf(HttpExceptionInterface::class, $error);
        $this->assertSame($status, $error->getStatusCode());
    }

    private function exportSnapshot(string $path, array $snapshot): void
    {
        $stream = fopen($path, 'x');
        $this->assertIsResource($stream, 'The private snapshot file must be new.');
        try {
            $json = json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->assertSame(strlen($json), fwrite($stream, $json));
        } finally {
            fclose($stream);
        }
    }
}
