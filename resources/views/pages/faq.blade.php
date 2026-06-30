@extends('layouts.default')
@section('title', __('messages.faq.title'))

@push('schema')
    {!! jsonld(seo()->faqPage($faqs ?? [])) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/faq.css') }}?v={{ @filemtime(public_path('css/faq.css')) ?: 1 }}">
@endpush

@section('content')

    {{-- Page Title --}}
    <x-page-title :title="__('messages.faq.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.faq.breadcrumb.home')],
        ['label' => __('messages.faq.breadcrumb.current')],
    ]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="FAQ Page" />

    <section class="faqx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="faqx-title">
        <div class="auto-container">

            <div class="faqx__head">
                <span class="faqx__chip" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 2.5-3 4"/>
                        <path d="M12 17h.01"/>
                    </svg>
                </span>
                <h1 id="faqx-title" class="faqx__title">{{ __('messages.faq.section_title') }}</h1>
            </div>

            <ul class="accordion-box">
                @foreach ($faqs as $faq)
                    <li class="accordion block {{ $loop->first ? 'active-block' : '' }}">
                        <div class="acc-btn {{ $loop->first ? 'active' : '' }}"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}" role="button">
                            <div class="icon-outer">
                                <span class="icon icon-plus fa fa-plus" aria-hidden="true"></span>
                                <span class="icon icon-minus fa fa-minus" aria-hidden="true"></span>
                            </div>
                            <span>{{ $faq['question'] }}</span>
                        </div>

                        <div class="acc-content {{ $loop->first ? 'current' : '' }}">
                            <div class="content">
                                <div class="text">{!! $faq['answer'] !!}</div>

                                @if (isset($faq['images']))
                                    <div class="row d-flex justify-content-center align-items-center mt-3">
                                        @foreach ($faq['images'] as $image)
                                            <div class="col-lg-6 mt-2">
                                                <figure>
                                                    <img src="{{ asset($image['url']) }}"
                                                        alt="{{ $image['caption'] }}" loading="lazy"
                                                        decoding="async" width="100%" />
                                                    <figcaption class="text-center mt-2">{{ $image['caption'] }}</figcaption>
                                                </figure>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

        </div>
    </section>

@stop
