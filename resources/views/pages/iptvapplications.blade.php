@extends('layouts.default')
@section('title', __('messages.app.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <section class="page-title" style="background-image: url('{{ asset('images/background/10.webp') }}')">
        <div class="auto-container">
            <h2>{{ __('messages.app.heading') }}</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">{{ __('messages.app.breadcrumb.home') }}</a></li>
                <li>{{ __('messages.app.breadcrumb.current') }}</li>
            </ul>
        </div>
    </section>

    <div class="section sec-application text-center d-flex justify-content-center align-items-center mt-2"
        style="background-image: url('{{ asset('images/background/pattern-6.webp') }}')">
        <div class="container">
            <div class="call-to-action">
                <div class="box-icon"><span class="ti-mobile gradient-fill ti-3x"></span></div>
                <h2>{{ __('messages.app.download_heading') }}</h2>
                <p class="tagline">{{ __('messages.app.tagline') }}</p>

                @php
                    $platforms = [
                        'android' => [
                            [
                                'version' => __('messages.app.iptv_smarters_pro'),
                                'file' => 'iptv_smarter_pro.apk',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-pro IPTV-app-download IPTV-APK-Download iptv-Smarters',
                            ],
                            [
                                'version' => __('messages.app.iptv_smarters_pro_3151'),
                                'file' => 'IPTV Smarters Pro_version-3.1.5.1.apk',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-pro IPTV-app-download IPTV-APK-Download iptv-Smarters',
                            ],
                            [
                                'version' => __('messages.app.iptv_smarters_403'),
                                'file' => 'iptv-smarters-4.0.3.apk',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-android IPTV-smarters-android-download IPTV-smarters-mobile IPTV-smarters-LCD IPTV-APK-download IPTV-smarters-for-Android IPTV-smarters-for-TV latest-IPTV-APK IPTV-for-Android-TV iptv-samsung-apk',
                            ],
                            [
                                'version' => __('messages.app.iptv_smarters_pro_403_latest'),
                                'file' => 'iptv-smarters-pro-4-0-3.apk',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-android IPTV-smarters-android-download IPTV-smarters-mobile IPTV-smarters-LCD IPTV-APK-download IPTV-smarters-for-Android IPTV-smarters-for-TV latest-IPTV-APK IPTV-for-Android-TV iptv-samsung-apk',
                            ],
                            [
                                'version' => __('messages.app.opplex_app'),
                                'file' => 'OPPLEXTV3.0.apk',
                                'image' => 'opplextv.webp',
                                'keywords' =>
                                    'Opplex-TV-App Opplex-TV-APK-Download Opplex-IPTV-App Opplex-TV-for-Android Opplex-TV-Mobile-Streaming Android-IPTV-Opplex-App',
                            ],
                            [
                                'version' => __('messages.app.xtv_app'),
                                'file' => 'XTVPLAYER3.0.apk',
                                'image' => 'xtv.webp',
                                'keywords' =>
                                    'latest-XTV-live-iptv XTV-live-iptv-download XTV-live-iptv-APK XTV-IPTV-app-download XTV-live-APK-download XTV-live-streaming-app XTV-live-TV-APK free-XTV-iptv-APK XTV-iptv-pro-APK latest-XTV-IPTV-app',
                            ],
                        ],
                        'ios' => [
                            [
                                'version' => __('messages.app.smarters_player_ios'),
                                'file' => 'https://apps.apple.com/us/app/smarters-player-lite/id1628995509',
                                'image' => 'smarterlite.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-iOS IPTV-smarters-iOS-download IPTV-iOS-app IPTV-smarters-iOS-APK-download IPTV-smarters-iOS-app-download IPTV-smarters-for-iPhone IPTV-smarters-for-iPad',
                            ],
                            [
                                'version' => __('messages.app.player_000'),
                                'file' => 'https://apps.apple.com/app/000-player/id1665441224',
                                'image' => '000.webp',
                                'keywords' =>
                                    '000-Player-iOS-download 000-Player-App-Store IPTV-000-player-iPhone 000-player-iPad-app IPTV-iOS-streaming-app',
                            ],
                        ],
                        'windows' => [
                            [
                                'version' => __('messages.app.iptv_smarters_windows'),
                                'file' => 'iptv-smarters-pro-1-1-1.exe',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-windows IPTV-smarters-windows-download IPTV-smarters-PC IPTV-smarters-laptop IPTV-windows-app-download IPTV-smarters-for-PC IPTV-smarters-for-windows',
                            ],
                        ],
                    ];
                @endphp


                {{-- Platform download buttons --}}
                <div class="my-4">
                    @foreach ($platforms as $platform => $apps)
                        <div class="{{ $platform }} mb-5">
                            <h6 class="mb-2">{{ __('messages.app.platform', ['platform' => ucfirst($platform)]) }}</h6>
                            </h6>
                            @foreach ($apps as $app)
                                @php $isExternal = filter_var($app['file'], FILTER_VALIDATE_URL); @endphp
                                <a target="_blank" href="{{ $isExternal ? $app['file'] : asset('downloads/' . $app['file']) }}"
                                    class="btn btn-light" keywords="{{ $app['keywords'] }}"
                                    aria-label="Download {{ $app['version'] }} for {{ ucfirst($platform) }}"
                                    {{ $isExternal ? '' : 'download' }}>
                                    <img width="40px" height="40" loading="lazy"
                                        src="{{ asset('images/' . $app['image']) }}" alt="{{ $app['version'] }}">
                                    {{ __('messages.app.download_button', ['version' => $app['version']]) }}
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <p class="text-primary"><small><i>{{ __('messages.app.compatibility_note') }}</i></small></p>
            </div>
        </div>
    </div>

@stop

@section('jsonld')
    <script type="application/ld+json">
@php
    // Prepara los datos para el JSON-LD
    $itemList = [];
    $position = 1;
    foreach ($platforms as $platform => $apps) {
        foreach ($apps as $app) {
            $isExternal = filter_var($app['file'], FILTER_VALIDATE_URL);
            $downloadUrl = $isExternal ? $app['file'] : asset('downloads/' . $app['file']);
            
            $itemList[] = [
                "@type" => "SoftwareApplication",
                "name" => $app['version'],
                "operatingSystem" => ucfirst($platform),
                "applicationCategory" => "MultimediaApplication",
                "downloadUrl" => $downloadUrl,
                "keywords" => $app['keywords'],
                "offers" => [
                    "@type" => "Offer",
                    "price" => "0",
                    "priceCurrency" => "USD"
                ]
            ];
            $position++;
        }
    }
@endphp
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "Organization",
            "@id": "{{ url('/') }}#organization",
            "name": "{{ config('app.name', 'IPTV Service Provider') }}",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo.png') }}"
        },
        {
            "@type": "WebSite",
            "@id": "{{ url('/') }}#website",
            "url": "{{ url('/') }}",
            "name": "{{ config('app.name', 'IPTV Service Provider') }}",
            "publisher": {
                "@id": "{{ url('/') }}#organization"
            }
        },
        {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}#webpage",
            "url": "{{ url()->current() }}",
            "name": "{{ __('messages.app.title') }}",
            "isPartOf": {
                "@id": "{{ url('/') }}#website"
            },
            "breadcrumb": {
                "@id": "{{ url()->current() }}#breadcrumb"
            },
            "inLanguage": "{{ app()->getLocale() }}"
        },
        {
            "@type": "BreadcrumbList",
            "@id": "{{ url()->current() }}#breadcrumb",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "{{ __('messages.app.breadcrumb.home') }}",
                    "item": "{{ url('/') }}"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "{{ __('messages.app.breadcrumb.current') }}"
                }
            ]
        },
        @json($itemList)
    ]
}
</script>
@endsection
