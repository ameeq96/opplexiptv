@extends('layouts.default')
@section('title', 'Frequently Asked Questions | Opplex IPTV - Your IPTV Queries Answered')
@section('content')

@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();
    $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
@endphp

    @php
        $faqs = [
            [
                'question' => 'What is Opplex IPTV',
                'answer' =>
                    'OPPLEXTV is an IPTV service that hosts over <strong>12,000 live channels, 50000+ MOVIES, 5000+ Latest TV Series</strong>. Along with several VOD options. The OPPLEXTV IPTV standard subscription plan costs PKR 350/month and includes international, sports, PPV, entertainment, news, and other channel categories. OPPLEXTV IPTV can be installed on any Android-powered / IOS devices, including the Amazon Firestick, Fire TV, Fire TV Cube, NVIDIA Shield, and more. OPPLEX IPTV is the best IPTV server in Pakistan.',
                'images' => [],
            ],
            [
                'question' => 'What does IPTV look like?',
                'answer' => 'Here are some screenshots of how IPTV Application Looks like on different devices.',
                'images' => [
                    ['url' => 'images/resource/samsung-tv-2.webp', 'caption' => 'On Samsung TV'],
                    ['url' => 'images/resource/mobileimg1.webp', 'caption' => 'On Mobile Phone'],
                    ['url' => 'images/resource/onmobile.webp', 'caption' => 'Front Page Interface'],
                    ['url' => 'images/resource/onmobile2.webp', 'caption' => 'Movies Section Interface'],
                    ['url' => 'images/resource/onmobile3.webp', 'caption' => 'Live Channels Section Interface'],
                    ['url' => 'images/resource/onmobile4.webp', 'caption' => 'Movie Play Section Interface'],
                    ['url' => 'images/resource/onmobile5.webp', 'caption' => 'Choose login way Interface'],
                    ['url' => 'images/resource/onmobile6.webp', 'caption' => 'Login Page Interface'],
                    ['url' => 'images/resource/onmobile7.webp', 'caption' => 'Live News Section Interface'],
                    ['url' => 'images/resource/onmobile8.webp', 'caption' => 'Application Settings Section Interface'],
                    ['url' => 'images/resource/onmobile9.webp', 'caption' => 'Playing Series Interface'],
                    ['url' => 'images/resource/onmobile10.webp', 'caption' => 'Series Section Interface'],
                    ['url' => 'images/resource/onmobile11.webp', 'caption' => 'On Screen Settings Interface'],
                    ['url' => 'images/resource/onmobile12.webp', 'caption' => 'Series Playlist Section Interface'],
                    ['url' => 'images/resource/alldevices.webp', 'caption' => 'Some Main Devices'],
                ],
            ],
            [
                'question' => 'How to Login Opplex IPTV with Codes',
                'answer' =>
                    'When a customer purchases an Opplex IPTV subscription, they receive login codes. The customer needs to enter these codes, along with the provided URL, into the app in the specified fields. Refer to the picture below for better understanding.',
                'images' => [['url' => 'images/resource/loginguide.webp', 'caption' => 'Login Guide']],
            ],
            [
                'question' => 'Does Opplex IPTV have a Trial?',
                'answer' =>
                    'Yes, If you want to try Opplex IPTV before purchasing, a trial is available for only PKR 50/=. ',
                'images' => [],
            ],
            [
                'question' => 'Sometimes when I watch a movie it keeps buffering?',
                'answer' =>
                    'Sometimes you may experience buffering due to low internet speed, high internet traffic. If you experience buffering, press "pause" for 10-15 seconds and resume; most of the time that will eliminate the need to buffer.',
                'images' => [],
            ],
            [
                'question' => 'What are the advantages of IPTV?',
                'answer' =>
                    'Generally IPTV or Internet Protocol Television has several advantages. It offers a prospective and very cost-effective option in the present market viewed by countless telecommunications providers and is thinking for positive as well as profitable new services that can generate new income streams.',
                'images' => [],
            ],
            [
                'question' => 'Does Opplex IPTV work on My Laptop/PC?',
                'answer' => 'Yes, Opplex IPTV will work on Laptops and Personal Computers.',
                'images' => [],
            ],
            [
                'question' => 'How many devices can I use with one subscription?',
                'answer' =>
                    'Fortunately, you can use two devices with one subscription. This is great news because now you can easily enjoy your favorite content on two different devices!',
                'images' => [],
            ],
            [
                'question' => 'How can I Download The Application?',
                'answer' =>
                    'Here is the link Below To Download the Application for your suitable Devices, <br> <a href="https://www.opplexiptv.com/iptv-applications">Download Now</a>',
                'images' => [],
            ],
            [
                'question' => 'Is My Payment Secure?',
                'answer' =>
                    'Yes, your payment is secure. We use advanced encryption technology and comply with industry-standard security protocols to ensure that your payment information is protected. Your privacy and security are our top priorities.',
                'images' => [],
            ],
            [
                'question' =>
                    'Can I ask Opplex IPTV if my favorite movie, series, or channel is available on IPTV before purchasing a subscription?',
                'answer' =>
                    'Yes, you can ask Opplex IPTV if your favorite movie, series, or channel is available on IPTV before purchasing a subscription.',
                'images' => [],
            ],
            [
                'question' => 'Can I Become a Reseller of Opplex IPTV?',
                'answer' => 'Yes, you can But After Buying Atleast 20 Credits.',
                'images' => [],
            ],
            [
                'question' => 'Can I Get a Discount On A Subscription?',
                'answer' =>
                    'Yes, Please Contact Via Email Or Whatsapp To Ask If The Discount Offer Is Available Or Not.',
                'images' => [],
            ],
            [
                'question' => 'Can I Hide The Adult Or Any Other Section On My IPTV Application?',
                'answer' =>
                    'Yes, You Can. Here is the Method Given Below For IPTV Smarters Pro Application:<br><br>1. Go to the main page.<br>2. Press the 3 dots.<br>3. Go To settings.<br>4. Go To parental control.<br>5. Set your password.<br>6. Save it.<br>7. Select category/channels to lock. Back at the main page.<br><br>Status: Your channel was Locked.<br><br>To see your locked channels:<br>1. Go to settings.<br>2. Enter Password.<br>3. Press Ok.<br>4. Unlock your channels.<br>5. Save It.<br><br>Status: Now see your channel again.',
                'images' => [],
            ],
        ];
    @endphp

    <!-- Page Title -->
    <section class="page-title" style="background-image: url(images/background/10.webp)">
        <div class="auto-container">
            <h2>FAQ's</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">Home</a></li>
                <li>FAQ's</li>
            </ul>
        </div>
    </section>
    <!-- End Page Title -->

    <section class="faq-section" style="background-image: url(images/background/4.webp)">
        <div class="auto-container">
            <div class="row clearfix">

                <!-- Accordion Column -->
                <div class="accordion-column col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title">
                            <div class="separator"></div>
                            <h3>Frequently Asked Questions (FAQ'S) </h3>
                        </div>

                        <!-- Accordian Box -->
                        <ul class="accordion-box">
                            @foreach ($faqs as $faq)
                                <li class="accordion block {{ $loop->first ? 'active-block' : '' }}">
                                    <div class="acc-btn {{ $loop->first ? 'active' : '' }}">
                                        <div class="icon-outer">
                                            <span class="icon icon-plus fa fa-plus"></span>
                                            <span class="icon icon-minus fa fa-minus"></span>
                                        </div>
                                        {{ $faq['question'] }}
                                    </div>
                                    <div class="acc-content {{ $loop->first ? 'current' : '' }}">
                                        <div class="content">
                                            <div class="text">
                                                {!! $faq['answer'] !!}
                                            </div>
                                            @if (isset($faq['images']))
                                                <div class="row d-flex justify-content-center align-items-center mt-3">
                                                    @foreach ($faq['images'] as $image)
                                                        <div class="col-lg-6 mt-2">
                                                            <img src="{{ $image['url'] }}" />
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
