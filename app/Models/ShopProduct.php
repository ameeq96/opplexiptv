<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopProduct extends Model
{
    protected $fillable = [
        'name',
        'asin',
        'link',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'sort_order' => 'int',
    ];
}
