<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelLogoTranslation extends Model
{
    protected $fillable = ['channel_logo_id', 'locale', 'alt_text'];
}
