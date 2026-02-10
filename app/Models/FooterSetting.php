<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    protected $fillable = [
        'brand_text',
        'crypto_note',
        'phone',
        'email',
        'address',
        'rights_text',
        'legal_note',
    ];
}
