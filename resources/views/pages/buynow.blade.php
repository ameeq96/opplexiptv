@extends('layouts.default')

@section('title', __('messages.buynow.title'))

@section('content')
    <!-- Page Title -->
    <x-page-title :title="__('messages.buynow.heading')" :breadcrumbs="[['url' => '/', 'label' => __('messages.nav.home')], ['label' => __('messages.buynow.heading')]]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="Buy Now Page" />
    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="contact-page-section" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            <div class="row clearfix">
                <div class="map-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">

                        <div class="contact-form-box" aria-labelledby="buynow-form-title">
                            <div class="{{ $containerClass }}">
                                <h3 id="buynow-form-title">{{ __('messages.buynow.form_title') }}</h3>
                            </div>

                            <div class="contact-form">
                                <form method="POST" action="{{ route('buynow.send') }}" id="contact-form" novalidate>
                                    @csrf

                                    <div class="row clearfix">
                                        {{-- Name --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="username" value="{{ old('username') }}"
                                                placeholder="{{ __('messages.form.name') }}" required
                                                aria-invalid="@error('username') true @else false @enderror"
                                                aria-describedby="@error('username') username-error @enderror">
                                            @error('username')
                                                <small id="username-error"
                                                    class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Email --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email" value="{{ old('email') }}"
                                                placeholder="{{ __('messages.form.email') }}" required
                                                aria-invalid="@error('email') true @else false @enderror"
                                                aria-describedby="@error('email') email-error @enderror">
                                            @error('email')
                                                <small id="email-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Package --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <select name="package" required
                                                aria-invalid="@error('package') true @else false @enderror"
                                                aria-describedby="@error('package') package-error @enderror">
                                                <option value="" disabled @selected(old('package') === null)>
                                                    {{ __('messages.form.select_package') }}
                                                </option>
                                                @foreach ($packagesDropdown as $pkg)
                                                    <option value="{{ $pkg['value'] }}" @selected(old('package') === $pkg['value'])>
                                                        {{ $pkg['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('package')
                                                <small id="package-error"
                                                    class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Phone --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="phone" value="{{ old('phone') }}"
                                                placeholder="{{ __('messages.form.phone') }}" required inputmode="tel"
                                                aria-invalid="@error('phone') true @else false @enderror"
                                                aria-describedby="@error('phone') phone-error @enderror">
                                            @error('phone')
                                                <small id="phone-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Captcha --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="captcha"
                                                placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                                required aria-invalid="@error('captcha') true @else false @enderror"
                                                aria-describedby="@error('captcha') captcha-error @enderror">
                                            @error('captcha')
                                                <small id="captcha-error"
                                                    class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Message --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <textarea class="darma" name="message" placeholder="{{ __('messages.form.message') }}" required
                                                aria-invalid="@error('message') true @else false @enderror"
                                                aria-describedby="@error('message') message-error @enderror">{{ old('message') }}</textarea>
                                            @error('message')
                                                <small id="message-error"
                                                    class="text-danger d-block">{{ $message }}</small>
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
                                                <span class="txt">{{ __('messages.form.submit') }}</span>
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
@stop
