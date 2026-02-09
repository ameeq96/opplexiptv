<?php
// app/Models/Package.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Package extends Model
{
    protected $fillable = [
        'type',
        'vendor',
        'title',
        'display_price',
        'price_amount',
        'icon',
        'icons',
        'features',
        'duration_months',
        'credits',
        'max_devices',
        'sort_order',
        'button_link',
        'delay',
        'active',
    ];

    protected $casts = [
        'icons'           => 'array',
        'features'        => 'array',
        'price_amount'    => 'float',
        'duration_months' => 'integer',
        'credits'         => 'integer',
        'max_devices'     => 'integer',
        'sort_order'      => 'integer',
        'active'          => 'boolean',
    ];

    /* ───────────────────────────── Scopes ───────────────────────────── */

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    /**
     * Filter by exact type(s). Accepts string or array.
     * Examples:
     *  ->type('opplex')
     *  ->type(['opplex','starshare'])
     */
    public function scopeType($q, $types)
    {
        return $q->whereIn('type', (array) $types);
    }

    /**
     * Filter by kind: 'iptv' or 'reseller'.
     */
    public function scopeKind($q, string $kind)
    {
        $k = strtolower($kind);
        if ($k === 'iptv') {
            // keep it flexible for legacy rows
            return $q->where(function ($qq) {
                $qq->whereIn('type', ['opplex', 'starshare', 'iptv'])
                    ->orWhere('type', 'like', 'iptv_%');
            });
        }
        if ($k === 'reseller') {
            return $q->where(function ($qq) {
                $qq->whereIn('type', ['reseller', 'reseller_opplex', 'reseller_starshare'])
                    ->orWhere('type', 'like', 'reseller_%');
            });
        }
        return $q;
    }

    /**
     * Filter by vendor across kinds.
     * Examples:
     *  ->vendor('opplex')             // opplex iptv + reseller
     *  ->vendor('starshare','iptv')   // only starshare iptv
     */
    public function scopeVendor($q, string $vendor, ?string $onlyKind = null)
    {
        $v = strtolower($vendor);
        $types = [];

        if (!$onlyKind || strtolower($onlyKind) === 'iptv') {
            // IPTV rows usually use just vendor (or iptv_vendor)
            $types[] = $v;
            $types[] = 'iptv_' . $v;
        }
        if (!$onlyKind || strtolower($onlyKind) === 'reseller') {
            // Reseller rows use reseller_vendor
            $types[] = 'reseller_' . $v;
        }

        return $q->whereIn('type', array_unique($types));
    }

    /**
     * Common, handy sorting for cards.
     */
    public function scopeSorted($q)
    {
        return $q->orderByRaw('COALESCE(sort_order, duration_months, credits, id)');
    }

    /* ────────────────────── Internal helpers ────────────────────── */

    /**
     * Normalize vendor from type.
     * Supports:
     *   opplex | starshare
     *   iptv_opplex | iptv_starshare
     *   reseller_opplex | reseller_starshare
     * Falls back to 'opplex' for legacy/unknown.
     */
    private static function normalizedVendor(?string $raw): string
    {
        // Default
        if (!$raw) {
            return 'opplex';
        }

        // Lowercase + trim
        $t = strtolower(trim($raw));

        // Common separators ko normalize karo
        // e.g. "iptv-opplex", "iptv opplex", "IPTV_OPPLEX" etc.
        $t = str_replace([' ', '-', '.'], '_', $t);

        // Direct match: "opplex" / "starshare"
        if (in_array($t, ['opplex', 'starshare'], true)) {
            return $t;
        }

        // Pattern: "iptv_opplex", "reseller_starshare", "iptv_starshare" etc.
        if (Str::startsWith($t, ['iptv_', 'reseller_'])) {
            $parts = explode('_', $t, 2);
            $maybe = $parts[1] ?? '';
            if (in_array($maybe, ['opplex', 'starshare'], true)) {
                return $maybe;
            }
        }

        // Agar kisi ne ulta likha ho: "opplex_iptv", "starshare_reseller"
        if (Str::endsWith($t, ['_opplex', '_starshare'])) {
            return Str::endsWith($t, '_starshare') ? 'starshare' : 'opplex';
        }

        // Last resort: agar kuch samajh na aaye to opplex
        return 'opplex';
    }

    private static function defaultIptvFeatures(): array
    {
        return [
            __('messages.no_buffer'),
            __('messages.support_24_7'),
            __('messages.regular_updates'),
            __('messages.quality_content'),
        ];
    }

    private static function defaultResellerFeatures(): array
    {
        return [
            __('messages.uptime'),
            __('messages.no_credit_expiry'),
            __('messages.unlimited_trials'),
            __('messages.no_subreseller'),
        ];
    }

    private static function formatMoney(?float $amount): ?string
    {
        if ($amount === null) return null;
        // "$2.99" → strip trailing zeros if .00
        $num = rtrim(rtrim(number_format((float) $amount, 2, '.', ''), '0'), '.');
        return '$' . $num;
    }

    /* ───────────────────── Public array mappers ──────────────────── */

    /**
     * IPTV card array used by Blade (needs 'vendor' to toggle).
     */
    public function toIptvArray(): array
    {
        // Yahan raw vendor dena best hai
        $vendor = self::normalizedVendor($this->vendor ?? $this->type);

        $months = (int) ($this->duration_months ?: 1);
        $unit   = $months === 1 ? '1 month' : ($months . ' months');

        $priceStr = $this->display_price;
        if (!$priceStr && $this->price_amount !== null) {
            $priceStr = self::formatMoney($this->price_amount) . ' / ' . $unit;
        }

        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'vendor'   => $vendor,
            'title'    => $this->title,
            'price'    => $priceStr,
            'features' => $this->features ?: self::defaultIptvFeatures(),
            'icon'     => $this->icon ?? 'images/icons/service-1.svg',
        ];
    }

    /**
     * Reseller card array (needs 'vendor' + icons).
     */
    public function toResellerArray(): array
    {
        // Pehle vendor column use karo, warna type (reseller_opplex / reseller_starshare / etc.)
        $vendor = self::normalizedVendor($this->vendor ?? $this->type);

        // Prefer display_price; else derive from numeric + credits
        $priceStr = $this->display_price;
        if (!$priceStr && $this->price_amount !== null) {
            $priceStr = self::formatMoney($this->price_amount);
            if ($this->credits) {
                $priceStr .= ' / ' . $this->credits . ' Credits';
            }
        }

        // Icons normalize
        $icons = $this->icons;
        if (!$icons || !is_array($icons)) {
            $icons = $this->icon ? [$this->icon] : ['images/icons/service-1.svg'];
        }

        return [
            'id'          => $this->id,
            'type'        => $this->type,                     // e.g. reseller_opplex | reseller_starshare
            'vendor'      => $vendor,                         // opplex | starshare (normalized)
            'title'       => $this->title,
            'price'       => $priceStr,
            'icons'       => $icons,
            'features'    => $this->features ?: self::defaultResellerFeatures(),
            'button_link' => $this->button_link,
            'delay'       => $this->delay,
        ];
    }
}
