<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPublishDelivery extends Model
{
    protected $fillable = [
        'run_id', 'channel', 'revision', 'status', 'attempts', 'http_status',
        'error_code', 'last_attempt_at', 'next_attempt_at',
    ];

    protected $casts = [
        'attempts' => 'integer', 'http_status' => 'integer',
        'last_attempt_at' => 'datetime', 'next_attempt_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(BlogGenerationRun::class, 'run_id');
    }
}
