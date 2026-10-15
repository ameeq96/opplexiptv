(function () {
    'use strict';

    const aliases = {
        client: ['client', 'clients'], admin: ['admin', 'admins'], adminUser: ['adminUser', 'admins'],
        order: ['order', 'orders'], digitalOrder: ['digitalOrders'], purchasing: ['purchasing', 'purchases'], purchase: ['purchases'],
        blog: ['blog', 'blogs'], product: ['product', 'products'], p: ['product', 'products', 'purchases', 'packages'],
        service: ['service', 'services'], s: ['services'], testimonial: ['testimonial', 'testimonials'], t: ['testimonials'],
        logo: ['logo', 'logos'], item: ['item', 'items'], i: ['item', 'items'], c: ['items'],
        package: ['package', 'packages'], link: ['link', 'links', 'externalLinks'], l: ['link', 'links', 'logos'],
        section: ['section'], setting: ['setting'], click: ['clicks', 'trialClicks'], r: ['clicks'],
        activity: ['activities'], followUp: ['followUps'], conversation: ['conversation', 'conversations'],
        message: ['messages', 'unlinkedMessages'], draft: ['checkoutDrafts'], delivery: ['marketingDeliveries'],
        referral: ['referrals'], auditLog: ['auditLog', 'logs'], log: ['logs'], record: ['records']
    };
    let pendingRead;

    function canonical(input) {
        const url = new URL(input, window.location.href);
        if (url.origin !== window.location.origin || !/^\/admin(?:\/|$)/.test(url.pathname)) return null;
        url.pathname = '/api/browser' + url.pathname;
        return url.pathname + url.search;
    }

    function feedback(message, danger = false) {
        let region = document.getElementById('adminApiFeedback');
        if (!region) {
            region = document.createElement('div');
            region.id = 'adminApiFeedback';
            region.setAttribute('role', 'status');
            (document.getElementById('adminContent') || document.querySelector('.admin-auth-card') || document.body).prepend(region);
        }
        region.className = danger ? 'alert alert-danger' : 'alert alert-info';
        region.textContent = message;
        region.hidden = !message;
    }

    function updateCsrf(body) {
        if (!body?.csrf_token) return;
        document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', body.csrf_token);
        document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = body.csrf_token; });
    }

    function redirect(body) {
        if (!body?.redirect_url) return false;
        const url = new URL(body.redirect_url, window.location.origin);
        if (url.origin !== window.location.origin || !/^\/admin(?:\/|$)/.test(url.pathname)) return false;
        window.location.assign(url.href);
        return true;
    }

    function at(value, path) {
        return path.split('.').reduce((item, key) => item == null ? undefined : item[key], value);
    }

    function findRecord(value, id, includeChildren = false) {
        if (!value || typeof value !== 'object') return undefined;
        const records = Array.isArray(value) ? value : Array.isArray(value.data) ? value.data : [value];
        for (const record of records) {
            if (!record || typeof record !== 'object') continue;
            if (String(record.id) === String(id)) return record;
            if (includeChildren) {
                const found = findRecord(record.children, id, true);
                if (found) return found;
            }
        }
        return undefined;
    }

    function readBinding(data, element) {
        const object = element.dataset.adminApiObject;
        if (!object) return at(data, element.dataset.adminApiField);
        for (const root of aliases[object] || [object]) {
            const value = data[root];
            const record = element.dataset.adminApiId ? findRecord(value, element.dataset.adminApiId, root === 'items') : value;
            let field = record && at(record, element.dataset.adminApiField);
            if (record && element.dataset.adminApiField.startsWith('translation.')) {
                const locale = data.locale || document.documentElement.lang || 'en';
                const translations = Array.isArray(record.translations) ? record.translations : [];
                field = at(translations.find(row => row.locale === locale) || translations.find(row => row.locale === 'en') || translations[0], element.dataset.adminApiField.slice(12));
            }
            if (field !== undefined) return field;
        }
        return undefined;
    }

    function formatted(value, element) {
        if (element.dataset.adminApiFormat === 'boolean') return value ? (element.dataset.adminApiYes || 'Active') : (element.dataset.adminApiNo || 'Inactive');
        if (value === null || value === '') return element.dataset.adminApiEmpty || '';
        if (element.dataset.adminApiFormat === 'money') return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (element.dataset.adminApiFormat === 'integer') return Number(value).toLocaleString('en-US', { maximumFractionDigits: 0 });
        if (element.dataset.adminApiFormat === 'title') return String(value).charAt(0).toUpperCase() + String(value).slice(1);
        if (element.dataset.adminApiLimit) return String(value).length > Number(element.dataset.adminApiLimit) ? String(value).slice(0, Number(element.dataset.adminApiLimit)) + '...' : String(value);
        return String(value);
    }

    function hydrateForm(data) {
        const resource = ['client', 'order', 'purchasing', 'blog', 'product', 'service', 'testimonial', 'logo', 'item', 'package', 'section', 'setting', 'link', 'adminUser']
            .map(key => data[key]).find(value => value && typeof value === 'object' && !Array.isArray(value));
        if (!resource) return;
        document.querySelectorAll('form').forEach(form => {
            if (!canonical(form.action) || form.method.toLowerCase() === 'get' || form.dataset.adminApiDirty === '1' || form.querySelector('.is-invalid')) return;
            const method = form.querySelector('[name="_method"]')?.value;
            if (!['PUT', 'PATCH'].includes(method?.toUpperCase())) return;
            const paymentSelect = form.querySelector('select[name="payment_method"]');
            const customPaymentInput = form.querySelector('[name="custom_payment_method"]');
            const mapPayment = paymentSelect && customPaymentInput && resource.payment_method !== undefined && [...paymentSelect.options].some(option => option.value === 'other');
            const paymentMethod = String(resource.payment_method ?? '');
            const paymentOption = mapPayment ? [...paymentSelect.options].find(option => option.value !== 'other' && option.value.toLowerCase() === paymentMethod.toLowerCase()) : null;
            const customPayment = mapPayment && paymentMethod !== '' && !paymentOption;
            const packageSelect = form.querySelector('select[name="package"]');
            const customPackageInput = form.querySelector('[name="custom_package"]');
            const mapPackage = packageSelect && customPackageInput && resource.package !== undefined && [...packageSelect.options].some(option => option.value === 'other');
            const packageName = String(resource.package ?? '');
            const packageOption = mapPackage ? [...packageSelect.options].find(option => option.value !== 'other' && option.value === packageName) : null;
            const customPackage = mapPackage && packageName !== '' && !packageOption;
            [...form.elements].forEach(field => {
                if (!field.name || field.type === 'hidden' || field.type === 'file' || field.type === 'password' || field.readOnly) return;
                const translation = field.name.match(/^translations\[([^\]]+)\]\[([^\]]+)\]$/);
                let value = translation ? resource.translations?.find(row => row.locale === translation[1])?.[translation[2]] : at(resource, field.name.replace(/\[([^\]]+)\]/g, '.$1'));
                if (mapPayment && field === paymentSelect) value = paymentOption ? paymentOption.value : customPayment ? 'other' : '';
                if (mapPayment && field === customPaymentInput) value = customPayment ? paymentMethod : '';
                if (mapPackage && field === packageSelect) value = packageOption ? packageOption.value : customPackage ? 'other' : '';
                if (mapPackage && field === customPackageInput) value = customPackage ? (packageName === 'other' ? resource.custom_package ?? '' : packageName) : '';
                if (value === undefined || value === '[Private signed URL omitted]' || field.name.includes('password')) return;
                if (field.type === 'checkbox' || field.type === 'radio') field.checked = field.type === 'checkbox' ? Boolean(Number(value)) : String(value) === field.value;
                else {
                    if (translation?.[2] === 'schema_json' && value !== null && typeof value === 'object') value = JSON.stringify(value, null, 2);
                    else if (Array.isArray(value)) value = value.join('\n');
                    if (field.type === 'date' && value) value = String(value).slice(0, 10);
                    if (field.type === 'datetime-local' && value) value = String(value).slice(0, 16);
                    field.value = value ?? '';
                    if (field.tagName === 'SELECT' && window.jQuery) window.jQuery(field).trigger('change.select2');
                }
            });
            const customPaymentSection = form.querySelector('#other-payment-method');
            if (mapPayment && customPaymentSection) customPaymentSection.style.display = customPayment ? 'block' : 'none';
            const customPackageSection = form.querySelector('#custom_package_field');
            if (mapPackage && customPackageSection) customPackageSection.style.display = customPackage ? 'block' : 'none';
        });
    }

    function hydrate(body) {
        updateCsrf(body);
        const data = body.data;
        if (!data || typeof data !== 'object') return;
        document.querySelectorAll('[data-admin-api-field]').forEach(element => {
            const value = readBinding(data, element);
            if (value !== undefined) element.textContent = formatted(value, element);
        });
        hydrateForm(data);
        if (body.view === 'auth.login') {
            const field = document.getElementById('admin-captcha');
            if (field) { field.placeholder = `${data.num1} + ${data.num2} = ?`; field.value = ''; }
        }
        if (body.view === 'dashboard' && window.Chart) {
            const earnings = window.Chart.getChart?.('earningsChart');
            if (earnings && data.earningsByCurrency) {
                earnings.data.labels = Object.keys(data.earningsByCurrency);
                earnings.data.datasets[0].data = Object.values(data.earningsByCurrency);
                earnings.update();
            }
            const purchasing = window.Chart.getChart?.('purchasingChart');
            if (purchasing && data.purchasingSeriesForChart) {
                const series = data.purchasingSeriesForChart;
                const labels = [...new Set(series.flatMap(row => row.data.map(point => point.x)))].sort();
                const colors = ['#5658d9', '#159c89', '#ce9237', '#4c8bc7', '#ad67ba', '#d27177'];
                purchasing.data.labels = labels;
                purchasing.data.datasets = series.map((row, index) => ({
                    label: row.name, data: labels.map(date => Number(row.data.find(point => point.x === date)?.y || 0)),
                    borderColor: colors[index % colors.length], backgroundColor: colors[index % colors.length],
                    borderWidth: 2, pointRadius: 3, pointHoverRadius: 5, fill: false, tension: 0.25
                }));
                purchasing.update();
            }
        }
        document.documentElement.dataset.adminApiState = 'ready';
        document.dispatchEvent(new CustomEvent('admin-api:loaded', { detail: { view: body.view, data } }));
    }

    async function load() {
        const endpoint = canonical(window.location.href);
        if (!endpoint || !window.OpplexApi || pendingRead) return;
        document.documentElement.dataset.adminApiState = 'loading';
        pendingRead = window.OpplexApi.request(endpoint, { method: 'GET' });
        try {
            const body = await pendingRead;
            if (!body.data && body.redirect_url) { redirect(body); return; }
            hydrate(body);
        } catch (error) {
            document.documentElement.dataset.adminApiState = 'error';
            if ([401, 403, 428].includes(error.status) && redirect(error.body)) return;
            feedback(error.body?.message || 'Current data could not be refreshed. The displayed page may be older; reload before making changes.', true);
        } finally { pendingRead = null; }
    }

    async function submit(form, submitter) {
        if (form.dataset.adminApiBusy === '1') return;
        const endpoint = canonical(submitter?.getAttribute('formaction') || form.action);
        if (!endpoint) return;
        const body = new FormData(form);
        if (submitter?.name) body.append(submitter.name, submitter.value);
        form.dataset.adminApiBusy = '1';
        form.setAttribute('aria-busy', 'true');
        const buttons = [...form.querySelectorAll('button[type="submit"],input[type="submit"]')];
        const wasDisabled = buttons.map(button => button === submitter ? false : button.disabled);
        buttons.forEach(button => { button.disabled = true; });
        feedback('Submitting request… Wait for confirmation.');
        try {
            const result = await window.OpplexApi.request(endpoint, { method: 'POST', body });
            updateCsrf(result);
            feedback(result.message || 'Saved successfully.');
            form.dispatchEvent(new CustomEvent('data-api-success', { bubbles: true, detail: result }));
            if (!redirect(result)) await load();
        } catch (error) {
            updateCsrf(error.body);
            if ([401, 403, 428].includes(error.status) && redirect(error.body)) return;
            const errors = error.body?.errors || {};
            Object.entries(errors).forEach(([name, messages]) => {
                const bracketName = name.replace(/\.([^.]*)/g, '[$1]');
                const field = [...form.elements].find(input => input.name === name || input.name === bracketName);
                if (field) { field.classList.add('is-invalid'); field.setAttribute('aria-invalid', 'true'); }
            });
            const messages = Object.values(errors).flat().join(' ');
            feedback(messages || error.body?.message || 'Completion was not confirmed. Check the result before trying again.', true);
            if (window.location.pathname === '/admin/login') await load();
        } finally {
            delete form.dataset.adminApiBusy;
            form.removeAttribute('aria-busy');
            buttons.forEach((button, index) => { button.disabled = wasDisabled[index]; });
        }
    }

    document.addEventListener('input', event => { if (event.target.form) event.target.form.dataset.adminApiDirty = '1'; });
    document.addEventListener('change', event => { if (event.target.form) event.target.form.dataset.adminApiDirty = '1'; });
    window.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented || !window.OpplexApi || form.hasAttribute('data-api-form') || form.method.toLowerCase() === 'get') return;
        if (!canonical(event.submitter?.getAttribute('formaction') || form.action)) return;
        event.preventDefault();
        submit(form, event.submitter);
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', load, { once: true });
    else load();
})();
