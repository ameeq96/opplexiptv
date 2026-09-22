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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value;
        $this->attributes['email_normalized'] = User::normalizeEmail($value);
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value;
        $this->attributes['phone_normalized'] = User::normalizePhone($value);
    }
}
