<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingSection extends Model
{
    protected $fillable = [
        'heading',
        'subheading',
        'show_reseller_label',
        'credit_info',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PricingSectionTranslation::class);
    }

    public function translation(?string $locale = null): ?PricingSectionTranslation
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');
        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        return $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', $fallback)
            ?: $translations->first();
    }
}
