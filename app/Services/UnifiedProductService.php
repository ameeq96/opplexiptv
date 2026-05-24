<?php

namespace App\Services;

use App\Models\Digital\DigitalProduct;
use App\Models\ShopProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class UnifiedProductService
{
    public function __construct(private ImageService $images) {}

    public function frontendProducts(): Collection
    {
        $key = 'ui:' . app()->getLocale() . ':frontend-products:v2';

        try {
            return Cache::remember($key, now()->addMinutes(30), fn () => $this->buildFrontendProducts());
        } catch (\Throwable) {
            return collect();
        }
    }

    private function buildFrontendProducts(): Collection
    {
        $waBase = 'https://wa.me/16393903194?text=';

        $affiliate = $this->hasTable('shop_products')
            ? ShopProduct::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->with('translations')
                ->get()
                ->map(function (ShopProduct $p) {
                    $name = $p->translation()?->name ?: $p->name;
                    return [
                        'id' => $p->id,
                        'type' => 'affiliate',
                        'name' => $name,
                        'description' => '',
                        'price' => null,
                        'currency' => null,
                        'image' => $p->image ? $this->images->optimizedLocalAsset('images/shop/' . $p->image) : null,
                        'url' => $p->link,
                        'target' => '_blank',
                        'rel' => 'nofollow sponsored noopener',
                        'sort_order' => (int) $p->sort_order,
                        'can_add_to_cart' => false,
                        'add_to_cart_url' => null,
                        'buy_now_url' => null,
                        'share_url' => route('products.share', ['type' => 'affiliate', 'id' => $p->id]),
                        'share_text' => "Check out {$name} on Opplex IPTV.",
                    ];
                })
            : collect();

        $digital = $this->hasTable('digital_products')
            ? DigitalProduct::query()
                ->with('category:id,name')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(function (DigitalProduct $p) use ($waBase) {
                    $price = (float) $p->price;
                    $priceText = (string) $p->currency . number_format($price, 2);
                    $waText = rawurlencode("Hi, I want to buy {$p->title} ({$priceText}).");

                    return [
                        'id' => $p->id,
                        'type' => 'digital',
                        'name' => $p->title,
                        'description' => '',
                        'price' => (float) $p->price,
                        'currency' => (string) $p->currency,
                        'image' => $p->image ? $this->images->optimizedLocalAsset('images/digital-products/' . $p->image) : null,
                        'url' => route('digital.product.show', $p->slug),
                        'target' => null,
                        'rel' => null,
                        'sort_order' => (int) $p->sort_order,
                        'can_add_to_cart' => false,
                        'add_to_cart_url' => null,
                        'buy_now_url' => $waBase . $waText,
                        'share_url' => route('products.share', ['type' => 'digital', 'id' => $p->id]),
                        'share_text' => "Check out {$p->title} on Opplex IPTV.",
                    ];
                })
            : collect();

        return $affiliate
            ->concat($digital)
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'desc'],
            ])
            ->values();
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
