@php
    $features = [
        ['title' => __('messages.feature_hd'), 'description' => __('messages.feature_hd_desc'), 'active' => false],
        ['title' => __('messages.feature_flexible'), 'description' => __('messages.feature_flexible_desc'), 'active' => true],
        ['title' => __('messages.feature_reliable'), 'description' => __('messages.feature_reliable_desc'), 'active' => false],
        ['title' => __('messages.feature_easy'), 'description' => __('messages.feature_easy_desc'), 'active' => false],
        ['title' => __('messages.feature_multidevice'), 'description' => __('messages.feature_multidevice_desc'), 'active' => false],
        ['title' => __('messages.feature_support'), 'description' => __('messages.feature_support_desc'), 'active' => false],
        ['title' => __('messages.feature_sports_movies'), 'description' => __('messages.feature_sports_movies_desc'), 'active' => false],
    ];
@endphp

<!-- Start Choose US Section -->
<section class="faq-section" style="background-image: url('{{ asset('images/background/4.webp') }}')">
    <div class="auto-container">
        <div class="row clearfix">

            <!-- Accordion Column -->
            <div class="accordion-column col-lg-5 col-md-12 col-sm-12">
                <div class="inner-column">
                    <div class="sec-title">
                        <div class="separator"></div>
                        <h3>{{ __('messages.few_reasons') }}</h3>
                    </div>

                    <ul class="accordion-box">
                        @foreach ($features as $feature)
                            <li class="accordion block {{ $feature['active'] ? 'active-block' : '' }}">
                                <div class="acc-btn {{ $feature['active'] ? 'active' : '' }}">
                                    <div class="icon-outer">
                                        <span class="icon icon-plus fa fa-plus"></span>
                                        <span class="icon icon-minus fa fa-minus"></span>
                                    </div>
                                    {{ $feature['title'] }}
                                </div>
                                <div class="acc-content {{ $feature['active'] ? 'current' : '' }}">
                                    <div class="content">
                                        <div class="text">{{ $feature['description'] }}</div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Image Column -->
            <div class="image-column col-lg-7 col-md-12 col-sm-12">
                <div class="inner-column wow fadeInRight" data-wow-delay="0ms" data-wow-duration="1500ms">
                    <div class="pattern-layer"
                        style="background-image: url('{{ asset('images/resource/faq-pattern.webp') }}')"></div>
                    <div class="image titlt" data-tilt data-tilt-max="5">
                        <img src="images/resource/faq.webp" alt="" width="472px" height="683px"
                            loading="lazy" />
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
