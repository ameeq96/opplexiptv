@php
    $whatsappLeadCaptureIsRtl = in_array(app()->getLocale(), ['ar', 'ur'], true);
    $whatsappLeadPhoneCountries = collect(\Nakanakaii\Countries\Countries::all())
        ->filter(static fn (array $country): bool => preg_match('/^\d+$/', (string) ($country['dialCode'] ?? '')) === 1)
        ->sortBy(static fn (array $country): string => (($country['regionCode'] ?? '') === '' ? '0' : '1')
            . (string) ($country['name'] ?? ''))
        ->unique('code')
        ->sortBy(static fn (array $country): string => (string) ($country['name'] ?? ''))
        ->values();
@endphp

<style>
    body.whatsapp-lead-capture-open {
        overflow: hidden !important;
    }

    body.whatsapp-lead-capture-open #contact-number-notice,
    body.whatsapp-lead-capture-open #cookie-consent,
    body.whatsapp-lead-capture-open #cookie-settings,
    body.whatsapp-lead-capture-open #dw-overlay,
    body.whatsapp-lead-capture-open #voice-assistant,
    body.whatsapp-lead-capture-open #va-onboarding,
    body.whatsapp-lead-capture-open .whatsapp-icon,
    body.whatsapp-lead-capture-open .scroll-to-top {
        visibility: hidden !important;
    }

    .whatsapp-lead-capture[hidden] {
        display: none !important;
    }

    .whatsapp-lead-capture {
        position: fixed;
        inset: 0;
        z-index: 2147483600;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(1, 12, 58, .72);
        font-family: Poppins, Arial, sans-serif;
    }

    .whatsapp-lead-capture *,
    .whatsapp-lead-capture *::before,
    .whatsapp-lead-capture *::after {
        box-sizing: border-box;
    }

    .whatsapp-lead-capture__dialog {
        position: relative;
        width: min(100%, 520px);
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        padding: 34px;
        border: 1px solid rgba(1, 12, 58, .12);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(1, 12, 58, .28);
        color: #010c3a;
    }

    .whatsapp-lead-capture__close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 50%;
        background: #f1f4f9;
        color: #010c3a;
        cursor: pointer;
        font-size: 26px;
        line-height: 36px;
    }

    [dir="rtl"] .whatsapp-lead-capture__close {
        right: auto;
        left: 14px;
    }

    .whatsapp-lead-capture__eyebrow {
        margin: 0 0 5px;
        color: #16a34a;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .whatsapp-lead-capture__title {
        margin: 0 42px 8px 0;
        color: #010c3a;
        font-size: 27px;
        line-height: 1.25;
    }

    [dir="rtl"] .whatsapp-lead-capture__title {
        margin-right: 0;
        margin-left: 42px;
    }

    .whatsapp-lead-capture__body {
        margin: 0 0 22px;
        color: #4b5563;
        font-size: 15px;
        line-height: 1.65;
    }

    .whatsapp-lead-capture__field {
        margin-bottom: 17px;
    }

    .whatsapp-lead-capture__label {
        display: block;
        margin-bottom: 7px;
        color: #010c3a;
        font-size: 14px;
        font-weight: 700;
    }

    .whatsapp-lead-capture__input,
    .whatsapp-lead-capture__country {
        display: block;
        width: 100%;
        min-height: 48px;
        padding: 11px 13px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #111827;
        font: inherit;
        line-height: 1.45;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .whatsapp-lead-capture__phone {
        display: grid;
        grid-template-columns: clamp(116px, 40%, 142px) minmax(0, 1fr);
        gap: 0;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        direction: ltr;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .whatsapp-lead-capture__country {
        min-width: 0;
        padding-right: 8px;
        padding-left: 45px;
        border: 0;
        border-radius: 0;
        background: transparent;
        font-size: 13px;
        cursor: pointer;
    }

    .whatsapp-lead-capture__country-control {
        position: relative;
        min-width: 0;
        border-right: 1px solid #cbd5e1;
    }

    .whatsapp-lead-capture__country-flag {
        position: absolute;
        top: 50%;
        left: 12px;
        z-index: 1;
        width: 24px;
        height: 18px;
        border: 1px solid rgba(15, 23, 42, .12);
        border-radius: 2px;
        object-fit: cover;
        pointer-events: none;
        transform: translateY(-50%);
    }

    .whatsapp-lead-capture__country-control > .select2-container {
        display: block;
        width: 100% !important;
        font: inherit;
    }

    .whatsapp-lead-capture__country-control > .select2-container--default .select2-selection--single {
        height: 47px;
        border: 0;
        border-radius: 0;
        background: transparent;
    }

    .whatsapp-lead-capture__country-control > .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-right: 28px;
        padding-left: 45px;
        color: #111827;
        font-size: 13px;
        line-height: 47px;
    }

    .whatsapp-lead-capture__country-control > .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 0;
        right: 3px;
        height: 47px;
    }

    .whatsapp-lead-capture__dialog .select2-dropdown {
        width: min(300px, calc(100vw - 48px)) !important;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #111827;
        font-family: inherit;
    }

    .whatsapp-lead-capture__dialog .select2-search--dropdown {
        padding: 8px;
    }

    .whatsapp-lead-capture__dialog .select2-container--default .select2-search--dropdown .select2-search__field {
        height: 38px;
        padding: 7px 9px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        font: inherit;
    }

    .whatsapp-lead-capture__dialog .select2-results__option {
        padding: 8px 10px;
        font-size: 13px;
    }

    .whatsapp-lead-capture__dialog .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #16a34a;
        color: #fff;
    }

    .whatsapp-lead-capture__country-result {
        display: flex;
        gap: 9px;
        align-items: center;
        min-width: 0;
    }

    .whatsapp-lead-capture__country-result img {
        flex: 0 0 auto;
        width: 24px;
        height: 18px;
        border: 1px solid rgba(15, 23, 42, .12);
        border-radius: 2px;
        object-fit: cover;
    }

    .whatsapp-lead-capture__country-result span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .whatsapp-lead-capture__phone .whatsapp-lead-capture__input {
        min-width: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
    }

    .whatsapp-lead-capture__input:focus,
    .whatsapp-lead-capture__country:focus {
        border-color: #16a34a;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, .16);
    }

    .whatsapp-lead-capture__phone:focus-within {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, .16);
    }

    .whatsapp-lead-capture__phone .whatsapp-lead-capture__input:focus,
    .whatsapp-lead-capture__phone .whatsapp-lead-capture__country:focus {
        border-right-color: #cbd5e1;
        box-shadow: none;
    }

    .whatsapp-lead-capture__phone:has(.whatsapp-lead-capture__input[aria-invalid="true"]) {
        border-color: #dc2626;
    }

    .whatsapp-lead-capture__phone:has(.whatsapp-lead-capture__input[aria-invalid="true"]):focus-within {
        box-shadow: 0 0 0 3px rgba(220, 38, 38, .14);
    }

    .whatsapp-lead-capture__input[aria-invalid="true"] {
        border-color: #dc2626;
    }

    .whatsapp-lead-capture__help,
    .whatsapp-lead-capture__field-error {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        line-height: 1.45;
    }

    .whatsapp-lead-capture__help {
        color: #64748b;
    }

    .whatsapp-lead-capture__field-error,
    .whatsapp-lead-capture__error {
        color: #b91c1c;
    }

    .whatsapp-lead-capture__field-error:empty,
    .whatsapp-lead-capture__error:empty {
        display: none;
    }

    .whatsapp-lead-capture__consent {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin: 2px 0 8px;
        color: #334155;
        cursor: pointer;
        font-size: 13px;
        line-height: 1.55;
    }

    .whatsapp-lead-capture__consent input {
        flex: 0 0 auto;
        width: 18px;
        height: 18px;
        margin: 2px 0 0;
        accent-color: #16a34a;
    }

    .whatsapp-lead-capture__privacy {
        display: inline-block;
        margin-bottom: 17px;
        color: #010c3a;
        font-size: 13px;
        font-weight: 600;
        text-decoration: underline;
    }

    .whatsapp-lead-capture__error {
        margin: 0 0 14px;
        padding: 10px 12px;
        border-radius: 8px;
        background: #fef2f2;
        font-size: 13px;
        line-height: 1.5;
    }

    .whatsapp-lead-capture__actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .whatsapp-lead-capture__button {
        min-height: 46px;
        padding: 10px 17px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #010c3a;
        cursor: pointer;
        font: inherit;
        font-size: 14px;
        font-weight: 700;
    }

    .whatsapp-lead-capture__button--primary {
        flex: 1 1 auto;
        border-color: #16a34a;
        background: #16a34a;
        color: #fff;
    }

    .whatsapp-lead-capture__button:hover:not(:disabled),
    .whatsapp-lead-capture__button:focus-visible:not(:disabled),
    .whatsapp-lead-capture__close:hover:not(:disabled),
    .whatsapp-lead-capture__close:focus-visible:not(:disabled) {
        filter: brightness(.94);
    }

    .whatsapp-lead-capture__button:disabled,
    .whatsapp-lead-capture__close:disabled,
    .whatsapp-lead-capture__input:disabled,
    .whatsapp-lead-capture__country:disabled,
    .whatsapp-lead-capture__consent input:disabled {
        cursor: wait;
        opacity: .65;
    }

    @media (max-width: 575px) {
        .whatsapp-lead-capture {
            align-items: flex-end;
            padding: 0;
        }

        .whatsapp-lead-capture__dialog {
            width: 100%;
            max-height: calc(100vh - 12px);
            padding: 28px 20px 20px;
            border-radius: 18px 18px 0 0;
        }

        .whatsapp-lead-capture__title {
            font-size: 23px;
        }

        .whatsapp-lead-capture__actions {
            flex-direction: column-reverse;
        }

        .whatsapp-lead-capture__button {
            width: 100%;
        }
    }
</style>

<div id="whatsapp-lead-capture" class="whatsapp-lead-capture" hidden
    aria-hidden="true" dir="{{ $whatsappLeadCaptureIsRtl ? 'rtl' : 'ltr' }}">
    <div class="whatsapp-lead-capture__dialog" role="dialog" aria-modal="true"
        aria-labelledby="whatsapp-lead-capture-title" aria-describedby="whatsapp-lead-capture-body">
        <button type="button" class="whatsapp-lead-capture__close" data-whatsapp-lead-close
            aria-label="{{ __('interface.whatsapp_lead_capture.close_label') }}">&times;</button>

        <p class="whatsapp-lead-capture__eyebrow">{{ __('interface.whatsapp_lead_capture.eyebrow') }}</p>
        <h2 id="whatsapp-lead-capture-title" class="whatsapp-lead-capture__title">
            {{ __('interface.whatsapp_lead_capture.title') }}
        </h2>
        <p id="whatsapp-lead-capture-body" class="whatsapp-lead-capture__body">
            {{ __('interface.whatsapp_lead_capture.body') }}
        </p>

        <form class="whatsapp-lead-capture__form" novalidate>
            <div class="whatsapp-lead-capture__field">
                <label class="whatsapp-lead-capture__label" for="whatsapp-lead-name">
                    {{ __('interface.whatsapp_lead_capture.name_label') }}
                </label>
                <input id="whatsapp-lead-name" class="whatsapp-lead-capture__input" type="text" name="name"
                    minlength="2" maxlength="120" autocomplete="name"
                    placeholder="{{ __('interface.whatsapp_lead_capture.name_placeholder') }}"
                    aria-describedby="whatsapp-lead-name-error" aria-invalid="false" required>
                <span id="whatsapp-lead-name-error" class="whatsapp-lead-capture__field-error"></span>
            </div>

            <div class="whatsapp-lead-capture__field">
                <label class="whatsapp-lead-capture__label" for="whatsapp-lead-phone">
                    {{ __('interface.whatsapp_lead_capture.phone_label') }}
                </label>
                <div class="whatsapp-lead-capture__phone" dir="ltr">
                    <div class="whatsapp-lead-capture__country-control">
                        <img class="whatsapp-lead-capture__country-flag" data-whatsapp-lead-country-flag
                            src="https://flagcdn.io/flags/4x3/pk.svg" alt="" width="24" height="18"
                            decoding="async" referrerpolicy="no-referrer" aria-hidden="true">
                        <select id="whatsapp-lead-country" class="whatsapp-lead-capture__country" name="dial_code"
                            autocomplete="tel-country-code" aria-label="{{ __('interface.phone.country_list_aria') }}"
                            aria-describedby="whatsapp-lead-phone-help whatsapp-lead-phone-error" required>
                            @foreach ($whatsappLeadPhoneCountries as $country)
                                @php($countryCode = strtoupper((string) ($country['code'] ?? '')))
                                <option value="{{ $countryCode }}" data-country-code="{{ $countryCode }}"
                                    data-country-name="{{ $country['name'] }}" data-dial-code="{{ $country['dialCode'] }}"
                                    data-min-digits="{{ $country['minLength'] }}" data-max-digits="{{ $country['maxLength'] }}"
                                    @if (in_array($countryCode, ['IT', 'VA'], true)) data-preserve-leading-zero="true" @endif
                                    @selected($countryCode === 'PK')>
                                    {{ $country['name'] }} ({{ $countryCode }} +{{ $country['dialCode'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <input id="whatsapp-lead-phone" class="whatsapp-lead-capture__input" type="tel" name="phone"
                        maxlength="30" inputmode="tel" autocomplete="tel-national" dir="ltr"
                        placeholder="{{ __('interface.whatsapp_lead_capture.phone_placeholder') }}"
                        aria-describedby="whatsapp-lead-phone-help whatsapp-lead-phone-error" aria-invalid="false" required>
                </div>
                <span id="whatsapp-lead-phone-help" class="whatsapp-lead-capture__help">
                    {{ __('interface.whatsapp_lead_capture.phone_help') }}
                </span>
                <span id="whatsapp-lead-phone-error" class="whatsapp-lead-capture__field-error"></span>
            </div>

            <label class="whatsapp-lead-capture__consent" for="whatsapp-lead-consent">
                <input id="whatsapp-lead-consent" type="checkbox" name="contact_consent"
                    aria-describedby="whatsapp-lead-consent-error" required>
                <span>{{ __('interface.whatsapp_lead_capture.consent') }}</span>
            </label>
            <span id="whatsapp-lead-consent-error" class="whatsapp-lead-capture__field-error"></span>

            <a class="whatsapp-lead-capture__privacy" href="{{ route('privacy-policy') }}" target="_blank" rel="noopener">
                {{ __('interface.whatsapp_lead_capture.privacy') }}
            </a>

            <p class="whatsapp-lead-capture__error" role="alert" aria-live="assertive"></p>

            <div class="whatsapp-lead-capture__actions">
                <button type="button" class="whatsapp-lead-capture__button" data-whatsapp-lead-close>
                    {{ __('interface.whatsapp_lead_capture.cancel') }}
                </button>
                <button type="submit" class="whatsapp-lead-capture__button whatsapp-lead-capture__button--primary">
                    <span data-whatsapp-lead-submit-label>{{ __('interface.whatsapp_lead_capture.continue') }}</span>
                    <span data-whatsapp-lead-saving-label hidden>{{ __('interface.whatsapp_lead_capture.saving') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const root = document.getElementById('whatsapp-lead-capture');
        if (!root) return;

        const dialog = root.querySelector('.whatsapp-lead-capture__dialog');
        const form = root.querySelector('form');
        const nameInput = form.elements.name;
        const phoneInput = form.elements.phone;
        const countryInput = form.elements.dial_code;
        const countryFlag = root.querySelector('[data-whatsapp-lead-country-flag]');
        const consentInput = form.elements.contact_consent;
        const nameError = document.getElementById('whatsapp-lead-name-error');
        const phoneError = document.getElementById('whatsapp-lead-phone-error');
        const consentError = document.getElementById('whatsapp-lead-consent-error');
        const formError = root.querySelector('.whatsapp-lead-capture__error');
        const submitButton = form.querySelector('[type="submit"]');
        const submitLabel = form.querySelector('[data-whatsapp-lead-submit-label]');
        const savingLabel = form.querySelector('[data-whatsapp-lead-saving-label]');
        const closeButtons = root.querySelectorAll('[data-whatsapp-lead-close]');
        const messages = {
            invalidName: @json(__('interface.whatsapp_lead_capture.invalid_name')),
            invalidPhone: @json(__('interface.whatsapp_lead_capture.invalid_phone')),
            consentRequired: @json(__('interface.whatsapp_lead_capture.consent_required')),
            requestError: @json(__('interface.whatsapp_lead_capture.error'))
        };
        let activeRequest = null;
        let lastFocusedElement = null;
        let previousMarketingPrompt = null;
        let busy = false;
        let countryManuallyChanged = false;
        let countryDetectionPromise = null;
        let countrySelect2Promise = null;
        let countrySelect2Jquery = null;
        let preferredCountryCode = countryInput.options[countryInput.selectedIndex]?.dataset.countryCode || 'PK';
        const dialOptions = Array.from(countryInput.options).sort(function (left, right) {
            return String(right.dataset.dialCode || '').length - String(left.dataset.dialCode || '').length;
        });

        function dialCodeFor(option) {
            return option ? String(option.dataset.dialCode || '') : '';
        }

        function syncCountrySelect2() {
            if (!countrySelect2Jquery || !countrySelect2Jquery(countryInput).data('select2')) return;
            countrySelect2Jquery(countryInput).val(countryInput.value).trigger('change.select2');
        }

        function selectCountry(countryCode) {
            const normalizedCode = String(countryCode || '').toUpperCase();
            const option = Array.from(countryInput.options).find(function (candidate) {
                return candidate.dataset.countryCode === normalizedCode;
            });
            if (!option) return false;
            countryInput.value = normalizedCode;
            preferredCountryCode = normalizedCode;
            if (countryFlag) {
                countryFlag.hidden = false;
                countryFlag.src = 'https://flagcdn.io/flags/4x3/' + normalizedCode.toLowerCase() + '.svg';
            }
            syncCountrySelect2();
            return true;
        }

        function browserCountryCode() {
            try {
                if (Intl.DateTimeFormat().resolvedOptions().timeZone === 'Asia/Karachi') return 'PK';
            } catch (error) {}

            const languages = Array.isArray(navigator.languages) && navigator.languages.length
                ? navigator.languages
                : [navigator.language || ''];

            for (const language of languages) {
                const parts = String(language).split(/[-_]/);
                for (let index = parts.length - 1; index > 0; index -= 1) {
                    if (/^[a-z]{2}$/i.test(parts[index])) return parts[index].toUpperCase();
                }
            }

            const languageDefaults = {
                ar: 'AE', es: 'ES', fr: 'FR', hi: 'IN', it: 'IT', nl: 'NL',
                pt: 'PT', ru: 'RU', ur: 'PK'
            };
            const siteLanguage = String(document.documentElement.lang || '').split(/[-_]/)[0].toLowerCase();
            return languageDefaults[siteLanguage] || 'PK';
        }

        function loadCountryStylesheet(href) {
            const absoluteHref = new URL(href, document.baseURI).href;
            const existing = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).find(function (link) {
                return link.href === absoluteHref;
            });
            if (existing) return Promise.resolve();

            return new Promise(function (resolve, reject) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = absoluteHref;
                link.addEventListener('load', resolve, { once: true });
                link.addEventListener('error', reject, { once: true });
                document.head.appendChild(link);
            });
        }

        function loadCountryScript(id, src) {
            const existing = document.getElementById(id);
            if (existing) {
                if (existing.dataset.loaded === 'true') return Promise.resolve();
                return new Promise(function (resolve, reject) {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                });
            }

            return new Promise(function (resolve, reject) {
                const script = document.createElement('script');
                script.id = id;
                script.src = src;
                script.async = true;
                script.addEventListener('load', function () {
                    script.dataset.loaded = 'true';
                    resolve();
                }, { once: true });
                script.addEventListener('error', reject, { once: true });
                document.head.appendChild(script);
            });
        }

        function formatCountryResult(state) {
            if (!state.element || !countrySelect2Jquery) return state.text;

            const option = state.element;
            const countryCode = String(option.dataset.countryCode || '').toUpperCase();
            const row = document.createElement('span');
            const flag = document.createElement('img');
            const label = document.createElement('span');

            row.className = 'whatsapp-lead-capture__country-result';
            flag.src = 'https://flagcdn.io/flags/4x3/' + countryCode.toLowerCase() + '.svg';
            flag.alt = '';
            flag.width = 24;
            flag.height = 18;
            flag.loading = 'lazy';
            flag.referrerPolicy = 'no-referrer';
            flag.setAttribute('aria-hidden', 'true');
            flag.addEventListener('error', function () {
                flag.hidden = true;
            });
            label.textContent = String(option.dataset.countryName || state.text)
                + ' (' + countryCode + ' +' + dialCodeFor(option) + ')';
            row.append(flag, label);

            return countrySelect2Jquery(row);
        }

        function formatCountrySelection(state) {
            if (!state.element) return state.text;
            return String(state.element.dataset.countryCode || '').toUpperCase()
                + ' +' + dialCodeFor(state.element);
        }

        function syncCountrySelect2Accessibility(country) {
            const selection = country.next('.select2').find('.select2-selection');
            const ariaLabel = countryInput.getAttribute('aria-label');
            const ariaDescribedBy = countryInput.getAttribute('aria-describedby');

            selection.removeAttr('aria-labelledby');
            if (ariaLabel) selection.attr('aria-label', ariaLabel);
            if (ariaDescribedBy) selection.attr('aria-describedby', ariaDescribedBy);
        }

        function initializeCountrySelect2() {
            if (!countrySelect2Jquery || !countrySelect2Jquery.fn.select2) return;

            const country = countrySelect2Jquery(countryInput);
            if (country.data('select2')) {
                syncCountrySelect2Accessibility(country);
                syncCountrySelect2();
                return;
            }

            country.select2({
                width: '100%',
                dir: 'ltr',
                dropdownParent: countrySelect2Jquery(dialog),
                dropdownAutoWidth: true,
                minimumResultsForSearch: 0,
                templateResult: formatCountryResult,
                templateSelection: formatCountrySelection
            });
            country.on('select2:select.whatsappLead', rememberCountrySelection);
            syncCountrySelect2Accessibility(country);
            syncCountrySelect2();
        }

        function ensureCountrySelect2() {
            if (countrySelect2Jquery && countrySelect2Jquery.fn.select2) {
                initializeCountrySelect2();
                return Promise.resolve();
            }
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                countrySelect2Jquery = window.jQuery;
                initializeCountrySelect2();
                return Promise.resolve();
            }
            if (countrySelect2Promise) return countrySelect2Promise;

            const hadJquery = Boolean(window.jQuery);
            const stylesheet = loadCountryStylesheet(
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ).then(function () {
                return true;
            }).catch(function () {
                return false;
            });
            const scripts = (window.jQuery
                ? Promise.resolve()
                : loadCountryScript('whatsapp-lead-jquery', @json(asset('js/jquery.js')))
            ).then(function () {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) return;
                return loadCountryScript(
                    'whatsapp-lead-select2',
                    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
                );
            }).then(function () {
                return true;
            }).catch(function () {
                return false;
            });

            countrySelect2Promise = Promise.all([stylesheet, scripts]).then(function (assetsLoaded) {
                if (!assetsLoaded.every(Boolean)
                    || !window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
                    throw new Error('Select2 is unavailable.');
                }
                countrySelect2Jquery = !hadJquery && typeof window.jQuery.noConflict === 'function'
                    ? window.jQuery.noConflict(true)
                    : window.jQuery;
                initializeCountrySelect2();
            }).catch(function () {
                if (!hadJquery && window.jQuery && typeof window.jQuery.noConflict === 'function') {
                    window.jQuery.noConflict(true);
                }
                countrySelect2Jquery = null;
                return null;
            });

            return countrySelect2Promise;
        }

        function detectVisitorCountry() {
            if (countryDetectionPromise) return countryDetectionPromise;

            countryDetectionPromise = fetch(@json(route('whatsapp.leads.token')), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store'
            }).then(function (response) {
                if (!response.ok) throw new Error('Unable to detect visitor country.');
                return response.json();
            }).then(function (payload) {
                if (!countryManuallyChanged) selectCountry(payload.country_code);
            }).catch(function () {});

            return countryDetectionPromise;
        }

        if (countryFlag) {
            countryFlag.addEventListener('error', function () {
                countryFlag.hidden = true;
            });
        }

        selectCountry(browserCountryCode()) || selectCountry('PK');

        function restoreMarketingPrompt() {
            if (window.__activeMarketingPrompt === 'whatsapp-lead-capture') {
                window.__activeMarketingPrompt = previousMarketingPrompt;
            }
            previousMarketingPrompt = null;
        }

        function setFieldError(input, errorElement, message) {
            input.setAttribute('aria-invalid', message ? 'true' : 'false');
            errorElement.textContent = message || '';
        }

        function clearErrors() {
            setFieldError(nameInput, nameError, '');
            setFieldError(phoneInput, phoneError, '');
            consentError.textContent = '';
            formError.textContent = '';
        }

        function isCountrySelect2Open() {
            return Boolean(countrySelect2Jquery
                && countrySelect2Jquery(countryInput).data('select2')
                && dialog.querySelector('.select2-container--open'));
        }

        function closeCountrySelect2() {
            if (!isCountrySelect2Open()) return;
            countrySelect2Jquery(countryInput).select2('close');
        }

        function rememberCountrySelection() {
            countryManuallyChanged = true;
            preferredCountryCode = selectedCountry()?.dataset.countryCode || preferredCountryCode;
            selectCountry(preferredCountryCode);
            setFieldError(phoneInput, phoneError, '');
        }

        function setBusy(isBusy) {
            busy = isBusy;
            form.setAttribute('aria-busy', isBusy ? 'true' : 'false');
            Array.from(form.elements).forEach(function (control) {
                control.disabled = isBusy;
            });
            closeButtons.forEach(function (button) {
                button.disabled = isBusy;
            });
            submitLabel.hidden = isBusy;
            savingLabel.hidden = !isBusy;
            syncCountrySelect2();
        }

        function close() {
            if (busy || root.hidden) return;
            closeCountrySelect2();
            root.hidden = true;
            root.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('whatsapp-lead-capture-open');
            activeRequest = null;
            restoreMarketingPrompt();
            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }
        }

        function open(options) {
            activeRequest = options || {};
            lastFocusedElement = document.activeElement;
            previousMarketingPrompt = window.__activeMarketingPrompt || null;
            window.__activeMarketingPrompt = 'whatsapp-lead-capture';
            form.reset();
            selectCountry(preferredCountryCode);
            clearErrors();
            setBusy(false);
            root.hidden = false;
            root.setAttribute('aria-hidden', 'false');
            document.body.classList.add('whatsapp-lead-capture-open');
            detectVisitorCountry();
            ensureCountrySelect2();
            window.requestAnimationFrame(function () {
                nameInput.focus();
            });
        }

        function validate() {
            const name = nameInput.value.trim();
            const normalizedPhone = normalizePhone();
            let valid = true;

            clearErrors();

            if (name.length < 2) {
                setFieldError(nameInput, nameError, messages.invalidName);
                valid = false;
            }

            if (!/^[1-9]\d{7,14}$/.test(normalizedPhone) || !hasValidCountryLength(normalizedPhone)) {
                setFieldError(phoneInput, phoneError, messages.invalidPhone);
                valid = false;
            }

            if (!consentInput.checked) {
                consentError.textContent = messages.consentRequired;
                valid = false;
            }

            return valid ? {
                name: name,
                phone: normalizedPhone,
                contactConsent: true
            } : null;
        }

        function selectedCountry() {
            return countryInput.options[countryInput.selectedIndex] || null;
        }

        function findDialOption(normalizedPhone) {
            const digits = String(normalizedPhone || '').replace(/\D/g, '');
            const current = selectedCountry();
            const currentDialCode = dialCodeFor(current);
            if (currentDialCode && digits.startsWith(currentDialCode) && digits.length > currentDialCode.length) {
                return current;
            }
            return dialOptions.find(function (option) {
                const dialCode = dialCodeFor(option);
                return dialCode && digits.startsWith(dialCode) && digits.length > dialCode.length;
            }) || null;
        }

        function normalizePhone() {
            const rawPhone = phoneInput.value.trim();
            if (!rawPhone) return '';
            if (rawPhone.startsWith('+')) return rawPhone.replace(/\D/g, '');
            if (rawPhone.startsWith('00')) return rawPhone.slice(2).replace(/\D/g, '');

            const country = selectedCountry();
            if (!country) return '';
            const dialCode = dialCodeFor(country);
            const nationalDigits = rawPhone.replace(/\D/g, '');
            const minimumDigits = Number.parseInt(country.dataset.minDigits, 10);
            const maximumDigits = Number.parseInt(country.dataset.maxDigits, 10);

            if (nationalDigits.startsWith(dialCode)) {
                const possibleNationalLength = nationalDigits.length - dialCode.length;
                if (possibleNationalLength >= minimumDigits && possibleNationalLength <= maximumDigits) {
                    return nationalDigits;
                }
            }

            const normalizedNational = country.dataset.preserveLeadingZero === 'true'
                ? nationalDigits
                : nationalDigits.replace(/^0+/, '');
            return dialCode + normalizedNational;
        }

        function hasValidCountryLength(normalizedPhone) {
            const rawPhone = phoneInput.value.trim();
            const isInternational = rawPhone.startsWith('+') || rawPhone.startsWith('00');
            const country = isInternational ? findDialOption(normalizedPhone) : selectedCountry();
            if (!country) return true;

            const nationalLength = normalizedPhone.length - dialCodeFor(country).length;
            const minimumDigits = Number.parseInt(country.dataset.minDigits, 10);
            const maximumDigits = Number.parseInt(country.dataset.maxDigits, 10);
            return nationalLength >= minimumDigits && nationalLength <= maximumDigits;
        }

        phoneInput.addEventListener('input', function () {
            setFieldError(phoneInput, phoneError, '');
        });

        phoneInput.addEventListener('blur', function () {
            const rawPhone = phoneInput.value.trim();
            if (!rawPhone.startsWith('+') && !rawPhone.startsWith('00')) return;
            const matchingCountry = findDialOption(normalizePhone());
            if (matchingCountry) {
                countryManuallyChanged = true;
                selectCountry(matchingCountry.dataset.countryCode);
            }
        });

        countryInput.addEventListener('change', rememberCountrySelection);

        closeButtons.forEach(function (button) {
            button.addEventListener('click', close);
        });

        root.addEventListener('click', function (event) {
            if (event.target === root) close();
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (busy || !activeRequest || typeof activeRequest.onSubmit !== 'function') return;

            const values = validate();
            if (!values) {
                const firstInvalid = form.querySelector('[aria-invalid="true"]')
                    || (!consentInput.checked ? consentInput : null);
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            setBusy(true);
            let request;
            try {
                request = activeRequest.onSubmit(values);
            } catch (error) {
                request = Promise.reject(error);
            }

            Promise.resolve(request).then(function () {
                busy = false;
                root.hidden = true;
                root.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('whatsapp-lead-capture-open');
                activeRequest = null;
                restoreMarketingPrompt();
            }).catch(function () {
                setBusy(false);
                formError.textContent = messages.requestError;
                formError.setAttribute('tabindex', '-1');
                formError.focus();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (root.hidden) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopImmediatePropagation();
                if (isCountrySelect2Open()) {
                    closeCountrySelect2();
                    return;
                }
                close();
                return;
            }
            if (event.key !== 'Tab') return;

            const focusable = Array.from(dialog.querySelectorAll(
                'a[href], button:not(:disabled), input:not(:disabled), select:not(:disabled):not(.select2-hidden-accessible), [tabindex]:not([tabindex="-1"])'
            ));
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }, true);

        window.OpplexWhatsAppLeadCapture = { open: open, close: close };
    })();
</script>
