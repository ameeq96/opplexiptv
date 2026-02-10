<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterLinkTranslation extends Model
{
    protected $fillable = ['footer_link_id', 'locale', 'label'];
}
