<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterLink extends Model
{
    protected $fillable = [
        'group',
        'label',
        'url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'sort_order' => 'int',
    ];
}
