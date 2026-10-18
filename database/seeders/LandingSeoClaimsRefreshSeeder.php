<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class LandingSeoClaimsRefreshSeeder extends LandingSeoRefreshSeeder
{
    private const PACKAGE_IDS = [17, 18, 19, 20, 49, 50, 51, 52, 62, 63, 64, 65, 87];
    private const INSTANT_ACTIVATION_IDS = [62, 63, 64, 65];
    private const PAYLOAD_SHA256 = '2d8f3a4c0ce385c4226a20d3c51b47925dbb12d8403b8ab49decd18f97de39e4';

    public function run(): void
    {
        $payload = $this->payload();
        if ($this->fingerprint($payload) !== self::PAYLOAD_SHA256
            || ($payload['package_ids'] ?? null) !== self::PACKAGE_IDS
            || ($payload['package_translations'] ?? null) !== []) {
            throw new RuntimeException('Claims refresh payload differs from the approved snapshot; no records were changed.');
        }
        $this->requireTransactionalTables();

        DB::transaction(function () use ($payload): void {
            $packages = DB::table('packages')->whereIn('id', self::PACKAGE_IDS)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $translations = DB::table('package_translations')->whereIn('package_id', self::PACKAGE_IDS)
                ->orderBy('id')->lockForUpdate()->get();
            if ($packages->count() !== count(self::PACKAGE_IDS)) {
                throw new RuntimeException('Expected all 13 approved live package IDs; no records were changed.');
            }
            if ($translations->isNotEmpty()) {
                throw new RuntimeException('Unexpected translations exist for the approved package IDs; no records were changed.');
            }

            $changes = [];
            foreach ($payload['rows'] as $expected) {
                $package = $packages->get($expected['id']);
                if (! $this->matchesIdentity($package, $expected)) {
                    throw new RuntimeException('Package identity changed since approval: '.$expected['id'].'; no records were changed.');
                }
                $current = $this->claims($package);
                if ($current === $expected['after']) {
                    continue;
                }
                if ($current !== $expected['before']) {
                    throw new RuntimeException('Package claims changed since approval: '.$expected['id'].'; no records were changed.');
                }
                $changes[$expected['id']] = [
                    'features' => json_encode($expected['after']['features'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                ];
                if (in_array($expected['id'], self::INSTANT_ACTIVATION_IDS, true)) {
                    $changes[$expected['id']]['instant_activation'] = $expected['after']['instant_activation'];
                }
            }
            if (! $changes) {
                return;
            }

            $snapshot = [
                'version' => 1,
                'created_at' => now()->toIso8601String(),
                'profile' => 'live-approved-ids',
                'payload_sha256' => self::PAYLOAD_SHA256,
                'packages' => $packages->map(fn ($package) => (array) $package)->values()->all(),
                'package_translations' => [],
            ];
            $backup = $this->writeBackup($snapshot);
            $this->verifyBackup($backup, $snapshot);
            $this->command?->line($backup);

            foreach ($changes as $id => $attributes) {
                if (DB::table('packages')->where('id', $id)->update($attributes) !== 1) {
                    throw new RuntimeException('Package claims update failed; transaction rolled back.');
                }
            }

            $updated = DB::table('packages')->whereIn('id', self::PACKAGE_IDS)->orderBy('id')->get()->keyBy('id');
            foreach ($packages as $id => $original) {
                $actual = $updated->get($id);
                if (! $actual) {
                    throw new RuntimeException('Package disappeared during refresh; transaction rolled back.');
                }
                $before = (array) $original;
                $after = (array) $actual;
                if (isset($changes[$id])) {
                    if (json_decode($after['features'], true, 512, JSON_THROW_ON_ERROR)
                        !== json_decode($changes[$id]['features'], true, 512, JSON_THROW_ON_ERROR)) {
                        throw new RuntimeException('Package feature verification failed; transaction rolled back.');
                    }
                    unset($before['features'], $after['features']);
                    if (array_key_exists('instant_activation', $changes[$id])) {
                        if ((string) $after['instant_activation'] !== (string) $changes[$id]['instant_activation']) {
                            throw new RuntimeException('Package activation verification failed; transaction rolled back.');
                        }
                        unset($before['instant_activation'], $after['instant_activation']);
                    }
                }
                if ($before !== $after) {
                    throw new RuntimeException('Unapproved package columns changed; transaction rolled back.');
                }
            }
            if (DB::table('package_translations')->whereIn('package_id', self::PACKAGE_IDS)->exists()) {
                throw new RuntimeException('Package translations changed during refresh; transaction rolled back.');
            }
        });
    }

    protected function payload(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/landing-seo-claims-refresh.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function backupDirectory(): string
    {
        return storage_path('app/private/landing-seo-claims-refresh');
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
        $tables = array_map(fn ($table) => $connection->getTablePrefix().$table, ['packages', 'package_translations']);
        $engines = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', $connection->getDatabaseName())
            ->whereIn('TABLE_NAME', $tables)->pluck('ENGINE');
        if ($engines->count() !== 2 || ! $engines->every(fn ($engine) => strtoupper((string) $engine) === 'INNODB')) {
            throw new RuntimeException('Both package tables must use InnoDB; no records were changed.');
        }
    }

    private function matchesIdentity(?object $package, array $expected): bool
    {
        return $package !== null && $package->title === $expected['title']
            && $package->type === $expected['type'] && $package->vendor === $expected['vendor']
            && (string) $package->duration_months === (string) $expected['duration_months']
            && (string) $package->max_devices === (string) $expected['max_devices']
            && is_numeric($package->price_amount)
            && preg_match('/^\d+(?:\.\d{1,2})?$/D', (string) $package->price_amount)
            && number_format((float) $package->price_amount, 2, '.', '') === $expected['price_amount'];
    }

    private function claims(object $package): array
    {
        if (! in_array((string) $package->instant_activation, ['0', '1'], true)) {
            throw new RuntimeException('Unexpected activation flag on package '.$package->id.'; no records were changed.');
        }

        return [
            'features' => json_decode($package->features, true, 512, JSON_THROW_ON_ERROR),
            'instant_activation' => (int) $package->instant_activation,
        ];
    }

    private function fingerprint(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function verifyBackup(string $path, array $snapshot): void
    {
        $private = realpath(storage_path('app/private'));
        $resolved = realpath($path);
        if (! $private || ! $resolved || is_link($path)
            || ! str_starts_with($resolved, $private.DIRECTORY_SEPARATOR)
            || ! is_file($resolved) || ! is_readable($resolved)
            || (PHP_OS_FAMILY !== 'Windows' && (fileperms($resolved) & 0777) !== 0600)) {
            throw new RuntimeException('Private backup verification failed; no records were changed.');
        }
        $saved = json_decode(file_get_contents($resolved), true, 512, JSON_THROW_ON_ERROR);
        if ($this->fingerprint($saved) !== $this->fingerprint($snapshot)) {
            throw new RuntimeException('Backup contents verification failed; no records were changed.');
        }
    }
}
