<?php

namespace App\Services\DigitalCommerce;

use App\Models\Digital\DigitalProduct;

class CartService
{
    private const SESSION_KEY = 'digital_cart';

    public function all(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function add(DigitalProduct $product, int $quantity = 1): void
    {
        $cart = $this->all();
        $currentQty = (int) ($cart[$product->id]['quantity'] ?? 0);
        $newQty = $currentQty + max(1, $quantity);

        $cart[$product->id] = [
            'product_id' => $product->id,
            'quantity' => $this->clampQuantity($product, $newQty),
        ];

        session([self::SESSION_KEY => $cart]);
    }

    public function update(DigitalProduct $product, int $quantity): void
    {
        $cart = $this->all();

        if ($quantity < 1) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'quantity' => $this->clampQuantity($product, $quantity),
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $productId): void
    {
        $cart = $this->all();
        unset($cart[$productId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function itemsDetailed(): array
    {
        $cart = $this->all();
        if (empty($cart)) {
            return [];
        }

        $products = DigitalProduct::query()
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $items = [];

        foreach ($cart as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $product = $products->get($productId);
            if (!$product) {
                continue;
            }

            $quantity = $this->clampQuantity($product, (int) ($row['quantity'] ?? 1));
            $lineTotal = $quantity * (float) $product->price;

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => round($lineTotal, 2),
            ];
        }

        return $items;
    }

    public function totals(): array
    {
        $items = $this->itemsDetailed();
        $subtotal = (float) collect($items)->sum('line_total');
        $discount = 0.0;
        $total = max(0, $subtotal - $discount);

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
        ];
    }

    private function clampQuantity(DigitalProduct $product, int $qty): int
    {
        $qty = max($product->min_qty ?: 1, $qty);

        if ($product->max_qty) {
            $qty = min($product->max_qty, $qty);
        }

        return $qty;
    }
}
