<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class CheapAccountsContentSeeder extends Seeder
{
    private const BRAND = 'cheapaccounts';
    private const LOCALES = ['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];
    private const JSON_FIELDS = ['guide_sections', 'faqs', 'highlights'];
    private const TEXT_FIELDS = ['hero_heading', 'hero_description', 'guide_heading', 'guide_intro'];

    public function run(): void
    {
        $payload = $this->payload();
        $this->validatePayload($payload);
        $this->requireTransactionalTable();

        DB::transaction(function () use ($payload): void {
            $existing = DB::table('landing_page_contents')->where('brand', self::BRAND)
                ->whereIn('locale', self::LOCALES)->orderBy('id')->lockForUpdate()->get();
            if ($existing->pluck('locale')->unique()->count() !== $existing->count()) {
                throw new RuntimeException('Duplicate Cheap Accounts language identity; no records were inserted.');
            }

            foreach ($existing as $record) {
                $expected = $payload['locales'][$record->locale];
                foreach (self::TEXT_FIELDS as $field) {
                    if ($record->{$field} !== $expected[$field]) {
                        throw new RuntimeException('Existing Cheap Accounts content differs; no records were inserted.');
                    }
                }
                foreach (self::JSON_FIELDS as $field) {
                    $value = json_decode($record->{$field}, true, 64, JSON_THROW_ON_ERROR);
                    if ($value !== $expected[$field]) {
                        throw new RuntimeException('Existing Cheap Accounts content differs; no records were inserted.');
                    }
                }
                if ((bool) $record->is_published !== $expected['is_published']) {
                    throw new RuntimeException('Existing Cheap Accounts visibility differs; no records were inserted.');
                }
            }

            $known = $existing->pluck('locale')->all();
            $timestamp = now()->format('Y-m-d H:i:s');
            $inserts = [];
            foreach (self::LOCALES as $locale) {
                if (in_array($locale, $known, true)) {
                    continue;
                }
                $row = $payload['locales'][$locale];
                foreach (self::JSON_FIELDS as $field) {
                    $row[$field] = json_encode($row[$field], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                }
                $inserts[] = $row + ['brand' => self::BRAND, 'locale' => $locale,
                    'created_at' => $timestamp, 'updated_at' => $timestamp];
            }
            if ($inserts && !DB::table('landing_page_contents')->insert($inserts)) {
                throw new RuntimeException('Cheap Accounts content insert failed; transaction rolled back.');
            }
        });
    }

    protected function payload(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/cheapaccounts-content.json')), true, 128, JSON_THROW_ON_ERROR);
    }

    private function requireTransactionalTable(): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() === 'sqlite') {
            return;
        }
        if ($connection->getDriverName() !== 'mysql') {
            throw new RuntimeException('Unsupported database driver; no records were inserted.');
        }
        $engine = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', $connection->getDatabaseName())
            ->where('TABLE_NAME', $connection->getTablePrefix().'landing_page_contents')->value('ENGINE');
        if (strtoupper((string) $engine) !== 'INNODB') {
            throw new RuntimeException('Landing content must use InnoDB; no records were inserted.');
        }
    }

    private function validatePayload(array $payload): void
    {
        if (($payload['version'] ?? null) !== 1 || ($payload['brand'] ?? null) !== self::BRAND
            || array_diff(array_keys($payload), ['version', 'brand', 'locales'])
            || !is_array($payload['locales'] ?? null)) {
            throw new RuntimeException('Unexpected Cheap Accounts content payload.');
        }
        $locales = array_keys($payload['locales']);
        $expected = self::LOCALES;
        sort($locales);
        sort($expected);
        if ($locales !== $expected) {
            throw new RuntimeException('All ten Cheap Accounts languages are required.');
        }
        $plain = static function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (strip_tags($value) !== $value || str_contains($value, "\0"))) {
                $fail('Landing content must be plain text.');
            }
        };
        $rules = [
            'locales.*' => ['required', 'array:hero_heading,hero_description,guide_heading,guide_intro,guide_sections,faqs,highlights,is_published'],
            'locales.*.is_published' => ['required', 'accepted'],
            'locales.*.guide_sections' => ['required', 'array', 'min:1', 'max:8'],
            'locales.*.guide_sections.*' => ['required', 'array:heading,body'],
            'locales.*.guide_sections.*.heading' => ['required', 'string', 'max:160', $plain],
            'locales.*.guide_sections.*.body' => ['required', 'string', 'max:4000', $plain],
            'locales.*.faqs' => ['required', 'array', 'min:1', 'max:8'],
            'locales.*.faqs.*' => ['required', 'array:question,answer'],
            'locales.*.faqs.*.question' => ['required', 'string', 'max:240', $plain],
            'locales.*.faqs.*.answer' => ['required', 'string', 'max:2000', $plain],
            'locales.*.highlights' => ['required', 'array', 'min:1', 'max:6'],
            'locales.*.highlights.*' => ['required', 'string', 'max:180', $plain],
        ];
        foreach (['hero_heading' => 160, 'hero_description' => 1200, 'guide_heading' => 160, 'guide_intro' => 2000] as $field => $limit) {
            $rules['locales.*.'.$field] = ['required', 'string', 'max:'.$limit, $plain];
        }
        Validator::make($payload, $rules)->validate();
        foreach ($payload['locales'] as $row) {
            if ($row['is_published'] !== true) {
                throw new RuntimeException('Cheap Accounts publication flags must be true booleans.');
            }
            foreach (self::JSON_FIELDS as $field) {
                if (!array_is_list($row[$field])) {
                    throw new RuntimeException('Cheap Accounts content groups must be JSON lists.');
                }
            }
        }
    }
}
