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

    <div id="app-loader" class="loader-overlay" aria-live="polite" aria-busy="true">
    <div class="spinner" role="status" aria-label="Loading"></div>
  </div>
  
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
      window.addEventListener('load', function(){
        document.documentElement.classList.remove('is-loading');
        var el = document.getElementById('fx-preloader');
        if(el){ el.style.opacity='0'; el.style.transition='opacity .25s ease'; setTimeout(function(){ el.parentNode && el.parentNode.removeChild(el); }, 260); }
      });

        document.documentElement.classList.remove('page-ready');

  // Mark page ready on window load (or when your main content is ready)
  window.addEventListener('load', function () {
    document.documentElement.classList.add('page-ready');
  });

    </script>
</body>

</html>
