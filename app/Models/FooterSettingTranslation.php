<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSettingTranslation extends Model
{
    protected $fillable = ['footer_setting_id', 'locale', 'brand_text', 'crypto_note', 'address', 'rights_text', 'legal_note'];
}
