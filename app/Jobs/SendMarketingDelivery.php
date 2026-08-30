<?php

namespace App\Jobs;

use App\Mail\MarketingWorkflowMail;
use App\Models\MarketingDelivery;
use App\Services\WhatsAppCloudService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendMarketingDelivery implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $uniqueFor = 3600;

    public function __construct(public int $deliveryId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->deliveryId;
    }

    public function handle(WhatsAppCloudService $whatsapp): void
    {
        $claimToken = (string) Str::uuid();
        $claimed = MarketingDelivery::query()
            ->whereKey($this->deliveryId)
            ->whereNull('sent_at')
            ->whereNull('failed_at')
            ->where(function ($query) {
                $query->whereNull('processing_at')
                    ->orWhere('processing_at', '<', now()->subMinutes(20));
            })
            ->update(['processing_at' => now(), 'processing_token' => $claimToken]);

        if ($claimed !== 1) {
            return;
        }

        $delivery = MarketingDelivery::with(['user', 'order', 'checkoutDraft', 'referral'])
            ->where('processing_token', $claimToken)
            ->find($this->deliveryId);
        if (!$delivery) {
            return;
        }

        $delivery->increment('attempts');
        $previousLocale = App::getLocale();
        App::setLocale($delivery->locale);

        if (!$this->hasConsent($delivery)) {
            $delivery->update([
                'failed_at' => now(),
                'last_error' => 'Consent unavailable, withdrawn, or workflow no longer eligible.',
                'processing_at' => null,
                'processing_token' => null,
            ]);
            App::setLocale($previousLocale);
            return;
        }

        try {
            $delivery = MarketingDelivery::with(['user', 'order', 'checkoutDraft', 'referral'])
                ->whereKey($delivery->id)
                ->where('processing_token', $claimToken)
                ->whereNull('sent_at')
                ->whereNull('failed_at')
                ->first();
            if (!$delivery) {
                return;
            }
            if (!$this->hasConsent($delivery)) {
                $delivery->update([
                    'failed_at' => now(),
                    'last_error' => 'Consent unavailable, withdrawn, or workflow no longer eligible.',
                    'processing_at' => null,
                    'processing_token' => null,
                ]);
                return;
            }

            $payload = $delivery->payload ?: [];
            $name = (string) ($delivery->user?->name ?? $delivery->checkoutDraft?->name ?? '');
            $package = (string) ($payload['package'] ?? $delivery->order?->package ?? '');
            $expiry = (string) ($payload['expiry'] ?? '');
            $device = (string) ($payload['device'] ?? __('marketing.workflows.onboarding.default_device'));
            $ctaUrl = $this->ctaUrl($delivery);
            $replace = compact('name', 'package', 'expiry', 'device');

            if ($delivery->channel === 'email') {
                $recipient = $delivery->user?->email ?: $delivery->checkoutDraft?->email;
                if (!$recipient) {
                    throw new RuntimeException('Email recipient is missing.');
                }

                $unsubscribeUrl = URL::signedRoute(
                    'marketing.unsubscribe',
                    array_filter([
                        'user' => $delivery->user_id,
                        'draft' => $delivery->checkoutDraft?->token,
                        'channel' => 'all',
                        'locale' => $delivery->locale,
                    ])
                );

                Mail::to($recipient)->send((new MarketingWorkflowMail(
                    Lang::get('marketing.workflows.' . $delivery->workflow . '.subject', $replace),
                    Lang::get('marketing.workflows.' . $delivery->workflow . '.body', $replace),
                    Lang::get('marketing.workflows.' . $delivery->workflow . '.cta'),
                    $ctaUrl,
                    $unsubscribeUrl,
                ))->locale($delivery->locale));
            } else {
                $phone = $delivery->user?->phone ?: $delivery->checkoutDraft?->phone;
                if (!$phone) {
                    throw new RuntimeException('WhatsApp recipient is missing.');
                }

                $result = $whatsapp->sendTemplate($phone, $delivery->workflow, $delivery->locale, [
                    $name ?: 'Customer',
                    $package ?: '-',
                    $ctaUrl,
                    $this->preferenceUrl($delivery),
                ]);
                if ($result['skipped'] ?? false) {
                    $delivery->update([
                        'failed_at' => $delivery->attempts >= $this->tries ? now() : null,
                        'last_error' => $result['reason'],
                        'scheduled_at' => now()->addHours(6),
                        'processing_at' => null,
                        'processing_token' => null,
                    ]);
                    return;
                }
                $delivery->provider_message_id = $result['message_id'] ?? null;
            }

            $delivery->forceFill([
                'sent_at' => now(),
                'last_error' => null,
                'processing_at' => null,
                'processing_token' => null,
            ])->save();
        } catch (Throwable $exception) {
            $delivery->forceFill([
                'failed_at' => $delivery->attempts >= $this->tries ? now() : null,
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
                'processing_at' => null,
                'processing_token' => null,
            ])->save();
            throw $exception;
        } finally {
            App::setLocale($previousLocale);
        }
    }

    private function hasConsent(MarketingDelivery $delivery): bool
    {
        if ($delivery->workflow === 'abandoned') {
            $draft = $delivery->checkoutDraft;
            if (!$draft || $draft->completed_at || $draft->retention_expires_at?->isPast()) {
                return false;
            }

            return $delivery->channel === 'email'
                ? (bool) $draft->email_consented_at
                : (bool) $draft->whatsapp_consented_at;
        }

        if ($delivery->workflow === 'renewal'
            && ($delivery->payload['expiry_date'] ?? null) !== $delivery->order?->expiry_date?->format('Y-m-d')) {
            return false;
        }

        return $delivery->order?->status === 'active'
            && $delivery->order?->payment_status === 'paid'
            && (bool) $delivery->user?->hasMarketingConsent($delivery->channel);
    }

    private function ctaUrl(MarketingDelivery $delivery): string
    {
        if ($delivery->workflow === 'abandoned' && $delivery->checkoutDraft) {
            $draft = $delivery->checkoutDraft;

            return route('checkout', array_filter([
                'package_id' => $draft->package_id,
                'device_id' => $draft->device_id,
                'iptv_vendor' => $draft->vendor,
                'connection_name' => $draft->connection_name,
                'connection_price' => $draft->connection_price,
            ], fn ($value) => $value !== null && $value !== ''));
        }

        if ($delivery->workflow === 'referral' && $delivery->referral) {
            return route('referrals.capture', ['code' => $delivery->referral->code]);
        }

        if ($delivery->workflow === 'onboarding') {
            return route('iptv-applications');
        }

        return route('pricing');
    }

    private function preferenceUrl(MarketingDelivery $delivery): string
    {
        return URL::signedRoute(
            'marketing.unsubscribe',
            array_filter([
                'user' => $delivery->user_id,
                'draft' => $delivery->checkoutDraft?->token,
                'channel' => 'all',
                'locale' => $delivery->locale,
            ])
        );
    }
}
