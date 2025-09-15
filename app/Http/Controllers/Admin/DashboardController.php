<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardFilterRequest;
use App\Support\DateRange;
use App\Services\DashboardMetricsService;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metrics) {}


    public function index(DashboardFilterRequest $request)
    {
        $filter = $request->validated('filter') ?? 'all';
        $range = DateRange::fromFilter(
            filter: $filter,
            start: $request->validated('start_date') ?? null,
            end: $request->validated('end_date') ?? null,
        );


        $data = $this->metrics->get($range, $filter);


        return view('admin.dashboard', [
            'users' => $data->users,
            'activeOrders' => $data->activeOrders,
            'expiredOrders' => $data->expiredOrders,
            'totalOrders' => $data->totalOrders,
            'earningsByCurrency' => $data->earningsByCurrency,
            'filter' => $filter,
            'startDate' => $range->start?->toDateTimeString(),
            'endDate' => $range->end?->toDateTimeString(),
        ]);
    }
}
