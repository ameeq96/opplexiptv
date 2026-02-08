<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');
        $search = trim((string) $request->query('q', ''));
        $categorySlug = trim((string) $request->query('category', ''));

        $featuredQuery = Blog::published()
            ->where('is_featured', true)
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }, 'categories.translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }]);

        if ($categorySlug !== '') {
            $featuredQuery->whereHas('categories.translations', function ($q) use ($categorySlug, $locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback])
                    ->where('slug', $categorySlug);
            });
        }

        $featured = $featuredQuery->orderByDesc('published_at')->first();

        $blogsQuery = Blog::published()
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }, 'categories.translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }]);

        if ($featured) {
            $blogsQuery->where('id', '!=', $featured->id);
        }

        if ($search !== '') {
            $blogsQuery->whereHas('translations', function ($q) use ($search, $locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback])
                    ->where(function ($qq) use ($search) {
                        $qq->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%");
                    });
            });
        }

        if ($categorySlug !== '') {
            $blogsQuery->whereHas('categories.translations', function ($q) use ($categorySlug, $locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback])
                    ->where('slug', $categorySlug);
            });
        }

        $blogs = $blogsQuery->orderByDesc('published_at')->paginate(12)->withQueryString();

        $categories = BlogCategory::query()
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }])
            ->orderBy('id')
            ->get();

        $pageMetaTitle = trans('meta.blogs.index.title');
        $pageMetaDescription = trans('meta.blogs.index.description');
        $pageMetaKeywords = trans('meta.blogs.index.keywords');

        return view('blogs.index', compact(
            'blogs',
            'featured',
            'search',
            'categories',
            'categorySlug',
            'pageMetaTitle',
            'pageMetaDescription',
            'pageMetaKeywords'
        ));
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        $translation = BlogTranslation::where('slug', $slug)
            ->whereIn('locale', [$locale, $fallback])
            ->firstOrFail();

        $blog = Blog::with(['translations', 'author'])->findOrFail($translation->blog_id);

        if ($blog->status !== 'published' || ($blog->published_at && $blog->published_at->isFuture())) {
            abort(404);
        }

        if ($blog->status === 'published' && (!$blog->published_at || $blog->published_at->isPast())) {
            $blog->increment('views');
        }

        $currentTranslation = $blog->translation($translation->locale) ?? $translation;

        $related = Blog::published()
            ->where('id', '!=', $blog->id)
            ->whereHas('translations', function ($q) use ($locale) {
                $q->where('locale', $locale);
            })
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $pageMetaTitle = $currentTranslation->seo_title ?: $currentTranslation->title;
        $pageMetaDescription = $currentTranslation->seo_description ?: Str::limit(strip_tags($currentTranslation->excerpt ?: $currentTranslation->content), 155);
        $pageMetaKeywords = $currentTranslation->seo_keywords ?: trans('meta.blogs.show.keywords');

        $pageOgTitle = $currentTranslation->og_title ?: $pageMetaTitle;
        $pageOgDescription = $currentTranslation->og_description ?: $pageMetaDescription;

        $imagePath = $currentTranslation->og_image ?: $blog->cover_image;
        $pageMetaImage = $imagePath ? url(Storage::url($imagePath)) : null;

        $pageCanonical = $currentTranslation->canonical_url ?: url()->current();

        $pageOgType = 'article';
        $jsonLd = $currentTranslation->schema_json ?: [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $currentTranslation->title,
            'description' => $pageMetaDescription,
            'inLanguage' => $translation->locale,
            'datePublished' => optional($blog->published_at)->toIso8601String(),
            'dateModified' => optional($blog->updated_at)->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => optional($blog->author)->name ?: config('app.name'),
            ],
            'image' => $pageMetaImage ? [$pageMetaImage] : [],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $pageCanonical,
            ],
        ];

        return view('blogs.show', compact(
            'blog',
            'currentTranslation',
            'related',
            'pageMetaTitle',
            'pageMetaDescription',
            'pageMetaKeywords',
            'pageMetaImage',
            'pageCanonical',
            'pageOgType',
            'pageOgTitle',
            'pageOgDescription',
            'jsonLd'
        ));
    }
}
