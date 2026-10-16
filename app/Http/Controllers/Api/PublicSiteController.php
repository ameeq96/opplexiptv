<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Package;
use App\Services\Api\PublicSiteDataService;
use App\Services\BlogCoverImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicSiteController extends Controller
{
    public function __construct(private PublicSiteDataService $site)
    {
    }

    public function page(Request $request, string $page)
    {
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1', 'max:500'],
            'search' => ['nullable', 'string', 'max:200'],
        ]);

        return response()->json(['data' => $this->site->publicPage($page)]);
    }

    public function configure(Request $request)
    {
        $request->validate(['price' => ['nullable', 'numeric', 'min:0']]);

        return response()->json(['data' => $this->site->configure($request, false)]);
    }

    public function browserContext()
    {
        return response()->json(['data' => $this->site->browserContext()]);
    }

    public function providerPlans(Package $package)
    {
        return response()->json(['data' => $this->site->providerPlans($package)]);
    }

    public function landingPlans(string $brand)
    {
        return response()->json(['data' => $this->site->landingPlans($brand)]);
    }

    public function landingContent(Request $request, string $brand)
    {
        $request->validate(['locale' => ['nullable', 'string', \Illuminate\Validation\Rule::in(config('app.locales', ['en']))]]);

        return response()->json(['data' => $this->site->landingContent($brand)]);
    }

    public function products(Request $request)
    {
        $data = $request->validate([
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'type' => ['nullable', 'in:digital,affiliate,all'],
        ]);
        $payload = $this->site->shop($data['type'] ?? null, (int) ($data['per_page'] ?? 8));
        $products = $payload['products'];

        return response()->json([
            'data' => $products->items(),
            'meta' => $this->pagination($products),
        ]);
    }

    public function product(string $type, int $id)
    {
        return response()->json(['data' => $this->site->product($type, $id)]);
    }

    public function blogs(Request $request, BlogCoverImageService $covers)
    {
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'q' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:191'],
        ]);
        $payload = $this->site->blogs($request, $covers);
        $blogs = $payload['blogs'];

        return response()->json([
            'data' => [
                'articles' => collect($blogs->items())->map(fn (Blog $blog) => $this->article($blog))->values(),
                'featured' => $payload['featured'] ? $this->article($payload['featured']) : null,
                'categories' => $payload['categories']->map(function ($category) {
                    $translation = $category->translation();

                    return ['id' => (int) $category->id, 'title' => $translation?->title, 'slug' => $translation?->slug];
                })->values(),
                'search' => $payload['search'],
                'category' => $payload['categorySlug'],
                'meta' => $this->pageMeta($payload),
            ],
            'meta' => $this->pagination($blogs),
        ]);
    }

    public function blog(string $slug, BlogCoverImageService $covers)
    {
        $payload = $this->site->blog($slug, $covers);
        if (isset($payload['redirect_url'])) {
            return response()->json(['data' => null, 'redirect_url' => $payload['redirect_url']]);
        }

        return response()->json(['data' => [
            'article' => $this->article($payload['blog'], true),
            'related' => $payload['related']->map(fn (Blog $blog) => $this->article($blog))->values(),
            'meta' => $this->pageMeta($payload),
            'schema' => $payload['jsonLd'],
        ]]);
    }

    private function article(Blog $blog, bool $includeContent = false): array
    {
        $translation = $blog->translation();
        $data = [
            'id' => (int) $blog->id,
            'title' => $translation?->title,
            'slug' => $translation?->slug,
            'excerpt' => $translation?->excerpt,
            'url' => $translation ? $this->site->pageUrl('blogs.show', ['slug' => $translation->slug]) : null,
            'cover_image' => $blog->cover_image ? url(Storage::url($blog->cover_image)) : null,
            'author' => $blog->relationLoaded('author') ? $blog->author?->name : null,
            'published_at' => $blog->published_at?->toIso8601String(),
            'updated_at' => $blog->updated_at?->toIso8601String(),
            'reading_time' => $blog->reading_time,
        ];
        if ($includeContent) {
            $data['content'] = $translation?->content;
        }

        return $data;
    }

    private function pagination($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    private function pageMeta(array $payload): array
    {
        return [
            'title' => $payload['pageMetaTitle'] ?? null,
            'description' => $payload['pageMetaDescription'] ?? null,
            'keywords' => $payload['pageMetaKeywords'] ?? null,
            'canonical' => $payload['pageCanonical'] ?? $this->site->pageUrl('blogs.index'),
            'image' => $payload['pageMetaImage'] ?? null,
            'og_title' => $payload['pageOgTitle'] ?? null,
            'og_description' => $payload['pageOgDescription'] ?? null,
        ];
    }
}
