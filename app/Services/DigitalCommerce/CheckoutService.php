<?php

namespace App\Services\DigitalCommerce;

use App\Mail\DigitalOrderPlacedMail;
use App\Models\Admin;
use App\Models\Digital\DigitalOrder;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    public function createOrder(array $payload): DigitalOrder
    {
        $totals = $this->cartService->totals();

        if (empty($totals['items'])) {
            abort(422, 'Your cart is empty.');
        }

        return DB::transaction(function () use ($payload, $totals) {
            $user = User::firstOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'phone' => $payload['phone'] ?? null,
                    'password' => bcrypt(Str::random(20)),
                ]
            );

            $order = DigitalOrder::create([
                'user_id' => $user->id,
                'order_number' => $this->nextOrderNumber(),
                'customer_name' => $payload['name'],
                'customer_email' => $payload['email'],
                'customer_phone' => $payload['phone'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'payment_method' => $payload['payment_method'],
                'currency' => 'USD',
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'customer_access_token' => Str::random(64),
            ]);

            foreach ($totals['items'] as $item) {
                $order->items()->create([
                    'digital_product_id' => $item['product']->id,
                    'product_title' => $item['product']->title,
                    'unit_price' => $item['product']->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);
            }

            $this->cartService->clear();
            $this->notify($order);

            return $order->fresh(['items']);
        });
    }

    private function notify(DigitalOrder $order): void
    {
        try {
            Mail::to($order->customer_email)->queue(new DigitalOrderPlacedMail($order, false));

            $adminEmail = config('mail.from.address');
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new DigitalOrderPlacedMail($order, true));
            }

            $admins = Admin::query()->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewOrderNotification([
                    'title' => 'New digital order',
                    'body' => "{$order->customer_name} placed order {$order->order_number}",
                    'digital_order_id' => $order->id,
                    'type' => 'digital_order',
                    'price' => $order->total,
                    'payment_status' => $order->payment_status,
                    'created' => now()->toDateTimeString(),
                ]));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function nextOrderNumber(): string
    {
        return 'DG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
