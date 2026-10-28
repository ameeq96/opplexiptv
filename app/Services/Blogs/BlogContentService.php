<?php

namespace App\Services\Blogs;

use App\Models\Blog;
use App\Models\BlogGenerationRun;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class BlogContentService
{
    public function __construct(private readonly BlogQualityService $quality)
    {
    }

    public function feed(string $site, int $page = 1, int $perPage = 25, ?string $revision = null): array
    {
        abort_if($site === 'main', 404);
        $snapshot = $this->inventory($site);
        abort_if($revision !== null && !hash_equals($snapshot['revision'], $revision), 409, 'The blog inventory changed; restart the build snapshot.');
        $perPage = max(1, min(50, $perPage));
        $total = $this->publishedQuery($site)?->count() ?? 0;
        $last = max(1, (int) ceil($total / $perPage));
        abort_if($page < 1 || $page > $last, 404);
        $rows = $this->publishedQuery($site)?->offset(($page - 1) * $perPage)->limit($perPage)->get() ?? collect();
        $snapshot['posts'] = $rows->map(fn ($run) => $run->payload['post'])->values()->all();
        abort_unless(hash_equals($snapshot['revision'], $this->inventory($site)['revision']), 409, 'The blog inventory changed while reading this page.');
        $snapshot['pagination'] = ['current_page' => $page, 'last_page' => $last, 'total' => $total, 'per_page' => $perPage];
        return $snapshot;
    }

    public function article(string $site, string $slug, string $locale): array
    {
        abort_if($site === 'main' || !in_array($locale, config('blog-publishing.supported_locales'), true), 404);
        $snapshot = $this->inventory($site);
        $record = $this->publishedQuery($site)?->where('payload->post->slug', $slug)->first();
        $post = $record?->payload['post'] ?? null;
        abort_unless($post, 404);
        abort_unless(hash_equals($snapshot['revision'], $this->inventory($site)['revision']), 409, 'The blog article changed while reading it.');
        $translation = $post['translations'][$locale];
        unset($post['translations']);
        $post['translation'] = $translation;
        return ['site_key' => $site, 'domain' => $snapshot['domain'], 'locale' => $locale, 'revision' => $snapshot['revision'], 'post' => $post];
    }

    public function snapshot(string $site, ?BlogGenerationRun $candidate = null): array
    {
        $definition = $this->quality->site($site);
        $posts = $site === 'main' ? [] : $this->publishedPosts($site);
        if ($candidate) {
            abort_unless($candidate->site_key === $site && $candidate->blog_id && $candidate->blog
                && $candidate->blog->site_key === $site && $candidate->blog->status !== 'archived'
                && in_array($candidate->status, ['ready', 'blocked', 'failed', 'published'], true), 422, 'This run is not a publication candidate.');
            $post = $candidate->payload['post'] ?? null;
            abort_unless(is_array($post) && ($post['id'] ?? null) === $candidate->blog_id
                && ($post['revision'] ?? '') === $candidate->revision, 422, 'The candidate snapshot is incomplete.');
            $posts = array_values(array_filter($posts, fn ($existing) => $existing['id'] !== $post['id']));
            $posts[] = $post;
        }
        usort($posts, fn ($a, $b) => strcmp($b['published_at'], $a['published_at']) ?: $b['id'] <=> $a['id']);
        $labels = $this->labels();
        $revision = hash('sha256', json_encode([$site, $labels, array_map(fn ($post) => [$post['id'], $post['revision']], $posts)], JSON_THROW_ON_ERROR));
        return [
            'version' => 1, 'site_key' => $site, 'domain' => $definition['domain'], 'revision' => $revision,
            'locales' => config('blog-publishing.supported_locales'), 'labels' => $labels, 'posts' => $posts,
        ];
    }

    public function makePost(Blog $blog, array $normalized, string $publishedAt, ?string $updatedAt = null): array
    {
        $site = $this->quality->site($blog->site_key);
        $post = [
            'id' => $blog->id, 'slug' => $normalized['slug'],
            'published_at' => $publishedAt, 'updated_at' => $updatedAt ?? $publishedAt,
            'author' => config('blog-publishing.publisher'),
            'image' => 'https://'.$site['domain'].($blog->site_key === 'main' ? '/images/placeholder.webp' : '/share-card.png'),
            'translations' => $normalized['translations'],
        ];
        $post['revision'] = hash('sha256', json_encode($post, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $post;
    }

    public function canonical(string $site, string $locale, string $slug): string
    {
        $definition = $this->quality->site($site);
        abort_unless(in_array($locale, config('blog-publishing.supported_locales'), true), 422);
        if ($site === 'main') {
            $base = 'https://'.$definition['domain'].'/blogs/'.$slug;
            $default = config('app.fallback_locale', 'en');
            return $locale === $default && config('laravellocalization.hideDefaultLocaleInURL')
                ? $base : LaravelLocalization::getLocalizedURL($locale, $base, [], true);
        }
        return 'https://'.$definition['domain'].'/'.($locale === 'en' ? '' : $locale.'/').'blogs/'.$slug.'/';
    }

    public function urls(BlogGenerationRun $run): array
    {
        $slug = $run->payload['post']['slug'] ?? '';
        abort_unless(is_string($slug) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug), 422);
        return array_map(fn ($locale) => $this->canonical($run->site_key, $locale, $slug), config('blog-publishing.supported_locales'));
    }

    public function validLandingPage(string $site, string $locale, string $page): bool
    {
        if ($site === 'main' || !isset(config('blog-publishing.sites')[$site])
            || !in_array($locale, config('blog-publishing.supported_locales'), true)
            || !Schema::hasTable('blog_generation_runs')) {
            return false;
        }
        if (preg_match('~^blogs/page/([2-9]|[1-9][0-9]+)$~D', $page, $matches)) {
            return (int) $matches[1] <= (int) ceil(($this->publishedQuery($site)?->count() ?? 0) / config('blog-publishing.per_page', 12));
        }
        if (!preg_match('~^blogs/([a-z0-9]+(?:-[a-z0-9]+)*)$~D', $page, $matches)) {
            return false;
        }
        return $this->publishedQuery($site)?->where('payload->post->slug', $matches[1])->exists() ?? false;
    }

    private function publishedQuery(string $site): ?Builder
    {
        if (!Schema::hasTable('blog_generation_runs')) {
            return null;
        }
        $latest = BlogGenerationRun::query()->selectRaw('MAX(id)')->where('site_key', $site)->where('status', 'published')
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->groupBy('blog_id');
        return BlogGenerationRun::query()->whereIn('id', $latest)
            ->whereHas('blog', fn ($query) => $query->where('site_key', $site)->where('status', '!=', 'archived'))
            ->whereColumn('payload->post->id', 'blog_id')->whereColumn('payload->post->revision', 'revision')
            ->orderByDesc('payload->post->published_at')->orderByDesc('blog_id');
    }

    private function publishedPosts(string $site): array
    {
        $query = $this->publishedQuery($site);
        if (!$query) {
            return [];
        }
        $posts = [];
        foreach ($query->lazy(50) as $run) {
            $posts[] = $run->payload['post'];
        }
        return $posts;
    }

    private function inventory(string $site): array
    {
        $definition = $this->quality->site($site);
        $labels = $this->labels();
        $rows = $this->publishedQuery($site)?->get(['blog_id', 'revision']) ?? collect();
        $ids = $rows->map(fn ($run) => [(int) $run->blog_id, $run->revision])->all();
        return ['version' => 1, 'site_key' => $site, 'domain' => $definition['domain'],
            'revision' => hash('sha256', json_encode([$site, $labels, $ids], JSON_THROW_ON_ERROR)),
            'locales' => config('blog-publishing.supported_locales'), 'labels' => $labels];
    }

    private function labels(): array
    {
        $labels = [];
        $keys = ['latestArticles', 'blogArchive', 'readArticle', 'previousPage', 'nextPage', 'publishedOn', 'pageLabel'];
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $path = resource_path('content/landing-pages/'.$locale.'.json');
            $document = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            foreach ($keys as $key) {
                $value = $document['labels'][$key] ?? null;
                abort_unless(is_string($value) && trim($value) !== '', 503, 'Blog interface translations are incomplete.');
                $labels[$locale][$key] = $value;
            }
        }
        return $labels;
    }
}
