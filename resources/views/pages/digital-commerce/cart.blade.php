@extends('layouts.default')
@section('title', 'Digital Cart')

@section('content')
<section class="shop-section mt-5" style="background-image:url('{{ asset('images/background/4.webp') }}');">
    <div class="auto-container">
        <div class="dcart-wrap">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h3 class="mb-0">Your Cart</h3>
                <a href="{{ route('shop', ['type' => 'digital']) }}" class="btn btn-outline-primary btn-sm">Continue Shopping</a>
            </div>

            @if(empty($items))
                <div class="dcart-empty">
                    <h5 class="mb-2">Your cart is empty</h5>
                    <p class="text-muted mb-3">Add some digital products to proceed.</p>
                    <a href="{{ route('shop', ['type' => 'digital']) }}" class="btn btn-primary">Browse Digital Products</a>
                </div>
            @else
                <div class="row g-4">
                    <div class="col-lg-8">
                        @foreach($items as $item)
                            <article class="dcart-item">
                                <img class="dcart-thumb"
                                     src="{{ $item['product']->image ? asset('images/digital-products/' . $item['product']->image) : asset('images/background/10.webp') }}"
                                     alt="{{ $item['product']->title }}"
                                     loading="lazy">

                                <div>
                                    <h4 class="dcart-title">{{ $item['product']->title }}</h4>
                                    <div class="dcart-meta">{{ $item['product']->short_description ?: 'Digital product' }}</div>
                                    <div class="dcart-price">
                                        {{ $item['product']->currency ?: 'USD' }} {{ number_format((float) $item['product']->price, 2) }}
                                    </div>
                                </div>

                                <div class="dcart-actions">
                                    <div class="fw-bold text-dark">
                                        {{ $item['product']->currency ?: 'USD' }} {{ number_format((float) $item['line_total'], 2) }}
                                    </div>
                                    <a href="https://wa.me/16393903194?text={{ rawurlencode('Hi, I want to buy ' . $item['product']->title . ' (' . ($item['product']->currency ?: '$') . number_format((float) $item['product']->price, 2) . ').') }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn btn-sm btn-primary">Buy Now</a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="col-lg-4">
                        <aside class="dcart-summary">
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="dcart-summary-row">
                                <span>Subtotal</span>
                                <strong>${{ number_format((float) $subtotal, 2) }}</strong>
                            </div>
                            <div class="dcart-summary-row">
                                <span>Discount</span>
                                <strong>${{ number_format((float) $discount, 2) }}</strong>
                            </div>
                            <div class="dcart-summary-row dcart-summary-total">
                                <span>Total</span>
                                <strong>${{ number_format((float) $total, 2) }}</strong>
                            </div>

                            <a href="https://wa.me/16393903194?text={{ rawurlencode('Hi, I want to place my digital products order.') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn btn-primary dcart-checkout-btn">Buy Now on WhatsApp</a>
                        </aside>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

