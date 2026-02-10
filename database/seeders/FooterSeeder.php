<?php

namespace Database\Seeders;

use App\Models\FooterSetting;
use App\Models\FooterLink;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    public function run(): void
    {
        FooterSetting::updateOrCreate(
            ['brand_text' => 'Opplex IPTV'],
            [
                'brand_text' => 'Opplex IPTV',
                'crypto_note' => 'We accept crypto payments via Cryptomus.',
                'phone' => '+1 (639) 390-3194',
                'email' => 'info@opplexiptv.com',
                'address' => 'Saskatoon SK, Canada',
                'rights_text' => 'All Rights Reserved.',
                'legal_note' => 'Use of crypto payments must comply with your local laws. See our Privacy Policy and Refund policies for details.',
            ]
        );

        $links = [
            ['group' => 'explore', 'label' => 'Home', 'url' => '/'],
            ['group' => 'explore', 'label' => 'Pricing', 'url' => '/pricing'],
            ['group' => 'explore', 'label' => 'Packages', 'url' => '/packages'],
            ['group' => 'explore', 'label' => 'Reseller Panel', 'url' => '/reseller-panel'],
            ['group' => 'explore', 'label' => 'Movies', 'url' => '/movies'],
            ['group' => 'explore', 'label' => 'IPTV Apps', 'url' => '/iptv-applications'],
            ['group' => 'explore', 'label' => 'Shop', 'url' => '/shop'],

            ['group' => 'company', 'label' => 'About Us', 'url' => '/about'],
            ['group' => 'company', 'label' => 'Contact Us', 'url' => '/contact'],
            ['group' => 'company', 'label' => 'FAQ', 'url' => '/faqs'],

            ['group' => 'legal', 'label' => 'Terms of Service', 'url' => '/terms-of-service'],
            ['group' => 'legal', 'label' => 'Privacy Policy', 'url' => '/privacy-policy'],
            ['group' => 'legal', 'label' => 'Refund & Cancellation', 'url' => '/refund-policy'],

            ['group' => 'deeplink', 'label' => 'Activate', 'url' => '/activate'],
            ['group' => 'deeplink', 'label' => 'Configure', 'url' => '/configure'],
            ['group' => 'deeplink', 'label' => 'Checkout', 'url' => '/checkout'],
            ['group' => 'deeplink', 'label' => 'Thank You', 'url' => '/thank-you'],
        ];

        foreach ($links as $i => $l) {
            FooterLink::updateOrCreate(
                ['group' => $l['group'], 'label' => $l['label']],
                $l + ['sort_order' => $i, 'is_active' => true]
            );
        }

        $socials = [
            ['platform' => 'Facebook', 'url' => 'https://www.facebook.com/profile.php?id=61565476366548', 'icon_class' => 'fa fa-facebook-f'],
            ['platform' => 'LinkedIn', 'url' => 'https://www.linkedin.com/company/digitalize-store/', 'icon_class' => 'fa fa-linkedin'],
            ['platform' => 'Instagram', 'url' => 'https://www.instagram.com/oplextv/', 'icon_class' => 'fa fa-instagram'],
        ];

        foreach ($socials as $i => $s) {
            SocialLink::updateOrCreate(
                ['platform' => $s['platform']],
                $s + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
