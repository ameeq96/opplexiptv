<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerExternalLink extends Model
{
    protected $guarded = [];

    protected $casts = ['state' => 'array', 'last_sequence' => 'integer'];

    public function user() { return $this->belongsTo(User::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
