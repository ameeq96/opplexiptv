<?php

namespace App\Http\Controllers\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\DigitalCommerce\AddToCartRequest;
use App\Http\Requests\DigitalCommerce\UpdateCartRequest;
use App\Models\Digital\DigitalProduct;
use App\Services\DigitalCommerce\CartService;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    public function index()
    {
        $totals = $this->cartService->totals();

        return view('pages.digital-commerce.cart', [
            'items' => $totals['items'],
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
        ]);
    }

    public function add(AddToCartRequest $request, DigitalProduct $digital_product)
    {
        abort_if(!$digital_product->is_active, 404);

        $qty = (int) ($request->validated()['quantity'] ?? 1);
        $this->cartService->add($digital_product, $qty);

        return back()->with('success', 'Product added to cart.');
    }

    public function update(UpdateCartRequest $request, DigitalProduct $digital_product)
    {
        $qty = (int) $request->validated()['quantity'];
        $this->cartService->update($digital_product, $qty);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(DigitalProduct $digital_product)
    {
        $this->cartService->remove($digital_product->id);

        return back()->with('success', 'Item removed.');
    }
}
