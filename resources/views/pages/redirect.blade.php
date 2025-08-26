@extends('layouts.default')
@section('title', __('messages.redirect.title'))

@section('content')
    <div class="section text-center p-5">
        <h2>{{ __('messages.redirect.preparing') }}</h2>
        <p>{{ __('messages.redirect.ad_loading') }}</p>

        <div class="mt-2 mb-2">
            <button id="clickToDownload" class="btn btn-primary btn-lg">
                {{ __('messages.redirect.click_to_download') }}
            </button>
        </div>

        {{-- (Optional) Display Banner/Container Ads --}}
        <script async="async" data-cfasync="false"
            src="//pl27369752.profitableratecpm.com/cbb33e2ef96d697fc1deef53ebb64e5b/invoke.js"></script>
        <div id="container-cbb33e2ef96d697fc1deef53ebb64e5b"></div>

    </div>

    <script>
        document.getElementById('clickToDownload').addEventListener('click', function() {
            // 🔥 Inject OnClick Ads Script dynamically
            let s = document.createElement("script");
            s.dataset.zone = "9778944";
            s.src = "https://al5sm.com/tag.min.js";
            document.body.appendChild(s);

            // Open direct link in new tab
            window.open("https://handhighlight.com/sgtebuerf8?key=6085cca57bba1090342bc3bcbd3ee779", "_blank");

            // Redirect to target after 2 seconds
            setTimeout(function() {
                window.location.href = "{{ $target }}";
            }, 2000);
        });
    </script>
@endsection
