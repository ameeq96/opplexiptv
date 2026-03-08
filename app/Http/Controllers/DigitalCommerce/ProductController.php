<?php

namespace App\Http\Controllers\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Models\Digital\DigitalProduct;

class ProductController extends Controller
{
    public function index()
    {
        return redirect()->route('shop', ['type' => 'digital']);
    }

    public function show(string $slug)
    {
        $product = DigitalProduct::query()
            ->with('category:id,name,slug')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('pages.digital-commerce.product', compact('product'));
    }
}
