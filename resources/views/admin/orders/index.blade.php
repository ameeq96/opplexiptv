@extends('admin.layout.app')
@section('page_title', 'All Orders')

@section('content')
    <a href="{{ route('orders.create') }}" class="btn btn-dark mb-3">Create Order</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Client</th>
                <th>Package</th>
                <th>Price</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->user->name }}</td>
                    <td>{{ $order->package }}</td>
                    <td>${{ $order->price }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ $order->payment_method }}</td>
                    <td>
                        <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-primary">Edit</a>
                        @php
                            $phone = preg_replace('/[^0-9]/', '', $order->user->phone);
                            $message = urlencode(
                                'Hello ' .
                                    $order->user->name .
                                    ", your IPTV order for package '" .
                                    $order->package .
                                    "' is now " .
                                    strtoupper($order->status) .
                                    '.',
                            );
                            $waUrl = "https://wa.me/{$phone}?text={$message}";
                        @endphp

                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-success">WhatsApp</a>
                        <form action="{{ route('orders.destroy', $order) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Delete this order?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
