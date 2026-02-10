<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        $query = HomeService::query();

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $services = $query->orderBy('sort_order')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.home-services.index', [
            'services' => $services,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('admin.home-services.create', [
            'service' => new HomeService(),
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['icon'] = $this->storeIcon($request);

        $service = HomeService::create($data);
        $this->syncTranslations($service, $request);

        return redirect()->route('admin.home-services.index')->with('success', 'Service added.');
    }

    public function edit(HomeService $home_service)
    {
        return view('admin.home-services.edit', [
            'service' => $home_service,
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request, HomeService $home_service)
    {
        $data = $this->validateData($request, false);

        if ($request->hasFile('icon')) {
            $this->deleteIcon($home_service->icon);
            $data['icon'] = $this->storeIcon($request);
        }

        $home_service->update($data);
        $this->syncTranslations($home_service, $request);

        return redirect()->route('admin.home-services.index')->with('success', 'Service updated.');
    }

    public function destroy(HomeService $home_service)
    {
        $this->deleteIcon($home_service->icon);
        $home_service->delete();

        return redirect()->route('admin.home-services.index')->with('success', 'Service deleted.');
    }

    private function validateData(Request $request, bool $isCreate): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'link' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.description"] = ['nullable', 'string', 'max:1000'];
        }

        $rules['icon'] = $isCreate
            ? ['required', 'image', 'mimes:webp,jpg,jpeg,png,svg', 'max:2048']
            : ['nullable', 'image', 'mimes:webp,jpg,jpeg,png,svg', 'max:2048'];

        $data = $request->validate($rules);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function syncTranslations(HomeService $service, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $title = trim((string) ($payload['title'] ?? ''));
            $description = trim((string) ($payload['description'] ?? ''));

            if ($title === '' && $description === '') {
                $service->translations()->where('locale', $locale)->delete();
                continue;
            }

            $service->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $title ?: null,
                    'description' => $description ?: null,
                ]
            );
        }
    }

    private function storeIcon(Request $request): string
    {
        $file = $request->file('icon');
        $ext = $file->getClientOriginalExtension();
        $filename = 'service-' . Str::random(8) . '-' . time() . '.' . $ext;

        $dest = public_path('images/icons');
        if (!is_dir($dest)) {
            mkdir($dest, 0775, true);
        }

        $file->move($dest, $filename);

        return $filename;
    }

    private function deleteIcon(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = public_path('images/icons/' . $filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
