<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CÔNG TY CỔ PHẦN CƠ KHÍ THỦ ĐỨC TEXENCO') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Tom Select (searchable multi-select) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --surface: #ffffff;
            --bg: #f8fafc;
            --text: #1e293b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --radius: 16px;
            --radius-sm: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .04), 0 4px 16px rgba(0, 0, 0, .06);
            --shadow-lg: 0 4px 24px rgba(0, 0, 0, .08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        /* ─── NAVBAR ─── */
        .navbar-erp {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 8px rgba(0, 0, 0, .04);
            padding: .6rem 0;
        }

        .navbar-erp .navbar-brand {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: -.3px;
        }

        .navbar-erp .nav-link {
            color: var(--text);
            font-size: .875rem;
            font-weight: 500;
            padding: .5rem .85rem !important;
            border-radius: 10px;
            transition: all .2s;
        }

        .navbar-erp .nav-link:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .navbar-erp .nav-link.active,
        .navbar-erp .nav-link.fw-bold {
            background: #eef2ff;
            color: var(--primary) !important;
        }

        .navbar-erp .dropdown-menu {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg);
            padding: .5rem;
            margin-top: .5rem;
        }

        .navbar-erp .dropdown-item {
            border-radius: 8px;
            font-size: .875rem;
            padding: .5rem .75rem;
            font-weight: 500;
            color: var(--text);
            transition: all .15s;
        }

        .navbar-erp .dropdown-item:hover {
            background: #eef2ff;
            color: var(--primary);
        }

        .navbar-erp .dropdown-divider {
            margin: .35rem 0;
            border-color: var(--border);
        }

        /* ─── CARD ─── */
        .card-page {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.75rem;
            border: 1px solid var(--border);
        }

        /* ─── TABLE ─── */
        .table {
            margin-bottom: 0;
            font-size: .875rem;
        }

        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
            color: var(--text-muted);
            font-weight: 600;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: .75rem 1rem;
        }

        .table thead.table-dark th {
            background: #f8fafc !important;
            border-color: var(--border) !important;
            color: var(--text-muted) !important;
            font-weight: 600;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .table tbody td {
            padding: .75rem 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
            color: var(--text);
        }

        .table-hover tbody tr:hover {
            background: #fafbfe;
        }

        /* ─── BUTTONS ─── */
        .btn {
            border-radius: 10px;
            font-weight: 500;
            font-size: .875rem;
            transition: all .2s;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(99, 102, 241, .25);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(99, 102, 241, .35);
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(99, 102, 241, .25);
        }

        .btn-warning {
            background: #fbbf24;
            border-color: #fbbf24;
            color: #78350f;
            box-shadow: 0 2px 6px rgba(251, 191, 36, .25);
        }

        .btn-warning:hover {
            background: #f59e0b;
            border-color: #f59e0b;
            color: #78350f;
        }

        .btn-danger {
            background: #ef4444;
            border-color: #ef4444;
            box-shadow: 0 2px 6px rgba(239, 68, 68, .25);
        }

        .btn-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }

        .btn-secondary {
            background: #f1f5f9;
            border-color: var(--border);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: var(--text);
        }

        .btn-xs {
            padding: .3rem .6rem;
            font-size: .75rem;
            border-radius: 8px;
        }

        .btn-sm {
            border-radius: 10px;
        }

        /* ─── BADGE ─── */
        .badge {
            font-size: .75rem;
            font-weight: 600;
            border-radius: 20px;
            padding: .35em .75em;
            letter-spacing: .2px;
        }

        /* ─── FORMS ─── */
        .form-control,
        .form-select {
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            padding: .55rem .9rem;
            font-size: .875rem;
            transition: all .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .form-label {
            font-size: .8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: .35rem;
        }

        /* ─── ALERTS ─── */
        .alert {
            border-radius: var(--radius-sm);
            border: none;
            font-size: .875rem;
            font-weight: 500;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
        }

        /* ─── PAGINATION ─── */
        .pagination {
            gap: .25rem;
        }

        .page-link {
            border-radius: 10px !important;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: .85rem;
            font-weight: 500;
            padding: .4rem .75rem;
        }

        .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
        }

        /* ─── MISC ─── */
        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -.3px;
        }

        .page-title i {
            color: var(--primary);
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .navbar-toggler-icon {
            filter: invert(0);
        }

        ::selection {
            background: rgba(99, 102, 241, .15);
        }
    </style>
    @yield('css')
</head>

<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-erp mb-0">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-cube me-2"></i>TEXENCO
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav me-auto gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active fw-bold' : '' }}"
                            href="{{ route('admin.orders.index') }}">
                            <i class="fa-solid fa-file-invoice me-1"></i>Order
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.order-tracking.*') ? 'active fw-bold' : '' }}"
                            href="{{ route('admin.order-tracking.index') }}">
                            <i class="fa-solid fa-truck-fast me-1"></i>Order Tracking
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.lenh-san-xuat.*') ? 'active fw-bold' : '' }}"
                            href="{{ route('admin.lenh-san-xuat.index') }}">
                            <i class="fa-solid fa-clipboard-list me-1"></i>Lệnh SX
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.production-reports.*') ? 'active fw-bold' : '' }}"
                            href="{{ route('admin.production-reports.index') }}">
                            <i class="fa-solid fa-industry me-1"></i>Production
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.warehouse-transactions.*') ? 'active fw-bold' : '' }}"
                            href="{{ route('admin.warehouse-transactions.index') }}">
                            <i class="fa-solid fa-warehouse me-1"></i>Warehouse
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" role="button" href="#" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-1"
                                style="width:28px;height:28px;background:#eef2ff;color:var(--primary);font-size:.75rem;font-weight:700;">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </span>
                            {{ Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="px-3 py-2 mb-1">
                                    <div class="fw-semibold" style="font-size:.875rem">
                                        {{ Auth::user()->name ?? 'User' }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ Auth::user()->email ?? '' }}
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i
                                        class="fa-solid fa-gauge-high me-1 text-muted"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i
                                        class="fa-solid fa-users me-1 text-muted"></i>Users</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}"><i
                                        class="fa-solid fa-file-invoice me-1 text-muted"></i>Đơn hàng</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.order-tracking.index') }}"><i
                                        class="fa-solid fa-truck-fast me-1 text-muted"></i>Order Tracking</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.lenh-san-xuat.index') }}"><i
                                        class="fa-solid fa-clipboard-list me-1 text-muted"></i>Lệnh Sản Xuất</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.production-reports.index') }}"><i
                                        class="fa-solid fa-industry me-1 text-muted"></i>Báo cáo SX</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.warehouse-transactions.index') }}"><i
                                        class="fa-solid fa-warehouse me-1 text-muted"></i>Giao dịch Kho</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.hang-hoa.index') }}"><i
                                        class="fa-solid fa-box-open me-1 text-muted"></i>Hàng hóa</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.khach-hang.index') }}"><i
                                        class="fa-solid fa-building me-1 text-muted"></i>Khách hàng</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="py-4">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Tom Select -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    @yield('scripts')
</body>

</html>
