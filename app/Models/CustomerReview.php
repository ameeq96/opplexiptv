<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    protected $guarded = [];

    protected $casts = [
        'rating' => 'integer', 'publication_consented_at' => 'datetime', 'withdrawn_at' => 'datetime',
        'moderated_at' => 'datetime', 'published_at' => 'datetime',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function user() { return $this->belongsTo(User::class); }
}
