<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Orders\{StoreOrderRequest, UpdateOrderRequest};
use App\Models\{Order, Picture, User};
use App\Services\Orders\{OrderService, OrderMediaService};
use App\Traits\HelperFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    use HelperFunction;

    public function __construct(
        private OrderService $orders,
        private OrderMediaService $media
    ) {}

    public function index(Request $request)
    {
        $orders = $this->runIndex($this->orders, $request);
        return view('admin.orders.index', [
            'orders' => $orders,
            'today'  => Carbon::today(),
            'tab'    => $request->query('tab', 'unmessaged'),
            'type'   => $request->query('type', 'package'),
        ]);
    }

    public function create()
    {
        return view('admin.orders.create', [
            'clients' => User::orderBy('name')->get(),
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $this->handleStore($this->orders, $this->media, $request);
        return redirect()->route('admin.orders.index')->with('success', __('messages.order_created'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.edit', [
            'order'   => $order,
            'clients' => User::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->handleUpdate($this->orders, $this->media, $request, $order);
        return redirect()->route('admin.orders.index')->with('success', __('messages.order_updated'));
    }

    public function destroyPicture(Order $order, Picture $picture)
    {
        $this->media->deletePicture($order, $picture);
        return back()->with('success', __('messages.screenshot_deleted'));
    }

    public function destroy(Order $order)
    {
        $this->orders->deleteOrder($order);
        return back()->with('success', __('messages.order_deleted'));
    }

    public function bulkDelete(Request $request)
    {
        return $this->bulkDelete($request, 'order_ids', $this->orders);
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
