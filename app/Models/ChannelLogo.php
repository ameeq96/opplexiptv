<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelLogo extends Model
{
    protected $fillable = [
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'sort_order' => 'int',
    ];
}
