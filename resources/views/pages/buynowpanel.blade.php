@extends('layouts.default')

@section('title', 'Reseller Panel | Opplex IPTV - Manage Your IPTV Business Easily')

@section('content')
    <!-- Page Title -->
    <x-page-title
        :title="__('messages.reseller.heading')"
        :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.nav.home')],
            ['label' => __('messages.buy_now_heading')],
        ]"
        background="images/background/10.webp"
        :rtl="$isRtl"
        aria-label="Reseller Buy Panel Page"
    />
    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="contact-page-section" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            <div class="row clearfix">
                <div class="map-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">

                        <div class="contact-form-box" aria-labelledby="reseller-form-title">
                            <div class="{{ $containerClass }}">
                                <h3 id="reseller-form-title">{{ __('messages.buy_now_heading', [], false) ?: 'Buy Panel Now' }}</h3>
                            </div>

                            <div class="contact-form">
                                <form method="POST" action="{{ route('buynow.send') }}" id="contact-form" novalidate>
                                    @csrf
                                    <div class="row clearfix">

                                        {{-- Name --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input
                                                type="text"
                                                name="username"
                                                value="{{ old('username') }}"
                                                placeholder="{{ __('messages.name') }}"
                                                required
                                                aria-invalid="@error('username') true @else false @enderror"
                                                aria-describedby="@error('username') username-error @enderror"
                                            >
                                            @error('username')
                                                <small id="username-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Email --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input
                                                type="email"
                                                name="email"
                                                value="{{ old('email') }}"
                                                placeholder="{{ __('messages.form.email') ?: 'Email Address' }}"
                                                required
                                                aria-invalid="@error('email') true @else false @enderror"
                                                aria-describedby="@error('email') email-error @enderror"
                                            >
                                            @error('email')
                                                <small id="email-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Package --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <select
                                                name="package"
                                                required
                                                aria-invalid="@error('package') true @else false @enderror"
                                                aria-describedby="@error('package') package-error @enderror"
                                            >
                                                <option value="" disabled @selected(old('package') === null)>
                                                    {{ __('messages.select_panel') }}
                                                </option>
                                                @foreach ($resellerPanelPackagesDropdown as $package)
                                                    <option value="{{ $package['value'] }}" @selected(old('package') === $package['value'])>
                                                        {{ $package['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('package')
                                                <small id="package-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Phone --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input
                                                type="text"
                                                name="phone"
                                                value="{{ old('phone') }}"
                                                placeholder="{{ __('messages.form.phone') ?: 'Phone' }}"
                                                required
                                                inputmode="tel"
                                                aria-invalid="@error('phone') true @else false @enderror"
                                                aria-describedby="@error('phone') phone-error @enderror"
                                            >
                                            @error('phone')
                                                <small id="phone-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Captcha --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input
                                                type="text"
                                                name="captcha"
                                                placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                                required
                                                aria-invalid="@error('captcha') true @else false @enderror"
                                                aria-describedby="@error('captcha') captcha-error @enderror"
                                            >
                                            @error('captcha')
                                                <small id="captcha-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Message --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <textarea
                                                class="darma"
                                                name="message"
                                                placeholder="{{ __('messages.form.message') ?: 'Write Your Message...' }}"
                                                required
                                                aria-invalid="@error('message') true @else false @enderror"
                                                aria-describedby="@error('message') message-error @enderror"
                                            >{{ old('message') }}</textarea>
                                            @error('message')
                                                <small id="message-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Flash messages --}}
                                        @if (session('success'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-success" role="status">
                                                    {{ session('success') }}
                                                </div>
                                            </div>
                                        @endif

                                        @if (session('error'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-danger" role="alert">
                                                    {{ session('error') }}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Submit --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group text-center">
                                            <button class="theme-btn btn-style-four" type="submit" name="submit-form">
                                                <span class="txt">{{ __('messages.form.submit') ?: 'Submit Now' }}</span>
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div><!-- contact-form -->
                        </div><!-- contact-form-box -->

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Contact Page Section -->
@stop
