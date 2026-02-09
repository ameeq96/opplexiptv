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
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'heading' => ['required', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'show_reseller_label' => ['nullable', 'string', 'max:255'],
            'credit_info' => ['nullable', 'string', 'max:5000'],
        ]);

        $section = PricingSection::query()->latest()->first();
        if (!$section) {
            PricingSection::create($data);
        } else {
            $section->update($data);
        }

        return redirect()->route('admin.pricing-section.edit')->with('success', 'Pricing section updated.');
    }
}
