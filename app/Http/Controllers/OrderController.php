<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order, Picture, User};
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'pictures'])
            ->where('type', 'package');
        $today = Carbon::today();

        // TYPE filter
        $type = $request->query('type', 'package');
        if ($type === 'reseller') {
            $query->where('type', 'reseller');
        } elseif ($type === 'all') {
            // no type filter
        } else {
            $type = 'package';
            $query->where('type', 'package');
        }

        // TAB filter
        $tab = $request->query('tab', 'unmessaged');
        if ($tab === 'messaged') {
            $query->whereNotNull('messaged_at');
        } elseif ($tab === 'unmessaged') {
            $query->whereNull('messaged_at');
        } else {
            $tab = 'all';
            // no messaged filter
        }

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%$search%");
                })
                    ->orWhere('package', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('iptv_username', 'like', "%$search%");
            });
        }

        // DATE FILTER
        if ($request->filled('date_filter')) {
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

        // CUSTOM DATE RANGE
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('buying_date', [$request->start_date, $request->end_date]);
        }

        // EXPIRY FILTERS
        if ($request->filled('expiry_status')) {
            if ($request->expiry_status === 'expired') {
                $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today);
            } elseif ($request->expiry_status === 'soon') {
                $query->whereNotNull('expiry_date')->whereBetween('expiry_date', [$today, $today->copy()->addDays(5)]);
            }
        }

      
        if ($request->filled('expiry_status')) {
            // bring soonest-to-expire up; expired at the bottom (but still visible via filter)
            $query->orderByRaw("
            CASE 
                WHEN expiry_date IS NULL THEN 999999
                WHEN expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 0
                WHEN expiry_date >= NOW() THEN DATEDIFF(expiry_date, NOW())
                ELSE 999998
            END ASC
        ");
            // tie-breakers
            $query->orderBy('expiry_date', 'asc')
                ->orderBy('created_at', 'desc');
        } else {
            // ✅ default: latest records on top
            $query->orderBy('created_at', 'desc');
            // (or use ->latest('buying_date') if you prefer buying_date)
        }

        $perPage = $request->get('per_page', 10);
        $orders  = $query->paginate($perPage)->appends($request->all());

        return view('admin.orders.index', compact('orders', 'today', 'tab', 'type'));
    }

    public function create()
    {
        $clients = User::orderBy('name')->get();
        return view('admin.orders.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'                => 'required|exists:users,id',
            'package'                => 'required',
            'price'                  => 'required|numeric',
            'duration'               => 'nullable|integer',
            'status'                 => 'required|in:pending,active,expired',
            'payment_method'         => 'nullable|string|max:255',
            'custom_payment_method'  => 'nullable|string|max:255',
            'expiry_date'            => 'nullable|date',
            'buying_date'            => 'required|date',
            'screenshots'            => 'nullable|array',
            'screenshots.*'          => 'image|max:5120',
            'currency'               => 'required|in:PKR,USD,AED,EUR,GBP,SAR,INR,CAD',
            'iptv_username'          => 'nullable|string|max:255',
            'custom_package'         => 'nullable|string|max:255',
            'note'                   => 'nullable|string|max:2000',
        ]);

        $data = $request->only([
            'user_id',
            'package',
            'price',
            'duration',
            'status',
            'payment_method',
            'currency',
            'buying_date',
            'expiry_date',
            'iptv_username',
            'note',
        ]);

        if ($request->payment_method === 'other' && $request->filled('custom_payment_method')) {
            $data['payment_method'] = $request->string('custom_payment_method');
        }
        if ($request->package === 'other' && $request->filled('custom_package')) {
            $data['package'] = $request->string('custom_package');
        }

        $data['type'] = 'package';

        $order = Order::create($data);

        // Upload multiple images
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) {
                    return back()->withErrors(['screenshots' => 'One of the files failed to upload.']);
                }
                $originalName = $file->getClientOriginalName();
                $mime         = $file->getClientMimeType();
                $size         = $file->getSize();
                $filename     = time() . '_' . uniqid() . '_' . $originalName;
                $file->move(public_path('screenshots'), $filename);

                $order->pictures()->create([
                    'path'          => 'screenshots/' . $filename,
                    'original_name' => $originalName,
                    'mime'          => $mime,
                    'size'          => $size,
                ]);
            }
        }

        return redirect()->route('orders.index')->with('success', 'Order added!');
    }

    public function edit(Order $order)
    {
        $clients = User::orderBy('name')->get();
        return view('admin.orders.edit', compact('order', 'clients'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'user_id'                => 'required|exists:users,id',
            'package'                => 'required',
            'price'                  => 'required|numeric',
            'duration'               => 'nullable|integer',
            'status'                 => 'required|in:pending,active,expired',
            'payment_method'         => 'nullable|string|max:255',
            'currency'               => 'required|in:PKR,USD,AED,EUR,GBP,SAR,INR,CAD',
            'buying_date'            => 'required|date',
            'expiry_date'            => 'nullable|date',
            'screenshots'            => 'nullable|array',
            'screenshots.*'          => 'image|max:5120',
            'custom_payment_method'  => 'nullable|string|max:255',
            'custom_package'         => 'nullable|string|max:255',
            'iptv_username'          => 'nullable|string|max:255',
            'note'                   => 'nullable|string|max:2000',
        ]);

        $data = $request->only([
            'user_id',
            'package',
            'price',
            'duration',
            'status',
            'payment_method',
            'currency',
            'buying_date',
            'expiry_date',
            'iptv_username',
            'note',
        ]);

        if ($request->payment_method === 'other' && $request->filled('custom_payment_method')) {
            $data['payment_method'] = $request->string('custom_payment_method');
        }
        if ($request->package === 'other' && $request->filled('custom_package')) {
            $data['package'] = $request->string('custom_package');
        }

        $order->update($data);

        // add new images only
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) continue;

                $originalName = $file->getClientOriginalName();
                $mime         = $file->getClientMimeType();
                $size         = $file->getSize();
                $filename     = time() . '_' . uniqid() . '_' . $originalName;
                $file->move(public_path('screenshots'), $filename);

                $order->pictures()->create([
                    'path'          => 'screenshots/' . $filename,
                    'original_name' => $originalName,
                    'mime'          => $mime,
                    'size'          => $size,
                ]);
            }
        }

        return redirect()->route('orders.index')->with('success', 'Order updated.');
    }

    public function destroyPicture(Order $order, Picture $picture)
    {
        if ($picture->imageable_id !== $order->id || $picture->imageable_type !== Order::class) {
            abort(404);
        }

        $fullPath = public_path($picture->path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $picture->delete();

        return back()->with('success', 'Screenshot deleted.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return back()->with('success', 'Order deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('order_ids', []);

        if (empty($ids)) {
            return back()->with('success', 'No orders selected.');
        }

        Order::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' order(s) deleted successfully.');
    }

    // NEW: generic bulk actions (mark/unmark/delete)
    public function bulkAction(Request $request)
    {
        $ids = $request->input('order_ids', []);
        $action = $request->string('action')->toString();

        if (empty($ids)) {
            return back()->with('success', 'No orders selected.');
        }

        if ($action === 'delete') {
            Order::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' order(s) deleted successfully.');
        }

        if ($action === 'mark_messaged') {
            Order::whereIn('id', $ids)->update([
                'messaged_at' => now(),
                'messaged_by' => auth()->id(),
            ]);
            return back()->with('success', 'Selected orders marked as messaged.');
        }

        if ($action === 'unmark_messaged') {
            Order::whereIn('id', $ids)->update([
                'messaged_at' => null,
                'messaged_by' => null,
            ]);
            return back()->with('success', 'Selected orders moved back to Unmessaged.');
        }

        return back()->with('success', 'No valid action provided.');
    }

    // (Optional) Single click mark (e.g., WhatsApp button)
    public function markOneMessaged(Order $order)
    {
        $order->update([
            'messaged_at' => now(),
            'messaged_by' => auth()->id(),
        ]);
        return response()->json(['ok' => true]);
    }
}
