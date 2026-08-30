<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\GoogleAnalyticsMeasurementProtocolService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendGa4Purchase implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $uniqueFor = 3600;

    public function __construct(public int $orderId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->orderId;
    }

    public function handle(GoogleAnalyticsMeasurementProtocolService $analytics): void
    {
        $claimToken = (string) Str::uuid();
        $claimed = Order::query()
            ->whereKey($this->orderId)
            ->where('payment_status', 'paid')
            ->whereNull('ga_purchase_sent_at')
            ->where(function ($query) {
                $query->whereNull('ga_purchase_processing_at')
                    ->orWhere('ga_purchase_processing_at', '<', now()->subMinutes(20));
            })
            ->update([
                'ga_purchase_processing_at' => now(),
                'ga_purchase_processing_token' => $claimToken,
            ]);

        if ($claimed !== 1) {
            return;
        }

        $order = Order::where('ga_purchase_processing_token', $claimToken)->find($this->orderId);

        if (!$order
            || $order->payment_status !== 'paid'
            || $order->paid_amount === null
            || !$order->paid_currency
            || $order->ga_purchase_sent_at) {
            Order::whereKey($this->orderId)->where('ga_purchase_processing_token', $claimToken)->update([
                'ga_purchase_processing_at' => null,
                'ga_purchase_processing_token' => null,
            ]);
            return;
        }

        try {
            $result = $analytics->sendPurchase($order);
            $order->forceFill([
                'ga_purchase_sent_at' => !($result['skipped'] ?? false) ? now() : null,
                'ga_purchase_processing_at' => null,
                'ga_purchase_processing_token' => null,
            ])->save();
        } catch (\Throwable $exception) {
            $order->forceFill([
                'ga_purchase_processing_at' => null,
                'ga_purchase_processing_token' => null,
            ])->save();
            throw $exception;
        }
    }
}
