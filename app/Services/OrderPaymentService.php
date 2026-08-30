<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Referral;

class OrderPaymentService
{
    public function markPaid(
        Order $order,
        string $provider,
        string $transactionId,
        float $amount,
        string $currency
    ): Order
    {
        if ($order->payment_status !== 'paid') {
            $order->forceFill([
                'payment_status' => 'paid',
                'paid_at' => $order->paid_at ?: now(),
                'paid_amount' => $amount,
                'paid_currency' => strtoupper($currency),
                'payment_provider' => $provider,
                'provider_transaction_id' => $transactionId,
            ])->save();
        }

        Referral::query()
            ->where('referred_order_id', $order->id)
            ->whereIn('status', ['captured', 'available'])
            ->update([
                'status' => 'qualified',
                'qualified_at' => now(),
                'updated_at' => now(),
            ]);

        return $order->refresh();
    }
}
