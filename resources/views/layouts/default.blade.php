@php
    $layoutIsRtl = $isRtl ?? in_array(app()->getLocale(), ['ar', 'ur'], true);
    $layoutSuppressDisruptivePrompts = request()->routeIs(
        'configure',
        'checkout',
        'thankyou',
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
        class="whatsapp-icon" title="{{ __('document_ui.footer.contact') }} — WhatsApp" @if (app()->environment('local')) style="display: none !important;" @endif>
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

    @if (app()->environment('local'))
        <!--Start of Tawk.to Script-->
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
                                var tokenResponse = await request(@json(route('whatsapp.leads.token', [], false)), {
                                    headers: { 'Accept': 'application/json' }
                                });
                                if (!tokenResponse.ok) throw new Error('Token request failed');
                                csrfToken = (await tokenResponse.json()).csrf_token;
                                if (typeof csrfToken !== 'string' || !csrfToken) throw new Error('Missing token');
                            }
                            var response = await request(@json(route('tawk.leads.store', [], false)), {
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

                var s1 = document.createElement('script'), s0 = document.getElementsByTagName('script')[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/6ac929cd7f1f3634beb0e4f6/1k4gsmj54';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
        <!--End of Tawk.to Script-->
    @endif
</body>

</html>
