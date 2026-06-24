{{--
    Reusable FAQ accordion section.

    Usage:
      - include 'includes._faq-section'                            uses route-specific $pageFaqs
      - include 'includes._faq-section' with ['faqItems' => ...]   pass a custom list
      - include 'includes._faq-section' with ['faqTitle' => ...]   pass a custom heading

    Each item: question (string), answer (html string), images (array, optional).
--}}

@php
    $faqItems = $faqItems ?? ($pageFaqs ?? []);
    $faqTitle = $faqTitle ?? __('messages.page_faq.section_title');
@endphp

@if (!empty($faqItems))
    @push('schema')
        {!! jsonld(seo()->faqPage($faqItems)) !!}
    @endpush
    <section class="faq-section"
        style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="auto-container">
            <div class="row clearfix">
                <div class="accordion-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                            <div class="separator"></div>
                            <h3 class="h3">{{ $faqTitle }}</h3>
                        </div>

                        <ul class="accordion-box">
                            @foreach ($faqItems as $faq)
                                <li class="accordion block {{ $loop->first ? 'active-block' : '' }}">
                                    <div class="acc-btn {{ $loop->first ? 'active' : '' }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" role="button"
                                        style="text-align: {{ $isRtl ? 'right' : 'left' }};">

                                        <div class="icon-outer"
                                            style="{{ $isRtl ? 'margin-left:10px;' : 'margin-right:10px;' }}">
                                            <span class="icon icon-plus fa fa-plus"></span>
                                            <span class="icon icon-minus fa fa-minus"></span>
                                        </div>
                                        <span class="{{ $isRtl ? 'mr-3' : '' }}">
                                            {{ $faq['question'] }}
                                        </span>
                                    </div>

                                    <div class="acc-content {{ $loop->first ? 'current' : '' }}">
                                        <div class="content" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                                            <div class="text">{!! $faq['answer'] !!}</div>

                                            @if (!empty($faq['images']))
                                                <div class="row d-flex justify-content-center align-items-center mt-3">
                                                    @foreach ($faq['images'] as $image)
                                                        <div class="col-lg-6 mt-2">
                                                            <figure>
                                                                <img src="{{ asset($image['url']) }}"
                                                                    alt="{{ $image['caption'] }}" loading="lazy"
                                                                    decoding="async" width="100%" />
                                                                <figcaption class="text-center mt-2">
                                                                    {{ $image['caption'] }}
                                                                </figcaption>
                                                            </figure>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
