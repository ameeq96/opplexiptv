<?php

namespace App\Models\Digital;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_category_id',
        'title',
        'slug',
        'short_description',
        'full_description',
        'price',
        'compare_price',
        'currency',
        'image',
        'is_active',
        'sort_order',
        'product_type',
        'delivery_type',
        'metadata',
        'min_qty',
        'max_qty',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_active' => 'bool',
        'sort_order' => 'int',
        'metadata' => 'array',
        'min_qty' => 'int',
        'max_qty' => 'int',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DigitalCategory::class, 'digital_category_id');
    }

    public function payloads(): HasMany
    {
        return $this->hasMany(DigitalDeliveryPayload::class);
    }
}
