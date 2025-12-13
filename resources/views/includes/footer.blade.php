<!-- ======= Pro Footer v2 ======= -->
<footer class="fx-footer" data-theme="dark">
@php
    // Safe defaults
    $isMobile = $isMobile ?? false;
    $isRtl    = $isRtl ?? false;

    // Translations with fallbacks
    $tPhone   = __('messages.footer_phone');    if ($tPhone   === 'messages.footer_phone')   $tPhone = '+1 (639) 390-3194';
    $tEmail   = __('messages.footer_email_1');  if ($tEmail   === 'messages.footer_email_1') $tEmail = 'info@opplexiptv.com';
    $tAddress = __('messages.footer_address');  if ($tAddress === 'messages.footer_address') $tAddress = 'Saskatoon SK, Canada';
    $tRights  = __('messages.footer_rights');   if ($tRights  === 'messages.footer_rights')  $tRights = 'All Rights Reserved.';
    $waText   = __('messages.whatsapp_footer'); if ($waText   === 'messages.whatsapp_footer')$waText = 'Hello! I need help with Opplex IPTV.';
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
                     alt="Opplex IPTV" width="250" height="65" loading="lazy">
                <p class="fx-brand__tag">{{ $isRtl ? 'ہم Cryptomus کے ذریعے کرپٹو ادائیگی قبول کرتے ہیں۔' : 'We accept crypto payments via Cryptomus.' }}</p>
            </div>

            <ul class="fx-social">
                <li><a href="https://www.facebook.com/profile.php?id=61565476366548"  class="fx-social__btn" aria-label="Facebook"  target="_blank" rel="noopener"><i class="fa fa-facebook-f"></i></a></li>
                <li><a href="https://www.linkedin.com/company/digitalize-store/"       class="fx-social__btn" aria-label="LinkedIn"  target="_blank" rel="noopener"><i class="fa fa-linkedin"></i></a></li>
                <li><a href="https://www.instagram.com/oplextv/"                       class="fx-social__btn" aria-label="Instagram" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a></li>
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
                    <li><a class="fx-link" href="{{ url('/') }}">Home</a></li>
                    <li><a class="fx-link" href="{{ url('/pricing') }}">Pricing</a></li>
                    <li><a class="fx-link" href="{{ url('/packages') }}">Packages</a></li>
                    <li><a class="fx-link" href="{{ url('/reseller-panel') }}">Reseller Panel</a></li>
                    <li><a class="fx-link" href="{{ url('/movies') }}">Movies</a></li>
                    <li><a class="fx-link" href="{{ url('/iptv-applications') }}">IPTV Apps</a></li>
                    <li><a class="fx-link" href="{{ url('/shop') }}">Shop</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="fx-col">
                <h4 class="fx-title">{{ $isRtl ? 'کمپنی' : 'Company' }}</h4>
                <ul class="fx-list">
                    <li><a class="fx-link" href="{{ url('/about') }}">{{ $isRtl ? 'ہمارے بارے میں' : 'About Us' }}</a></li>
                    <li><a class="fx-link" href="{{ url('/contact') }}">{{ $isRtl ? 'ہم سے رابطہ' : 'Contact Us' }}</a></li>
                    <li><a class="fx-link" href="{{ url('/faqs') }}">FAQ</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div class="fx-col">
                <h4 class="fx-title">{{ $isRtl ? 'قانونی' : 'Legal' }}</h4>
                <ul class="fx-list">
                    <li><a class="fx-link" href="{{ url('/terms-of-service') }}">{{ $isRtl ? 'سروس کی شرائط' : 'Terms of Service' }}</a></li>
                    <li><a class="fx-link" href="{{ url('/privacy-policy') }}">{{ $isRtl ? 'رازداری پالیسی' : 'Privacy Policy' }}</a></li>
                    <li><a class="fx-link" href="{{ url('/refund-policy') }}">{{ $isRtl ? 'ریفنڈ و منسوخی' : 'Refund & Cancellation' }}</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="fx-footer__bottom">
            <div class="fx-copy">&copy; 2022 - {{ date('Y') }} <strong>Opplex IPTV</strong>. {{ $tRights }}</div>
            <div class="fx-legal-note">
                {{ $isRtl
                    ? 'کرپٹو ادائیگیوں کا استعمال مقامی قوانین کے مطابق ہونا چاہیے۔ مزید معلومات کے لیے Privacy Policy اور ریفنڈ پالیسی دیکھیں۔'
                    : 'Use of crypto payments must comply with your local laws. See our Privacy Policy and Refund policies for details.' }}
                        <div class="fx-deeplinks" style="font-size:12px; margin-top:6px; color:#aaa;">
                <a href="{{ url('/activate') }}">Activate</a> |
                <a href="{{ url('/configure') }}">Configure</a> |
                <a href="{{ url('/checkout') }}">Checkout</a> |
                <a href="{{ url('/thank-you') }}">Thank You</a>
            </div></div>
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
<script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.10/jquery.mixitup.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js"
    defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-appear/0.1/jquery.appear.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paroller.js/1.4.6/jquery.paroller.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.1/owl.carousel.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/intlTelInput.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js" defer></script>

<!-- Local scripts last -->
<script src="{{ v('js/nav-tool.js') }}" defer></script>
<script src="{{ v('js/discount-wheel.js') }}" defer></script>
<script src="{{ v('js/script.js') }}" defer></script>

<script>
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

            // Reseller toggle (guard all elements)
            const toggle = document.getElementById('resellerToggle');
            const normal = document.getElementById('normalPackages');
            const reseller = document.getElementById('resellerPackages');
            const creditInfo = document.getElementById('creditInfo');
            const realToggle = document.getElementById('real-toggle');

            if (realToggle) realToggle.style.display = 'block';

            if (toggle && normal && reseller && creditInfo) {
                const applyState = (checked) => {
                    normal.style.display = checked ? 'none' : 'flex';
                    reseller.style.display = checked ? 'flex' : 'none';
                    creditInfo.style.display = checked ? 'block' : 'none';
                };
                applyState(!!toggle.checked);
                toggle.addEventListener('change', (e) => applyState(e.target.checked));
            }
            // else: elements not on this page — silently skip
        });
    })();
</script>
