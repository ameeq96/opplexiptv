<?php

namespace Database\Seeders;

use App\Models\LandingPageContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LandingPageTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(file_get_contents(database_path('seeders/data/landing-page-translations.json')), true, 512, JSON_THROW_ON_ERROR);
        $locales = ['es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];
        $plain = function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (strip_tags($value) !== $value || str_contains($value, "\0"))) {
                $fail('Translation content must be plain text.');
            }
        };
        Validator::make(['rows' => $rows], [
            'rows' => ['required', 'array', 'size:36'],
            'rows.*' => ['array:brand,locale,hero_heading,hero_description,guide_heading,guide_intro,guide_sections,faqs,highlights'],
            'rows.*.brand' => ['required', Rule::in(array_keys(LandingPageContent::BRANDS))],
            'rows.*.locale' => ['required', Rule::in($locales)],
            'rows.*.hero_heading' => ['required', 'string', 'max:160', $plain],
            'rows.*.hero_description' => ['required', 'string', 'max:1200', $plain],
            'rows.*.guide_heading' => ['required', 'string', 'max:160', $plain],
            'rows.*.guide_intro' => ['required', 'string', 'max:2000', $plain],
            'rows.*.guide_sections' => ['required', 'array', 'min:1', 'max:8'],
            'rows.*.guide_sections.*' => ['array:heading,body'],
            'rows.*.guide_sections.*.heading' => ['required', 'string', 'max:160', $plain],
            'rows.*.guide_sections.*.body' => ['required', 'string', 'max:4000', $plain],
            'rows.*.faqs' => ['required', 'array', 'max:8'],
            'rows.*.faqs.*' => ['array:question,answer'],
            'rows.*.faqs.*.question' => ['required', 'string', 'max:240', $plain],
            'rows.*.faqs.*.answer' => ['required', 'string', 'max:2000', $plain],
            'rows.*.highlights' => ['required', 'array', 'max:6'],
            'rows.*.highlights.*' => ['required', 'string', 'max:180', $plain],
        ])->validate();
        $keys = array_map(static fn (array $row): string => $row['brand'].':'.$row['locale'], $rows);
        if (count(array_unique($keys)) !== 36) {
            throw new \RuntimeException('Each brand/language pair must appear exactly once.');
        }

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                LandingPageContent::firstOrCreate(
                    ['brand' => $row['brand'], 'locale' => $row['locale']],
                    $row + ['is_published' => true]
                );
            }
        });
    }
}
