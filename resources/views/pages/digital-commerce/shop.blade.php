@extends('layouts.default')
@section('title', 'Digital Shop')

@section('content')
<section class="shop-section mt-5" style="background-image:url('{{ asset('images/background/4.webp') }}');">
    <div class="auto-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-white mb-0">Digital Shop</h2>
            <span></span>
        </div>

        <div class="row g-4">
            @forelse($products as $product)
                <div class="col-lg-3 col-md-6">
                    <div class="p-3 bg-white rounded h-100">
                        <a href="{{ route('digital.product.show', $product->slug) }}">
                            @if($product->image)
                                <img src="{{ asset('images/digital-products/' . $product->image) }}" alt="{{ $product->title }}" style="width:100%;height:160px;object-fit:cover;border-radius:10px;">
                            @endif
                        </a>
                        <div class="mt-3">
                            <h5 class="mb-1"><a href="{{ route('digital.product.show', $product->slug) }}">{{ $product->title }}</a></h5>
                            <div class="text-muted small mb-2">{{ $product->category?->name ?? 'General' }}</div>
                            <div class="fw-bold mb-2">${{ number_format((float) $product->price, 2) }}</div>
                            <a href="https://wa.me/16393903194?text={{ rawurlencode('Hi, I want to buy ' . $product->title . ' ($' . number_format((float) $product->price, 2) . ').') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn btn-sm btn-primary w-100">Buy Now</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">No digital products available.</div></div>
            @endforelse
        </div>

        <div class="mt-4">{{ $products->links('pagination::bootstrap-5') }}</div>
    </div>
</section>
@endsection
