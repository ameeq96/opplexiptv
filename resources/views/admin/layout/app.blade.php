<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Opplex Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ v('css/admin-2026.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            margin: 0;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 8px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
            right: 6px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: inherit;
        }

        .logout-link {
            background: none;
            border: none;
            text-align: left;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                width: 200px;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
                padding: 15px;
            }
        }

        @media (min-width: 769px) {
            .sidebar {
                width: 220px;
                position: fixed;
            }

            .content {
                margin-left: 220px;
                padding: 20px;
            }
        }

        .toggle-btn {
            background: none;
            border: none;
            color: #000;
            font-size: 24px;
        }

        .notif-bell {
            position: relative;
        }

        .notif-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            font-size: 10px;
            padding: 2px 6px;
        }

        #sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>

<body>

    <div id="sidebar-overlay" class="d-md-none" onclick="closeSidebar()" style="display: none;"></div>

    <div class="sidebar admin-sidebar d-flex flex-column p-3" id="sidebar">
        <div class="brand text-center mb-4">Opplex IPTV</div>
        <a href="{{ route('admin.dashboard') }}">ðŸ  Dashboard</a>
        <a href="{{ route('admin.clients.index') }}">ðŸ‘¤ Clients</a>
        <a href="{{ route('admin.orders.index') }}">ðŸ“¦ Package Orders</a>
        <a href="{{ route('admin.panel-orders.index') }}">ðŸ–¥ï¸ Panel Orders</a>
        <a href="{{ route('admin.purchasing.index') }}">ðŸ’³ Purchasing</a>
        <a href="{{ route('admin.trial_clicks.index') }}">ðŸ“± WhatsApp Trials</a>
        <a href="{{ route('admin.blogs.index') }}">ðŸ“ Blogs</a>
        <a href="{{ route('admin.shop-products.index') }}">ðŸ›’ Shop Products</a>
        <a href="{{ route('admin.home-services.index') }}">ðŸ§© Home Services</a>
        <a href="{{ route('admin.testimonials.index') }}">ðŸ’¬ Testimonials</a>
        <a href="{{ route('admin.channel-logos.index') }}">ðŸ–¼ Channel Logos</a>
        <a href="{{ route('admin.menu-items.index') }}">ðŸ—‚ Menu Items</a>
        <a href="{{ route('admin.packages.index') }}">ðŸ· Packages</a>
        <a href="{{ route('admin.pricing-section.edit') }}">ðŸ§¾ Pricing Section</a>


        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-link">ðŸšª Logout</button>
        </form>
    </div>



    <!-- Content -->
    <div class="content admin-content">
        <div class="admin-topbar d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-btn d-md-none" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="m-0">@yield('page_title', 'Dashboard')</h2>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-light notif-bell" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-danger notif-badge d-none" id="notifCount">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 320px; max-height: 360px; overflow-y: auto;">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <strong>Notifications</strong>
                            <button class="btn btn-link btn-sm p-0" id="notifMarkAll" type="button">Mark all read</button>
                        </div>
                        <div id="notifList" class="list-group list-group-flush"></div>
                    </div>
                </div>
                <span class="d-none d-md-inline">Welcome, Admin</span>
            </div>
        </div>

        <main>
            @yield('content')
        </main>
    </div>

    <script>
        document.getElementById('checkAll')?.addEventListener('click', function() {
            document.querySelectorAll(
                'input[name="client_ids[]"], input[name="order_ids[]"], input[name="purchase_ids[]"]'
            ).forEach(cb => cb.checked = this.checked);
        });


        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.toggle('show');
            overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebar-overlay').style.display = 'none';
        }

        function toggleOtherField() {
            const select = document.getElementById('payment_method');
            const other = document.getElementById('other-payment-method');

            if (!select || !other) return;

            other.style.display = select.value === 'other' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            toggleOtherField();

            const select = document.getElementById('payment_method');
            if (select) {
                select.addEventListener('change', toggleOtherField);
            }
        });

        function toggleOtherField2() {
            const select = document.getElementById('payment_method');
            const other = document.getElementById('other-payment-method');

            if (!select || !other) return;

            other.style.display = select.value === 'other' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            let packageSelect = document.getElementById('package');
            let customField = document.getElementById('custom_package_field');
            let customInput = document.getElementById('custom_package');

            if (packageSelect) {
                packageSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        customField.style.display = 'block';
                    } else {
                        customField.style.display = 'none';
                        customInput.value = '';
                    }
                });

                if (packageSelect.value === 'other') {
                    customField.style.display = 'block';
                }
            }
        });

        function sendAll() {
            document.querySelectorAll('table a.btn-outline-success').forEach(a => {
                window.open(a.href, '_blank');
            });
        }

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select a client",
                allowClear: true
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            let exclude = document.getElementById('excludeIPTV');
            if (exclude) {
                exclude.addEventListener('change', function() {
                    this.form.submit();
                });
            }
        });

        function showScreenshot(src) {
            document.getElementById('modalScreenshot').src = src;
        }

        // Notifications (bell)
        (() => {
            const notifListEl = document.getElementById('notifList');
            const countEl = document.getElementById('notifCount');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const routes = {
                index: "{{ route('admin.notifications.index') }}",
                readAll: "{{ route('admin.notifications.readAll') }}",
                readOne: "{{ route('admin.notifications.read', ['id' => '__ID__']) }}",
                viewPackage: "{{ route('admin.orders.show', ['order' => '__ID__']) }}",
                viewReseller: "{{ route('admin.panel-orders.show', ['panel_order' => '__ID__']) }}",
            };

            function buildRow(item) {
                const link = item.order_id
                    ? (item.type === 'reseller'
                        ? routes.viewReseller.replace('__ID__', item.order_id)
                        : routes.viewPackage.replace('__ID__', item.order_id))
                    : null;

                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'list-group-item list-group-item-action text-start d-flex flex-column';
                row.dataset.id = item.id;
                row.dataset.link = link || '';
                if (!item.read_at) row.classList.add('fw-bold');

                row.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <div>${item.title || 'New order'}</div>
                        <small class="text-muted ms-2">${item.created_at || ''}</small>
                    </div>
                    <div class="text-muted small">${item.body || ''}</div>
                    <div class="small mt-1">
                        <span class="badge bg-secondary me-1">${item.type || 'order'}</span>
                        ${item.package ? `<span class="badge bg-light text-dark">${item.package}</span>` : ''}
                    </div>
                `;
                row.addEventListener('click', () => {
                    markOne(item.id).then(() => {
                        if (link) window.location.href = link;
                    });
                });
                return row;
            }

            function render(list, unread) {
                notifListEl.innerHTML = '';
                if (!list.length) {
                    notifListEl.innerHTML = '<div class="px-3 py-3 text-muted small">No notifications.</div>';
                } else {
                    list.forEach(item => notifListEl.appendChild(buildRow(item)));
                }
                if (unread > 0) {
                    countEl.classList.remove('d-none');
                    countEl.textContent = unread > 99 ? '99+' : unread;
                } else {
                    countEl.classList.add('d-none');
                }
            }

            async function load() {
                try {
                    const res = await fetch(routes.index);
                    if (!res.ok) return;
                    const data = await res.json();
                    render(data.items || [], data.unread_count || 0);
                } catch (e) {
                    console.error('Notification fetch failed', e);
                }
            }

            async function markOne(id) {
                try {
                    await fetch(routes.readOne.replace('__ID__', id), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) {
                    console.error('Mark read failed', e);
                } finally {
                    load();
                }
            }

            async function markAll() {
                try {
                    await fetch(routes.readAll, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) {
                    console.error('Mark all failed', e);
                } finally {
                    load();
                }
            }

            document.getElementById('notifMarkAll')?.addEventListener('click', markAll);

            load();
        })();
    </script>

</body>

</html>
