<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Clients\CustomerActivityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CustomerOrderObserver
{
    public function created(Order $order): void
    {
        $this->record($order, 'order.created');
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged(['user_id', 'status', 'payment_status', 'paid_at', 'buying_date', 'expiry_date', 'duration'])) {
            $this->record($order, 'order.updated');
        }
    }

    private function record(Order $order, string $type): void
    {
        if (!Schema::hasTable('customer_activities')) {
            return;
        }
        $fields = ['status', 'payment_status', 'paid_at', 'buying_date', 'expiry_date', 'duration', 'currency', 'paid_currency', 'paid_amount'];
        app(CustomerActivityService::class)->record([
            'source' => 'iptv', 'event_type' => $type, 'entity_type' => 'order', 'entity_id' => (string) $order->id,
            'user_id' => $order->user_id, 'order_id' => $order->id, 'admin_id' => Auth::guard('admin')->id(),
            'details' => [
                'current' => $order->only($fields),
                'previous' => $type === 'order.updated' ? array_intersect_key($order->getRawOriginal(), array_flip($fields)) : null,
            ],
        ]);
    }
}
