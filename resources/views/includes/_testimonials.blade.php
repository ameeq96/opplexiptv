@php

$testimonials = [
    [
        'text' => 'I’ve been really impressed with this IPTV service. The variety of channels is impressive, and the picture quality is fantastic. I did face some temporary glitches, but the support was outstanding and handled everything promptly. Highly recommend it!',
        'author_name' => 'Amaan Khalid',
        'image' => 'images/img-test-2.webp',
    ],
    [
        'text' => 'As someone who watches a lot of international content, I’ve found this IPTV service to be the best. No interruptions, great picture quality, and a ton of channels to choose from. Couldn\'t be happier!',
        'author_name' => 'Nouman Shahid',
        'image' => 'images/img-test-3.webp',
    ],
    [
        'text' => 'I’ve tried several IPTV services in the past, but none compare to the quality and reliability of this one. The channel variety is outstanding, and I’ve never experienced buffering issues during live events. Absolutely worth it!',
        'author_name' => 'Michael',
        'image' => 'images/resource/author-1.webp',
    ],
    [
        'text' => 'I was impressed by the customer support and the smooth streaming quality. Whether I’m watching sports, movies, everything works perfectly. Highly recommended if you’re looking for a reliable IPTV service!',
        'author_name' => 'Sarah',
        'image' => 'images/resource/author-2.webp',
    ],
    [
        'text' => 'This IPTV service offers an amazing selection of international channels and top-notch picture quality. While I’ve encountered a few server issues, the support team has been incredibly responsive and resolved everything quickly. Truly a great experience!',
        'author_name' => 'Ameeq Khan',
        'image' => 'images/img-test.webp',
    ],
];

@endphp

<section class="testimonial-section style-two">
    <div class="auto-container">
        <div class="sec-title centered">
            <div class="title">testimonial</div>
            <h2>Hear from Our Users</h2>
        </div>
        <div class="testimonial-carousel owl-carousel owl-theme">
            @foreach ($testimonials as $testimonial)
                <div class="testimonial-block">
                    <div class="inner-box">
                        <div class="upper-box">
                            <div class="text">"{{ $testimonial['text'] }}"</div>
                        </div>
                        <div class="lower-box">
                            <div class="color-layer"></div>
                            <div class="pattern-layer" style="background-image: url(images/background/pattern-8.webp)"></div>
                            <div class="author-image-outer">
                                <span class="quote-icon fa fa-quote-left"></span>
                                <div class="author-image">
                                    <img src="{{ asset($testimonial['image']) }}" alt="{{ $testimonial['author_name'] }}" width="150" height="150" loading="lazy" />
                                </div>
                            </div>
                            <div class="author-name">{{ $testimonial['author_name'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
