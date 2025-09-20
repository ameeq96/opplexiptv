<section class="testimonial-section style-two" aria-label="Customer Testimonials about Opplex IPTV">
    <div class="auto-container">
        <div class="sec-title centered">
            <div class="title" aria-label="Testimonials Section Subheading">{{ __('messages.testimonials_title') }}</div>
            <h2 aria-label="Hear from our satisfied IPTV customers">{{ __('messages.testimonials_heading') }}</h2>
        </div>

        <div class="testimonial-carousel owl-carousel owl-theme" role="region" aria-label="Testimonial carousel of IPTV customer feedback">
            @foreach ($testimonials as $testimonial)
                <div class="testimonial-block" role="group" aria-label="Testimonial from {{ $testimonial['author_name'] }}">
                    <div class="inner-box">
                        <div class="upper-box">
                            <div class="text" aria-label="Customer Feedback">"{{ $testimonial['text'] }}"</div>
                        </div>
                        <div class="lower-box">
                            <div class="color-layer" aria-hidden="true"></div>
                            <div class="pattern-layer" style="background-image:url('{{ asset('images/background/pattern-8.webp') }}')" aria-hidden="true"></div>

                            <div class="author-image-outer">
                                <span class="quote-icon fa fa-quote-left" aria-hidden="true"></span>
                                <div class="author-image">
                                    <img src="{{ asset($testimonial['image']) }}" alt="Photo of {{ $testimonial['author_name'] }}, IPTV customer"
                                         width="150" height="150" loading="lazy" />
                                </div>
                            </div>

                            <div class="author-name" aria-label="Customer Name">{{ $testimonial['author_name'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>