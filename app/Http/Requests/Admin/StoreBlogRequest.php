<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:600'],
            'is_featured' => ['nullable', 'boolean'],
            'translations' => ['array'],
        ];

        foreach (admin_locales() as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.slug"] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('blog_translations', 'slug')->where('locale', $locale),
            ];
            $rules["translations.$locale.excerpt"] = ['nullable', 'string', 'max:500'];
            $rules["translations.$locale.content"] = ['nullable', 'string'];
            $rules["translations.$locale.seo_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.seo_description"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.seo_keywords"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.og_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.og_description"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.og_image"] = ['nullable', 'image', 'max:4096'];
            $rules["translations.$locale.canonical_url"] = ['nullable', 'url', 'max:255'];
            $rules["translations.$locale.schema_json"] = ['nullable'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasTitle = false;
            foreach (admin_locales() as $locale) {
                $title = trim((string) $this->input("translations.$locale.title", ''));
                $content = trim((string) $this->input("translations.$locale.content", ''));
                if ($title !== '') {
                    $hasTitle = true;
                    if ($content === '') {
                        $validator->errors()->add("translations.$locale.content", "Content is required when title is provided for $locale.");
                    }
                }
            }
            if (!$hasTitle) {
                $validator->errors()->add('translations', 'At least one locale title is required.');
            }
        });
    }
}
