<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Picture;
use Illuminate\Http\Request;

class PanelOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'pictures'])->where('type', 'reseller');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%$search%")
                       ->orWhere('email', 'like', "%$search%")
                       ->orWhere('phone', 'like', "%$search%");
                })
                ->orWhere('package', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%")
                ->orWhere('iptv_username', 'like', "%$search%")
                ->orWhere('note', 'like', "%$search%"); // NEW: search note
            });
        }

        $perPage = $request->get('per_page', 10);

        $orders = $query->orderBy('id', 'desc')->paginate($perPage);
        $orders->appends($request->all());

        return view('admin.reseller-orders.index', compact('orders'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('order_ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No orders selected.');
        }

        $orders = Order::whereIn('id', $ids)->where('type', 'reseller')->with('pictures')->get();
        foreach ($orders as $order) {
            foreach ($order->pictures as $pic) {
                $fullPath = public_path($pic->path);
                if (file_exists($fullPath)) @unlink($fullPath);
                $pic->delete();
            }
        }

        Order::whereIn('id', $ids)->where('type', 'reseller')->delete();

        return back()->with('success', count($ids) . ' reseller order(s) deleted successfully.');
    }

    public function create()
    {
        $clients = User::orderBy('name')->get();
        return view('admin.reseller-orders.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package' => 'required|string|max:255',
            'price' => 'required|numeric',
            'sell_price' => 'required|numeric|gte:price',
            'status' => 'required|in:pending,active,expired',
            'currency' => 'nullable|string|max:10',
            'payment_method' => 'nullable|string|max:255',
            'custom_payment_method' => 'nullable|string|max:255',
            'custom_package' => 'nullable|string|max:255',
            'buying_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'screenshots' => 'nullable|array',
            'screenshots.*' => 'image|max:5120',
            'iptv_username' => 'nullable|string|max:255',
            'credits' => 'nullable|integer',
            'duration' => 'required|integer|min:1',
            'note' => 'nullable|string|max:2000', // NEW
        ]);

        // Normalize "other"
        if (($validated['payment_method'] ?? null) === 'other' && $request->filled('custom_payment_method')) {
            $validated['payment_method'] = (string) $request->string('custom_payment_method');
        }
        if (($validated['package'] ?? null) === 'other' && $request->filled('custom_package')) {
            $validated['package'] = (string) $request->string('custom_package');
        }

        // ONLY orders columns
        $data = collect($validated)->only([
            'user_id',
            'package',
            'price',
            'sell_price',
            'status',
            'currency',
            'payment_method',
            'buying_date',
            'expiry_date',
            'iptv_username',
            'credits',
            'duration',
            'note', // NEW
        ])->all();

        $data['type'] = 'reseller';
        $data['profit'] = $data['sell_price'] - $data['price'];

        $order = Order::create($data);

        // Multiple images
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) continue;

                $original = $file->getClientOriginalName();
                $mime     = $file->getClientMimeType();
                $size     = $file->getSize();

                $filename = time() . '_' . uniqid() . '_' . $original;
                $file->move(public_path('screenshots'), $filename);

                $order->pictures()->create([
                    'path'          => 'screenshots/' . $filename,
                    'original_name' => $original,
                    'mime'          => $mime,
                    'size'          => $size,
                ]);
            }
        }

        return redirect()->route('panel-orders.index')->with('success', 'Reseller order created!');
    }

    public function edit(Order $panel_order)
    {
        $clients = User::orderBy('name')->get();
        $order = $panel_order;
        return view('admin.reseller-orders.edit', compact('order', 'clients'));
    }

    public function update(Request $request, Order $panel_order)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package' => 'required|string|max:255',
            'price' => 'required|numeric',
            'sell_price' => 'required|numeric|gte:price',
            'status' => 'required|in:pending,active,expired',
            'currency' => 'nullable|string|max:10',
            'payment_method' => 'nullable|string|max:255',
            'custom_payment_method' => 'nullable|string|max:255',
            'custom_package' => 'nullable|string|max:255',
            'buying_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'screenshots' => 'nullable|array',
            'screenshots.*' => 'image|max:5120',
            'iptv_username' => 'nullable|string|max:255',
            'credits' => 'nullable|integer',
            'duration' => 'required|integer|min:1',
            'note' => 'nullable|string|max:2000', // NEW
        ]);

        if (($validated['payment_method'] ?? null) === 'other' && $request->filled('custom_payment_method')) {
            $validated['payment_method'] = (string) $request->string('custom_payment_method');
        }
        if (($validated['package'] ?? null) === 'other' && $request->filled('custom_package')) {
            $validated['package'] = (string) $request->string('custom_package');
        }

        // ONLY orders columns
        $clean = collect($validated)->only([
            'user_id',
            'package',
            'price',
            'sell_price',
            'status',
            'currency',
            'payment_method',
            'buying_date',
            'expiry_date',
            'iptv_username',
            'credits',
            'duration',
            'note', // NEW
        ])->all();

        $clean['profit'] = $clean['sell_price'] - $clean['price'];

        $panel_order->update($clean);

        // Add new images
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) continue;

                $original = $file->getClientOriginalName();
                $mime     = $file->getClientMimeType();
                $size     = $file->getSize();

                $filename = time() . '_' . uniqid() . '_' . $original;
                $file->move(public_path('screenshots'), $filename);

                $panel_order->pictures()->create([
                    'path'          => 'screenshots/' . $filename,
                    'original_name' => $original,
                    'mime'          => $mime,
                    'size'          => $size,
                ]);
            }
        }

        return redirect()->route('panel-orders.index')->with('success', 'Reseller order updated!');
    }

    public function destroy(Order $panel_order)
    {
        $panel_order->load('pictures');
        foreach ($panel_order->pictures as $pic) {
            $fullPath = public_path($pic->path);
            if (file_exists($fullPath)) @unlink($fullPath);
            $pic->delete();
        }

        $panel_order->delete();

        return redirect()->route('panel-orders.index')
            ->with('success', 'Reseller order deleted!');
    }

    // Per-image delete (optional)
    public function destroyPicture(Order $order, Picture $picture)
    {
        if ($picture->imageable_id !== $order->id || $picture->imageable_type !== Order::class) {
            abort(404);
        }
        $fullPath = public_path($picture->path);
        if (file_exists($fullPath)) @unlink($fullPath);
        $picture->delete();

        return back()->with('success', 'Screenshot deleted.');
    }
}
