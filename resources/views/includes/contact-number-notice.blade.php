@php
    $contactNumber = config('services.whatsapp.number');
    $contactDisplay = config('services.whatsapp.display');
    $contactNoticeKey = 'opplex.contactNumberNotice.'.($contactNumber ?: 'unavailable');
@endphp

<style>
    .contact-number-notice {
        position: fixed;
        z-index: 2147483000;
        inset: 0;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(7, 15, 38, .72);
        backdrop-filter: blur(5px);
    }
    .contact-number-notice[hidden] { display: none !important; }
    .contact-number-notice__dialog {
        position: relative;
        width: min(540px, 100%);
        padding: 34px;
        overflow: hidden;
        border: 1px solid #dce3ef;
        border-radius: 22px;
        background: #fff;
        color: #0b1637;
        box-shadow: 0 28px 80px rgba(7, 15, 38, .28);
        text-align: center;
    }
    .contact-number-notice__close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 38px;
        height: 38px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #f1f4f8;
        color: #17213b;
        font: 700 25px/1 inherit;
        cursor: pointer;
    }
    .contact-number-notice__icon {
        display: grid;
        width: 68px;
        height: 68px;
        margin: 0 auto 18px;
        place-items: center;
        border-radius: 20px;
        background: #effcf3;
        box-shadow: 0 10px 28px rgba(37, 211, 102, .16);
    }
    .contact-number-notice__icon img { width: 38px; height: 38px; }
    .contact-number-notice__eyebrow {
        margin: 0 0 8px;
        color: #d71923;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .contact-number-notice__title {
        margin: 0 0 12px;
        color: #071331;
        font-size: clamp(25px, 4vw, 34px);
        line-height: 1.15;
    }
    .contact-number-notice__text {
        margin: 0 auto;
        max-width: 440px;
        color: #4a556d;
        font-size: 16px;
        line-height: 1.65;
    }
    .contact-number-notice__number {
        display: inline-block;
        margin-top: 10px;
        color: #0d8f45;
        font-size: 21px;
        font-weight: 800;
        text-decoration: none;
    }
    .contact-number-notice__actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 24px;
    }
    .contact-number-notice__button {
        display: inline-flex;
        min-height: 46px;
        align-items: center;
        justify-content: center;
        padding: 11px 18px;
        border: 1px solid #d5dce8;
        border-radius: 12px;
        background: #fff;
        color: #17213b;
        font: 800 14px/1.25 inherit;
        text-decoration: none;
        cursor: pointer;
    }
    .contact-number-notice__button--whatsapp {
        border-color: #16a34a;
        background: #20c763;
        color: #fff;
    }
    .contact-number-notice__button:focus-visible,
    .contact-number-notice__close:focus-visible {
        outline: 3px solid rgba(21, 63, 188, .35);
        outline-offset: 3px;
    }
    body.contact-number-notice-open { overflow: hidden; }
    body.contact-number-notice-open #cookie-consent,
    body.contact-number-notice-open #cookie-settings,
    body.contact-number-notice-open #dw-overlay,
    body.contact-number-notice-open #voice-assistant,
    body.contact-number-notice-open #va-onboarding {
        visibility: hidden !important;
        pointer-events: none !important;
    }
    @media (max-width: 560px) {
        .contact-number-notice { padding: 12px; }
        .contact-number-notice__dialog { padding: 30px 18px 22px; border-radius: 18px; }
        .contact-number-notice__actions { flex-direction: column; }
        .contact-number-notice__button { width: 100%; }
    }
</style>

<section id="contact-number-notice" class="contact-number-notice" role="dialog" aria-modal="true"
    aria-labelledby="contact-number-notice-title" aria-describedby="contact-number-notice-text" dir="ltr" hidden>
    <div class="contact-number-notice__dialog">
        <button type="button" class="contact-number-notice__close" data-contact-number-notice-close
            aria-label="Close contact update">&times;</button>

        <span class="contact-number-notice__icon" aria-hidden="true">
            <img src="{{ asset('images/whatsapp.webp') }}" width="38" height="38" alt="">
        </span>
        <p class="contact-number-notice__eyebrow">Important Contact Update</p>
        <h2 id="contact-number-notice-title" class="contact-number-notice__title">Our WhatsApp Number Has Changed</h2>
        <p id="contact-number-notice-text" class="contact-number-notice__text">
            All existing and new customers should now contact us on this number for support or any questions.
            @if ($contactNumber)
                <a class="contact-number-notice__number"
                    href="https://wa.me/{{ $contactNumber }}?text={{ urlencode('Hello, I have a question and need support.') }}"
                    target="_blank" rel="noopener noreferrer"
                    data-whatsapp-click data-whatsapp-placement="contact_number_notice"
                    data-whatsapp-intent="support">{{ $contactDisplay }}</a>
            @endif
        </p>

        <div class="contact-number-notice__actions">
            @if ($contactNumber)
                <a class="contact-number-notice__button contact-number-notice__button--whatsapp"
                    href="https://wa.me/{{ $contactNumber }}?text={{ urlencode('Hello, I have a question and need support.') }}"
                    target="_blank" rel="noopener noreferrer"
                    data-whatsapp-click data-whatsapp-placement="contact_number_notice"
                    data-whatsapp-intent="support" data-contact-number-notice-close>
                    Contact Us on WhatsApp
                </a>
            @endif
            <button type="button" class="contact-number-notice__button" data-contact-number-notice-close>
                Continue to Website
            </button>
        </div>
    </div>
</section>

<script>
    (function (w, d) {
        var notice = d.getElementById('contact-number-notice');
        if (!notice) return;

        var storageKey = @json($contactNoticeKey);
        var previousFocus = null;

        function wasDismissed() {
            try { return w.sessionStorage.getItem(storageKey) === '1'; }
            catch (e) { return false; }
        }

        function openNotice() {
            previousFocus = d.activeElement;
            notice.hidden = false;
            d.body.classList.add('contact-number-notice-open');
            w.__activeMarketingPrompt = 'contact-number-notice';
            w.requestAnimationFrame(function () {
                notice.querySelector('.contact-number-notice__close')?.focus();
            });
        }

        function closeNotice() {
            if (notice.hidden) return;
            try { w.sessionStorage.setItem(storageKey, '1'); } catch (e) {}
            notice.hidden = true;
            d.body.classList.remove('contact-number-notice-open');
            if (w.__activeMarketingPrompt === 'contact-number-notice') {
                w.__activeMarketingPrompt = null;
            }
            w.dispatchEvent(new CustomEvent('contact-number-notice:closed'));
            if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
        }

        notice.querySelectorAll('[data-contact-number-notice-close]').forEach(function (element) {
            element.addEventListener('click', closeNotice);
        });

        d.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !notice.hidden) closeNotice();
        });

        if (!wasDismissed()) openNotice();
    })(window, document);
</script>
