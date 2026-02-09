<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingSection extends Model
{
    protected $fillable = [
        'heading',
        'subheading',
        'show_reseller_label',
        'credit_info',
    ];
}
