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

        <script async="async" data-cfasync="false"
            src="//pl27369752.profitableratecpm.com/cbb33e2ef96d697fc1deef53ebb64e5b/invoke.js"></script>
        <div id="container-cbb33e2ef96d697fc1deef53ebb64e5b"></div>


    </div>

    <script>
        document.getElementById('clickToDownload').addEventListener('click', function() {
            window.open("https://handhighlight.com/sgtebuerf8?key=6085cca57bba1090342bc3bcbd3ee779", '_blank');

            setTimeout(function() {
                window.location.href = "{{ $target }}";
            }, 2000);
        });
    </script>
@endsection
