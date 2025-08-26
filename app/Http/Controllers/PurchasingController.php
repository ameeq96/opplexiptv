<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use Illuminate\Http\Request;

class PurchasingController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchasing::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%$search%")
                    ->orWhere('cost_price', 'like', "%$search%")
                    ->orWhere('currency', 'like', "%$search%")
                    ->orWhere('purchase_date', 'like', "%$search%");
            });
        }

        $perPage = $request->get('per_page', 10);

        $purchases = $query->orderBy('id', 'desc')->paginate($perPage);

        $purchases->appends($request->all());

        return view('admin.purchasing.index', compact('purchases'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('purchase_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No purchases selected.');
        }

        Purchasing::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' purchase(s) deleted successfully.');
    }

    public function create()
    {
        return view('admin.purchasing.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name'     => 'required|string|max:255',
            'cost_price'    => 'required|numeric',
            'currency'      => 'required|string|max:10',
            'quantity'      => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            'screenshot'    => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $data = $request->all();

        if ($request->hasFile('screenshot')) {
            $fileName = time() . '_' . $request->screenshot->getClientOriginalName();
            $filePath = $request->screenshot->storeAs('uploads/purchases', $fileName, 'public');
            $data['screenshot'] = 'storage/' . $filePath;
        }

        Purchasing::create($data);

        return redirect()->route('purchasing.index')->with('success', 'Purchase added successfully.');
    }

    public function edit(Purchasing $purchasing)
    {
        return view('admin.purchasing.edit', compact('purchasing'));
    }

    public function update(Request $request, Purchasing $purchasing)
    {
        $request->validate([
            'item_name'     => 'required|string|max:255',
            'cost_price'    => 'required|numeric',
            'currency'      => 'required|string|max:10',
            'quantity'      => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            'screenshot'    => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $data = $request->all();

        if ($request->hasFile('screenshot')) {
            $fileName = time() . '_' . $request->screenshot->getClientOriginalName();
            $filePath = $request->screenshot->storeAs('uploads/purchases', $fileName, 'public');
            $data['screenshot'] = 'storage/' . $filePath;
        }

        $purchasing->update($data);

        return redirect()->route('purchasing.index')->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchasing $purchasing)
    {
        $purchasing->delete();
        return redirect()->route('admin.purchasing.index')->with('success', 'Purchase deleted successfully.');
    }
}
