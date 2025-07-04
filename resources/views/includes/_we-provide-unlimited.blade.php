@php
    $features = [
        [
            'title' => __('messages.feature_50k_movies'),
            'description' => __('messages.feature_50k_movies_desc'),
        ],
        [
            'title' => __('messages.feature_5k_series'),
            'description' => __('messages.feature_5k_series_desc'),
        ],
        [
            'title' => __('messages.feature_12k_channels'),
            'description' => __('messages.feature_12k_channels_desc'),
        ],
        [
            'title' => __('messages.feature_hd_4k'),
            'description' => __('messages.feature_hd_4k_desc'),
        ],
    ];

    use Jenssegers\Agent\Agent;
    $agent = new Agent();
@endphp

<section class="network-section {{ $agent->isMobile() ? 'p-0' : '' }}" aria-label="Opplex IPTV Features Section">
    <div class="auto-container">
        <div class="inner-container">
            <div class="row clearfix">

                @if (!$agent->isMobile())
                    <div class="images-column col-lg-7 col-md-12 col-sm-12" aria-hidden="true">
                        <div class="inner-column">
                            <div class="image wow fadeInLeft" data-wow-delay="0ms" data-wow-duration="1500ms">
                                <img src="{{ asset('images/resource/network-4.webp') }}" alt="IPTV streaming setup image" />
                            </div>
                            <div class="image-two wow fadeInRight" data-wow-delay="0ms" data-wow-duration="1500ms">
                                <img src="{{ asset('images/resource/network-5.webp') }}" alt="IPTV content preview screen" />
                            </div>
                            <div class="image-three titlt" data-tilt data-tilt-max="6">
                                <img src="{{ asset('images/resource/network-3.webp') }}" alt="High-quality IPTV connection graphic" />
                            </div>
                        </div>
                    </div>
                @endif

                <div class="content-column col-lg-5 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title">
                            <div class="separator"></div>
                            <h2 aria-label="IPTV Network Features Heading">{{ __('messages.network_heading') }}</h2>
                        </div>

                        <ul class="network-list" aria-label="List of IPTV Features">
                            @foreach ($features as $feature)
                                <li aria-label="{{ $feature['title'] }}">
                                    <span class="icon flaticon-tick-1" aria-hidden="true"></span>
                                    <strong>{{ $feature['title'] }}</strong>
                                    {{ $feature['description'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
