@extends('layouts.default')
@section('title', __('messages.buynow.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';

        $packages = [
            [
                'value' => 'monthly_USD_1.99',
                'label' => __('messages.buynow.packages.monthly'),
            ],
            [
                'value' => 'half_yearly_USD_6.99',
                'label' => __('messages.buynow.packages.half_yearly'),
            ],
            [
                'value' => 'yearly_USD_11.99',
                'label' => __('messages.buynow.packages.yearly'),
            ],
        ];
    @endphp

    <!-- Page Title -->
    <section class="page-title" style="background-image: url('{{ asset('images/background/10.webp') }}')">
        <div class="auto-container">
            <h2>{{ __('messages.buynow.heading') }}</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">{{ __('messages.nav.home') }}</a></li>
                <li>{{ __('messages.buynow.heading') }}</li>
            </ul>
        </div>
    </section>
    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="contact-page-section">
        <div class="auto-container">
            <div class="row clearfix">

                <div class="map-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">

                        <div class="contact-form-box">
                            <div class="form-title-box">
                                <h3>{{ __('messages.buynow.form_title') }}</h3>
                            </div>

                            <div class="contact-form">
                                <form method="POST" action="{{ route('buynow.send') }}" id="contact-form">
                                    @csrf
                                    <div class="row clearfix">

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="username"
                                                placeholder="{{ __('messages.form.name') }}" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email"
                                                placeholder="{{ __('messages.form.email') }}" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <select name="package" required>
                                                <option value="" disabled selected>
                                                    {{ __('messages.form.select_package') }}</option>
                                                @foreach ($packages as $pkg)
                                                    <option value="{{ $pkg['value'] }}">{{ $pkg['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="phone"
                                                placeholder="{{ __('messages.form.phone') }}" required>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="captcha"
                                                placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                                required>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <textarea class="darma" name="message" placeholder="{{ __('messages.form.message') }}" required></textarea>
                                        </div>

                                        @if (session('success'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-success">
                                                    {{ session('success') }}
                                                </div>
                                            </div>
                                        @endif

                                        @if (session('error'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-danger">
                                                    {{ session('error') }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group text-center">
                                            <button class="theme-btn btn-style-four" type="submit" name="submit-form">
                                                <span class="txt">{{ __('messages.form.submit') }}</span>
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@stop
