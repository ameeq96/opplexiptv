<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use Illuminate\Http\Request;

class FooterLinkController extends Controller
{
    public function index()
    {
        $links = FooterLink::query()->orderBy('group')->orderBy('sort_order')->get();

        return view('admin.footer-links.index', [
            'links' => $links,
        ]);
    }

    public function create()
    {
        return view('admin.footer-links.create', [
            'link' => new FooterLink(),
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $link = FooterLink::create($data);
        $this->syncTranslations($link, $request);

        return redirect()->route('admin.footer-links.index')->with('success', 'Footer link added.');
    }

    public function edit(FooterLink $footer_link)
    {
        return view('admin.footer-links.edit', [
            'link' => $footer_link,
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request, FooterLink $footer_link)
    {
        $data = $this->validateData($request);
        $footer_link->update($data);
        $this->syncTranslations($footer_link, $request);

        return redirect()->route('admin.footer-links.index')->with('success', 'Footer link updated.');
    }

    public function destroy(FooterLink $footer_link)
    {
        $footer_link->delete();

        return redirect()->route('admin.footer-links.index')->with('success', 'Footer link deleted.');
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'group' => ['required', 'in:explore,company,legal,deeplink'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.label"] = ['nullable', 'string', 'max:255'];
        }

        $data = $request->validate($rules);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function syncTranslations(FooterLink $link, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $label = trim((string) ($payload['label'] ?? ''));

            if ($label === '') {
                $link->translations()->where('locale', $locale)->delete();
                continue;
            }

            $link->translations()->updateOrCreate(
                ['locale' => $locale],
                ['label' => $label]
            );
        }
    }
}
