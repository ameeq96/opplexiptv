<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchasing extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function pictures()
    {
        return $this->morphMany(\App\Models\Picture::class, 'imageable');
    }
}
