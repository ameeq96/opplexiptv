@extends('layouts.default')
@section('title', 'Shop')

@section('content')
    <x-page-title
        :title="'Shop'"
        :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.faq.breadcrumb.home') ],
            ['label' => 'Shop'],
        ]"
        background="images/background/10.webp"
        :rtl="$isRtl"
        aria-label="Shop Page"
    />

    <section class="shop-section mt-5" style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="auto-container">
            <div class="sec-title" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="separator"></div>
                <h1 class="h3">{{ __('messages.our_products') }}</h1>
            </div>

            @include('includes._product-grid', ['products' => $products, 'isRtl' => $isRtl])

            @if (method_exists($products, 'links'))
                <div class="mt-4 mb-4" style="display:flex; justify-content:center;">
                    @include('includes._pagination', ['paginator' => $products, 'isRtl' => $isRtl])
                </div>
            @endif
        </div>
    </section>
@endsection
