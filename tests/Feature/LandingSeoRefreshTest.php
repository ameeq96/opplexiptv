<?php

namespace Tests\Feature;

use Database\Seeders\LandingSeoRefreshSeeder;
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

class LandingSeoRefreshTest extends TestCase
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
        $this->payload = json_decode(file_get_contents(database_path('seeders/data/landing-seo-refresh.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->backupDirectory = storage_path('app/private/landing-seo-refresh-test-'.bin2hex(random_bytes(12)));
        Schema::create('landing_page_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('brand');
            $table->string('locale');
            foreach (['hero_heading', 'hero_description', 'guide_heading', 'guide_intro', 'guide_sections', 'faqs', 'highlights'] as $field) {
                $table->text($field);
            }
            $table->boolean('is_published');
            $table->string('internal_note');
            $table->timestamps();
            $table->unique(['brand', 'locale']);
        });
        Schema::create('packages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('vendor');
            $table->integer('duration_months');
            $table->integer('max_devices')->nullable();
            $table->decimal('price_amount', 10, 2);
            $table->boolean('active');
            $table->boolean('is_available');
            $table->string('internal_note');
            $table->timestamps();
        });
        $this->fixtures('local');
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

    private function fixtures(string $profile): void
    {
        foreach ($this->payload['rows'] as $index => $row) {
            $content = $row['before'];
            foreach (['guide_sections', 'faqs', 'highlights'] as $field) {
                // MySQL may reorder object keys; key ordering must not invalidate an approved snapshot.
                $content[$field] = json_encode($content[$field], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
            DB::table('landing_page_contents')->insert($content + [
                'brand' => $row['brand'], 'locale' => $row['locale'], 'is_published' => $index % 2 === 0,
                'internal_note' => 'Unrelated CMS setting '.$index,
                'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
            ]);
        }
        $this->packageFixtures($profile);
        $otherPage = (array) DB::table('landing_page_contents')->first();
        unset($otherPage['id']);
        DB::table('landing_page_contents')->insert(array_replace($otherPage, ['brand' => 'unrelated']));
        $otherPackage = (array) DB::table('packages')->first();
        DB::table('packages')->insert(array_replace($otherPackage, ['id' => 9999, 'title' => 'Other provider', 'max_devices' => 5]));
    }

    private function packageFixtures(string $profile): void
    {
        foreach ($this->payload['package_profiles'][$profile] as $index => $row) {
            DB::table('packages')->insert([
                'id' => $row['id'], 'title' => $row['title'], 'type' => $row['type'], 'vendor' => $row['vendor'],
                'duration_months' => $row['duration_months'], 'max_devices' => null,
                'price_amount' => 4.50 + $index, 'active' => $index % 2 === 0, 'is_available' => true,
                'internal_note' => 'Keep package setting '.$index,
                'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
            ]);
        }
    }

    private function seeder(?array $payload = null, bool $failBackup = false, ?string $directory = null): LandingSeoRefreshSeeder
    {
        return new class($payload ?? $this->payload, $directory ?? $this->backupDirectory, $failBackup) extends LandingSeoRefreshSeeder
        {
            public function __construct(private array $fixture, private string $directory, private bool $failBackup) {}

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
                if ($this->failBackup) {
                    throw new RuntimeException('Simulated backup failure.');
                }

                return parent::writeBackup($snapshot);
            }
        };
    }

    private function snapshot(): array
    {
        return [
            'pages' => DB::table('landing_page_contents')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
            'packages' => DB::table('packages')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
        ];
    }

    private function backups(): array
    {
        return glob($this->backupDirectory.DIRECTORY_SEPARATOR.'*.json') ?: [];
    }

    private function assertRejected(string $message, ?LandingSeoRefreshSeeder $seeder = null, int $backups = 0): void
    {
        $before = $this->snapshot();
        $failure = null;
        try {
            ($seeder ?? $this->seeder())->run();
        } catch (Throwable $exception) {
            $failure = $exception;
        }
        $this->assertNotNull($failure, 'Unsafe refresh unexpectedly succeeded.');
        $this->assertStringContainsString($message, $failure->getMessage());
        $this->assertSame($before, $this->snapshot(), 'A rejected refresh must not change any row.');
        $this->assertCount($backups, $this->backups());
    }

    public function test_exact_content_and_package_inventory_preserves_existing_copy(): void
    {
        $this->assertCount(40, $this->payload['rows']);
        $pairs = [];
        foreach ($this->payload['rows'] as $row) {
            $pairs[] = $row['brand'].':'.$row['locale'];
            $this->assertSame(array_keys($row['before']), array_keys($row['after']));
            foreach (['hero_heading', 'hero_description', 'guide_heading', 'guide_intro'] as $field) {
                $this->assertSame($row['before'][$field], $row['after'][$field]);
            }
            foreach (['guide_sections', 'faqs'] as $field) {
                $this->assertSame($row['before'][$field], array_slice($row['after'][$field], 0, count($row['before'][$field])));
                $this->assertCount(count($row['before'][$field]) + 1, $row['after'][$field]);
            }
        }
        $this->assertCount(40, array_unique($pairs));
        foreach ($this->payload['package_profiles'] as $profile) {
            $this->assertCount(13, $profile);
            $this->assertCount(13, array_unique(array_column($profile, 'id')));
            $this->assertSame([1], array_values(array_unique(array_column($profile, 'after_max_devices'))));
        }
    }

    public function test_local_refresh_changes_only_presentation_with_cms_timestamp_and_package_allowance_with_recoverable_backup(): void
    {
        $before = $this->snapshot();
        $this->seeder()->run();
        $after = $this->snapshot();
        $this->assertCount(41, $after['pages']);
        $this->assertCount(14, $after['packages']);
        foreach ($this->payload['rows'] as $index => $row) {
            $actual = $after['pages'][$index];
            foreach ($row['after'] as $field => $expected) {
                $this->assertEquals($expected, in_array($field, ['guide_sections', 'faqs', 'highlights'], true) ? json_decode($actual[$field], true) : $actual[$field]);
                unset($actual[$field]);
            }
            unset($actual['updated_at']);
            $original = array_diff_key($before['pages'][$index], $row['before'], ['updated_at' => true]);
            $this->assertSame($original, $actual);
        }
        $this->assertSame($before['pages'][40], $after['pages'][40]);
        foreach ($after['packages'] as $index => $package) {
            if ($package['id'] === 9999) {
                $this->assertSame($before['packages'][$index], $package);
                continue;
            }
            $this->assertSame(1, $package['max_devices']);
            unset($package['max_devices']);
            $original = $before['packages'][$index];
            unset($original['max_devices']);
            $this->assertSame($original, $package);
        }
        $this->assertCount(1, $this->backups());
        $path = $this->backups()[0];
        $backup = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('local', $backup['profile']);
        $this->assertSame(array_slice($before['pages'], 0, 40), $backup['landing_page_contents']);
        $this->assertEqualsCanonicalizing(array_slice($before['packages'], 0, 13), $backup['packages']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $backup['payload_sha256']);
        $this->assertStringStartsWith(realpath(storage_path('app/private')), realpath($path));
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertSame(0600, fileperms($path) & 0777);
        }
    }

    public function test_live_id_profile_is_supported_without_touching_any_other_id(): void
    {
        DB::table('packages')->where('id', '!=', 9999)->delete();
        $this->packageFixtures('live');
        $this->seeder()->run();
        $this->assertSame(13, DB::table('packages')->where('max_devices', 1)->count());
        $this->assertSame(5, DB::table('packages')->where('id', 9999)->value('max_devices'));
        $this->assertSame('live', json_decode(file_get_contents($this->backups()[0]), true)['profile']);
    }

    public function test_repeat_run_is_a_true_noop_and_does_not_create_another_backup(): void
    {
        $this->seeder()->run();
        $before = $this->snapshot();
        $backups = $this->backups();
        $this->travel(2)->days();
        $this->seeder()->run();
        $this->assertSame($before, $this->snapshot());
        $this->assertSame($backups, $this->backups());
    }

    public function test_missing_content_aborts_before_backup_or_mutation(): void
    {
        DB::table('landing_page_contents')->where('brand', 'trex')->where('locale', 'ar')->delete();
        $this->assertRejected('40 existing landing content');
    }

    public function test_changed_content_is_not_overwritten(): void
    {
        DB::table('landing_page_contents')->where('brand', 'geo')->where('locale', 'fr')->update(['guide_intro' => 'New operator copy after approval.']);
        $this->assertRejected('changed since approval: geo:fr');
    }

    public function test_missing_package_aborts_all_content_updates(): void
    {
        DB::table('packages')->where('id', 148)->delete();
        $this->assertRejected('Package profile');
    }

    public function test_each_semantic_package_guard_is_required(): void
    {
        $original = (array) DB::table('packages')->where('id', 13)->first();
        foreach (['title' => 'Wrong provider', 'type' => 'panel', 'vendor' => 'another', 'duration_months' => 99] as $field => $wrong) {
            DB::table('packages')->where('id', 13)->update([$field => $wrong]);
            $this->assertRejected('Package profile');
            DB::table('packages')->where('id', 13)->update([$field => $original[$field]]);
        }
    }

    public function test_unexpected_allowance_is_not_overwritten(): void
    {
        foreach ([0, 2] as $allowance) {
            DB::table('packages')->where('id', 148)->update(['max_devices' => $allowance]);
            $this->assertRejected('Unexpected connection allowance on package 148');
        }
    }

    public function test_two_matching_profiles_are_rejected_as_ambiguous(): void
    {
        $this->packageFixtures('live');
        $this->assertRejected('ambiguous');
    }

    public function test_backup_failure_prevents_all_database_writes(): void
    {
        $this->assertRejected('Simulated backup failure', $this->seeder(failBackup: true));
    }

    public function test_non_private_backup_location_is_rejected(): void
    {
        $this->assertRejected('not private', $this->seeder(directory: public_path()));
    }

    public function test_mid_update_failure_rolls_back_content_and_packages_but_keeps_backup(): void
    {
        DB::unprepared("CREATE TRIGGER fail_second_package BEFORE UPDATE ON packages WHEN NEW.id = 13 BEGIN SELECT RAISE(ABORT, 'Simulated update failure'); END");
        $this->assertRejected('Simulated update failure', backups: 1);
        $backup = json_decode(file_get_contents($this->backups()[0]), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(40, $backup['landing_page_contents']);
        $this->assertCount(13, $backup['packages']);
    }

    public function test_payload_cannot_expand_approved_package_scope_or_duplicate_content(): void
    {
        $payload = $this->payload;
        $payload['package_profiles']['local'][0]['id'] = 9999;
        $this->assertRejected('differs from the approved local profile', $this->seeder($payload));
        $payload = $this->payload;
        $payload['rows'][1] = $payload['rows'][0];
        $this->assertRejected('must appear once', $this->seeder($payload));
        $payload = $this->payload;
        $payload['rows'][0]['after']['is_published'] = true;
        $this->assertRejected('after', $this->seeder($payload));
    }
}
