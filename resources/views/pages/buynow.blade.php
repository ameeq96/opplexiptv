@extends('layouts.default')
@section('title', 'Buy Now | Opplex IPTV - Affordable IPTV Packages, IPTV Smarters Pro, XTV Live & More')
@section('content')

    @php
        $packages = [
            [
                'value' => 'monthly_PKR_350.00',
                'label' => 'Monthly PKR 350.00',
            ],
            [
                'value' => 'half_yearly_PKR_1799.00',
                'label' => 'Half-Yearly PKR 1799.00',
            ],
            [
                'value' => 'yearly_PKR_3399.00',
                'label' => 'Yearly PKR 3399.00',
            ],
        ];
    @endphp

    <!-- Page Title -->
    <section class="page-title" style="background-image: url(images/background/10.webp)">
        <div class="auto-container">
            <h2>Buy Packages</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">Home</a></li>
                <li>Buy Packages</li>
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
                                <h3>Buy IPTV Now</h3>
                            </div>
                            <!-- Contact Form -->
                            <div class="contact-form">
                                <form method="POST" action="{{ route('buynow.send') }}" id="contact-form">
                                    @csrf
                                    <div class="row clearfix">

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="username" placeholder="Name" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email" placeholder="Email Address" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <select name="package" required>
                                                <option value="" disabled selected>Select Package</option>
                                                @foreach ($packages as $pkg)
                                                    <option value="{{ $pkg['value'] }}">{{ $pkg['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="phone" placeholder="Phone" required>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="captcha"
                                                placeholder="What is {{ $num1 }} + {{ $num2 }}?" required>
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
