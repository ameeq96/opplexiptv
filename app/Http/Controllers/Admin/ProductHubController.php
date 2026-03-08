<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Digital\DigitalProduct;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductHubController extends Controller
{
    public function index(Request $request)
    {
        $type = (string) $request->query('type', 'all');
        $search = trim((string) $request->query('q', ''));

        $affiliate = ShopProduct::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('asin', 'like', "%{$search}%");
            })
            ->with('translations')
            ->get()
            ->map(function (ShopProduct $p) {
                $name = $p->translation()?->name ?: $p->name;
                return [
                    'id' => $p->id,
                    'type' => 'affiliate',
                    'name' => $name,
                    'description' => $p->asin ? 'ASIN: ' . $p->asin : '-',
                    'price' => null,
                    'currency' => null,
                    'is_active' => (bool) $p->is_active,
                    'sort_order' => (int) $p->sort_order,
                    'edit_url' => route('admin.shop-products.edit', $p),
                ];
            });

        $digital = DigitalProduct::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            })
            ->get()
            ->map(function (DigitalProduct $p) {
                return [
                    'id' => $p->id,
                    'type' => 'digital',
                    'name' => $p->title,
                    'description' => $p->short_description ?: '-',
                    'price' => (float) $p->price,
                    'currency' => (string) $p->currency,
                    'is_active' => (bool) $p->is_active,
                    'sort_order' => (int) $p->sort_order,
                    'edit_url' => route('admin.digital-products.edit', $p),
                ];
            });

        $rows = collect();

        if ($type === 'affiliate') {
            $rows = $affiliate;
        } elseif ($type === 'digital') {
            $rows = $digital;
        } else {
            $rows = $affiliate->concat($digital);
        }

        $rows = $rows
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'desc'],
            ])
            ->values();

        $products = $this->paginateCollection($rows, 20, $request);

        return view('admin.products.index', compact('products', 'type', 'search'));
    }

    public function toggleStatus(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:affiliate,digital'],
            'id' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['type'] === 'affiliate') {
            $product = ShopProduct::findOrFail($data['id']);
            $product->update(['is_active' => !$product->is_active]);
        } else {
            $product = DigitalProduct::findOrFail($data['id']);
            $product->update(['is_active' => !$product->is_active]);
        }

        return back()->with('success', 'Product status updated.');
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $slice = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
