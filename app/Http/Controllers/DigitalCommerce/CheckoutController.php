<?php

namespace App\Http\Controllers\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\DigitalCommerce\CheckoutRequest;
use App\Services\DigitalCommerce\CartService;
use App\Services\DigitalCommerce\CheckoutService;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
    ) {
    }

    public function show()
    {
        $totals = $this->cartService->totals();

        if (empty($totals['items'])) {
            return redirect()->route('digital.cart.index')->with('error', 'Your cart is empty.');
        }

        return view('pages.digital-commerce.checkout', $totals);
    }

    public function store(CheckoutRequest $request)
    {
        $order = $this->checkoutService->createOrder($request->validated());

        session([
            'digital_customer_email' => $order->customer_email,
            'digital_customer_token' => $order->customer_access_token,
        ]);

        return redirect()
            ->route('digital.orders.access', $order->customer_access_token)
            ->with('success', 'Order placed successfully.');
    }
}
