<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Blog extends Model
{
    protected $fillable = [
        'author_id',
        'cover_image',
        'status',
        'published_at',
        'reading_time',
        'views',
        'is_featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BlogTranslation::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_blog_category');
    }

    public function translation(?string $locale = null): ?BlogTranslation
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

    public function getTitleAttribute(): ?string
    {
        return optional($this->translation())->title;
    }

    public function getSlugAttribute(): ?string
    {
        return optional($this->translation())->slug;
    }

    public function getExcerptAttribute(): ?string
    {
        return optional($this->translation())->excerpt;
    }

    public function getContentAttribute(): ?string
    {
        return optional($this->translation())->content;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', Carbon::now());
            });
    }
}
