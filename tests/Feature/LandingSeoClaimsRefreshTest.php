<?php

namespace Tests\Feature;

use Database\Seeders\LandingSeoClaimsRefreshSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class LandingSeoClaimsRefreshTest extends TestCase
{
    private array $payload;
    private string $backupDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
        DB::purge('sqlite');
        Http::preventStrayRequests();
        Bus::fake();
        Mail::fake();
        Notification::fake();
        $this->payload = json_decode(file_get_contents(database_path('seeders/data/landing-seo-claims-refresh.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->backupDirectory = storage_path('app/private/landing-seo-claims-test-'.bin2hex(random_bytes(12)));
        Schema::create('packages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('vendor');
            $table->integer('duration_months');
            $table->decimal('price_amount', 10, 2);
            $table->integer('max_devices')->nullable();
            $table->text('features');
            $table->boolean('instant_activation');
            $table->boolean('active');
            $table->boolean('is_available');
            $table->string('internal_note');
            $table->timestamps();
        });
        Schema::create('package_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->string('locale', 10);
            $table->string('title')->nullable();
            $table->text('features')->nullable();
            $table->timestamps();
            $table->unique(['package_id', 'locale']);
        });
        foreach ($this->payload['rows'] as $index => $row) {
            DB::table('packages')->insert([
                'id' => $row['id'], 'title' => $row['title'], 'type' => $row['type'], 'vendor' => $row['vendor'],
                'duration_months' => $row['duration_months'], 'price_amount' => $row['price_amount'], 'max_devices' => $row['max_devices'],
                'features' => json_encode($row['before']['features'], JSON_THROW_ON_ERROR),
                'instant_activation' => $row['before']['instant_activation'],
                'active' => $index % 2 === 0, 'is_available' => true, 'internal_note' => 'Preserve setting '.$index,
                'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-02 00:00:00',
            ]);
        }
        $other = (array) DB::table('packages')->first();
        DB::table('packages')->insert(array_replace($other, ['id' => 9999, 'title' => 'Unrelated provider', 'max_devices' => 5]));
        DB::table('package_translations')->insert([
            'package_id' => 9999, 'locale' => 'fr', 'title' => 'Unrelated translation', 'features' => '["Keep this"]',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-02 00:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        if (isset($this->backupDirectory) && is_dir($this->backupDirectory)) {
            foreach (glob($this->backupDirectory.DIRECTORY_SEPARATOR.'*.json') ?: [] as $path) {
                unlink($path);
            }
            rmdir($this->backupDirectory);
        }
        DB::purge('sqlite');
        parent::tearDown();
    }

    private function seeder(?array $payload = null, string $backupMode = '', ?string $directory = null): LandingSeoClaimsRefreshSeeder
    {
        return new class($payload ?? $this->payload, $directory ?? $this->backupDirectory, $backupMode) extends LandingSeoClaimsRefreshSeeder
        {
            public function __construct(private array $fixture, private string $directory, private string $backupMode) {}

            protected function payload(): array
            {
                return $this->fixture;
            }

            protected function backupDirectory(): string
            {
                return $this->directory;
            }

            protected function writeBackup(array $snapshot): string
            {
                if ($this->backupMode === 'fail') {
                    throw new RuntimeException('Simulated backup failure.');
                }
                if ($this->backupMode === 'missing') {
                    return $this->directory.DIRECTORY_SEPARATOR.'missing.json';
                }
                if ($this->backupMode === 'incorrect') {
                    return parent::writeBackup(['incomplete' => true]);
                }

                return parent::writeBackup($snapshot);
            }
        };
    }

    private function snapshot(): array
    {
        return [
            'packages' => DB::table('packages')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
            'translations' => DB::table('package_translations')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
        ];
    }

    private function backups(): array
    {
        return glob($this->backupDirectory.DIRECTORY_SEPARATOR.'*.json') ?: [];
    }

    private function assertRejected(string $message, ?LandingSeoClaimsRefreshSeeder $seeder = null, int $backups = 0): void
    {
        $before = $this->snapshot();
        $failure = null;
        try {
            ($seeder ?? $this->seeder())->run();
        } catch (Throwable $exception) {
            $failure = $exception;
        }
        $this->assertNotNull($failure, 'Unsafe claims refresh unexpectedly succeeded.');
        $this->assertStringContainsString($message, $failure->getMessage());
        $this->assertSame($before, $this->snapshot(), 'Rejected claims refresh must preserve every row and column.');
        $this->assertCount($backups, $this->backups());
    }

    public function test_manifest_contains_only_approved_live_ids_and_preserves_tier_and_yearly_marker(): void
    {
        $this->assertSame([17, 18, 19, 20, 49, 50, 51, 52, 62, 63, 64, 65, 87], $this->payload['package_ids']);
        $this->assertSame($this->payload['package_ids'], array_column($this->payload['rows'], 'id'));
        $this->assertSame([], $this->payload['package_translations']);
        foreach ($this->payload['rows'] as $row) {
            $this->assertSame($row['before']['features'][0], $row['after']['features'][0]);
            $this->assertContains('One simultaneous connection', $row['after']['features']);
            $this->assertContains('Customer support available 24 hours a day', $row['after']['features']);
            $this->assertSame(0, $row['after']['instant_activation']);
            $this->assertDoesNotMatchRegularExpression('/buffer.free|freeze.free|channels|movies|trial|4k|all.device|fast activation/i', implode(' ', $row['after']['features']));
            if ($row['id'] === 87) {
                $this->assertContains('Yearly subscription only (monthly plan unavailable)', $row['after']['features']);
            }
        }
    }

    public function test_refresh_changes_only_features_and_four_activation_flags_with_full_private_backup(): void
    {
        $before = $this->snapshot();
        $this->seeder()->run();
        $after = $this->snapshot();
        $expected = collect($this->payload['rows'])->keyBy('id');
        foreach ($after['packages'] as $index => $package) {
            $original = $before['packages'][$index];
            if ($package['id'] === 9999) {
                $this->assertSame($original, $package);
                continue;
            }
            $row = $expected->get($package['id']);
            $this->assertSame($row['after']['features'], json_decode($package['features'], true, 512, JSON_THROW_ON_ERROR));
            $this->assertSame(0, $package['instant_activation']);
            unset($package['features'], $original['features']);
            if (in_array($package['id'], [62, 63, 64, 65], true)) {
                unset($package['instant_activation'], $original['instant_activation']);
            }
            $this->assertSame($original, $package, 'Prices, duration, allowances, flags and timestamps must not change.');
        }
        $this->assertSame($before['translations'], $after['translations']);
        $this->assertCount(1, $this->backups());
        $path = $this->backups()[0];
        $backup = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('live-approved-ids', $backup['profile']);
        $this->assertSame(array_slice($before['packages'], 0, 13), $backup['packages']);
        $this->assertSame([], $backup['package_translations']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $backup['payload_sha256']);
        $this->assertStringStartsWith(realpath(storage_path('app/private')).DIRECTORY_SEPARATOR, realpath($path));
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertSame(0600, fileperms($path) & 0777);
        }
    }

    public function test_repeat_after_state_is_a_true_noop_without_another_backup(): void
    {
        $this->seeder()->run();
        $before = $this->snapshot();
        $backups = $this->backups();
        $this->travel(2)->days();
        $this->seeder()->run();
        $this->assertSame($before, $this->snapshot());
        $this->assertSame($backups, $this->backups());
    }

    public function test_every_identity_field_is_preflighted_before_any_backup_or_write(): void
    {
        $original = (array) DB::table('packages')->where('id', 87)->first();
        foreach (['title' => 'Different provider', 'type' => 'reseller', 'vendor' => 'another', 'duration_months' => 1, 'price_amount' => '71.01', 'max_devices' => 2] as $field => $wrong) {
            DB::table('packages')->where('id', 87)->update([$field => $wrong]);
            $this->assertRejected('Package identity changed since approval: 87');
            DB::table('packages')->where('id', 87)->update([$field => $original[$field]]);
        }
    }

    public function test_missing_live_package_is_not_replaced_by_a_local_profile_or_created(): void
    {
        DB::table('packages')->where('id', 87)->update(['id' => 148]);
        $this->assertRejected('Expected all 13 approved live package IDs');
        $this->assertFalse(DB::table('packages')->where('id', 87)->exists());
    }

    public function test_feature_and_activation_drift_on_last_package_aborts_the_entire_batch(): void
    {
        $original = (array) DB::table('packages')->where('id', 87)->first();
        DB::table('packages')->where('id', 87)->update(['features' => '["New operator copy"]']);
        $this->assertRejected('Package claims changed since approval: 87');
        DB::table('packages')->where('id', 87)->update(['features' => $original['features'], 'instant_activation' => 1]);
        $this->assertRejected('Package claims changed since approval: 87');
    }

    public function test_unexpected_translations_are_not_deleted_overwritten_or_created(): void
    {
        DB::table('package_translations')->insert([
            'package_id' => 62, 'locale' => 'es', 'title' => 'New translation', 'features' => '["Operator content"]',
            'created_at' => '2026-10-18 00:00:00', 'updated_at' => '2026-10-18 00:00:00',
        ]);
        $this->assertRejected('Unexpected translations exist');
        $this->assertSame(2, DB::table('package_translations')->count());
    }

    public function test_backup_failure_prevents_every_database_write(): void
    {
        $this->assertRejected('Simulated backup failure', $this->seeder(backupMode: 'fail'));
    }

    public function test_missing_backup_is_independently_rejected_before_database_writes(): void
    {
        $this->assertRejected('Private backup verification failed', $this->seeder(backupMode: 'missing'));
    }

    public function test_incomplete_backup_is_independently_rejected_before_database_writes(): void
    {
        $this->assertRejected('Backup contents verification failed', $this->seeder(backupMode: 'incorrect'), 1);
    }

    public function test_non_private_backup_directory_is_rejected(): void
    {
        $this->assertRejected('not private', $this->seeder(directory: public_path()));
    }

    public function test_mid_write_failure_rolls_back_the_first_package_and_preserves_backup(): void
    {
        DB::unprepared("CREATE TRIGGER fail_second_claim BEFORE UPDATE ON packages WHEN NEW.id = 18 BEGIN SELECT RAISE(ABORT, 'Simulated claims update failure'); END");
        $this->assertRejected('Simulated claims update failure', backups: 1);
        $backup = json_decode(file_get_contents($this->backups()[0]), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(13, $backup['packages']);
    }

    public function test_unapproved_timestamp_change_by_a_trigger_rolls_back_all_claim_updates(): void
    {
        DB::unprepared("CREATE TRIGGER change_timestamp AFTER UPDATE ON packages WHEN NEW.id = 17 BEGIN UPDATE packages SET updated_at = '2099-01-01 00:00:00' WHERE id = 17; END");
        $this->assertRejected('Unapproved package columns changed', backups: 1);
    }

    public function test_manifest_cannot_expand_ids_change_prices_or_add_write_columns(): void
    {
        $payload = $this->payload;
        $payload['rows'][0]['id'] = 9999;
        $this->assertRejected('differs from the approved snapshot', $this->seeder($payload));
        $payload = $this->payload;
        $payload['rows'][0]['price_amount'] = '99.00';
        $this->assertRejected('differs from the approved snapshot', $this->seeder($payload));
        $payload = $this->payload;
        $payload['rows'][0]['after']['updated_at'] = '2099-01-01 00:00:00';
        $this->assertRejected('differs from the approved snapshot', $this->seeder($payload));
    }

    public function test_exact_after_rows_can_coexist_with_before_rows_without_being_rewritten(): void
    {
        $row = $this->payload['rows'][0];
        DB::table('packages')->where('id', $row['id'])->update(['features' => json_encode($row['after']['features'], JSON_THROW_ON_ERROR)]);
        $original = (array) DB::table('packages')->where('id', $row['id'])->first();
        $this->seeder()->run();
        $this->assertSame($original, (array) DB::table('packages')->where('id', $row['id'])->first());
        $this->assertCount(1, $this->backups());
    }
}
