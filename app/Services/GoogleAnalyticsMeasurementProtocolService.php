<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class GoogleAnalyticsMeasurementProtocolService
{
    public function sendPurchase(Order $order): array
    {
        $measurementId = config('services.google.analytics_id');
        $apiSecret = config('services.google.measurement_protocol_secret');

        if (!$measurementId || !$apiSecret) {
            return ['skipped' => true, 'reason' => 'missing_config'];
        }

        if (!$order->analytics_consented_at || !$order->ga_client_id) {
            return ['skipped' => true, 'reason' => 'analytics_not_consented'];
        }

        $value = (float) $order->paid_amount;
        $transactionId = 'order-' . $order->id;

        $response = Http::asJson()
            ->connectTimeout(3)
            ->timeout(8)
            ->post('https://www.google-analytics.com/mp/collect?' . http_build_query([
                'measurement_id' => $measurementId,
                'api_secret' => $apiSecret,
            ]), [
                'client_id' => $order->ga_client_id,
                'events' => [[
                    'name' => 'purchase',
                    'params' => [
                        'transaction_id' => $transactionId,
                        'currency' => strtoupper((string) ($order->paid_currency ?: 'USD')),
                        'value' => $value,
                        'engagement_time_msec' => 1,
                        'items' => [[
                            'item_id' => (string) ($order->package_id ?: $order->id),
                            'item_name' => (string) $order->package,
                            'item_category' => (string) $order->type,
                            'price' => $value,
                            'quantity' => 1,
                        ]],
                    ],
                ]],
            ]);

        if (!$response->successful()) {
            $response->throw();
        }

        return ['skipped' => false, 'status' => $response->status()];
    }
}
