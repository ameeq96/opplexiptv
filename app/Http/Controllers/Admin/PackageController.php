<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', '');
        $vendor = $request->query('vendor', '');
        $search = trim((string) $request->query('q', ''));

        $query = Package::query();

        if ($type !== '') {
            $query->where('type', $type);
        }
        if ($vendor !== '') {
            $query->where('vendor', $vendor);
        }
        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $packages = $query->orderBy('type')->orderBy('vendor')->orderBy('sort_order')->paginate(25)->withQueryString();

        return view('admin.packages.index', [
            'packages' => $packages,
            'type' => $type,
            'vendor' => $vendor,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('admin.packages.create', [
            'package' => new Package(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Package::create($data);

        return redirect()->route('admin.packages.index')->with('success', 'Package added.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.edit', [
            'package' => $package,
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $data = $this->validateData($request);
        $package->update($data);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:iptv,reseller'],
            'vendor' => ['required', 'in:opplex,starshare'],
            'title' => ['required', 'string', 'max:255'],
            'display_price' => ['nullable', 'string', 'max:255'],
            'price_amount' => ['nullable', 'numeric', 'min:0'],
            'duration_months' => ['nullable', 'integer', 'min:1'],
            'credits' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'string', 'max:255'],
            'icons' => ['nullable', 'string'],
            'features' => ['nullable', 'string'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'delay' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = (bool) ($data['active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['duration_months'] = $data['duration_months'] ? (int) $data['duration_months'] : null;
        $data['credits'] = $data['credits'] ? (int) $data['credits'] : null;

        $data['features'] = $this->splitLines($data['features'] ?? '');
        $data['icons'] = $this->splitLines($data['icons'] ?? '');

        return $data;
    }

    private function splitLines(string $value): ?array
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $parts = preg_split('/[\r\n,]+/', $value);
        $parts = array_values(array_filter(array_map('trim', $parts)));
        return $parts ?: null;
    }
}
