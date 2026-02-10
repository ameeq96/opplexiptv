<!DOCTYPE html>
<html lang="{{ admin_locale() }}" dir="{{ admin_dir() }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="{{ v('css/admin-2026.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="admin-body">
<div class="admin-shell">
    @include('admin.partials.sidebar')
    <div class="admin-main mt-4">
        @include('admin.partials.header')
        @include('admin.partials.flash')
        <main class="mt-3">
            @yield('content')
        </main>
        @includeIf('admin.partials.footer')
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('adminSidebarOverlay');
        if (!sidebar) return;
        sidebar.classList.toggle('show');
        if (overlay) {
            overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
        }
    }
    function closeAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('adminSidebarOverlay');
        if (sidebar) sidebar.classList.remove('show');
        if (overlay) overlay.style.display = 'none';
    }
    document.addEventListener('click', (e) => {
        if (e.target?.dataset?.adminSidebar === 'toggle') {
            toggleAdminSidebar();
        }
        if (e.target?.dataset?.adminSidebar === 'close') {
            closeAdminSidebar();
        }
    });

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
            $el.select2({
                width: '100%',
                placeholder,
                allowClear: hasEmpty,
                dropdownParent: jQuery('body')
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
