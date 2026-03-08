<?php

namespace App\Models\Digital;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_order_id',
        'digital_product_id',
        'product_title',
        'unit_price',
        'quantity',
        'line_total',
        'delivery_status',
        'delivered_at',
        'delivery_payload_id',
        'assigned_by_admin_id',
        'assigned_at',
        'delivery_meta',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'quantity' => 'int',
        'delivered_at' => 'datetime',
        'assigned_at' => 'datetime',
        'delivery_meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DigitalOrder::class, 'digital_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class, 'digital_product_id');
    }

    public function deliveryPayload(): BelongsTo
    {
        return $this->belongsTo(DigitalDeliveryPayload::class, 'delivery_payload_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by_admin_id');
    }
}
