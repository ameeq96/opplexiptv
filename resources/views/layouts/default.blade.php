@php
    $layoutIsRtl = $isRtl ?? in_array(app()->getLocale(), ['ar', 'ur'], true);
@endphp
<!DOCTYPE html>
<html class="loading" lang="{{ app()->getLocale() }}"
    dir="{{ $layoutIsRtl ? 'rtl' : 'ltr' }}"
    data-textdirection="{{ $layoutIsRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('includes.head')
</head>

<body>

    @include('includes.header')

    <div class="body_wrap">
        @yield('content')
    </div>

    @include('includes.footer')

    @include('includes.cookie-consent')

    @include('includes.voice-assistant')

    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}" target="_blank"
        class="whatsapp-icon" title="{{ __('document_ui.footer.contact') }} — WhatsApp">
        <img src="{{ asset('images/whatsapp-img-small.webp') }}" alt="WhatsApp" width="60" height="60"
            decoding="async" />
    </a>

    @hasSection('native-shell')
        @yield('native-shell')
        @vite('resources/js/native-shell.js')
    @endif

    @yield('script')

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                var s = document.createElement('script');
                s.src = "{{ Vite::asset('resources/js/voice-assistant.js') }}";
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
