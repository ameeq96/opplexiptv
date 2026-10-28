<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogSiteSetting extends Model
{
    protected $fillable = ['site_key', 'enabled', 'timezone', 'publish_time', 'locales'];

    protected $casts = ['enabled' => 'boolean', 'locales' => 'array'];

    public function runs(): HasMany
    {
        return $this->hasMany(BlogGenerationRun::class, 'site_key', 'site_key');
    }
}
