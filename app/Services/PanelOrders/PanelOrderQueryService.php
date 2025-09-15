<?php

namespace App\Services\PanelOrders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PanelOrderQueryService
{
    public function base(): Builder
    {
        return Order::with(['user','pictures'])->where('type', 'reseller');
    }

    public function applyFilters(Builder $q, Request $request): void
    {
        // type filter
        $type = $request->query('type', 'reseller');
        if ($type === 'reseller')       $q->where('type', 'reseller');
        elseif ($type === 'package')    $q->where('type', 'package');
        // 'all' => no type filter

        // tab filter
        $tab = $request->query('tab', 'unmessaged');
        if ($tab === 'messaged')        $q->whereNotNull('messaged_at');
        elseif ($tab === 'unmessaged')  $q->whereNull('messaged_at');
        // 'all' => none

        // search
        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $q->where(function ($qb) use ($search) {
                $qb->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%")
                           ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhere('package', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('iptv_username', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // date filters
        if ($request->filled('date_filter')) {
            $today = Carbon::today(); $now = now();
            switch ($request->date_filter) {
                case 'today':     $q->whereDate('buying_date', $today); break;
                case 'yesterday': $q->whereDate('buying_date', $today->copy()->subDay()); break;
                case '7days':     $q->whereBetween('buying_date', [$now->copy()->subDays(6)->startOfDay(), $now->endOfDay()]); break;
                case '30days':    $q->whereBetween('buying_date', [$now->copy()->subDays(29)->startOfDay(), $now->endOfDay()]); break;
                case '90days':    $q->whereBetween('buying_date', [$now->copy()->subDays(89)->startOfDay(), $now->endOfDay()]); break;
                case 'year':      $q->whereYear('buying_date', $now->year); break;
            }
        }

        // custom range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $q->whereBetween('buying_date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        // expiry filters
        if ($request->filled('expiry_status')) {
            $today = Carbon::today();
            if ($request->expiry_status === 'expired') {
                $q->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today);
            } elseif ($request->expiry_status === 'soon') {
                $q->whereNotNull('expiry_date')->whereBetween('expiry_date', [$today, $today->copy()->addDays(5)]);
            }
        }
    }

    public function applySorting(Builder $q, Request $request): void
    {
        if ($request->filled('expiry_status')) {
            $q->orderByRaw("
                CASE 
                    WHEN expiry_date IS NULL THEN 999999
                    WHEN expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 0
                    WHEN expiry_date >= NOW() THEN DATEDIFF(expiry_date, NOW())
                    ELSE 999998
                END ASC
            ")
              ->orderBy('expiry_date', 'asc')
              ->orderBy('created_at', 'desc');
        } else {
            $q->orderBy('created_at', 'desc');
        }
    }

    public function paginate(Builder $q, Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 200 ? $perPage : 10;
        return $q->paginate($perPage)->appends($request->all());
    }
}
