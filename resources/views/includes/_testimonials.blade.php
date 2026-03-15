<section class="testimonial-section style-two {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--testimonials' : '' }}"
    @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
    aria-label="Customer Testimonials about Opplex IPTV">
    @if (!empty($useSectionSkeletons))
        <div class="section-skeleton__overlay" aria-hidden="true">
            <div class="section-skeleton__content">
                <span class="section-skeleton__pill"></span>
                <span class="section-skeleton__line section-skeleton__line--lg"></span>
                <span class="section-skeleton__line section-skeleton__line--md"></span>
                <div class="section-skeleton__cards">
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                </div>
            </div>
        </div>
    @endif
    <div class="auto-container">
        <div class="sec-title centered testimonial-showcase__heading">
            <div class="title" aria-label="Testimonials Section Subheading">{{ __('messages.testimonials_title') }}</div>
            <h3 aria-label="Hear from our satisfied IPTV customers">{{ __('messages.testimonials_heading') }}</h3>
            <p>Verified customer feedback from viewers using Opplex IPTV across live TV, sports and on-demand streaming.</p>
        </div>

        @if (!empty($useNativeCarousel))
            <div class="native-carousel native-carousel--cards native-carousel--testimonials"
                data-native-carousel
                data-items-desktop="3"
                data-items-tablet="2"
                data-items-mobile="1"
                data-gap="30"
                data-autoplay="4000"
                role="region"
                aria-label="Testimonial carousel of IPTV customer feedback">
                <div class="native-carousel__viewport">
                    <div class="native-carousel__track">
                        @foreach ($testimonials as $testimonial)
                            <div class="native-carousel__slide">
                                <div class="testimonial-block" role="group" aria-label="Testimonial from {{ $testimonial['author_name'] }}">
                                    <div class="inner-box testimonial-card">
                                        <div class="testimonial-card__quote-mark" aria-hidden="true">"</div>
                                        <div class="upper-box testimonial-card__body">
                                            <div class="text" aria-label="Customer Feedback">{{ $testimonial['text'] }}</div>
                                        </div>
                                        <div class="lower-box testimonial-card__footer">
                                            <div class="author-image-outer testimonial-card__author">
                                                <div class="author-image">
                                                    <img src="{{ $testimonial['image'] ? asset($testimonial['image']) : asset('images/placeholder.webp') }}"
                                                        alt="Photo of {{ $testimonial['author_name'] }}, IPTV customer"
                                                        width="150" height="150" loading="lazy" />
                                                </div>
                                                <div class="testimonial-card__author-copy">
                                                    <div class="author-name" aria-label="Customer Name">{{ $testimonial['author_name'] }}</div>
                                                    <div class="testimonial-card__author-role">Verified IPTV Customer</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="testimonial-carousel owl-carousel owl-theme" role="region" aria-label="Testimonial carousel of IPTV customer feedback">
                @foreach ($testimonials as $testimonial)
                    <div class="testimonial-block" role="group" aria-label="Testimonial from {{ $testimonial['author_name'] }}">
                        <div class="inner-box testimonial-card">
                            <div class="testimonial-card__quote-mark" aria-hidden="true">"</div>
                            <div class="upper-box testimonial-card__body">
                                <div class="text" aria-label="Customer Feedback">{{ $testimonial['text'] }}</div>
                            </div>
                            <div class="lower-box testimonial-card__footer">
                                <div class="author-image-outer testimonial-card__author">
                                    <div class="author-image">
                                        <img src="{{ $testimonial['image'] ? asset($testimonial['image']) : asset('images/placeholder.webp') }}"
                                             alt="Photo of {{ $testimonial['author_name'] }}, IPTV customer"
                                             width="150" height="150" loading="lazy" />
                                    </div>
                                    <div class="testimonial-card__author-copy">
                                        <div class="author-name" aria-label="Customer Name">{{ $testimonial['author_name'] }}</div>
                                        <div class="testimonial-card__author-role">Verified IPTV Customer</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
