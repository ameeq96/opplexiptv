@extends('layouts.default')
@section('title', 'IPTV Applications | Opplex IPTV - Best Apps for Streaming IPTV Services')
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <section class="page-title" style="background-image: url(images/background/10.webp)">
        <div class="auto-container">
            <h2>IPTV Applications</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">Home</a></li>
                <li>IPTV Applications</li>
            </ul>
        </div>
    </section>

    <div class="section sec-application text-center d-flex justify-content-center align-items-center mt-2"
        style="background-image: url(images/background/pattern-6.webp)">
        <div class="container">
            <div class="call-to-action">
                <div class="box-icon"><span class="ti-mobile gradient-fill ti-3x"></span></div>
                <h2>Download IPTV</h2>
                <p class="tagline">Available for all major mobile and desktop platforms. Rapidiously visualize optimal ROI
                    rather than enterprise-wide methods of empowerment. </p>

                @php
                    $platforms = [
                        'android' => [
                            [
                                'version' => 'IPTV Smarter Pro APK',
                                'file' => 'iptv_smarter_pro.apk',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-pro IPTV-app-download IPTV-APK-Download iptv-Smarters',
                            ],
                            [
                                'version' => 'IPTV Smarter Pro APK 3.1.5.1',
                                'file' => 'IPTV Smarters Pro_version-3.1.5.1.apk',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-pro IPTV-app-download IPTV-APK-Download iptv-Smarters',
                            ],
                            [
                                'version' => 'IPTV Smarters APK 4.0.3',
                                'file' => 'iptv-smarters-4.0.3.apk',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-android IPTV-smarters-android-download IPTV-smarters-mobile IPTV-smarters-LCD IPTV-APK-download IPTV-smarters-for-Android IPTV-smarters-for-TV latest-IPTV-APK IPTV-for-Android-TV iptv-samsung-apk',
                            ],
                            [
                                'version' => 'XTV Live APK',
                                'file' => 'XTVPLAYER3.0.apk',
                                'image' => 'xtv.webp',
                                'keywords' =>
                                    'latest-XTV-live-iptv XTV-live-iptv-download XTV-live-iptv-APK XTV-IPTV-app-download XTV-live-APK-download XTV-live-streaming-app XTV-live-TV-APK free-XTV-iptv-APK XTV-iptv-pro-APK latest-XTV-IPTV-app',
                            ],
                        ],
                        'ios' => [
                            [
                                'version' => 'Smarters Player Lite IOS',
                                'file' => 'https://apps.apple.com/us/app/smarters-player-lite/id1628995509',
                                'image' => 'smarterlite.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-iOS IPTV-smarters-iOS-download IPTV-iOS-app IPTV-smarters-iOS-APK-download IPTV-smarters-iOS-app-download IPTV-smarters-for-iPhone IPTV-smarters-for-iPad',
                            ],
                        ],
                        'windows' => [
                            [
                                'version' => 'IPTV Smarter Pro For Windows',
                                'file' => 'iptv-smarters-pro-1-1-1.exe',
                                'image' => 'iptv_smarter.webp',
                                'keywords' =>
                                    'latest-IPTV-smarters-windows IPTV-smarters-windows-download IPTV-smarters-PC IPTV-smarters-laptop IPTV-windows-app-download IPTV-smarters-for-PC IPTV-smarters-for-windows',
                            ],
                        ],
                    ];
                @endphp

                <div class="my-4">
                    @foreach ($platforms as $platform => $apps)
                        <div class="{{ $platform }} mb-5">
                            <h6 class="mb-2">For {{ ucfirst($platform) }} Devices</h6>
                            @foreach ($apps as $app)
                                @php
                                    $isExternal = filter_var($app['file'], FILTER_VALIDATE_URL);
                                @endphp
                                <a target="_blank" href="{{ $isExternal ? $app['file'] : 'downloads/' . $app['file'] }}"
                                    class="btn btn-light" keywords="{{ $app['keywords'] }}"
                                    {{ $isExternal ? '' : 'download' }}>
                                    <img width="40px" src="images/{{ $app['image'] }}" alt="icon">
                                    Download {{ $app['version'] }}
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <p class="text-primary"><small><i>*Works on iOS 10.0.5+, Android Kitkat and above. </i></small></p>
            </div>
        </div>
    </div>

@stop
