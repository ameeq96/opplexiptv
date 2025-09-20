@extends('layouts.default')
@section('title', __('messages.redirect.title'))

@section('content')
    <div class="section text-center p-5" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <h2>{{ __('messages.redirect.preparing') }}</h2>
        <p id="statusText" class="mb-3">{{ __('messages.redirect.ad_loading') }}</p>

        <div class="mt-2 mb-2">
            <button id="clickToDownload" class="btn btn-primary btn-lg">
                {{ __('messages.redirect.click_to_download') }}
            </button>
        </div>

        {{-- (optional) Ad network widget --}}
        <script async data-cfasync="false" src="//handhighlight.com/cbb33e2ef96d697fc1deef53ebb64e5b/invoke.js"></script>
        <div id="container-cbb33e2ef96d697fc1deef53ebb64e5b"></div>

        <noscript>
            <p class="mt-3">
                {{ __('messages.redirect.noscript') }}
                <a class="btn btn-outline-primary mt-2" href="{{ $target }}">{{ __('messages.redirect.open_direct') }}</a>
            </p>
        </noscript>
    </div>

    <script>
      (function () {
        'use strict';

        const TARGET = @json($target ?? '');
        const AD_URL = @json($adUrl ?? '');

        const btn = document.getElementById('clickToDownload');
        const statusText = document.getElementById('statusText');

        function openInNewTab(url) {
          try {
            const w = window.open(url, '_blank', 'noopener');
            if (w && !w.closed) return true;
          } catch (e) {}
          try {
            const a = document.createElement('a');
            a.href = url;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            a.remove();
            return true;
          } catch (e) { return false; }
        }

        btn.addEventListener('click', function () {
          if (!TARGET) return;

          btn.disabled = true;

          let seconds = 3;
          statusText.textContent = 'Please wait ' + seconds + ' seconds...';

          const timer = setInterval(() => {
            seconds--;
            if (seconds >= 0) {
              statusText.textContent = 'Please wait ' + seconds + ' seconds...';
            }
            if (seconds < 0) {
              clearInterval(timer);

              if (AD_URL) openInNewTab(AD_URL);

              statusText.textContent = '{{ __('messages.redirecting') }}';
              setTimeout(() => {
                window.location.assign(TARGET);
              }, 900);
            }
          }, 1000);
        });
      })();
    </script>
@endsection
