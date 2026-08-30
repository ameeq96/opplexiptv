<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutDraft extends Model
{
    protected $guarded = [];

    protected $casts = [
        'connection_price' => 'decimal:2',
        'email_consented_at' => 'datetime',
        'whatsapp_consented_at' => 'datetime',
        'ads_consented_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'retention_expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function completedOrder()
    {
        return $this->belongsTo(Order::class, 'completed_order_id');
    }
}
