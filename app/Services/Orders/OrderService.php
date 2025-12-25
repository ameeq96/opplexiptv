<?php

namespace App\Services\Orders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderService
{
    public function query(): Builder
    {
        return Order::with(['user','pictures']);
    }

    public function applyFilters(Builder $query, Request $request): void
    {
        $today = Carbon::today();

        // type
        $type = $request->query('type', 'package');
        if ($type === 'reseller') {
            $query->where('type', 'reseller');
        } elseif ($type === 'package') {
            $query->where('type', 'package');
        } // 'all' => no filter

        // tab
        $tab = $request->query('tab', 'unmessaged');
        if ($tab === 'messaged')       $query->whereNotNull('messaged_at');
        elseif ($tab === 'unmessaged') $query->whereNull('messaged_at');

        // search
        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($qu) => $qu->where('name', 'like', "%{$search}%"))
                  ->orWhere('package', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('iptv_username', 'like', "%{$search}%");
            });
        }

        // quick date filters
        if ($request->filled('date_filter')) {
            $now = now();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('buying_date', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('buying_date', $today->copy()->subDay());
                    break;
                case '7days':
                    $query->whereBetween('buying_date', [$now->copy()->subDays(6)->startOfDay(), $now->endOfDay()]);
                    break;
                case '30days':
                    $query->whereBetween('buying_date', [$now->copy()->subDays(29)->startOfDay(), $now->endOfDay()]);
                    break;
                case '90days':
                    $query->whereBetween('buying_date', [$now->copy()->subDays(89)->startOfDay(), $now->endOfDay()]);
                    break;
                case 'year':
                    $query->whereYear('buying_date', $now->year);
                    break;
            }
        }

        // custom date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('buying_date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        // expiry filters
        if ($request->filled('expiry_status')) {
            if ($request->expiry_status === 'expired') {
                $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today);
            } elseif ($request->expiry_status === 'soon') {
                $query->whereNotNull('expiry_date')->whereBetween('expiry_date', [$today, $today->copy()->addDays(5)]);
            }
        }

        // status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
    }

    public function applySorting(Builder $query, Request $request): void
    {
        if ($request->filled('expiry_status')) {
            $query->orderByRaw("
                CASE 
                    WHEN expiry_date IS NULL THEN 999999
                    WHEN expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 0
                    WHEN expiry_date >= NOW() THEN DATEDIFF(expiry_date, NOW())
                    ELSE 999998
                END ASC
            ")->orderBy('expiry_date', 'asc')
              ->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    public function paginate(Builder $query, Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 200 ? $perPage : 10;
        return $query->paginate($perPage)->appends($request->all());
    }

    /** Create / Update shared mapping for 'other' fields */
    public function mapPayload(array $data, Request $request): array
    {
        if (($request->payment_method === 'other') && $request->filled('custom_payment_method')) {
            $data['payment_method'] = (string) $request->string('custom_payment_method');
        }
        if (($request->package === 'other') && $request->filled('custom_package')) {
            $data['package'] = (string) $request->string('custom_package');
        }
        return $data;
    }

    public function createOrder(Request $request): Order
    {
        $data = $this->mapPayload($request->only([
            'user_id','package','price','duration','status','payment_method',
            'currency','buying_date','expiry_date','iptv_username','note'
        ]), $request);

        $data['type'] = 'package';
        return Order::create($data);
    }

    public function updateOrder(Request $request, Order $order): void
    {
        $data = $this->mapPayload($request->only([
            'user_id','package','price','duration','status','payment_method',
            'currency','buying_date','expiry_date','iptv_username','note'
        ]), $request);

        // If dates are left empty in the form, keep existing values
        if (!$request->filled('buying_date')) {
            unset($data['buying_date']);
        }
        if (!$request->filled('expiry_date')) {
            unset($data['expiry_date']);
        }

        $order->update($data);
    }

    public function deleteOrder(Order $order): void
    {
        $order->delete();
    }

    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        return Order::whereIn('id', $ids)->delete();
    }

    public function handleBulkAction(Request $request): string
    {
        $ids = $request->input('order_ids', []);
        $action = (string) $request->string('action');

        if (empty($ids)) return 'No orders selected.';

        if ($action === 'delete') {
            $deleted = $this->bulkDelete($ids);
            return "{$deleted} order(s) deleted successfully.";
        }

        if ($action === 'mark_messaged') {
            Order::whereIn('id', $ids)->update([
                'messaged_at' => now(),
                'messaged_by' => auth()->id(),
            ]);
            return 'Selected orders marked as messaged.';
        }

        if ($action === 'unmark_messaged') {
            Order::whereIn('id', $ids)->update([
                'messaged_at' => null,
                'messaged_by' => false,
            ]);
            return 'Selected orders moved back to Unmessaged.';
        }

        return 'No valid action provided.';
    }

    public function markAsMessaged(Order $order, ?int $userId): void
    {
        $order->update(['messaged_at' => now(), 'messaged_by' => $userId]);
    }
}
