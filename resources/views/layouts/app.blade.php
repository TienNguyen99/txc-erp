<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TEXENCO ERP') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #f7941d;
            --primary-light: #fbb04c;
            --primary-dark: #e07b08;
            --primary-rgb: 247, 148, 29;
            --surface: #ffffff;
            --bg: #fdf8f3;
            --text: #1e293b;
            --text-muted: #94a3b8;
            --border: #f0e8dc;
            --sidebar-w: 240px;
            --header-h: 56px;
            --radius: 14px;
            --radius-sm: 10px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .04), 0 4px 16px rgba(0, 0, 0, .06);
            --shadow-lg: 0 4px 24px rgba(0, 0, 0, .08);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            margin: 0;
        }

        /* ── SIDEBAR ── */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform .25s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 20px 16px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -.3px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f7941d, #e07b08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .85rem;
        }

        .sidebar-section {
            padding: 18px 12px 4px;
        }

        .sidebar-section-label {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 8px;
            margin-bottom: 6px;
        }

        .nav-item-sb {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            color: #475569;
            font-size: .83rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .15s;
            margin-bottom: 1px;
        }

        .nav-item-sb i {
            width: 18px;
            text-align: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .nav-item-sb:hover {
            background: #fff4e8;
            color: var(--primary);
        }

        .nav-item-sb.active {
            background: #fff0db;
            color: var(--primary-dark);
            font-weight: 600;
        }

        .nav-item-sb.active i {
            color: var(--primary);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 12px;
            border-top: 1px solid var(--border);
        }

        /* ── HEADER ── */
        #topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--header-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            z-index: 1030;
            transition: left .25s ease;
        }

        .topbar-toggle {
            background: none;
            border: none;
            padding: 6px 8px;
            color: var(--text-muted);
            font-size: 1rem;
            cursor: pointer;
            border-radius: 8px;
            transition: all .15s;
        }

        .topbar-toggle:hover {
            background: #fff4e8;
            color: var(--primary);
        }

        .topbar-title {
            font-size: .9rem;
            font-weight: 600;
            color: var(--text);
            flex-grow: 1;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .topbar-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            position: relative;
            font-size: .9rem;
        }

        .topbar-btn:hover {
            background: #fff4e8;
            color: var(--primary);
        }

        .topbar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f7941d, #fbb04c);
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── MAIN CONTENT ── */
        #main-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--header-h);
            min-height: 100vh;
            transition: margin-left .25s ease;
        }

        .page-content {
            padding: 24px;
        }

        /* ── COLLAPSED SIDEBAR ── */
        body.sidebar-collapsed #sidebar {
            transform: translateX(calc(-1 * var(--sidebar-w)));
        }

        body.sidebar-collapsed #topbar {
            left: 0;
        }

        body.sidebar-collapsed #main-content {
            margin-left: 0;
        }

        /* ── CARD ── */
        .card-page {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            border: 1px solid var(--border);
        }

        /* ── TABLE ── */
        .table {
            margin-bottom: 0;
            font-size: .875rem;
        }

        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
            color: var(--text-muted);
            font-weight: 600;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: .7rem 1rem;
        }

        .table thead.table-dark th {
            background: #f8fafc !important;
            border-color: var(--border) !important;
            color: var(--text-muted) !important;
            font-weight: 600;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .table thead th:not(.no-sort) {
            cursor: pointer;
            position: relative;
            padding-right: 20px;
            user-select: none;
        }

        .table thead th:not(.no-sort):hover {
            background: #f1f5f9;
        }

        .table thead th:not(.no-sort)::after {
            content: '↕';
            position: absolute;
            right: 5px;
            opacity: .3;
            font-weight: normal;
        }

        .table thead th.asc::after {
            content: '↑';
            opacity: 1;
            color: var(--primary);
        }

        .table thead th.desc::after {
            content: '↓';
            opacity: 1;
            color: var(--primary);
        }

        .table tbody td {
            padding: .7rem 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: #fffaf5;
        }

        /* ── BUTTONS – Soft / Tinted Texenco ── */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: .82rem;
            border: none;
            line-height: 1.5;
            transition: all .18s ease;
            box-shadow: none;
        }

        .btn:active {
            transform: scale(.97);
        }

        .btn:focus-visible {
            box-shadow: 0 0 0 3px rgba(247, 148, 29, .3) !important;
            outline: none;
        }

        .btn-primary {
            background: rgba(247, 148, 29, .14);
            color: #c55f00;
        }

        .btn-primary:hover {
            background: rgba(247, 148, 29, .24);
            color: #a04c00;
            box-shadow: none;
        }

        .btn-danger {
            background: rgba(239, 68, 68, .1);
            color: #dc2626;
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, .18);
            color: #b91c1c;
            box-shadow: none;
        }

        .btn-warning {
            background: rgba(245, 158, 11, .1);
            color: #b45309;
        }

        .btn-warning:hover {
            background: rgba(245, 158, 11, .18);
            color: #92400e;
            box-shadow: none;
        }

        .btn-success {
            background: rgba(16, 185, 129, .1);
            color: #059669;
        }

        .btn-success:hover {
            background: rgba(16, 185, 129, .18);
            color: #047857;
            box-shadow: none;
        }

        .btn-secondary {
            background: rgba(100, 116, 139, .1);
            color: #475569;
        }

        .btn-secondary:hover {
            background: rgba(100, 116, 139, .18);
            color: #334155;
            box-shadow: none;
        }

        .btn-info {
            background: rgba(6, 182, 212, .1);
            color: #0891b2;
        }

        .btn-info:hover {
            background: rgba(6, 182, 212, .18);
            color: #0e7490;
            box-shadow: none;
        }

        .btn-outline-primary {
            background: transparent;
            border: 1.5px solid rgba(247, 148, 29, .4);
            color: #c55f00;
        }

        .btn-outline-primary:hover {
            background: rgba(247, 148, 29, .1);
            border-color: var(--primary);
            color: #a04c00;
            box-shadow: none;
        }

        .btn-outline-secondary {
            background: transparent;
            border: 1.5px solid var(--border);
            color: #475569;
        }

        .btn-outline-secondary:hover {
            background: rgba(100, 116, 139, .08);
            border-color: #94a3b8;
            color: #334155;
            box-shadow: none;
        }

        .btn-outline-danger {
            background: transparent;
            border: 1.5px solid rgba(239, 68, 68, .3);
            color: #dc2626;
        }

        .btn-outline-danger:hover {
            background: rgba(239, 68, 68, .08);
            border-color: rgba(239, 68, 68, .55);
            color: #b91c1c;
            box-shadow: none;
        }

        .btn-xs {
            padding: .28rem .65rem;
            font-size: .75rem;
            border-radius: 8px;
        }

        .btn-sm {
            padding: .38rem .85rem;
            font-size: .8rem;
            border-radius: 10px;
        }

        /* ── MISC ── */
        .badge {
            font-size: .72rem;
            font-weight: 600;
            border-radius: 20px;
            padding: .35em .75em;
        }

        .form-control,
        .form-select {
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            padding: .5rem .85rem;
            font-size: .875rem;
            transition: all .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(247, 148, 29, .15);
        }

        .form-label {
            font-size: .8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: .3rem;
        }

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

        .page-title {
            font-size: 1.25rem;
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
            color: #fff;
        }

        ::selection {
            background: rgba(247, 148, 29, .2);
        }

        /* Mobile overlay */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .3);
            z-index: 1039;
        }

        @media (max-width: 991px) {
            #sidebar {
                transform: translateX(calc(-1 * var(--sidebar-w)));
            }

            #topbar {
                left: 0;
            }

            #main-content {
                margin-left: 0;
            }

            body.sidebar-open #sidebar {
                transform: translateX(0);
            }

            body.sidebar-open #sidebar-overlay {
                display: block;
            }

            body.sidebar-collapsed #sidebar {
                transform: translateX(calc(-1 * var(--sidebar-w)));
            }
        }
    </style>
    @yield('css')
</head>

<body>

    {{-- Sidebar overlay (mobile) --}}
    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    {{-- ══════════════════ SIDEBAR ══════════════════ --}}
    <aside id="sidebar">
        {{-- Brand --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <div class="brand-icon"><i class="fa-solid fa-cube"></i></div>
            <span>TEXENCO</span>
        </a>

        {{-- MAIN --}}
        <div class="sidebar-section">
            <div class="sidebar-section-label">Main</div>
            <a href="{{ route('admin.dashboard') }}"
                class="nav-item-sb {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="{{ route('admin.ai-assistant.index') }}"
                class="nav-item-sb {{ request()->routeIs('admin.ai-assistant.*') ? 'active' : '' }}">
                <i class="fa-solid fa-wand-magic-sparkles"></i> AI Assistant
            </a>
            @can('orders.view')
                <a href="{{ route('admin.orders.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice"></i> Đơn hàng
                </a>
            @endcan
            @can('tracking.view')
                <a href="{{ route('admin.order-tracking.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.order-tracking.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck-fast"></i> Order Tracking
                </a>
            @endcan
        </div>

        {{-- SẢN XUẤT --}}
        @can('lenh_sx.view')
            <div class="sidebar-section">
                <div class="sidebar-section-label">Sản Xuất</div>
                <a href="{{ route('admin.lenh-san-xuat.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.lenh-san-xuat.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list"></i> Lệnh Sản Xuất
                </a>
                <a href="{{ route('admin.quy-trinh-san-xuat.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.quy-trinh-san-xuat.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-diagram-project"></i> Quy trình SX
                </a>
                <a href="{{ route('admin.dinh-muc-nvl.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.dinh-muc-nvl.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ol"></i> BOM / Định mức
                </a>
            </div>
        @endcan
        @can('production.view')
            <div class="sidebar-section" style="padding-top:0">
                <a href="{{ route('admin.production-reports.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.production-reports.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-industry"></i> Báo cáo SX
                </a>
            </div>
        @endcan

        {{-- KHO --}}
        @can('warehouse.view')
            <div class="sidebar-section">
                <div class="sidebar-section-label">Kho</div>
                <a href="{{ route('admin.warehouse-transactions.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.warehouse-transactions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-warehouse"></i> Giao dịch Kho
                </a>
                <a href="{{ route('admin.warehouse-documents.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.warehouse-documents.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice"></i> Phiếu kho
                </a>
                <a href="{{ route('admin.warehouse-transactions.ton-kho') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.warehouse-transactions.ton-kho') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked"></i> Tồn kho
                </a>
                <a href="{{ route('admin.costing.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.costing.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calculator"></i> Giá vốn
                </a>
            </div>
        @endcan

        {{-- DANH MỤC --}}
        @can('catalog.view')
            <div class="sidebar-section">
                <div class="sidebar-section-label">Danh mục</div>
                <a href="{{ route('admin.hang-hoa.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.hang-hoa.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-box-open"></i> Hàng hóa
                </a>
                <a href="{{ route('admin.khach-hang.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.khach-hang.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i> Khách hàng
                </a>
                <a href="{{ route('admin.nha-cung-cap.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.nha-cung-cap.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck"></i> Nhà cung cấp
                </a>
                <a href="{{ route('admin.purchase-orders.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-shopping"></i> Đặt hàng NVL
                </a>
            </div>
        @endcan

        {{-- HỆ THỐNG --}}
        @role('admin')
            <div class="sidebar-section">
                <div class="sidebar-section-label">Hệ thống</div>
                <a href="{{ route('admin.users.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Người dùng
                </a>
                <a href="{{ route('admin.notifications.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bell"></i> Thông báo
                    @php $unread = \App\Models\ErpNotification::unread()->count(); @endphp
                    @if ($unread > 0)
                        <span class="badge bg-danger ms-auto" style="font-size:.6rem">{{ $unread }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.settings.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears"></i> Cài đặt
                </a>
                <a href="{{ route('admin.activity-logs.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i> Nhật ký
                </a>
            </div>
        @endrole

        {{-- Footer: Logout --}}
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item-sb w-100 text-start border-0"
                    style="background:none;color:#ef4444;cursor:pointer">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════════ TOPBAR ══════════════════ --}}
    <header id="topbar">
        <button class="topbar-toggle" onclick="toggleSidebar()" title="Toggle menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        {{-- Breadcrumb / page context --}}
        <span class="topbar-title d-none d-md-block">
            @yield('page-title', config('app.name', 'TEXENCO ERP'))
        </span>

        <div class="topbar-actions ms-auto">
            {{-- Notifications bell --}}
            <a href="{{ route('admin.notifications.index') }}" class="topbar-btn" title="Thông báo">
                <i class="fa-solid fa-bell"></i>
                <span id="unreadBadge"
                    style="
                position:absolute;top:4px;right:4px;
                width:8px;height:8px;border-radius:50%;
                background:#ef4444;display:none;
            "></span>
            </a>

            {{-- User dropdown --}}
            <div class="dropdown">
                <button class="d-flex align-items-center gap-2 border-0 bg-transparent"
                    style="cursor:pointer;border-radius:10px;padding:4px 8px;transition:all .15s"
                    data-bs-toggle="dropdown">
                    <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                    <span style="font-size:.83rem;font-weight:600;color:var(--text)" class="d-none d-md-inline">
                        {{ Auth::user()->name ?? 'User' }}
                    </span>
                    <i class="fa-solid fa-chevron-down" style="font-size:.65rem;color:var(--text-muted)"
                        class="d-none d-md-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end"
                    style="border:1px solid var(--border);border-radius:var(--radius-sm);box-shadow:var(--shadow-lg);padding:.5rem;min-width:200px;margin-top:.5rem">
                    <li>
                        <div class="px-3 py-2 mb-1">
                            <div class="fw-semibold" style="font-size:.875rem">{{ Auth::user()->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ Auth::user()->email }}</div>
                            <div class="mt-1">
                                @foreach (Auth::user()->getRoleNames() as $r)
                                    <span class="badge"
                                        style="background:var(--primary);color:#fff;font-size:.6rem">{{ ucfirst($r) }}</span>
                                @endforeach
                            </div>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider" style="margin:.35rem 0;border-color:var(--border)">
                    </li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"
                            style="border-radius:8px;font-size:.85rem">
                            <i class="fa-solid fa-user me-2 text-muted"></i>Hồ sơ cá nhân</a></li>
                    <li>
                        <hr class="dropdown-divider" style="margin:.35rem 0;border-color:var(--border)">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger" style="border-radius:8px;font-size:.85rem">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- ══════════════════ CONTENT ══════════════════ --}}
    <main id="main-content" class="d-flex flex-column">
        <div class="page-content" style="flex: 1;">
            {{ $slot ?? '' }}
            @yield('content')
        </div>

        <footer style="padding: 15px 24px; border-top: 1px solid var(--border); background: var(--surface); font-size: 0.82rem; color: var(--text-muted); display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
            <div>
                &copy; {{ date('Y') }} TXC ERP. Engineered with <i class="fa-solid fa-heart" style="color: #ef4444; font-size: 0.75rem;"></i> by Tiến.
            </div>
            <div>
                @php
                    $onlineUsers = \App\Models\User::whereNotNull('last_seen_at')->where('last_seen_at', '>=', now()->subMinutes(5))->get();
                @endphp
                <div class="dropdown dropup">
                    <span data-bs-toggle="dropdown" style="cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                        <span style="color: #10b981; font-size: 0.6rem;"><i class="fa-solid fa-circle"></i></span>
                        {{ $onlineUsers->count() }} Users Online
                    </span>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.8rem; min-width: 240px; border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 10px;">
                        <li class="dropdown-header fw-bold text-dark border-bottom pb-2 mb-1" style="font-size: 0.75rem;">ĐANG HOẠT ĐỘNG (5 PHÚT QUA)</li>
                        <div style="max-height: 250px; overflow-y: auto;">
                        @forelse($onlineUsers as $ou)
                            <li class="px-3 py-1 d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-dark">{{ $ou->name }}</span>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem; font-family: monospace;">{{ $ou->last_seen_ip ?? 'N/A' }}</span>
                            </li>
                        @empty
                            <li class="px-3 py-2 text-muted fst-italic">Không có dữ liệu</li>
                        @endforelse
                        </div>
                    </ul>
                </div>
            </div>
        </footer>
    </main>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const isMobile = window.innerWidth < 992;
            if (isMobile) {
                document.body.classList.toggle('sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
            }
        }

        function closeSidebar() {
            document.body.classList.remove('sidebar-open');
        }

        // Restore state on desktop
        if (window.innerWidth >= 992 && localStorage.getItem('sidebar-collapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }

        // Bell badge poll
        function updateBell() {
            @auth
            fetch('{{ route('admin.notifications.unread-count') }}')
                .then(r => r.json())
                .then(d => {
                    const b = document.getElementById('unreadBadge');
                    if (b) b.style.display = d.count > 0 ? 'block' : 'none';
                }).catch(() => {});
        @endauth
        }
        @auth updateBell();
        setInterval(updateBell, 60000);
        @endauth

        // Table sorting
        document.addEventListener('DOMContentLoaded', function() {
            const getCellValue = (tr, idx) => {
                let cell = tr.children[idx];
                if (!cell) return '';
                let input = cell.querySelector('input');
                if (input && input.type !== 'checkbox' && input.type !== 'hidden') return input.value;
                return cell.innerText || cell.textContent;
            };
            const comparer = (idx, asc) => (a, b) => {
                let v1 = getCellValue(asc ? a : b, idx).trim().replace(/,/g, '');
                let v2 = getCellValue(asc ? b : a, idx).trim().replace(/,/g, '');
                if (v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2)) return parseFloat(v1) - parseFloat(v2);
                return v1.toString().localeCompare(v2);
            };
            document.querySelectorAll('th:not(.no-sort)').forEach(th => th.addEventListener('click', () => {
                const table = th.closest('table');
                if (!table || table.classList.contains('no-sort-table')) return;
                const tbody = table.querySelector('tbody');
                if (!tbody) return;
                Array.from(th.parentNode.children).forEach(s => {
                    if (s !== th) s.classList.remove('asc', 'desc');
                });
                let asc = true;
                if (th.classList.contains('asc')) {
                    th.classList.replace('asc', 'desc');
                    asc = false;
                } else if (th.classList.contains('desc')) {
                    th.classList.replace('desc', 'asc');
                } else {
                    th.classList.add('asc');
                }
                Array.from(tbody.querySelectorAll('tr')).sort(comparer(Array.from(th.parentNode
                        .children).indexOf(th), asc))
                    .forEach(tr => tbody.appendChild(tr));
            }));
        });
    </script>

    @yield('scripts')
</body>

</html>
