@extends('layouts.default')
@section('title', __('messages.redirect.title'))

@section('content')
    <div class="section text-center p-5">
        <h2>{{ __('messages.redirect.preparing') }}</h2>
        <p id="statusText">{{ __('messages.redirect.ad_loading') }}</p>

        <div class="mt-2 mb-2">
            <button id="clickToDownload" class="btn btn-primary btn-lg">
                {{ __('messages.redirect.click_to_download') }}
            </button>
        </div>

        {{-- (optional) aapke ad network ke widgets --}}
        <script async data-cfasync="false" src="//handhighlight.com/cbb33e2ef96d697fc1deef53ebb64e5b/invoke.js"></script>
        <div id="container-cbb33e2ef96d697fc1deef53ebb64e5b"></div>
    </div>

    <script>
      // Safe JSON me Blade vars
      const TARGET = @json($target ?? '');
      const AD_URL = 'https://handhighlight.com/sgtebuerf8?key=6085cca57bba1090342bc3bcbd3ee779';

      const btn = document.getElementById('clickToDownload');
      const statusText = document.getElementById('statusText');

      // Sirf direct ad URL new tab me open karo (no about:blank)
      function openAdNewTabDirect() {
        try {
          const popup = window.open(AD_URL, '_blank', 'noopener');
          if (popup && !popup.closed) return true;
        } catch (e) {
          console.error('window.open failed', e);
        }

        // Fallback: synthetic anchor (Safari/iOS friendly)
        try {
          const a = document.createElement('a');
          a.href = AD_URL;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
          a.style.display = 'none';
          document.body.appendChild(a);
          a.click();
          a.remove();
          return true;
        } catch (e) {
          console.error('anchor fallback failed', e);
        }
        return false;
      }

      btn.addEventListener('click', function () {
        if (!TARGET) {
          console.error('TARGET download URL missing');
          return;
        }

        btn.disabled = true;

        let countdown = 3;
        const timer = setInterval(() => {
          statusText.innerText = 'Please wait ' + countdown + ' seconds...';
          countdown--;

          if (countdown < 0) {
            clearInterval(timer);

            // 1) Ad ko NEW TAB me open karo (direct)
            openAdNewTabDirect();

            // 2) Current tab: download/start redirect
            statusText.innerText = 'Redirecting to your download...';
            setTimeout(() => {
              window.location.href = TARGET; // or window.location.assign(TARGET);
            }, 1200);
          }
        }, 1000);
      });
    </script>
@endsection
