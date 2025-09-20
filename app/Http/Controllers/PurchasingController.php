<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Purchasing\{StorePurchasingRequest, UpdatePurchasingRequest};
use App\Models\{Purchasing, Picture};
use App\Services\Purchasing\{
    PurchasingQueryService,
    PurchasingCrudService,
    PurchasingMediaService
};
use App\Traits\HelperFunction;
use Illuminate\Http\Request;

class PurchasingController extends Controller
{
    use HelperFunction;

    public function __construct(
        private PurchasingQueryService $query,
        private PurchasingCrudService $crud,
        private PurchasingMediaService $media,
    ) {}

    public function index(Request $request)
    {
        $purchases = $this->runIndex($this->query, $request);
        return view('admin.purchasing.index', compact('purchases'));
    }

    public function bulkDelete(Request $request)
    {
        return $this->bulkDelete($request, 'purchase_ids', $this->crud, $this->media, Purchasing::class);
    }

    public function create()
    {
        return view('admin.purchasing.create');
    }

    public function store(StorePurchasingRequest $request)
    {
        $this->handleStore($this->crud, $this->media, $request);
        return redirect()->route('admin.purchasing.index')->with('success', __('messages.purchase_created'));
    }

    public function edit(Purchasing $purchasing)
    {
        return view('admin.purchasing.edit', compact('purchasing'));
    }

    public function update(UpdatePurchasingRequest $request, Purchasing $purchasing)
    {
        $this->handleUpdate($this->crud, $this->media, $request, $purchasing);
        return redirect()->route('admin.purchasing.index')->with('success', __('messages.purchase_updated'));
    }

    public function destroy(Purchasing $purchasing)
    {
        $purchasing->load('pictures');
        $this->media->cleanupPictures([$purchasing]);
        $this->crud->delete($purchasing);

        return redirect()->route('admin.purchasing.index')->with('success', __('messages.purchase_deleted'));
    }

    public function destroyPicture(Purchasing $purchasing, Picture $picture)
    {
        $this->media->deletePicture($purchasing, $picture);
        return back()->with('success', __('messages.screenshot_deleted'));
    }
}
