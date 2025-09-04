@extends('layouts.default')
@section('title', __('messages.contact.title'))

@section('content')
    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <!-- Page Title -->
    <x-page-title :title="__('messages.contact.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.contact.breadcrumb.home')],
        ['label' => __('messages.contact.breadcrumb.current')],
    ]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="Contact Page" />

    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="contact-page-section" style="direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="auto-container">
            <div class="row clearfix {{ $isRtl ? 'rtl-row' : '' }}">

                <!-- Info Column -->
                <div class="info-column col-lg-4 col-md-12 col-sm-12" 
                     style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="inner-column">
                        <div class="title-box">
                            <h4>{{ __('messages.contact.details.title') }}</h4>
                        </div>
                     <div class="lower-box">
    <ul class="info-list {{ $isRtl ? 'text-end' : 'text-start' }}">
        <li class="d-flex align-items-start">
            <span class="icon flaticon-map me-2"></span>
            <span>{{ __('messages.contact.details.location') }}</span>
        </li>
        <li class="d-flex align-items-start">
            <span class="icon flaticon-call me-2"></span>
            <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_contact')) }}" target="_blank">
                {{ $isRtl ? '4913-093 (936) 1+' : __('messages.contact.details.phone') }}
            </a>
        </li>
        <li class="d-flex align-items-start">
            <span class="icon flaticon-email-1 me-2"></span>
            <a href="mailto:info@opplexiptv.com">{{ __('messages.contact.details.email1') }}</a>
        </li>
    </ul>

    <div class="timing {{ $isRtl ? 'text-end' : 'text-start' }}">
        {{ __('messages.contact.details.hours') }}
    </div>

    <!-- Social Box -->
    <ul class="social-box d-flex {{ $isRtl ? 'justify-content-end flex-row-reverse' : 'justify-content-start' }}">
        <li class="facebook">
            <a href="https://www.facebook.com/profile.php?id=61565476366548"
                class="fa fa-facebook-f" target="_blank"></a>
        </li>
        <li class="linkedin">
            <a href="https://www.linkedin.com/company/digitalize-store/" class="fa fa-linkedin"
                target="_blank"></a>
        </li>
        <li class="instagram">
            <a href="https://www.instagram.com/oplextv/" class="fa fa-instagram"
                target="_blank"></a>
        </li>
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
                            <div class="form-title-box" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                                <h3>{{ __('messages.contact.form.title') }}</h3>
                            </div>
                            <!-- Contact Form -->
                            <div class="contact-form">
                                <form method="POST" action="{{ route('contact.send') }}" id="contact-form" 
                                      style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                                    @csrf
                                    <div class="row clearfix">
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="username"
                                                class="{{ $isRtl ? 'text-end' : '' }}"
                                                placeholder="{{ __('messages.contact.form.name') }}" required>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email"
                                                class="{{ $isRtl ? 'text-end' : '' }}"
                                                placeholder="{{ __('messages.contact.form.email') }}" required>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="phone"
                                                class="{{ $isRtl ? 'text-end' : '' }}"
                                                placeholder="{{ __('messages.contact.form.phone') }}" required>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="captcha"
                                                class="{{ $isRtl ? 'text-end' : '' }}"
                                                placeholder="{{ __('messages.contact.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                                required>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <textarea class="darma {{ $isRtl ? 'text-end' : '' }}" 
                                                      name="message" 
                                                      placeholder="{{ __('messages.contact.form.message') }}" required></textarea>
                                        </div>

                                        @if (session('success'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-success text-center">
                                                    {{ session('success') }}
                                                </div>
                                            </div>
                                        @endif

                                        @if (session('error'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-danger text-center">
                                                    {{ session('error') }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group text-center">
                                            <button class="theme-btn btn-style-four" type="submit" name="submit-form">
                                                <span class="txt">{{ __('messages.contact.form.submit') }}</span>
                                            </button>
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
