<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'messaged_at' => 'datetime',
        'buying_date' => 'datetime',
        'expiry_date' => 'datetime',
        'paid_at' => 'datetime',
        'paid_amount' => 'decimal:2',
        'analytics_consented_at' => 'datetime',
        'ga_purchase_processing_at' => 'datetime',
        'ga_purchase_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function referral()
    {
        return $this->hasOne(Referral::class, 'source_order_id');
    }

    public function referredBy()
    {
        return $this->hasOne(Referral::class, 'referred_order_id');
    }

    public function marketingDeliveries()
    {
        return $this->hasMany(MarketingDelivery::class);
    }

    public function pictures()
    {
        return $this->morphMany(Picture::class, 'imageable');
    }

    public function scopeUnmessaged($q)
    {
        return $q->whereNull('messaged_at');
    }
    public function scopeMessaged($q)
    {
        return $q->whereNotNull('messaged_at');
    }

    public function scopeStatusIn($q, array $statuses)
    {
        return $q->whereIn('status', $statuses);
    }
    public function scopeType($q, string $type)
    {
        return $q->where('type', $type);
    }
    public function scopeCurrency($q, string $currency)
    {
        return $q->where('currency', $currency);
    }
    public function scopeBetweenBuying($q, ?\App\Support\DateRange $range)
    {
        if ($range?->isBounded()) {
            $q->whereBetween('buying_date', [$range->start, $range->end]);
        }
        return $q;
    }
}
