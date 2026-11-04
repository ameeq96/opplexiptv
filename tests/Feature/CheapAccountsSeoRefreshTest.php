<?php

namespace Tests\Feature;

use Database\Seeders\CheapAccountsSeoRefreshSeeder;
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

class CheapAccountsSeoRefreshTest extends TestCase
{
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
        $this->payload = json_decode(file_get_contents(database_path('seeders/data/cheapaccounts-content.json')), true, 128, JSON_THROW_ON_ERROR);
        $this->backupDirectory = storage_path('app/private/cheapaccounts-seo-test-'.bin2hex(random_bytes(12)));
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
        });
        foreach ($this->payload['locales'] as $locale => $content) {
            $content['hero_heading'] = self::BEFORE_HEADINGS[$locale];
            foreach (['guide_sections', 'faqs', 'highlights'] as $field) {
                $content[$field] = json_encode($content[$field], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
            DB::table('landing_page_contents')->insert($content + [
                'brand' => 'cheapaccounts', 'locale' => $locale, 'internal_note' => 'Preserve '.$locale,
                'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-02 00:00:00',
            ]);
        }
        $other = (array) DB::table('landing_page_contents')->first();
        unset($other['id']);
        DB::table('landing_page_contents')->insert(array_replace($other, ['brand' => 'trex', 'internal_note' => 'Keep other brand']));
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

    private function seeder(?array $payload = null, string $failure = ''): CheapAccountsSeoRefreshSeeder
    {
        return new class($payload ?? $this->payload, $this->backupDirectory, $failure) extends CheapAccountsSeoRefreshSeeder
        {
            public function __construct(private array $fixture, private string $directory, private string $failure) {}

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
                if ($this->failure === 'write') {
                    throw new RuntimeException('Simulated backup failure.');
                }
                $path = parent::writeBackup($snapshot);
                if ($this->failure === 'verify') {
                    file_put_contents($path, '{}');
                }

                return $path;
            }
        };
    }

    private function snapshot(): array
    {
        return DB::table('landing_page_contents')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    private function backups(): array
    {
        return glob($this->backupDirectory.DIRECTORY_SEPARATOR.'*.json') ?: [];
    }

    private function assertRejected(string $message, ?CheapAccountsSeoRefreshSeeder $seeder = null, int $backups = 0): void
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
        $this->assertSame($before, $this->snapshot());
        $this->assertCount($backups, $this->backups());
    }

    public function test_updates_only_ten_headings_and_preserves_all_other_columns_and_brands(): void
    {
        $before = $this->snapshot();
        $this->seeder()->run();
        $after = $this->snapshot();
        foreach ($before as $index => $row) {
            $expected = $row;
            if ($row['brand'] === 'cheapaccounts') {
                $expected['hero_heading'] = $this->payload['locales'][$row['locale']]['hero_heading'];
            }
            $this->assertSame($expected, $after[$index]);
        }
        $this->assertCount(1, $this->backups());
        $backup = json_decode(file_get_contents($this->backups()[0]), true, 128, JSON_THROW_ON_ERROR);
        $this->assertSame(array_values(array_filter($before, fn ($row) => $row['brand'] === 'cheapaccounts')), $backup['rows']);
        $this->assertCount(10, $backup['after_headings']);
        $this->assertSame('cheapaccounts', $backup['brand']);
        Http::assertNothingSent();
        Mail::assertNothingSent();
        Notification::assertNothingSent();
        Bus::assertNothingDispatched();
    }

    public function test_a_repeat_run_is_idempotent_without_another_backup(): void
    {
        $this->seeder()->run();
        $after = $this->snapshot();
        $this->seeder()->run();
        $this->assertSame($after, $this->snapshot());
        $this->assertCount(1, $this->backups());
    }

    public function test_an_already_updated_locale_can_coexist_with_the_original_approved_headings(): void
    {
        DB::table('landing_page_contents')->where('brand', 'cheapaccounts')->where('locale', 'en')
            ->update(['hero_heading' => $this->payload['locales']['en']['hero_heading']]);
        $before = $this->snapshot();
        $this->seeder()->run();
        $after = $this->snapshot();
        $this->assertSame($before[0], $after[0]);
        foreach ($after as $row) {
            if ($row['brand'] === 'cheapaccounts') {
                $this->assertSame($this->payload['locales'][$row['locale']]['hero_heading'], $row['hero_heading']);
            }
        }
    }

    public function test_a_missing_locale_is_rejected_before_writing(): void
    {
        DB::table('landing_page_contents')->where('brand', 'cheapaccounts')->where('locale', 'hi')->delete();
        $this->assertRejected('exactly ten distinct');
    }

    public function test_a_duplicate_locale_is_rejected_before_writing(): void
    {
        $row = (array) DB::table('landing_page_contents')->where('brand', 'cheapaccounts')->first();
        unset($row['id']);
        DB::table('landing_page_contents')->insert($row);
        $this->assertRejected('exactly ten distinct');
    }

    public function test_an_unexpected_locale_is_rejected_before_writing(): void
    {
        DB::table('landing_page_contents')->where('brand', 'cheapaccounts')->where('locale', 'hi')->update(['locale' => 'sv']);
        $this->assertRejected('exactly ten distinct');
    }

    public function test_an_unexpected_existing_heading_blocks_every_update(): void
    {
        DB::table('landing_page_contents')->where('brand', 'cheapaccounts')->where('locale', 'hi')->update(['hero_heading' => 'An independent admin edit']);
        $this->assertRejected('changed since approval: hi');
    }

    public function test_changed_source_headings_are_not_accepted_as_an_approved_refresh(): void
    {
        $payload = $this->payload;
        $payload['locales']['en']['hero_heading'] = 'Unexpected replacement';
        $this->assertRejected('differ from the approved snapshot', $this->seeder($payload));
    }

    public function test_incomplete_source_locales_are_rejected(): void
    {
        $payload = $this->payload;
        unset($payload['locales']['hi']);
        $this->assertRejected('Unexpected Cheap Accounts heading payload', $this->seeder($payload));
    }

    public function test_a_backup_write_failure_leaves_the_database_unchanged(): void
    {
        $this->assertRejected('Simulated backup failure', $this->seeder(failure: 'write'));
    }

    public function test_a_corrupt_backup_is_rejected_before_writing(): void
    {
        $this->assertRejected('backup verification failed', $this->seeder(failure: 'verify'), 1);
    }

    public function test_an_unapproved_column_change_rolls_back_the_heading_updates(): void
    {
        DB::unprepared("CREATE TRIGGER heading_side_effect AFTER UPDATE OF hero_heading ON landing_page_contents BEGIN UPDATE landing_page_contents SET internal_note = 'Unexpected side effect' WHERE id = NEW.id; END");
        $this->assertRejected('Unapproved Cheap Accounts columns changed', backups: 1);
    }
}
