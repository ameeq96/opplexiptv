@extends('layouts.default')
@section('title', __('messages.faq.title'))

@section('content')

    <!-- Page Title -->
    <x-page-title :title="__('messages.faq.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.faq.breadcrumb.home')],
        ['label' => __('messages.faq.breadcrumb.current')],
    ]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="FAQ Page" />


    <section class="faq-section"
        style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="auto-container">
            <div class="row clearfix">
                <div class="accordion-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                            <div class="separator"></div>
                            <h3>{{ __('messages.faq.section_title') }}</h3>
                        </div>

                        <ul class="accordion-box">
                            @foreach ($faqs as $faq)
                                <li class="accordion block {{ $loop->first ? 'active-block' : '' }}">
                                    <div class="acc-btn {{ $loop->first ? 'active' : '' }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" role="button"
                                        style="text-align: {{ $isRtl ? 'right' : 'left' }};">

                                        <div class="icon-outer"
                                            style="{{ $isRtl ? 'margin-left:10px;' : 'margin-right:10px;' }}">
                                            <span class="icon icon-plus fa fa-plus"></span>
                                            <span class="icon icon-minus fa fa-minus" ></span>
                                        </div>
                                        <span class=" {{$isRtl ? 'mr-3' : ''}}">
                                            {{ $faq['question'] }}
                                        </span>
                                    </div>

                                    <div class="acc-content {{ $loop->first ? 'current' : '' }}">
                                        <div class="content" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                                            <div class="text">{!! $faq['answer'] !!}</div>

                                            @if (isset($faq['images']))
                                                <div class="row d-flex justify-content-center align-items-center mt-3">
                                                    @foreach ($faq['images'] as $image)
                                                        <div class="col-lg-6 mt-2">
                                                            <figure>
                                                                <img src="{{ asset($image['url']) }}"
                                                                    alt="{{ $image['caption'] }}" loading="lazy"
                                                                    width="100%" />
                                                                <figcaption class="text-center mt-2">
                                                                    {{ $image['caption'] }}
                                                                </figcaption>
                                                            </figure>
                                                            <h4 class="text-center mt-3">{{ $image['caption'] }}</h4>
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
                </div>
            </div>
        </div>
    </section>


@stop
