<?php

namespace Tests\Feature\Accessibility;

use Tests\TestCase;

class AriaRolesTest extends TestCase
{
    public function test_vendor_toggles_use_button_group_roles_instead_of_tablists(): void
    {
        $contents = file_get_contents(resource_path('views/includes/_best-packages.blade.php'));

        $this->assertStringContainsString('id="vendorToggle" class="vendor-toggle" role="group"', $contents);
        $this->assertStringContainsString('id="vendorToggleReseller" class="vendor-toggle-reseller" role="group"', $contents);
        $this->assertStringNotContainsString('id="vendorToggle" class="vendor-toggle" role="tablist"', $contents);
        $this->assertStringNotContainsString('id="vendorToggleReseller" class="vendor-toggle-reseller" role="tablist"', $contents);
    }

    public function test_public_filter_controls_do_not_use_tablist_without_tabs(): void
    {
        $contents = file_get_contents(resource_path('views/pages/movies.blade.php'));

        $this->assertStringContainsString('role="group" aria-label="Content filters"', $contents);
        $this->assertStringContainsString('role="button" tabindex="0" data-filter="all"', $contents);
        $this->assertStringNotContainsString('role="tablist" aria-label="Content filters"', $contents);
    }

    public function test_icon_only_social_links_have_accessible_names(): void
    {
        foreach (['includes/header.blade.php', 'pages/contact.blade.php'] as $view) {
            $contents = file_get_contents(resource_path('views/'.$view));

            $this->assertMatchesRegularExpression('/class="fa fa-facebook-f"[^>]*aria-label="Facebook"/', $contents, $view);
            $this->assertMatchesRegularExpression('/class="fa fa-linkedin"[^>]*aria-label="LinkedIn"/', $contents, $view);
            $this->assertMatchesRegularExpression('/class="fa fa-instagram"[^>]*aria-label="Instagram"/', $contents, $view);
        }
    }

    public function test_home_headings_do_not_skip_levels_in_pricing_sections(): void
    {
        $pricing = file_get_contents(resource_path('views/includes/_best-packages.blade.php'));
        $slider = file_get_contents(resource_path('views/includes/_slider.blade.php'));

        $this->assertStringContainsString('<h1 class="heading">', $slider);
        $this->assertStringContainsString('<h2 class="h3"><b>', $pricing);
        $this->assertStringContainsString('<p class="h4">', $pricing);
        $this->assertStringContainsString('<h3 class="package-plan-title">', $pricing);
        $this->assertStringNotContainsString('<h3><b>', $pricing);
        $this->assertStringNotContainsString('<h4>{{ $displayTitle }}', $pricing);
    }

    public function test_accessibility_fix_styles_are_loaded_and_cover_audit_targets(): void
    {
        $head = file_get_contents(resource_path('views/includes/head.blade.php'));
        $css = file_get_contents(public_path('css/accessibility-fixes.css'));

        $this->assertStringContainsString("v('css/accessibility-fixes.css')", $head);
        $this->assertStringContainsString('.testimonial-card__author-role', $css);
        $this->assertStringContainsString('color: #475569 !important', $css);
        $this->assertStringContainsString('.home-product-action', $css);
        $this->assertStringContainsString('min-height: 48px', $css);
        $this->assertStringContainsString('.fx-link', $css);
        $this->assertStringContainsString('min-height: 44px', $css);
    }
}
