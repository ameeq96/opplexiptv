<!-- ======= Pro Footer v2 (optimized) ======= -->
<footer class="fx-footer" data-theme="dark">
    @php
        // -------- Helpers & defaults --------
        $isMobile = $isMobile ?? false;
        $isRtl    = $isRtl ?? false;

        if (!function_exists('tf')) {
            // tf('messages.key', 'Fallback') => returns translated string or fallback if missing
            function tf(string $key, string $fallback) {
                $t = __($key);
                return $t === $key ? $fallback : $t;
            }
        }

        // Translations with clean fallbacks
        $tPhone   = tf('messages.footer_phone', '+1 (639) 390-3194');
        $tEmail   = tf('messages.footer_email_1', 'info@opplexiptv.com');
        $tAddress = tf('messages.footer_address', 'Saskatoon SK, Canada');
        $tRights  = tf('messages.footer_rights', 'All Rights Reserved.');
        $waText   = tf('messages.whatsapp_footer', 'Hello! I need help with Opplex IPTV.');
    @endphp

    {{-- Background layers (desktop only) --}}
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
                     alt="Opplex IPTV"
                     width="340" height="90"
                     loading="lazy" decoding="async" />
                <p class="fx-brand__tag">
                    {{ $isRtl ? 'ہم Cryptomus کے ذریعے کرپٹو ادائیگی قبول کرتے ہیں۔' : 'We accept crypto payments via Cryptomus.' }}
                </p>
            </div>

            <ul class="fx-social">
                <li>
                    <a href="https://www.facebook.com/profile.php?id=61565476366548"
                       class="fx-social__btn" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-facebook-f" aria-hidden="true"></i>
                    </a>
                </li>
                <li>
                    <a href="https://www.linkedin.com/company/digitalize-store/"
                       class="fx-social__btn" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-linkedin" aria-hidden="true"></i>
                    </a>
                </li>
                <li>
                    <a href="https://www.instagram.com/oplextv/"
                       class="fx-social__btn" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-instagram" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Mid: grid -->
        <div class="fx-grid">
            <!-- Contact -->
            <div class="fx-col">
                <h4 class="fx-title">{{ $isRtl ? 'رابطہ' : 'Contact' }}</h4>
                <ul class="fx-list">
                    <li class="fx-list__item">
                        <span class="fx-list__icon" aria-hidden="true">📱</span>
                        <a href="https://wa.me/16393903194?text={{ urlencode($waText) }}"
                           target="_blank" rel="noopener noreferrer" class="fx-link">
                            {{ $tPhone }}
                        </a>
                    </li>
                    <li class="fx-list__item">
                        <span class="fx-list__icon" aria-hidden="true">✉️</span>
                        <a href="mailto:info@opplexiptv.com" class="fx-link">{{ $tEmail }}</a>
                    </li>
                    <li class="fx-list__item">
                        <span class="fx-list__icon" aria-hidden="true">📍</span>
                        <span>{{ $tAddress }}</span>
                    </li>
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

            <!-- Payments -->
            <div class="fx-col fx-col--payments">
                <h4 class="fx-title">{{ $isRtl ? 'ادائیگیاں' : 'Payments' }}</h4>
                <ul class="fx-pay">
                    <li><img src="{{ asset('images/payments/visa.png') }}"        alt="Visa"       width="50" loading="lazy" decoding="async"></li>
                    <li><img src="{{ asset('images/payments/mastercard.png') }}"  alt="Mastercard" width="50" loading="lazy" decoding="async"></li>
                    <li><img src="{{ asset('images/payments/cryptomus.png') }}"   alt="Cryptomus"  width="50" loading="lazy" decoding="async"></li>
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

<!-- ======= Scripts (deferred; order preserved) ======= -->
<script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.10/jquery.mixitup.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-appear/0.1/jquery.appear.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paroller.js/1.4.6/jquery.paroller.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.1/owl.carousel.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/intlTelInput.min.js" defer></script>
{{-- DUPLICATE REMOVED: utils.js will be pulled lazily via utilsScript option in init --}}

<!-- Local scripts: load at idle (or onload fallback) -->
<script>
(() => {
  'use strict';

  // -------- Helpers --------
  const onReady = (fn) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else { fn(); }
  };

  const onLoad = (fn) => window.addEventListener('load', fn, { once: true });

  const idle = (fn, timeout = 2000) => {
    if ('requestIdleCallback' in window) requestIdleCallback(fn, { timeout });
    else onLoad(fn);
  };

  // -------- Preloader fade-out --------
  onLoad(() => {
    document.documentElement.classList.remove('is-loading');
    const el = document.getElementById('fx-preloader');
    if (!el) return;
    el.style.transition = 'opacity .25s ease';
    el.style.opacity = '0';
    setTimeout(() => { el.parentNode && el.parentNode.removeChild(el); }, 260);
  });

  // -------- Load local JS files at idle --------
  idle(() => {
    ['{{ v('js/nav-tool.js') }}','{{ v('js/discount-wheel.js') }}','{{ v('js/script.js') }}']
      .forEach(src => {
        const s = document.createElement('script');
        s.src = src;
        s.async = true; // non-blocking
        document.body.appendChild(s);
      });
  });

  // -------- intl-tel-input init (after DOM ready) --------
  onReady(() => {
    const input = document.getElementById('phone');
    const errEl = document.getElementById('phone-client-error');
    if (!input || !window.intlTelInput) return;

    const iti = window.intlTelInput(input, {
      initialCountry: "pk",
      preferredCountries: ["pk", "sa", "ae", "gb", "us", "ca"],
      separateDialCode: true,
      nationalMode: true,
      placeholderNumberType: "MOBILE",
      // utils file is fetched lazily by the plugin when needed
      utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js"
    });

    // Seed with existing +E.164
    if (input.value && input.value.trim().startsWith('+')) {
      try { iti.setNumber(input.value.trim()); } catch(e) {}
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
    }, { passive: true });

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
        // normalize to E.164
        input.value = iti.getNumber();
      });
    }
  });

  // -------- Page enhancements --------
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
          if (entry.isIntersecting) { applyBg(entry.target); obs.unobserve(entry.target); }
        }
      }, { rootMargin: '200px 0px' });
      lazyBackgrounds.forEach(el => io.observe(el));
    } else {
      lazyBackgrounds.forEach(applyBg); // fallback
    }

    // Reseller toggle (guard elements)
    const toggle     = document.getElementById('resellerToggle');
    const normal     = document.getElementById('normalPackages');
    const reseller   = document.getElementById('resellerPackages');
    const creditInfo = document.getElementById('creditInfo');
    const realToggle = document.getElementById('real-toggle');

    if (realToggle) realToggle.style.display = 'block';

    if (toggle && normal && reseller && creditInfo) {
      const applyState = (checked) => {
        normal.style.display   = checked ? 'none'  : 'flex';
        reseller.style.display = checked ? 'flex'  : 'none';
        creditInfo.style.display = checked ? 'block' : 'none';
      };
      applyState(!!toggle.checked);
      toggle.addEventListener('change', (e) => applyState(e.target.checked));
    }
  });

})();
</script>
