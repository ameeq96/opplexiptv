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

    @include('includes.voice-assistant')

    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}" target="_blank"
        class="whatsapp-icon" title="Chat with us on WhatsApp">
        <img src="{{ asset('images/whatsapp-img-small.webp') }}" alt="WhatsApp" />
    </a>

    @include('includes.footer')

    @yield('script')

    <script src="{{ v('js/voice-assistant.js') }}"></script>

    </div>
</body>

</html>
