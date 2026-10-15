<?php

namespace Tests\Feature;

use App\Jobs\SendMarketingDelivery;
use App\Mail\MarketingWorkflowMail;
use App\Models\MarketingDelivery;
use App\Models\Order;
use App\Models\User;
use App\Services\EventPromotionService;
use App\Services\MarketingWorkflowService;
use App\Services\WhatsAppCloudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use ReflectionMethod;
use Tests\TestCase;

class CustomerRenewalPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::preventStrayRequests();
    }

    public function test_separate_preference_preserves_legacy_consent_only_while_unset(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->wantsRenewalEmail());

        $user->forceFill(['marketing_email_consented_at' => now()->subMinute()])->save();
        $this->assertTrue($user->wantsRenewalEmail());
        $user->forceFill(['renewal_email_enabled' => false])->save();
        $this->assertFalse($user->wantsRenewalEmail());
        $this->assertTrue($user->hasMarketingConsent('email'));

        $user->forceFill([
            'marketing_email_consented_at' => null,
            'renewal_email_enabled' => true,
        ])->save();
        $this->assertTrue($user->wantsRenewalEmail());
        $this->assertFalse($user->hasMarketingConsent('email'));
    }

    public function test_scheduler_honours_renewal_preference_without_granting_promotional_consent(): void
    {
        $renewalOnly = User::factory()->create(['renewal_email_enabled' => true]);
        $optedOut = User::factory()->create([
            'renewal_email_enabled' => false,
            'marketing_email_consented_at' => now(),
        ]);
        $legacy = User::factory()->create(['marketing_email_consented_at' => now()]);
        foreach ([$renewalOnly, $optedOut, $legacy] as $user) {
            $this->order($user);
        }

        $this->scheduleRenewals();
        $this->scheduleRenewals();

        $this->assertDatabaseCount('marketing_deliveries', 2);
        foreach ([$renewalOnly, $legacy] as $user) {
            $this->assertDatabaseHas('marketing_deliveries', [
                'user_id' => $user->id, 'workflow' => 'renewal', 'channel' => 'email',
            ]);
        }
        $this->assertDatabaseMissing('marketing_deliveries', ['user_id' => $optedOut->id]);
        $this->assertFalse($renewalOnly->fresh()->hasMarketingConsent('email'));
        Mail::assertNothingSent();
    }

    public function test_queued_renewal_is_cancelled_when_customer_opts_out_before_sending(): void
    {
        $user = User::factory()->create(['renewal_email_enabled' => true]);
        $this->order($user);
        $this->scheduleRenewals();
        $delivery = MarketingDelivery::firstOrFail();
        $user->update(['renewal_email_enabled' => false, 'renewal_email_updated_at' => now()]);

        $this->send($delivery);

        Mail::assertNothingSent();
        $this->assertNotNull($delivery->fresh()->failed_at);
        $this->assertNull($delivery->fresh()->sent_at);
    }

    public function test_renewal_only_email_uses_order_renewal_and_separate_signed_unsubscribe_links(): void
    {
        $user = User::factory()->create(['renewal_email_enabled' => true]);
        $order = $this->order($user);
        $this->scheduleRenewals();
        $delivery = MarketingDelivery::firstOrFail();

        $this->send($delivery);

        Mail::assertSent(MarketingWorkflowMail::class, function ($mail) use ($user, $order) {
            $this->assertSame(route('customer.orders.renew', ['order' => $order->id]), $mail->ctaUrl);
            $this->assertStringContainsString(
                parse_url(route('customer.renewal.unsubscribe', ['user' => $user->id]), PHP_URL_PATH),
                $mail->unsubscribeUrl
            );
            $this->assertTrue(URL::hasValidSignature(Request::create($mail->unsubscribeUrl)));

            return $mail->hasTo($user->email);
        });
        $this->assertNotNull($delivery->fresh()->sent_at);
        $this->assertFalse($user->fresh()->hasMarketingConsent('email'));
    }

    public function test_rescheduled_expiry_prevents_a_stale_renewal_email(): void
    {
        $user = User::factory()->create(['renewal_email_enabled' => true]);
        $order = $this->order($user);
        $this->scheduleRenewals();
        $delivery = MarketingDelivery::firstOrFail();
        $order->update(['expiry_date' => now()->addMonth()]);

        $this->send($delivery);

        Mail::assertNothingSent();
        $this->assertNotNull($delivery->fresh()->failed_at);
    }

    public function test_reassigned_order_does_not_send_details_to_its_previous_owner(): void
    {
        $user = User::factory()->create(['renewal_email_enabled' => true]);
        $newOwner = User::factory()->create(['renewal_email_enabled' => true]);
        $order = $this->order($user);
        $this->scheduleRenewals();
        $delivery = MarketingDelivery::firstOrFail();
        $order->update(['user_id' => $newOwner->id]);

        $this->send($delivery);

        Mail::assertNothingSent();
        $this->assertNotNull($delivery->fresh()->failed_at);
    }

    public function test_renewal_opt_in_does_not_allow_other_email_workflows(): void
    {
        $user = User::factory()->create(['renewal_email_enabled' => true]);
        $order = $this->order($user);
        $delivery = MarketingDelivery::create([
            'dedupe_key' => 'customer-renewal-only-onboarding-test',
            'workflow' => 'onboarding',
            'stage' => 'active',
            'channel' => 'email',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'locale' => 'en',
            'scheduled_at' => now(),
        ]);

        $this->send($delivery);

        Mail::assertNothingSent();
        $this->assertNotNull($delivery->fresh()->failed_at);
    }

    public function test_signed_unsubscribe_requires_post_confirmation_and_does_not_change_marketing_consent(): void
    {
        $user = User::factory()->create([
            'renewal_email_enabled' => true,
            'marketing_email_consented_at' => now()->subDay(),
        ]);
        $this->order($user);
        $this->scheduleRenewals();
        $url = URL::signedRoute('customer.renewal.unsubscribe', ['user' => $user->id]);

        $this->get($url)->assertOk();
        $this->assertTrue($user->fresh()->wantsRenewalEmail());
        $this->post(route('customer.renewal.unsubscribe', ['user' => $user->id]))->assertForbidden();
        $this->assertTrue($user->fresh()->wantsRenewalEmail());
        $this->post($url)->assertOk();

        $this->assertFalse($user->fresh()->wantsRenewalEmail());
        $this->assertTrue($user->fresh()->hasMarketingConsent('email'));
        $this->assertNotNull($user->fresh()->renewal_email_updated_at);
        $this->assertNotNull(MarketingDelivery::firstOrFail()->failed_at);
        Mail::assertNothingSent();
    }

    private function order(User $user): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'package' => 'Test monthly service',
            'price' => 10,
            'status' => 'active',
            'payment_status' => 'paid',
            'expiry_date' => now()->addDays(7)->startOfDay(),
            'locale' => 'en',
        ]);
    }

    private function scheduleRenewals(): void
    {
        (new ReflectionMethod(MarketingWorkflowService::class, 'createRenewalDeliveries'))
            ->invoke(app(MarketingWorkflowService::class));
    }

    private function send(MarketingDelivery $delivery): void
    {
        (new SendMarketingDelivery($delivery->id))->handle(
            app(WhatsAppCloudService::class), app(EventPromotionService::class)
        );
    }
}
