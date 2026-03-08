<?php

namespace App\Models\Digital;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalDeliveryPayload extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_product_id',
        'payload_type',
        'payload',
        'is_assigned',
        'assigned_order_item_id',
        'assigned_by_admin_id',
        'created_by_admin_id',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'payload' => 'encrypted:array',
        'is_assigned' => 'bool',
        'assigned_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class, 'digital_product_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by_admin_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function assignedOrderItem(): BelongsTo
    {
        return $this->belongsTo(DigitalOrderItem::class, 'assigned_order_item_id');
    }

    public function maskedPreview(): string
    {
        $payload = $this->payload ?? [];

        if (isset($payload['username']) || isset($payload['email'])) {
            $value = (string) ($payload['username'] ?? $payload['email']);
            return substr($value, 0, 2) . str_repeat('*', max(strlen($value) - 2, 4));
        }

        if (isset($payload['code'])) {
            $code = (string) $payload['code'];
            return str_repeat('*', max(strlen($code) - 4, 4)) . substr($code, -4);
        }

        if (isset($payload['url'])) {
            return 'Link hidden';
        }

        return 'Payload available';
    }
}
