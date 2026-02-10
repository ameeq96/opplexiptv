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
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'brand_text' => ['nullable', 'string', 'max:255'],
            'crypto_note' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'rights_text' => ['nullable', 'string', 'max:255'],
            'legal_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $setting = FooterSetting::query()->latest()->first();
        if (!$setting) {
            FooterSetting::create($data);
        } else {
            $setting->update($data);
        }

        return redirect()->route('admin.footer-settings.edit')->with('success', 'Footer settings updated.');
    }
}
