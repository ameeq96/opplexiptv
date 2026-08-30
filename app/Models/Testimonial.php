<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Testimonial extends Model
{
    protected $fillable = [
        'author_name',
        'text',
        'image',
        'sort_order',
        'is_active',
        'is_verified',
        'verified_at',
        'review_date',
        'country',
        'device',
        'verification_source',
        'proof_reference',
        'publication_consented_at',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'sort_order' => 'int',
        'is_verified' => 'bool',
        'verified_at' => 'datetime',
        'review_date' => 'date',
        'publication_consented_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(TestimonialTranslation::class);
    }

    public function translation(?string $locale = null): ?TestimonialTranslation
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
