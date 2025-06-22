@extends('layouts.default')
@section('title', 'Contact Us | Opplex IPTV - Get in Touch for Support and Inquiries')
@section('content')

@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();
    $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
@endphp

    <!-- Page Title -->
    <section class="page-title" style="background-image: url(images/background/10.webp)">
        <div class="auto-container">
            <h2>Contact us</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">Home</a></li>
                <li>Contact us</li>
            </ul>
        </div>
    </section>
    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="contact-page-section">
        <div class="auto-container">
            <div class="row clearfix">

                <!-- Info Column -->
                <div class="info-column col-lg-4 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="title-box">
                            <h4>Contact Details</h4>
                        </div>
                        <div class="lower-box">
                            <ul class="info-list">
                                <li>
                                    <span class="icon flaticon-map"></span></br>
                                    Karachi, Pakistan
                                </li>
                                <li>
                                    <span class="icon flaticon-call"></span></br>
                                    <a href="tel:+923121108582">+923121108582</a>
                                </li>
                                <li>
                                    <span class="icon flaticon-email-1"></span>
                                    <a href="mailto:info@opplexiptv.com">info@opplexiptv.com</a><br>
                                    <a href="mailto:support@opplexiptv.com">support@opplexiptv.com</a><br>
                                </li>
                            </ul>
                            <div class="timing">Working hours 24/7</div>

                            <!-- Social Box -->
                            <ul class="social-box">
                                <li class="facebook"><a href="https://www.facebook.com/profile.php?id=61565476366548"
                                        class="fa fa-facebook-f" target="_blank"></a></li>
                                <li class="linkedin"><a href="https://www.linkedin.com/company/digitalize-store/"
                                        class="fa fa-linkedin" target="_blank"></a></li>
                                <li class="instagram"><a href="https://www.instagram.com/oplextv/" class="fa fa-instagram"
                                        target="_blank"></a></li>
                            </ul>

                        </div>
                    </div>
                </div>

                <!-- Map Column -->
                <div class="map-column col-lg-8 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <!-- Contact Form Box -->
                        <div class="contact-form-box">
                            <!-- Form Title Box -->
                            <div class="form-title-box">
                                <h3>Send a Message</h3>
                            </div>
                            <!-- Contact Form -->
                            <div class="contact-form">
                                <form method="POST" action="{{ route('contact.send') }}" id="contact-form">
                                    @csrf
                                    <div class="row clearfix">

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="username" placeholder="Name" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email" placeholder="Email Address" required>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="phone" placeholder="Phone" required>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="captcha" placeholder="What is {{ $num1 }} + {{ $num2 }}?" required>
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
