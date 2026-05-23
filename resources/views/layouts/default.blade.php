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
        <img src="{{ asset('images/whatsapp-img-small.webp') }}" alt="WhatsApp" width="60" height="60"
            decoding="async" />
    </a>

    @include('includes.footer')

    @yield('script')

    <script>
        (function () {
            var src = @json(v('js/voice-assistant.js'));
            var loaded = false;

            function loadVoiceAssistant() {
                if (loaded) return;
                loaded = true;
                var script = document.createElement('script');
                script.src = src;
                script.defer = true;
                document.body.appendChild(script);
            }

            if ('requestIdleCallback' in window) {
                requestIdleCallback(loadVoiceAssistant, { timeout: 2500 });
            } else {
                window.addEventListener('load', function () {
                    setTimeout(loadVoiceAssistant, 1200);
                }, { once: true });
            }
        })();
    </script>
</body>

</html>
