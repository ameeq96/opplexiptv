<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');
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
                    ->whereBetween('expiry_date', [$today, $today->copy()->addDays(3)]);
            }
        }

        $query->orderByRaw("
        CASE 
            WHEN expiry_date IS NULL THEN 999999
            WHEN expiry_date < NOW() THEN 0
            ELSE DATEDIFF(expiry_date, NOW())
        END ASC
    ");

        $perPage = $request->get('per_page', 10);
        $orders = $query->paginate($perPage);
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
            'user_id' => 'required|exists:users,id',
            'package' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|integer',
            'status' => 'required|in:pending,active,expired',
            'payment_method' => 'nullable',
            'custom_payment_method' => 'nullable',
            'expiry_date' => 'nullable|date',
            'buying_date' => 'required|date',
            'screenshot' => 'nullable|image',
            'currency' => 'required|in:PKR,USD,AED,EUR,GBP,SAR,INR,CAD',
        ]);


        $data = $request->all();

        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('screenshots'), $filename);
            $data['screenshot'] = 'screenshots/' . $filename;
        }

        if (($data['payment_method'] ?? null) === 'other') {
            $data['payment_method'] = $data['custom_payment_method'] ?? null;
        }

        if (($data['package'] ?? null) === 'other') {
            $data['package'] = $data['custom_package'] ?? null;
        }

        Order::create($data);
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
            'user_id'   => 'required|exists:users,id',
            'package'   => 'required',
            'price'     => 'required|numeric',
            'duration'  => 'required|integer',
            'status'    => 'required|in:pending,active,expired',
            'payment_method' => 'nullable',
            'currency'  => 'required|in:PKR,USD,AED,EUR,GBP,SAR,INR,CAD',
            'buying_date' => 'required|date',
            'screenshot' => 'nullable|image',
        ]);

        $data = $request->except('screenshot');

        if ($request->hasFile('screenshot')) {
            $oldPath = public_path($order->screenshot);
            if ($order->screenshot && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $file = $request->file('screenshot');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('screenshots'), $filename);
            $data['screenshot'] = 'screenshots/' . $filename;
        }


        $order->update($data);

        return redirect()->route('orders.index')
            ->with('success', 'Order updated.');
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
