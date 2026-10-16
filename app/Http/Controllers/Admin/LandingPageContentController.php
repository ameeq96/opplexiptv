<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageContent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LandingPageContentController extends Controller
{
    public function edit(Request $request, string $brand)
    {
        abort_unless(isset(LandingPageContent::BRANDS[$brand]), 404);
        $locale = $this->locale($request);
        $page = LandingPageContent::query()->where('brand', $brand)->where('locale', $locale)->first();
        $section = $page ? $page->publicContent() + ['is_published' => $page->is_published] : [
            'hero_heading' => '', 'hero_description' => '', 'guide_heading' => '', 'guide_intro' => '',
            'guide_sections' => [], 'faqs' => [], 'highlights' => [], 'is_published' => false,
        ];

        return view('admin.landing-pages.edit', [
            'section' => $section,
            'brand' => $brand,
            'brands' => LandingPageContent::BRANDS,
            'locale' => $locale,
        ]);
    }

    public function update(Request $request, string $brand)
    {
        abort_unless(isset(LandingPageContent::BRANDS[$brand]), 404);
        $locale = $this->locale($request);
        $plainText = function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (strip_tags($value) !== $value || str_contains($value, "\0"))) {
                $fail('Use plain text only; HTML and scripts are not accepted.');
            }
        };
        $data = $request->validate([
            'hero_heading' => ['required', 'string', 'max:160', $plainText],
            'hero_description' => ['required', 'string', 'max:1200', $plainText],
            'guide_heading' => ['required', 'string', 'max:160', $plainText],
            'guide_intro' => ['required', 'string', 'max:2000', $plainText],
            'guide_sections' => ['required', 'array', 'max:8'],
            'guide_sections.*' => ['array:heading,body'],
            'guide_sections.*.heading' => ['nullable', 'string', 'max:160', $plainText],
            'guide_sections.*.body' => ['nullable', 'string', 'max:4000', $plainText],
            'faqs' => ['nullable', 'array', 'max:8'],
            'faqs.*' => ['array:question,answer'],
            'faqs.*.question' => ['nullable', 'string', 'max:240', $plainText],
            'faqs.*.answer' => ['nullable', 'string', 'max:2000', $plainText],
            'highlights' => ['nullable', 'array', 'max:6'],
            'highlights.*' => ['nullable', 'string', 'max:180', $plainText],
            'is_published' => ['required', 'boolean'],
        ]);

        $errors = [];
        foreach (['guide_sections' => ['heading', 'body'], 'faqs' => ['question', 'answer']] as $field => [$first, $second]) {
            $items = [];
            foreach ($data[$field] ?? [] as $index => $item) {
                $left = trim((string) ($item[$first] ?? ''));
                $right = trim((string) ($item[$second] ?? ''));
                if ($left === '' && $right === '') {
                    continue;
                }
                if ($left === '' || $right === '') {
                    $errors["$field.$index.".($left === '' ? $first : $second)] = 'Complete both fields, or leave both empty to remove this item.';
                }
                $items[] = [$first => $left, $second => $right];
            }
            $data[$field] = $items;
        }
        if (empty($data['guide_sections'])) {
            $errors['guide_sections'] = 'Add at least one complete guide section.';
        }
        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
        foreach (['hero_heading', 'hero_description', 'guide_heading', 'guide_intro'] as $field) {
            $data[$field] = trim($data[$field]);
        }
        $data['highlights'] = array_values(array_filter(array_map(
            static fn ($text) => trim((string) $text), $data['highlights'] ?? []
        ), static fn ($text) => $text !== ''));
        $data['is_published'] = (bool) $data['is_published'];

        LandingPageContent::query()->updateOrCreate(['brand' => $brand, 'locale' => $locale], $data);

        return redirect()->route('admin.landing-pages.edit', ['brand' => $brand, 'locale' => $locale])
            ->with('success', 'Landing page content saved. Prices and the main website content were not changed.');
    }

    private function locale(Request $request): string
    {
        $data = validator(['locale' => $request->input('locale', 'en')], [
            'locale' => ['required', 'string', Rule::in(config('app.locales', ['en']))],
        ])->validate();

        return $data['locale'];
    }
}
