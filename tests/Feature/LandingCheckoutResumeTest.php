<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\LandingCheckoutResumeMail;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LandingCheckoutResumeTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('cache.default'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests(); Mail::fake(); Notification::fake(); Bus::fake(); Cache::flush();
        Package::query()->update(['active' => false]);
        config()->set('services.app.default_currency', 'USD');
        config()->set('services.opplexify.url', 'https://opplexify.com');
        config()->set('services.opplexify.shared_secret', 'isolated-plan-secret');
    }

    public function test_saved_plan_is_private_expiring_and_does_not_start_a_payment_or_send_email(): void
    {
        $plan = $this->plan();
        $result = $this->createLink($plan);
        $this->assertSame(201, $result->status(), $result->getContent());
        $result->assertJsonPath('data.email_sent', false);
        $url = $result->json('data.url');
        $this->assertStringContainsString('/checkout/resume/', $url);
        $this->assertStringNotContainsString('signature=', $url);
        $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')->assertSee('USD 9.99');
        $response = $this->postJson($this->confirmUrl($url), ['confirm_selection' => true, 'amount_minor' => 999, 'currency' => 'USD'])->assertOk();
        parse_str(parse_url($response->json('redirect_url'), PHP_URL_QUERY), $query);
        $this->assertSame('opplexiptv-store', $query['utm_source']);
        $this->assertSame((string) $plan->id, $query['package_id']);
        $this->assertSame('landing', $query['utm_medium']);
        $this->assertStringContainsString('no-store', $result->headers->get('Cache-Control'));
        foreach (['orders', 'checkout_drafts', 'customer_activities', 'customer_external_links'] as $table) $this->assertDatabaseCount($table, 0);
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched(); Http::assertNothingSent();
        $this->travel(25)->hours();
        $this->get($url)->assertStatus(410);
        $this->postJson($this->confirmUrl($url), ['confirm_selection' => true, 'amount_minor' => 999, 'currency' => 'USD'])->assertStatus(410);
        $this->travelBack();
    }

    public function test_price_change_requires_fresh_confirmation_and_unavailable_plan_cannot_continue(): void
    {
        $plan = $this->plan();
        $url = $this->createLink($plan)->assertCreated()->json('data.url');
        $plan->update(['price_amount' => 12.99]);
        $this->get($url)->assertOk()->assertSee('USD 12.99');
        $this->postJson($this->confirmUrl($url), ['confirm_selection' => true, 'amount_minor' => 999, 'currency' => 'USD'])->assertConflict();
        $this->postJson($this->confirmUrl($url), ['confirm_selection' => false, 'amount_minor' => 1299, 'currency' => 'USD'])->assertUnprocessable();
        $this->postJson($this->confirmUrl($url), ['confirm_selection' => true, 'amount_minor' => 1299, 'currency' => 'USD'])->assertOk();
        $plan->update(['is_available' => false]);
        $this->get($url)->assertStatus(410);
        $this->postJson($this->confirmUrl($url), ['confirm_selection' => true, 'amount_minor' => 1299, 'currency' => 'USD'])->assertStatus(410);
    }

    public function test_email_is_only_sent_once_on_explicit_consent_and_not_added_to_crm(): void
    {
        $plan = $this->plan();
        $this->createLink($plan, ['email' => 'one-time@example.test'])->assertUnprocessable();
        $this->createLink($plan, ['email' => 'one-time@example.test', 'one_send_consent' => false])->assertUnprocessable();
        Mail::assertNothingSent();
        $this->createLink($plan, ['email' => 'one-time@example.test', 'one_send_consent' => true])->assertCreated()->assertJsonPath('data.email_sent', true);
        Mail::assertSent(LandingCheckoutResumeMail::class, fn ($mail) => $mail->hasTo('one-time@example.test') && str_contains($mail->resumeUrl, '/checkout/resume/'));
        $this->createLink($plan, ['email' => 'one-time@example.test', 'one_send_consent' => true])->assertStatus(429);
        Mail::assertSentCount(1);
        $this->assertDatabaseCount('users', 0); $this->assertDatabaseCount('trial_clicks', 0);
    }

    public function test_mismatched_origin_brand_invalid_token_and_honeypot_are_rejected(): void
    {
        $plan = $this->plan();
        $this->postJson('/api/v1/landing/resume-links', ['brand' => 'opplex', 'package_id' => $plan->id, 'locale' => 'en'], ['Origin' => 'https://foreign.example'])->assertForbidden();
        $this->createLink($plan, ['brand' => 'geo'])->assertForbidden();
        $this->createLink($plan, ['website' => 'bot'])->assertUnprocessable();
        $this->createLink($plan, ['locale' => 'unknown'])->assertUnprocessable();
        $this->get('/checkout/resume/'.str_repeat('x', 100))->assertStatus(410);
        Mail::assertNothingSent(); Http::assertNothingSent();
    }

    public function test_confirmation_requires_browser_csrf(): void
    {
        $url = $this->createLink($this->plan())->assertCreated()->json('data.url');
        $this->app->instance(VerifyCsrfToken::class, new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken {
            protected function runningUnitTests() { return false; }
        });
        $this->withSession(['_token' => 'isolated-browser-token'])->postJson($this->confirmUrl($url), [
            'confirm_selection' => true, 'amount_minor' => 999, 'currency' => 'USD',
        ])->assertStatus(419);
    }

    private function createLink(Package $plan, array $extra = [])
    {
        return $this->postJson('/api/v1/landing/resume-links', array_merge([
            'brand' => 'opplex', 'package_id' => $plan->id, 'locale' => 'en', 'website' => '',
        ], $extra), ['Origin' => 'https://opplexiptv.store']);
    }

    private function confirmUrl(string $url): string
    {
        return '/api/browser'.parse_url($url, PHP_URL_PATH);
    }

    private function plan(): Package
    {
        return Package::create(['type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Opplex iptv', 'price_amount' => 9.99,
            'duration_months' => 1, 'features' => ['Isolated fixture'], 'active' => true, 'is_available' => true]);
    }
}
