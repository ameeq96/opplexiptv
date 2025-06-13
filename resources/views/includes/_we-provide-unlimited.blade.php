@php
    $features = [
        [
            'title' => '50k+ Movies (Hindi-English)',
            'description' =>
                "With over 50,000 movies in Hindi and English, our extensive library ensures there's something for everyone.",
        ],
        [
            'title' => '5k+ Web Series (Netflix, Prime Video)',
            'description' =>
                'Explore over 5,000 captivating web series from top streaming platforms like Netflix and Prime Video.',
        ],
        [
            'title' => '12k+ World Channels',
            'description' => 'Access over 12,000 channels worldwide, offering news, entertainment, and culture.',
        ],
        [
            'title' => 'HD and 4K Quality',
            'description' => 'Elevate your viewing experience with unparalleled HD and 4K quality.',
        ],
    ];
@endphp

@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();
@endphp


<section class="network-section {{ $agent->isMobile() ? 'p-0' : '' }}">
    <div class="auto-container">
        <div class="inner-container">
            <div class="row clearfix">
                @if (!$agent->isMobile())
                    <div class="images-column col-lg-7 col-md-12 col-sm-12">
                        <div class="inner-column">
                            <div class="image wow fadeInLeft" data-wow-delay="0ms" data-wow-duration="1500ms">
                                <img src="images/resource/network-4.webp" alt="" />
                            </div>
                            <div class="image-two wow fadeInRight" data-wow-delay="0ms" data-wow-duration="1500ms">
                                <img src="images/resource/network-5.webp" alt="" />
                            </div>
                            <div class="image-three titlt" data-tilt data-tilt-max="6">
                                <img src="images/resource/network-3.webp" alt="" />
                            </div>
                        </div>
                    </div>
                @endif
                <div class="content-column col-lg-5 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title">
                            <div class="separator"></div>
                            <h2>We Provide Unlimited</h2>
                        </div>

                        <ul class="network-list">
                            @foreach ($features as $feature)
                                <li>
                                    <span class="icon flaticon-tick-1"></span>
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
