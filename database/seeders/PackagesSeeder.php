<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackagesSeeder extends Seeder
{
    public function run(): void
    {
        // Clean duplicates only if needed
        Package::whereIn('type', ['iptv','reseller','opplex','starshare','opplex_reseller','starshare_reseller'])->delete();

        // Common features
        $iptvFeatures = [
            __('messages.no_buffer'),
            __('messages.support_24_7'),
            __('messages.regular_updates'),
            __('messages.quality_content'),
        ];
        $resellerFeatures = [
            __('messages.uptime'),
            __('messages.no_credit_expiry'),
            __('messages.unlimited_trials'),
            __('messages.no_subreseller'),
        ];

        // -------- IPTV (type=iptv, vendor=opplex|starshare) --------
        $iptv = [
            // Opplex
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Monthly',     'display_price'=>'$2.99 / 1 month',   'price_amount'=>2.99,  'duration_months'=>1,  'sort_order'=>1, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'3 Months',    'display_price'=>'$7.99 / 3 months',  'price_amount'=>7.99,  'duration_months'=>3,  'sort_order'=>2, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Half Yearly', 'display_price'=>'$14.99 / 6 months', 'price_amount'=>14.99, 'duration_months'=>6,  'sort_order'=>3, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Yearly',      'display_price'=>'$23.99 / 12 months','price_amount'=>23.99, 'duration_months'=>12, 'sort_order'=>4, 'icon'=>'bi-router', 'features'=>$iptvFeatures],

            // Starshare
            ['type'=>'iptv','vendor'=>'starshare','title'=>'Monthly',     'display_price'=>'$4.50 / 1 month',   'price_amount'=>4.50,  'duration_months'=>1,  'sort_order'=>1, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'starshare','title'=>'3 Months',    'display_price'=>'$11.99 / 3 months', 'price_amount'=>11.99, 'duration_months'=>3,  'sort_order'=>2, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'starshare','title'=>'Half Yearly', 'display_price'=>'$21.99 / 6 months', 'price_amount'=>21.99, 'duration_months'=>6,  'sort_order'=>3, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'starshare','title'=>'Yearly',      'display_price'=>'$39.99 / 12 months','price_amount'=>39.99, 'duration_months'=>12, 'sort_order'=>4, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
        ];

        foreach ($iptv as $p) {
            Package::updateOrCreate(
                ['type'=>$p['type'],'vendor'=>$p['vendor'],'title'=>$p['title']],
                $p + ['active'=>true]
            );
        }

        // -------- Reseller (type=reseller, vendor=opplex|starshare) --------
        $resellers = [
            // Opplex reseller (aapke purane numbers)
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Starter Reseller Package',  'credits'=>20,  'display_price'=>'$16.99 / 20 Credits',   'price_amount'=>16.99,  'sort_order'=>1, 'icons'=>['images/icons/service-1.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'0ms'],
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Essential Reseller Bundle', 'credits'=>50,  'display_price'=>'$40.99 / 50 Credits',   'price_amount'=>40.99,  'sort_order'=>2, 'icons'=>['images/icons/service-2.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'150ms'],
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Pro Reseller Suite',        'credits'=>100, 'display_price'=>'$77.99 / 100 Credits',  'price_amount'=>77.99,  'sort_order'=>3, 'icons'=>['images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'300ms'],
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Advanced Reseller Toolkit', 'credits'=>200, 'display_price'=>'$149.99 / 200 Credits', 'price_amount'=>149.99, 'sort_order'=>4, 'icons'=>['images/icons/service-1.svg','images/icons/service-2.svg','images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'450ms'],

            // Starshare reseller (aapke diye gaye rates)
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Starter Reseller Package',  'credits'=>50,  'display_price'=>'$134.99 / 50 Credits',  'price_amount'=>134.99, 'sort_order'=>1, 'icons'=>['images/icons/service-1.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'0ms'],
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Essential Reseller Bundle', 'credits'=>100, 'display_price'=>'$249.99 / 100 Credits', 'price_amount'=>249.99, 'sort_order'=>2, 'icons'=>['images/icons/service-2.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'150ms'],
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Pro Reseller Suite',        'credits'=>200, 'display_price'=>'$480.00 / 200 Credits', 'price_amount'=>480.00, 'sort_order'=>3, 'icons'=>['images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'300ms'],
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Advanced Reseller Toolkit', 'credits'=>300, 'display_price'=>'$659.99 / 300 Credits', 'price_amount'=>659.99, 'sort_order'=>4, 'icons'=>['images/icons/service-1.svg','images/icons/service-2.svg','images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'450ms'],
        ];

        foreach ($resellers as $r) {
            Package::updateOrCreate(
                ['type'=>$r['type'],'vendor'=>$r['vendor'],'title'=>$r['title']],
                $r + ['active'=>true]
            );
        }
    }
}
