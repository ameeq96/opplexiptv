<style>
    .cc-banner {
        position: fixed;
        z-index: 2147482000;
        inset-inline: 16px auto;
        bottom: 16px;
        width: min(760px, calc(100% - 32px));
        color: #0b1637;
        background: #fff;
        border: 1px solid #dce3ef;
        border-radius: 8px;
        box-shadow: 0 12px 34px rgba(11, 22, 55, .18);
        font-family: inherit;
    }
    .cc-banner[hidden], .cc-settings[hidden] { display: none !important; }
    .cc-inner {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 14px 16px;
    }
    .cc-copy { flex: 1 1 360px; min-width: 0; }
    .cc-title { display: block; margin-bottom: 3px; font-size: 15px; line-height: 1.3; }
    .cc-copy p { margin: 0; color: #44506b; font-size: 13px; line-height: 1.45; }
    .cc-copy a { color: #153fbc; text-decoration: underline; text-underline-offset: 2px; }
    .cc-actions { display: flex; flex: 0 1 auto; gap: 8px; }
    .cc-button, .cc-settings {
        min-height: 40px;
        padding: 9px 13px;
        border: 1px solid #cbd4e4;
        border-radius: 6px;
        font: 700 13px/1.2 inherit;
        letter-spacing: 0;
        cursor: pointer;
    }
    .cc-button--essential { color: #15203d; background: #fff; }
    .cc-button--allow { color: #fff; background: #e11b22; border-color: #e11b22; }
    .cc-button:focus-visible, .cc-settings:focus-visible { outline: 3px solid rgba(21, 63, 188, .3); outline-offset: 2px; }
    .cc-settings {
        position: fixed;
        z-index: 2147481000;
        left: 12px;
        bottom: 12px;
        min-height: 36px;
        padding: 8px 11px;
        color: #15203d;
        background: #fff;
        box-shadow: 0 5px 16px rgba(11, 22, 55, .14);
    }
    body.cookie-consent-open .whatsapp-icon,
    body.cookie-consent-open .scroll-to-top,
    body.cookie-consent-open #voice-assistant,
    body.cookie-consent-open #va-onboarding,
    body.cookie-consent-open #dw-overlay { display: none !important; }
    @media (max-width: 680px) {
        .cc-banner { inset-inline: 8px; bottom: 8px; width: auto; }
        .cc-inner { align-items: stretch; flex-direction: column; gap: 11px; padding: 13px; }
        .cc-copy { flex-basis: auto; }
        .cc-actions { display: grid; grid-template-columns: 1fr; }
        .cc-button { width: 100%; }
    }
</style>

<section id="cookie-consent" class="cc-banner" role="region"
    aria-label="{{ __('cookie_consent.aria_label') }}" hidden>
    <div class="cc-inner">
        <div class="cc-copy">
            <strong class="cc-title">{{ __('cookie_consent.title') }}</strong>
            <p>
                {{ __('cookie_consent.description') }}
                <a href="{{ route('privacy-policy') }}">{{ __('cookie_consent.privacy') }}</a>
            </p>
        </div>
        <div class="cc-actions">
            <button id="cookie-essential" class="cc-button cc-button--essential" type="button">
                {{ __('cookie_consent.essential_only') }}
            </button>
            <button id="cookie-allow" class="cc-button cc-button--allow" type="button">
                {{ __('cookie_consent.allow_all') }}
            </button>
        </div>
    </div>
</section>

<button id="cookie-settings" class="cc-settings" type="button" hidden>
    {{ __('cookie_consent.settings') }}
</button>

<script>
    (function (w, d) {
        var storageKey = 'opplex.cookieConsent';
        var cookieName = 'opplex_consent';
        var version = w.__trackingConsentVersion || '2026-08-29.1';
        var banner = d.getElementById('cookie-consent');
        var settings = d.getElementById('cookie-settings');
        var essentialButton = d.getElementById('cookie-essential');
        var allowButton = d.getElementById('cookie-allow');
        var current = { analytics: false, marketing: false };

        function normalize(value) {
            if (!value || value.version !== version) return null;
            if (typeof value.analytics !== 'boolean' || typeof value.marketing !== 'boolean') return null;
            return {
                version: version,
                analytics: value.analytics,
                marketing: value.marketing,
                updatedAt: typeof value.updatedAt === 'string' ? value.updatedAt : null
            };
        }

        function readPreference() {
            try {
                var localValue = normalize(JSON.parse(w.localStorage.getItem(storageKey) || 'null'));
                if (localValue) return localValue;
            } catch (e) {}

            var match = d.cookie.match(/(?:^|; )opplex_consent=([^;]*)/);
            if (!match) return null;
            try { return normalize(JSON.parse(decodeURIComponent(match[1]))); } catch (e) { return null; }
        }

        function writeCookie(preference) {
            var serverValue = {
                version: preference.version,
                analytics: preference.analytics,
                marketing: preference.marketing
            };
            var secure = w.location.protocol === 'https:' ? '; Secure' : '';
            d.cookie = cookieName + '=' + encodeURIComponent(JSON.stringify(serverValue))
                + '; Path=/; Max-Age=31536000; SameSite=Lax' + secure;
        }

        function savePreference(analytics, marketing) {
            var preference = {
                version: version,
                analytics: analytics,
                marketing: marketing,
                updatedAt: new Date().toISOString()
            };
            try { w.localStorage.setItem(storageKey, JSON.stringify(preference)); } catch (e) {}
            writeCookie(preference);
            return preference;
        }

        function clearTrackingCookies() {
            d.cookie.split(';').forEach(function (part) {
                var name = part.split('=')[0].trim();
                if (!/^(_ga($|_)|_gid$|_gat($|_)|_gcl_au$|_clck$|_clsk$|_fbp$|_fbc$)/.test(name)) return;
                d.cookie = name + '=; Path=/; Max-Age=0; SameSite=Lax';
                if (w.location.hostname) {
                    d.cookie = name + '=; Path=/; Domain=' + w.location.hostname + '; Max-Age=0; SameSite=Lax';
                    d.cookie = name + '=; Path=/; Domain=.' + w.location.hostname + '; Max-Age=0; SameSite=Lax';
                }
            });
        }

        function openBanner() {
            banner.hidden = false;
            settings.hidden = true;
            d.body.classList.add('cookie-consent-open');
            w.__activeMarketingPrompt = 'cookie-consent';
        }

        function closeBanner() {
            banner.hidden = true;
            settings.hidden = true;
            d.body.classList.remove('cookie-consent-open');
            if (w.__activeMarketingPrompt === 'cookie-consent') w.__activeMarketingPrompt = null;
        }

        function choose(analytics, marketing) {
            var shouldReload = (current.analytics || current.marketing) && !analytics && !marketing;
            current = savePreference(analytics, marketing);
            if (typeof w.__applyTrackingConsent === 'function') w.__applyTrackingConsent(current);
            var csrf = d.querySelector('meta[name="csrf-token"]');
            if (csrf) {
                fetch(@json(route('tracking.consent')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    keepalive: true,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf.content
                    },
                    body: JSON.stringify({ analytics: analytics, marketing: marketing })
                }).catch(function () {});
            }
            if (!analytics && !marketing) clearTrackingCookies();
            closeBanner();
            if (shouldReload) w.location.reload();
        }

        essentialButton.addEventListener('click', function () { choose(false, false); });
        allowButton.addEventListener('click', function () { choose(true, true); });
        settings.addEventListener('click', openBanner);

        var saved = readPreference();
        if (saved) {
            current = saved;
            try { w.localStorage.setItem(storageKey, JSON.stringify(saved)); } catch (e) {}
            writeCookie(saved);
            if (typeof w.__applyTrackingConsent === 'function') w.__applyTrackingConsent(saved);
            closeBanner();
        } else {
            if (typeof w.__applyTrackingConsent === 'function') w.__applyTrackingConsent(current);
            openBanner();
        }
    })(window, document);
</script>
