<?php

namespace App\Services\DigitalCommerce;

use App\Models\Admin;
use App\Models\Digital\DigitalDeliveryPayload;
use App\Models\Digital\DigitalOrderItem;
use Illuminate\Support\Facades\DB;

class DeliveryAssignmentService
{
    public function assignManual(DigitalOrderItem $item, DigitalDeliveryPayload $payload, Admin $admin): DigitalOrderItem
    {
        return DB::transaction(function () use ($item, $payload, $admin) {
            $item = DigitalOrderItem::query()->lockForUpdate()->findOrFail($item->id);

            if ($item->delivery_payload_id) {
                throw new \RuntimeException('Delivery is already assigned to this item.');
            }

            $payload = DigitalDeliveryPayload::query()->lockForUpdate()->findOrFail($payload->id);

            if ($payload->is_assigned) {
                throw new \RuntimeException('This payload is already assigned.');
            }

            if ((int) $payload->digital_product_id !== (int) $item->digital_product_id) {
                throw new \RuntimeException('Payload product does not match item product.');
            }

            $now = now();

            $item->update([
                'delivery_payload_id' => $payload->id,
                'assigned_by_admin_id' => $admin->id,
                'assigned_at' => $now,
                'delivery_status' => 'assigned',
                'delivery_meta' => [
                    'payload_preview' => $payload->maskedPreview(),
                ],
            ]);

            $payload->update([
                'is_assigned' => true,
                'assigned_order_item_id' => $item->id,
                'assigned_by_admin_id' => $admin->id,
                'assigned_at' => $now,
            ]);

            return $item->fresh(['deliveryPayload']);
        });
    }

    public function assignFirstAvailable(DigitalOrderItem $item, Admin $admin): ?DigitalOrderItem
    {
        $payload = DigitalDeliveryPayload::query()
            ->where('digital_product_id', $item->digital_product_id)
            ->where('is_assigned', false)
            ->orderBy('id')
            ->first();

        if (!$payload) {
            return null;
        }

        return $this->assignManual($item, $payload, $admin);
    }
}
