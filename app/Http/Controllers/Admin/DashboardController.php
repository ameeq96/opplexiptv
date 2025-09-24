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

        $now = now();

        [$start, $end] = match ($filter) {
            'today'     => [$now->clone()->startOfDay(), $now->clone()->endOfDay()],
            'yesterday' => [$now->clone()->subDay()->startOfDay(), $now->clone()->subDay()->endOfDay()],
            '7days'     => [$now->clone()->subDays(6)->startOfDay(), $now->clone()->endOfDay()],
            '30days'    => [$now->clone()->subDays(29)->startOfDay(), $now->clone()->endOfDay()],
            '90days'    => [$now->clone()->subDays(89)->startOfDay(), $now->clone()->endOfDay()],
            'year'      => [$now->clone()->startOfYear(), $now->clone()->endOfDay()],
            default     => [null, null],
        };

        if ($startInput && $endInput) {
            $start  = \Carbon\Carbon::parse($startInput)->startOfDay();
            $end    = \Carbon\Carbon::parse($endInput)->endOfDay();
            $filter = 'custom';
        }
        $hasRange = $start && $end;

        $baseOrders = Order::query()
            ->when($hasRange, fn($q) => $q->whereBetween('buying_date', [$start, $end]));

        $totalOrders = (clone $baseOrders)->count();
        $activeOrders = (clone $baseOrders)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', $now)
            ->count();

        $expiredOrders = (clone $baseOrders)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $now)
            ->count();

        $users = User::query()->count();

        $ordersAgg = Order::query()
            ->selectRaw("
            currency,
            SUM(CASE WHEN type = 'package'  THEN price  ELSE 0 END) AS package_sum,
            SUM(CASE WHEN type = 'reseller' THEN profit ELSE 0 END) AS reseller_sum
        ")
            ->when($hasRange, fn($q) => $q->whereBetween('buying_date', [$start, $end]))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        $purchaseAgg = Purchasing::query()
            ->selectRaw('currency, SUM(cost_price) AS purchase_sum')
            ->when($hasRange, fn($q) => $q->whereBetween('purchase_date', [$start, $end]))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        $currencies = collect($ordersAgg->keys())->merge($purchaseAgg->keys())->unique()->values();

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
            'startDate'          => $hasRange ? $start->toDateTimeString() : null,
            'endDate'            => $hasRange ? $end->toDateTimeString()   : null,
        ]);
    }
}
