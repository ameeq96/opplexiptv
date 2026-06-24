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

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                var s = document.createElement('script');
                s.src = "{{ \Illuminate\Support\Facades\Vite::asset('resources/js/voice-assistant.js') }}";
                s.type = 'module';
                s.defer = true;
                document.body.appendChild(s);
            }, 4000);
        });
    </script>

    {{-- Per-page JSON-LD (breadcrumbs, FAQ, HowTo, Service, page-type nodes).
         Placed at end of <body> so @push('schema') from page top-level, components
         (x-page-title) and includes (_faq-section) are all captured. --}}
    @stack('schema')
</body>

</html>
