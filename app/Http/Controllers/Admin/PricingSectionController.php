<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingSection;
use Illuminate\Http\Request;

class PricingSectionController extends Controller
{
    public function edit()
    {
        $section = PricingSection::query()->latest()->first();
        if (!$section) {
            $section = PricingSection::create([
                'heading' => __('messages.pricing_heading'),
                'subheading' => __('messages.pricing_subheading'),
                'show_reseller_label' => __('messages.show_reseller_packages'),
                'credit_info' => '<span style="color:red;">1 '.__('messages.credit').'</span> = '.__('messages.1_month')
                    .' &nbsp;<i class="fa fa-plus"></i>&nbsp; '
                    .'<span style="color:red;">5 '.__('messages.credit').'</span> = '.__('messages.6_months')
                    .' &nbsp;<i class="fa fa-plus"></i>&nbsp; '
                    .'<span style="color:red;">10 '.__('messages.credit').'</span> = '.__('messages.12_months'),
            ]);
        }

        return view('admin.pricing-section.edit', [
            'section' => $section,
            'locales' => config('app.locales', [app()->getLocale()]),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'heading' => ['required', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'show_reseller_label' => ['nullable', 'string', 'max:255'],
            'credit_info' => ['nullable', 'string', 'max:5000'],
        ];

        foreach (config('app.locales', [app()->getLocale()]) as $locale) {
            $rules["translations.$locale.heading"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.subheading"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.show_reseller_label"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.credit_info"] = ['nullable', 'string', 'max:5000'];
        }

        $data = $request->validate($rules);

        $section = PricingSection::query()->latest()->first();
        if (!$section) {
            $section = PricingSection::create($data);
        } else {
            $section->update($data);
        }

        $this->syncTranslations($section, $request);

        return redirect()->route('admin.pricing-section.edit')->with('success', 'Pricing section updated.');
    }

    private function syncTranslations(PricingSection $section, Request $request): void
    {
        $locales = config('app.locales', [app()->getLocale()]);
        foreach ($locales as $locale) {
            $payload = $request->input("translations.$locale", []);
            $heading = trim((string) ($payload['heading'] ?? ''));
            $subheading = trim((string) ($payload['subheading'] ?? ''));
            $label = trim((string) ($payload['show_reseller_label'] ?? ''));
            $credit = trim((string) ($payload['credit_info'] ?? ''));

            if ($heading === '' && $subheading === '' && $label === '' && $credit === '') {
                $section->translations()->where('locale', $locale)->delete();
                continue;
            }

            $section->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'heading' => $heading ?: null,
                    'subheading' => $subheading ?: null,
                    'show_reseller_label' => $label ?: null,
                    'credit_info' => $credit ?: null,
                ]
            );
        }
    }
}
