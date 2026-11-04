<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheapAccountsSeoRefreshSeeder extends Seeder
{
    private const BRAND = 'cheapaccounts';
    private const LOCALES = ['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];
    private const HEADINGS_SHA256 = '263f105e46b3dcb210fe4fa676fcd071a8edb9ba0c8d0bdfc74ff548653abfff';
    private const BEFORE_HEADINGS = [
        'en' => 'Digital plans. Clear choices.',
        'es' => 'Planes digitales. Opciones claras.',
        'fr' => 'Des offres numériques. Un choix clair.',
        'it' => 'Piani digitali. Scelte chiare.',
        'nl' => 'Digitale abonnementen. Duidelijke keuzes.',
        'pt' => 'Planos digitais. Escolhas claras.',
        'ru' => 'Цифровые планы. Понятный выбор.',
        'ur' => 'ڈیجیٹل پلانز۔ واضح انتخاب۔',
        'ar' => 'خطط رقمية. خيارات واضحة.',
        'hi' => 'डिजिटल प्लान। स्पष्ट विकल्प।',
    ];

    public function run(): void
    {
        $headings = $this->headings();
        $this->requireTransactionalTable();

        DB::transaction(function () use ($headings): void {
            $rows = DB::table('landing_page_contents')->where('brand', self::BRAND)
                ->orderBy('id')->lockForUpdate()->get();
            $locales = $rows->pluck('locale')->all();
            $expected = self::LOCALES;
            sort($locales);
            sort($expected);
            if ($rows->count() !== count(self::LOCALES) || $locales !== $expected) {
                throw new RuntimeException('Expected exactly ten distinct Cheap Accounts locales; no records were changed.');
            }

            $changes = [];
            foreach ($rows as $row) {
                if ($row->hero_heading === $headings[$row->locale]) {
                    continue;
                }
                if ($row->hero_heading !== self::BEFORE_HEADINGS[$row->locale]) {
                    throw new RuntimeException('Cheap Accounts heading changed since approval: '.$row->locale.'; no records were changed.');
                }
                $changes[$row->id] = $headings[$row->locale];
            }
            if (! $changes) {
                return;
            }

            $snapshot = [
                'version' => 1,
                'created_at' => now()->toIso8601String(),
                'brand' => self::BRAND,
                'headings_sha256' => self::HEADINGS_SHA256,
                'rows' => $rows->map(fn ($row) => (array) $row)->all(),
                'after_headings' => $headings,
            ];
            $backup = $this->writeBackup($snapshot);
            if (json_decode(file_get_contents($backup), true, 128, JSON_THROW_ON_ERROR) !== $snapshot) {
                throw new RuntimeException('Cheap Accounts backup verification failed; no records were changed.');
            }
            $this->command?->line($backup);

            foreach ($rows as $row) {
                if (! isset($changes[$row->id])) {
                    continue;
                }
                if (DB::table('landing_page_contents')->where('id', $row->id)->where('brand', self::BRAND)
                    ->where('locale', $row->locale)->where('hero_heading', $row->hero_heading)
                    ->update(['hero_heading' => $changes[$row->id]]) !== 1) {
                    throw new RuntimeException('Cheap Accounts heading update failed; transaction rolled back.');
                }
            }

            $updated = DB::table('landing_page_contents')->where('brand', self::BRAND)->orderBy('id')->get()->keyBy('id');
            if ($updated->count() !== $rows->count()) {
                throw new RuntimeException('Cheap Accounts row identity changed; transaction rolled back.');
            }
            foreach ($rows as $row) {
                $actual = $updated->get($row->id);
                if (! $actual || $actual->hero_heading !== $headings[$row->locale]) {
                    throw new RuntimeException('Cheap Accounts heading verification failed; transaction rolled back.');
                }
                $before = (array) $row;
                $after = (array) $actual;
                unset($before['hero_heading'], $after['hero_heading']);
                if ($before !== $after) {
                    throw new RuntimeException('Unapproved Cheap Accounts columns changed; transaction rolled back.');
                }
            }
        });
    }

    protected function payload(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/cheapaccounts-content.json')), true, 128, JSON_THROW_ON_ERROR);
    }

    protected function backupDirectory(): string
    {
        return storage_path('app/private/cheapaccounts-seo-refresh');
    }

    protected function writeBackup(array $snapshot): string
    {
        $directory = $this->backupDirectory();
        if (is_link($directory) || (! is_dir($directory) && ! mkdir($directory, 0700, true))) {
            throw new RuntimeException('Cannot create the private Cheap Accounts backup directory.');
        }
        if (! chmod($directory, 0700)) {
            throw new RuntimeException('Cannot restrict the Cheap Accounts backup directory.');
        }
        $path = $directory.DIRECTORY_SEPARATOR.'headings-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(8)).'.json';
        $handle = fopen($path, 'x');
        if ($handle === false) {
            throw new RuntimeException('Cannot create the Cheap Accounts backup.');
        }
        try {
            if (! chmod($path, 0600)) {
                throw new RuntimeException('Cannot restrict the Cheap Accounts backup.');
            }
            $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            if (fwrite($handle, $json) !== strlen($json) || ! fflush($handle)) {
                throw new RuntimeException('Cannot write the complete Cheap Accounts backup.');
            }
        } finally {
            fclose($handle);
        }

        return $path;
    }

    private function headings(): array
    {
        $payload = $this->payload();
        $locales = array_keys($payload['locales'] ?? []);
        $expected = self::LOCALES;
        sort($locales);
        sort($expected);
        if (($payload['version'] ?? null) !== 1 || ($payload['brand'] ?? null) !== self::BRAND || $locales !== $expected) {
            throw new RuntimeException('Unexpected Cheap Accounts heading payload; no records were changed.');
        }
        $headings = [];
        foreach (self::LOCALES as $locale) {
            $heading = $payload['locales'][$locale]['hero_heading'] ?? null;
            if (! is_string($heading) || ! trim($heading) || mb_strlen($heading) > 160
                || strip_tags($heading) !== $heading || str_contains($heading, "\0")) {
                throw new RuntimeException('Invalid Cheap Accounts heading; no records were changed.');
            }
            $headings[$locale] = $heading;
        }
        if (hash('sha256', json_encode($headings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)) !== self::HEADINGS_SHA256) {
            throw new RuntimeException('Cheap Accounts headings differ from the approved snapshot; no records were changed.');
        }

        return $headings;
    }

    private function requireTransactionalTable(): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() === 'sqlite') {
            return;
        }
        if ($connection->getDriverName() !== 'mysql') {
            throw new RuntimeException('Unsupported database driver; no records were changed.');
        }
        $engine = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', $connection->getDatabaseName())
            ->where('TABLE_NAME', $connection->getTablePrefix().'landing_page_contents')->value('ENGINE');
        if (strtoupper((string) $engine) !== 'INNODB') {
            throw new RuntimeException('Landing content must use InnoDB; no records were changed.');
        }
    }
}
