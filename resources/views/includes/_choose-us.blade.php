@php
$features = [
    [
        'title' => 'HD Quality Streaming',
        'description' => 'Enjoy crystal-clear visuals and immersive audio for an unparalleled viewing experience.',
        'active' => false,
    ],
    [
        'title' => 'Flexible Packages',
        'description' => 'Choose from a variety of packages tailored to your preferences and budget.',
        'active' => true,
    ],
    [
        'title' => 'Reliable Service',
        'description' => 'Experience seamless streaming with minimal downtime.',
        'active' => false,
    ],
    [
        'title' => 'Easy Setup',
        'description' => 'Get started quickly and effortlessly with our user-friendly setup process. Choose us for top-notch entertainment at your fingertips!',
        'active' => false,
    ],
];
@endphp

<!-- Start Choose US Section -->
<section class="faq-section" style="background-image: url(images/background/4.webp)">
    <div class="auto-container">
        <div class="row clearfix">
            
            <!-- Accordion Column -->
            <div class="accordion-column col-lg-5 col-md-12 col-sm-12">
                <div class="inner-column">
                    <div class="sec-title">
                        <div class="separator"></div>
                        <h3>Few Great Reasons Make <br> You Choose us</h3>
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
                    <div class="pattern-layer" style="background-image: url(images/resource/faq-pattern.webp)"></div>
                    <div class="image titlt" data-tilt data-tilt-max="5">
                        <img src="images/resource/faq.webp" alt="" width="472px" height="683px" loading="lazy" />
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>