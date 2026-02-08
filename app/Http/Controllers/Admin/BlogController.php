<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\BlogTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    protected function authorizeManage(): void
    {
        if (auth('admin')->check()) {
            return;
        }

        if (!Gate::allows('manage-blogs')) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->authorizeManage();

        $locale = $request->query('locale', admin_locale());
        $status = $request->query('status');
        $featured = $request->query('featured');
        $search = trim((string) $request->query('q', ''));

        $query = Blog::query()->with('translations');

        if ($status) {
            $query->where('status', $status);
        }

        if ($featured === '1') {
            $query->where('is_featured', true);
        }

        if ($search !== '') {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $blogs = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        return view('admin.blogs.index', [
            'blogs' => $blogs,
            'locale' => $locale,
            'status' => $status,
            'featured' => $featured,
            'search' => $search,
            'locales' => admin_locales(),
        ]);
    }

    public function create()
    {
        $this->authorizeManage();
        $blog = new Blog();
        $blog->setRelation('translations', collect());
        return view('admin.blogs.create', [
            'locales' => admin_locales(),
            'blog' => $blog,
        ]);
    }

    public function store(StoreBlogRequest $request)
    {
        $this->authorizeManage();

        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            $cover = null;
            if ($request->hasFile('cover_image')) {
                $cover = $request->file('cover_image')->store('blogs', 'public');
            }

            $blog = Blog::create([
                'author_id' => $data['author_id'] ?? null,
                'cover_image' => $cover,
                'status' => $data['status'],
                'published_at' => $data['published_at'] ?? null,
                'reading_time' => $data['reading_time'] ?? null,
                'is_featured' => (bool) ($data['is_featured'] ?? false),
            ]);

            $this->syncTranslations($blog, $data['translations'] ?? [], $request);

            return redirect()->route('admin.blogs.index')->with('success', 'Blog created successfully.');
        });
    }

    public function edit(Blog $blog)
    {
        $this->authorizeManage();
        $blog->load('translations');

        return view('admin.blogs.edit', [
            'blog' => $blog,
            'locales' => admin_locales(),
        ]);
    }

    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        $this->authorizeManage();

        return DB::transaction(function () use ($request, $blog) {
            $data = $request->validated();

            if ($request->hasFile('cover_image')) {
                if ($blog->cover_image) {
                    Storage::disk('public')->delete($blog->cover_image);
                }
                $blog->cover_image = $request->file('cover_image')->store('blogs', 'public');
            }

            $blog->fill([
                'author_id' => $data['author_id'] ?? null,
                'status' => $data['status'],
                'published_at' => $data['published_at'] ?? null,
                'reading_time' => $data['reading_time'] ?? null,
                'is_featured' => (bool) ($data['is_featured'] ?? false),
            ])->save();

            $this->syncTranslations($blog, $data['translations'] ?? [], $request, true);

            return redirect()->route('admin.blogs.index')->with('success', 'Blog updated successfully.');
        });
    }

    public function destroy(Blog $blog)
    {
        $this->authorizeManage();
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog deleted.');
    }

    public function publish(Blog $blog)
    {
        $this->authorizeManage();
        $blog->update([
            'status' => 'published',
            'published_at' => $blog->published_at ?? now(),
        ]);
        return back()->with('success', 'Blog published.');
    }

    public function archive(Blog $blog)
    {
        $this->authorizeManage();
        $blog->update(['status' => 'archived']);
        return back()->with('success', 'Blog archived.');
    }

    public function duplicate(Blog $blog)
    {
        $this->authorizeManage();

        return DB::transaction(function () use ($blog) {
            $new = $blog->replicate(['views']);
            $new->status = 'draft';
            $new->published_at = null;
            $new->views = 0;
            $new->push();

            $blog->load('translations');
            foreach ($blog->translations as $translation) {
                $newSlug = $this->uniqueSlug($translation->slug . '-copy', $translation->locale, $new->id);
                $new->translations()->create([
                    'locale' => $translation->locale,
                    'title' => $translation->title,
                    'slug' => $newSlug,
                    'excerpt' => $translation->excerpt,
                    'content' => $translation->content,
                    'seo_title' => $translation->seo_title,
                    'seo_description' => $translation->seo_description,
                    'seo_keywords' => $translation->seo_keywords,
                    'og_title' => $translation->og_title,
                    'og_description' => $translation->og_description,
                    'og_image' => $translation->og_image,
                    'canonical_url' => $translation->canonical_url,
                    'schema_json' => $translation->schema_json,
                ]);
            }

            return back()->with('success', 'Blog duplicated.');
        });
    }

    protected function syncTranslations(Blog $blog, array $translations, Request $request, bool $isUpdate = false): void
    {
        foreach (admin_locales() as $locale) {
            $payload = $translations[$locale] ?? [];
            $title = trim((string) ($payload['title'] ?? ''));

            if ($title === '') {
                if ($isUpdate) {
                    continue;
                }
                continue;
            }

            $slug = trim((string) ($payload['slug'] ?? ''));
            if ($slug === '') {
                $slug = Str::slug($title, '-');
            }
            if ($slug === '') {
                $slug = "blog-{$blog->id}-{$locale}-" . Str::random(6);
            }
            $slug = $this->uniqueSlug($slug, $locale, $blog->id);

            $ogImagePath = null;
            if ($request->hasFile("translations.$locale.og_image")) {
                $ogImagePath = $request->file("translations.$locale.og_image")->store('blogs', 'public');
            }

            $schemaJson = $payload['schema_json'] ?? null;
            if (is_string($schemaJson) && trim($schemaJson) !== '') {
                $decoded = json_decode($schemaJson, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $schemaJson = $decoded;
                }
            }

            $blog->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $payload['excerpt'] ?? null,
                    'content' => $payload['content'] ?? '',
                    'seo_title' => $payload['seo_title'] ?? null,
                    'seo_description' => $payload['seo_description'] ?? null,
                    'seo_keywords' => $payload['seo_keywords'] ?? null,
                    'og_title' => $payload['og_title'] ?? null,
                    'og_description' => $payload['og_description'] ?? null,
                    'og_image' => $ogImagePath ?? ($payload['existing_og_image'] ?? null),
                    'canonical_url' => $payload['canonical_url'] ?? null,
                    'schema_json' => $schemaJson,
                ]
            );
        }
    }

    protected function uniqueSlug(string $slug, string $locale, int $blogId): string
    {
        $base = $slug;
        $counter = 2;

        while (
            BlogTranslation::where('locale', $locale)
                ->where('slug', $slug)
                ->where('blog_id', '!=', $blogId)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
