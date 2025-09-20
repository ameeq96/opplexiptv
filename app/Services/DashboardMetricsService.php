<?php

namespace App\Services;

use App\Data\DashboardData;
use App\Models\Order;
use App\Models\User;
use App\Support\DateRange;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    /** @var string[] */
    private array $currencies = ['PKR','USD','CAD','AED','EUR','GBP','SAR','INR'];

    public function get(DateRange $range, string $filter): DashboardData
    {
        $cacheKey = $this->cacheKey($range, $filter);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($range, $filter) {
            $baseOrders = Order::query()->whereIn('status', ['active','expired']);
            if ($range->isBounded()) {
                $baseOrders->whereBetween('buying_date', [$range->start, $range->end]);
            }

            $totalOrders   = (clone $baseOrders)->count();
            $activeOrders  = (clone $baseOrders)->where('status', 'active')->count();
            $expiredOrders = (clone $baseOrders)->where('status', 'expired')->count();
            $users         = User::query()->count();

            $ordersAgg = DB::table('orders')
                ->selectRaw("
                    currency,
                    SUM(CASE WHEN type='package'  THEN price  ELSE 0 END) as package_sum,
                    SUM(CASE WHEN type='reseller' THEN profit ELSE 0 END) as reseller_sum
                ")
                ->whereIn('status', ['active','expired'])
                ->when($range->isBounded(), fn($q) => $q->whereBetween('buying_date', [$range->start, $range->end]))
                ->groupBy('currency')
                ->get()
                ->keyBy('currency');

            $purchaseAgg = DB::table('purchasings')
                ->selectRaw('currency, SUM(cost_price) as purchase_sum')
                ->when($range->isBounded(), fn($q) => $q->whereBetween('purchase_date', [$range->start, $range->end]))
                ->groupBy('currency')
                ->get()
                ->keyBy('currency');

            $earningsByCurrency = [];
            foreach ($this->currencies as $ccy) {
                $o   = $ordersAgg[$ccy] ?? null;
                $pkg = $o?->package_sum ? (float)$o->package_sum : 0.0;
                $res = $o?->reseller_sum ? (float)$o->reseller_sum : 0.0;
                $pur = (float)($purchaseAgg[$ccy]->purchase_sum ?? 0.0);

                $gross = $pkg + $res;
                $earningsByCurrency[$ccy] = $filter === 'all' ? $gross - $pur : $gross;
            }

            return new DashboardData(
                users: $users,
                activeOrders: $activeOrders,
                expiredOrders: $expiredOrders,
                totalOrders: $totalOrders,
                earningsByCurrency: $earningsByCurrency,
            );
        });
    }

    private function cacheKey(DateRange $range, string $filter): string
    {
        $start = $range->start?->timestamp ?? 'null';
        $end   = $range->end?->timestamp ?? 'null';
        return "dash:v1:{$filter}:{$start}:{$end}";
    }
}
