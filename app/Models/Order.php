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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
