<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'admin_name',
        'admin_email',
        'action',
        'route_name',
        'method',
        'path',
        'target_type',
        'target_id',
        'request_data',
        'ip_address',
        'user_agent',
        'response_status',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_status' => 'integer',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
