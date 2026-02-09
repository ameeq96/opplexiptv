<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        $query = ShopProduct::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('asin', 'like', "%{$search}%");
        }

        if ($status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $products = $query
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.shop-products.index', [
            'products' => $products,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('admin.shop-products.create', [
            'product' => new ShopProduct(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);

        $imageName = $this->storeImage($request, $data['asin'] ?? null);
        $data['image'] = $imageName;

        ShopProduct::create($data);

        return redirect()->route('admin.shop-products.index')->with('success', 'Product added.');
    }

    public function edit(ShopProduct $shop_product)
    {
        return view('admin.shop-products.edit', [
            'product' => $shop_product,
        ]);
    }

    public function update(Request $request, ShopProduct $shop_product)
    {
        $data = $this->validateData($request, false);

        if ($request->hasFile('image')) {
            $this->deleteImage($shop_product->image);
            $data['image'] = $this->storeImage($request, $data['asin'] ?? $shop_product->asin);
        }

        $shop_product->update($data);

        return redirect()->route('admin.shop-products.index')->with('success', 'Product updated.');
    }

    public function destroy(ShopProduct $shop_product)
    {
        $this->deleteImage($shop_product->image);
        $shop_product->delete();

        return redirect()->route('admin.shop-products.index')->with('success', 'Product deleted.');
    }

    private function validateData(Request $request, bool $isCreate): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'asin' => ['nullable', 'string', 'max:32'],
            'link' => ['required', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($isCreate) {
            $rules['image'] = ['required', 'image', 'mimes:webp,jpg,jpeg,png', 'max:2048'];
        } else {
            $rules['image'] = ['nullable', 'image', 'mimes:webp,jpg,jpeg,png', 'max:2048'];
        }

        $data = $request->validate($rules);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function storeImage(Request $request, ?string $asin): string
    {
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $base = $asin ? Str::slug($asin) : Str::random(10);
        $filename = $base . '-' . time() . '.' . $ext;

        $dest = public_path('images/shop');
        if (!is_dir($dest)) {
            mkdir($dest, 0775, true);
        }

        $file->move($dest, $filename);

        return $filename;
    }

    private function deleteImage(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = public_path('images/shop/' . $filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
