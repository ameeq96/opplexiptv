<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $items = MenuItem::with('children')->whereNull('parent_id')->orderBy('sort_order')->orderByDesc('id')->get();

        return view('admin.menu-items.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('admin.menu-items.create', [
            'item' => new MenuItem(),
            'parents' => MenuItem::whereNull('parent_id')->orderBy('sort_order')->get(),
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $item = MenuItem::create($data);
        $this->syncTranslations($item, $request);

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu item added.');
    }

    public function edit(MenuItem $menu_item)
    {
        return view('admin.menu-items.edit', [
            'item' => $menu_item,
            'parents' => MenuItem::whereNull('parent_id')->where('id', '!=', $menu_item->id)->orderBy('sort_order')->get(),
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request, MenuItem $menu_item)
    {
        $data = $this->validateData($request, $menu_item->id);
        $menu_item->update($data);
        $this->syncTranslations($menu_item, $request);

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menu_item)
    {
        $menu_item->delete();

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu item deleted.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'open_new_tab' => ['nullable', 'boolean'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.label"] = ['nullable', 'string', 'max:120'];
        }

        $data = $request->validate($rules);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['open_new_tab'] = (bool) ($data['open_new_tab'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['parent_id'] = $data['parent_id'] ?: null;

        return $data;
    }

    private function syncTranslations(MenuItem $item, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $label = trim((string) ($payload['label'] ?? ''));

            if ($label === '') {
                $item->translations()->where('locale', $locale)->delete();
                continue;
            }

            $item->translations()->updateOrCreate(
                ['locale' => $locale],
                ['label' => $label]
            );
        }
    }
}
