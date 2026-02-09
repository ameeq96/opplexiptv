<?php

namespace Database\Seeders;

use App\Models\PricingSection;
use Illuminate\Database\Seeder;

class PricingSectionSeeder extends Seeder
{
    public function run(): void
    {
        PricingSection::updateOrCreate(
            ['heading' => __('messages.pricing_heading')],
            [
                'heading' => __('messages.pricing_heading'),
                'subheading' => __('messages.pricing_subheading'),
                'show_reseller_label' => __('messages.show_reseller_packages'),
                'credit_info' => '<span style="color:red;">1 '.__('messages.credit').'</span> = '.__('messages.1_month')
                    .' &nbsp;<i class="fa fa-plus"></i>&nbsp; '
                    .'<span style="color:red;">5 '.__('messages.credit').'</span> = '.__('messages.6_months')
                    .' &nbsp;<i class="fa fa-plus"></i>&nbsp; '
                    .'<span style="color:red;">10 '.__('messages.credit').'</span> = '.__('messages.12_months'),
            ]
        );
    }
}
