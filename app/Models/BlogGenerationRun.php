<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogGenerationRun extends Model
{
    protected $fillable = [
        'site_key', 'publishing_date', 'blog_id', 'topic', 'primary_keyword', 'fingerprint',
        'revision', 'payload', 'errors', 'status', 'attempts', 'published_at',
    ];

    protected $casts = [
        'publishing_date' => 'date', 'payload' => 'array', 'errors' => 'array',
        'attempts' => 'integer', 'published_at' => 'datetime',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(BlogSiteSetting::class, 'site_key', 'site_key');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(BlogPublishDelivery::class, 'run_id');
    }
}
