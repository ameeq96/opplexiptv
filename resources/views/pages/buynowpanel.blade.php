@extends('layouts.default')
@section('title', 'Reseller Panel | Opplex IPTV - Manage Your IPTV Business Easily')
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    @php
        $packages = [
            [
                'value' => '20_credits_USD_16.99',
                'label' => 'Starter Reseller Package 20 Credits - $16.99',
            ],
            [
                'value' => '50_credits_USD_40.99',
                'label' => 'Essential Reseller Bundle 50 Credits - $40.99',
            ],
            [
                'value' => '100_credits_USD_77.99',
                'label' => 'Pro Reseller Suite 100 Credits - $77.99',
            ],
            [
                'value' => '200_credits_USD_149.99',
                'label' => 'Advanced Reseller Toolkit 200 Credits - $149.99',
            ],
        ];
    @endphp
    <!-- Page Title -->
    <x-page-title :title="__('messages.reseller.heading')" :breadcrumbs="[['url' => '/', 'label' => 'Home'], ['label' => 'Buy Panel']]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="Reseller Buy Panel Page" />

    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="contact-page-section">
        <div class="auto-container">
            <div class="row clearfix">

                <!-- Map Column -->
                <div class="map-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <!-- Contact Form Box -->
                        <div class="contact-form-box">
                            <!-- Form Title Box -->
                            <div class="form-title-box">
                                <h3>Buy Panel Now</h3>
                            </div>
                            <!-- Contact Form -->
                            <div class="contact-form">
                                <form method="POST" action="{{ route('buynow.send') }}" id="contact-form">
                                    @csrf
                                    <div class="row clearfix">

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="username" placeholder="{{ __('messages.name') }}"
                                                required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email" placeholder="Email Address" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <select name="package" required>
                                                <option value="">{{ __('messages.select_panel') }}</option>
                                                @foreach ($packages as $package)
                                                    <option value="{{ $package['value'] }}">{{ $package['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="phone" placeholder="Phone" required>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="captcha"
                                                placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                                required>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <textarea class="darma" name="message" placeholder="Write Your Message..." required></textarea>
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
                                            <button class="theme-btn btn-style-four" type="submit" name="submit-form"><span
                                                    class="txt">Submit Now</span></button>
                                        </div>

                                    </div>
                                </form>
                            </div>

                        </div>
                        <!-- End Contact Form Box -->
                    </div>
                </div>

            </div>



        </div>
    </section>
    <!-- End Contact Page Section -->

@stop
