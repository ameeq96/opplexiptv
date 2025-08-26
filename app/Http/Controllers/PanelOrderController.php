<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class PanelOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::where('type', 'reseller');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
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

        Order::whereIn('id', $ids)
            ->where('type', 'reseller')
            ->delete();

        return back()->with('success', count($ids) . ' reseller order(s) deleted successfully.');
    }

    public function create()
    {
        $clients = User::orderBy('name')->get();
        return view('admin.reseller-orders.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
            'screenshot' => 'nullable',
            'iptv_username' => 'nullable|string|max:255',
            'credits' => 'nullable|integer',
            'duration' => 'required|integer|min:1',
        ]);

        if ($request->hasFile('screenshot')) {
            $fileName = time() . '_' . $request->file('screenshot')->getClientOriginalName();
            $request->file('screenshot')->move(public_path('screenshots'), $fileName);
            $data['screenshot'] = 'screenshots/' . $fileName;
        }

        $data['type'] = 'reseller';
        $data['profit'] = $data['sell_price'] - $data['price'];

        Order::create($data);

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
        $data = $request->validate([
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
            'screenshot' => 'nullable',
            'iptv_username' => 'nullable|string|max:255',
            'credits' => 'nullable|integer',
            'duration' => 'required|integer|min:1',
        ]);

        if ($request->hasFile('screenshot')) {
            $fileName = time() . '_' . $request->file('screenshot')->getClientOriginalName();
            $request->file('screenshot')->move(public_path('screenshots'), $fileName);
            $data['screenshot'] = 'screenshots/' . $fileName;
        }


        $data['profit'] = $data['sell_price'] - $data['price'];

        $panel_order->update($data);

        return redirect()->route('panel-orders.index')->with('success', 'Reseller order updated!');
    }

    public function destroy(Order $panel_order)
    {
        $panel_order->delete();

        return redirect()->route('panel-orders.index')
            ->with('success', 'Reseller order deleted!');
    }
}
