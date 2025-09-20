<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardFilterRequest;
use App\Models\Order;
use App\Models\Purchasing;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index(DashboardFilterRequest $request)
    {
        $filter     = $request->validated('filter') ?? 'all';
        $startInput = $request->validated('start_date') ?? null;
        $endInput   = $request->validated('end_date') ?? null;

        $now     = now();
        $today   = $now->toDateString();
        $isToday = $filter === 'today';
        $isRange = !$isToday && $startInput && $endInput;

        $start = $isRange ? Carbon::parse($startInput)->startOfDay() : null;
        $end   = $isRange ? Carbon::parse($endInput)->endOfDay()   : null;

        $baseOrders = Order::query()
            ->whereIn('status', ['active', 'expired'])
            ->when($isToday, fn($q) => $q->whereDate('buying_date', $today))
            ->when($isRange, fn($q) => $q->whereBetween('buying_date', [$start, $end]));

        $counts = (clone $baseOrders)
            ->toBase()
            ->selectRaw("
            COUNT(*)                                             AS total,
            SUM(CASE WHEN status = 'active'  THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) AS expired
        ")
            ->first();

        $totalOrders   = (int) ($counts->total   ?? 0);
        $activeOrders  = (int) ($counts->active  ?? 0);
        $expiredOrders = (int) ($counts->expired ?? 0);

        $users = User::query()->count();

        $ordersAgg = Order::query()
            ->selectRaw("
            currency,
            SUM(CASE WHEN type = 'package'  THEN price  ELSE 0 END) AS package_sum,
            SUM(CASE WHEN type = 'reseller' THEN profit ELSE 0 END) AS reseller_sum
        ")
            ->when($isToday, fn($q) => $q->whereDate('buying_date', $today))
            ->when($isRange, fn($q) => $q->whereBetween('buying_date', [$start, $end]))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        $purchaseAgg = Purchasing::query()
            ->selectRaw('currency, SUM(cost_price) AS purchase_sum')
            ->when($isToday, fn($q) => $q->whereDate('purchase_date', $today))
            ->when($isRange, fn($q) => $q->whereBetween('purchase_date', [$start, $end]))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        $currencies = collect($ordersAgg->keys())
            ->merge($purchaseAgg->keys())
            ->unique()
            ->values();

        $earningsByCurrency = [];
        foreach ($currencies as $ccy) {
            $o   = $ordersAgg[$ccy] ?? null;
            $pkg = (float) ($o->package_sum ?? 0);
            $res = (float) ($o->reseller_sum ?? 0);
            $pur = (float) ($purchaseAgg[$ccy]->purchase_sum ?? 0);

            $gross = $pkg + $res;
            $earningsByCurrency[$ccy] = $filter === 'all' ? $gross - $pur : $gross;
        }

        return view('admin.dashboard', [
            'users'              => $users,
            'activeOrders'       => $activeOrders,
            'expiredOrders'      => $expiredOrders,
            'totalOrders'        => $totalOrders,
            'earningsByCurrency' => $earningsByCurrency,
            'filter'             => $filter,
            'startDate'          => $isToday ? $now->startOfDay()->toDateTimeString() : $start?->toDateTimeString(),
            'endDate'            => $isToday ? $now->endOfDay()->toDateTimeString()   : $end?->toDateTimeString(),
        ]);
    }
}
