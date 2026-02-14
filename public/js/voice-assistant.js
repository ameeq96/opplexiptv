(function () {
    var container = document.getElementById('voice-assistant');
    if (!container) return;

    var routes = {};
    try {
        routes = JSON.parse(container.getAttribute('data-routes') || '{}');
    } catch (e) {
        routes = {};
    }

    var panel = container.querySelector('.va-panel');
    var fab = container.querySelector('.va-fab');
    var closeBtn = container.querySelector('.va-close');
    var messages = container.querySelector('.va-messages');
    var micBtn = container.querySelector('.va-mic');
    var sendBtn = container.querySelector('.va-send');
    var input = container.querySelector('.va-text');
    var speakToggle = container.querySelector('.va-speak-toggle');
    var helpBtn = container.querySelector('.va-help');
    var guideBtn = container.querySelector('.va-guide');
    var quickBtns = container.querySelectorAll('[data-quick]');
    var onboarding = document.getElementById('va-onboarding');
    var ONBOARDING_SEEN_KEY = 'va_onboarding_seen_v1';

    var voiceEnabled = true;
    var listening = false;
    var pendingAction = null;
    var isSpeaking = false;
    var speakMuteUntil = 0;
    var lastSpokenText = '';
    var lastSpokenAt = 0;
    var lastHandledTranscript = '';
    var lastHandledAt = 0;
    var LISTEN_CONTINUOUS_KEY = 'va_listen_continuous_v2';
    var LEGACY_LISTEN_KEY = 'va_listen_continuous';
    var GUIDE_DONE_MESSAGE = container.getAttribute('data-guide-complete') || 'Guide complete. Say your command or type it below.';
    var WELCOME_MESSAGE = container.getAttribute('data-welcome-message') || 'Hi! Tell me what you want to do. I can open pages, help with checkout, or fill forms.';
    var HELP_MESSAGE = container.getAttribute('data-help-message') || 'Try: \"open pricing\", \"go to packages\", \"download opplex app\", \"checkout\", \"contact support\", or \"my email is ...\".';
    var VOICE_ON_TEXT = container.getAttribute('data-voice-on') || 'Voice On';
    var VOICE_OFF_TEXT = container.getAttribute('data-voice-off') || 'Voice Off';
    var memory = {
        fullName: '',
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        message: '',
        notes: ''
    };

    function addMessage(role, text) {
        var item = document.createElement('div');
        item.className = 'va-msg va-' + role;
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
        if (role === 'assistant' && voiceEnabled) speak(text);
    }

    function speak(text) {
        if (!('speechSynthesis' in window)) return;
        try {
            var now = Date.now();
            var t = String(text || '').trim();
            if (!t) return;
            // Prevent repeated identical TTS lines in quick succession.
            if (t === lastSpokenText && (now - lastSpokenAt) < 4000) return;
            lastSpokenText = t;
            lastSpokenAt = now;

            window.speechSynthesis.cancel();
            var utter = new SpeechSynthesisUtterance(t);
            utter.onstart = function () {
                isSpeaking = true;
            };
            utter.onend = function () {
                isSpeaking = false;
                // Ignore mic results briefly after speaking to avoid self-trigger loops.
                speakMuteUntil = Date.now() + 1200;
            };
            utter.onerror = function () {
                isSpeaking = false;
                speakMuteUntil = Date.now() + 1200;
            };
            window.speechSynthesis.speak(utter);
        } catch (e) {}
    }

    function openPanel() {
        panel.classList.add('open');
    }

    function closePanel() {
        panel.classList.remove('open');
    }

    function setOnboardingSeen() {
        try { localStorage.setItem(ONBOARDING_SEEN_KEY, '1'); } catch (e) {}
    }

    function isOnboardingSeen() {
        try { return localStorage.getItem(ONBOARDING_SEEN_KEY) === '1'; } catch (e) { return false; }
    }

    function initOnboardingGuide() {
        if (!onboarding) return;

        var steps = Array.prototype.slice.call(onboarding.querySelectorAll('.va-step'));
        var prevBtn = onboarding.querySelector('[data-prev]');
        var nextBtn = onboarding.querySelector('[data-next]');
        var skipBtn = onboarding.querySelector('[data-skip]');
        var closeEls = onboarding.querySelectorAll('[data-close]');
        var locale = String(container.getAttribute('data-locale') || 'en').toLowerCase();
        var localeBase = locale.split('-')[0];
        var doneByLocale = {
            en: 'Done',
            ur: 'مکمل',
            ar: 'تم',
            es: 'Listo',
            fr: 'Terminé',
            hi: 'पूरा',
            it: 'Fatto',
            nl: 'Klaar',
            pt: 'Concluir',
            ru: 'Готово'
        };
        var nextLabel = nextBtn ? (nextBtn.getAttribute('data-next-text') || nextBtn.textContent || 'Next') : 'Next';
        var doneRaw = nextBtn ? (nextBtn.getAttribute('data-done-text') || '') : '';
        var doneLabel = doneRaw;
        if (!doneLabel || doneLabel === 'Done' || doneLabel === 'messages.voice_guide.done') {
            doneLabel = doneByLocale[locale] || doneByLocale[localeBase] || 'Done';
        }
        var stepIndex = 0;

        function renderStep() {
            if (!steps.length) return;
            if (stepIndex < 0) stepIndex = 0;
            if (stepIndex > steps.length - 1) stepIndex = steps.length - 1;

            steps.forEach(function (s, i) { s.classList.toggle('active', i === stepIndex); });
            if (prevBtn) prevBtn.disabled = stepIndex === 0;
            if (nextBtn) nextBtn.textContent = stepIndex === steps.length - 1 ? doneLabel : nextLabel;
        }

        function openGuide(markSeen) {
            if (markSeen) setOnboardingSeen();
            onboarding.classList.add('open');
            onboarding.setAttribute('aria-hidden', 'false');
            stepIndex = 0;
            renderStep();
        }

        function closeGuide(markSeen) {
            if (markSeen) setOnboardingSeen();
            onboarding.classList.remove('open');
            onboarding.setAttribute('aria-hidden', 'true');
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                stepIndex -= 1;
                renderStep();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                if (stepIndex >= steps.length - 1) {
                    closeGuide(true);
                    openPanel();
                    if (!messages.hasChildNodes()) {
                        addMessage('assistant', GUIDE_DONE_MESSAGE);
                    }
                    return;
                }
                stepIndex += 1;
                renderStep();
            });
        }

        if (skipBtn) {
            skipBtn.addEventListener('click', function () {
                closeGuide(true);
            });
        }

        closeEls.forEach(function (el) {
            el.addEventListener('click', function () {
                closeGuide(true);
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && onboarding.classList.contains('open')) {
                closeGuide(true);
            }
        });

        if (guideBtn) {
            guideBtn.addEventListener('click', function () {
                openGuide(false);
            });
        }

        if (!isOnboardingSeen()) {
            setTimeout(function () {
                openGuide(true);
            }, 900);
        }
    }

    function isCheckoutCompletePage() {
        var path = (window.location.pathname || '').toLowerCase();
        if (path.indexOf('thank-you') !== -1 || path.indexOf('thankyou') !== -1) return true;
        return !!document.querySelector('.thank-wrap, .thank-card');
    }

    function setContinuousListening(enabled) {
        try {
            if (enabled) sessionStorage.setItem(LISTEN_CONTINUOUS_KEY, '1');
            else sessionStorage.removeItem(LISTEN_CONTINUOUS_KEY);
        } catch (e) {}
    }

    function isContinuousListeningEnabled() {
        try {
            return sessionStorage.getItem(LISTEN_CONTINUOUS_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    fab.addEventListener('click', function () {
        openPanel();
        if (!messages.hasChildNodes()) {
            addMessage('assistant', WELCOME_MESSAGE);
        }
    });
    closeBtn.addEventListener('click', closePanel);

    speakToggle.addEventListener('click', function () {
        voiceEnabled = !voiceEnabled;
        speakToggle.setAttribute('aria-pressed', voiceEnabled ? 'true' : 'false');
        speakToggle.textContent = voiceEnabled ? VOICE_ON_TEXT : VOICE_OFF_TEXT;
    });

    helpBtn.addEventListener('click', function () {
        addMessage('assistant', HELP_MESSAGE);
    });

    quickBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-quick');
            handleIntent('open ' + key);
        });
    });

    function setFieldValue(name, value) {
        var field = document.querySelector('[name="' + name + '"]');
        if (!field) return false;
        field.value = value;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    function fillContactForm() {
        var did = false;
        if (memory.fullName) did = setFieldValue('username', memory.fullName) || did;
        if (memory.email) did = setFieldValue('email', memory.email) || did;
        if (memory.phone) did = setFieldValue('phone', memory.phone) || did;
        if (memory.message) did = setFieldValue('message', memory.message) || did;
        return did;
    }

    function fillCheckoutForm() {
        var did = false;
        if (memory.email) did = setFieldValue('email', memory.email) || did;
        if (memory.firstName) did = setFieldValue('first_name', memory.firstName) || did;
        if (memory.lastName) did = setFieldValue('last_name', memory.lastName) || did;
        if (memory.phone) did = setFieldValue('phone', memory.phone) || did;
        if (memory.notes) did = setFieldValue('notes', memory.notes) || did;
        return did;
    }

    function parseName(text) {
        var parts = text.trim().split(/\s+/);
        if (parts.length === 1) return { first: parts[0], last: '' };
        return { first: parts[0], last: parts.slice(1).join(' ') };
    }

    function openRoute(key, label) {
        var url = routes[key];
        if (!url) {
            addMessage('assistant', 'I cannot find that page right now.');
            return;
        }
        addMessage('assistant', 'Opening ' + (label || key) + '...');
        window.location.href = url;
    }

    function normalizeText(text) {
        return (text || '')
            .toLowerCase()
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function normalizeIntentLower(lowerInput) {
        var s = String(lowerInput || '').toLowerCase();

        // Politeness/noise words
        s = s.replace(/\b(please|kindly|just|bro|buddy|hey|yo)\b/g, ' ');

        // Navigation variants
        s = s.replace(/\b(take me to|navigate to|go to|go on|bring me to|show me|open up)\b/g, ' open ');

        // Buy/action variants
        s = s.replace(/\b(purchase|order now|book now|get me|i want to buy|i wanna buy|buy it now)\b/g, ' buy ');

        // Checkout variants
        s = s.replace(/\b(check out|checkout now|complete order|complete checkout|finish checkout)\b/g, ' checkout ');

        // Close variants
        s = s.replace(/\b(dismiss|hide this|shut this|close this window|x out|exit popup|close pop up)\b/g, ' close ');

        // Vendor variants
        s = s.replace(/\bstar[\s-]?share\b/g, ' starshare ');

        // Package duration variants
        s = s.replace(/\b(12 months?|12 month|one year|1 year|annual|year plan)\b/g, ' yearly ');
        s = s.replace(/\b(6 months?|6 month|six months?|half year|semi annual|semi-annual)\b/g, ' half yearly ');
        s = s.replace(/\b(3 months?|3 month|three months?|quarterly|quarter plan)\b/g, ' 3 months ');
        s = s.replace(/\b(1 month|one month|per month|month plan)\b/g, ' monthly ');

        // Email spoken variants
        s = s.replace(/\bat the rate\b/g, ' at ');
        s = s.replace(/\bat[-\s]?rate\b/g, ' at ');

        return s.replace(/\s+/g, ' ').trim();
    }

    function isDownloadAnchor(anchor) {
        if (!anchor || !anchor.href) return false;
        var text = normalizeText(anchor.textContent || '');
        var label = normalizeText(anchor.getAttribute('aria-label') || '');
        var keywords = normalizeText(anchor.getAttribute('data-keywords') || '');
        var href = (anchor.getAttribute('href') || '').toLowerCase();

        if (text.indexOf('download') !== -1) return true;
        if (label.indexOf('download') !== -1) return true;
        if (keywords.length > 0) return true;
        if (href.indexOf('/redirect') !== -1) return true;
        if (href.indexOf('/downloads/') !== -1) return true;
        return false;
    }

    function downloadLinkText(anchor) {
        var text = normalizeText(anchor.textContent || '');
        var label = normalizeText(anchor.getAttribute('aria-label') || '');
        var keywords = normalizeText(anchor.getAttribute('data-keywords') || '');
        return (text + ' ' + label + ' ' + keywords).trim();
    }

    function findBestDownloadLink(query) {
        var stopWords = {
            download: true, app: true, apk: true, please: true, for: true, iptv: true,
            player: true, open: true, install: true, get: true, karo: true, krdo: true,
            kar: true, do: true, mujhe: true, chahiye: true
        };

        var cleanQuery = normalizeText(query);
        var tokens = cleanQuery.split(' ').filter(function (t) {
            return t.length > 1 && !stopWords[t];
        });
        if (!tokens.length) return null;

        var links = Array.prototype.slice.call(document.querySelectorAll('a[href]')).filter(isDownloadAnchor);
        var best = null;
        var bestScore = 0;

        links.forEach(function (anchor) {
            var hay = downloadLinkText(anchor);
            var score = 0;
            tokens.forEach(function (token) {
                if (hay.indexOf(token) !== -1) score += 3;
            });
            if (cleanQuery.length > 2 && hay.indexOf(cleanQuery) !== -1) score += 5;
            if (score > bestScore) {
                best = anchor;
                bestScore = score;
            }
        });

        return bestScore > 0 ? best : null;
    }

    function triggerDownload(anchor) {
        var title = (anchor.textContent || 'that app').replace(/\s+/g, ' ').trim();
        addMessage('assistant', 'Starting download: ' + title);
        var target = anchor.getAttribute('target') || '_self';
        if (target === '_blank') {
            window.open(anchor.href, '_blank', 'noopener');
            return;
        }
        window.location.href = anchor.href;
    }

    function requestDownloadOnAppsPage(query) {
        try {
            localStorage.setItem('va_pending_download_query', query);
        } catch (e) {}
        openRoute('apps', 'IPTV applications');
    }

    function requestPricingActionOnPricingPage(command) {
        try {
            localStorage.setItem('va_pending_pricing_command', command);
        } catch (e) {}
        openRoute('pricing', 'pricing');
    }

    function requestShopActionOnShopPage(command) {
        try {
            localStorage.setItem('va_pending_shop_command', command);
        } catch (e) {}
        openRoute('shop', 'shop');
    }

    function requestMoviesSearchOnMoviesPage(command) {
        try {
            localStorage.setItem('va_pending_movies_search', command);
        } catch (e) {}
        openRoute('movies', 'movies');
    }

    function tryPendingDownload() {
        var pending = '';
        try {
            pending = localStorage.getItem('va_pending_download_query') || '';
        } catch (e) {}
        if (!pending) return;
        try {
            localStorage.removeItem('va_pending_download_query');
        } catch (e) {}

        var found = findBestDownloadLink(pending);
        if (found) {
            setTimeout(function () {
                addMessage('assistant', 'Matched app "' + pending + '".');
                triggerDownload(found);
            }, 400);
        } else {
            addMessage('assistant', 'I opened apps page. Please say the app name again, like "download opplex app".');
        }
    }

    function tryPendingPricingAction() {
        var pending = '';
        try {
            pending = localStorage.getItem('va_pending_pricing_command') || '';
        } catch (e) {}
        if (!pending) return;
        try {
            localStorage.removeItem('va_pending_pricing_command');
        } catch (e) {}

        // Delay slightly so pricing cards/toggles are fully rendered.
        setTimeout(function () {
            var ok = handlePricingSectionIntent(pending, pending.toLowerCase());
            if (!ok) {
                addMessage('assistant', 'Pricing page opened. Please repeat package command, e.g. "buy yearly".');
            }
        }, 500);
    }

    function tryPendingShopAction() {
        var pending = '';
        try {
            pending = localStorage.getItem('va_pending_shop_command') || '';
        } catch (e) {}
        if (!pending) return;
        try {
            localStorage.removeItem('va_pending_shop_command');
        } catch (e) {}

        setTimeout(function () {
            var ok = handleShopBuyIntent(pending, pending.toLowerCase());
            if (!ok) {
                addMessage('assistant', 'Shop page opened. Please repeat product hint, e.g. "buy roku 4k on amazon".');
            }
        }, 500);
    }

    function hasMoviesSearchSection() {
        return !!document.querySelector('form[aria-label="Search Movies and Series"] input[name="search"]');
    }

    function extractMoviesSearchQuery(raw, lower) {
        var q = String(raw || '');
        q = q.replace(/^\s*(search|find|look up|lookup)\s*/i, '');
        q = q.replace(/\s*(in\s+movies|movies|movie|series|cartoons)\s*$/i, '');
        q = q.replace(/\s+/g, ' ').trim();

        if (!q) {
            var n = normalizeText(lower || '');
            var m = n.match(/(?:search|find|look up|lookup)\s+(.+)/);
            if (m && m[1]) q = m[1].trim();
        }
        return q;
    }

    function runMoviesSearch(query) {
        if (!hasMoviesSearchSection()) return false;
        var form = document.querySelector('form[aria-label="Search Movies and Series"]');
        var input = form ? form.querySelector('input[name="search"]') : null;
        if (!form || !input) return false;
        var q = String(query || '').trim();
        if (!q) return false;
        input.value = q;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.submit();
        addMessage('assistant', 'Searching movies for: ' + q);
        return true;
    }

    function handleMoviesSearchIntent(raw, lower) {
        var hasIntent = /\bsearch\b|\bfind\b|\blook up\b|\blookup\b/.test(lower);
        var hasMoviesContext = /\bmovie\b|\bmovies\b|\bseries\b|\bcartoons\b/.test(lower);
        if (!hasIntent && !hasMoviesContext) return false;

        var query = extractMoviesSearchQuery(raw, lower);
        if (!query || query.length < 2) return false;

        if (hasMoviesSearchSection()) {
            return runMoviesSearch(query);
        }

        requestMoviesSearchOnMoviesPage(raw);
        return true;
    }

    function hasMoviesGrid() {
        return !!document.querySelector('.filter-list .feature-block.style-two');
    }

    function normalizeMovieQuery(raw) {
        return normalizeText(raw || '')
            .replace(/\b(open|play|watch|start|show|close)\b/g, ' ')
            .replace(/\b(trailer|video|movie|series|cartoon)\b/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function findMovieCardByName(query) {
        if (!hasMoviesGrid()) return null;
        var cards = Array.prototype.slice.call(document.querySelectorAll('.filter-list .feature-block.style-two'));
        if (!cards.length) return null;

        var q = normalizeText(query || '');
        if (!q) return null;
        var tokens = q.split(' ').filter(function (t) { return t.length > 1; });
        if (!tokens.length) return null;

        var best = null;
        var bestScore = 0;
        cards.forEach(function (card) {
            var titleEl = card.querySelector('.lower-content h6 a');
            var title = normalizeText(titleEl ? titleEl.textContent : '');
            if (!title) return;

            var score = 0;
            if (title === q) score += 20;
            if (title.indexOf(q) !== -1 || q.indexOf(title) !== -1) score += 10;
            tokens.forEach(function (t) {
                if (title.indexOf(t) !== -1) score += 2;
            });

            if (score > bestScore) {
                best = card;
                bestScore = score;
            }
        });

        return bestScore >= 4 ? best : null;
    }

    function handleOpenTrailerByName(raw, lower) {
        var intent = /\b(open|play|watch|start|show)\b.*\b(trailer|video)\b|\btrailer\b.*\b(open|play|watch)\b/.test(lower);
        if (!intent) return false;
        if (!hasMoviesGrid()) return false;

        var query = normalizeMovieQuery(raw);
        var card = findMovieCardByName(query);
        if (!card) {
            addMessage('assistant', 'Movie naam clear nahi mila. Example: "open trailer marty supreme".');
            return true;
        }

        var playLink = card.querySelector('a.lightbox-image.video-box');
        if (!playLink) {
            addMessage('assistant', 'Is movie ka trailer link available nahi hai.');
            return true;
        }

        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        playLink.click();
        addMessage('assistant', 'Opening trailer.');
        return true;
    }

    function closeOpenOverlays() {
        // Try common close controls used by fancybox/bootstrap/magnific and similar lightboxes.
        var selectors = [
            '.fancybox-button--close',
            '.fancybox-close-small',
            '.fancybox-close',
            '.mfp-close',
            '.modal.show .btn-close',
            '.modal.show [data-dismiss="modal"]',
            '.lightbox .close',
            '.video-modal .close'
        ];

        var clicked = false;
        selectors.forEach(function (sel) {
            var btn = document.querySelector(sel);
            if (btn && isElementVisible(btn)) {
                btn.click();
                clicked = true;
            }
        });

        // Escape fallback for libraries listening to keyboard close.
        try {
            document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', code: 'Escape', keyCode: 27, which: 27, bubbles: true }));
            document.dispatchEvent(new KeyboardEvent('keyup', { key: 'Escape', code: 'Escape', keyCode: 27, which: 27, bubbles: true }));
        } catch (e) {}

        return clicked;
    }

    function handleCloseTrailerIntent(lower) {
        var closeIntent =
            /\bclose\b.*\btrailer\b/.test(lower) ||
            /\bclose\b.*\bvideo\b/.test(lower) ||
            /\bclose\b.*\bpopup\b/.test(lower) ||
            /\bclose\b.*\bmodal\b/.test(lower) ||
            /\btrailer\b.*\bband\b/.test(lower) ||
            /\bclose this\b/.test(lower) ||
            /\bclose it\b/.test(lower) ||
            /\bclose\b/.test(lower) ||
            /\bband karo\b/.test(lower) ||
            /\bband krdo\b/.test(lower) ||
            /\bband kar do\b/.test(lower);

        if (!closeIntent) return false;

        var ok = closeOpenOverlays();
        addMessage('assistant', ok ? 'Trailer closed.' : 'Tried to close open trailer/popup.');
        return true;
    }

    function tryPendingMoviesSearch() {
        var pending = '';
        try {
            pending = localStorage.getItem('va_pending_movies_search') || '';
        } catch (e) {}
        if (!pending) return;
        try {
            localStorage.removeItem('va_pending_movies_search');
        } catch (e) {}

        setTimeout(function () {
            var query = extractMoviesSearchQuery(pending, pending.toLowerCase());
            if (!runMoviesSearch(query)) {
                addMessage('assistant', 'Movies page opened. Please say: "search avatar".');
            }
        }, 500);
    }

    function handleDownloadIntent(raw, lower) {
        var hasDownloadWord =
            lower.indexOf('download') !== -1 ||
            lower.indexOf('install') !== -1 ||
            lower.indexOf('get app') !== -1 ||
            lower.indexOf('apk') !== -1;
        if (!hasDownloadWord) return false;

        var query = raw
            .replace(/download/ig, ' ')
            .replace(/install/ig, ' ')
            .replace(/get app/ig, ' ')
            .replace(/apk/ig, ' ')
            .replace(/please/ig, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        if (!query) {
            addMessage('assistant', 'App name bolo. Example: "download opplex app" or "download 9xtream".');
            return true;
        }

        var found = findBestDownloadLink(query);
        if (found) {
            triggerDownload(found);
            return true;
        }

        requestDownloadOnAppsPage(query);
        return true;
    }

    function setPending(action, prompt) {
        pendingAction = action;
        addMessage('assistant', prompt + ' Please confirm to proceed.');
    }

    function isConfigurePage() {
        return !!document.getElementById('configForm');
    }

    function isCheckoutPage() {
        return !!document.getElementById('checkoutForm');
    }

    function clickElement(el) {
        if (!el) return false;
        el.click();
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return true;
    }

    function pickConfigDevice(lower) {
        if (!isConfigurePage()) return false;
        var map = [
            { key: 'android', aliases: ['android'] },
            { key: 'firestick', aliases: ['firestick', 'fire stick', 'amazon'] },
            { key: 'ios', aliases: ['ios', 'iphone', 'ipad'] },
            { key: 'mag', aliases: ['mag', 'mag box'] },
            { key: 'pcmac', aliases: ['pc', 'mac', 'pc/mac', 'pc mac'] },
            { key: 'smarttv', aliases: ['smart tv', 'smarttv', 'tv'] }
        ];

        var target = null;
        map.forEach(function (m) {
            if (target) return;
            if (m.aliases.some(function (a) { return lower.indexOf(a) !== -1; })) {
                target = m.key;
            }
        });
        if (!target) return false;

        var cards = Array.prototype.slice.call(document.querySelectorAll('#deviceSection [data-device]'));
        var found = cards.find(function (card) {
            var text = normalizeText(card.getAttribute('data-device') || card.textContent || '');
            return text.indexOf(target) !== -1;
        });
        if (!found && target === 'pcmac') {
            found = cards.find(function (card) {
                var text = normalizeText(card.getAttribute('data-device') || card.textContent || '');
                return text.indexOf('pc') !== -1 || text.indexOf('mac') !== -1;
            });
        }
        if (!found) return false;
        clickElement(found);
        addMessage('assistant', 'Device selected.');
        return true;
    }

    function pickConfigVendor(lower) {
        if (!isConfigurePage()) return false;
        var vendor = null;
        if (lower.indexOf('opplex') !== -1) vendor = 'opplex';
        if (lower.indexOf('starshare') !== -1 || lower.indexOf('star share') !== -1) vendor = 'starshare';
        if (!vendor) return false;

        var cards = Array.prototype.slice.call(document.querySelectorAll('#vendorSection [data-vendor]'));
        var found = cards.find(function (card) {
            return normalizeText(card.getAttribute('data-vendor') || '').indexOf(vendor) !== -1;
        });
        if (!found) return false;
        clickElement(found);
        addMessage('assistant', 'Provider selected: ' + (vendor === 'opplex' ? 'Opplex' : 'Starshare') + '.');
        return true;
    }

    function pickConfigConnection(lower) {
        if (!isConfigurePage()) return false;
        if (lower.indexOf('connection') === -1 && lower.indexOf('device 1') === -1 && lower.indexOf('device 2') === -1 && lower.indexOf('device 4') === -1) {
            return false;
        }
        var max = null;
        if (lower.indexOf('4') !== -1 || lower.indexOf('four') !== -1) max = '4';
        else if (lower.indexOf('2') !== -1 || lower.indexOf('two') !== -1) max = '2';
        else if (lower.indexOf('1') !== -1 || lower.indexOf('one') !== -1 || lower.indexOf('single') !== -1) max = '1';
        if (!max) return false;

        var cards = Array.prototype.slice.call(document.querySelectorAll('[data-kind="connection"]')).filter(function (c) {
            return c.style.display !== 'none';
        });
        var found = cards.find(function (card) { return (card.getAttribute('data-max') || '') === max; });
        if (!found) {
            addMessage('assistant', 'This connection plan is not available for the current provider/package.');
            return true;
        }
        clickElement(found);
        addMessage('assistant', 'Connection plan selected.');
        return true;
    }

    function setPackageTabByIntent(lower) {
        if (!isConfigurePage()) return false;
        var tab = null;
        if (lower.indexOf('reseller') !== -1) tab = 'reseller';
        if (lower.indexOf('iptv') !== -1) tab = 'iptv';
        if (!tab) return false;

        var btn = document.querySelector('#pkgToggle .tg-btn[data-tab="' + tab + '"]');
        if (!btn) return false;
        clickElement(btn);
        addMessage('assistant', (tab === 'iptv' ? 'IPTV' : 'Reseller') + ' packages opened.');
        return true;
    }

    function pickConfigPackage(lower) {
        if (!isConfigurePage()) return false;
        var targetKey = inferPackageKeyFromText(lower);
        if (!targetKey) return false;

        if (['starter', 'essential', 'pro', 'advanced'].indexOf(targetKey) !== -1) {
            var resellerTab = document.querySelector('#pkgToggle .tg-btn[data-tab="reseller"]');
            if (resellerTab && !resellerTab.classList.contains('active')) resellerTab.click();
        } else {
            var iptvTab = document.querySelector('#pkgToggle .tg-btn[data-tab="iptv"]');
            if (iptvTab && !iptvTab.classList.contains('active')) iptvTab.click();
        }

        var found = selectPackageByKey(targetKey);
        if (!found) {
            addMessage('assistant', 'Package not found for current filters.');
            return true;
        }

        if (found.getAttribute('data-kind') === 'iptv') {
            ensureVendorForIptvCard(found);
        }
        clickElement(found);
        addMessage('assistant', 'Package selected.');
        return true;
    }

    function pickConfigPackageByName(raw, lower) {
        if (!isConfigurePage()) return false;

        var triggerWords = ['select', 'choose', 'package', 'plan', 'pick'];
        var hasTrigger = triggerWords.some(function (w) { return lower.indexOf(w) !== -1; });
        var hasCommonPackageName =
            /\bmonthly\b|\b3\s*months?\b|\bhalf\s*year(ly)?\b|\byearly\b|\bstarter\b|\bessential\b|\bpro\b|\badvanced\b/.test(lower);
        if (!hasTrigger && !hasCommonPackageName) return false;

        var query = normalizeText(raw)
            .replace(/\b(select|choose|package|plan|pick|please|karo|kar do|krdo|wala|wla)\b/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        if (!query) return false;

        if (query.indexOf('reseller') !== -1) {
            var resellerTab = document.querySelector('#pkgToggle .tg-btn[data-tab="reseller"]');
            if (resellerTab && !resellerTab.classList.contains('active')) resellerTab.click();
        } else if (query.indexOf('monthly') !== -1 || query.indexOf('yearly') !== -1 || query.indexOf('half yearly') !== -1 || query.indexOf('3 months') !== -1) {
            var iptvTab = document.querySelector('#pkgToggle .tg-btn[data-tab="iptv"]');
            if (iptvTab && !iptvTab.classList.contains('active')) iptvTab.click();
        }

        var directKey = inferPackageKeyFromText(query);
        if (directKey) {
            var directFound = selectPackageByKey(directKey);
            if (directFound) {
                if (directFound.getAttribute('data-kind') === 'iptv') ensureVendorForIptvCard(directFound);
                clickElement(directFound);
                addMessage('assistant', 'Selected package: ' + ((directFound.getAttribute('data-plan') || '').trim() || 'package') + '.');
                return true;
            }
        }

        var cards = Array.prototype.slice.call(document.querySelectorAll('.pkg-card')).filter(function (c) {
            return c.style.display !== 'none';
        });
        if (!cards.length) return false;

        var queryTokens = query.split(' ').filter(function (t) { return t.length > 1; });
        var best = null;
        var bestScore = 0;

        cards.forEach(function (card) {
            var plan = normalizeText(card.getAttribute('data-plan') || card.textContent || '');
            var score = 0;
            if (plan.indexOf(query) !== -1 || query.indexOf(plan) !== -1) score += 6;
            if (plan === query) score += 20;
            if (query === 'yearly' && plan === 'yearly') score += 15;
            if (query === '3 months' && plan.indexOf('3 months') !== -1) score += 15;
            if (query === 'half yearly' && plan.indexOf('half yearly') !== -1) score += 15;
            if (query === 'yearly' && plan.indexOf('half yearly') !== -1) score -= 8;
            queryTokens.forEach(function (t) {
                if (plan.indexOf(t) !== -1) score += 2;
            });
            if (score > bestScore) {
                best = card;
                bestScore = score;
            }
        });

        if (!best || bestScore < 2) return false;
        if (best.getAttribute('data-kind') === 'iptv') {
            ensureVendorForIptvCard(best);
        }
        clickElement(best);
        addMessage('assistant', 'Selected package: ' + ((best.getAttribute('data-plan') || '').trim() || 'package') + '.');
        return true;
    }

    function packageKeyFromPlanName(planText) {
        var p = normalizeText(planText || '');
        if (!p) return '';
        if (/\b3\s*months?\b|\bthree\s*months?\b/.test(p)) return '3months';
        if (/\bhalf\s*year(ly)?\b|\b6\s*months?\b/.test(p)) return 'halfyearly';
        if (/^yearly$|\byearly\b/.test(p) && p.indexOf('half yearly') === -1) return 'yearly';
        if (/\bmonthly\b|\b1\s*month\b/.test(p)) return 'monthly';
        if (/\bstarter\b|\b20\s*credits?\b/.test(p)) return 'starter';
        if (/\bessential\b|\b50\s*credits?\b/.test(p)) return 'essential';
        if (/\bpro\b|\b100\s*credits?\b/.test(p)) return 'pro';
        if (/\badvanced\b|\b200\s*credits?\b|\b300\s*credits?\b/.test(p)) return 'advanced';
        return '';
    }

    function inferPackageKeyFromText(text) {
        var t = normalizeText(text || '');
        if (!t) return '';
        if (/\b3\s*months?\b|\bthree\s*months?\b|\bquarter\b/.test(t)) return '3months';
        if (/\bhalf\s*year(ly)?\b|\b6\s*months?\b|\bsix\s*months?\b/.test(t)) return 'halfyearly';
        if (/\byearly\b|\bannual\b|\b12\s*months?\b|\b1\s*year\b|\bone\s*year\b/.test(t) && t.indexOf('half yearly') === -1) return 'yearly';
        if (/\bmonthly\b|\b1\s*month\b|\bone\s*month\b/.test(t)) return 'monthly';
        if (/\bstarter\b|\b20\s*credits?\b/.test(t)) return 'starter';
        if (/\bessential\b|\b50\s*credits?\b/.test(t)) return 'essential';
        if (/\bpro\b|\b100\s*credits?\b/.test(t)) return 'pro';
        if (/\badvanced\b|\b200\s*credits?\b|\b300\s*credits?\b/.test(t)) return 'advanced';
        return '';
    }

    function selectPackageByKey(key) {
        if (!key) return null;
        var cards = Array.prototype.slice.call(document.querySelectorAll('.pkg-card')).filter(function (c) {
            return c.style.display !== 'none';
        });
        var exact = cards.find(function (card) {
            var planKey = packageKeyFromPlanName(card.getAttribute('data-plan') || card.textContent || '');
            return planKey === key;
        });
        return exact || null;
    }

    function ensureVendorForIptvCard(card) {
        var vendorInput = document.getElementById('iptvVendorInput');
        if (!vendorInput || vendorInput.value) return;

        var cardVendor = normalizeText(card.getAttribute('data-vendor') || '');
        var vendorButtons = Array.prototype.slice.call(document.querySelectorAll('#vendorSection [data-vendor]'));

        var match = vendorButtons.find(function (btn) {
            return normalizeText(btn.getAttribute('data-vendor') || '').indexOf(cardVendor) !== -1;
        });
        if (!match) match = vendorButtons[0] || null;
        if (match) match.click();
    }

    function continueFromConfigure() {
        if (!isConfigurePage()) return false;
        var btn = document.getElementById('continueBtn');
        if (!btn) return false;
        if (btn.disabled) {
            addMessage('assistant', 'Please select device, provider, connection, and package first.');
            return true;
        }
        clickElement(btn);
        addMessage('assistant', 'Continuing to checkout.');
        return true;
    }

    function hasPricingPackagesSection() {
        return !!document.getElementById('pricing-section');
    }

    function isElementVisible(el) {
        if (!el) return false;
        if (el.offsetParent === null && getComputedStyle(el).position !== 'fixed') return false;
        var st = getComputedStyle(el);
        return st.display !== 'none' && st.visibility !== 'hidden' && st.opacity !== '0';
    }

    function isAssistantElement(el) {
        return !!(el && el.closest && el.closest('#voice-assistant'));
    }

    function clickableCandidates() {
        return Array.prototype.slice.call(
            document.querySelectorAll('a[href], button, [role="button"], input[type="button"], input[type="submit"]')
        ).filter(function (el) {
            if (!el) return false;
            if (isAssistantElement(el)) return false;
            if (!isElementVisible(el)) return false;
            if (el.disabled) return false;
            var href = (el.getAttribute('href') || '').trim();
            if (el.tagName && el.tagName.toLowerCase() === 'a' && (!href || href === '#')) return false;
            return true;
        });
    }

    function clickableText(el) {
        if (!el) return '';
        var txt = normalizeText(el.textContent || '');
        var aria = normalizeText(el.getAttribute('aria-label') || '');
        var title = normalizeText(el.getAttribute('title') || '');
        var value = normalizeText(el.value || '');
        var href = normalizeText((el.getAttribute('href') || '').replace(/^https?:\/\//i, ''));
        return (txt + ' ' + aria + ' ' + title + ' ' + value + ' ' + href).trim();
    }

    function extractClickQuery(raw, lower) {
        var q = normalizeText(raw || '');
        q = q
            .replace(/\b(click|press|tap|open|go to|goto|select|choose|visit|trigger)\b/g, ' ')
            .replace(/\bbutton|link|menu|page\b/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        // If nothing left, use original normalized transcript.
        return q || normalizeText(lower || '');
    }

    function handleGenericClickByText(raw, lower) {
        // Ignore if command is clearly form-field assignment.
        if (/\b(first name|last name|full name|email|phone|order note|notes)\b/.test(lower)) return false;

        var query = extractClickQuery(raw, lower);
        if (!query) return false;

        var tokens = query.split(' ').filter(function (t) { return t.length > 1; });
        if (!tokens.length) return false;

        var candidates = clickableCandidates();
        if (!candidates.length) return false;

        var best = null;
        var bestScore = 0;
        candidates.forEach(function (el) {
            var hay = clickableText(el);
            if (!hay) return;
            var score = 0;
            if (hay === query) score += 20;
            if (hay.indexOf(query) !== -1) score += 10;
            tokens.forEach(function (t) {
                if (hay.indexOf(t) !== -1) score += 2;
            });
            if (score > bestScore) {
                best = el;
                bestScore = score;
            }
        });

        // Require minimum confidence to avoid random clicks from noisy speech.
        if (!best || bestScore < 5) return false;

        best.scrollIntoView({ behavior: 'smooth', block: 'center' });
        best.click();
        addMessage('assistant', 'Clicked: ' + ((best.textContent || best.getAttribute('aria-label') || 'target').trim().slice(0, 60)));
        return true;
    }

    function setPricingVendor(vendorKey) {
        if (!hasPricingPackagesSection()) return false;
        var resellerToggle = document.getElementById('resellerToggle');
        var isReseller = !!(resellerToggle && resellerToggle.checked);
        var toggle = document.getElementById(isReseller ? 'vendorToggleReseller' : 'vendorToggle');
        if (!toggle) return false;
        var btn = toggle.querySelector('.tg[data-vendor="' + vendorKey + '"]');
        if (!btn) return false;
        btn.click();
        addMessage('assistant', 'Vendor selected: ' + (vendorKey === 'opplex' ? 'Opplex' : 'Starshare') + '.');
        return true;
    }

    function setPricingMode(showReseller) {
        if (!hasPricingPackagesSection()) return false;
        var resellerToggle = document.getElementById('resellerToggle');
        if (!resellerToggle) return false;
        if (resellerToggle.checked !== showReseller) {
            resellerToggle.checked = showReseller;
            resellerToggle.dispatchEvent(new Event('change', { bubbles: true }));
        }
        addMessage('assistant', showReseller ? 'Reseller packages shown.' : 'IPTV packages shown.');
        return true;
    }

    function visiblePricingCards() {
        return Array.prototype.slice.call(document.querySelectorAll('#pricing-section .pkg-item')).filter(isElementVisible);
    }

    function pricingCardKey(card) {
        var t = normalizeText(card ? card.textContent : '');
        if (/\b3\s*months?\b/.test(t)) return '3months';
        if (/\b6\s*months?\b|\bhalf\s*year(ly)?\b/.test(t)) return 'halfyearly';
        if (/\b12\s*months?\b|\byearly\b/.test(t)) return 'yearly';
        if (/\b1\s*month\b|\bmonthly\b/.test(t)) return 'monthly';
        if (/\bstarter\b|\b20\s*credits?\b/.test(t)) return 'starter';
        if (/\bessential\b|\b50\s*credits?\b/.test(t)) return 'essential';
        if (/\bpro\b|\b100\s*credits?\b/.test(t)) return 'pro';
        if (/\badvanced\b|\b200\s*credits?\b|\b300\s*credits?\b/.test(t)) return 'advanced';
        return '';
    }

    function pricingCardTitle(card) {
        if (!card) return '';
        var h4 = card.querySelector('h4');
        return normalizeText(h4 ? h4.textContent : card.textContent || '');
    }

    function extractPricingQuery(raw) {
        var q = normalizeText(raw || '');
        if (!q) return '';
        q = q
            .replace(/\bbuy now\b/g, ' ')
            .replace(/\bpricing\b/g, ' ')
            .replace(/\bpackage(s)?\b/g, ' ')
            .replace(/\bplan(s)?\b/g, ' ')
            .replace(/\bbuy\b/g, ' ')
            .replace(/\bselect\b/g, ' ')
            .replace(/\bchoose\b/g, ' ')
            .replace(/\bopen\b/g, ' ')
            .replace(/\bnow\b/g, ' ')
            .replace(/\bplease\b/g, ' ')
            .replace(/\bthis\b/g, ' ')
            .replace(/\bone\b/g, ' ')
            .replace(/\bplease\b/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        return q;
    }

    function findPricingCardByQuery(query) {
        var cards = visiblePricingCards();
        if (!cards.length) return null;
        if (!query) return null;
        var inferredKey = inferPackageKeyFromText(query);
        if (inferredKey) {
            var byKey = cards.find(function (c) { return pricingCardKey(c) === inferredKey; }) || null;
            if (byKey) return byKey;
        }
        var tokens = query.split(' ').filter(function (t) { return t.length > 1; });
        if (!tokens.length) return null;

        var best = null;
        var bestScore = 0;
        cards.forEach(function (card) {
            var title = pricingCardTitle(card);
            var key = pricingCardKey(card);
            var score = 0;

            if (title === query) score += 25;
            if (title.indexOf(query) !== -1) score += 10;
            if (key && query.indexOf(key) !== -1) score += 6;
            if (key === '3months' && /\b3\s*months?\b|\bthree\s*months?\b/.test(query)) score += 12;
            if (key === 'halfyearly' && /\bhalf\s*year(ly)?\b|\b6\s*months?\b|\bsix\s*months?\b/.test(query)) score += 12;
            if (key === 'yearly' && /\byearly\b|\b12\s*months?\b|\bannual\b|\bone\s*year\b/.test(query)) score += 12;
            if (key === 'monthly' && /\bmonthly\b|\b1\s*month\b|\bone\s*month\b/.test(query)) score += 12;
            tokens.forEach(function (t) {
                if (title.indexOf(t) !== -1) score += 2;
            });

            if (score > bestScore) {
                best = card;
                bestScore = score;
            }
        });

        return bestScore >= 3 ? best : null;
    }

    function selectAndBuyPricingCardByKey(key, shouldBuy) {
        var cards = visiblePricingCards();
        if (!cards.length) return false;

        var card = cards.find(function (c) { return pricingCardKey(c) === key; }) || null;
        if (!card) return false;

        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (!shouldBuy) {
            addMessage('assistant', 'Package found: ' + (card.querySelector('h4') ? card.querySelector('h4').textContent.trim() : 'selected') + '.');
            return true;
        }

        var buyBtn = card.querySelector('a.theme-btn.btn-style-four');
        if (!buyBtn) return false;
        buyBtn.click();
        addMessage('assistant', 'Opening buy now.');
        return true;
    }

    function selectAndBuyPricingCardByQuery(query, shouldBuy) {
        var card = findPricingCardByQuery(query);
        if (!card) return false;
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });

        if (!shouldBuy) {
            addMessage('assistant', 'Package found: ' + (card.querySelector('h4') ? card.querySelector('h4').textContent.trim() : 'selected') + '.');
            return true;
        }

        var buyBtn = card.querySelector('a.theme-btn.btn-style-four');
        if (!buyBtn) return false;
        buyBtn.click();
        addMessage('assistant', 'Opening buy now.');
        return true;
    }

    function handlePricingSectionIntent(raw, lower) {
        if (!hasPricingPackagesSection() || isConfigurePage() || isCheckoutPage()) return false;

        if (/\bshow\b.*\breseller\b|\breseller\b.*\bshow\b|\breseller packages\b/.test(lower)) {
            return setPricingMode(true);
        }
        if (/\bshow\b.*\biptv\b|\biptv\b.*\bshow\b|\bhide reseller\b|\bnormal packages\b/.test(lower)) {
            return setPricingMode(false);
        }

        if (lower.indexOf('opplex') !== -1) return setPricingVendor('opplex');
        if (lower.indexOf('starshare') !== -1 || lower.indexOf('star share') !== -1) return setPricingVendor('starshare');

        var key = inferPackageKeyFromText(lower);
        var wantsBuy = /\bbuy\b|\bbuy now\b|\bselect\b|\bchoose\b|\bopen\b/.test(lower);
        if (key) {
            var ok = selectAndBuyPricingCardByKey(key, wantsBuy);
            if (ok) return true;
        }

        var query = extractPricingQuery(raw);
        if (query) {
            var okByQuery = selectAndBuyPricingCardByQuery(query, wantsBuy);
            if (okByQuery) return true;
        }

        if (/\bbuy now\b|\bbuy\b/.test(lower)) {
            if (query || key) {
                addMessage('assistant', 'Package not found. Please say exact package name, like "buy 3 months package".');
                return true;
            }
            var cards = visiblePricingCards();
            if (!cards.length) return false;
            var firstBuy = cards[0].querySelector('a.theme-btn.btn-style-four');
            if (firstBuy) {
                firstBuy.click();
                addMessage('assistant', 'Opening buy now.');
                return true;
            }
        }

        return false;
    }

    function hasShopProductsSection() {
        return !!document.querySelector('.product-grid, .product-card');
    }

    function getShopProductCards() {
        return Array.prototype.slice.call(document.querySelectorAll('.product-card')).filter(isElementVisible);
    }

    function extractShopQuery(raw, lower) {
        var q = raw;
        q = q.replace(/\bbuy on amazon\b/ig, ' ');
        q = q.replace(/\bbuy\b/ig, ' ');
        q = q.replace(/\bon amazon\b/ig, ' ');
        q = q.replace(/\bamazon\b/ig, ' ');
        q = q.replace(/\bplease\b/ig, ' ');
        q = q.replace(/\s+/g, ' ').trim();
        if (!q) return '';
        return normalizeText(q);
    }

    function shopCardText(card) {
        if (!card) return '';
        var title = normalizeText((card.querySelector('.product-card__title') || {}).textContent || '');
        var meta = normalizeText((card.querySelector('.product-card__meta') || {}).textContent || '');
        var aria = normalizeText(card.getAttribute('aria-label') || '');
        return (title + ' ' + meta + ' ' + aria).trim();
    }

    function findBestShopCard(query) {
        var cards = getShopProductCards();
        if (!cards.length) return null;
        if (!query) return cards[0];

        var tokens = query.split(' ').filter(function (t) { return t.length > 1; });
        if (!tokens.length) return cards[0];

        var best = null;
        var bestScore = 0;
        cards.forEach(function (card) {
            var hay = shopCardText(card);
            var score = 0;
            if (hay.indexOf(query) !== -1) score += 8;
            tokens.forEach(function (t) {
                if (hay.indexOf(t) !== -1) score += 2;
            });
            // Strong boost for ASIN-style hints.
            var asinLike = query.match(/\b[a-z0-9]{8,12}\b/i);
            if (asinLike && hay.indexOf(normalizeText(asinLike[0])) !== -1) score += 10;
            if (score > bestScore) {
                best = card;
                bestScore = score;
            }
        });
        return bestScore > 0 ? best : null;
    }

    function clickShopBuy(card) {
        if (!card) return false;
        var btn = card.querySelector('a.product-card__btn');
        if (!btn) return false;
        btn.click();
        addMessage('assistant', 'Opening Amazon product.');
        return true;
    }

    function handleShopBuyIntent(raw, lower) {
        var buyIntent = /\bbuy\b|\bbuy on amazon\b|\bon amazon\b|\bamazon\b/.test(lower);
        var hasAmazonIntent = /\bamazon\b|\bbuy on amazon\b|\bon amazon\b/.test(lower);
        var maybeProductHint = /\bremote\b|\broku\b|\bfire\b|\bsamsung\b|\bvizio\b|\bstick\b|\basin\b|\bpack\b|\btv\b/.test(lower);
        // Do not hijack generic "buy monthly/yearly" pricing intents.
        if (!(hasAmazonIntent || (buyIntent && maybeProductHint))) return false;

        if (!hasShopProductsSection()) {
            requestShopActionOnShopPage(raw);
            return true;
        }

        var query = extractShopQuery(raw, lower);
        var card = findBestShopCard(query);
        if (!card) {
            addMessage('assistant', 'Product not found. Please say a clearer hint or ASIN.');
            return true;
        }

        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return clickShopBuy(card);
    }

    function isDirectPricingBuyPackageIntent(lower) {
        var hasBuy = /\bbuy\b|\bbuy now\b/.test(lower);
        var hasPackageKey = !!inferPackageKeyFromText(lower);
        var hasPricingWord = /\bpricing\b|\bplan(s)?\b|\bpackage(s)?\b/.test(lower);
        return hasBuy && hasPackageKey && hasPricingWord;
    }

    function shouldRouteToPricingForBuy(raw, lower) {
        if (hasPricingPackagesSection()) return false;
        if (isConfigurePage() || isCheckoutPage()) return false;
        var mentionsPricing = /\bpricing\b|\bplans?\b|\bpackages?\b/.test(lower);
        var wantsBuy = /\bbuy\b|\bbuy now\b|\bselect\b|\bchoose\b/.test(lower);
        // Explicit pricing context required to avoid accidental route jumps.
        return mentionsPricing && wantsBuy;
    }

    function setCheckoutType(lower) {
        if (!isCheckoutPage()) return false;
        if (
            lower.indexOf('package type') === -1 &&
            lower.indexOf('reseller type') === -1 &&
            lower.indexOf('iptv type') === -1 &&
            lower.indexOf('iptv package') === -1 &&
            lower.indexOf('reseller package') === -1
        ) return false;
        var select = document.querySelector('#checkoutForm select[name="package_type"]');
        if (!select) return false;
        if (lower.indexOf('reseller') !== -1) select.value = 'reseller';
        else if (lower.indexOf('iptv') !== -1 || lower.indexOf('package') !== -1) select.value = 'package';
        else return false;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        addMessage('assistant', 'Checkout type updated.');
        return true;
    }

    function setPaymentMethod(lower) {
        if (!isCheckoutPage()) return false;
        if (lower.indexOf('payment') === -1 && lower.indexOf('pay') === -1 && lower.indexOf('card') === -1 && lower.indexOf('crypto') === -1) return false;
        var targetValue = null;
        if (lower.indexOf('crypto') !== -1 || lower.indexOf('usdt') !== -1 || lower.indexOf('bitcoin') !== -1) targetValue = 'crypto';
        if (lower.indexOf('card') !== -1 || lower.indexOf('visa') !== -1 || lower.indexOf('master') !== -1) targetValue = 'card';
        if (!targetValue) return false;

        var radio = document.querySelector('input[name="paymethod"][value="' + targetValue + '"]');
        if (!radio) return false;
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));
        addMessage('assistant', 'Payment method selected: ' + targetValue + '.');
        return true;
    }

    function setCheckoutFormFields(raw, lower) {
        if (!isCheckoutPage()) return false;
        var changed = false;
        var textNorm = normalizeText(raw);

        var parseSpokenEmail = function (source) {
            var s = String(source || '');
            var explicit = s.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
            if (explicit) return explicit[0];

            var tail = s.replace(/.*(?:my\s+)?email(?:\s+address)?\s*(?:is|=|:)?/i, '').trim();
            if (!tail) return '';

            // Convert common speech-to-text forms: "name at gmail dot com".
            var e = tail
                .toLowerCase()
                .replace(/\bat\s+the\s+rate\b/g, '@')
                .replace(/\bat[-\s]?rate\b/g, '@')
                .replace(/\battherate\b/g, '@')
                .replace(/\s*\(at\)\s*/g, '@')
                .replace(/\s+at\s+/g, '@')
                .replace(/\s*\(dot\)\s*/g, '.')
                .replace(/\s+dot\s+/g, '.')
                .replace(/\s+underscore\s+/g, '_')
                .replace(/\s+dash\s+/g, '-')
                .replace(/\s+plus\s+/g, '+')
                .replace(/\s+/g, '')
                .replace(/[,;]+$/g, '')
                .replace(/[^a-z0-9@._+\-]/g, '');

            // Remove accidental words captured after email.
            e = e.replace(/(thanks|thankyou|ok|okay|please).*$/i, '');

            return e;
        };

        var emailMatch = raw.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
        var emailIntent = lower.indexOf('email') !== -1 || !!emailMatch;
        if (emailIntent) {
            var parsedEmail = parseSpokenEmail(raw);
            if (parsedEmail && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(parsedEmail)) {
                changed = setFieldValue('email', parsedEmail) || changed;
                memory.email = parsedEmail;
            } else {
                addMessage('assistant', 'Email samajh nahi aaya. Example bolo: "email is ameeq at gmail dot com".');
                return true;
            }
        }

        // Strict field parsing to avoid cross-filling first/last names.
        var extractFieldValue = function (source, pattern, stopWordsRegex) {
            var m = source.match(pattern);
            if (!m || !m[1]) return '';
            var value = m[1].trim();
            if (!value) return '';
            value = value.replace(stopWordsRegex, '').trim();
            return value.split(/\s+/)[0] || '';
        };

        var lastValue = extractFieldValue(
            textNorm,
            /(?:^|\b)(?:last\s*name|lname|surname)\s*(?:is|=|:)?\s*([a-z][a-z\s'-]*)/i,
            /\b(first\s*name|fname|email|phone|mobile|note|order)\b.*$/i
        );
        if (lastValue) {
            changed = setFieldValue('last_name', lastValue) || changed;
            memory.lastName = lastValue;
        }

        var firstValue = extractFieldValue(
            textNorm,
            /(?:^|\b)(?:first\s*name|fname)\s*(?:is|=|:)?\s*([a-z][a-z\s'-]*)/i,
            /\b(last\s*name|lname|surname|email|phone|mobile|note|order)\b.*$/i
        );
        if (firstValue) {
            changed = setFieldValue('first_name', firstValue) || changed;
            memory.firstName = firstValue;
        }

        if (!firstValue && !lastValue && (lower.indexOf('full name') !== -1 || lower.indexOf('my full name') !== -1)) {
            var full = raw.replace(/.*(my full name|full name)\s*(is|=|:)?/i, '').trim();
            if (full) {
                var parts = parseName(full);
                changed = setFieldValue('first_name', parts.first) || changed;
                if (parts.last) changed = setFieldValue('last_name', parts.last) || changed;
                memory.firstName = parts.first;
                memory.lastName = parts.last;
                memory.fullName = full;
            }
        }

        // Keep phone strict to avoid accidental assignment from unrelated numbers.
        var cleanedDigits = raw.replace(/[^0-9+]/g, '');
        var phoneIntent = lower.indexOf('phone') !== -1 || lower.indexOf('mobile') !== -1 || lower.indexOf('whatsapp number') !== -1;
        if (phoneIntent && cleanedDigits.length >= 7) {
            changed = setFieldValue('phone', cleanedDigits) || changed;
            memory.phone = cleanedDigits;
        }

        if (lower.indexOf('order note') !== -1 || lower.indexOf('notes') !== -1 || lower.indexOf('note') !== -1) {
            var note = raw.replace(/.*(order note|notes|note)\s*(is|=|:)?/i, '').trim();
            if (note) {
                changed = setFieldValue('notes', note) || changed;
                memory.notes = note;
            }
        }

        if (changed) {
            addMessage('assistant', 'Checkout details updated.');
            return true;
        }
        return false;
    }

    function submitCheckoutOrder() {
        var form = document.getElementById('checkoutForm');
        if (!form) return false;

        var requiredFields = ['email', 'first_name', 'last_name', 'phone'];
        var missing = requiredFields.filter(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            return !field || !String(field.value || '').trim();
        });

        if (missing.length) {
            addMessage('assistant', 'Please complete required fields first: email, first name, last name, and phone.');
            return true;
        }

        var emailField = form.querySelector('[name="email"]');
        var emailVal = emailField ? String(emailField.value || '').trim() : '';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
            addMessage('assistant', 'Please enter a valid email address.');
            return true;
        }
        // Use requestSubmit/click so page submit handlers and validations still run.
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            var submitBtn = document.querySelector('button[form="checkoutForm"][type="submit"], #checkoutForm button[type="submit"], .place-order[form="checkoutForm"]');
            if (submitBtn) submitBtn.click();
            else form.submit();
        }
        addMessage('assistant', 'Placing your order now.');
        return true;
    }

    function isPlaceOrderIntent(lower) {
        return /\bplace\s*order(?:\s*(?:&|and)\s*pay)?\b|\bsubmit\s*order\b|\bbuy\s*now\b/.test(lower);
    }

    function clickPlaceOrderButton() {
        var candidates = Array.prototype.slice.call(
            document.querySelectorAll('.place-order, button[form="checkoutForm"], #checkoutForm button[type="submit"]')
        );
        var btn = candidates.find(function (el) {
            var txt = normalizeText(el.textContent || '');
            return txt.indexOf('place order') !== -1 || txt.indexOf('pay') !== -1;
        }) || candidates[0];
        if (!btn) return false;
        btn.click();
        addMessage('assistant', 'Placing your order now.');
        return true;
    }

    function confirmIfPending(text) {
        var confirmWords = ['yes', 'confirm', 'ok', 'okay', 'sure', 'haan', 'ha', 'jee', 'theek', 'thik'];
        var lower = text.toLowerCase();
        var confirmed = confirmWords.some(function (w) { return lower.indexOf(w) !== -1; });
        if (confirmed && pendingAction) {
            var action = pendingAction;
            pendingAction = null;
            action();
            return true;
        }
        return false;
    }

    function handleIntent(text) {
        var raw = (text || '').trim();
        if (!raw) return;

        addMessage('user', raw);

        if (confirmIfPending(raw)) return;

        var lower = normalizeIntentLower(raw.toLowerCase());

        if (handleOpenTrailerByName(raw, lower)) return;

        if (handleCloseTrailerIntent(lower)) return;

        if (handleMoviesSearchIntent(raw, lower)) return;

        if (isDirectPricingBuyPackageIntent(lower)) {
            if (hasPricingPackagesSection()) {
                if (handlePricingSectionIntent(raw, lower)) return;
            } else {
                requestPricingActionOnPricingPage(raw);
                return;
            }
        }

        if (shouldRouteToPricingForBuy(raw, lower)) {
            requestPricingActionOnPricingPage(raw);
            return;
        }

        if (handlePricingSectionIntent(raw, lower)) return;

        if (handleShopBuyIntent(raw, lower)) return;

        if (isPlaceOrderIntent(lower)) {
            if (isCheckoutPage() && clickPlaceOrderButton()) return;
            if (submitCheckoutOrder()) return;
        }

        if (pickConfigDevice(lower)) return;
        if (pickConfigVendor(lower)) return;
        if (setPackageTabByIntent(lower)) return;
        if (pickConfigConnection(lower)) return;
        if (pickConfigPackageByName(raw, lower)) return;
        if (pickConfigPackage(lower)) return;
        if (setCheckoutFormFields(raw, lower)) return;

        if (setCheckoutType(lower)) return;
        if (setPaymentMethod(lower)) return;

        if (handleGenericClickByText(raw, lower)) return;

        var checkoutKeywords = ['first name', 'last name', 'full name', 'email', 'phone', 'mobile', 'whatsapp', 'package type', 'payment', 'card', 'crypto', 'note'];
        var looksCheckoutCommand = checkoutKeywords.some(function (k) { return lower.indexOf(k) !== -1; });
        if (isCheckoutPage() && looksCheckoutCommand) {
            addMessage('assistant', 'Use clear commands like: "first name Ali", "last name Khan", "email ali@gmail.com", "phone 923001234567", "pay with card", "place order and pay".');
            return;
        }

        if (
            lower.indexOf('continue checkout') !== -1 ||
            lower.indexOf('continue to checkout') !== -1 ||
            lower.indexOf('next step') !== -1
        ) {
            if (continueFromConfigure()) return;
            return openRoute('checkout', 'checkout');
        }

        if (handleDownloadIntent(raw, lower)) return;

        if (lower.indexOf('help') !== -1 || lower.indexOf('kya kar') !== -1) {
            addMessage('assistant', 'I can open pages, guide checkout, and fill forms. Try: "open pricing", "checkout", "contact".');
            return;
        }

        if (lower.indexOf('open') !== -1 || lower.indexOf('go to') !== -1 || lower.indexOf('show') !== -1 || lower.indexOf('visit') !== -1) {
            if (lower.indexOf('pricing') !== -1 || lower.indexOf('price') !== -1) return openRoute('pricing', 'pricing');
            if (lower.indexOf('package') !== -1) return openRoute('packages', 'packages');
            if (lower.indexOf('reseller') !== -1) return openRoute('reseller', 'reseller panel');
            if (lower.indexOf('app') !== -1 || lower.indexOf('application') !== -1) return openRoute('apps', 'IPTV applications');
            if (lower.indexOf('movie') !== -1 || lower.indexOf('vod') !== -1 || lower.indexOf('series') !== -1) return openRoute('movies', 'movies');
            if (lower.indexOf('shop') !== -1 || lower.indexOf('device') !== -1) return openRoute('shop', 'shop');
            if (lower.indexOf('blog') !== -1) return openRoute('blogs', 'blogs');
            if (lower.indexOf('faq') !== -1) return openRoute('faqs', 'FAQs');
            if (lower.indexOf('contact') !== -1 || lower.indexOf('support') !== -1) return openRoute('contact', 'contact');
            if (lower.indexOf('about') !== -1) return openRoute('about', 'about');
            if (lower.indexOf('home') !== -1) return openRoute('home', 'home');
            if (lower.indexOf('activate') !== -1) return openRoute('activate', 'activate');
            if (lower.indexOf('configure') !== -1) return openRoute('configure', 'configure');
            if (lower.indexOf('checkout') !== -1 || lower.indexOf('pay') !== -1 || lower.indexOf('order') !== -1) {
                return openRoute('checkout', 'checkout');
            }
        }

        if (lower.indexOf('checkout') !== -1 || lower.indexOf('pay') !== -1 || lower.indexOf('order') !== -1) {
            if (continueFromConfigure()) return;
            setPending(function () {
                var form = document.getElementById('checkoutForm');
                if (form) {
                    form.submit();
                    addMessage('assistant', 'Submitting your checkout now.');
                } else {
                    openRoute('checkout', 'checkout');
                }
            }, 'I can proceed with checkout.');
            return;
        }

        if (lower.indexOf('send message') !== -1 || lower.indexOf('submit') !== -1) {
            setPending(function () {
                var form = document.getElementById('contact-form');
                if (form) {
                    form.submit();
                    addMessage('assistant', 'Sending your message.');
                } else {
                    addMessage('assistant', 'I could not find the contact form on this page.');
                }
            }, 'I can submit the form.');
            return;
        }

        if (!isCheckoutPage() && (lower.indexOf('my name is') !== -1 || lower.indexOf('mera naam') !== -1 || lower.indexOf('name is') !== -1)) {
            var nameText = raw.replace(/.*(my name is|name is|mera naam)/i, '').trim();
            if (nameText) {
                memory.fullName = nameText;
                var parts = parseName(nameText);
                memory.firstName = parts.first;
                memory.lastName = parts.last;
                fillContactForm();
                fillCheckoutForm();
                addMessage('assistant', 'Got it. I saved your name.');
                return;
            }
        }

        if (!isCheckoutPage() && lower.indexOf('email') !== -1 && raw.indexOf('@') !== -1) {
            var email = raw.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
            if (email) {
                memory.email = email[0];
                fillContactForm();
                fillCheckoutForm();
                addMessage('assistant', 'Email saved.');
                return;
            }
        }

        if (!isCheckoutPage() && (lower.indexOf('phone') !== -1 || lower.indexOf('mobile') !== -1)) {
            var phone = raw.replace(/[^0-9+]/g, '');
            if (phone.length >= 7) {
                memory.phone = phone;
                fillContactForm();
                fillCheckoutForm();
                addMessage('assistant', 'Phone number saved.');
                return;
            }
        }

        if (!isCheckoutPage() && (lower.indexOf('message') !== -1 || lower.indexOf('note') !== -1)) {
            var msg = raw.replace(/.*(message|note)/i, '').trim();
            if (msg) {
                memory.message = msg;
                memory.notes = msg;
                fillContactForm();
                fillCheckoutForm();
                addMessage('assistant', 'Message saved.');
                return;
            }
        }

        if (lower.indexOf('terms') !== -1) return openRoute('terms', 'terms of service');
        if (lower.indexOf('privacy') !== -1) return openRoute('privacy', 'privacy policy');
        if (lower.indexOf('refund') !== -1) return openRoute('refund', 'refund policy');

        addMessage('assistant', 'I can help open pages or fill forms. Try: "open pricing", "checkout", or "contact".');
    }

    sendBtn.addEventListener('click', function () {
        handleIntent(input.value);
        input.value = '';
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendBtn.click();
        }
    });

    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    var recognizer = null;
    function startListening() {
        if (!recognizer || listening) return;
        try {
            listening = true;
            micBtn.classList.add('listening');
            recognizer.start();
        } catch (e) {
            listening = false;
            micBtn.classList.remove('listening');
        }
    }

    function stopListening() {
        if (!recognizer) return;
        setContinuousListening(false);
        try { recognizer.stop(); } catch (e) {}
        listening = false;
        micBtn.classList.remove('listening');
    }

    if (SpeechRecognition) {
        recognizer = new SpeechRecognition();
        recognizer.lang = document.documentElement.lang || 'en-US';
        recognizer.interimResults = false;
        recognizer.onresult = function (event) {
            var transcript = event.results[0][0].transcript || '';
            var confidence = event.results[0][0].confidence;
            var cleanTranscript = String(transcript || '').trim();

            if (!cleanTranscript) return;
            if (isSpeaking) return;
            if (Date.now() < speakMuteUntil) return;

            // Prevent duplicate handling for same phrase in short interval.
            if (cleanTranscript === lastHandledTranscript && (Date.now() - lastHandledAt) < 2500) return;
            lastHandledTranscript = cleanTranscript;
            lastHandledAt = Date.now();

            // Ignore noisy/very-short transcripts in continuous mode.
            if (isContinuousListeningEnabled()) {
                if (typeof confidence === 'number' && confidence > 0 && confidence < 0.45) return;
                if (cleanTranscript.length < 4) return;
            }
            handleIntent(cleanTranscript);
        };
        recognizer.onend = function () {
            listening = false;
            micBtn.classList.remove('listening');
            if (isContinuousListeningEnabled() && !isCheckoutCompletePage()) {
                setTimeout(function () {
                    startListening();
                }, 250);
            }
        };
    } else {
        micBtn.disabled = true;
        micBtn.title = 'Voice input not supported in this browser.';
    }

    micBtn.addEventListener('click', function () {
        if (!recognizer) return;
        if (listening) {
            stopListening();
            return;
        }
        setContinuousListening(true);
        startListening();
    });

    tryPendingDownload();
    tryPendingPricingAction();
    tryPendingShopAction();
    tryPendingMoviesSearch();
    initOnboardingGuide();

    // Clear old persistent mode from previous versions.
    try { localStorage.removeItem(LEGACY_LISTEN_KEY); } catch (e) {}

    if (isCheckoutCompletePage()) {
        stopListening();
    } else if (isContinuousListeningEnabled() && recognizer) {
        openPanel();
        startListening();
    }
})();
