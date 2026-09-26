@php
    $recentPurchases = app(\App\Services\RecentPurchaseService::class)->items();
@endphp

@if ($recentPurchases !== [])
    <style>
        .recent-purchase-toast {
            position: fixed;
            left: 20px;
            bottom: 96px;
            z-index: 1035;
            display: flex;
            width: min(360px, calc(100vw - 32px));
            align-items: center;
            gap: 12px;
            padding: 14px;
            padding-inline-end: 44px;
            color: #172033;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, .1);
            border-radius: 14px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .2);
            font-family: "Poppins", Arial, sans-serif;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(18px);
            transition: bottom .28s ease, opacity .28s ease, visibility .28s ease, transform .28s ease;
        }
        .recent-purchase-toast[hidden] { display: none !important; }
        .recent-purchase-toast.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }
        .recent-purchase-toast__icon {
            display: inline-flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 999px;
            box-shadow: 0 9px 20px rgba(22, 163, 74, .28);
            font-size: 22px;
            font-weight: 800;
        }
        .recent-purchase-toast__copy { min-width: 0; }
        .recent-purchase-toast__label {
            display: block;
            margin-bottom: 2px;
            color: #15803d;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .recent-purchase-toast__message {
            margin: 0;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .recent-purchase-toast__close {
            position: absolute;
            top: 8px;
            inset-inline-end: 9px;
            width: 28px;
            height: 28px;
            padding: 0;
            color: #64748b;
            background: transparent;
            border: 0;
            border-radius: 999px;
            font: 700 20px/28px Arial, sans-serif;
            cursor: pointer;
        }
        .recent-purchase-toast__close:hover { color: #0f172a; background: #f1f5f9; }
        .recent-purchase-toast__close:focus-visible { outline: 3px solid rgba(37, 99, 235, .28); }
        body.cookie-consent-open .recent-purchase-toast,
        body.whatsapp-lead-capture-open .recent-purchase-toast,
        body.contact-number-notice-open .recent-purchase-toast,
        body.modal-open .recent-purchase-toast,
        body.va-panel-open .recent-purchase-toast { display: none !important; }
        @media (min-width: 769px) {
            body.pricing-in-view .recent-purchase-toast { bottom: 20px; }
        }
        @media (max-width: 480px) {
            .recent-purchase-toast {
                right: 10px;
                bottom: 88px;
                left: 10px;
                width: auto;
                padding: 12px;
                padding-inline-end: 40px;
            }
            .recent-purchase-toast__icon { width: 40px; height: 40px; flex-basis: 40px; }
            .recent-purchase-toast__message { font-size: 12px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .recent-purchase-toast { transition: none; }
        }
    </style>

    <aside id="recent-purchase-toast" class="recent-purchase-toast" role="status"
        aria-live="polite" aria-atomic="true" hidden>
        <span class="recent-purchase-toast__icon" aria-hidden="true">✓</span>
        <div class="recent-purchase-toast__copy">
            <span class="recent-purchase-toast__label">{{ __('marketing.social_proof.verified_purchase') }}</span>
            <p id="recent-purchase-message" class="recent-purchase-toast__message"></p>
        </div>
        <button id="recent-purchase-close" class="recent-purchase-toast__close" type="button"
            aria-label="{{ __('marketing.social_proof.close') }}">&times;</button>
    </aside>

    <script>
        (function () {
            const purchases = @json($recentPurchases);
            const messageTemplate = @json(__('marketing.social_proof.purchased'));
            const anonymousName = @json(__('marketing.social_proof.anonymous_name'));
            const toast = document.getElementById('recent-purchase-toast');
            const message = document.getElementById('recent-purchase-message');
            const close = document.getElementById('recent-purchase-close');
            if (!toast || !message || !close || !Array.isArray(purchases) || purchases.length === 0) return;

            try {
                if (sessionStorage.getItem('opplex_recent_purchases_dismissed') === '1') return;
            } catch (e) {}

            let queue = shuffle(purchases.slice());
            let index = 0;
            let showTimer = null;
            let hideTimer = null;
            let stopped = false;

            function shuffle(items) {
                for (let i = items.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [items[i], items[j]] = [items[j], items[i]];
                }
                return items;
            }

            function blocked() {
                return document.hidden
                    || Boolean(window.__activeMarketingPrompt)
                    || document.body.classList.contains('cookie-consent-open')
                    || document.body.classList.contains('whatsapp-lead-capture-open')
                    || document.body.classList.contains('contact-number-notice-open')
                    || Boolean(document.querySelector('.modal.show, .mfp-wrap.mfp-ready'));
            }

            function schedule(delay) {
                window.clearTimeout(showTimer);
                if (!stopped) showTimer = window.setTimeout(showNext, delay);
            }

            function hide(scheduleAnother) {
                window.clearTimeout(hideTimer);
                toast.classList.remove('is-visible');
                window.setTimeout(function () {
                    if (!toast.classList.contains('is-visible')) toast.hidden = true;
                }, 300);

                if (scheduleAnother && !stopped) {
                    schedule(10000 + Math.floor(Math.random() * 6000));
                }
            }

            function showNext() {
                if (stopped) return;
                if (blocked()) {
                    schedule(3000);
                    return;
                }

                if (index >= queue.length) {
                    queue = shuffle(purchases.slice());
                    index = 0;
                }

                const purchase = queue[index++];
                const name = purchase && purchase.name ? purchase.name : anonymousName;
                const packageName = purchase && purchase.package ? purchase.package : '';
                if (!packageName) {
                    schedule(1000);
                    return;
                }

                message.textContent = messageTemplate
                    .replace(':name', function () { return name; })
                    .replace(':package', function () { return packageName; });
                toast.hidden = false;
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(function () {
                        toast.classList.add('is-visible');
                    });
                });
                hideTimer = window.setTimeout(function () { hide(true); }, 5500);
            }

            close.addEventListener('click', function () {
                stopped = true;
                window.clearTimeout(showTimer);
                window.clearTimeout(hideTimer);
                try {
                    sessionStorage.setItem('opplex_recent_purchases_dismissed', '1');
                } catch (e) {}
                hide(false);
            });

            document.addEventListener('visibilitychange', function () {
                if (!document.hidden && !stopped && toast.hidden) schedule(1000);
            });

            schedule(5000);
        })();
    </script>
@endif
