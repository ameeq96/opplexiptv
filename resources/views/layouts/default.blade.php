@php
    $layoutIsRtl = $isRtl ?? in_array(app()->getLocale(), ['ar', 'ur'], true);
    $layoutSuppressDisruptivePrompts = request()->routeIs(
        'configure',
        'checkout',
        'thankyou',
        'customer.*',
        'orders.status',
        'order-status*'
    );
@endphp
<!DOCTYPE html>
<html class="loading" lang="{{ app()->getLocale() }}"
    dir="{{ $layoutIsRtl ? 'rtl' : 'ltr' }}"
    data-textdirection="{{ $layoutIsRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('includes.head')
    <script src="{{ asset('js/api-browser.js') }}" defer></script>
</head>

<body>
    @include('includes.cookie-consent')

    @include('includes.whatsapp-lead-capture')

    @include('includes.header')

    <div class="body_wrap">
        @yield('content')
    </div>

    @include('includes.footer')

    @unless ($layoutSuppressDisruptivePrompts)
        @include('includes.contact-number-notice')
    @endunless

    @include('includes.voice-assistant')

    @unless ($layoutSuppressDisruptivePrompts)
        @include('includes.recent-purchase-popup')
    @endunless

    <a href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('messages.whatsapp_explore')) }}" target="_blank"
        class="whatsapp-icon" title="{{ __('document_ui.footer.contact') }} — WhatsApp">
        <img src="{{ asset('images/whatsapp-img-small.webp') }}" alt="WhatsApp" width="60" height="60"
            decoding="async" />
    </a>

    @hasSection('native-shell')
        @yield('native-shell')
        @vite('resources/js/native-shell.js')
    @endif

    @yield('script')

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                var s = document.createElement('script');
                s.src = "{{ Vite::asset('resources/js/voice-assistant.js') }}";
                s.type = 'module';
                s.defer = true;
                document.body.appendChild(s);
            }, 4000);
        });
    </script>

    {{-- Per-page JSON-LD (breadcrumbs, FAQ, HowTo, Service, page-type nodes).
         Placed at end of <body> so @push('schema') from page top-level, components
         (x-page-title) and includes (_faq-section) are all captured. --}}
    @stack('schema')

        <!--Start of Tawk.to Script-->
        <style>
            .mobile-support-launcher { display: none; }
            @media (max-width: 768px) {
                .mobile-support-launcher:not([hidden]) {
                    display: inline-flex; position: fixed; right: 12px;
                    bottom: calc(12px + env(safe-area-inset-bottom, 0px)); z-index: 1040;
                    align-items: center; justify-content: center; width: 56px; height: 56px;
                    padding: 0; border: 2px solid #fff; border-radius: 50%;
                    color: #fff; background: #15803d; box-shadow: 0 5px 18px #0003; cursor: pointer;
                }
                .mobile-support-launcher:focus-visible { outline: 3px solid #2563eb; outline-offset: 3px; }
                .mobile-support-launcher:disabled { cursor: wait; opacity: .75; }
            }
        </style>
        <button id="mobileSupportLauncher" class="mobile-support-launcher" type="button"
            aria-label="Open live chat" aria-haspopup="dialog" aria-expanded="false">
            <svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 0 0-7.6 13.8L3 21l4.5-1.3A9 9 0 1 0 12 3Zm-4 8h8v2H8v-2Z" /></svg>
        </button>
        <span id="mobileSupportStatus" class="sr-only" role="status" aria-live="polite"></span>
        <script type="text/javascript">
            var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
            Tawk_API.customStyle = {
                visibility: {
                    desktop: { position: 'br', xOffset: 20, yOffset: 20 },
                    mobile: { position: 'br', xOffset: 10, yOffset: 10 }
                }
            };
            (function () {
                var pending = Promise.resolve();
                var csrfToken = '';
                var mobileChat = window.matchMedia('(max-width: 768px)');
                var launcher = document.getElementById('mobileSupportLauncher');
                var chatStatus = document.getElementById('mobileSupportStatus');
                var widgetRequested = false;
                var widgetReady = false;
                var widgetLoading = false;
                var widgetTimer;

                function closeMobileChat() {
                    if (!mobileChat.matches) return;
                    widgetRequested = false;
                    Tawk_API.hideWidget();
                    launcher.hidden = false;
                    launcher.setAttribute('aria-expanded', 'false');
                }

                Tawk_API.onLoad = function () {
                    widgetReady = true;
                    widgetLoading = false;
                    clearTimeout(widgetTimer);
                    launcher.disabled = false;
                    chatStatus.textContent = '';
                    if (mobileChat.matches && widgetRequested) {
                        Tawk_API.showWidget();
                        Tawk_API.maximize();
                    } else if (mobileChat.matches) {
                        closeMobileChat();
                    }
                };
                Tawk_API.onChatMaximized = function () {
                    if (!mobileChat.matches) return;
                    if (!widgetRequested) {
                        Tawk_API.minimize();
                        closeMobileChat();
                        return;
                    }
                    launcher.hidden = true;
                    launcher.setAttribute('aria-expanded', 'true');
                };
                Tawk_API.onChatMinimized = function () {
                    if (!mobileChat.matches) return;
                    closeMobileChat();
                    launcher.focus();
                };
                launcher.addEventListener('click', function () {
                    widgetRequested = true;
                    if (widgetReady) {
                        Tawk_API.showWidget();
                        Tawk_API.maximize();
                        return;
                    }
                    launcher.disabled = true;
                    chatStatus.textContent = 'Opening live chat…';
                    loadWidget();
                });
                mobileChat.addEventListener('change', function () {
                    launcher.hidden = !mobileChat.matches;
                    if (!widgetReady) {
                        if (!mobileChat.matches) loadWidget();
                        return;
                    }
                    if (mobileChat.matches) {
                        Tawk_API.minimize();
                        closeMobileChat();
                    } else {
                        Tawk_API.showWidget();
                    }
                });

                function field(data, names) {
                    function key(value) {
                        return String(value || '').toLowerCase().replace(/[\s_-]+/g, '');
                    }
                    function text(value) {
                        return typeof value === 'string' ? value.trim() : '';
                    }
                    for (var i = 0; i < names.length; i++) {
                        if (text(data[names[i]])) return text(data[names[i]]);
                    }
                    var questions = Array.isArray(data.questions) ? data.questions : [];
                    for (var question of questions) {
                        if (question && names.includes(key(question.label)) && text(question.answer)) {
                            return text(question.answer);
                        }
                    }
                    var attributes = data.customAttributes;
                    if (attributes && typeof attributes === 'object') {
                        for (var name of Object.keys(attributes)) {
                            if (names.includes(key(name)) && text(attributes[name])) return text(attributes[name]);
                        }
                    }
                    return '';
                }

                async function request(url, options) {
                    var controller = new AbortController();
                    var timeout = setTimeout(function () { controller.abort(); }, 10000);
                    try {
                        return await fetch(url, Object.assign({
                            credentials: 'same-origin',
                            cache: 'no-store',
                            signal: controller.signal
                        }, options));
                    } finally {
                        clearTimeout(timeout);
                    }
                }

                async function save(payload) {
                    for (var attempt = 0; attempt < 3; attempt++) {
                        try {
                            if (!csrfToken) {
                                var tokenResponse = await request(@json(route('api.browser.whatsapp.leads.token', [], false)), {
                                    headers: { 'Accept': 'application/json' }
                                });
                                if (!tokenResponse.ok) throw new Error('Token request failed');
                                csrfToken = (await tokenResponse.json()).csrf_token;
                                if (typeof csrfToken !== 'string' || !csrfToken) throw new Error('Missing token');
                            }
                            var response = await request(@json(route('api.browser.tawk.leads.store', [], false)), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify(payload)
                            });
                            if (response.ok && (await response.json()).ok === true) return;
                            if (response.status === 419) csrfToken = '';
                            if (response.status === 422) {
                                console.warn('Tawk.to contact was not saved to the website database. Check the name, email, international phone number and message fields.');
                                return;
                            }
                        } catch (error) {
                            csrfToken = '';
                        }
                        if (attempt < 2) await new Promise(function (resolve) { setTimeout(resolve, 1000 * (attempt + 1)); });
                    }
                    console.warn('Tawk.to contact could not be saved to the website database. The Tawk.to chat is unaffected.');
                }

                function capture(data, type) {
                    if (!data || typeof data !== 'object') return;
                    var payload = {
                        event_id: crypto.randomUUID(),
                        form_type: type,
                        contact_name: field(data, ['name', 'fullname', 'contactname']),
                        email: field(data, ['email', 'emailaddress']),
                        phone: field(data, ['phone', 'phonenumber', 'mobilenumber', 'whatsappnumber']),
                        message: field(data, ['message', 'yourmessage', 'question', 'howcanwehelp']),
                        page: location.origin + location.pathname
                    };
                    pending = pending.then(function () { return save(payload); }).catch(function () {
                        console.warn('Tawk.to contact could not be saved to the website database.');
                    });
                }

                Tawk_API.onPrechatSubmit = function (data) { capture(data, 'prechat'); };
                Tawk_API.onOfflineSubmit = function (data) { capture(data, 'offline'); };

                function loadWidget() {
                    if (widgetLoading || widgetReady) return;
                    widgetLoading = true;
                    var s1 = document.createElement('script'), s0 = document.getElementsByTagName('script')[0];
                    s1.async = true;
                    s1.src = 'https://embed.tawk.to/6ac929cd7f1f3634beb0e4f6/1k4gsmj54';
                    s1.charset = 'UTF-8';
                    s1.setAttribute('crossorigin', '*');
                    s1.onerror = function () {
                        widgetLoading = false;
                        clearTimeout(widgetTimer);
                        launcher.disabled = false;
                        chatStatus.textContent = 'Chat could not load. Try again or use WhatsApp for support.';
                        s1.remove();
                    };
                    widgetTimer = setTimeout(function () {
                        launcher.disabled = false;
                        chatStatus.textContent = 'Chat is taking longer to load. You can use WhatsApp for support.';
                    }, 15000);
                    s0.parentNode.insertBefore(s1, s0);
                }
                if (!mobileChat.matches) loadWidget();
            })();
        </script>
        <!--End of Tawk.to Script-->
</body>

</html>
