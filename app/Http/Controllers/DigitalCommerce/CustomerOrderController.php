<?php

namespace App\Http\Controllers\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Models\Digital\DigitalOrder;

class CustomerOrderController extends Controller
{
    public function access(string $token)
    {
        $order = DigitalOrder::query()->where('customer_access_token', $token)->firstOrFail();

        session([
            'digital_customer_email' => $order->customer_email,
            'digital_customer_token' => $order->customer_access_token,
        ]);

        return redirect()->route('digital.orders.index');
    }

    public function index()
    {
        $email = (string) session('digital_customer_email', '');
        abort_if($email === '', 403);

        $orders = DigitalOrder::query()
            ->where('customer_email', $email)
            ->orderByDesc('id')
            ->paginate(12);

        return view('pages.digital-commerce.orders', compact('orders'));
    }

    public function show(DigitalOrder $digital_order)
    {
        $email = (string) session('digital_customer_email', '');
        abort_if($email === '' || strcasecmp($email, $digital_order->customer_email) !== 0, 403);

        $digital_order->load(['items.deliveryPayload']);

        return view('pages.digital-commerce.order-show', compact('digital_order'));
    }
}
