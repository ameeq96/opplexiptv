<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\PanelOrders\{StorePanelOrderRequest, UpdatePanelOrderRequest};
use App\Models\{Order, Picture, User};
use App\Services\PanelOrders\{
    PanelOrderQueryService,
    PanelOrderCrudService,
    PanelOrderMediaService,
    PanelOrderBulkService
};
use App\Traits\HelperFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelOrderController extends Controller
{
    use HelperFunction { bulkDelete as helperBulkDelete; }

    public function __construct(
        private PanelOrderQueryService $query,
        private PanelOrderCrudService $crud,
        private PanelOrderMediaService $media,
        private PanelOrderBulkService $bulk,
    ) {}

    public function index(Request $request)
    {
        $orders = $this->runIndex($this->query, $request);
        return view('admin.reseller-orders.index', [
            'orders' => $orders,
            'tab'    => $request->query('tab', 'unmessaged'),
            'type'   => $request->query('type', 'reseller'),
        ]);
    }

    public function show(Order $panel_order)
    {
        $panel_order->load(['user', 'pictures']);
        return view('admin.orders.show', [
            'order'      => $panel_order,
            'isReseller' => true,
        ]);
    }

    public function create()
    {
        return view('admin.reseller-orders.create', [
            'clients' => User::orderBy('name')->get(),
        ]);
    }

    public function store(StorePanelOrderRequest $request)
    {
        $this->handleStore($this->crud, $this->media, $request);
        return redirect()->route('admin.panel-orders.index')->with('success', __('messages.reseller_order_created'));
    }

    public function edit(Order $panel_order)
    {
        return view('admin.reseller-orders.edit', [
            'order'   => $panel_order,
            'clients' => User::orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePanelOrderRequest $request, Order $panel_order)
    {
        $this->handleUpdate($this->crud, $this->media, $request, $panel_order);
        return redirect()->route('admin.panel-orders.index')->with('success', __('messages.reseller_order_updated'));
    }

    public function destroy(Order $panel_order)
    {
        $panel_order->load('pictures');
        foreach ($panel_order->pictures as $pic) {
            $fullPath = public_path($pic->path);
            if (is_file($fullPath)) @unlink($fullPath);
            $pic->delete();
        }
        $this->crud->delete($panel_order);

        return redirect()->route('admin.panel-orders.index')->with('success', __('messages.reseller_order_deleted'));
    }

    public function destroyPicture(Order $order, Picture $picture)
    {
        $this->media->deletePicture($order, $picture);
        return back()->with('success', __('messages.screenshot_deleted'));
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('order_ids', []);
        $action = (string) $request->string('action');
        $msg = $this->bulk->handle($action, $ids, Auth::id());

        return back()->with(str_starts_with($msg, 'No') ? 'error' : 'success', $msg);
    }

    public function bulkDelete(Request $request)
    {
        return $this->helperBulkDelete($request, 'order_ids', $this->crud, $this->media, \App\Models\Order::class);
    }
}
