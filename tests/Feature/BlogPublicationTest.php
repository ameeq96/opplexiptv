<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogGenerationRun;
use App\Models\BlogSiteSetting;
use App\Services\Blogs\BlogContentService;
use App\Services\Blogs\BlogPublicationService;
use App\Services\Blogs\BlogQualityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;
use Throwable;

class BlogPublicationTest extends TestCase
{
    use RefreshDatabase;

    private array $publicResponses = [];

    private ?\Closure $beforePublicResponse = null;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-28T05:00:00+00:00');
        CarbonImmutable::setTestNow('2026-10-28T05:00:00+00:00');
        Http::preventStrayRequests();
        Bus::fake();
        Mail::fake();
        Notification::fake();
        Cache::flush();
        Http::fake(function (Request $request) {
            if ($this->beforePublicResponse) {
                $callback = $this->beforePublicResponse;
                $this->beforePublicResponse = null;
                $callback($request);
            }
            if (!isset($this->publicResponses[$request->url()])) {
                throw new RuntimeException('Unexpected HTTP request in isolated publication test.');
            }
            return Http::response(...$this->publicResponses[$request->url()]);
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_paused_site_can_keep_an_imported_draft_but_cannot_activate_it(): void
    {
        $service = app(BlogPublicationService::class);
        $run = $service->import('geo', '2026-10-28', $this->payload());
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $this->fakePublishedPages($run);

        $this->assertHttpRejected(fn () => $service->activate($run, $run->revision, $snapshot['revision']), 422);

        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertNotSame('published', $run->fresh()->status);
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        Http::assertNothingSent();
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_all_ten_verified_public_articles_activate_once_without_sending_notifications(): void
    {
        $run = $this->importReady();
        $this->fakePublishedPages($run);
        $service = app(BlogPublicationService::class);
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);

        $published = $service->activate($run, $run->revision, $snapshot['revision']);

        $this->assertSame('published', $published->status);
        $this->assertSame('published', $published->blog->status);
        $this->assertSame($run->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        $delivery = $published->deliveries()->where('channel', 'site')->firstOrFail();
        $this->assertSame('accepted', $delivery->status);
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame(200, $delivery->http_status);
        $this->assertSame(3, $published->deliveries()->whereIn('channel', ['sitemap', 'indexnow', 'google'])->where('status', 'pending')->count());
        Http::assertSentCount(10);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'indexnow.org') || str_contains($request->url(), 'googleapis.com'));

        $service->activate($published, $published->revision, app(BlogContentService::class)->snapshot('geo', $published)['revision']);
        $this->assertSame(1, $delivery->fresh()->attempts);
        $this->assertSame(1, BlogGenerationRun::where('site_key', 'geo')->where('status', 'published')->count());
        Http::assertSentCount(20);
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public static function invalidPublicPages(): array
    {
        return [
            'last translation missing' => ['missing'],
            'redirect instead of public article' => ['redirect'],
            'wrong canonical' => ['canonical'],
            'missing canonical' => ['missing-canonical'],
            'wrong title' => ['title'],
            'wrong visible heading' => ['heading'],
            'multiple H1 headings' => ['duplicate-heading'],
            'meta noindex' => ['meta-noindex'],
            'header noindex' => ['header-noindex'],
            'wrong Next release revision' => ['revision'],
            'wrong article identity' => ['identity'],
        ];
    }

    #[DataProvider('invalidPublicPages')]
    public function test_one_invalid_translation_never_publishes_the_candidate(string $fault): void
    {
        $run = $this->importReady();
        $this->fakePublishedPages($run, $fault);
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);

        $this->assertRuntimeRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision']));

        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertNotSame('published', $run->fresh()->status);
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        $this->assertSame(0, $run->deliveries()->where('status', 'accepted')->count());
        Http::assertSentCount(10);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'indexnow.org') || str_contains($request->url(), 'googleapis.com'));
    }

    public function test_snapshot_revision_mismatch_is_rejected_before_http_or_visibility_changes(): void
    {
        $run = $this->importReady();
        $this->fakePublishedPages($run);

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, str_repeat('0', 64)), 409);

        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertSame('ready', $run->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_current_editorial_changes_cannot_be_overwritten_by_an_older_staged_activation(): void
    {
        $run = $this->importReady();
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $run->blog->translations()->where('locale', 'en')->update(['title' => 'Geo IPTV editorial change after the build snapshot']);
        $this->fakePublishedPages($run);

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision']), 409);

        $this->assertSame('Geo IPTV editorial change after the build snapshot', $run->blog->translations()->where('locale', 'en')->value('title'));
        $this->assertSame('draft', $run->blog->fresh()->status);
    }

    public function test_queuing_an_edited_draft_keeps_the_previous_published_snapshot_visible(): void
    {
        [$previous, $candidate] = $this->publishedAndEdited();
        $public = app(BlogContentService::class)->snapshot('geo');
        $build = app(BlogContentService::class)->snapshot('geo', $candidate);

        $this->assertSame('published', $previous->fresh()->status);
        $this->assertSame('ready', $candidate->status);
        $this->assertNotSame($previous->revision, $candidate->revision);
        $this->assertSame($previous->revision, $public['posts'][0]['revision']);
        $this->assertSame($previous->payload['post']['translations']['en']['title'], $public['posts'][0]['translations']['en']['title']);
        $this->assertSame($candidate->revision, $build['posts'][0]['revision']);
        $this->assertSame(1, count($public['posts']));
    }

    public function test_failed_candidate_verification_does_not_hide_the_previous_published_snapshot(): void
    {
        [$previous, $candidate] = $this->publishedAndEdited();
        $this->fakePublishedPages($candidate, 'canonical');
        $snapshot = app(BlogContentService::class)->snapshot('geo', $candidate);

        $this->assertRuntimeRejected(fn () => app(BlogPublicationService::class)->activate($candidate, $candidate->revision, $snapshot['revision']));

        $this->assertSame('published', $previous->fresh()->status);
        $this->assertNotSame('published', $candidate->fresh()->status);
        $public = app(BlogContentService::class)->snapshot('geo');
        $this->assertSame($previous->revision, $public['posts'][0]['revision']);
        $this->assertSame($previous->payload['post']['translations']['en']['title'], $public['posts'][0]['translations']['en']['title']);
    }

    public function test_failed_build_keeps_its_candidate_draft_and_the_previous_snapshot_visible(): void
    {
        [$previous, $candidate] = $this->publishedAndEdited();
        app(BlogPublicationService::class)->block($candidate, 'build-failed');

        $this->assertSame('blocked', $candidate->fresh()->status);
        $this->assertSame(['build_failed'], $candidate->fresh()->errors);
        $this->assertSame($previous->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        $this->assertSame(0, $candidate->deliveries()->where('status', 'accepted')->count());
    }

    public function test_stale_activation_and_rollback_cannot_replace_a_newer_manual_candidate(): void
    {
        [$previous, $candidate] = $this->publishedAndEdited();
        $oldSnapshot = app(BlogContentService::class)->snapshot('geo', $previous);
        $this->fakePublishedPages($previous);

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->activate($previous, $previous->revision, $oldSnapshot['revision']), 409);
        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->rollback($previous, $previous->revision), 409);

        $this->assertSame('published', $previous->fresh()->status);
        $this->assertSame('ready', $candidate->fresh()->status);
        $this->assertSame($previous->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
    }

    public function test_rollback_with_an_unowned_revision_never_hides_the_current_release(): void
    {
        $run = $this->publishReady();

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->rollback($run, str_repeat('f', 64)), 409);

        $this->assertSame('published', $run->fresh()->status);
        $this->assertSame($run->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
    }

    public function test_stale_draft_model_cannot_bypass_rollback_protection_after_publication_and_a_new_edit(): void
    {
        $run = $this->importReady();
        $stale = $run->fresh('blog');
        $this->fakePublishedPages($run);
        $published = app(BlogPublicationService::class)->activate($run, $run->revision, app(BlogContentService::class)->snapshot('geo', $run)['revision']);
        Carbon::setTestNow('2026-10-28T06:00:00+00:00');
        CarbonImmutable::setTestNow('2026-10-28T06:00:00+00:00');
        $blog = $published->blog->fresh();
        $blog->translations()->where('locale', 'en')->update(['title' => 'Geo IPTV newer editorial revision pending publication']);
        $blog->update(['status' => 'draft']);
        $candidate = app(BlogPublicationService::class)->queueExisting($blog->fresh());

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->rollback($stale, $stale->revision), 409);

        $this->assertSame('published', $published->fresh()->status);
        $this->assertSame('ready', $candidate->fresh()->status);
        $this->assertSame($published->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
    }

    public function test_first_main_activation_restores_draft_when_public_http_cannot_be_verified(): void
    {
        $run = $this->importReady('main');
        $this->fakePublishedPages($run, 'missing', 'en');
        Artisan::shouldReceive('call')->twice()->with('sitemap:generate')->andReturn(0);
        $snapshot = app(BlogContentService::class)->snapshot('main', $run);

        $this->assertRuntimeRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision']));

        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertSame('failed', $run->fresh()->status);
        $this->assertNull($run->fresh()->published_at);
        $this->assertSame(0, $run->deliveries()->where('status', 'accepted')->count());
        Http::assertSentCount(1);
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_retry_preserves_accepted_deliveries_and_never_sends_or_creates_another_post(): void
    {
        $run = $this->importReady();
        $service = app(BlogPublicationService::class);
        $service->block($run, 'build-failed');
        $accepted = $run->deliveries()->where('channel', 'indexnow')->firstOrFail();
        $accepted->update(['status' => 'accepted', 'attempts' => 1, 'http_status' => 200]);
        $failed = $run->deliveries()->where('channel', 'google')->firstOrFail();
        $failed->update(['status' => 'failed', 'attempts' => 2, 'error_code' => 'google_http_503', 'next_attempt_at' => now()->addHour()]);

        $service->retry($run->fresh());

        $this->assertSame('ready', $run->fresh()->status);
        $this->assertSame('accepted', $accepted->fresh()->status);
        $this->assertSame(1, $accepted->fresh()->attempts);
        $this->assertSame('pending', $failed->fresh()->status);
        $this->assertSame(2, $failed->fresh()->attempts);
        $this->assertNull($failed->fresh()->next_attempt_at);
        $this->assertNull($failed->fresh()->error_code);
        $this->assertSame(1, BlogGenerationRun::where('site_key', 'geo')->count());
        $this->assertSame(4, $run->deliveries()->count());
        Http::assertNothingSent();
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_a_failed_notification_cannot_mark_an_already_published_run_blocked(): void
    {
        $run = $this->publishReady();

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->block($run, 'public-unavailable'), 409);

        $this->assertSame('published', $run->fresh()->status);
        $this->assertSame($run->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
    }

    public function test_stale_ready_model_cannot_block_a_run_that_another_actor_published(): void
    {
        $run = $this->importReady();
        $stale = $run->fresh('blog');
        $this->fakePublishedPages($run);
        $published = app(BlogPublicationService::class)->activate($run, $run->revision, app(BlogContentService::class)->snapshot('geo', $run)['revision']);
        Http::fake();

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->block($stale, 'build-failed'), 409);

        $this->assertSame('published', $published->fresh()->status);
        $this->assertSame($published->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        Http::assertNothingSent();
    }

    public function test_stale_retry_preserves_publication_and_rejects_a_superseded_manual_revision(): void
    {
        $run = $this->importReady();
        $stale = $run->fresh('blog');
        $older = $run->fresh('blog');
        $this->fakePublishedPages($run);
        $service = app(BlogPublicationService::class);
        $published = $service->activate($run, $run->revision, app(BlogContentService::class)->snapshot('geo', $run)['revision']);
        Http::fake();

        $service->retry($stale);

        $this->assertSame('published', $published->fresh()->status);
        $this->assertSame('published', $stale->status);
        $this->assertSame('accepted', $published->deliveries()->where('channel', 'site')->firstOrFail()->status);
        $blog = $published->blog->fresh();
        $blog->translations()->where('locale', 'en')->update(['title' => 'Geo IPTV new editorial version pending release']);
        $blog->update(['status' => 'draft']);
        $candidate = $service->queueExisting($blog->fresh());

        $this->assertHttpRejected(fn () => $service->retry($older), 409);

        $this->assertSame('published', $published->fresh()->status);
        $this->assertSame('ready', $candidate->fresh()->status);
        $this->assertSame($published->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        Http::assertNothingSent();
    }

    public function test_main_verification_failure_preserves_an_editorial_edit_made_after_database_publication(): void
    {
        $run = $this->importReady('main');
        $this->fakePublishedPages($run, 'missing', 'en');
        Artisan::shouldReceive('call')->twice()->with('sitemap:generate')->andReturn(0);
        $future = now()->addDays(2);
        $changedTitle = 'Opplex IPTV operator editorial change during verification';
        $changedBody = '<h2>A later editorial decision</h2><p>This operator change must survive a failed publication check.</p>';
        $edited = false;
        $this->beforePublicResponse = function () use ($run, $future, $changedTitle, $changedBody, &$edited) {
            $blog = $run->blog->fresh();
            $this->assertSame('published', $blog->status);
            $blog->translations()->where('locale', 'en')->update(['title' => $changedTitle, 'content' => $changedBody]);
            $blog->update(['status' => 'archived', 'published_at' => $future]);
            $edited = true;
        };
        $snapshot = app(BlogContentService::class)->snapshot('main', $run);

        $this->assertRuntimeRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision']));

        $this->assertTrue($edited);
        $this->assertSame('archived', $run->blog->fresh()->status);
        $this->assertSame($future->toIso8601String(), $run->blog->fresh()->published_at->toIso8601String());
        $this->assertSame($changedTitle, $run->blog->translations()->where('locale', 'en')->value('title'));
        $this->assertSame($changedBody, $run->blog->translations()->where('locale', 'en')->value('content'));
        $this->assertSame('failed', $run->fresh()->status);
        $this->assertSame(0, $run->deliveries()->where('status', 'accepted')->count());
        Http::assertSentCount(1);
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public static function dailyPublicationBoundary(): array
    {
        return [
            'same Pakistan day but previous UTC day' => ['2026-10-27T19:30:00+00:00', true],
            'before the Pakistan day boundary' => ['2026-10-27T18:59:59+00:00', false],
        ];
    }

    #[DataProvider('dailyPublicationBoundary')]
    public function test_automatic_publication_limit_uses_the_pakistan_day_of_other_published_runs(string $alreadyPublishedAt, bool $blocked): void
    {
        $run = $this->importReady();
        $otherBlog = Blog::create(['site_key' => 'geo', 'status' => 'published', 'published_at' => $alreadyPublishedAt]);
        $post = $run->payload['post'];
        $post['id'] = $otherBlog->id;
        $post['slug'] = 'geo-earlier-independent-manual-release';
        $post['published_at'] = $alreadyPublishedAt;
        $post['updated_at'] = $alreadyPublishedAt;
        $post['revision'] = hash('sha256', 'independent manual publication at '.$alreadyPublishedAt);
        $other = BlogGenerationRun::create(['site_key' => 'geo', 'publishing_date' => null, 'blog_id' => $otherBlog->id,
            'topic' => 'An earlier independent manual publication', 'status' => 'published', 'revision' => $post['revision'],
            'payload' => ['post' => $post], 'published_at' => $alreadyPublishedAt]);
        $this->fakePublishedPages($run);
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);

        if ($blocked) {
            $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision']), 409);
            $this->assertSame('draft', $run->blog->fresh()->status);
            $this->assertNotSame('published', $run->fresh()->status);
            $this->assertSame([$otherBlog->id], array_column(app(BlogContentService::class)->snapshot('geo')['posts'], 'id'));
        } else {
            $published = app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision']);
            $this->assertSame('published', $published->status);
            $this->assertSame('published', $published->blog->status);
            $this->assertCount(2, app(BlogContentService::class)->snapshot('geo')['posts']);
        }
        $this->assertSame('published', $other->fresh()->status);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'indexnow.org') || str_contains($request->url(), 'googleapis.com'));
    }

    public function test_an_expired_automatic_draft_is_not_backfilled_after_pakistan_midnight(): void
    {
        $run = $this->importReady();
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        Carbon::setTestNow('2026-10-28T19:05:00+00:00');
        CarbonImmutable::setTestNow('2026-10-28T19:05:00+00:00');
        $this->fakePublishedPages($run);
        $failure = null;
        try {
            app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision']);
        } catch (ValidationException $error) {
            $failure = $error;
        }

        $this->assertInstanceOf(ValidationException::class, $failure);
        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertSame('ready', $run->fresh()->status);
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        Http::assertNothingSent();
    }

    public function test_an_already_published_daily_run_remains_idempotent_on_a_later_pakistan_day(): void
    {
        $run = $this->publishReady();
        $publishedAt = $run->published_at->toIso8601String();
        Carbon::setTestNow('2026-10-28T19:05:00+00:00');
        CarbonImmutable::setTestNow('2026-10-28T19:05:00+00:00');
        $this->fakePublishedPages($run);

        $published = app(BlogPublicationService::class)->activate($run, $run->revision, app(BlogContentService::class)->snapshot('geo', $run)['revision']);

        $this->assertSame('published', $published->status);
        $this->assertSame($publishedAt, $published->published_at->toIso8601String());
        $this->assertSame(1, $published->deliveries()->where('channel', 'site')->firstOrFail()->attempts);
        $this->assertCount(1, app(BlogContentService::class)->snapshot('geo')['posts']);
        Http::assertSentCount(10);
    }

    public function test_an_explicit_manual_queue_without_daily_date_can_publish_on_a_later_day(): void
    {
        [$previous, $candidate] = $this->publishedAndEdited();
        $this->assertNull($candidate->publishing_date);
        Carbon::setTestNow('2026-10-29T05:00:00+00:00');
        CarbonImmutable::setTestNow('2026-10-29T05:00:00+00:00');
        $this->fakePublishedPages($candidate);

        $published = app(BlogPublicationService::class)->activate($candidate, $candidate->revision, app(BlogContentService::class)->snapshot('geo', $candidate)['revision']);

        $this->assertSame('published', $published->status);
        $this->assertSame('published', $published->blog->status);
        $this->assertSame('published', $previous->fresh()->status);
        $this->assertSame($candidate->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        Http::assertSentCount(10);
    }

    public function test_preflight_reports_the_exact_current_article_and_snapshot_without_mutation(): void
    {
        $run = $this->importReady();
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $before = $this->publicationRecords();

        $eligibility = app(BlogPublicationService::class)->publicationEligibility($run);

        $this->assertSame(['eligible' => true, 'revision' => $run->revision, 'snapshot_revision' => $snapshot['revision']], $eligibility);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        Http::assertNothingSent(); Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_preflight_rejects_an_expired_automatic_run_without_backfilling_or_mutation(): void
    {
        $run = $this->importReady();
        Carbon::setTestNow('2026-10-28T19:05:00+00:00');
        CarbonImmutable::setTestNow('2026-10-28T19:05:00+00:00');
        $before = $this->publicationRecords();

        $eligibility = app(BlogPublicationService::class)->publicationEligibility($run);

        $this->assertSame(['eligible' => false, 'error_code' => 'eligibility_validation_failed'], $eligibility);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame('ready', $run->fresh()->status);
        $this->assertSame('draft', $run->blog->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_preflight_respects_a_current_pause_even_when_the_run_was_loaded_before_it(): void
    {
        $run = $this->importReady();
        BlogSiteSetting::where('site_key', 'geo')->update(['enabled' => false]);
        $before = $this->publicationRecords();

        $eligibility = app(BlogPublicationService::class)->publicationEligibility($run);

        $this->assertSame(['eligible' => false, 'error_code' => 'eligibility_http_422'], $eligibility);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertFalse(BlogSiteSetting::where('site_key', 'geo')->firstOrFail()->enabled);
        Http::assertNothingSent();
    }

    public function test_preflight_rejects_an_editorial_change_after_the_snapshot_without_reverting_it(): void
    {
        $run = $this->importReady();
        $changed = 'Geo IPTV operator correction after the staged snapshot';
        $run->blog->translations()->where('locale', 'en')->update(['title' => $changed]);
        $before = $this->publicationRecords();

        $eligibility = app(BlogPublicationService::class)->publicationEligibility($run);

        $this->assertSame(['eligible' => false, 'error_code' => 'eligibility_http_409'], $eligibility);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame($changed, $run->blog->translations()->where('locale', 'en')->value('title'));
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        Http::assertNothingSent();
    }

    public function test_preflight_rejects_an_older_run_when_a_new_revision_is_queued(): void
    {
        [$previous, $candidate] = $this->publishedAndEdited();
        $before = $this->publicationRecords();

        $eligibility = app(BlogPublicationService::class)->publicationEligibility($previous);

        $this->assertSame(['eligible' => false, 'error_code' => 'eligibility_http_409'], $eligibility);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame('published', $previous->fresh()->status);
        $this->assertSame('ready', $candidate->fresh()->status);
        $this->assertSame($previous->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        Http::assertNothingSent();
    }

    public function test_preflight_rejects_a_used_daily_slot_without_mutating_either_record(): void
    {
        $run = $this->importReady();
        $publishedAt = '2026-10-27T19:30:00+00:00';
        $otherBlog = Blog::create(['site_key' => 'geo', 'status' => 'published', 'published_at' => $publishedAt]);
        $post = $run->payload['post'];
        $post['id'] = $otherBlog->id;
        $post['slug'] = 'geo-independent-preflight-daily-slot';
        $post['revision'] = hash('sha256', 'isolated preflight already published daily slot');
        $post['published_at'] = $publishedAt;
        $post['updated_at'] = $publishedAt;
        $other = BlogGenerationRun::create(['site_key' => 'geo', 'blog_id' => $otherBlog->id, 'publishing_date' => null,
            'topic' => 'Isolated earlier publication within the Pakistan day', 'status' => 'published',
            'revision' => $post['revision'], 'payload' => ['post' => $post], 'published_at' => $publishedAt]);
        $before = $this->publicationRecords();

        $eligibility = app(BlogPublicationService::class)->publicationEligibility($run);

        $this->assertSame(['eligible' => false, 'error_code' => 'eligibility_http_409'], $eligibility);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame('ready', $run->fresh()->status);
        $this->assertSame('published', $other->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_preflight_does_not_expire_an_explicit_manual_revision_without_a_daily_date(): void
    {
        [$previous, $candidate] = $this->publishedAndEdited();
        $this->assertNull($candidate->publishing_date);
        Carbon::setTestNow('2026-10-30T05:00:00+00:00');
        CarbonImmutable::setTestNow('2026-10-30T05:00:00+00:00');
        $snapshot = app(BlogContentService::class)->snapshot('geo', $candidate);
        $before = $this->publicationRecords();

        $eligibility = app(BlogPublicationService::class)->publicationEligibility($candidate);

        $this->assertSame(['eligible' => true, 'revision' => $candidate->revision, 'snapshot_revision' => $snapshot['revision']], $eligibility);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame('ready', $candidate->fresh()->status);
        $this->assertSame($previous->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        Http::assertNothingSent();
    }

    public function test_cli_status_exposes_the_selected_run_preflight_without_publishing_it(): void
    {
        $run = $this->importReady();
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        config()->set('blog-publishing.google.credentials_file', sys_get_temp_dir().'/isolated-cli-missing-google-'.bin2hex(random_bytes(8)).'.json');
        $before = $this->publicationRecords();

        $exit = Artisan::call('blogs:publishing', ['action' => 'status', '--site' => 'geo', '--run' => (string) $run->id, '--json' => true]);
        $result = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exit);
        $this->assertTrue($result['ok']);
        $this->assertSame($run->id, $result['data']['run']['id']);
        $this->assertSame(['eligible' => true, 'revision' => $run->revision, 'snapshot_revision' => $snapshot['revision']], $result['data']['eligibility']);
        $this->assertFalse($result['data']['google_configured']);
        $this->assertArrayNotHasKey('payload', $result['data']['run']);
        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        Http::assertNothingSent(); Mail::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_matching_git_source_activates_and_an_existing_caller_can_recheck_without_source_options(): void
    {
        [$run, $source] = $this->importGitReady();
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $this->fakePublishedPages($run);
        $service = app(BlogPublicationService::class);

        $published = $service->activate($run, $run->revision, $snapshot['revision'], $source);

        $this->assertSame($source, $published->payload['git_source']);
        $this->assertSame($source['fingerprint'], $published->fingerprint);
        $this->assertSame('published', $published->status);
        $this->assertSame('published', $published->blog->status);
        $this->assertSame($run->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        $before = $this->publicationRecords();

        $service->activate($published, $published->revision, app(BlogContentService::class)->snapshot('geo', $published)['revision']);

        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame(1, $published->deliveries()->where('channel', 'site')->firstOrFail()->attempts);
        Http::assertSentCount(20);
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public static function conflictingGitActivationSources(): array
    {
        return [
            'ready run different commit' => ['commit', false],
            'ready run different source bytes' => ['sha256', false],
            'ready run different normalized article' => ['fingerprint', false],
            'published run different commit' => ['commit', true],
            'published run different source bytes' => ['sha256', true],
            'published run different normalized article' => ['fingerprint', true],
        ];
    }

    #[DataProvider('conflictingGitActivationSources')]
    public function test_conflicting_git_expectations_never_mutate_a_ready_or_published_run(string $field, bool $alreadyPublished): void
    {
        [$run, $source] = $this->importGitReady();
        $service = app(BlogPublicationService::class);
        $this->fakePublishedPages($run);
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        if ($alreadyPublished) {
            $run = $service->activate($run, $run->revision, $snapshot['revision'], $source);
            $this->fakePublishedPages($run);
        }
        $before = $this->publicationRecords();
        $expected = $source;
        $expected[$field] = $field === 'commit' ? str_repeat('b', 40) : hash('sha256', 'conflicting '.$field);

        $this->assertHttpRejected(fn () => $service->activate($run, $run->revision, $snapshot['revision'], $expected), 409);

        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame($source, $run->fresh()->payload['git_source']);
        $this->assertSame($alreadyPublished ? 'published' : 'ready', $run->fresh()->status);
        Http::assertNothingSent(); Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public static function malformedGitActivationSources(): array
    {
        return [
            'another website source path' => ['path'],
            'uppercase commit' => ['commit'],
            'missing source field' => ['missing'],
            'unsupported source field' => ['extra'],
        ];
    }

    #[DataProvider('malformedGitActivationSources')]
    public function test_malformed_git_activation_metadata_is_rejected_before_any_public_checks(string $fault): void
    {
        [$run, $source] = $this->importGitReady();
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $before = $this->publicationRecords();
        $expected = $source;
        if ($fault === 'path') {
            $expected['path'] = 'resources/content/blog-publications/getiptv/2026-10-28.json';
        } elseif ($fault === 'commit') {
            $expected['commit'] = strtoupper($expected['commit']);
        } elseif ($fault === 'missing') {
            unset($expected['sha256']);
        } else {
            $expected['payload'] = 'An unexpected source metadata field';
        }

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision'], $expected), 422);

        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame('draft', $run->blog->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_git_expectations_cannot_relabel_an_existing_non_git_publication(): void
    {
        $run = $this->importReady();
        $source = $this->gitSource($this->payload());
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $before = $this->publicationRecords();

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision'], $source), 409);

        $this->assertSame($before, $this->publicationRecords());
        $this->assertArrayNotHasKey('git_source', $run->fresh()->payload);
        Http::assertNothingSent();
    }

    public static function concurrentGitSourceChanges(): array
    {
        return ['changed committed revision' => [false], 'removed provenance' => [true]];
    }

    #[DataProvider('concurrentGitSourceChanges')]
    public function test_git_source_change_during_public_verification_is_rechecked_before_locked_activation(bool $remove): void
    {
        [$run, $source] = $this->importGitReady();
        $snapshot = app(BlogContentService::class)->snapshot('geo', $run);
        $this->fakePublishedPages($run);
        $afterConcurrentEdit = null;
        $this->beforePublicResponse = function () use ($run, $remove, &$afterConcurrentEdit): void {
            $current = $run->fresh();
            $payload = $current->payload;
            if ($remove) {
                unset($payload['git_source']);
            } else {
                $payload['git_source']['commit'] = str_repeat('c', 40);
            }
            $current->update(['payload' => $payload]);
            $afterConcurrentEdit = $this->publicationRecords();
        };

        $this->assertHttpRejected(fn () => app(BlogPublicationService::class)->activate($run, $run->revision, $snapshot['revision'], $source), 409);

        $this->assertNotNull($afterConcurrentEdit);
        $this->assertSame($afterConcurrentEdit, $this->publicationRecords());
        $this->assertSame('ready', $run->fresh()->status);
        $this->assertSame([], $run->fresh()->errors);
        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertSame([], app(BlogContentService::class)->snapshot('geo')['posts']);
        $this->assertSame(0, $run->deliveries()->where('status', 'accepted')->count());
        Http::assertSentCount(10);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'indexnow.org') || str_contains($request->url(), 'googleapis.com'));
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_an_explicit_manual_revision_after_a_git_release_does_not_reuse_daily_provenance(): void
    {
        [$run, $source] = $this->importGitReady();
        $this->fakePublishedPages($run);
        $service = app(BlogPublicationService::class);
        $previous = $service->activate($run, $run->revision, app(BlogContentService::class)->snapshot('geo', $run)['revision'], $source);
        Carbon::setTestNow('2026-10-29T05:00:00+00:00');
        CarbonImmutable::setTestNow('2026-10-29T05:00:00+00:00');
        $blog = $previous->blog->fresh();
        $blog->translations()->where('locale', 'en')->update([
            'title' => 'Geo IPTV later operator editorial device checklist',
            'seo_title' => 'Geo IPTV later operator editorial device checklist',
        ]);
        $blog->update(['status' => 'draft']);
        $candidate = $service->queueExisting($blog->fresh());
        $this->fakePublishedPages($candidate);

        $published = $service->activate($candidate, $candidate->revision, app(BlogContentService::class)->snapshot('geo', $candidate)['revision']);

        $this->assertNull($published->publishing_date);
        $this->assertArrayNotHasKey('git_source', $published->payload);
        $this->assertSame($source, $previous->fresh()->payload['git_source']);
        $this->assertSame('published', $previous->fresh()->status);
        $this->assertSame('published', $published->status);
        $this->assertSame($candidate->revision, app(BlogContentService::class)->snapshot('geo')['posts'][0]['revision']);
        Http::assertSentCount(10);
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public static function reorderedJsonPublicationSites(): array
    {
        return ['main article' => ['main'], 'landing article' => ['geo']];
    }

    #[DataProvider('reorderedJsonPublicationSites')]
    public function test_mysql_object_key_order_does_not_change_eligibility_or_activation(string $site): void
    {
        $service = app(BlogPublicationService::class);
        $service->initializeSettings();
        BlogSiteSetting::where('site_key', $site)->update(['enabled' => true]);
        $input = $this->payload($site);
        $source = $this->gitSource($input);
        $run = $service->import($site, '2026-10-28', $input, $source, $source['sha256']);
        $original = $run->payload;
        $run->update(['payload' => $this->mysqlObjectKeyOrder($original)]);
        $this->assertNotSame(array_keys($original['article']['translations']['en']['sections'][0]),
            array_keys($run->fresh()->payload['article']['translations']['en']['sections'][0]));
        $this->assertNotSame($original, $run->fresh()->payload);
        $before = $this->publicationRecords();
        $snapshot = app(BlogContentService::class)->snapshot($site, $run);

        $this->assertSame(['eligible' => true, 'revision' => $run->revision, 'snapshot_revision' => $snapshot['revision']], $service->publicationEligibility($run));
        $this->assertSame($before, $this->publicationRecords());
        Http::assertNothingSent();
        $this->fakePublishedPages($run);
        if ($site === 'main') {
            Artisan::shouldReceive('call')->once()->with('sitemap:generate')->andReturn(0);
        }

        $published = $service->activate($run, $run->revision, $snapshot['revision'], $source);

        $this->assertSame('published', $published->status);
        $this->assertSame('published', $published->blog->status);
        $this->assertSame($before['blog_generation_runs'][0]['payload'], $published->getRawOriginal('payload'));
        $this->assertSame(10, $published->blog->translations()->count());
        $this->assertSame('accepted', $published->deliveries()->where('channel', 'site')->value('status'));
        Http::assertSentCount(10);
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public static function changedStructuredJsonValues(): array
    {
        return array_combine(['paragraph value', 'heading value', 'section order', 'paragraph order', 'item order',
            'link order', 'scalar type', 'list dictionary', 'extra field', 'whitespace value'], array_map(fn ($fault) => [$fault],
            ['paragraph value', 'heading value', 'section order', 'paragraph order', 'item order',
                'link order', 'scalar type', 'list dictionary', 'extra field', 'whitespace value']));
    }

    #[DataProvider('changedStructuredJsonValues')]
    public function test_object_key_canonicalization_still_rejects_changed_values_types_or_list_order(string $fault): void
    {
        $service = app(BlogPublicationService::class);
        $service->initializeSettings();
        BlogSiteSetting::where('site_key', 'main')->update(['enabled' => true]);
        $input = $this->payload('main');
        $input['translations']['en']['sections'][0]['paragraphs'][] = 'This second isolated paragraph makes paragraph ordering observable without changing any real publication.';
        $input['translations']['en']['sections'][0]['items'] = ['12345678', 'An independent second isolated checklist item.'];
        $input['translations']['en']['sections'][0]['links'][] = ['label' => 'Official Apple reset reference', 'url' => 'https://support.apple.com/en-us/108897'];
        $run = $service->import('main', '2026-10-28', $input);
        $payload = $this->mysqlObjectKeyOrder($run->payload);
        $section = &$payload['article']['translations']['en']['sections'][0];
        switch ($fault) {
            case 'paragraph value': $section['paragraphs'][0] .= ' A later changed claim.'; break;
            case 'heading value': $section['heading'] .= ' revised'; break;
            case 'section order': $payload['article']['translations']['en']['sections'] = array_reverse($payload['article']['translations']['en']['sections']); break;
            case 'paragraph order': $section['paragraphs'] = array_reverse($section['paragraphs']); break;
            case 'item order': $section['items'] = array_reverse($section['items']); break;
            case 'link order': $section['links'] = array_reverse($section['links']); break;
            case 'scalar type': $section['items'][0] = 12345678; break;
            case 'list dictionary': $section['paragraphs'] = ['first' => $section['paragraphs'][0], 'second' => $section['paragraphs'][1]]; break;
            case 'extra field': $section['unexpected'] = 'Do not discard unexpected structured data.'; break;
            case 'whitespace value': $section['paragraphs'][0] .= ' '; break;
        }
        unset($section);
        $run->update(['payload' => $payload]);
        $before = $this->publicationRecords();

        $this->assertSame(['eligible' => false, 'error_code' => 'eligibility_http_409'], $service->publicationEligibility($run));

        $this->assertSame($before, $this->publicationRecords());
        $this->assertSame('ready', $run->fresh()->status);
        $this->assertSame('draft', $run->blog->fresh()->status);
        Http::assertNothingSent();
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    private function mysqlObjectKeyOrder(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $value = array_map(fn ($item) => $this->mysqlObjectKeyOrder($item), $value);
        if (!array_is_list($value)) {
            uksort($value, fn ($left, $right) => strlen((string) $left) <=> strlen((string) $right) ?: strcmp((string) $left, (string) $right));
        }
        return $value;
    }

    private function importGitReady(): array
    {
        $service = app(BlogPublicationService::class);
        $service->initializeSettings();
        BlogSiteSetting::where('site_key', 'geo')->update(['enabled' => true]);
        $input = $this->payload();
        $source = $this->gitSource($input);
        return [$service->import('geo', '2026-10-28', $input, $source, $source['sha256']), $source];
    }

    private function gitSource(array $input): array
    {
        $normalized = app(BlogQualityService::class)->normalize($input, $input['site_key']);
        return ['commit' => str_repeat('a', 40),
            'path' => 'resources/content/blog-publications/'.$input['site_key'].'/'.$input['publishing_date'].'.json',
            'sha256' => hash('sha256', json_encode($input, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)),
            'fingerprint' => hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE))];
    }

    private function publicationRecords(): array
    {
        $records = [];
        foreach (['blogs', 'blog_translations', 'blog_generation_runs', 'blog_publish_deliveries', 'blog_site_settings'] as $table) {
            $records[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }
        return $records;
    }

    private function importReady(string $site = 'geo'): BlogGenerationRun
    {
        $service = app(BlogPublicationService::class);
        $service->initializeSettings();
        BlogSiteSetting::where('site_key', $site)->update(['enabled' => true]);
        return $service->import($site, '2026-10-28', $this->payload($site));
    }

    private function publishReady(): BlogGenerationRun
    {
        $run = $this->importReady();
        $this->fakePublishedPages($run);
        return app(BlogPublicationService::class)->activate($run, $run->revision, app(BlogContentService::class)->snapshot('geo', $run)['revision']);
    }

    private function publishedAndEdited(): array
    {
        $previous = $this->publishReady();
        Carbon::setTestNow('2026-10-28T06:00:00+00:00');
        CarbonImmutable::setTestNow('2026-10-28T06:00:00+00:00');
        $blog = $previous->blog->fresh();
        $blog->translations()->where('locale', 'en')->update([
            'title' => 'Geo IPTV revised private editorial device checklist',
            'seo_title' => 'Geo IPTV revised private editorial device checklist',
        ]);
        $blog->update(['status' => 'draft']);
        $candidate = app(BlogPublicationService::class)->queueExisting($blog->fresh());
        Http::fake();
        return [$previous->fresh('blog'), $candidate->fresh('blog')];
    }

    private function payload(string $site = 'geo'): array
    {
        $translations = [];
        $name = config('blog-publishing.sites.'.$site.'.name');
        $source = ['label' => 'Official Google TV troubleshooting', 'url' => 'https://support.google.com/googletv/answer/10070482'];
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $translations[$locale] = [
                'title' => $name.' private publication fixture for '.$locale,
                'excerpt' => 'An isolated '.$locale.' publication summary for a device checklist; this fixture never reaches a real website.',
                'seo_title' => $name.' private device checklist for '.$locale,
                'seo_description' => 'An isolated '.$locale.' description that explains a device baseline, one controlled change and a useful support note.',
                'sections' => [
                    ['heading' => 'Record the '.$locale.' device baseline',
                        'paragraphs' => ['Write down the exact device model, operating system and player version before changing a working setting. This isolated '.$locale.' fixture keeps the original error and connection type together so a later comparison can answer a specific question. Ordinary internet access, the selected service and an existing order status are separate checks. A player installation does not establish that an order is active, and another purchase is not a useful substitute for checking the current reference.'],
                        'items' => ['Keep the original '.$locale.' error message in the private support note.'], 'links' => [$source]],
                    ['heading' => 'Compare one '.$locale.' condition',
                        'paragraphs' => ['Keep the same device and source while comparing a documented player option. Note the result, restore the earlier setting if the comparison does not help and avoid combining a network switch with an app reinstall. The '.$locale.' test record should identify the first failing step instead of treating every interruption as the same problem. Ask for the package allowance when it is not stated, and keep passwords, payment codes and private playlist addresses out of a support message.'],
                        'items' => ['Record the time of the '.$locale.' comparison without sharing credentials.'], 'links' => []],
                ],
            ];
        }
        return ['version' => 1, 'site_key' => $site, 'publishing_date' => '2026-10-28',
            'topic' => 'An isolated '.$site.' device baseline publication sequence', 'primary_keyword' => $name,
            'slug' => $site.'-publication-baseline-check', 'translations' => $translations, 'sources' => [$source]];
    }

    private function fakePublishedPages(BlogGenerationRun $run, ?string $fault = null, string $faultLocale = 'hi'): void
    {
        $post = $run->payload['post'];
        $responses = [];
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $url = app(BlogContentService::class)->canonical($run->site_key, $locale, $post['slug']);
            $row = $post['translations'][$locale];
            $escape = fn (string $value) => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $canonical = $url;
            $title = $row['seo_title'];
            $heading = $row['title'];
            $revision = $post['revision'];
            $identity = $post['id'];
            $meta = '';
            $headers = ['Content-Type' => 'text/html; charset=utf-8'];
            $status = 200;
            $extraHeading = '';
            if ($locale === $faultLocale) {
                match ($fault) {
                    'canonical' => $canonical = 'https://example.invalid/unrelated/',
                    'missing-canonical' => $canonical = '',
                    'title' => $title = 'A different website title',
                    'heading' => $heading = 'A different article heading',
                    'duplicate-heading' => $extraHeading = '<h1>Unexpected second heading</h1>',
                    'meta-noindex' => $meta = '<meta name="robots" content="noindex, follow">',
                    'header-noindex' => $headers['X-Robots-Tag'] = 'noindex',
                    'revision' => $revision = str_repeat('f', 64),
                    'identity' => $identity = $post['id'] + 1,
                    'missing' => $status = 404,
                    'redirect' => $status = 302,
                    default => null,
                };
            }
            $body = '<!doctype html><html lang="'.$locale.'"><head><title>'.$escape($title).'</title>'.$meta
                .($canonical !== '' ? '<link rel="canonical" href="'.$escape($canonical).'">' : '').'</head><body>'
                .'<article data-blog-id="'.$identity.'" data-blog-revision="'.$revision.'"><h1>'.$escape($heading).'</h1>'
                .$extraHeading.'<p>'.$escape($row['sections'][0]['paragraphs'][0]).'</p></article></body></html>';
            $responses[$url] = [$body, $status, $headers];
        }
        $this->publicResponses = $responses;
        Http::fake();
    }

    private function assertHttpRejected(callable $operation, int $status): void
    {
        $error = null;
        try {
            $operation();
        } catch (Throwable $caught) {
            $error = $caught;
        }
        $this->assertInstanceOf(HttpExceptionInterface::class, $error, 'A stale or unauthorized publication must be rejected.');
        $this->assertSame($status, $error->getStatusCode());
    }

    private function assertRuntimeRejected(callable $operation): void
    {
        $error = null;
        try {
            $operation();
        } catch (Throwable $caught) {
            $error = $caught;
        }
        $this->assertInstanceOf(RuntimeException::class, $error, 'An unverified candidate must remain unpublished.');
    }
}
