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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelOrderController extends Controller
{
    public function __construct(
        private PanelOrderQueryService $query,
        private PanelOrderCrudService $crud,
        private PanelOrderMediaService $media,
        private PanelOrderBulkService $bulk,
    ) {}

    public function index(Request $request)
    {
        $builder = $this->query->base();
        $this->query->applyFilters($builder, $request);
        $this->query->applySorting($builder, $request);
        $orders = $this->query->paginate($builder, $request);

        $tab  = $request->query('tab', 'unmessaged');
        $type = $request->query('type', 'reseller');

        return view('admin.reseller-orders.index', compact('orders','tab','type'));
    }

    public function create()
    {
        return view('admin.reseller-orders.create', [
            'clients' => User::orderBy('name')->get(),
        ]);
    }

    public function store(StorePanelOrderRequest $request)
    {
        $order = $this->crud->create($request);
        if ($request->hasFile('screenshots')) {
            $this->media->storeScreenshots($order, $request->file('screenshots'));
        }
        return redirect()->route('admin.panel-orders.index')->with('success', 'Reseller order created!');
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
        $this->crud->update($request, $panel_order);
        if ($request->hasFile('screenshots')) {
            $this->media->storeScreenshots($panel_order, $request->file('screenshots'));
        }
        return redirect()->route('admin.panel-orders.index')->with('success', 'Reseller order updated!');
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

        return redirect()->route('admin.panel-orders.index')->with('success', 'Reseller order deleted!');
    }

    public function destroyPicture(Order $order, Picture $picture)
    {
        $this->media->deletePicture($order, $picture);
        return back()->with('success', 'Screenshot deleted.');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('order_ids', []);
        $action = (string) $request->string('action');
        $msg = $this->bulk->handle($action, $ids, Auth::id());

        return back()->with(str_starts_with($msg, 'No') ? 'error' : 'success', $msg);
    }
}
