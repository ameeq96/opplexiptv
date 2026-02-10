<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingSectionTranslation extends Model
{
    protected $fillable = ['pricing_section_id', 'locale', 'heading', 'subheading', 'show_reseller_label', 'credit_info'];
}
