<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        $query = Testimonial::query();

        if ($search !== '') {
            $query->where('author_name', 'like', "%{$search}%")
                ->orWhere('text', 'like', "%{$search}%");
        }

        if ($status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $testimonials = $query->orderBy('sort_order')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('admin.testimonials.create', [
            'testimonial' => new Testimonial(),
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['image'] = $this->storeImage($request);

        $testimonial = Testimonial::create($data);
        $this->syncTranslations($testimonial, $request);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', [
            'testimonial' => $testimonial,
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $this->validateData($request, false);

        if ($request->hasFile('image')) {
            $this->deleteImage($testimonial->image);
            $data['image'] = $this->storeImage($request);
        }

        $testimonial->update($data);
        $this->syncTranslations($testimonial, $request);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->deleteImage($testimonial->image);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    private function validateData(Request $request, bool $isCreate): array
    {
        $rules = [
            'author_name' => ['required', 'string', 'max:120'],
            'text' => ['required', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.author_name"] = ['nullable', 'string', 'max:120'];
            $rules["translations.$locale.text"] = ['nullable', 'string', 'max:1000'];
        }

        $rules['image'] = $isCreate
            ? ['required', 'image', 'mimes:webp,jpg,jpeg,png', 'max:2048']
            : ['nullable', 'image', 'mimes:webp,jpg,jpeg,png', 'max:2048'];

        $data = $request->validate($rules);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function syncTranslations(Testimonial $testimonial, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $author = trim((string) ($payload['author_name'] ?? ''));
            $text = trim((string) ($payload['text'] ?? ''));

            if ($author === '' && $text === '') {
                $testimonial->translations()->where('locale', $locale)->delete();
                continue;
            }

            $testimonial->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'author_name' => $author ?: null,
                    'text' => $text ?: null,
                ]
            );
        }
    }

    private function storeImage(Request $request): string
    {
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $filename = 'testimonial-' . Str::random(8) . '-' . time() . '.' . $ext;

        $dest = public_path('images/resource');
        if (!is_dir($dest)) {
            mkdir($dest, 0775, true);
        }

        $file->move($dest, $filename);

        return 'images/resource/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }
        $full = public_path($path);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
