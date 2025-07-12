<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order, User};
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%$search%");
                })
                    ->orWhere('package', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.orders.index', compact('orders'));
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
            $path = $file->store('screenshots', 'public');
            $data['screenshot'] = $path;
        }

        if (($data['payment_method'] ?? null) === 'other') {
            $data['payment_method'] = $data['custom_payment_method'] ?? null;
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
            // delete old file
            if ($order->screenshot && Storage::disk('public')->exists($order->screenshot)) {
                Storage::disk('public')->delete($order->screenshot);
            }
            // store new file
            $data['screenshot'] = $request->file('screenshot')
                ->store('screenshots', 'public');
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
