@extends('layouts.default')
@section('title', $product->title)

@section('content')
<section class="shop-section mt-5" style="background-image:url('{{ asset('images/background/4.webp') }}');">
    <div class="auto-container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <div class="bg-white rounded p-3">
                    @if($product->image)
                        <img src="{{ asset('images/digital-products/' . $product->image) }}" alt="{{ $product->title }}" style="width:100%;border-radius:10px;">
                    @endif
                </div>
            </div>
            <div class="col-lg-7">
                <div class="bg-white rounded p-4">
                    <h2>{{ $product->title }}</h2>
                    <div class="mb-2"><strong>Type:</strong> {{ ucfirst($product->delivery_type) }}</div>
                    <div class="mb-3"><strong>Price:</strong> ${{ number_format((float) $product->price, 2) }}</div>
                    <a href="https://wa.me/16393903194?text={{ rawurlencode('Hi, I want to buy ' . $product->title . ' ($' . number_format((float) $product->price, 2) . ').') }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-primary">Buy Now</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
