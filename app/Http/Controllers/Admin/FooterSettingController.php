<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use Illuminate\Http\Request;

class FooterSettingController extends Controller
{
    public function edit()
    {
        $setting = FooterSetting::query()->latest()->first();
        if (!$setting) {
            $setting = FooterSetting::create([
                'brand_text' => 'Opplex IPTV',
                'crypto_note' => 'We accept crypto payments via Cryptomus.',
                'phone' => '+1 (639) 390-3194',
                'email' => 'info@opplexiptv.com',
                'address' => 'Saskatoon SK, Canada',
                'rights_text' => 'All Rights Reserved.',
                'legal_note' => 'Use of crypto payments must comply with your local laws. See our Privacy Policy and Refund policies for details.',
            ]);
        }

        return view('admin.footer-settings.edit', [
            'setting' => $setting,
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'brand_text' => ['nullable', 'string', 'max:255'],
            'crypto_note' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'rights_text' => ['nullable', 'string', 'max:255'],
            'legal_note' => ['nullable', 'string', 'max:2000'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.brand_text"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.crypto_note"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.address"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.rights_text"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.legal_note"] = ['nullable', 'string', 'max:2000'];
        }

        $data = $request->validate($rules);

        $setting = FooterSetting::query()->latest()->first();
        if (!$setting) {
            $setting = FooterSetting::create($data);
        } else {
            $setting->update($data);
        }

        $this->syncTranslations($setting, $request);

        return redirect()->route('admin.footer-settings.edit')->with('success', 'Footer settings updated.');
    }

    private function syncTranslations(FooterSetting $setting, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $brand = trim((string) ($payload['brand_text'] ?? ''));
            $crypto = trim((string) ($payload['crypto_note'] ?? ''));
            $address = trim((string) ($payload['address'] ?? ''));
            $rights = trim((string) ($payload['rights_text'] ?? ''));
            $legal = trim((string) ($payload['legal_note'] ?? ''));

            if ($brand === '' && $crypto === '' && $address === '' && $rights === '' && $legal === '') {
                $setting->translations()->where('locale', $locale)->delete();
                continue;
            }

            $setting->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'brand_text' => $brand ?: null,
                    'crypto_note' => $crypto ?: null,
                    'address' => $address ?: null,
                    'rights_text' => $rights ?: null,
                    'legal_note' => $legal ?: null,
                ]
            );
        }
    }
}
