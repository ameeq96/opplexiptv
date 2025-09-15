<?php

namespace App\Services\PanelOrders;

use App\Models\Order;

class PanelOrderBulkService
{
    public function delete(array $ids): int
    {
        if (empty($ids)) return 0;

        $orders = Order::whereIn('id', $ids)->where('type', 'reseller')->with('pictures')->get();
        foreach ($orders as $order) {
            foreach ($order->pictures as $pic) {
                $fullPath = public_path($pic->path);
                if (is_file($fullPath)) @unlink($fullPath);
                $pic->delete();
            }
        }

        return Order::whereIn('id', $ids)->where('type', 'reseller')->delete();
    }

    public function handle(string $action, array $ids, ?int $userId): string
    {
        if (empty($ids)) return 'No orders selected.';

        $base = Order::whereIn('id', $ids)->where('type', 'reseller');

        if ($action === 'mark_messaged') {
            $base->update(['messaged_at' => now(), 'messaged_by' => $userId]);
            return 'Selected reseller orders marked as messaged.';
        }

        if ($action === 'unmark_messaged') {
            $base->update(['messaged_at' => null, 'messaged_by' => null]);
            return 'Selected reseller orders moved back to Unmessaged.';
        }

        if ($action === 'delete') {
            $count = $this->delete($ids);
            return "{$count} reseller order(s) deleted successfully.";
        }

        return 'No valid action provided.';
    }
}
