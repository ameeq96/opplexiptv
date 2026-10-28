<?php

namespace App\Console\Commands;

use App\Models\BlogGenerationRun;
use App\Models\BlogSiteSetting;
use App\Services\Api\PublicSiteDataService;
use App\Services\Blogs\BlogContentService;
use App\Services\Blogs\BlogDiscoveryService;
use App\Services\Blogs\BlogPublicationService;
use App\Services\Blogs\BlogQualityService;
use App\Services\Blogs\GoogleSitemapClient;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class BlogPublishing extends Command
{
    protected $signature = 'blogs:publishing
        {action : setup|sites|status|reserve|validate|preflight|import|snapshot|activate|rollback|block|retry|notify}
        {--site= : Fixed publishing site key}
        {--date= : Current publishing date, YYYY-MM-DD}
        {--run= : Existing run ID}
        {--payload=- : JSON article from standard input only}
        {--revision= : Expected article revision}
        {--snapshot-revision= : Expected complete site snapshot revision}
        {--source-commit= : Committed article source revision}
        {--source-sha256= : Expected exact article source SHA-256}
        {--source-fingerprint= : Expected normalized article fingerprint}
        {--reason= : Fixed publication blocking reason}
        {--json : Emit a machine-readable result}';

    protected $description = 'Validate and coordinate explicitly prepared daily blogs; never generates paid API content or executes a site build';

    private ?string $payloadSha256 = null;

    public function handle(
        BlogPublicationService $publisher,
        BlogContentService $content,
        BlogQualityService $quality,
        BlogDiscoveryService $discovery,
    ): int {
        try {
            $action = (string) $this->argument('action');
            $actions = ['setup', 'sites', 'status', 'reserve', 'validate', 'preflight', 'import', 'snapshot', 'activate', 'rollback', 'block', 'retry', 'notify'];
            abort_unless(in_array($action, $actions, true), 422, 'Unknown publishing action.');
            $gitSource = $this->sourceOptions($action);
            if (in_array($action, ['setup', 'sites'], true)) {
                if ($action === 'setup') {
                    $publisher->initializeSettings();
                }
                return $this->result(['sites' => $this->sites(), 'writer' => 'Codex scheduled run', 'paid_generation_api' => false]);
            }
            $site = (string) $this->option('site');
            $quality->site($site);
            if ($action === 'validate') {
                $article = $quality->normalize($this->payload(), $site);
                return $this->result(['valid' => true, 'site_key' => $site, 'slug' => $article['slug'], 'locales' => array_keys($article['translations'])]);
            }
            if ($action === 'preflight') {
                $input = $this->payload();
                return $this->result($publisher->preflight($site, (string) $this->option('date'), $input, $this->payloadSha256));
            }
            if ($gitSource === null) {
                $publisher->initializeSettings();
            }
            $setting = BlogSiteSetting::where('site_key', $site)->firstOrFail();
            $date = $this->option('date') ?: CarbonImmutable::now($setting->timezone)->format('Y-m-d');
            if ($action === 'status') {
                $run = $this->option('run') ? $this->publicationRun($site) : BlogGenerationRun::where('site_key', $site)->whereDate('publishing_date', $date)->first();
                return $this->result([
                    'site_key' => $site, 'enabled' => $setting->enabled, 'timezone' => $setting->timezone,
                    'publish_time' => $setting->publish_time, 'publishing_date' => $date, 'locales' => $setting->locales,
                    'google_configured' => app(GoogleSitemapClient::class)->configured(), 'run' => $run ? $this->runData($run) : null,
                    'eligibility' => $run ? $publisher->publicationEligibility($run) : null,
                    'pending_runs' => BlogGenerationRun::where('site_key', $site)->whereIn('status', ['ready', 'failed', 'blocked'])->latest('id')->limit(100)->get()->map(fn ($item) => $this->runData($item))->all(),
                    'recent_topics' => BlogGenerationRun::whereNotNull('blog_id')->latest('id')->limit(100)->get(['site_key', 'topic', 'primary_keyword'])->toArray(),
                    'facts' => $this->facts($site),
                ]);
            }
            if (in_array($action, ['reserve', 'import'], true)) {
                if ($action === 'reserve') {
                    $run = $publisher->reserve($site, $date);
                } else {
                    $input = $this->payload();
                    if ($gitSource !== null) {
                        $gitSource['path'] = 'resources/content/blog-publications/'.$site.'/'.$date.'.json';
                    }
                    $run = $publisher->import($site, $date, $input, $gitSource, $this->payloadSha256);
                }
                return $this->result(['site_key' => $site, 'run' => $this->runData($run)]);
            }
            if ($action === 'snapshot') {
                $run = $this->option('run') ? $this->publicationRun($site) : null;
                return $this->result($content->snapshot($site, $run));
            }
            $run = $this->publicationRun($site);
            if ($action === 'activate') {
                if ($gitSource !== null) {
                    abort_unless($run->publishing_date, 422, 'A daily source publication date is required.');
                    $gitSource['path'] = 'resources/content/blog-publications/'.$site.'/'.$run->publishing_date->format('Y-m-d').'.json';
                }
                $run = $publisher->activate($run, $this->revision('revision'), $this->revision('snapshot-revision'), $gitSource);
            } elseif ($action === 'rollback') {
                $publisher->rollback($run, $this->revision('revision'));
                $run->refresh();
            } elseif ($action === 'block') {
                $publisher->block($run, (string) $this->option('reason'));
                $run->refresh();
            } elseif ($action === 'retry') {
                $publisher->retry($run);
                $run->refresh();
            } elseif ($action === 'notify') {
                return $this->result($discovery->notify($run));
            }
            return $this->result(['site_key' => $site, 'run' => $this->runData($run)]);
        } catch (ValidationException $error) {
            return $this->failure('validation_failed', $error->errors());
        } catch (HttpExceptionInterface $error) {
            return $this->failure('publishing_http_'.$error->getStatusCode());
        } catch (Throwable) {
            return $this->failure('publishing_failed');
        }
    }

    private function payload(): array
    {
        abort_unless($this->option('payload') === '-', 422, 'Use standard input; arbitrary payload file paths are not accepted.');
        $stream = $this->input instanceof StreamableInputInterface ? $this->input->getStream() : null;
        $raw = stream_get_contents($stream ?: STDIN, 2 * 1024 * 1024 + 1);
        abort_unless(is_string($raw) && strlen($raw) <= 2 * 1024 * 1024, 422, 'The article payload is too large.');
        $this->payloadSha256 = hash('sha256', $raw);
        $input = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', $raw), true, 64, JSON_THROW_ON_ERROR);
        abort_unless(is_array($input) && !array_is_list($input), 422, 'The article payload must be a JSON object.');
        return $input;
    }

    private function sourceOptions(string $action): ?array
    {
        $source = ['commit' => $this->option('source-commit'), 'sha256' => $this->option('source-sha256'),
            'fingerprint' => $this->option('source-fingerprint')];
        if (count(array_filter($source, fn ($value) => $value !== null)) === 0) {
            return null;
        }
        abort_unless(in_array($action, ['import', 'activate'], true) && count(array_filter($source, fn ($value) => $value !== null)) === 3, 422, 'Git source options must be supplied together for import or activate.');
        abort_unless(preg_match('/^(?:[a-f0-9]{40}|[a-f0-9]{64})$/D', (string) $source['commit'])
            && preg_match('/^[a-f0-9]{64}$/D', (string) $source['sha256'])
            && preg_match('/^[a-f0-9]{64}$/D', (string) $source['fingerprint']), 422, 'Git source identifiers must be lowercase hexadecimal revisions.');
        return $source;
    }

    private function publicationRun(string $site): BlogGenerationRun
    {
        $id = (string) $this->option('run');
        abort_unless(preg_match('/^[1-9][0-9]{0,15}$/D', $id), 422, 'A positive run ID is required.');
        return BlogGenerationRun::where('site_key', $site)->findOrFail((int) $id);
    }

    private function revision(string $key): string
    {
        $revision = (string) $this->option($key);
        abort_unless(preg_match('/^[a-f0-9]{64}$/D', $revision), 422, 'A SHA-256 revision is required.');
        return $revision;
    }

    private function runData(BlogGenerationRun $run): array
    {
        return [
            'id' => $run->id, 'blog_id' => $run->blog_id, 'status' => $run->status, 'revision' => $run->revision,
            'publishing_date' => $run->publishing_date?->format('Y-m-d'), 'topic' => $run->topic,
            'errors' => $run->errors ?? [],
            'git_source' => is_array($run->payload['git_source'] ?? null)
                ? array_intersect_key($run->payload['git_source'], array_flip(['commit', 'path', 'sha256', 'fingerprint'])) : null,
            'deliveries' => $run->deliveries()->where('revision', $run->revision)->get()->map->only(['channel', 'status', 'http_status', 'error_code'])->all(),
        ];
    }

    private function sites(): array
    {
        return collect(config('blog-publishing.sites'))->map(fn ($site, $key) => [
            'site_key' => $key, 'name' => $site['name'], 'domain' => $site['domain'],
            'timezone' => config('blog-publishing.timezone'), 'publish_time' => config('blog-publishing.time'),
            'locales' => config('blog-publishing.supported_locales'),
        ])->values()->all();
    }

    private function facts(string $site): array
    {
        try {
            $catalog = app(PublicSiteDataService::class)->landingPlans($site === 'main' ? 'iptvsubscription' : $site);
            return ['support_name' => config('blog-publishing.publisher.name'), 'support_hours' => '24 hours',
                'packages' => collect($catalog['packages'])->map(fn ($plan) => collect($plan)->only([
                    'id', 'service', 'duration_months', 'price_amount', 'currency', 'max_devices',
                ])->all())->values()->all(), 'activation_time' => 'unconfirmed', 'distribution_rights' => 'unconfirmed'];
        } catch (Throwable) {
            return ['catalog_available' => false, 'activation_time' => 'unconfirmed', 'distribution_rights' => 'unconfirmed'];
        }
    }

    private function result(array $data): int
    {
        $this->output->write(json_encode(['ok' => true, 'data' => $data], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL, false, OutputInterface::OUTPUT_RAW);
        return self::SUCCESS;
    }

    private function failure(string $code, array $errors = []): int
    {
        $this->output->write(json_encode(['ok' => false, 'error_code' => $code, 'errors' => $errors], JSON_THROW_ON_ERROR).PHP_EOL, false, OutputInterface::OUTPUT_RAW);
        return self::FAILURE;
    }
}
