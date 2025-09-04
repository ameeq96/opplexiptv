@extends('layouts.default')
@section('title', __('messages.faq.title'))

@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';

        $faqs = [
            [
                'question' => __('messages.faq.q1'),
                'answer' => __('messages.faq.a1'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q2'),
                'answer' => __('messages.faq.a2'),
                'images' => [
                    ['url' => 'images/resource/samsung-tv-2.webp', 'caption' => __('messages.faq.samsung')],
                    ['url' => 'images/resource/mobileimg1.webp', 'caption' => __('messages.faq.mobile')],
                    ['url' => 'images/resource/onmobile.webp', 'caption' => __('messages.faq.front')],
                    ['url' => 'images/resource/onmobile2.webp', 'caption' => __('messages.faq.movies')],
                    ['url' => 'images/resource/onmobile3.webp', 'caption' => __('messages.faq.live')],
                    ['url' => 'images/resource/onmobile4.webp', 'caption' => __('messages.faq.play')],
                    ['url' => 'images/resource/onmobile5.webp', 'caption' => __('messages.faq.login_way')],
                    ['url' => 'images/resource/onmobile6.webp', 'caption' => __('messages.faq.login_page')],
                    ['url' => 'images/resource/onmobile7.webp', 'caption' => __('messages.faq.news')],
                    ['url' => 'images/resource/onmobile8.webp', 'caption' => __('messages.faq.settings')],
                    ['url' => 'images/resource/onmobile9.webp', 'caption' => __('messages.faq.series_play')],
                    ['url' => 'images/resource/onmobile10.webp', 'caption' => __('messages.faq.series_section')],
                    ['url' => 'images/resource/onmobile11.webp', 'caption' => __('messages.faq.on_screen')],
                    ['url' => 'images/resource/onmobile12.webp', 'caption' => __('messages.faq.series_playlist')],
                    ['url' => 'images/resource/alldevices.webp', 'caption' => __('messages.faq.devices')],
                ],
            ],
            [
                'question' => __('messages.faq.q3'),
                'answer' => __('messages.faq.a3'),
                'images' => [['url' => 'images/resource/loginguide.webp', 'caption' => __('messages.faq.login_guide')]],
            ],
            [
                'question' => __('messages.faq.q4'),
                'answer' => __('messages.faq.a4'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q5'),
                'answer' => __('messages.faq.a5'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q6'),
                'answer' => __('messages.faq.a6'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q7'),
                'answer' => __('messages.faq.a7'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q8'),
                'answer' => __('messages.faq.a8'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q9'),
                'answer' => __('messages.faq.a9'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q10'),
                'answer' => __('messages.faq.a10'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q11'),
                'answer' => __('messages.faq.a11'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q12'),
                'answer' => __('messages.faq.a12'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q13'),
                'answer' => __('messages.faq.a13'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q14'),
                'answer' => __('messages.faq.a14'),
                'images' => [],
            ],
            [
                'question' => __('messages.faq.q15'),
                'answer' => __('messages.faq.a15'),
                'images' => [],
            ],
        ];
    @endphp

    <!-- Page Title -->
    <x-page-title :title="__('messages.faq.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.faq.breadcrumb.home')],
        ['label' => __('messages.faq.breadcrumb.current')],
    ]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="FAQ Page" />


    <section class="faq-section"
        style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="auto-container">
            <div class="row clearfix">
                <div class="accordion-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                            <div class="separator"></div>
                            <h3>{{ __('messages.faq.section_title') }}</h3>
                        </div>

                        <ul class="accordion-box">
                            @foreach ($faqs as $faq)
                                <li class="accordion block {{ $loop->first ? 'active-block' : '' }}">
                                    <div class="acc-btn {{ $loop->first ? 'active' : '' }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" role="button"
                                        style="text-align: {{ $isRtl ? 'right' : 'left' }};">

                                        <div class="icon-outer"
                                            style="{{ $isRtl ? 'margin-left:10px;' : 'margin-right:10px;' }}">
                                            <span class="icon icon-plus fa fa-plus"></span>
                                            <span class="icon icon-minus fa fa-minus" ></span>
                                        </div>
                                        <span class=" {{$isRtl ? 'mr-3' : ''}}">
                                            {{ $faq['question'] }}
                                        </span>
                                    </div>

                                    <div class="acc-content {{ $loop->first ? 'current' : '' }}">
                                        <div class="content" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                                            <div class="text">{!! $faq['answer'] !!}</div>

                                            @if (isset($faq['images']))
                                                <div class="row d-flex justify-content-center align-items-center mt-3">
                                                    @foreach ($faq['images'] as $image)
                                                        <div class="col-lg-6 mt-2">
                                                            <figure>
                                                                <img src="{{ asset($image['url']) }}"
                                                                    alt="{{ $image['caption'] }}" loading="lazy"
                                                                    width="100%" />
                                                                <figcaption class="text-center mt-2">
                                                                    {{ $image['caption'] }}
                                                                </figcaption>
                                                            </figure>
                                                            <h4 class="text-center mt-3">{{ $image['caption'] }}</h4>
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


@stop
