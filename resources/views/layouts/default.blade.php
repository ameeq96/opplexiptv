<!DOCTYPE html>
<html class="loading" lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'ur']) ? 'rtl' : 'ltr' }}"
    data-textdirection="{{ in_array(app()->getLocale(), ['ar', 'ur']) ? 'rtl' : 'ltr' }}">

@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();
    $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
@endphp

<head>
    @include('includes.head')
</head>

<body>

    @include('includes.header')

    <div class="body_wrap">

        @yield('content')

    </div>

    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}" target="_blank"
        class="whatsapp-icon" title="Chat with us on WhatsApp">
        <img src="{{ asset('images/whatsapp.webp') }}" alt="WhatsApp" width="50" height="50" decoding="async" loading="lazy" />
    </a>

    @include('includes.footer')

    @yield('script')

    </div>
</body>

</html>
