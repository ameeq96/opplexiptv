<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMessage extends Model
{
    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime'];

    public function conversation() { return $this->belongsTo(CustomerConversation::class, 'conversation_id'); }
}
