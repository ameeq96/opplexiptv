<?php

namespace App\Services\PanelOrders;

use App\Models\Order;
use Illuminate\Http\Request;

class PanelOrderCrudService
{
    public function normalize(array $data, Request $request): array
    {
        if (($data['payment_method'] ?? null) === 'other' && $request->filled('custom_payment_method')) {
            $data['payment_method'] = (string) $request->string('custom_payment_method');
        }
        if (($data['package'] ?? null) === 'other' && $request->filled('custom_package')) {
            $data['package'] = (string) $request->string('custom_package');
        }
        return $data;
    }

    public function create(Request $request): Order
    {
        $validated = $this->normalize($request->validated(), $request);

        $data = collect($validated)->only([
            'user_id','package','price','sell_price','status','currency','payment_method',
            'buying_date','expiry_date','iptv_username','credits','duration','note',
        ])->all();

        $data['type']   = 'reseller';
        $data['profit'] = $data['sell_price'] - $data['price'];

        return Order::create($data);
    }

    public function update(Request $request, Order $order): void
    {
        $validated = $this->normalize($request->validated(), $request);

        $clean = collect($validated)->only([
            'user_id','package','price','sell_price','status','currency','payment_method',
            'buying_date','expiry_date','iptv_username','credits','duration','note',
        ])->all();

        $clean['profit'] = $clean['sell_price'] - $clean['price'];

        $order->update($clean);
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }
}
