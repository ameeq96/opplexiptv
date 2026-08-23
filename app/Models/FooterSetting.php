<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FooterSetting extends Model
{
    protected $fillable = [
        'brand_text',
        'crypto_note',
        'phone',
        'email',
        'address',
        'rights_text',
        'legal_note',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(FooterSettingTranslation::class);
    }

    public function translation(?string $locale = null): ?FooterSettingTranslation
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');
        $this->loadMissing('translations');
        $translations = $this->translations;

        return $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', $fallback)
            ?: $translations->first();
    }
}
