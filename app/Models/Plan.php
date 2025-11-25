<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name','max_devices','price','active'];

    protected $casts = [
        'max_devices' => 'integer',
        'price'       => 'float',
        'active'      => 'boolean',
    ];
}
