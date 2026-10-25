<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class NewLandingBrandsSeeder extends Seeder
{
    private const BRANDS = ['iptvsubscription', 'getiptv'];
    private const LOCALES = ['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];
    private const JSON_FIELDS = ['guide_sections', 'faqs', 'highlights'];

    public function run(): void
    {
        $payload = $this->payload();
        $this->validatePayload($payload);
        $this->requireTransactionalTable();

        DB::transaction(function () use ($payload): void {
            $existing = DB::table('landing_page_contents')->whereIn('brand', self::BRANDS)
                ->whereIn('locale', self::LOCALES)->orderBy('id')->lockForUpdate()->get();
            $keys = $existing->map(fn ($row) => $row->brand.':'.$row->locale);
            if ($keys->unique()->count() !== $existing->count()) {
                throw new RuntimeException('Duplicate new-brand content identity; no records were inserted.');
            }

            $timestamp = now()->format('Y-m-d H:i:s');
            $inserts = [];
            foreach ($payload['rows'] as $row) {
                if ($keys->contains($row['brand'].':'.$row['locale'])) {
                    continue;
                }
                foreach (self::JSON_FIELDS as $field) {
                    $row[$field] = json_encode($row[$field], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                }
                $inserts[] = $row + ['created_at' => $timestamp, 'updated_at' => $timestamp];
            }
            if ($inserts && ! DB::table('landing_page_contents')->insert($inserts)) {
                throw new RuntimeException('New-brand content insert failed; transaction rolled back.');
            }
        });
    }

    protected function payload(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/new-landing-brands.json')), true, 128, JSON_THROW_ON_ERROR);
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
        if (($payload['version'] ?? null) !== 1 || array_diff(array_keys($payload), ['version', 'rows'])) {
            throw new RuntimeException('Unexpected new-brand content payload.');
        }
        $plain = static function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (strip_tags($value) !== $value || str_contains($value, "\0"))) {
                $fail('Landing content must be plain text.');
            }
        };
        $rules = [
            'rows' => ['required', 'array', 'size:20'],
            'rows.*' => ['required', 'array:brand,locale,hero_heading,hero_description,guide_heading,guide_intro,guide_sections,faqs,highlights,is_published'],
            'rows.*.brand' => ['required', Rule::in(self::BRANDS)],
            'rows.*.locale' => ['required', Rule::in(self::LOCALES)],
            'rows.*.is_published' => ['required', 'accepted'],
            'rows.*.guide_sections' => ['required', 'array', 'min:1', 'max:8'],
            'rows.*.guide_sections.*' => ['required', 'array:heading,body'],
            'rows.*.guide_sections.*.heading' => ['required', 'string', 'max:160', $plain],
            'rows.*.guide_sections.*.body' => ['required', 'string', 'max:4000', $plain],
            'rows.*.faqs' => ['required', 'array', 'min:1', 'max:8'],
            'rows.*.faqs.*' => ['required', 'array:question,answer'],
            'rows.*.faqs.*.question' => ['required', 'string', 'max:240', $plain],
            'rows.*.faqs.*.answer' => ['required', 'string', 'max:2000', $plain],
            'rows.*.highlights' => ['required', 'array', 'min:1', 'max:6'],
            'rows.*.highlights.*' => ['required', 'string', 'max:180', $plain],
        ];
        foreach (['hero_heading' => 160, 'hero_description' => 1200, 'guide_heading' => 160, 'guide_intro' => 2000] as $field => $limit) {
            $rules['rows.*.'.$field] = ['required', 'string', 'max:'.$limit, $plain];
        }
        Validator::make($payload, $rules)->validate();
        $keys = array_map(fn ($row) => $row['brand'].':'.$row['locale'], $payload['rows']);
        if (count(array_unique($keys)) !== 20) {
            throw new RuntimeException('Each approved new brand/language pair must appear exactly once.');
        }
    }
}
