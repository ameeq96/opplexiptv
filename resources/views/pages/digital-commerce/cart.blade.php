@extends('layouts.default')
@section('title', 'Digital Cart')
@push('styles')
<style>
    .dcart-wrap {
        background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(245,248,252,.94));
        border: 1px solid rgba(10, 20, 35, .08);
        border-radius: 22px;
        box-shadow: 0 14px 36px rgba(14, 24, 39, 0.10);
        padding: 20px;
    }
    .dcart-item {
        display: grid;
        grid-template-columns: 96px 1fr auto;
        gap: 14px;
        background: #fff;
        border: 1px solid #e8edf5;
        border-radius: 16px;
        padding: 12px;
        margin-bottom: 12px;
    }
    .dcart-thumb {
        width: 96px;
        height: 96px;
        border-radius: 12px;
        object-fit: cover;
        background: #eff3f9;
    }
    .dcart-title {
        font-size: 20px;
        line-height: 1.25;
        font-weight: 700;
        margin-bottom: 6px;
        color: #101828;
    }
    .dcart-meta {
        color: #667085;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .dcart-price {
        font-weight: 700;
        color: #0f172a;
    }
    .dcart-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }
    .dcart-summary {
        background: #fff;
        border: 1px solid #e8edf5;
        border-radius: 16px;
        padding: 16px;
        position: sticky;
        top: 95px;
    }
    .dcart-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #475467;
    }
    .dcart-summary-total {
        border-top: 1px dashed #d7dfec;
        padding-top: 12px;
        margin-top: 6px;
        font-size: 20px;
        font-weight: 800;
        color: #101828;
    }
    .dcart-checkout-btn {
        width: 100%;
        margin-top: 14px;
        border-radius: 12px;
        padding: 12px 14px;
        font-weight: 700;
        letter-spacing: .2px;
    }
    .dcart-empty {
        text-align: center;
        background: #fff;
        border: 1px dashed #cfd8e6;
        border-radius: 16px;
        padding: 36px 20px;
    }
    @media (max-width: 991px) {
        .dcart-item {
            grid-template-columns: 1fr;
        }
        .dcart-thumb {
            width: 100%;
            height: 180px;
        }
        .dcart-actions {
            align-items: stretch;
        }
        .dcart-summary {
            position: static;
            margin-top: 12px;
        }
    }
</style>
@endpush

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
