<?php

namespace Tests\Feature;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class BlogPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Storage::fake('public');
    }

    public function test_blog_index_links_cacheable_styles_and_uses_native_navigation_without_legacy_assets(): void
    {
        $this->createPublishedBlog('blogs/missing-cover.jpg');

        $response = $this->get('/blogs');

        $response->assertOk();
        $html = $response->getContent();

        foreach ([
            'resources/css/site-critical.css',
            'resources/css/blogs.css',
        ] as $entry) {
            $this->assertStringContainsString('href="'.Vite::asset($entry).'"', $html);
        }
        foreach ([
            'blogs-index-critical-styles',
            'blogs-index-styles',
        ] as $styleId) {
            $this->assertStringNotContainsString('id="'.$styleId.'"', $html);
        }

        foreach ([
            'https://code.jquery.com/jquery-1.12.4.min.js',
            'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
            'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js',
            Vite::asset('resources/js/site.js'),
        ] as $legacyAsset) {
            $this->assertStringNotContainsString($legacyAsset, $html);
        }

        $this->assertSame(
            1,
            preg_match('/<script id="blogs-native-shell">(.*?)<\/script>/s', $html, $scriptMatches)
        );
        foreach ([
            'initBlogsScrollUi',
            'initBlogsMobileMenu',
            'initBlogsDropdowns',
            'window.requestAnimationFrame(update)',
        ] as $nativeMarker) {
            $this->assertStringContainsString($nativeMarker, $scriptMatches[1]);
        }
        foreach ([
            'offsetHeight',
            'offsetWidth',
            'clientWidth',
            'getBoundingClientRect',
            'jQuery',
            'mCustomScrollbar',
        ] as $forcedLayoutMarker) {
            $this->assertStringNotContainsString($forcedLayoutMarker, $scriptMatches[1]);
        }
    }

    public function test_blog_index_serves_a_responsive_featured_webp_when_gd_is_available(): void
    {
        $this->requireGdWebp();

        $sourcePath = 'blogs/featured-cover.jpg';
        Storage::disk('public')->put($sourcePath, $this->jpeg(714, 447));
        $this->createPublishedBlog($sourcePath);

        $response = $this->get('/blogs');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('blogs/variants/', $html);
        $this->assertStringContainsString('-480x300.webp 480w', $html);
        $this->assertStringContainsString('-672x420.webp 672w', $html);
        $this->assertStringContainsString(
            'sizes="(min-width: 1340px) 672px, (min-width: 768px) 51vw, calc(100vw - 30px)"',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<img[^>]+width="672"[^>]+height="420"[^>]+loading="lazy"[^>]+decoding="async"/s',
            $html
        );

        $variantFiles = Storage::disk('public')->files('blogs/variants');
        $this->assertCount(2, $variantFiles);

        foreach ([480 => 300, 672 => 420] as $width => $height) {
            $variantPath = collect($variantFiles)
                ->first(fn (string $path): bool => str_ends_with($path, "-{$width}x{$height}.webp"));

            $this->assertIsString($variantPath);
            $image = imagecreatefromstring(Storage::disk('public')->get($variantPath));
            $this->assertNotFalse($image);
            $this->assertSame($width, imagesx($image));
            $this->assertSame($height, imagesy($image));
            imagedestroy($image);
        }
    }

    public function test_blog_show_keeps_its_existing_assets_and_original_cover(): void
    {
        $sourcePath = 'blogs/show-cover.jpg';
        Storage::disk('public')->put($sourcePath, 'original cover bytes');
        $blog = $this->createPublishedBlog($sourcePath);

        $response = $this->get('/blogs/'.$blog->translation('en')->slug);

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString(
            'href="'.Vite::asset('resources/css/site-critical.css').'"',
            $html
        );
        $this->assertStringContainsString(
            'href="'.Vite::asset('resources/css/blogs.css').'"',
            $html
        );
        $this->assertStringContainsString('https://code.jquery.com/jquery-1.12.4.min.js', $html);
        $this->assertStringContainsString(Vite::asset('resources/js/site.js'), $html);
        $this->assertStringContainsString($sourcePath, $html);
        $this->assertStringNotContainsString('blogs/variants/', $html);
        $this->assertStringNotContainsString('id="blogs-native-shell"', $html);
    }

    public function test_blog_index_falls_back_to_the_original_cover_when_the_source_is_unavailable(): void
    {
        $sourcePath = 'blogs/missing-cover.jpg';
        $this->createPublishedBlog($sourcePath);

        $response = $this->get('/blogs');

        $response->assertOk();
        $response->assertSee($sourcePath, false);
        $this->assertStringNotContainsString('blogs/variants/', $response->getContent());
        $this->assertSame([], Storage::disk('public')->files('blogs/variants'));
    }

    private function createPublishedBlog(string $coverImage): Blog
    {
        $blog = Blog::query()->create([
            'cover_image' => $coverImage,
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'reading_time' => 4,
            'is_featured' => true,
        ]);

        $blog->translations()->create([
            'locale' => 'en',
            'title' => 'Building a reliable IPTV playlist',
            'slug' => 'building-a-reliable-iptv-playlist',
            'excerpt' => 'A stable source makes streaming smoother.',
            'content' => '<p>Test article content.</p>',
        ]);

        return $blog->load('translations');
    }

    private function requireGdWebp(): void
    {
        foreach ([
            'imagecreatetruecolor',
            'imagejpeg',
            'imagecreatefromstring',
            'imagewebp',
        ] as $function) {
            if (! function_exists($function)) {
                $this->markTestSkipped('GD with JPEG and WebP support is required for this conversion test.');
            }
        }
    }

    private function jpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y += 8) {
            $colour = imagecolorallocate(
                $image,
                ($y * 3) % 255,
                ($y * 5) % 255,
                ($y * 7) % 255
            );
            imagefilledrectangle($image, 0, $y, $width, min($height - 1, $y + 7), $colour);
        }

        ob_start();
        imagejpeg($image, null, 90);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        return is_string($jpeg) ? $jpeg : '';
    }
}
