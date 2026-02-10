<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTranslation extends Model
{
    protected $fillable = ['package_id', 'locale', 'title', 'features'];

    protected $casts = [
        'features' => 'array',
    ];
}
