<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Orders\{StoreOrderRequest, UpdateOrderRequest};
use App\Models\{Order, Picture, User};
use App\Services\Orders\{OrderService, OrderMediaService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orders,
        private OrderMediaService $media
    ) {}

    public function index(Request $request)
    {
        $query = $this->orders->query();
        $this->orders->applyFilters($query, $request);
        $this->orders->applySorting($query, $request);

        $orders = $this->orders->paginate($query, $request);
        $today  = Carbon::today();
        $tab    = $request->query('tab', 'unmessaged');
        $type   = $request->query('type', 'package');

        return view('admin.orders.index', compact('orders','today','tab','type'));
    }

    public function create()
    {
        $clients = User::orderBy('name')->get();
        return view('admin.orders.create', compact('clients'));
    }

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orders->createOrder($request);
        if ($request->hasFile('screenshots')) {
            $this->media->storeScreenshots($order, $request->file('screenshots'));
        }
        return redirect()->route('admin.orders.index')->with('success', 'Order added!');
    }

    public function edit(Order $order)
    {
        $clients = User::orderBy('name')->get();
        return view('admin.orders.edit', compact('order','clients'));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->orders->updateOrder($request, $order);
        if ($request->hasFile('screenshots')) {
            $this->media->storeScreenshots($order, $request->file('screenshots'));
        }
        return redirect()->route('admin.orders.index')->with('success', 'Order updated.');
    }

    public function destroyPicture(Order $order, Picture $picture)
    {
        $this->media->deletePicture($order, $picture);
        return back()->with('success', 'Screenshot deleted.');
    }

    public function destroy(Order $order)
    {
        $this->orders->deleteOrder($order);
        return back()->with('success', 'Order deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $deleted = $this->orders->bulkDelete($request->input('order_ids', []));
        return back()->with('success', "{$deleted} order(s) deleted successfully.");
    }

    public function bulkAction(Request $request)
    {
        $msg = $this->orders->handleBulkAction($request);
        return back()->with('success', $msg);
    }

    public function markOneMessaged(Order $order)
    {
        $this->orders->markAsMessaged($order, Auth::id());
        return response()->json(['ok' => true]);
    }
}
