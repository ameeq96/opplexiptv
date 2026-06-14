<!-- ======= Pro Footer v2 ======= -->
<footer class="fx-footer" data-theme="dark">
@php
    // Safe defaults
    $isMobile = $isMobile ?? false;
    $isRtl    = $isRtl ?? false;

    $footerSettings = $footer['settings'] ?? [];
    $footerLinks = $footer['links'] ?? [];
    $footerSocials = $footer['socials'] ?? [];

    // Translations with fallbacks
    $tPhone   = __('messages.footer_phone');    if ($tPhone   === 'messages.footer_phone')   $tPhone = '+1 (639) 390-3194';
    $tEmail   = __('messages.footer_email_1');  if ($tEmail   === 'messages.footer_email_1') $tEmail = 'info@opplexiptv.com';
    $tAddress = __('messages.footer_address');  if ($tAddress === 'messages.footer_address') $tAddress = 'Saskatoon SK, Canada';
    $tRights  = __('messages.footer_rights');   if ($tRights  === 'messages.footer_rights')  $tRights = 'All Rights Reserved.';
    $waText   = __('messages.whatsapp_footer'); if ($waText   === 'messages.whatsapp_footer')$waText = 'Hello! I need help with Opplex IPTV.';

    $tPhone = $footerSettings['phone'] ?? $tPhone;
    $tEmail = $footerSettings['email'] ?? $tEmail;
    $tAddress = $footerSettings['address'] ?? $tAddress;
    $tRights = $footerSettings['rights_text'] ?? $tRights;
    $brandText = $footerSettings['brand_text'] ?? null;
    $cryptoNote = $footerSettings['crypto_note'] ?? null;
    $legalNote = $footerSettings['legal_note'] ?? null;

    $routeName = optional(request()->route())->getName();
    $isMoviesRoute = $routeName === 'movies';
    $targetOptimizedRoutes = ['packages', 'faqs', 'about', 'contact', 'reseller-panel', 'pricing', 'movies', 'shop', 'blogs.index'];
    $isTargetOptimizedRoute = in_array($routeName, $targetOptimizedRoutes, true);
    $needsJquery = !$isMoviesRoute;
    $needsStandalonePopper = !$isMoviesRoute;
    $needsBootstrap = !$isMoviesRoute;
    $needsCustomScrollbar = !$isMoviesRoute;
    $needsMixItUp = false; // MixItUp not used anywhere: movie filtering uses vanilla JS (applyFilter). Avoids shipping legacy JS.
    $needsFancybox = !$isTargetOptimizedRoute;
    $needsAppear = !$isTargetOptimizedRoute;
    $needsParallax = !$isTargetOptimizedRoute;
    $needsParoller = !$isTargetOptimizedRoute;
    $needsOwlCarousel = !$isTargetOptimizedRoute || in_array($routeName, ['about', 'reseller-panel'], true);
    $needsValidation = $routeName !== 'movies';
    $needsPhoneAssets = in_array($routeName, ['contact', 'checkout', 'digital.checkout.show', 'buynow', 'buynowpanel'], true);
@endphp

    <!-- Background layers -->
    @unless ($isMobile)
        <div class="fx-footer__dots" style="background-image:url('{{ asset('images/background/pattern-13.webp') }}')"></div>
        <div class="fx-footer__grad"></div>
    @endunless

    <div class="fx-container" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <!-- Top: brand + socials -->
        <div class="fx-footer__head">
            <div class="fx-brand">
                <img class="fx-brand__logo"
                     src="{{ asset('images/opplexiptvlogo.webp') }}"
                     alt="Opplex IPTV" width="250" height="65" loading="lazy" decoding="async">
                <p class="fx-brand__tag">
                    {{ $cryptoNote ?? ($isRtl ? 'ہم Cryptomus کے ذریعے کرپٹو ادائیگی قبول کرتے ہیں۔' : 'We accept crypto payments via Cryptomus.') }}
                </p>
            </div>

            <ul class="fx-social">
                @if (!empty($footerSocials))
                    @foreach ($footerSocials as $s)
                        <li>
                            <a href="{{ $s['url'] }}" class="fx-social__btn" aria-label="{{ $s['platform'] ?: 'Social profile' }}"
                               target="_blank" rel="noopener">
                                <i class="{{ $s['icon_class'] ?: 'fa fa-link' }}"></i>
                            </a>
                        </li>
                    @endforeach
                @else
                    <li><a href="https://www.facebook.com/profile.php?id=61565476366548"  class="fx-social__btn" aria-label="Facebook"  target="_blank" rel="noopener"><i class="fa fa-facebook-f"></i></a></li>
                    <li><a href="https://www.linkedin.com/company/digitalize-store/"       class="fx-social__btn" aria-label="LinkedIn"  target="_blank" rel="noopener"><i class="fa fa-linkedin"></i></a></li>
                    <li><a href="https://www.instagram.com/oplextv/"                       class="fx-social__btn" aria-label="Instagram" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a></li>
                @endif
            </ul>
        </div>

        <!-- Mid: grid -->
        <div class="fx-grid">
            <!-- Contact -->
            <div class="fx-col">
                <h4 class="fx-title">{{ $isRtl ? 'رابطہ' : 'Contact' }}</h4>
                <ul class="fx-list">
                    <li class="fx-list__item">
                        <span class="fx-list__icon">📱</span>
                        <a href="https://wa.me/16393903194?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="fx-link">{{ $tPhone }}</a>
                    </li>
                    <li class="fx-list__item">
                        <span class="fx-list__icon">✉️</span>
                        <a href="mailto:info@opplexiptv.com" class="fx-link">{{ $tEmail }}</a>
                    </li>
                    <li class="fx-list__item">
                        <span class="fx-list__icon">📍</span>
                        <span>{{ $tAddress }}</span>
                    </li>
                </ul>
            </div>

                        <!-- Payments -->
            <div class="fx-col fx-col--payments">
                <h4 class="fx-title">{{ $isRtl ? 'ایکسپلور' : 'Explore' }}</h4>
                <ul class="fx-list">
                    @foreach (($footerLinks['explore'] ?? []) as $l)
                        <li><a class="fx-link" href="{{ $l['url'] }}">{{ $l['label'] }}</a></li>
                    @endforeach
                    @if (empty($footerLinks['explore']))
                        <li><a class="fx-link" href="{{ url('/') }}">Home</a></li>
                        <li><a class="fx-link" href="{{ url('/pricing') }}">Pricing</a></li>
                        <li><a class="fx-link" href="{{ url('/packages') }}">Packages</a></li>
                        <li><a class="fx-link" href="{{ url('/reseller-panel') }}">Reseller Panel</a></li>
                        <li><a class="fx-link" href="{{ url('/movies') }}">Movies</a></li>
                        <li><a class="fx-link" href="{{ url('/iptv-applications') }}">IPTV Apps</a></li>
                        <li><a class="fx-link" href="{{ url('/shop') }}">Products</a></li>
                    @endif
                </ul>
            </div>

            <!-- Company -->
            <div class="fx-col">
                <h4 class="fx-title">{{ $isRtl ? 'کمپنی' : 'Company' }}</h4>
                <ul class="fx-list">
                    @foreach (($footerLinks['company'] ?? []) as $l)
                        <li><a class="fx-link" href="{{ $l['url'] }}">{{ $l['label'] }}</a></li>
                    @endforeach
                    @if (empty($footerLinks['company']))
                        <li><a class="fx-link" href="{{ url('/about') }}">{{ $isRtl ? 'ہمارے بارے میں' : 'About Us' }}</a></li>
                        <li><a class="fx-link" href="{{ url('/contact') }}">{{ $isRtl ? 'ہم سے رابطہ' : 'Contact Us' }}</a></li>
                        <li><a class="fx-link" href="{{ url('/faqs') }}">FAQ</a></li>
                    @endif
                </ul>
            </div>

            <!-- Legal -->
            <div class="fx-col">
                <h4 class="fx-title">{{ $isRtl ? 'قانونی' : 'Legal' }}</h4>
                <ul class="fx-list">
                    @foreach (($footerLinks['legal'] ?? []) as $l)
                        <li><a class="fx-link" href="{{ $l['url'] }}">{{ $l['label'] }}</a></li>
                    @endforeach
                    @if (empty($footerLinks['legal']))
                        <li><a class="fx-link" href="{{ url('/terms-of-service') }}">{{ $isRtl ? 'سروس کی شرائط' : 'Terms of Service' }}</a></li>
                        <li><a class="fx-link" href="{{ url('/privacy-policy') }}">{{ $isRtl ? 'رازداری پالیسی' : 'Privacy Policy' }}</a></li>
                        <li><a class="fx-link" href="{{ url('/refund-policy') }}">{{ $isRtl ? 'ریفنڈ و منسوخی' : 'Refund & Cancellation' }}</a></li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="fx-footer__bottom">
            <div class="fx-copy">&copy; 2022 - {{ date('Y') }} <strong>Opplex IPTV</strong>. {{ $tRights }}</div>
            <div class="fx-legal-note">
                {{ $legalNote ?? ($isRtl
                    ? 'کرپٹو ادائیگیوں کا استعمال مقامی قوانین کے مطابق ہونا چاہیے۔ مزید معلومات کے لیے Privacy Policy اور ریفنڈ پالیسی دیکھیں۔'
                    : 'Use of crypto payments must comply with your local laws. See our Privacy Policy and Refund policies for details.') }}
                <div class="fx-deeplinks" style="font-size:12px; margin-top:6px; color:#aaa;">
                    @foreach (($footerLinks['deeplink'] ?? []) as $l)
                        <a href="{{ $l['url'] }}">{{ $l['label'] }}</a>@if(!$loop->last) | @endif
                    @endforeach
                    @if (empty($footerLinks['deeplink']))
                        <a href="{{ url('/activate') }}">Activate</a> |
                        <a href="{{ url('/configure') }}">Configure</a> |
                        <a href="{{ url('/checkout') }}">Checkout</a> |
                        <a href="{{ url('/thank-you') }}">Thank You</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- ======= /Pro Footer v2 ======= -->


</div><!-- End pagewrapper -->

@include('includes.spin-popup')

<div class="scroll-to-top scroll-to-target" data-target="html" aria-label="Scroll to top">
    <span class="fa fa-arrow-up" aria-hidden="true"></span>
</div>

<!-- Scripts: keep order; defer ensures execution after parse (preserves order across tags) -->
@if ($needsJquery)
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
@endif
@if ($needsStandalonePopper)
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" defer></script>
@endif
@if ($needsMixItUp)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.10/jquery.mixitup.min.js" defer></script>
@endif
@if ($needsValidation)
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js" defer></script>
@endif
@if ($needsBootstrap)
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>
@endif
@if ($needsCustomScrollbar)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js"
        defer></script>
@endif
@if ($needsFancybox)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" defer></script>
@endif
@if ($needsAppear)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-appear/0.1/jquery.appear.min.js" defer></script>
@endif
@if ($needsParallax)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js" defer></script>
@endif
@if ($needsParoller)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paroller.js/1.4.6/jquery.paroller.min.js" defer></script>
@endif
@if ($needsOwlCarousel)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.1/owl.carousel.min.js" defer></script>
@endif
@if ($needsPhoneAssets)
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/intlTelInput.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js" defer></script>
@endif

<!-- Local scripts last -->
<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            var s = document.createElement('script');
            s.src = "{{ \Illuminate\Support\Facades\Vite::asset('resources/js/discount-wheel.js') }}";
            s.type = 'module';
            s.defer = true;
            document.body.appendChild(s);
        }, 5000);
    });
</script>
@unless ($isMoviesRoute)
    @vite('resources/js/site.js')
@endunless

@if ($isMoviesRoute)
    <script>
        (function () {
            'use strict';

            var fancyboxReady = null;

            function onReady(fn) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fn, { once: true });
                } else {
                    fn();
                }
            }

            function firstChildByTag(parent, tagName) {
                if (!parent) return null;
                tagName = tagName.toUpperCase();
                for (var i = 0; i < parent.children.length; i += 1) {
                    if (parent.children[i].tagName === tagName) return parent.children[i];
                }
                return null;
            }

            function loadScript(src) {
                return new Promise(function (resolve, reject) {
                    var existing = document.querySelector('script[src="' + src + '"]');
                    if (existing) {
                        if (existing.dataset.loaded === 'true') {
                            resolve();
                            return;
                        }
                        existing.addEventListener('load', resolve, { once: true });
                        existing.addEventListener('error', reject, { once: true });
                        return;
                    }

                    var script = document.createElement('script');
                    script.src = src;
                    script.defer = true;
                    script.onload = function () {
                        script.dataset.loaded = 'true';
                        resolve();
                    };
                    script.onerror = reject;
                    document.body.appendChild(script);
                });
            }

            function loadCss(href) {
                if (document.querySelector('link[href="' + href + '"]')) return;
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            }

            function ensureFancybox() {
                if (window.jQuery && window.jQuery.fancybox) {
                    return Promise.resolve();
                }

                if (!fancyboxReady) {
                    loadCss('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css');
                    fancyboxReady = loadScript('https://code.jquery.com/jquery-1.12.4.min.js')
                        .then(function () {
                            return loadScript('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js');
                        });
                }

                return fancyboxReady;
            }

            function applyFilter(filter) {
                var selector = filter ? filter.getAttribute('data-filter') : 'all';
                if (!selector) selector = 'all';

                document.querySelectorAll('.filter-tabs .filter').forEach(function (item) {
                    item.classList.toggle('active', item === filter);
                });

                document.querySelectorAll('.filter-list .feature-block').forEach(function (card) {
                    var visible = selector === 'all' || card.matches(selector);
                    card.style.display = visible ? 'block' : 'none';
                });
            }

            function initMovieFilters() {
                var filters = document.querySelectorAll('.filter-tabs .filter');
                if (!filters.length) return;

                filters.forEach(function (filter) {
                    filter.addEventListener('click', function () {
                        applyFilter(filter);
                    });

                    filter.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            applyFilter(filter);
                        }
                    });
                });

                applyFilter(document.querySelector('.filter-tabs .filter.active') || filters[0]);
            }

            function initMobileMenu() {
                var source = document.querySelector('.main-header .main-menu .navigation');
                var target = document.querySelector('.mobile-menu .menu-outer');
                if (source && target && !target.querySelector('.navigation')) {
                    target.insertBefore(source.cloneNode(true), target.firstChild);
                }

                document.querySelectorAll('.mobile-menu .navigation li.dropdown').forEach(function (item) {
                    var submenu = firstChildByTag(item, 'ul');
                    if (!submenu || item.querySelector('.dropdown-btn')) return;

                    var button = document.createElement('div');
                    button.className = 'dropdown-btn';
                    button.innerHTML = '<span class="fa fa-angle-down"></span>';
                    item.appendChild(button);

                    var toggle = function (event) {
                        if (event) event.preventDefault();
                        button.classList.toggle('open');
                        submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                    };

                    button.addEventListener('click', toggle);

                    var link = firstChildByTag(item, 'a');
                    if (link && (link.getAttribute('href') === '#' || link.getAttribute('href') === '')) {
                        link.addEventListener('click', toggle);
                    }
                });

                var open = document.querySelector('.mobile-nav-toggler');
                var backdrop = document.querySelector('.mobile-menu .menu-backdrop');
                var close = document.querySelector('.mobile-menu .close-btn');

                if (open) {
                    open.addEventListener('click', function () {
                        document.body.classList.add('mobile-menu-visible');
                    });
                }

                [backdrop, close].forEach(function (el) {
                    if (!el) return;
                    el.addEventListener('click', function () {
                        document.body.classList.remove('mobile-menu-visible');
                    });
                });
            }

            function initScrollUi() {
                var header = document.querySelector('.main-header');
                var scrollButton = document.querySelector('.scroll-to-target');

                function updateHeader() {
                    if (!header) return;
                    var fixed = window.scrollY >= header.offsetHeight;
                    header.classList.toggle('fixed-header', fixed);
                    if (scrollButton) scrollButton.style.display = fixed ? 'block' : 'none';
                }

                if (scrollButton) {
                    scrollButton.addEventListener('click', function () {
                        var target = scrollButton.getAttribute('data-target') || 'html';
                        var targetEl = document.querySelector(target) || document.documentElement;
                        window.scrollTo({ top: targetEl.offsetTop || 0, behavior: 'smooth' });
                    });
                }

                window.addEventListener('scroll', updateHeader, { passive: true });
                updateHeader();
            }

            function initLightbox() {
                document.addEventListener('click', function (event) {
                    var link = event.target.closest('.lightbox-image');
                    if (!link) return;

                    var href = link.getAttribute('href');
                    if (!href) return;

                    event.preventDefault();
                    ensureFancybox()
                        .then(function () {
                            window.jQuery.fancybox.open({
                                src: href,
                                type: 'iframe',
                                opts: {
                                    iframe: {
                                        preload: false
                                    }
                                }
                            });
                        })
                        .catch(function () {
                            window.open(href, '_blank', 'noopener');
                        });
                });
            }

            onReady(function () {
                initMovieFilters();
                initMobileMenu();
                initScrollUi();
                initLightbox();
            });
        })();
    </script>
@endif

<script>
    @if ($needsPhoneAssets)
    (function() {
        function initPhone() {
            const input = document.getElementById('phone');
            const errEl = document.getElementById('phone-client-error');
            if (!input || !window.intlTelInput) return; // plugin not loaded yet

            const iti = window.intlTelInput(input, {
                initialCountry: "pk",
                preferredCountries: ["pk", "sa", "ae", "gb", "us", "ca"],
                separateDialCode: true,
                nationalMode: true,
                placeholderNumberType: "MOBILE",
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js"
            });

            if (input.value && input.value.trim().startsWith('+')) {
                try {
                    iti.setNumber(input.value.trim());
                } catch (e) {}
            }

            const showError = (msg) => {
                if (!errEl) return;
                errEl.textContent = msg || '';
                errEl.classList.toggle('d-none', !msg);
            };

            input.addEventListener('blur', () => {
                showError('');
                if (!input.value.trim()) return;
                if (!iti.isValidNumber()) showError("{{ __('Invalid phone number') }}");
            }, {
                passive: true
            });

            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    showError('');
                    if (input.value.trim() && !iti.isValidNumber()) {
                        e.preventDefault();
                        showError("{{ __('Invalid phone number') }}");
                        input.focus();
                        return false;
                    }
                    // submit in E.164
                    input.value = iti.getNumber();
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPhone, {
                once: true
            });
        } else {
            initPhone();
        }
    })();
    @endif

    (function() {
        'use strict';

        const onReady = (fn) => {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn, {
                    once: true
                });
            } else {
                fn();
            }
        };

        onReady(() => {
            // Lazy background images
            const lazyBackgrounds = document.querySelectorAll('.lazy-background');

            const applyBg = (el) => {
                const bgUrl = el.getAttribute('data-bg');
                if (bgUrl) el.style.backgroundImage = `url(${bgUrl})`;
            };

            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries, obs) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting) {
                            applyBg(entry.target);
                            obs.unobserve(entry.target);
                        }
                    }
                }, {
                    rootMargin: '200px 0px'
                });
                lazyBackgrounds.forEach(el => io.observe(el));
            } else {
                // Fallback: apply immediately
                lazyBackgrounds.forEach(applyBg);
            }

            const nativeCarousels = document.querySelectorAll('[data-native-carousel]');

            const bindNativeCarousel = (root) => {
                const viewport = root.querySelector('.native-carousel__viewport');
                const track = root.querySelector('.native-carousel__track');
                const slides = Array.from(root.querySelectorAll('.native-carousel__slide'));
                const prev = root.querySelector('[data-native-prev]');
                const next = root.querySelector('[data-native-next]');
                const dots = Array.from(root.querySelectorAll('[data-native-dot]'));
                const autoplay = parseInt(root.getAttribute('data-autoplay') || '0', 10);
                const isHero = root.getAttribute('data-carousel-type') === 'hero';
                const isRtlCarousel = root.getAttribute('data-rtl') === 'true';

                if (!viewport || !track || slides.length === 0) {
                    return;
                }

                let index = 0;
                let visibleItems = 1;
                let timer = null;
                let startX = 0;
                let isPointerDown = false;

                const getVisibleItems = () => {
                    if (isHero) return 1;
                    const width = window.innerWidth;
                    if (width <= 767) return parseInt(root.getAttribute('data-items-mobile') || '1', 10);
                    if (width <= 1024) return parseInt(root.getAttribute('data-items-tablet') || root.getAttribute('data-items-mobile') || '1', 10);
                    return parseInt(root.getAttribute('data-items-desktop') || '1', 10);
                };

                const maxIndex = () => Math.max(0, slides.length - visibleItems);

                const lazyLoadSlide = (slide) => {
                    if (!slide) return;
                    const bg = slide.getAttribute('data-bg');
                    if (bg && !slide.style.backgroundImage) {
                        slide.style.backgroundImage = `url(${bg})`;
                    }
                };

                const updateDots = () => {
                    dots.forEach((dot, dotIndex) => {
                        dot.classList.toggle('is-active', dotIndex === index);
                    });
                };

                const updateArrows = () => {
                    const disabled = slides.length <= visibleItems;
                    if (prev) {
                        prev.classList.toggle('is-hidden', disabled);
                        prev.disabled = disabled;
                    }
                    if (next) {
                        next.classList.toggle('is-hidden', disabled);
                        next.disabled = disabled;
                    }
                };

                const render = () => {
                    visibleItems = getVisibleItems();
                    root.style.setProperty('--native-items', String(visibleItems));
                    root.style.setProperty('--native-gap', `${parseInt(root.getAttribute('data-gap') || '30', 10)}px`);
                    root.style.setProperty('--native-autoplay', `${autoplay}ms`);

                    if (index > maxIndex()) {
                        index = maxIndex();
                    }

                    if (isHero) {
                        slides.forEach((slide, slideIndex) => {
                            const isActive = slideIndex === index;
                            slide.classList.toggle('is-active', isActive);
                            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                            if (isActive) lazyLoadSlide(slide);
                        });
                        updateDots();
                        updateArrows();
                        return;
                    }

                    const gap = parseInt(root.getAttribute('data-gap') || '30', 10);
                    const viewportWidth = viewport.clientWidth;
                    const slideWidth = visibleItems > 0 ? (viewportWidth - (gap * (visibleItems - 1))) / visibleItems : viewportWidth;
                    const direction = isRtlCarousel ? 1 : -1;
                    track.style.transform = `translate3d(${direction * index * (slideWidth + gap)}px, 0, 0)`;
                    slides.slice(index, index + visibleItems + 1).forEach(lazyLoadSlide);
                    updateArrows();
                };

                const goTo = (nextIndex) => {
                    if (isHero) {
                        if (nextIndex < 0) nextIndex = slides.length - 1;
                        if (nextIndex >= slides.length) nextIndex = 0;
                    } else {
                        if (nextIndex < 0) nextIndex = maxIndex();
                        if (nextIndex > maxIndex()) nextIndex = 0;
                    }

                    index = nextIndex;
                    render();
                    updateDots();
                };

                const stopAutoplay = () => {
                    if (timer) {
                        window.clearInterval(timer);
                        timer = null;
                    }
                };

                const startAutoplay = () => {
                    stopAutoplay();
                    if (!autoplay) return;
                    if (!isHero && slides.length <= visibleItems) return;

                    timer = window.setInterval(() => {
                        goTo(index + 1);
                    }, autoplay);
                };

                if (prev) {
                    prev.addEventListener('click', () => {
                        goTo(index - 1);
                        startAutoplay();
                    });
                }

                if (next) {
                    next.addEventListener('click', () => {
                        goTo(index + 1);
                        startAutoplay();
                    });
                }

                dots.forEach((dot) => {
                    dot.addEventListener('click', () => {
                        goTo(parseInt(dot.getAttribute('data-native-dot') || '0', 10));
                        startAutoplay();
                    });
                });

                if (!isHero) {
                    root.addEventListener('mouseenter', stopAutoplay);
                    root.addEventListener('mouseleave', startAutoplay);
                    root.addEventListener('focusin', stopAutoplay);
                    root.addEventListener('focusout', startAutoplay);
                }

                viewport.addEventListener('pointerdown', (event) => {
                    isPointerDown = true;
                    startX = event.clientX;
                });

                viewport.addEventListener('pointerup', (event) => {
                    if (!isPointerDown) return;
                    const delta = event.clientX - startX;
                    isPointerDown = false;
                    if (Math.abs(delta) < 40) return;
                    goTo(index + (delta > 0 ? -1 : 1));
                    startAutoplay();
                });

                viewport.addEventListener('pointerleave', () => {
                    isPointerDown = false;
                });

                window.addEventListener('resize', render, { passive: true });

                lazyLoadSlide(slides[0]);
                render();
                updateDots();
                startAutoplay();
            };

            nativeCarousels.forEach(bindNativeCarousel);

            const shareButtons = document.querySelectorAll('[data-share-url]');

            shareButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.getAttribute('data-share-url') || window.location.href;
                    const title = button.getAttribute('data-share-title') || document.title;
                    const text = button.getAttribute('data-share-text') || title;
                    const original = button.innerHTML;

                    try {
                        if (navigator.share) {
                            await navigator.share({ title, text, url });
                        } else if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(url);
                            button.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
                            window.setTimeout(() => {
                                button.innerHTML = original;
                            }, 1600);
                        } else {
                            window.prompt('Copy this link:', url);
                        }
                    } catch (error) {
                        // User cancelled share sheet or clipboard write failed.
                    }
                });
            });

            // Reseller toggle (guard all elements)
            // else: elements not on this page — silently skip
        });
    })();
</script>
