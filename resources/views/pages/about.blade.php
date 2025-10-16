@extends('layouts.default')

@section('title', __('messages.about.title'))

@section('content')
    <x-page-title
        :title="__('messages.about.title_short')"
        :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.nav.home')],
            ['label' => __('messages.nav.about_us')],
        ]"
        background="images/background/7.webp"
        :rtl="$isRtl"
        aria-label="About Us Page"
    />

    @include('includes._we-provide-unlimited')
    @include('includes._check-trail')

    <section class="services-section-three"
             dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
             aria-labelledby="choose-us-title"
             style="background-image:url('{{ asset('images/background/pattern-6.webp') }}')">
        <div class="auto-container">
            <div @class(['sec-title','clearfix','section-header', $isRtl ? 'rtl' : 'ltr'])>
                <div class="pull-left">
                    <div class="separator"></div>
                    <h3 id="choose-us-title">{!! __('messages.choose_us.title') !!}</h3>
                </div>

                <div class="pull-right">
                    <a href="{{ route('packages') }}" class="theme-btn btn-style-four">
                        <span class="txt">
                            {{ __('messages.choose_us.button') }}
                            <i class="lnr lnr-arrow-right"
                               style="display:inline-block; transform: {{ $isRtl ? 'rotate(180deg)' : 'none' }};">
                            </i>
                        </span>
                    </a>
                </div>
            </div>

            <div class="row clearfix" role="list">
                @foreach ($features as $feature)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12"
                         role="listitem"
                         @style(['text-align: right' => $isRtl])>
                        <div class="inner-box" aria-label="{{ $feature['title'] }}">
                            <div class="pattern-layer"
                                 style="background-image:url('{{ asset('images/background/pattern-14.webp') }}')">
                            </div>

                            <div class="icon-box {{ $feature['icon'] }}"
                                 role="img"
                                 aria-label="{{ $feature['title'] }} icon"></div>

                            <h5>
                                <a href="{{ $feature['link'] }}"
                                   aria-label="{{ __('Read more about :title', ['title' => $feature['title']]) }}">
                                    {{ $feature['title'] }}
                                </a>
                            </h5>

                            <div class="text">{{ $feature['description'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('includes._testimonials')

    @unless($isMobile)
        @include('includes._channels-carousel')
    @endunless
@stop
