<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    protected $fillable = [];

    public function translations(): HasMany
    {
        return $this->hasMany(BlogCategoryTranslation::class);
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_blog_category');
    }

    public function translation(?string $locale = null): ?BlogCategoryTranslation
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