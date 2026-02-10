<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        $links = SocialLink::query()->orderBy('sort_order')->get();

        return view('admin.social-links.index', [
            'links' => $links,
        ]);
    }

    public function create()
    {
        return view('admin.social-links.create', [
            'link' => new SocialLink(),
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $link = SocialLink::create($data);
        $this->syncTranslations($link, $request);

        return redirect()->route('admin.social-links.index')->with('success', 'Social link added.');
    }

    public function edit(SocialLink $social_link)
    {
        return view('admin.social-links.edit', [
            'link' => $social_link,
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request, SocialLink $social_link)
    {
        $data = $this->validateData($request);
        $social_link->update($data);
        $this->syncTranslations($social_link, $request);

        return redirect()->route('admin.social-links.index')->with('success', 'Social link updated.');
    }

    public function destroy(SocialLink $social_link)
    {
        $social_link->delete();

        return redirect()->route('admin.social-links.index')->with('success', 'Social link deleted.');
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'platform' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:500'],
            'icon_class' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.platform"] = ['nullable', 'string', 'max:100'];
        }

        $data = $request->validate($rules);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function syncTranslations(SocialLink $link, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $platform = trim((string) ($payload['platform'] ?? ''));

            if ($platform === '') {
                $link->translations()->where('locale', $locale)->delete();
                continue;
            }

            $link->translations()->updateOrCreate(
                ['locale' => $locale],
                ['platform' => $platform]
            );
        }
    }
}
