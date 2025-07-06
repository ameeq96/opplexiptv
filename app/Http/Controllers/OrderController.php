<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;

class OrderController extends Controller
{
    public function index() {
        $orders = Order::with('user')->latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function create() {
        $clients = User::orderBy('name')->get();
        return view('admin.orders.create', compact('clients'));
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|integer',
            'status' => 'required|in:pending,active,expired',
            'payment_method' => 'nullable',
        ]);

        Order::create($request->all());
        return redirect()->route('orders.index')->with('success', 'Order added!');
    }

    public function edit(Order $order) {
        $clients = User::orderBy('name')->get();
        return view('admin.orders.edit', compact('order', 'clients'));
    }

    public function update(Request $request, Order $order) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|integer',
            'status' => 'required|in:pending,active,expired',
            'payment_method' => 'nullable',
        ]);

        $order->update($request->all());
        return redirect()->route('orders.index')->with('success', 'Order updated.');
    }

    public function destroy(Order $order) {
        $order->delete();
        return back()->with('success', 'Order deleted.');
    }
}
