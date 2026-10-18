<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceIncident extends Model
{
    public const STATES = ['investigating', 'identified', 'monitoring', 'resolved'];

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime', 'resolved_at' => 'datetime', 'is_published' => 'boolean', 'published_at' => 'datetime',
    ];
}
