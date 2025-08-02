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
    <section class="page-title" style="background-image: url('{{ asset('images/background/10.webp') }}')">
        <div class="auto-container">
            <h2>{{ __('messages.faq.heading') }}</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">{{ __('messages.faq.breadcrumb.home') }}</a></li>
                <li>{{ __('messages.faq.breadcrumb.current') }}</li>
            </ul>
        </div>
    </section>

    <section class="faq-section" style="background-image: url('{{ asset('images/background/4.webp') }}')">
        <div class="auto-container">
            <div class="row clearfix">
                <div class="accordion-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title">
                            <div class="separator"></div>
                            <h3>{{ __('messages.faq.section_title') }}</h3>
                        </div>
                        <ul class="accordion-box">
                            @foreach ($faqs as $faq)
                                <li class="accordion block {{ $loop->first ? 'active-block' : '' }}">
                                    <div class="acc-btn {{ $loop->first ? 'active' : '' }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" role="button">
                                        <div class="icon-outer">
                                            <span class="icon icon-plus fa fa-plus"></span>
                                            <span class="icon icon-minus fa fa-minus"></span>
                                        </div>
                                        {{ $faq['question'] }}
                                    </div>
                                    <div class="acc-content {{ $loop->first ? 'current' : '' }}">
                                        <div class="content">
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
                                                                    {{ $image['caption'] }}</figcaption>
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

@section('jsonld')
   <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [{
    "@type": "Question",
    "name": "What is Opplex IPTV ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "OPPLEXTV is an IPTV service that hosts over <strong>12,000 live channels, 50000+ MOVIES, 5000+ Latest TV Series</strong>. Along with several VOD options. The OPPLEXTV IPTV standard subscription plan costs $1.99/month and includes international, sports, PPV, entertainment, news, and other channel categories. OPPLEXTV IPTV can be installed on any Android-powered / IOS devices, including the Amazon Firestick, Fire TV, Fire TV Cube, NVIDIA Shield, and more. OPPLEX IPTV is the best IPTV server in Canada."
    }
  },{
    "@type": "Question",
    "name": "Does Opplex IPTV have a Trial ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Yes, a trial is available for only $1."
    }
  },{
    "@type": "Question",
    "name": "How to Login Opplex IPTV with Codes ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "When a customer purchases an Opplex IPTV subscription, they receive login codes. The customer needs to enter these codes, along with the provided URL, into the app in the specified fields. Refer to the picture below for better understanding."
    }
  },{
    "@type": "Question",
    "name": "Sometimes when I watch a movie it keeps buffering ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "This may happen due to low internet speed or high traffic. Pause for 10-15 seconds and then resume playback."
    }
  },{
    "@type": "Question",
    "name": "What are the advantages of IPTV ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "IPTV offers cost-effective and scalable TV service through internet protocol, allowing providers to offer innovative new services."
    }
  },{
    "@type": "Question",
    "name": "Does Opplex IPTV work on My Laptop/PC ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Yes, Opplex IPTV works on laptops and personal computers."
    }
  },{
    "@type": "Question",
    "name": "Can I Become a Reseller of Opplex IPTV?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Yes, after buying at least 20 credits."
    }
  },{
    "@type": "Question",
    "name": "Can I ask Opplex IPTV if my favorite content is available ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Yes, you can check availability before purchasing."
    }
  },{
    "@type": "Question",
    "name": "Can I Hide The Adult Or Any Other Section On My IPTV Application ?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Lock/unlock channels on IPTV Smarters Pro by going to Settings > Parental Control, setting a password, and selecting channels to lock or unlock."
    }
  }]
}
</script>
@endsection
