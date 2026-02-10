<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLinkTranslation extends Model
{
    protected $fillable = ['social_link_id', 'locale', 'platform'];
}
