<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        $packages = $query->orderBy('type')->orderBy('vendor')->orderBy('sort_order')->paginate(24)->withQueryString();
        $packageGroups = $packages->getCollection()
            ->groupBy(function (Package $package): string {
                if ($package->type !== 'iptv') {
                    return "package:{$package->id}";
                }

                return 'provider:' . strtolower($package->vendor . ':' . $this->providerServiceName($package->title));
            })
            ->map(function ($group): array {
                /** @var Package $first */
                $first = $group->first();
                $isProvider = $first->type === 'iptv';

                return [
                    'is_provider' => $isProvider,
                    'name' => $isProvider ? $this->providerServiceName($first->title) : null,
                    'representative' => $first,
                    'packages' => $group,
                    'is_featured' => $isProvider && $group->every(fn (Package $package) => $package->is_featured),
                    'is_available' => ! $isProvider || $group->every(fn (Package $package) => $package->is_available),
                    'provider_order' => $isProvider
                        ? max(0, intdiv((int) $group->min('sort_order'), 10))
                        : null,
                ];
            })
            ->values();

        return view('admin.packages.index', [
            'packages' => $packages,
            'packageGroups' => $packageGroups,
            'type' => $type,
            'vendor' => $vendor,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('admin.packages.create', [
            'package' => new Package(),
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $package = Package::create($data);
        $this->syncTranslations($package, $request);
        $this->forgetPackageCache();

        return redirect()->route('admin.packages.index')->with('success', 'Package added.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.edit', [
            'package' => $package,
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request, Package $package)
    {
        if ($request->boolean('provider_merchandising')) {
            return $this->updateProviderMerchandising($request, $package);
        }

        $data = $this->validateData($request);
        $package->update($data);
        $this->syncTranslations($package, $request);
        $this->forgetPackageCache();

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        $this->forgetPackageCache();
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }

    private function validateData(Request $request): array
    {
        $rules = [
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
            'badge_key' => ['nullable', 'in:most_popular,best_value'],
            'is_featured' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
            'free_trial_hours' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'instant_activation' => ['nullable', 'boolean'],
            'connection_prices' => ['nullable', 'array'],
            'connection_prices.2' => ['nullable', 'numeric', 'min:0'],
            'connection_prices.4' => ['nullable', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.features"] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        $data['active'] = (bool) ($data['active'] ?? false);
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['is_available'] = (bool) ($data['is_available'] ?? false);
        $data['instant_activation'] = (bool) ($data['instant_activation'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['duration_months'] = $data['duration_months'] ? (int) $data['duration_months'] : null;
        $data['credits'] = $data['credits'] ? (int) $data['credits'] : null;
        $data['free_trial_hours'] = isset($data['free_trial_hours']) ? (int) $data['free_trial_hours'] : null;

        $connectionPrices = array_filter(
            $data['connection_prices'] ?? [],
            static fn ($price) => $price !== null && $price !== ''
        );
        $data['connection_prices'] = $connectionPrices
            ? array_map(static fn ($price) => (float) $price, $connectionPrices)
            : null;

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

    private function syncTranslations(Package $package, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $title = trim((string) ($payload['title'] ?? ''));
            $featuresRaw = (string) ($payload['features'] ?? '');
            $features = $this->splitLines($featuresRaw);

            if ($title === '' && empty($features)) {
                $package->translations()->where('locale', $locale)->delete();
                continue;
            }

            $package->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $title ?: null,
                    'features' => $features,
                ]
            );
        }
    }

    private function updateProviderMerchandising(Request $request, Package $package)
    {
        abort_unless($package->type === 'iptv', 404);

        $data = $request->validate([
            'provider_merchandising' => ['required', 'accepted'],
            'is_featured' => ['required', 'boolean'],
            'is_available' => ['required', 'boolean'],
            'provider_order' => ['required', 'integer', 'min:0'],
        ]);

        $serviceName = $this->providerServiceName($package->title);
        $titles = [
            $serviceName,
            $serviceName . ' - 3 Months',
            $serviceName . ' - Half Yearly',
            $serviceName . ' - Yearly',
        ];
        $durationOffsets = [1 => 0, 3 => 1, 6 => 2, 12 => 3];

        DB::transaction(function () use ($package, $titles, $durationOffsets, $data): void {
            Package::query()
                ->where('type', 'iptv')
                ->where('vendor', $package->vendor)
                ->whereIn('title', $titles)
                ->get()
                ->each(function (Package $providerPackage) use ($durationOffsets, $data): void {
                    $offset = $durationOffsets[(int) $providerPackage->duration_months] ?? 0;
                    $providerPackage->update([
                        'is_featured' => (bool) $data['is_featured'],
                        'is_available' => (bool) $data['is_available'],
                        'sort_order' => ((int) $data['provider_order'] * 10) + $offset,
                    ]);
                });
        });

        $this->forgetPackageCache();

        return redirect()->back()->with('success', "{$serviceName} provider settings updated.");
    }

    private function providerServiceName(string $title): string
    {
        return preg_replace('/\s+-\s+(?:3 Months|Half Yearly|Yearly)$/i', '', trim($title)) ?: trim($title);
    }

    private function forgetPackageCache(): void
    {
        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            Cache::forget("ui:{$locale}:packages:v3");
        }
    }
}
