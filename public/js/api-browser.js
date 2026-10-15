(function () {
    'use strict';
    if (window.OpplexApi) return;

    function safeNavigation(value) {
        if (typeof value !== 'string' || !value) return null;
        try {
            var url = new URL(value, window.location.href);
            if (url.username || url.password) return null;
            var loopback = ['127.0.0.1', 'localhost', '[::1]'];
            if (loopback.includes(window.location.hostname) && loopback.includes(url.hostname) && /^https?:$/.test(url.protocol)) return url.href;
            if (url.origin === window.location.origin || (url.protocol === 'https:' && ['opplexify.com', 'www.opplexify.com'].includes(url.hostname))) return url.href;
        } catch (_) {}
        return null;
    }

    async function request(value, options) {
        var url = new URL(value, window.location.href);
        if (url.origin !== window.location.origin || !/^https?:$/.test(url.protocol)) throw new Error('API requests must use this website.');
        var settings = Object.assign({ credentials: 'same-origin', cache: 'no-store', redirect: 'error' }, options || {});
        var headers = new Headers(settings.headers || {});
        headers.set('Accept', 'application/json');
        headers.set('X-Requested-With', 'XMLHttpRequest');
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token && !headers.has('X-CSRF-TOKEN')) headers.set('X-CSRF-TOKEN', token.content);
        if (settings.body && !(settings.body instanceof FormData) && typeof settings.body !== 'string') {
            headers.set('Content-Type', 'application/json');
            settings.body = JSON.stringify(settings.body);
        }
        settings.headers = headers;
        var response;
        try { response = await fetch(url.href, settings); }
        catch (cause) {
            var network = new Error('Connection interrupted. Check the result before retrying; the request may have completed.');
            network.status = 0;
            network.cause = cause;
            throw network;
        }
        var body;
        try { body = await response.json(); }
        catch (_) {
            var invalid = new Error('The server did not return an API response. Please refresh and try again.');
            invalid.status = response.status;
            throw invalid;
        }
        if (body && typeof body.csrf_token === 'string' && token) token.content = body.csrf_token;
        if (!response.ok) {
            var error = new Error(body.message || 'The request could not be completed.');
            error.status = response.status;
            error.body = body;
            throw error;
        }
        return body;
    }

    function feedback(form, message, errors) {
        var region = form.querySelector('[data-api-feedback]');
        if (!region) {
            region = document.createElement('div');
            region.setAttribute('data-api-feedback', '');
            region.setAttribute('role', 'status');
            region.setAttribute('aria-live', 'polite');
            region.style.marginBottom = '1rem';
            form.prepend(region);
        }
        region.replaceChildren();
        region.textContent = message || '';
        if (errors && typeof errors === 'object') {
            var list = document.createElement('ul');
            Object.keys(errors).forEach(function (name) {
                var messages = Array.isArray(errors[name]) ? errors[name] : [errors[name]];
                messages.forEach(function (message) {
                    var item = document.createElement('li');
                    item.textContent = String(message);
                    list.appendChild(item);
                });
                var fieldName = name.replace(/\.([^.]*)/g, '[$1]');
                var field = Array.from(form.elements).find(function (element) { return element.name === name || element.name === fieldName; });
                if (field) field.setAttribute('aria-invalid', 'true');
            });
            region.appendChild(list);
        }
        region.hidden = !region.textContent;
    }

    async function submitForm(form, submitter) {
        if (form.dataset.apiBusy === 'true') return;
        if (!form.reportValidity()) return;
        var payload = new FormData(form);
        if (submitter && submitter.name) payload.append(submitter.name, submitter.value);
        var action = submitter && submitter.hasAttribute('formaction') ? submitter.formAction : form.action;
        action = form.getAttribute('data-api-action') || action;
        var method = (payload.get('_method') || form.getAttribute('method') || 'GET').toUpperCase();
        var url = new URL(action, window.location.href);
        var settings = { method: method };
        if (method === 'GET') {
            payload.forEach(function (value, key) { if (typeof value === 'string' && key !== '_token') url.searchParams.append(key, value); });
        } else {
            // PHP reads multipart uploads through POST; preserve Laravel's method override.
            if (method === 'PUT' || method === 'PATCH' || method === 'DELETE') { payload.set('_method', method); settings.method = 'POST'; }
            settings.body = payload;
        }
        form.dataset.apiBusy = 'true';
        form.setAttribute('aria-busy', 'true');
        var buttons = Array.from(form.querySelectorAll('button[type="submit"],input[type="submit"],button:not([type])'));
        var states = buttons.map(function (button) { return button.disabled; });
        buttons.forEach(function (button) { button.disabled = true; });
        form.querySelectorAll('[aria-invalid="true"]').forEach(function (field) { field.removeAttribute('aria-invalid'); });
        feedback(form, 'Saving…');
        try {
            var body = await request(url.href, settings);
            feedback(form, body.message || 'Saved successfully.');
            var follow = form.dispatchEvent(new CustomEvent('data-api-success', { bubbles: true, cancelable: true, detail: body }));
            var destination = safeNavigation(body.redirect_url);
            if (follow && destination && !form.hasAttribute('data-api-no-redirect')) window.location.assign(destination);
        } catch (error) {
            feedback(form, error.message, error.body && error.body.errors);
            form.dispatchEvent(new CustomEvent('data-api-error', { bubbles: true, detail: error }));
            var destination = safeNavigation(error.body && error.body.redirect_url);
            if (destination) {
                var link = document.createElement('a');
                link.href = destination;
                link.textContent = 'Continue to the required security step';
                form.querySelector('[data-api-feedback]').appendChild(link);
            }
        } finally {
            delete form.dataset.apiBusy;
            form.removeAttribute('aria-busy');
            buttons.forEach(function (button, index) { button.disabled = states[index]; });
        }
    }

    window.OpplexApi = { request: request, safeNavigation: safeNavigation, submitForm: submitForm, feedback: feedback };
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-api-form') || event.defaultPrevented) return;
        event.preventDefault();
        submitForm(form, event.submitter);
    });
})();
