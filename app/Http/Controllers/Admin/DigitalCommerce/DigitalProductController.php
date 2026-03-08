<?php

namespace App\Http\Controllers\Admin\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DigitalCommerce\StoreDigitalProductRequest;
use App\Http\Requests\Admin\DigitalCommerce\UpdateDigitalProductRequest;
use App\Models\Digital\DigitalCategory;
use App\Models\Digital\DigitalProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DigitalProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $products = DigitalProduct::query()
            ->with('category:id,name')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($status !== '', fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.digital-products.index', compact('products', 'search', 'status'));
    }

    public function create()
    {
        return view('admin.digital-products.create', [
            'product' => new DigitalProduct(),
            'categories' => DigitalCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreDigitalProductRequest $request)
    {
        $data = $this->normalized($request->validated());

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'), $data['slug']);
        }

        DigitalProduct::create($data);

        return redirect()->route('admin.digital-products.index')->with('success', 'Product created.');
    }

    public function edit(DigitalProduct $digital_product)
    {
        return view('admin.digital-products.edit', [
            'product' => $digital_product,
            'categories' => DigitalCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateDigitalProductRequest $request, DigitalProduct $digital_product)
    {
        $data = $this->normalized($request->validated());

        if ($request->hasFile('image')) {
            $this->deleteImage($digital_product->image);
            $data['image'] = $this->storeImage($request->file('image'), $data['slug']);
        }

        $digital_product->update($data);

        return redirect()->route('admin.digital-products.index')->with('success', 'Product updated.');
    }

    public function destroy(DigitalProduct $digital_product)
    {
        $this->deleteImage($digital_product->image);
        $digital_product->delete();

        return redirect()->route('admin.digital-products.index')->with('success', 'Product deleted.');
    }

    private function normalized(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['product_type'] = 'digital';
        $data['currency'] = strtoupper((string) ($data['currency'] ?? 'USD'));
        $data['min_qty'] = (int) ($data['min_qty'] ?? 1);
        $data['max_qty'] = $data['max_qty'] ?? null;

        if (($data['max_qty'] ?? null) !== null && (int) $data['max_qty'] < $data['min_qty']) {
            $data['max_qty'] = $data['min_qty'];
        }

        $meta = (array) ($data['metadata'] ?? []);
        $data['metadata'] = array_filter($meta, fn ($v) => trim((string) $v) !== '');

        return $data;
    }

    private function storeImage($file, string $slug): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::slug($slug) . '-' . time() . '.' . $ext;
        $destination = public_path('images/digital-products');

        if (!is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);

        return $filename;
    }

    private function deleteImage(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = public_path('images/digital-products/' . $filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
