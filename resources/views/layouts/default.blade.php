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
    <!-- Simple preloader overlay -->
    <div id="fx-preloader" class="fx-preloader" aria-live="polite" aria-label="Loading">
        <div class="fx-preloader__dot"></div>
        <div class="fx-preloader__dot"></div>
        <div class="fx-preloader__dot"></div>
    </div>

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
    <script>
      // Hide preloader once everything has loaded
      function hidePreloader(){
        document.documentElement.classList.remove('is-loading');
        var el = document.getElementById('fx-preloader');
        if(el){ el.style.opacity='0'; el.style.transition='opacity .25s ease'; setTimeout(function(){ el.parentNode && el.parentNode.removeChild(el); }, 260); }
      }
      window.addEventListener('load', function(){
        if (window.__fxScriptsReady) { hidePreloader(); }
        else {
          // Wait until dynamic scripts finish, but cap wait to 4s
          var t = setTimeout(hidePreloader, 4000);
          window.addEventListener('fx:ready', function(){ clearTimeout(t); hidePreloader(); }, { once: true });
        }
      });
    </script>
</body>

</html>
