@extends('layouts.default')
@section('title', __('interface.redirect.title'))

@section('content')
    <div class="section text-center p-5" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <h2>{{ __('interface.redirect.preparing') }}</h2>
        <p id="statusText" class="mb-3">{{ __('interface.redirect.ad_loading') }}</p>

        <div class="mt-2 mb-2">
            <button id="clickToDownload" class="btn btn-primary btn-lg">
                {{ __('interface.redirect.click_to_download') }}
            </button>
        </div>

        <noscript>
            <p class="mt-3">
                {{ __('interface.redirect.noscript') }}
                <a class="btn btn-outline-primary mt-2" href="{{ $target }}">{{ __('interface.redirect.open_direct') }}</a>
            </p>
        </noscript>
    </div>

    <script>
      (function () {
        'use strict';

        const TARGET = @json($target ?? '');
        const WAIT_SECONDS = @json(__('interface.redirect.wait_seconds', ['seconds' => ':seconds']));
        const WAIT_ONE_SECOND = @json(__('interface.redirect.wait_one_second'));
        const WAIT_MOMENT = @json(__('interface.redirect.wait_moment'));
        const REDIRECTING = @json(__('interface.redirect.redirecting'));

        const btn = document.getElementById('clickToDownload');
        const statusText = document.getElementById('statusText');

        let started = false;

        function waitMessage(seconds) {
          if (seconds === 1) return WAIT_ONE_SECOND;
          if (seconds === 0) return WAIT_MOMENT;
          return WAIT_SECONDS.replace(':seconds', String(seconds));
        }

        function startDownloadFlow() {
          if (started || !TARGET) return;
          started = true;

          if (!TARGET) return;

          btn.disabled = true;

          let seconds = 3;
          statusText.textContent = waitMessage(seconds);

          const timer = setInterval(() => {
            seconds--;
            if (seconds >= 0) {
              statusText.textContent = waitMessage(seconds);
            }
            if (seconds < 0) {
              clearInterval(timer);

              statusText.textContent = REDIRECTING;
              setTimeout(() => {
                window.location.assign(TARGET);
              }, 900);
            }
          }, 1000);
        }

        btn.addEventListener('click', startDownloadFlow);

        // Auto-start so user does not need to click manually.
        setTimeout(startDownloadFlow, 500);
      })();
    </script>
@endsection
