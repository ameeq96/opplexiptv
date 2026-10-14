<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerActivity extends Model
{
    protected $guarded = [];

    protected $casts = ['details' => 'array', 'occurred_at' => 'datetime', 'source_sequence' => 'integer'];

    public function user() { return $this->belongsTo(User::class); }
    public function order() { return $this->belongsTo(Order::class); }
    public function admin() { return $this->belongsTo(Admin::class); }
}
