<!-- Start Choose US Section -->
<section class="faq-section"
    style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
    <div class="auto-container">
        <div class="row clearfix {{ $isRtl ? 'flex-row-reverse' : '' }}">

            <!-- Accordion Column -->
            <div class="accordion-column col-lg-5 col-md-12 col-sm-12">
                <div class="inner-column" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="sec-title">
                        <div class="separator"></div>
                        <h3>{{ __('messages.few_reasons') }}</h3>
                    </div>

                    <ul class="accordion-box">
                        @foreach ($features as $i => $feature)
                            @php
                                $idx = $i;
                                $activeIndex = $activeIndex ?? 0;
                                $isActive = $feature['active'] ?? $idx === $activeIndex;
                            @endphp

                            <li class="accordion block {{ $isActive ? 'active-block' : '' }}">
                                <div
                                    class="acc-btn {{ $isActive ? 'active' : '' }} d-flex {{ $isRtl ? 'flex-row-reverse text-end' : '' }}">
                                    <div class="icon-outer">
                                        <span class="icon icon-plus fa fa-plus"></span>
                                        <span class="icon icon-minus fa fa-minus"></span>
                                    </div>
                                    <span
                                        class="ms-2 {{ $isRtl ? 'text-right' : '' }}">{{ $feature['title'] ?? '' }}</span>
                                </div>
                                <div class="acc-content {{ $isActive ? 'current' : '' }}">
                                    <div class="content">
                                        <div class="text">{{ $feature['description'] ?? '' }}</div>
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
                        <img src="{{ asset('images/resource/faq.webp') }}" alt="FAQ Image" width="472"
                            height="683" loading="lazy" />
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
