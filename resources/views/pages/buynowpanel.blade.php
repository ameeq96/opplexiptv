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
                'value' => '20_credits_PKR_4399.00',
                'label' => 'Starter Reseller Package 20 Credits - PKR 4399.00',
            ],
            [
                'value' => '50_credits_PKR_10499.00',
                'label' => 'Essential Reseller Bundle 50 Credits - PKR 10499.00',
            ],
            [
                'value' => '100_credits_PKR_18999.00',
                'label' => 'Pro Reseller Suite 100 Credits - PKR 18999.00',
            ],
            [
                'value' => '200_credits_PKR_35999.00',
                'label' => 'Advanced Reseller Toolkit 200 Credits - PKR 35999.00',
            ],
        ];
    @endphp
    <!-- Page Title -->
    <section class="page-title" style="background-image: url('{{ asset('images/background/10.webp') }}')">
        <div class="auto-container">
            <h2>{{ __('messages.reseller.heading') }}</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">Home</a></li>
                <li>Buy Panel</li>
            </ul>
        </div>
    </section>
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
                                            <input type="text" name="username" placeholder="{{ __('form.name') }}"
                                                required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email" placeholder="Email Address" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <select name="package" required>
                                                <option value="">{{ __('form.select_panel') }}</option>
                                                @foreach ($packages as $package)
                                                    <option value="{{ $package['value'] }}">{{ $package['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="phone" placeholder="Phone" required>
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
