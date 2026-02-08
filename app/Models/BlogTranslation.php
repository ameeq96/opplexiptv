<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogTranslation extends Model
{
    protected $fillable = [
        'blog_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'schema_json',
    ];

    protected $casts = [
        'schema_json' => 'array',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }
}
