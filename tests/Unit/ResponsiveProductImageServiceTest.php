<?php

namespace Tests\Unit;

use App\Services\ResponsiveProductImageService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResponsiveProductImageServiceTest extends TestCase
{
    private string $relativePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requireResponsiveGd();
        $this->relativePath = 'images/digital-products/responsive-test-' . Str::lower(Str::random(10)) . '.png';

        $image = imagecreatetruecolor(700, 350);
        $background = imagecolorallocate($image, 12, 34, 78);
        imagefill($image, 0, 0, $background);
        imagepng($image, public_path($this->relativePath), 6);
        imagedestroy($image);
    }

    protected function tearDown(): void
    {
        if (isset($this->relativePath)) {
            app(ResponsiveProductImageService::class)->deleteVariants($this->relativePath);
            @unlink(public_path($this->relativePath));
        }

        parent::tearDown();
    }

    public function test_it_generates_responsive_formats_without_upscaling_and_keeps_original_fallback(): void
    {
        $service = app(ResponsiveProductImageService::class);
        $metadata = $service->generateVariants($this->relativePath);

        $this->assertIsArray($metadata);
        $this->assertSame(asset($this->relativePath), $metadata['src']);
        $this->assertSame(700, $metadata['width']);
        $this->assertSame(350, $metadata['height']);
        $this->assertStringContainsString('-320.avif 320w', $metadata['avif_srcset']);
        $this->assertStringContainsString('-640.avif 640w', $metadata['avif_srcset']);
        $this->assertStringContainsString('-320.webp 320w', $metadata['webp_srcset']);
        $this->assertStringContainsString('-640.webp 640w', $metadata['webp_srcset']);
        $this->assertStringNotContainsString('-960.', $metadata['avif_srcset']);
        $this->assertStringNotContainsString('-960.', $metadata['webp_srcset']);

        foreach (['avif', 'webp'] as $format) {
            foreach ([320 => 160, 640 => 320] as $width => $height) {
                $path = public_path(sprintf(
                    'images/digital-products/responsive/%s-png-%d.%s',
                    pathinfo($this->relativePath, PATHINFO_FILENAME),
                    $width,
                    $format
                ));

                $this->assertFileExists($path);
                $info = getimagesize($path);
                $this->assertSame($width, $info[0]);
                $this->assertSame($height, $info[1]);
            }
        }

        $html = Blade::render(
            '<x-product-picture :image="$image" alt="Responsive product" sizes="50vw" />',
            ['image' => $metadata]
        );

        $this->assertStringContainsString('<picture class="product-picture">', $html);
        $this->assertTrue(
            strpos($html, 'type="image/avif"') < strpos($html, 'type="image/webp"')
        );
        $this->assertStringContainsString('src="' . asset($this->relativePath) . '"', $html);
        $this->assertStringContainsString('width="700"', $html);
        $this->assertStringContainsString('height="350"', $html);
        $this->assertStringContainsString('sizes="50vw"', $html);
    }

    public function test_missing_image_metadata_fails_open_to_the_original_url(): void
    {
        $missing = 'images/digital-products/missing-product-image.png';
        $metadata = app(ResponsiveProductImageService::class)->metadata($missing);

        $this->assertSame(asset($missing), $metadata['src']);
        $this->assertNull($metadata['width']);
        $this->assertNull($metadata['height']);
        $this->assertNull($metadata['avif_srcset']);
        $this->assertNull($metadata['webp_srcset']);
    }

    private function requireResponsiveGd(): void
    {
        foreach ([
            'imagecreatetruecolor',
            'imagecreatefromstring',
            'imagepng',
            'imageavif',
            'imagewebp',
        ] as $function) {
            if (! function_exists($function)) {
                $this->markTestSkipped('GD with PNG, AVIF and WebP support is required.');
            }
        }
    }
}
