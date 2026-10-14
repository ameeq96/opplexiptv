<!DOCTYPE html>
<html lang="{{ admin_locale() }}" dir="{{ admin_dir() }}">
<head>
    <meta charset="UTF-8">
    <title>
        @hasSection('title')
            @yield('title')
        @else
            @yield('page_title', 'Admin Dashboard')
        @endif
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    @vite('resources/css/admin.css')
    @stack('styles')
</head>
<body class="admin-body">
<a class="admin-skip-link" href="#adminContent">Skip to content</a>
<div class="admin-shell">
    @include('admin.partials.sidebar')
    <div class="admin-main">
        @include('admin.partials.header')
        @include('admin.partials.flash')
        <main class="admin-content" id="adminContent" tabindex="-1">
            <div class="alert alert-warning" id="adminValidationNotice" role="alert" hidden>Please review the required or invalid fields. Nothing has been submitted.</div>
            @yield('content')
        </main>
        @includeIf('admin.partials.footer')
    </div>
</div>
<div class="admin-request-feedback" id="adminRequestFeedback" role="status" hidden>
    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
    <span id="adminRequestMessage"></span>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('adminSidebarOverlay');
        if (!sidebar) return;
        const open = sidebar.classList.toggle('show');
        document.body.classList.toggle('admin-sidebar-open', open);
        document.querySelectorAll('[data-admin-sidebar="toggle"]').forEach(button => button.setAttribute('aria-expanded', String(open)));
        if (overlay) overlay.hidden = !open;
        if (open) sidebar.querySelector('[data-admin-sidebar="close"]')?.focus();
    }
    function closeAdminSidebar(restoreFocus = false) {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('adminSidebarOverlay');
        if (sidebar) sidebar.classList.remove('show');
        document.body.classList.remove('admin-sidebar-open');
        document.querySelectorAll('[data-admin-sidebar="toggle"]').forEach(button => button.setAttribute('aria-expanded', 'false'));
        if (overlay) overlay.hidden = true;
        if (restoreFocus) document.querySelector('[data-admin-sidebar="toggle"]')?.focus();
    }
    document.addEventListener('click', (e) => {
        const action = e.target.closest?.('[data-admin-sidebar]')?.dataset.adminSidebar;
        if (action === 'toggle') {
            toggleAdminSidebar();
        }
        if (action === 'close') {
            closeAdminSidebar(true);
        }
    });
    document.addEventListener('keydown', (e) => {
        const sidebar = document.querySelector('.admin-sidebar.show');
        if (!sidebar || window.innerWidth >= 992) return;
        if (e.key === 'Escape') closeAdminSidebar(true);
        if (e.key !== 'Tab') return;
        const controls = [...sidebar.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')].filter(control => control.getClientRects().length);
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last?.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first?.focus();
        }
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) closeAdminSidebar();
    });

    const pageSearch = document.getElementById('adminPageSearch');
    if (pageSearch) {
        pageSearch.closest('.admin-nav-search').hidden = false;
        const navigation = document.getElementById('adminNavigation');
        const clearSearch = document.getElementById('adminPageSearchClear');
        const filterPages = () => {
            const query = pageSearch.value.trim().toLocaleLowerCase();
            let matches = 0;
            let heading = null;
            navigation.querySelectorAll(':scope > .nav-section, :scope > a, :scope > form').forEach(item => {
                if (item.classList.contains('nav-section')) {
                    heading = item;
                    heading.hidden = Boolean(query);
                } else {
                    item.hidden = Boolean(query) && !item.textContent.toLocaleLowerCase().includes(query);
                    if (!item.hidden) {
                        if (heading) heading.hidden = false;
                        matches++;
                    }
                }
            });
            clearSearch.hidden = !query;
            document.getElementById('adminPageSearchEmpty').hidden = matches > 0;
            document.getElementById('adminPageSearchStatus').textContent = query ? `${matches} matching navigation items` : '';
        };
        pageSearch.addEventListener('input', filterPages);
        clearSearch.addEventListener('click', () => {
            pageSearch.value = '';
            filterPages();
            pageSearch.focus();
        });
        pageSearch.addEventListener('keydown', event => {
            if (event.key === 'Escape' && pageSearch.value) {
                event.preventDefault();
                event.stopPropagation();
                clearSearch.click();
            }
        });
        navigation.querySelector('a.active')?.setAttribute('aria-current', 'page');
    }

    function focusAdminField(field) {
        const selection = field.classList.contains('select2-hidden-accessible')
            ? field.nextElementSibling?.querySelector('.select2-selection') : null;
        const target = selection || field;
        if (target.getClientRects().length) {
            target.scrollIntoView({ block: 'center' });
            target.focus({ preventScroll: true });
        }
    }

    document.querySelectorAll('[data-admin-field-error]').forEach(message => {
        const name = message.dataset.adminFieldError;
        const bracketName = name.replace(/\.([^.]*)/g, '[$1]');
        const field = [...document.querySelectorAll('#adminContent [name]')].find(input =>
            input.name === name || input.name === bracketName || input.name === name.replace(/\.\d+$/, '[]'));
        if (!field || field.type === 'hidden') return;
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        const link = document.createElement('button');
        link.type = 'button';
        link.className = 'admin-error-link';
        link.textContent = message.textContent;
        link.addEventListener('click', () => focusAdminField(field));
        message.replaceWith(link);
    });
    document.querySelector('[data-admin-feedback-error]')?.focus({ preventScroll: true });

    let invalidForm = null;
    document.addEventListener('invalid', event => {
        const field = event.target;
        if (!field.form || !field.closest('#adminContent')) return;
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        document.getElementById('adminValidationNotice').hidden = false;
        event.preventDefault();
        if (!invalidForm) {
            invalidForm = field.form;
            setTimeout(() => {
                const label = field.labels?.[0]?.textContent.replace(/\s+/g, ' ').trim() || field.name;
                document.getElementById('adminValidationNotice').textContent = `${label}: ${field.validationMessage} Review the highlighted fields. Nothing has been submitted.`;
                focusAdminField(field);
                invalidForm = null;
            }, 0);
        }
    }, true);
    ['input', 'change'].forEach(type => document.addEventListener(type, event => {
        const field = event.target;
        if (!field.closest('#adminContent') || !field.matches('.is-invalid') || !field.validity?.valid) return;
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
        if (!document.querySelector('#adminContent .is-invalid')) document.getElementById('adminValidationNotice').hidden = true;
    }));

    const requestFeedback = document.getElementById('adminRequestFeedback');
    let requestTimer;
    let pendingForm;
    let previousBusy;
    function resetAdminRequestFeedback() {
        clearTimeout(requestTimer);
        requestFeedback.hidden = true;
        requestFeedback.classList.remove('is-delayed');
        if (pendingForm) {
            if (previousBusy === null) pendingForm.removeAttribute('aria-busy');
            else pendingForm.setAttribute('aria-busy', previousBusy);
        }
        pendingForm = null;
    }
    window.addEventListener('submit', event => {
        const form = event.target;
        const target = event.submitter?.getAttribute('formtarget') || form.target;
        if (!form.closest('#adminContent') || (target && target !== '_self') || form.method === 'dialog') return;
        queueMicrotask(() => {
            if (event.defaultPrevented) return;
            resetAdminRequestFeedback();
            pendingForm = form;
            previousBusy = form.getAttribute('aria-busy');
            form.setAttribute('aria-busy', 'true');
            requestFeedback.hidden = false;
            document.getElementById('adminRequestMessage').textContent = form.method === 'get'
                ? 'Loading results…' : 'Submitting request… Wait for the server confirmation.';
            requestTimer = setTimeout(() => {
                requestFeedback.classList.add('is-delayed');
                document.getElementById('adminRequestMessage').textContent = 'Still waiting. Completion is not confirmed. Check the result before trying again.';
                if (previousBusy === null) form.removeAttribute('aria-busy');
                else form.setAttribute('aria-busy', previousBusy);
            }, 20000);
        });
    });
    window.addEventListener('pageshow', resetAdminRequestFeedback);

    document.querySelectorAll('[data-slug-target]').forEach((input) => {
        input.addEventListener('input', function () {
            const targetSelector = this.getAttribute('data-slug-target');
            const slugInput = document.querySelector(targetSelector);
            if (!slugInput || slugInput.dataset.touched === '1') return;
            const text = this.value || '';
            const slug = text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
            slugInput.value = slug;
        });
    });

    document.querySelectorAll('[data-slug-input]').forEach((input) => {
        input.addEventListener('input', function () {
            this.dataset.touched = '1';
        });
    });

    function initAdminSelect2() {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
        jQuery('select.form-select').not('.no-select2').each(function () {
            const $el = jQuery(this);
            if ($el.data('select2')) return;
            const placeholder = $el.data('placeholder') || $el.find('option:first').text() || 'Select';
            const hasEmpty = $el.find('option[value=""]').length > 0;
            const $modal = $el.closest('.modal');
            $el.select2({
                width: '100%',
                placeholder,
                allowClear: hasEmpty,
                dropdownParent: $modal.length ? $modal : jQuery('body')
            });
            const selection = $el.next('.select2-container').find('.select2-selection');
            const label = this.labels?.[0];
            if (label && this.id) {
                if (!label.id) label.id = `${this.id}-admin-label`;
                selection.attr('aria-labelledby', `${label.id} ${selection.attr('aria-labelledby') || ''}`.trim());
            }
            if (this.required) selection.attr('aria-required', 'true');
            $el.on('change.adminNative', function (event) {
                // Select2 emits jQuery events; dependent fields use native change listeners.
                // Inline onchange handlers already run through jQuery, so do not submit them twice.
                if (event.isTrigger && !event.originalEvent && !this.onchange) {
                    this.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminSelect2, { once: true });
    } else {
        initAdminSelect2();
    }
</script>
@stack('scripts')
</body>
</html>
