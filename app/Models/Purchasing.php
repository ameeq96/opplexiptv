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

    public function scopeCurrency($q, string $currency)
    {
        return $q->where('currency', $currency);
    }
    public function scopeBetweenPurchase($q, ?\App\Support\DateRange $range)
    {
        if ($range?->isBounded()) {
            $q->whereBetween('purchase_date', [$range->start, $range->end]);
        }
        return $q;
    }
}
