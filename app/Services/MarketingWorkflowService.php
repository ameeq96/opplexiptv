<?php

namespace App\Services;

use App\Jobs\SendGa4Purchase;
use App\Jobs\SendMarketingDelivery;
use App\Models\CheckoutDraft;
use App\Models\MarketingDelivery;
use App\Models\Order;
use App\Models\Referral;
use Illuminate\Support\Str;
use Throwable;

class MarketingWorkflowService
{
    public function retryConfigurationFailures(): int
    {
        return MarketingDelivery::query()
            ->where('last_error', 'missing_whatsapp_template_config')
            ->whereNull('sent_at')
            ->update([
                'attempts' => 0,
                'failed_at' => null,
                'scheduled_at' => now(),
                'processing_at' => null,
                'processing_token' => null,
            ]);
    }

    public function dispatchDue(): int
    {
        $this->createAbandonedDeliveries();
        $this->createOnboardingDeliveries();
        $this->createRenewalDeliveries();
        $this->createReferralDeliveries();

        $deliveries = MarketingDelivery::query()
            ->whereNull('sent_at')
            ->whereNull('failed_at')
            ->where('scheduled_at', '<=', now())
            ->where('attempts', '<', 3)
            ->orderBy('id')
            ->limit(250)
            ->get();

        foreach ($deliveries as $delivery) {
            try {
                SendMarketingDelivery::dispatch($delivery->id);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $purchaseOrderIds = Order::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('analytics_consented_at')
            ->whereNotNull('ga_client_id')
            ->whereNotNull('paid_amount')
            ->whereNotNull('paid_currency')
            ->whereNull('ga_purchase_sent_at')
            ->orderBy('id')
            ->limit(250)
            ->pluck('id')
            ->all();

        foreach ($purchaseOrderIds as $id) {
            try {
                SendGa4Purchase::dispatch($id);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        CheckoutDraft::query()
            ->where('retention_expires_at', '<', now())
            ->delete();

        $deliveryCutoff = now()->subDays(
            max(30, (int) config('services.marketing.delivery_retention_days', 90))
        );
        MarketingDelivery::query()
            ->where(function ($query) {
                $query->whereNotNull('sent_at')->orWhereNotNull('failed_at');
            })
            ->where('updated_at', '<', $deliveryCutoff)
            ->update([
                'user_id' => null,
                'order_id' => null,
                'checkout_draft_id' => null,
                'referral_id' => null,
                'payload' => null,
                'provider_message_id' => null,
                'last_error' => null,
                'processing_at' => null,
                'processing_token' => null,
            ]);

        return $deliveries->count();
    }

    private function createAbandonedDeliveries(): void
    {
        $cutoff = now()->subMinutes(max(15, (int) config('services.marketing.abandoned_after_minutes', 60)));

        CheckoutDraft::query()
            ->whereNull('completed_at')
            ->where('last_activity_at', '<=', $cutoff)
            ->where('retention_expires_at', '>', now())
            ->chunkById(100, function ($drafts) {
                foreach ($drafts as $draft) {
                    if ($draft->email && $draft->email_consented_at) {
                        $this->delivery(
                            'abandoned:' . $draft->id . ':email',
                            'abandoned',
                            'first',
                            'email',
                            $draft->locale,
                            checkoutDraftId: $draft->id,
                            payload: []
                        );
                    }
                    if ($draft->phone && $draft->whatsapp_consented_at) {
                        $this->delivery(
                            'abandoned:' . $draft->id . ':whatsapp',
                            'abandoned',
                            'first',
                            'whatsapp',
                            $draft->locale,
                            checkoutDraftId: $draft->id,
                            payload: []
                        );
                    }
                }
            });
    }

    private function createOnboardingDeliveries(): void
    {
        Order::query()
            ->with(['user', 'device'])
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    $this->forConsentedChannels($order, 'onboarding', 'active', [
                        'package' => $order->package,
                        'device' => $order->device?->name,
                    ]);
                }
            });
    }

    private function createRenewalDeliveries(): void
    {
        foreach ([7, 1] as $days) {
            $start = now()->addDays($days)->startOfDay();
            $end = now()->addDays($days)->endOfDay();

            Order::query()
                ->with('user')
                ->where('status', 'active')
                ->where('payment_status', 'paid')
                ->whereBetween('expiry_date', [$start, $end])
                ->chunkById(100, function ($orders) use ($days) {
                    foreach ($orders as $order) {
                        $expiryDate = $order->expiry_date?->format('Y-m-d');
                        $this->forConsentedChannels($order, 'renewal', 'expires-' . $expiryDate . '-day-' . $days, [
                            'package' => $order->package,
                            'expiry' => $order->expiry_date?->toFormattedDateString(),
                            'expiry_date' => $expiryDate,
                            'days' => $days,
                        ]);
                    }
                });
        }
    }

    private function createReferralDeliveries(): void
    {
        $cutoff = now()->subDays(max(0, (int) config('services.marketing.referral_after_days', 3)));

        Order::query()
            ->with('user')
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('paid_at', '<=', $cutoff)
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    if (!$order->user
                        || (!$order->user->hasMarketingConsent('email')
                            && !$order->user->hasMarketingConsent('whatsapp'))) {
                        continue;
                    }

                    $referral = Referral::firstOrCreate(
                        ['source_order_id' => $order->id],
                        [
                            'code' => $this->uniqueReferralCode(),
                            'referrer_user_id' => $order->user_id,
                            'status' => 'available',
                            'reward_type' => config('services.marketing.referral_reward_type'),
                            'reward_value' => config('services.marketing.referral_reward_value'),
                            'reward_currency' => config('services.marketing.referral_reward_currency'),
                        ]
                    );

                    $this->forConsentedChannels($order, 'referral', 'invite', [
                        'package' => $order->package,
                        'referral_id' => $referral->id,
                    ], $referral->id);
                }
            });
    }

    private function forConsentedChannels(
        Order $order,
        string $workflow,
        string $stage,
        array $payload,
        ?int $referralId = null
    ): void {
        $user = $order->user;
        if (!$user) {
            return;
        }

        foreach (['email', 'whatsapp'] as $channel) {
            if (!$user->hasMarketingConsent($channel)) {
                continue;
            }

            $this->delivery(
                $workflow . ':' . $order->id . ':' . $stage . ':' . $channel,
                $workflow,
                $stage,
                $channel,
                $order->locale ?: $user->marketing_consent_locale ?: 'en',
                userId: $user->id,
                orderId: $order->id,
                referralId: $referralId,
                payload: $payload
            );
        }
    }

    private function delivery(
        string $dedupeKey,
        string $workflow,
        string $stage,
        string $channel,
        string $locale,
        ?int $userId = null,
        ?int $orderId = null,
        ?int $checkoutDraftId = null,
        ?int $referralId = null,
        array $payload = []
    ): void {
        MarketingDelivery::firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'workflow' => $workflow,
                'stage' => $stage,
                'channel' => $channel,
                'user_id' => $userId,
                'order_id' => $orderId,
                'checkout_draft_id' => $checkoutDraftId,
                'referral_id' => $referralId,
                'locale' => in_array($locale, config('app.locales', ['en']), true) ? $locale : 'en',
                'payload' => $payload,
                'scheduled_at' => now(),
            ]
        );
    }

    private function uniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (Referral::where('code', $code)->exists());

        return $code;
    }
}
