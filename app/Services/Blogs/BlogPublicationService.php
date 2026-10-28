<?php

namespace App\Services\Blogs;

use App\Models\Blog;
use App\Models\BlogGenerationRun;
use App\Models\BlogPublishDelivery;
use App\Models\BlogSiteSetting;
use Carbon\CarbonImmutable;
use DOMDocument;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class BlogPublicationService
{
    public function __construct(
        private readonly BlogQualityService $quality,
        private readonly BlogContentService $content,
        private readonly BlogDiscoveryService $discovery,
    ) {
    }

    public function initializeSettings(): void
    {
        foreach (array_keys(config('blog-publishing.sites')) as $site) {
            BlogSiteSetting::firstOrCreate(['site_key' => $site], [
                'enabled' => false, 'timezone' => config('blog-publishing.timezone'),
                'publish_time' => config('blog-publishing.time'), 'locales' => config('blog-publishing.supported_locales'),
            ]);
        }
    }

    public function reserve(string $site, string $date): BlogGenerationRun
    {
        $setting = $this->setting($site);
        abort_unless($setting->enabled, 422, 'Publishing is paused for this website.');
        $this->currentDate($date, $setting->timezone);
        return BlogGenerationRun::firstOrCreate(['site_key' => $site, 'publishing_date' => $date], [
            'topic' => 'Daily article awaiting editorial preparation', 'status' => 'reserved',
        ]);
    }

    public function preflight(string $site, string $date, array $input, string $sourceSha256): array
    {
        $this->quality->site($site);
        $setting = BlogSiteSetting::where('site_key', $site)->first();
        abort_unless($setting && $setting->enabled, 422, 'Publishing must already be enabled for this website.');
        $this->currentDate($date, $setting->timezone);
        if (($input['publishing_date'] ?? null) !== $date) {
            throw ValidationException::withMessages(['publishing_date' => 'The payload must match the reserved publication day.']);
        }
        abort_unless(preg_match('/^[a-f0-9]{64}$/D', $sourceSha256), 422, 'The exact source SHA-256 is required.');
        $normalized = $this->quality->normalize($input, $site);
        $fingerprint = hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $run = BlogGenerationRun::where('site_key', $site)->whereDate('publishing_date', $date)->first();
        if ($run && ($run->blog_id !== null || $run->payload !== null || $run->fingerprint !== null || $run->revision !== null)) {
            abort_unless($run->blog_id && hash_equals($run->fingerprint ?? '', $fingerprint), 409, 'This website already has a different article for this day.');
        } else {
            $this->rejectDuplicate($normalized);
            abort_if(DB::table('blog_translations')->where('slug', $normalized['slug'])->exists(), 422, 'Choose a new immutable article slug.');
        }
        return ['valid' => true, 'site_key' => $site, 'publishing_date' => $date, 'slug' => $normalized['slug'],
            'fingerprint' => $fingerprint, 'source_sha256' => $sourceSha256];
    }

    public function import(string $site, string $date, array $input, ?array $gitSource = null, ?string $sourceSha256 = null): BlogGenerationRun
    {
        $setting = $this->setting($site, $gitSource === null);
        $this->currentDate($date, $setting->timezone);
        if (isset($input['publishing_date']) && $input['publishing_date'] !== $date) {
            throw ValidationException::withMessages(['publishing_date' => 'The payload must match the reserved publication day.']);
        }
        if ($gitSource !== null) {
            $gitSource = $this->gitSource($gitSource, $site, $date);
            abort_unless(($input['publishing_date'] ?? null) === $date && is_string($sourceSha256)
                && hash_equals($gitSource['sha256'], $sourceSha256), 409, 'The committed article source changed.');
        }
        $result = Cache::lock('blog-import-global', 300)->get(function () use ($site, $date, $setting, $input, $gitSource): BlogGenerationRun {
            try {
                $normalized = $this->quality->normalize($input, $site);
                $fingerprint = hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
                abort_if($gitSource !== null && !hash_equals($gitSource['fingerprint'], $fingerprint), 409, 'The committed article fingerprint changed.');
                return DB::transaction(function () use ($site, $date, $setting, $normalized, $fingerprint, $gitSource): BlogGenerationRun {
                    $run = BlogGenerationRun::where('site_key', $site)->whereDate('publishing_date', $date)->lockForUpdate()->first();
                    if ($run && ($run->blog_id !== null || $run->payload !== null || $run->fingerprint !== null || $run->revision !== null)) {
                        abort_unless($run->blog_id && hash_equals($run->fingerprint ?? '', $fingerprint), 409, 'This website already has a different article for this day.');
                        $this->assertGitSource($run, $gitSource);
                        return $run;
                    }
                    $this->rejectDuplicate($normalized);
                    abort_if(DB::table('blog_translations')->where('slug', $normalized['slug'])->exists(), 422, 'Choose a new immutable article slug.');
                    $run ??= new BlogGenerationRun(['site_key' => $site, 'publishing_date' => $date]);
                    $blog = Blog::create(['site_key' => $site, 'author_id' => null, 'status' => 'draft', 'published_at' => now(),
                        'reading_time' => max(1, (int) ceil(str_word_count($this->quality->bodyText($normalized['translations']['en'])) / 200)), 'is_featured' => false]);
                    $post = $this->content->makePost($blog, $normalized, now()->toIso8601String());
                    $this->saveTranslations($blog, $normalized, $post);
                    $run->fill([
                        'blog_id' => $blog->id, 'topic' => $normalized['topic'], 'primary_keyword' => $normalized['primary_keyword'],
                        'fingerprint' => $fingerprint, 'revision' => $post['revision'],
                        'payload' => ['article' => $normalized, 'post' => $post] + ($gitSource !== null ? ['git_source' => $gitSource] : []),
                        'status' => $setting->enabled ? 'ready' : 'blocked', 'errors' => $setting->enabled ? [] : ['publishing_paused'],
                        'attempts' => $run->attempts + 1,
                    ])->save();
                    $this->ensureDeliveries($run);
                    return $run->fresh('blog');
                });
            } catch (ValidationException $error) {
                DB::transaction(function () use ($site, $date, $error): void {
                    $failed = BlogGenerationRun::where('site_key', $site)->whereDate('publishing_date', $date)->lockForUpdate()->first();
                    if ($failed && ($failed->blog_id !== null || $failed->payload !== null || $failed->fingerprint !== null || $failed->revision !== null)) {
                        return;
                    }
                    $failed ??= new BlogGenerationRun(['site_key' => $site, 'publishing_date' => $date, 'topic' => 'Daily article needs editorial correction']);
                    $failed->fill(['status' => 'draft', 'errors' => array_values(array_merge(...array_values($error->errors()))),
                        'attempts' => $failed->attempts + 1])->save();
                });
                throw $error;
            }
        });
        if (!$result instanceof BlogGenerationRun) {
            throw new RuntimeException('Another article import is already running.');
        }
        return $result;
    }

    public function queueExisting(Blog $blog): BlogGenerationRun
    {
        abort_if($blog->site_key === 'main', 422, 'Existing main-site manual publishing uses its original workflow.');
        $rows = $blog->translations()->get()->keyBy('locale');
        $translations = [];
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $row = $rows->get($locale);
            abort_unless($row, 422, 'Every supported translation is required.');
            $translations[$locale] = [
                'title' => $row->title, 'excerpt' => $row->excerpt,
                'seo_title' => $row->seo_title ?: $row->title, 'seo_description' => $row->seo_description ?: $row->excerpt,
                'sections' => $this->quality->sectionsFromHtml($row->content, $row->title, $blog->site_key),
            ];
        }
        $slug = $rows->get('en')->slug;
        abort_unless($rows->every(fn ($row) => $row->slug === $slug), 422, 'All translations must share the immutable article slug.');
        $sources = collect($translations['en']['sections'])->flatMap(fn ($section) => $section['links'])->unique('url')->values()->all();
        $editorialRun = $blog->generationRuns()->latest('id')->first();
        $keyword = $editorialRun?->primary_keyword ?: trim(explode(',', $rows->get('en')->seo_keywords ?: '')[0]);
        $input = ['version' => 1, 'site_key' => $blog->site_key, 'topic' => $rows->get('en')->title,
            'primary_keyword' => $keyword ?: config('blog-publishing.sites.'.$blog->site_key.'.name'), 'slug' => $slug,
            'translations' => $translations, 'sources' => $sources];
        $normalized = $this->quality->normalize($input, $blog->site_key);
        $previous = $blog->generationRuns()->where('status', 'published')->latest('id')->first();
        $publishedAt = $previous?->payload['post']['published_at'] ?? now()->toIso8601String();
        $post = $this->content->makePost($blog, $normalized, $publishedAt, now()->toIso8601String());
        $pending = $blog->generationRuns()->where('revision', $post['revision'])->first();
        if ($pending) {
            return $pending;
        }
        return DB::transaction(function () use ($blog, $normalized, $post): BlogGenerationRun {
            $run = BlogGenerationRun::create([
                'site_key' => $blog->site_key, 'publishing_date' => null, 'blog_id' => $blog->id,
                'topic' => $normalized['topic'], 'primary_keyword' => $normalized['primary_keyword'],
                'fingerprint' => hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR)), 'revision' => $post['revision'],
                'payload' => ['article' => $normalized, 'post' => $post], 'status' => 'ready', 'errors' => [],
            ]);
            $this->ensureDeliveries($run);
            return $run;
        });
    }

    public function activate(BlogGenerationRun $run, string $revision, string $snapshotRevision, ?array $gitSource = null): BlogGenerationRun
    {
        $run->refresh()->load('blog');
        $this->assertGitSource($run, $gitSource);
        $setting = $this->setting($run->site_key, $gitSource === null);
        abort_unless($setting->enabled, 422, 'Publishing is paused for this website.');
        if ($run->publishing_date && $run->status !== 'published') {
            $this->currentDate($run->publishing_date->format('Y-m-d'), $setting->timezone);
        }
        abort_unless(hash_equals($run->revision ?? '', $revision), 409, 'The article revision changed.');
        $snapshot = $this->content->snapshot($run->site_key, $run);
        abort_unless(hash_equals($snapshot['revision'], $snapshotRevision), 409, 'The site snapshot changed before publication.');
        $newest = $run->blog->generationRuns()->latest('id')->first();
        abort_if($newest && $newest->id > $run->id, 409, 'A newer queued revision must be preserved.');
        if ($run->status === 'published') {
            $this->verifyPublic($run);
            $this->assertGitSource($run->fresh(), $gitSource);
            return $run;
        }
        $lock = Cache::lock('blog-activate:'.$run->site_key, 900);
        $activated = $lock->get(function () use ($run, $snapshotRevision, $gitSource): BlogGenerationRun {
            abort_unless(hash_equals($this->content->snapshot($run->site_key, $run)['revision'], $snapshotRevision), 409, 'Another site release changed the build snapshot.');
            $this->assertGitSource($run->fresh(), $gitSource);
            if ($run->site_key !== 'main') {
                $this->verifyPublic($run);
            }
            $old = ['status' => $run->blog->status, 'published_at' => $run->blog->published_at];
            $mutated = false;
            try {
                DB::transaction(function () use ($run, $gitSource): void {
                    $locked = BlogGenerationRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
                    $this->assertGitSource($locked, $gitSource);
                    $this->assertCurrentDraft($run);
                    $setting = BlogSiteSetting::where('site_key', $run->site_key)->lockForUpdate()->firstOrFail();
                    abort_unless($setting->enabled, 422, 'Publishing was paused before activation.');
                    if ($run->publishing_date) {
                        $start = CarbonImmutable::now($setting->timezone)->startOfDay();
                        abort_if(BlogGenerationRun::where('site_key', $run->site_key)->where('status', 'published')
                            ->where('id', '!=', $run->id)->where('published_at', '>=', $start->utc())
                            ->where('published_at', '<', $start->addDay()->utc())->exists(), 409, 'The daily automatic publication limit has already been used.');
                    }
                    $post = $run->payload['post'];
                    $run->blog->update(['status' => 'published', 'published_at' => $post['published_at']]);
                    $run->update(['status' => 'published', 'published_at' => now(), 'errors' => []]);
                });
                $mutated = true;
                if ($run->site_key === 'main') {
                    if (Artisan::call('sitemap:generate') !== 0) {
                        throw new RuntimeException('Main sitemap generation failed.');
                    }
                    $this->verifyPublic($run);
                }
                $delivery = $this->delivery($run, 'site');
                $delivery->update(['status' => 'accepted', 'attempts' => $delivery->attempts + 1,
                    'http_status' => 200, 'error_code' => null, 'last_attempt_at' => now(), 'next_attempt_at' => null]);
                return $run->fresh('blog');
            } catch (Throwable $error) {
                if (!$mutated) {
                    $this->assertGitSource($run->fresh(), $gitSource);
                }
                if ($mutated) {
                    DB::transaction(function () use ($run, $old): void {
                        try {
                            $this->assertCurrentDraft($run);
                            Blog::whereKey($run->blog_id)->firstOrFail()->update($old);
                        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface|ValidationException) {
                            // A later editorial edit owns its visibility; do not replace it with an older state.
                        }
                    });
                }
                $run->update(['status' => 'failed', 'published_at' => null, 'errors' => ['public_verification_failed']]);
                if ($mutated && $run->site_key === 'main') {
                    Artisan::call('sitemap:generate');
                }
                if ($error instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    throw $error;
                }
                throw new RuntimeException('Publication was not verified; the previous database visibility was restored.', previous: $error);
            }
        });
        if (!$activated instanceof BlogGenerationRun) {
            throw new RuntimeException('Another release is already being activated for this website.');
        }
        return $activated;
    }

    public function verifyPublic(BlogGenerationRun $run): void
    {
        $post = $run->payload['post'] ?? null;
        abort_unless(is_array($post), 422);
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $url = $this->content->canonical($run->site_key, $locale, $post['slug']);
            $body = $this->discovery->publicFile($url, 2097152, true);
            $document = new DOMDocument();
            $previous = libxml_use_internal_errors(true);
            try {
                $document->loadHTML('<?xml encoding="UTF-8">'.$body, LIBXML_NONET | LIBXML_HTML_NODEFDTD);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
            $canonicals = [];
            foreach ($document->getElementsByTagName('link') as $link) {
                if (strtolower($link->getAttribute('rel')) === 'canonical') {
                    $canonicals[] = $link->getAttribute('href');
                }
            }
            foreach ($document->getElementsByTagName('meta') as $meta) {
                if (in_array(strtolower($meta->getAttribute('name')), ['robots', 'googlebot', 'bingbot'], true)
                    && preg_match('/\b(?:noindex|none)\b/i', $meta->getAttribute('content'))) {
                    throw new RuntimeException('The new article is not indexable.');
                }
            }
            $headings = $document->getElementsByTagName('h1');
            $titles = $document->getElementsByTagName('title');
            if ($canonicals !== [$url] || $headings->length !== 1
                || trim($headings->item(0)->textContent) !== $post['translations'][$locale]['title']
                || $titles->length !== 1 || trim($titles->item(0)->textContent) !== $post['translations'][$locale]['seo_title']) {
                throw new RuntimeException('The canonical article or heading did not match the release.');
            }
            if ($run->site_key === 'main') {
                $first = $post['translations'][$locale]['sections'][0]['paragraphs'][0] ?? null;
                if (!$first || !str_contains(preg_replace('/\s+/u', ' ', $document->textContent), $first)) {
                    throw new RuntimeException('The published main article body did not match.');
                }
            } elseif (!str_contains($body, 'data-blog-id="'.$post['id'].'"') || !str_contains($body, 'data-blog-revision="'.$post['revision'].'"')) {
                throw new RuntimeException('The live Next article revision did not match.');
            }
        }
    }

    public function rollback(BlogGenerationRun $run, string $revision): void
    {
        DB::transaction(function () use ($run, $revision): void {
            $locked = BlogGenerationRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
            abort_unless(hash_equals($locked->revision ?? '', $revision), 409, 'The article was edited after this release.');
            $blog = Blog::whereKey($locked->blog_id)->lockForUpdate()->firstOrFail();
            $newest = $blog->generationRuns()->latest('id')->first();
            abort_if($newest && $newest->id > $locked->id, 409, 'A newer queued revision must be preserved.');
            $published = $locked->status === 'published';
            $locked->update(['status' => 'failed', 'published_at' => null, 'errors' => ['release_rolled_back']]);
            if ($published) {
                $previous = $blog->generationRuns()->where('status', 'published')->latest('id')->first();
                $blog->update(['status' => $previous ? 'published' : 'draft',
                    'published_at' => $previous?->payload['post']['published_at'] ?? $blog->published_at]);
            }
            $this->delivery($locked, 'site')->update(['status' => 'failed', 'error_code' => 'release_rolled_back']);
        });
        $run->refresh();
        if ($run->site_key === 'main') {
            Artisan::call('sitemap:generate');
        }
    }

    public function block(BlogGenerationRun $run, string $reason): void
    {
        abort_unless(in_array($reason, ['public-unavailable', 'build-failed', 'verification-failed'], true), 422);
        DB::transaction(function () use ($run, $reason): void {
            $locked = BlogGenerationRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->status === 'published', 409, 'A published record must not be hidden by a failed notification.');
            $locked->update(['status' => 'blocked', 'errors' => [str_replace('-', '_', $reason)]]);
        });
        $run->refresh();
    }

    public function retry(BlogGenerationRun $run): void
    {
        DB::transaction(function () use ($run): void {
            $locked = BlogGenerationRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->blog_id && is_array($locked->payload['post'] ?? null), 422, 'An editorially valid draft is required.');
            $newest = $locked->blog->generationRuns()->latest('id')->first();
            abort_if($newest && $newest->id > $locked->id, 409, 'A newer queued revision must be preserved.');
            if ($locked->status !== 'published') {
                $locked->update(['status' => 'ready', 'errors' => []]);
            }
            $locked->deliveries()->whereNotIn('status', ['accepted', 'accepted_pending'])->update(['status' => 'pending', 'next_attempt_at' => null, 'error_code' => null]);
        });
        $run->refresh();
    }

    public function publicationEligibility(BlogGenerationRun $run): array
    {
        try {
            return DB::transaction(function () use ($run): array {
                $current = BlogGenerationRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
                $setting = BlogSiteSetting::where('site_key', $current->site_key)->lockForUpdate()->firstOrFail();
                abort_unless($setting->enabled, 422, 'Publishing is paused for this website.');
                abort_unless($current->blog_id && in_array($current->status, ['ready', 'failed', 'blocked', 'published'], true)
                    && preg_match('/^[a-f0-9]{64}$/D', $current->revision ?? '')
                    && hash_equals($current->revision, $current->payload['post']['revision'] ?? ''), 422, 'A valid article revision is required.');
                if ($current->publishing_date && $current->status !== 'published') {
                    $this->currentDate($current->publishing_date->format('Y-m-d'), $setting->timezone);
                    $start = CarbonImmutable::now($setting->timezone)->startOfDay();
                    abort_if(BlogGenerationRun::where('site_key', $current->site_key)->where('status', 'published')
                        ->where('id', '!=', $current->id)->where('published_at', '>=', $start->utc())
                        ->where('published_at', '<', $start->addDay()->utc())->exists(), 409, 'The daily automatic publication limit has already been used.');
                }
                $this->assertCurrentDraft($current);
                $snapshot = $this->content->snapshot($current->site_key, $current);
                return ['eligible' => true, 'revision' => $current->revision, 'snapshot_revision' => $snapshot['revision']];
            });
        } catch (ValidationException) {
            return ['eligible' => false, 'error_code' => 'eligibility_validation_failed'];
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $error) {
            return ['eligible' => false, 'error_code' => 'eligibility_http_'.$error->getStatusCode()];
        }
    }

    private function setting(string $site, bool $initialize = true): BlogSiteSetting
    {
        $this->quality->site($site);
        if ($initialize) {
            $this->initializeSettings();
        }
        return BlogSiteSetting::where('site_key', $site)->firstOrFail();
    }

    private function gitSource(array $source, string $site, string $date): array
    {
        abort_unless(count($source) === 4 && array_diff(array_keys($source), ['commit', 'path', 'sha256', 'fingerprint']) === []
            && is_string($source['commit'] ?? null) && preg_match('/^(?:[a-f0-9]{40}|[a-f0-9]{64})$/D', $source['commit'])
            && is_string($source['sha256'] ?? null) && preg_match('/^[a-f0-9]{64}$/D', $source['sha256'])
            && is_string($source['fingerprint'] ?? null) && preg_match('/^[a-f0-9]{64}$/D', $source['fingerprint'])
            && ($source['path'] ?? null) === 'resources/content/blog-publications/'.$site.'/'.$date.'.json', 422, 'The Git source metadata does not match this publication.');
        return ['commit' => $source['commit'], 'path' => $source['path'], 'sha256' => $source['sha256'], 'fingerprint' => $source['fingerprint']];
    }

    private function assertGitSource(BlogGenerationRun $run, ?array $expected): void
    {
        if ($expected === null) {
            return;
        }
        abort_unless($run->publishing_date, 422, 'A daily source publication date is required.');
        $expected = $this->gitSource($expected, $run->site_key, $run->publishing_date->format('Y-m-d'));
        $stored = $run->payload['git_source'] ?? null;
        abort_unless(is_array($stored) && count($stored) === 4
            && collect($expected)->every(fn ($value, $key) => isset($stored[$key]) && is_string($stored[$key]) && hash_equals($value, $stored[$key]))
            && hash_equals($run->fingerprint ?? '', $expected['fingerprint']), 409, 'The committed article provenance changed.');
    }

    private function currentDate(string $date, string $timezone): void
    {
        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        } catch (Throwable) {
            $parsed = null;
        }
        if (!$parsed || $parsed->format('Y-m-d') !== $date || $date !== CarbonImmutable::now($timezone)->format('Y-m-d')) {
            throw ValidationException::withMessages(['publishing_date' => 'New automatic imports must belong to the current publishing day; retry an existing run instead of backfilling.']);
        }
    }

    private function assertCurrentDraft(BlogGenerationRun $run): void
    {
        $blog = Blog::whereKey($run->blog_id)->lockForUpdate()->firstOrFail();
        $latest = $blog->generationRuns()->latest('id')->first();
        abort_if($latest && $latest->id > $run->id, 409, 'The staged article was superseded by a newer edit.');
        abort_unless($blog->site_key === $run->site_key && $blog->status !== 'archived', 409, 'The article placement or visibility changed.');
        $rows = $blog->translations()->lockForUpdate()->get()->keyBy('locale');
        $canonical = function (mixed $value) use (&$canonical): mixed {
            if (!is_array($value)) {
                return $value;
            }
            if (!array_is_list($value)) {
                ksort($value);
            }
            return array_map($canonical, $value);
        };
        $article = $canonical($run->payload['article']);
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $row = $rows->get($locale);
            $expected = $article['translations'][$locale];
            abort_unless($row && $row->slug === $article['slug'] && $row->title === $expected['title']
                && $row->excerpt === $expected['excerpt'] && ($row->seo_title ?: $row->title) === $expected['seo_title']
                && ($row->seo_description ?: $row->excerpt) === $expected['seo_description']
                && $canonical($this->quality->sectionsFromHtml($row->content, $row->title, $run->site_key)) === $expected['sections'], 409, 'The article was edited after staging.');
        }
    }

    private function rejectDuplicate(array $article): void
    {
        $english = $this->quality->bodyText($article['translations']['en']);
        foreach (BlogGenerationRun::whereNotNull('payload')->latest('id')->limit(500)->get() as $existing) {
            $previous = $existing->payload['article'] ?? null;
            if (is_array($previous) && (mb_strtolower($previous['topic']) === mb_strtolower($article['topic'])
                || $this->quality->similarity($english, $this->quality->bodyText($previous['translations']['en'])) >= 0.72)) {
                throw ValidationException::withMessages(['originality' => 'This topic or body is too close to an existing article; prepare a distinct useful article.']);
            }
        }
        $document = json_decode(file_get_contents(resource_path('content/landing-pages/en.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($document['brands'] as $brand) {
            foreach ($brand['pages'] as $page) {
                if ($page['kind'] === 'article') {
                    $text = implode(' ', array_column($page['sections'], 'body'));
                    if ($this->quality->similarity($english, $text) >= 0.72) {
                        throw ValidationException::withMessages(['originality' => 'The new article repeats an existing guide.']);
                    }
                }
            }
        }
    }

    private function saveTranslations(Blog $blog, array $article, array $post): void
    {
        foreach ($article['translations'] as $locale => $row) {
            $canonical = $this->content->canonical($blog->site_key, $locale, $article['slug']);
            $blog->translations()->create([
                'locale' => $locale, 'slug' => $article['slug'], 'title' => $row['title'], 'excerpt' => $row['excerpt'],
                'content' => $this->quality->render($row), 'seo_title' => $row['seo_title'], 'seo_description' => $row['seo_description'],
                'seo_keywords' => $article['primary_keyword'], 'og_title' => $row['seo_title'], 'og_description' => $row['seo_description'],
                'canonical_url' => $canonical,
                'schema_json' => ['@context' => 'https://schema.org', '@type' => 'BlogPosting',
                    'headline' => $row['title'], 'description' => $row['seo_description'], 'inLanguage' => $locale,
                    'datePublished' => $post['published_at'], 'dateModified' => $post['updated_at'],
                    'author' => ['@type' => 'Organization', 'name' => $post['author']['name']],
                    'publisher' => ['@type' => 'Organization', 'name' => $post['author']['name']],
                    'image' => [$post['image']], 'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical]],
            ]);
        }
    }

    private function ensureDeliveries(BlogGenerationRun $run): void
    {
        foreach (['site', 'sitemap', 'indexnow', 'google'] as $channel) {
            $this->delivery($run, $channel);
        }
    }

    private function delivery(BlogGenerationRun $run, string $channel): BlogPublishDelivery
    {
        return BlogPublishDelivery::firstOrCreate(['run_id' => $run->id, 'channel' => $channel, 'revision' => $run->revision], ['status' => 'pending']);
    }
}
