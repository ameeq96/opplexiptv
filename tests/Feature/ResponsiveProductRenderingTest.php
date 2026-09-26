<?php

namespace Tests\Feature;

use App\Models\Digital\DigitalProduct;
use App\Models\ShopProduct;
use App\Services\UnifiedProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResponsiveProductRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unified_product_data_preserves_image_and_adds_responsive_sources(): void
    {
        Cache::flush();
        $product = $this->digitalProduct();

        $item = app(UnifiedProductService::class)
            ->frontendProducts()
            ->firstWhere('id', $product->id);

        $this->assertIsArray($item);
        $this->assertSame(asset('images/digital-products/netflix.webp'), $item['image']);
        $this->assertSame($item['image'], $item['image_sources']['src']);
        $this->assertStringContainsString('netflix-webp-320.avif 320w', $item['image_sources']['avif_srcset']);
        $this->assertStringContainsString('netflix-webp-960.webp 960w', $item['image_sources']['webp_srcset']);
    }

    public function test_active_product_share_uses_modern_sources_and_original_fallback(): void
    {
        Cache::flush();
        $product = $this->digitalProduct();

        $response = $this->get(route('products.share', ['type' => 'digital', 'id' => $product->id]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('<picture class="product-picture">', $html);
        $this->assertTrue(strpos($html, 'type="image/avif"') < strpos($html, 'type="image/webp"'));
        $this->assertStringContainsString('netflix-webp-320.avif 320w', $html);
        $this->assertStringContainsString('src="' . asset('images/digital-products/netflix.webp') . '"', $html);
    }

    public function test_active_home_and_shop_cards_render_through_the_picture_component(): void
    {
        Cache::flush();
        Cache::put('ui:tmdb:v2:trending:all:day:en:p1', [], now()->addMinutes(10));
        $this->digitalProduct();
        ShopProduct::query()->create([
            'name' => 'Android TV Box',
            'asin' => 'B08CRV62C4',
            'link' => 'https://example.com/product',
            'image' => 'B08CRV62C4.webp',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $home = $this->get('/');
        $home->assertOk();
        $this->assertMatchesRegularExpression(
            '/<picture class="product-picture">.*?src="[^"]*\/images\/shop\/B08CRV62C4\.webp"/s',
            $home->getContent()
        );

        Cache::flush();
        $shop = $this->get('/shop');
        $shop->assertOk();
        $this->assertMatchesRegularExpression(
            '/<picture class="product-picture">.*?netflix-webp-320\.avif 320w.*?src="[^"]*\/images\/digital-products\/netflix\.webp"/s',
            $shop->getContent()
        );
    }

    private function digitalProduct(): DigitalProduct
    {
        return DigitalProduct::query()->create([
            'title' => 'Netflix',
            'slug' => 'netflix',
            'price' => 3,
            'currency' => 'USD',
            'image' => 'netflix.webp',
            'is_active' => true,
            'sort_order' => 1,
            'delivery_type' => 'manual',
        ]);
    }
}
