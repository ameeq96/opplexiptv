<?php

namespace Tests\Feature\StructuredData;

use App\Models\Digital\DigitalCategory;
use App\Models\Digital\DigitalProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_does_not_emit_product_structured_data(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('"@type": "OnlineStore"', false);
        $response->assertDontSee('"@type": "Product"', false);
    }

    public function test_digital_product_page_emits_valid_product_structured_data(): void
    {
        $category = DigitalCategory::create([
            'name' => 'Streaming',
            'slug' => 'streaming',
        ]);

        $product = DigitalProduct::create([
            'digital_category_id' => $category->id,
            'title' => 'Netflix 1 Month',
            'slug' => 'netflix-1-month',
            'short_description' => 'Premium Netflix access with digital delivery.',
            'price' => 12.99,
            'currency' => 'USD',
            'delivery_type' => 'credential',
            'is_active' => true,
        ]);

        $response = $this->get(route('digital.product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('"@type":"Product"', false);
        $response->assertSee('"price":"12.99"', false);
        $response->assertSee('"image":["', false);
        $response->assertSee('"description":"Premium Netflix access with digital delivery."', false);
        $response->assertSee('"doesNotShip":true', false);
    }
}
