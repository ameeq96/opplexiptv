<!-- Main Footer -->
<footer class="main-footer">
    @unless ($isMobile)
        <div class="pattern-layer-one" style="background-image:url('{{ asset('images/background/pattern-12.webp') }}')"></div>
        <div class="pattern-layer-two" style="background-image:url('{{ asset('images/background/pattern-13.webp') }}')"></div>
    @endunless

    <div class="auto-container" style="direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="widgets-section">
            <!-- Logo -->
            <div class="logo" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Opplex IPTV Logo" width="386"
                    height="100" loading="lazy" />
            </div>

            @php
                $contactAlign = $isMobile ? 'center' : ($isRtl ? 'right' : 'left');
            @endphp

            <!-- Contact Info (single DRY block) -->
            <ul class="contact-info-list" style="text-align: {{ $contactAlign }};">
                <li>
                    <span class="icon">
                        <img src="{{ asset('images/icons/icon-1.webp') }}" alt="Phone Icon" loading="lazy" />
                    </span><br>
                    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_footer')) }}"
                        target="_blank" rel="noopener noreferrer">
                        {{ $isRtl ? '4913-093 (936) 1+' : __('messages.footer_phone') }}
                    </a>
                </li>
                <li class="icon-center-footer">
                    <span class="icon">
                        <img src="{{ asset('images/icons/icon-2.webp') }}" alt="Email Icon" loading="lazy" />
                    </span><br>
                    <a href="mailto:info@opplexiptv.com">{{ __('messages.footer_email_1') }}</a><br>
                </li>
                <li>
                    <span class="icon">
                        <img src="{{ asset('images/icons/icon-3.webp') }}" alt="Address Icon" loading="lazy" />
                    </span><br>
                    {{ __('messages.footer_address') }}
                </li>
            </ul>

            <!-- Social Box -->
            <ul class="social-box"
                style="display:flex; justify-content:{{ $isRtl ? 'flex-end' : 'flex-start' }}; gap:10px;">
                <li>
                    <a href="https://www.facebook.com/profile.php?id=61565476366548" class="fa fa-facebook-f"
                        target="_blank" rel="noopener noreferrer" aria-label="Facebook"></a>
                </li>
                <li>
                    <a href="https://www.linkedin.com/company/digitalize-store/" class="fa fa-linkedin" target="_blank"
                        rel="noopener noreferrer" aria-label="LinkedIn"></a>
                </li>
                <li>
                    <a href="https://www.instagram.com/oplextv/" class="fa fa-instagram" target="_blank"
                        rel="noopener noreferrer" aria-label="Instagram"></a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="auto-container">
            <div class="copyright">
                &copy; 2022 - {{ date('Y') }} Opplex IPTV. {{ __('messages.footer_rights') }}
            </div>
        </div>
    </div>
</footer>
<!-- End Main Footer -->

</div><!-- End pagewrapper -->

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
<script src="{{ asset('js/nav-tool.js') }}" defer></script>
<script src="{{ asset('js/script.js') }}" defer></script>

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

            const form = document.getElementById('contact-form');
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
