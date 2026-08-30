<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingDelivery extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'scheduled_at' => 'datetime',
        'processing_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function checkoutDraft()
    {
        return $this->belongsTo(CheckoutDraft::class);
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
}
