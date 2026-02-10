<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeServiceTranslation extends Model
{
    protected $fillable = ['home_service_id', 'locale', 'title', 'description'];
}
