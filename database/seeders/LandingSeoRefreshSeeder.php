<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class LandingSeoRefreshSeeder extends Seeder
{
    private const FIELDS = ['hero_heading', 'hero_description', 'guide_heading', 'guide_intro', 'guide_sections', 'faqs', 'highlights'];
    private const JSON_FIELDS = ['guide_sections', 'faqs', 'highlights'];
    private const LOCALES = ['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];
    private const TITLES = ['trex' => 'TREX IPTV', 'opplex' => 'Opplex iptv', 'geo' => 'GEO World IPTV', '5g' => '5G IPTV'];
    private const PACKAGE_IDS = [
        'live' => ['trex' => [12 => 87], 'opplex' => [1 => 62, 3 => 63, 6 => 64, 12 => 65], 'geo' => [1 => 49, 3 => 50, 6 => 51, 12 => 52], '5g' => [1 => 17, 3 => 18, 6 => 19, 12 => 20]],
        'local' => ['trex' => [12 => 148], 'opplex' => [1 => 13, 3 => 14, 6 => 15, 12 => 16], 'geo' => [1 => 128, 3 => 129, 6 => 130, 12 => 131], '5g' => [1 => 119, 3 => 120, 6 => 121, 12 => 122]],
    ];

    public function run(): void
    {
        $payload = $this->payload();
        $this->validatePayload($payload);
        $this->requireTransactionalTables();
        DB::transaction(function () use ($payload): void {
            $pages = DB::table('landing_page_contents')->whereIn('brand', array_keys(self::TITLES))
                ->whereIn('locale', self::LOCALES)->orderBy('id')->lockForUpdate()->get();
            if ($pages->count() !== 40) {
                throw new RuntimeException('Expected all 40 existing landing content records; no records were changed.');
            }
            $pagesByKey = $pages->keyBy(fn ($page) => $page->brand.':'.$page->locale);
            if ($pagesByKey->count() !== 40) {
                throw new RuntimeException('Duplicate landing content identity; no records were changed.');
            }
            $packageIds = array_merge(...array_map(fn ($profile) => array_column($profile, 'id'), array_values($payload['package_profiles'])));
            $packages = DB::table('packages')->whereIn('id', $packageIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $matchingProfiles = [];
            foreach ($payload['package_profiles'] as $name => $profile) {
                if (collect($profile)->every(fn ($expected) => $this->matchesPackage($packages->get($expected['id']), $expected))) {
                    $matchingProfiles[$name] = $profile;
                }
            }
            if (count($matchingProfiles) !== 1) {
                throw new RuntimeException('Package profile is missing, mismatched or ambiguous; no records were changed.');
            }
            $profileName = array_key_first($matchingProfiles);
            $profile = $matchingProfiles[$profileName];
            $packageChanges = [];
            foreach ($profile as $expected) {
                $package = $packages->get($expected['id']);
                if ($package->max_devices !== null && (string) $package->max_devices !== '1') {
                    throw new RuntimeException('Unexpected connection allowance on package '.$expected['id'].'; no records were changed.');
                }
                if ($package->max_devices === null) {
                    $packageChanges[] = $expected['id'];
                }
            }
            $pageChanges = [];
            foreach ($payload['rows'] as $row) {
                $key = $row['brand'].':'.$row['locale'];
                $page = $pagesByKey->get($key);
                if (! $page) {
                    throw new RuntimeException('Missing landing content '.$key.'; no records were changed.');
                }
                $current = $this->presentation($page);
                if ($this->fingerprint($current) === $this->fingerprint($row['after'])) {
                    continue;
                }
                if ($this->fingerprint($current) !== $this->fingerprint($row['before'])) {
                    throw new RuntimeException('Landing content changed since approval: '.$key.'; no records were changed.');
                }
                $pageChanges[$page->id] = $row['after'];
            }
            if (! $pageChanges && ! $packageChanges) {
                return;
            }

            // Full original rows permit a scoped restore, including fields not changed by this seeder.
            $backup = $this->writeBackup([
                'version' => 1, 'created_at' => now()->toIso8601String(), 'profile' => $profileName,
                'payload_sha256' => $this->fingerprint($payload),
                'landing_page_contents' => $pages->map(fn ($page) => (array) $page)->all(),
                'packages' => array_map(fn ($expected) => (array) $packages->get($expected['id']), $profile),
            ]);
            $this->command?->line($backup);
            $updatedAt = now()->format('Y-m-d H:i:s');
            foreach ($pageChanges as $id => $content) {
                foreach (self::JSON_FIELDS as $field) {
                    $content[$field] = json_encode($content[$field], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                }
                if (DB::table('landing_page_contents')->where('id', $id)->update($content + ['updated_at' => $updatedAt]) !== 1) {
                    throw new RuntimeException('Landing content update failed; transaction rolled back.');
                }
            }
            foreach ($packageChanges as $id) {
                if (DB::table('packages')->where('id', $id)->whereNull('max_devices')->update(['max_devices' => 1]) !== 1) {
                    throw new RuntimeException('Package allowance update failed; transaction rolled back.');
                }
            }
        });
    }

    protected function payload(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/landing-seo-refresh.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function requireTransactionalTables(): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() === 'sqlite') {
            return;
        }
        if ($connection->getDriverName() !== 'mysql') {
            throw new RuntimeException('Unsupported database driver; no records were changed.');
        }
        $tables = array_map(fn ($table) => $connection->getTablePrefix().$table, ['landing_page_contents', 'packages']);
        $engines = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', $connection->getDatabaseName())
            ->whereIn('TABLE_NAME', $tables)->pluck('ENGINE');
        if ($engines->count() !== 2 || ! $engines->every(fn ($engine) => strtoupper((string) $engine) === 'INNODB')) {
            throw new RuntimeException('Both affected tables must use InnoDB; no records were changed.');
        }
    }

    private function validatePayload(array $payload): void
    {
        if (($payload['version'] ?? null) !== 1 || ! isset($payload['package_profiles']) || array_keys($payload['package_profiles']) !== array_keys(self::PACKAGE_IDS)) {
            throw new RuntimeException('Unexpected refresh payload version or package profiles.');
        }
        $suffixes = [1 => '', 3 => ' - 3 Months', 6 => ' - Half Yearly', 12 => ' - Yearly'];
        foreach (self::PACKAGE_IDS as $name => $brands) {
            $expected = [];
            foreach ($brands as $brand => $plans) {
                foreach ($plans as $months => $id) {
                    $expected[] = [
                        'id' => $id, 'brand' => $brand, 'title' => self::TITLES[$brand].$suffixes[$months],
                        'type' => 'iptv', 'vendor' => 'opplex', 'duration_months' => $months,
                        'before_max_devices' => null, 'after_max_devices' => 1,
                    ];
                }
            }
            if ($this->fingerprint($expected) !== $this->fingerprint($payload['package_profiles'][$name])) {
                throw new RuntimeException('Package inventory differs from the approved '.$name.' profile.');
            }
        }
        $plain = static function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (strip_tags($value) !== $value || str_contains($value, "\0"))) {
                $fail('Landing content must be plain text.');
            }
        };
        $rules = [
            'rows' => ['required', 'array', 'size:40'],
            'rows.*' => ['required', 'array:brand,locale,before,after'],
            'rows.*.brand' => ['required', Rule::in(array_keys(self::TITLES))],
            'rows.*.locale' => ['required', Rule::in(self::LOCALES)],
        ];
        foreach (['before', 'after'] as $state) {
            $prefix = 'rows.*.'.$state;
            $rules[$prefix] = ['required', 'array:'.implode(',', self::FIELDS)];
            foreach (['hero_heading' => 160, 'hero_description' => 1200, 'guide_heading' => 160, 'guide_intro' => 2000] as $field => $limit) {
                $rules[$prefix.'.'.$field] = ['required', 'string', 'max:'.$limit, $plain];
            }
            $rules += [
                $prefix.'.guide_sections' => ['required', 'array', 'min:1', 'max:8'],
                $prefix.'.guide_sections.*' => ['required', 'array:heading,body'],
                $prefix.'.guide_sections.*.heading' => ['required', 'string', 'max:160', $plain],
                $prefix.'.guide_sections.*.body' => ['required', 'string', 'max:4000', $plain],
                $prefix.'.faqs' => ['required', 'array', 'min:1', 'max:8'],
                $prefix.'.faqs.*' => ['required', 'array:question,answer'],
                $prefix.'.faqs.*.question' => ['required', 'string', 'max:240', $plain],
                $prefix.'.faqs.*.answer' => ['required', 'string', 'max:2000', $plain],
                $prefix.'.highlights' => ['required', 'array', 'min:1', 'max:6'],
                $prefix.'.highlights.*' => ['required', 'string', 'max:180', $plain],
            ];
        }
        Validator::make($payload, $rules)->validate();
        $keys = array_map(fn ($row) => $row['brand'].':'.$row['locale'], $payload['rows']);
        if (count(array_unique($keys)) !== 40) {
            throw new RuntimeException('Each of the 40 approved brand/language pairs must appear once.');
        }
    }

    private function matchesPackage(?object $package, array $expected): bool
    {
        return $package !== null && $package->title === $expected['title']
            && $package->type === $expected['type'] && $package->vendor === $expected['vendor']
            && (string) $package->duration_months === (string) $expected['duration_months'];
    }

    private function presentation(object $page): array
    {
        $content = [];
        foreach (self::FIELDS as $field) {
            $content[$field] = in_array($field, self::JSON_FIELDS, true)
                ? json_decode($page->{$field}, true, 512, JSON_THROW_ON_ERROR) : $page->{$field};
        }

        return $content;
    }

    private function fingerprint(array $value): string
    {
        $normalize = static function (mixed $item) use (&$normalize): mixed {
            if (is_array($item)) {
                if (! array_is_list($item)) {
                    ksort($item);
                }
                return array_map($normalize, $item);
            }
            return $item;
        };

        return hash('sha256', json_encode($normalize($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    protected function backupDirectory(): string
    {
        return storage_path('app/private/landing-seo-refresh');
    }

    protected function writeBackup(array $snapshot): string
    {
        $previousMask = umask(0077);
        $handle = null;
        try {
            $directory = $this->backupDirectory();
            if (is_link($directory) || (! is_dir($directory) && ! @mkdir($directory, 0700, true))) {
                throw new RuntimeException('Could not create a private backup directory; no records were changed.');
            }
            $resolved = realpath($directory);
            $privateRoot = realpath(storage_path('app')).DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR;
            if (! $resolved || ! str_starts_with($resolved.DIRECTORY_SEPARATOR, $privateRoot) || ! @chmod($directory, 0700)) {
                throw new RuntimeException('Backup directory is not private; no records were changed.');
            }
            $path = $resolved.DIRECTORY_SEPARATOR.'landing-seo-refresh-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(12)).'.json';
            $handle = @fopen($path, 'x+b');
            if (! $handle || ! @chmod($path, 0600)) {
                throw new RuntimeException('Could not create an exclusive private backup; no records were changed.');
            }
            clearstatcache(true, $path);
            if (PHP_OS_FAMILY !== 'Windows' && (fileperms($path) & 0777) !== 0600) {
                throw new RuntimeException('Backup permissions are not private; no records were changed.');
            }
            $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            $offset = 0;
            while ($offset < strlen($json)) {
                $written = fwrite($handle, substr($json, $offset));
                if ($written === false || $written === 0) {
                    throw new RuntimeException('Backup write failed; no records were changed.');
                }
                $offset += $written;
            }
            if (! fflush($handle) || (function_exists('fsync') && ! fsync($handle))) {
                throw new RuntimeException('Backup flush failed; no records were changed.');
            }
            rewind($handle);
            if (stream_get_contents($handle) !== $json) {
                throw new RuntimeException('Backup verification failed; no records were changed.');
            }

            return $path;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            umask($previousMask);
        }
    }
}
