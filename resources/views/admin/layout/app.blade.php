<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Opplex Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .sidebar {
            background: #343a40;
            color: white;
            min-height: 100vh;
        }

        .sidebar a,
        .logout-link {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: background 0.3s ease, border-radius 0.3s ease;
        }

        .sidebar a:hover,
        .logout-link:hover {
            background: #495057;
            border-radius: 10px;
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

    <div class="sidebar d-flex flex-column p-3" id="sidebar">
        <h4 class="text-center mb-4">Opplex IPTV</h4>
        <a href="{{ route('admin.dashboard') }}">🏠 Dashboard</a>
        <a href="{{ route('admin.clients.index') }}">👤 Clients</a>
        <a href="{{ route('admin.orders.index') }}">📦 Package Orders</a>
        <a href="{{ route('admin.panel-orders.index') }}">🖥️ Panel Orders</a>
        <a href="{{ route('admin.purchasing.index') }}">💳 Purchasing</a>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-link">🚪 Logout</button>
        </form>
    </div>



    <!-- Content -->
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-btn d-md-none" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="m-0">@yield('page_title', 'Dashboard')</h2>
            </div>
            <span class="d-none d-md-inline">Welcome, Admin</span>
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
    </script>

</body>

</html>
