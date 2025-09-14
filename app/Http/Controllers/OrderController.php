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
        $query = Order::with(['user', 'pictures'])->where('type', 'package');
        $today = Carbon::today();

        if ($request->has('search') && $request->search != '') {
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

        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('buying_date', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('buying_date', $today->copy()->subDay());
                    break;
                case '7days':
                    $query->whereBetween('buying_date', [Carbon::now()->subDays(6), Carbon::now()]);
                    break;
                case '30days':
                    $query->whereBetween('buying_date', [Carbon::now()->subDays(29), Carbon::now()]);
                    break;
                case '90days':
                    $query->whereBetween('buying_date', [Carbon::now()->subDays(89), Carbon::now()]);
                    break;
                case 'year':
                    $query->whereYear('buying_date', Carbon::now()->year);
                    break;
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('buying_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('expiry_status')) {
            if ($request->expiry_status === 'expired') {
                $query->whereNotNull('expiry_date')
                      ->whereDate('expiry_date', '<', $today);
            } elseif ($request->expiry_status === 'soon') {
                $query->whereNotNull('expiry_date')
                      ->whereBetween('expiry_date', [$today, $today->copy()->addDays(5)]);
            }
        }

        $query->orderByRaw("
            CASE 
                WHEN expiry_date IS NULL THEN 999999
                WHEN expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 0
                WHEN expiry_date >= NOW() THEN DATEDIFF(expiry_date, NOW())
                ELSE 999998
            END ASC
        ");

        $perPage = $request->get('per_page', 10);
        $orders  = $query->paginate($perPage);
        $orders->appends($request->all());

        return view('admin.orders.index', compact('orders', 'today'));
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
            'note'                   => 'nullable|string|max:2000', // NEW
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
            'note', // NEW
        ]);

        if ($request->payment_method === 'other' && $request->filled('custom_payment_method')) {
            $data['payment_method'] = $request->string('custom_payment_method');
        }
        if ($request->package === 'other' && $request->filled('custom_package')) {
            $data['package'] = $request->string('custom_package');
        }

        $data['type'] = 'package';

        $order = Order::create($data);

        // UPLOAD MULTIPLE IMAGES (meta move se pehle)
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) {
                    return back()->withErrors(['screenshots' => 'One of the files failed to upload.']);
                }

                $originalName = $file->getClientOriginalName();
                $mime         = $file->getClientMimeType();
                $size         = $file->getSize();

                $filename = time() . '_' . uniqid() . '_' . $originalName;
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
            'note'                   => 'nullable|string|max:2000', // NEW
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
            'note', // NEW
        ]);

        if ($request->payment_method === 'other' && $request->filled('custom_payment_method')) {
            $data['payment_method'] = $request->string('custom_payment_method');
        }
        if ($request->package === 'other' && $request->filled('custom_package')) {
            $data['package'] = $request->string('custom_package');
        }

        $order->update($data);

        // add new images only (NO batch remove)
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (!$file->isValid()) continue;

                $originalName = $file->getClientOriginalName();
                $mime         = $file->getClientMimeType();
                $size         = $file->getSize();

                $filename = time() . '_' . uniqid() . '_' . $originalName;
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
}
