<?php

namespace App\Services;

use App\Models\ShopProduct;
use App\Models\ChannelLogo;
use Illuminate\Support\Facades\Schema;

class ProductCatalogService
{
    public function getAll(): array
    {
        if (Schema::hasTable('shop_products')) {
            return ShopProduct::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(['name', 'asin', 'link', 'image'])
                ->toArray();
        }

        return (array) config('shop.products', []);
    }

    public function getLogos(): array
    {
        if (Schema::hasTable('channel_logos')) {
            return ChannelLogo::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(['image'])
                ->pluck('image')
                ->toArray();
        }

        return (array) config('shop.logos', []);
    }
}
