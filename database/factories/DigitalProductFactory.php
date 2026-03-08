<?php

namespace Database\Factories;

use App\Models\Digital\DigitalProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DigitalProductFactory extends Factory
{
    protected $model = DigitalProduct::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'title' => ucfirst($title),
            'slug' => Str::slug($title . '-' . $this->faker->unique()->numberBetween(1000, 9999)),
            'short_description' => $this->faker->sentence(),
            'full_description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 3, 50),
            'compare_price' => null,
            'currency' => 'USD',
            'is_active' => true,
            'sort_order' => 0,
            'product_type' => 'digital',
            'delivery_type' => 'manual',
            'metadata' => null,
            'min_qty' => 1,
            'max_qty' => 5,
        ];
    }
}
