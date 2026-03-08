@extends('layouts.default')
@section('title', 'Digital Checkout')

@section('content')
<section class="shop-section mt-5" style="background-image:url('{{ asset('images/background/4.webp') }}');">
    <div class="auto-container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="bg-white rounded p-4">
                    <h3 class="mb-3">Checkout</h3>
                    <form method="POST" action="{{ route('digital.checkout.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input class="form-control" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="card" @selected(old('payment_method')==='card')>Card</option>
                                <option value="crypto" @selected(old('payment_method')==='crypto')>Crypto</option>
                                <option value="manual" @selected(old('payment_method')==='manual')>Manual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" rows="3" name="notes">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Place Order</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bg-white rounded p-4">
                    <h5 class="mb-3">Order Summary</h5>
                    @foreach($items as $item)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $item['product']->title }} x {{ $item['quantity'] }}</span>
                            <span>${{ number_format((float) $item['line_total'], 2) }}</span>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${{ number_format((float) $subtotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Discount</span><strong>${{ number_format((float) $discount, 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Total</span><strong>${{ number_format((float) $total, 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
