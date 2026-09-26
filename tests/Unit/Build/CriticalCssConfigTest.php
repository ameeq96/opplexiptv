<?php

namespace Tests\Unit\Build;

use PHPUnit\Framework\TestCase;

class CriticalCssConfigTest extends TestCase
{
    public function test_production_build_purges_source_css_with_dynamic_class_safelists(): void
    {
        $config = file_get_contents(dirname(__DIR__, 3).'/vite.config.js');

        $this->assertStringContainsString("mode === 'production'", $config);
        $this->assertStringContainsString("purgeCriticalCss({", $config);
        $this->assertStringContainsString("./resources/**/*.{blade.php,js,php,ts,vue}", $config);
        $this->assertStringContainsString("!./resources/views/admin/**/*.blade.php", $config);
        $this->assertStringContainsString("source.endsWith('/resources/css/site-critical.css')", $config);
        $this->assertStringContainsString("'fa-facebook-f'", $config);
        $this->assertStringContainsString("'flaticon-customer-service'", $config);
        $this->assertStringContainsString("'lnr-arrow-right'", $config);
        $this->assertStringContainsString("'bi-router'", $config);
        $this->assertStringContainsString("./public/js/{nav-tool.js,owl.js,script.js}", $config);
    }

    public function test_footer_styles_are_not_part_of_the_critical_entry(): void
    {
        $criticalCss = file_get_contents(dirname(__DIR__, 3).'/resources/css/site-critical.css');

        $this->assertStringNotContainsString('footer.css', $criticalCss);
    }

    public function test_built_critical_css_stays_within_180_kilobyte_budget(): void
    {
        $projectRoot = dirname(__DIR__, 3);
        $manifest = json_decode(
            file_get_contents($projectRoot.'/public/build/manifest.json'),
            true,
            flags: JSON_THROW_ON_ERROR
        );
        $file = $manifest['resources/css/site-critical.css']['file'] ?? null;

        $this->assertIsString($file);
        $this->assertFileExists($projectRoot.'/public/build/'.$file);
        $this->assertLessThanOrEqual(
            180_000,
            filesize($projectRoot.'/public/build/'.$file),
            'Built critical CSS exceeds the 180 KB budget.'
        );
    }
}
