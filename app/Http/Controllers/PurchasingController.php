<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Purchasing\{StorePurchasingRequest, UpdatePurchasingRequest};
use App\Models\{Purchasing, Picture};
use App\Services\Purchasing\{
    PurchasingQueryService,
    PurchasingCrudService,
    PurchasingMediaService
};
use Illuminate\Http\Request;

class PurchasingController extends Controller
{
    public function __construct(
        private PurchasingQueryService $query,
        private PurchasingCrudService $crud,
        private PurchasingMediaService $media,
    ) {}

    public function index(Request $request)
    {
        $builder = $this->query->base();
        $this->query->applySearch($builder, $request);
        $this->query->applySorting($builder);
        $purchases = $this->query->paginate($builder, $request);

        return view('admin.purchasing.index', compact('purchases'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('purchase_ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No purchases selected.');
        }

        $purchases = \App\Models\Purchasing::whereIn('id', $ids)->with('pictures')->get();
        $this->media->cleanupPictures($purchases);

        $count = $this->crud->bulkDelete($ids);
        return back()->with('success', "{$count} purchase(s) deleted successfully.");
    }

    public function create()
    {
        return view('admin.purchasing.create');
    }

    public function store(StorePurchasingRequest $request)
    {
        $purchase = $this->crud->create($request);

        if ($request->hasFile('screenshots')) {
            $this->media->storeScreenshots($purchase, $request->file('screenshots'));
        }

        return redirect()->route('admin.purchasing.index')->with('success', 'Purchase added successfully.');
    }

    public function edit(Purchasing $purchasing)
    {
        return view('admin.purchasing.edit', compact('purchasing'));
    }

    public function update(UpdatePurchasingRequest $request, Purchasing $purchasing)
    {
        $this->crud->update($request, $purchasing);

        if ($request->hasFile('screenshots')) {
            $this->media->storeScreenshots($purchasing, $request->file('screenshots'));
        }

        return redirect()->route('admin.purchasing.index')->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchasing $purchasing)
    {
        $purchasing->load('pictures');
        $this->media->cleanupPictures([$purchasing]);

        $this->crud->delete($purchasing);

        return redirect()->route('admin.purchasing.index')->with('success', 'Purchase deleted successfully.');
    }

    public function destroyPicture(Purchasing $purchasing, Picture $picture)
    {
        $this->media->deletePicture($purchasing, $picture);
        return back()->with('success', 'Screenshot deleted.');
    }
}
