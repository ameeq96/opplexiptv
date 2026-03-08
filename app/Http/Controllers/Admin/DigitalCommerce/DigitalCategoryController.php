<?php

namespace App\Http\Controllers\Admin\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DigitalCommerce\StoreDigitalCategoryRequest;
use App\Http\Requests\Admin\DigitalCommerce\UpdateDigitalCategoryRequest;
use App\Models\Digital\DigitalCategory;
use Illuminate\Http\Request;

class DigitalCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $categories = DigitalCategory::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.digital-categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('admin.digital-categories.create', ['category' => new DigitalCategory()]);
    }

    public function store(StoreDigitalCategoryRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        DigitalCategory::create($data);

        return redirect()->route('admin.digital-categories.index')->with('success', 'Category created.');
    }

    public function edit(DigitalCategory $digital_category)
    {
        return view('admin.digital-categories.edit', ['category' => $digital_category]);
    }

    public function update(UpdateDigitalCategoryRequest $request, DigitalCategory $digital_category)
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $digital_category->update($data);

        return redirect()->route('admin.digital-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(DigitalCategory $digital_category)
    {
        $digital_category->delete();

        return redirect()->route('admin.digital-categories.index')->with('success', 'Category deleted.');
    }
}
