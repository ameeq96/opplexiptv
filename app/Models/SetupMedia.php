<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupMedia extends Model
{
    protected $table = 'setup_media';

    protected $guarded = [];

    protected $casts = [
        'tested_at' => 'datetime', 'publication_permission_at' => 'datetime',
        'is_published' => 'boolean', 'published_at' => 'datetime',
    ];
}
