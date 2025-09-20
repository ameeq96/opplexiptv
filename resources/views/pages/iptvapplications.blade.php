@extends('layouts.default')
@section('title', __('messages.app.title'))

@section('content')
    <x-page-title
        :title="__('messages.app.heading')"
        :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.app.breadcrumb.home')],
            ['label' => __('messages.app.breadcrumb.current')],
        ]"
        background="images/background/10.webp"
        :rtl="$isRtl"
        aria-label="App Download Page"
    />

    <section class="section sec-application text-center d-flex justify-content-center align-items-center mt-2"
             style="background-image: url('{{ asset('images/background/pattern-6.webp') }}')">
        <div class="container">
            <div class="call-to-action">
                <div class="box-icon"><span class="ti-mobile gradient-fill ti-3x"></span></div>
                <h2>{{ __('messages.app.download_heading') }}</h2>
                <p class="tagline">{{ __('messages.app.tagline') }}</p>

                {{-- Platform download buttons --}}
                <div class="my-4" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                    @foreach ($platforms as $platform => $apps)
                        <div class="{{ $platform }} mb-5">
                            <h6 class="mb-2">
                                {{ __('messages.app.platform', ['platform' => ucfirst($platform)]) }}
                            </h6>

                            @foreach ($apps as $app)
                                <a target="_blank"
                                   href="{{ $app['href'] }}"
                                   class="btn btn-light me-2 mb-2 d-inline-flex align-items-center"
                                   data-keywords="{{ $app['keywords'] }}"
                                   aria-label="Download {{ $app['version'] }} for {{ ucfirst($platform) }}">
                                    <img width="40" height="40" loading="lazy"
                                         class="me-2"
                                         src="{{ $app['image_url'] }}"
                                         alt="{{ $app['version'] }}">
                                    {{ __('messages.app.download_button', ['version' => $app['version']]) }}
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <p class="text-primary"><small><i>{{ __('messages.app.compatibility_note') }}</i></small></p>
            </div>
        </div>
    </section>
@endsection
