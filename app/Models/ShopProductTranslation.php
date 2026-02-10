<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopProductTranslation extends Model
{
    protected $fillable = ['shop_product_id', 'locale', 'name'];
}
