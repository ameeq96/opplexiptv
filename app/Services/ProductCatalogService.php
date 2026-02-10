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
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale');

            return ShopProduct::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->with(['translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', array_unique([$locale, $fallback]));
                }])
                ->get(['id', 'name', 'asin', 'link', 'image'])
                ->map(function (ShopProduct $p) {
                    $t = $p->translation();
                    return [
                        'name' => $t?->name ?: $p->name,
                        'asin' => $p->asin,
                        'link' => $p->link,
                        'image' => $p->image,
                    ];
                })
                ->toArray();
        }

        return (array) config('shop.products', []);
    }

    public function getLogos(): array
    {
        if (Schema::hasTable('channel_logos')) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale');

            return ChannelLogo::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->with(['translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', array_unique([$locale, $fallback]));
                }])
                ->get(['id', 'image'])
                ->map(function (ChannelLogo $logo) {
                    $t = $logo->translation();
                    return [
                        'image' => $logo->image,
                        'alt' => $t?->alt_text,
                    ];
                })
                ->toArray();
        }

        return (array) config('shop.logos', []);
    }
}
