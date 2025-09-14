<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use App\Models\Picture;
use Illuminate\Http\Request;

class PurchasingController extends Controller
{
    public function index(Request $request)
    {
        // Eager-load pictures if you will show thumbs on index (optional)
        $query = Purchasing::with('pictures');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%$search%")
                  ->orWhere('cost_price', 'like', "%$search%")
                  ->orWhere('currency', 'like', "%$search%")
                  ->orWhere('purchase_date', 'like', "%$search%")
                  ->orWhere('note', 'like', "%$search%"); // NEW: search in note
            });
        }

        $perPage   = $request->get('per_page', 10);
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

        // Clean up pictures and files for each purchase
        $purchases = Purchasing::whereIn('id', $ids)->with('pictures')->get();
        foreach ($purchases as $purchase) {
            foreach ($purchase->pictures as $pic) {
                $fullPath = public_path($pic->path);
                if (file_exists($fullPath)) @unlink($fullPath);
                $pic->delete();
            }
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
        $validated = $request->validate([
            'item_name'     => 'required|string|max:255',
            'cost_price'    => 'required|numeric',
            'currency'      => 'required|string|max:10',
            'quantity'      => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            // multiple screenshots
            'screenshots'   => 'nullable|array',
            'screenshots.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'note'          => 'nullable|string|max:2000', // NEW
        ]);

        // Only DB columns for purchases table
        $data = collect($validated)->only([
            'item_name', 'cost_price', 'currency', 'quantity', 'purchase_date', 'note' // NEW
        ])->all();

        $purchase = Purchasing::create($data);

        // Multiple images
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) { continue; }

                // meta BEFORE move to avoid tmp stat errors
                $original = $file->getClientOriginalName();
                $mime     = $file->getClientMimeType();
                $size     = $file->getSize();

                $filename = time() . '_' . uniqid() . '_' . $original;
                $file->move(public_path('uploads/purchases'), $filename);

                $purchase->pictures()->create([
                    'path'          => 'uploads/purchases/' . $filename,
                    'original_name' => $original,
                    'mime'          => $mime,
                    'size'          => $size,
                ]);
            }
        }

        return redirect()->route('purchasing.index')->with('success', 'Purchase added successfully.');
    }

    public function edit(Purchasing $purchasing)
    {
        return view('admin.purchasing.edit', compact('purchasing'));
    }

    public function update(Request $request, Purchasing $purchasing)
    {
        $validated = $request->validate([
            'item_name'     => 'required|string|max:255',
            'cost_price'    => 'required|numeric',
            'currency'      => 'required|string|max:10',
            'quantity'      => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            // multiple screenshots
            'screenshots'   => 'nullable|array',
            'screenshots.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'note'          => 'nullable|string|max:2000', // NEW
        ]);

        $data = collect($validated)->only([
            'item_name', 'cost_price', 'currency', 'quantity', 'purchase_date', 'note' // NEW
        ])->all();

        $purchasing->update($data);

        // Add new images (no batch remove here)
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) { continue; }

                $original = $file->getClientOriginalName();
                $mime     = $file->getClientMimeType();
                $size     = $file->getSize();

                $filename = time() . '_' . uniqid() . '_' . $original;
                $file->move(public_path('uploads/purchases'), $filename);

                $purchasing->pictures()->create([
                    'path'          => 'uploads/purchases/' . $filename,
                    'original_name' => $original,
                    'mime'          => $mime,
                    'size'          => $size,
                ]);
            }
        }

        return redirect()->route('purchasing.index')->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchasing $purchasing)
    {
        // remove associated pictures + files
        $purchasing->load('pictures');
        foreach ($purchasing->pictures as $pic) {
            $fullPath = public_path($pic->path);
            if (file_exists($fullPath)) @unlink($fullPath);
            $pic->delete();
        }

        $purchasing->delete();
        return redirect()->route('purchasing.index')->with('success', 'Purchase deleted successfully.');
    }

    // OPTIONAL: per-image delete route for purchases page
    public function destroyPicture(Purchasing $purchasing, Picture $picture)
    {
        if ($picture->imageable_id !== $purchasing->id || $picture->imageable_type !== Purchasing::class) {
            abort(404);
        }
        $fullPath = public_path($picture->path);
        if (file_exists($fullPath)) @unlink($fullPath);
        $picture->delete();

        return back()->with('success', 'Screenshot deleted.');
    }
}
