<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Picture;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PanelOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'pictures'])
            ->where('type', 'reseller');

        // ✅ TYPE filter: reseller | package | all (default: reseller)
        $type = $request->query('type', 'reseller');
        if ($type === 'reseller') {
            $query->where('type', 'reseller');
        } elseif ($type === 'package') {
            $query->where('type', 'package');
        } elseif ($type === 'all') {
            // no type filter
        } else {
            // fallback to reseller if unknown
            $type = 'reseller';
            $query->where('type', 'reseller');
        }

        // ✅ TAB filter (default unmessaged; options: unmessaged, messaged, all)
        $tab = $request->query('tab', 'unmessaged');
        if ($tab === 'messaged') {
            $query->whereNotNull('messaged_at');
        } elseif ($tab === 'unmessaged') {
            $query->whereNull('messaged_at');
        } elseif ($tab === 'all') {
            // no messaged filter
        } else {
            $tab = 'all';
        }

        // 🔎 Search
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
                    ->orWhere('note', 'like', "%$search%");
            });
        }

        // 📅 Date filters
        if ($request->filled('date_filter')) {
            $today = Carbon::today();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('buying_date', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('buying_date', $today->copy()->subDay());
                    break;
                case '7days':
                    $query->whereBetween('buying_date', [now()->subDays(6), now()]);
                    break;
                case '30days':
                    $query->whereBetween('buying_date', [now()->subDays(29), now()]);
                    break;
                case '90days':
                    $query->whereBetween('buying_date', [now()->subDays(89), now()]);
                    break;
                case 'year':
                    $query->whereYear('buying_date', now()->year);
                    break;
            }
        }

        // Custom date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('buying_date', [$request->start_date, $request->end_date]);
        }

        // ⏳ Expiry filters
        if ($request->filled('expiry_status')) {
            $today = Carbon::today();
            if ($request->expiry_status === 'expired') {
                $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today);
            } elseif ($request->expiry_status === 'soon') {
                $query->whereNotNull('expiry_date')->whereBetween('expiry_date', [$today, $today->copy()->addDays(5)]);
            }
        }


        if ($request->filled('expiry_status')) {
            $query->orderByRaw("
            CASE 
                WHEN expiry_date IS NULL THEN 999999
                WHEN expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 0
                WHEN expiry_date >= NOW() THEN DATEDIFF(expiry_date, NOW())
                ELSE 999998
            END ASC
        ")
                ->orderBy('expiry_date', 'asc')   // tie-breaker among soon
                ->orderBy('created_at', 'desc');  // newest first within same day diff
        } else {
            // ✅ default: latest records on top
            $query->orderBy('created_at', 'desc'); // or ->latest('buying_date')
            // if you strictly want by id: ->orderBy('id', 'desc')
        }

        $perPage = $request->get('per_page', 10);
        $orders  = $query->paginate($perPage)->appends($request->all());

        // pass $type as well
        return view('admin.reseller-orders.index', compact('orders', 'tab', 'type'));
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
            'note' => 'nullable|string|max:2000',
        ]);

        if (($validated['payment_method'] ?? null) === 'other' && $request->filled('custom_payment_method')) {
            $validated['payment_method'] = (string) $request->string('custom_payment_method');
        }
        if (($validated['package'] ?? null) === 'other' && $request->filled('custom_package')) {
            $validated['package'] = (string) $request->string('custom_package');
        }

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
            'note',
        ])->all();

        $data['type'] = 'reseller';
        $data['profit'] = $data['sell_price'] - $data['price'];

        $order = Order::create($data);

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
            'note' => 'nullable|string|max:2000',
        ]);

        if (($validated['payment_method'] ?? null) === 'other' && $request->filled('custom_payment_method')) {
            $validated['payment_method'] = (string) $request->string('custom_payment_method');
        }
        if (($validated['package'] ?? null) === 'other' && $request->filled('custom_package')) {
            $validated['package'] = (string) $request->string('custom_package');
        }

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
            'note',
        ])->all();

        $clean['profit'] = $clean['sell_price'] - $clean['price'];

        $panel_order->update($clean);

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

    // NEW: bulkAction (mark_messaged / unmark_messaged / delete) for reseller
    public function bulkAction(Request $request)
    {
        $ids = $request->input('order_ids', []);
        $action = $request->string('action')->toString();

        if (empty($ids)) {
            return back()->with('error', 'No orders selected.');
        }

        // ✅ Reseller scope lock
        $base = Order::whereIn('id', $ids)->where('type', 'reseller');

        if ($action === 'mark_messaged') {
            $base->update(['messaged_at' => now(), 'messaged_by' => auth()->id()]);
            return back()->with('success', 'Selected reseller orders marked as messaged.');
        }

        if ($action === 'unmark_messaged') {
            $base->update(['messaged_at' => null, 'messaged_by' => null]);
            return back()->with('success', 'Selected reseller orders moved back to Unmessaged.');
        }

        if ($action === 'delete') {
            $orders = Order::whereIn('id', $ids)->where('type', 'reseller')->with('pictures')->get();
            foreach ($orders as $o) {
                foreach ($o->pictures as $pic) {
                    $fullPath = public_path($pic->path);
                    if (file_exists($fullPath)) @unlink($fullPath);
                    $pic->delete();
                }
            }
            $base->delete();
            return back()->with('success', count($ids) . ' reseller order(s) deleted successfully.');
        }

        return back()->with('error', 'No valid action provided.');
    }
}
