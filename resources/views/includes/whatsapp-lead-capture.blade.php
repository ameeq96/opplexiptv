@php
    $whatsappLeadCaptureIsRtl = in_array(app()->getLocale(), ['ar', 'ur'], true);
    $whatsappLeadPhoneCountries = collect(\Nakanakaii\Countries\Countries::all())
        ->filter(static fn (array $country): bool => preg_match('/^\d+$/', (string) ($country['dialCode'] ?? '')) === 1)
        ->sortBy(static fn (array $country): string => (($country['regionCode'] ?? '') === '' ? '0' : '1')
            . (string) ($country['name'] ?? ''))
        ->unique('code')
        ->sortBy(static fn (array $country): string => (string) ($country['name'] ?? ''))
        ->values();
    $whatsappLeadDetectedCountry = '';

    foreach (['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Vercel-IP-Country'] as $countryHeader) {
        $countryCode = strtoupper(trim((string) request()->header($countryHeader, '')));
        if (preg_match('/^[A-Z]{2}$/', $countryCode) === 1
            && $whatsappLeadPhoneCountries->contains(static fn (array $country): bool => ($country['code'] ?? '') === $countryCode)) {
            $whatsappLeadDetectedCountry = $countryCode;
            break;
        }
    }
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
        border: 0;
        border-right: 1px solid #cbd5e1;
        border-radius: 0;
        background: transparent;
        font-size: 13px;
        cursor: pointer;
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
                    <select id="whatsapp-lead-country" class="whatsapp-lead-capture__country" name="dial_code"
                        autocomplete="tel-country-code" aria-label="{{ __('interface.phone.country_list_aria') }}"
                        aria-describedby="whatsapp-lead-phone-help whatsapp-lead-phone-error" required>
                        @foreach ($whatsappLeadPhoneCountries as $country)
                            @php($countryCode = strtoupper((string) ($country['code'] ?? '')))
                            <option value="{{ $country['dialCode'] }}" data-country-code="{{ $countryCode }}"
                                data-min-digits="{{ $country['minLength'] }}" data-max-digits="{{ $country['maxLength'] }}"
                                @if (in_array($countryCode, ['IT', 'VA'], true)) data-preserve-leading-zero="true" @endif
                                @selected($countryCode === ($whatsappLeadDetectedCountry ?: 'PK'))>
                                {{ $country['flag'] }} {{ $countryCode }} +{{ $country['dialCode'] }}
                            </option>
                        @endforeach
                    </select>
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
        let preferredCountryCode = countryInput.options[countryInput.selectedIndex]?.dataset.countryCode || 'PK';
        const dialOptions = Array.from(countryInput.options).sort(function (left, right) {
            return right.value.length - left.value.length;
        });

        function selectCountry(countryCode) {
            const normalizedCode = String(countryCode || '').toUpperCase();
            const option = Array.from(countryInput.options).find(function (candidate) {
                return candidate.dataset.countryCode === normalizedCode;
            });
            if (!option) return false;
            option.selected = true;
            preferredCountryCode = normalizedCode;
            return true;
        }

        function browserCountryCode() {
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

        selectCountry(@json($whatsappLeadDetectedCountry) || browserCountryCode()) || selectCountry('PK');

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
        }

        function close() {
            if (busy || root.hidden) return;
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
            if (current && digits.startsWith(current.value) && digits.length > current.value.length) return current;
            return dialOptions.find(function (option) {
                return digits.startsWith(option.value) && digits.length > option.value.length;
            }) || null;
        }

        function normalizePhone() {
            const rawPhone = phoneInput.value.trim();
            if (!rawPhone) return '';
            if (rawPhone.startsWith('+')) return rawPhone.replace(/\D/g, '');
            if (rawPhone.startsWith('00')) return rawPhone.slice(2).replace(/\D/g, '');

            const country = selectedCountry();
            if (!country) return '';
            const nationalDigits = rawPhone.replace(/\D/g, '');
            const minimumDigits = Number.parseInt(country.dataset.minDigits, 10);
            const maximumDigits = Number.parseInt(country.dataset.maxDigits, 10);

            if (nationalDigits.startsWith(country.value)) {
                const possibleNationalLength = nationalDigits.length - country.value.length;
                if (possibleNationalLength >= minimumDigits && possibleNationalLength <= maximumDigits) {
                    return nationalDigits;
                }
            }

            const normalizedNational = country.dataset.preserveLeadingZero === 'true'
                ? nationalDigits
                : nationalDigits.replace(/^0+/, '');
            return country.value + normalizedNational;
        }

        function hasValidCountryLength(normalizedPhone) {
            const rawPhone = phoneInput.value.trim();
            const isInternational = rawPhone.startsWith('+') || rawPhone.startsWith('00');
            const country = isInternational ? findDialOption(normalizedPhone) : selectedCountry();
            if (!country) return true;

            const nationalLength = normalizedPhone.length - country.value.length;
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
            if (matchingCountry) selectCountry(matchingCountry.dataset.countryCode);
        });

        countryInput.addEventListener('change', function () {
            preferredCountryCode = selectedCountry()?.dataset.countryCode || preferredCountryCode;
            setFieldError(phoneInput, phoneError, '');
        });

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
                close();
                return;
            }
            if (event.key !== 'Tab') return;

            const focusable = Array.from(dialog.querySelectorAll(
                'a[href], button:not(:disabled), input:not(:disabled), select:not(:disabled), [tabindex]:not([tabindex="-1"])'
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
