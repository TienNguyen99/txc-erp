<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TEXENCO ERP') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    </noscript>
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
            justify-content: center;
            padding: 15px 18px 14px;
            min-height: 74px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -.3px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .sidebar-brand-logo {
            width: 168px;
            max-height: 54px;
            object-fit: contain;
            object-position: center;
            display: block;
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

        /* Garment ERP layout refresh */
        :root {
            --primary: #f27a1a;
            --primary-light: #ffb35c;
            --primary-dark: #c65d0c;
            --primary-rgb: 242, 122, 26;
            --accent: #0f766e;
            --accent-soft: #e6fffb;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --bg: #f6f7fb;
            --text: #172033;
            --text-muted: #64748b;
            --border: #e5e7eb;
            --sidebar-w: 276px;
            --header-h: 64px;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 10px 30px rgba(15, 23, 42, .06);
            --shadow-lg: 0 18px 46px rgba(15, 23, 42, .12);
        }

        body {
            background:
                linear-gradient(180deg, rgba(15, 118, 110, .045), rgba(246, 247, 251, 0) 260px),
                var(--bg);
        }

        #sidebar {
            background: var(--surface);
            border-right: 1px solid rgba(242, 122, 26, .16);
            box-shadow: 8px 0 28px rgba(194, 86, 12, .08);
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(242, 122, 26, .2);
        }

        .sidebar-brand {
            justify-content: center;
            min-height: 78px;
            padding: 14px 18px;
            color: var(--primary-dark);
            border-bottom: 1px solid rgba(242, 122, 26, .14);
            background: linear-gradient(180deg, #fff7ed, #ffffff);
        }

        .sidebar-brand-logo {
            width: 148px;
            max-height: 48px;
            padding: 0;
            background: transparent;
            border-radius: 0;
        }

        .sidebar-section {
            padding: 10px 12px 0;
        }

        .sidebar-section-label {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--text-muted);
            font-size: .68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 8px 10px;
            margin-bottom: 4px;
            border-radius: 8px;
            cursor: pointer;
        }

        .sidebar-section-label::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(242, 122, 26, .12);
        }

        .sidebar-section-label:hover {
            background: #fff7ed;
            color: var(--primary-dark);
        }

        .sidebar-section-label .fa-chevron-down {
            margin-left: auto;
            font-size: .62rem;
            transition: transform .18s ease;
        }

        .sidebar-section-label[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }

        .nav-item-sb {
            min-height: 38px;
            gap: 11px;
            padding: 7px 10px;
            border-radius: 10px;
            color: #475569;
            font-size: .84rem;
            margin-bottom: 3px;
            border: 1px solid transparent;
        }

        .nav-item-sb i {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: var(--primary);
            background: rgba(242, 122, 26, .09);
            font-size: .82rem;
        }

        .nav-item-sb:hover {
            background: #fff7ed;
            color: var(--primary-dark);
            border-color: rgba(242, 122, 26, .16);
        }

        .nav-item-sb:hover i {
            color: #fff;
            background: var(--primary);
        }

        .nav-item-sb.active {
            background: linear-gradient(135deg, #fff0db, #fffaf4);
            color: var(--primary-dark);
            border-color: rgba(242, 122, 26, .25);
            box-shadow: inset 4px 0 0 var(--primary), 0 8px 20px rgba(242, 122, 26, .1);
        }

        .nav-item-sb.active i {
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .nav-text {
            display: block;
            min-width: 0;
        }

        .nav-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(242, 122, 26, .14);
            background: #fffaf4;
        }

        #topbar {
            height: var(--header-h);
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226, 232, 240, .85);
            padding: 0 28px;
        }

        .topbar-toggle,
        .topbar-btn {
            border: 1px solid var(--border);
            background: #fff;
        }

        .topbar-title {
            font-size: .95rem;
        }

        .topbar-context {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
        }

        .topbar-context small {
            color: var(--text-muted);
            font-size: .72rem;
            font-weight: 500;
        }

        .topbar-avatar {
            background: linear-gradient(135deg, var(--primary), var(--accent));
        }

        .page-content {
            padding: 28px;
        }

        .card-page {
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(226, 232, 240, .9);
        }

        .table thead th,
        .table thead.table-dark th {
            background: #f1f5f9 !important;
            color: #475569 !important;
        }

        .btn {
            letter-spacing: 0;
        }

        .sidebar-logout {
            color: #dc2626 !important;
        }

        .sidebar-logout:hover {
            background: rgba(239, 68, 68, .08) !important;
            color: #b91c1c !important;
        }

        #app-loader {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top, rgba(var(--primary-rgb), .14), transparent 32%),
                rgba(253, 248, 243, .82);
            backdrop-filter: blur(10px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .18s ease, visibility .18s ease;
        }

        body.app-is-loading #app-loader {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .app-loader-card {
            width: min(320px, calc(100vw - 48px));
            padding: 22px 24px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, .76);
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 24px 80px rgba(15, 23, 42, .16);
        }

        .app-loader-mark {
            position: relative;
            width: 44px;
            height: 44px;
            margin: 0 auto 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 12px 28px rgba(var(--primary-rgb), .32);
        }

        .app-loader-mark::before {
            content: '';
            position: absolute;
            inset: 9px;
            border: 3px solid rgba(255, 255, 255, .92);
            border-top-color: transparent;
            border-radius: 50%;
            animation: appLoaderSpin .78s linear infinite;
        }

        .app-loader-title {
            margin: 0;
            color: #0f172a;
            font-size: .92rem;
            font-weight: 700;
            text-align: center;
        }

        .app-loader-subtitle {
            margin: 4px 0 0;
            color: #64748b;
            font-size: .78rem;
            text-align: center;
        }

        .app-loader-bar {
            position: relative;
            height: 4px;
            margin-top: 16px;
            overflow: hidden;
            border-radius: 999px;
            background: #ffead0;
        }

        .app-loader-bar::after {
            content: '';
            position: absolute;
            inset: 0;
            width: 42%;
            border-radius: inherit;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            animation: appLoaderSweep 1.05s ease-in-out infinite;
        }

        @keyframes appLoaderSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes appLoaderSweep {
            0% {
                transform: translateX(-110%);
            }

            100% {
                transform: translateX(260%);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .app-loader-mark::before,
            .app-loader-bar::after {
                animation: none;
            }
        }

        @media (max-width: 991px) {
            :root {
                --sidebar-w: 286px;
                --header-h: 58px;
            }

            #topbar {
                padding: 0 14px;
            }

            .page-content {
                padding: 16px;
            }
        }
    </style>
    @yield('css')
</head>

<body class="app-is-loading">

    <div id="app-loader" aria-live="polite" aria-label="Đang tải">
        <div class="app-loader-card">
            <div class="app-loader-mark" aria-hidden="true"></div>
            <p class="app-loader-title">Đang xử lý</p>
            <p class="app-loader-subtitle">TEXENCO ERP</p>
            <div class="app-loader-bar" aria-hidden="true"></div>
        </div>
    </div>

    {{-- Sidebar overlay (mobile) --}}
    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    {{-- ══════════════════ SIDEBAR ══════════════════ --}}
    <aside id="sidebar">
        {{-- Brand --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <img src="{{ asset('storage/logo-texenco-sidebar.png') }}" alt="Texenco" class="sidebar-brand-logo" width="168" height="72" fetchpriority="high" decoding="async">
        </a>

        {{-- MAIN --}}
        <div class="sidebar-section">
            <button class="sidebar-section-label" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMain"
                aria-expanded="{{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.ai-assistant.*') || request()->routeIs('admin.orders.*') || request()->routeIs('admin.order-tracking.*') ? 'true' : 'false' }}">
                Điều hành <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div id="sidebarMain"
                class="collapse {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.ai-assistant.*') || request()->routeIs('admin.orders.*') || request()->routeIs('admin.order-tracking.*') ? 'show' : '' }}">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i><span class="nav-label">Tổng quan</span>
                </a>
                <a href="{{ route('admin.ai-assistant.index') }}"
                    class="nav-item-sb {{ request()->routeIs('admin.ai-assistant.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wand-magic-sparkles"></i><span class="nav-label">AI Assistant</span>
                </a>
                @can('orders.view')
                    <a href="{{ route('admin.orders.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice"></i><span class="nav-label">Đơn hàng</span>
                    </a>
                @endcan
                @can('tracking.view')
                    <a href="{{ route('admin.order-tracking.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.order-tracking.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck-fast"></i><span class="nav-label">Theo dõi đơn</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- SẢN XUẤT --}}
        @if (auth()->user()?->can('lenh_sx.view') || auth()->user()?->can('production.view'))
            <div class="sidebar-section">
                <button class="sidebar-section-label" type="button" data-bs-toggle="collapse"
                    data-bs-target="#sidebarProduction"
                    aria-expanded="{{ request()->routeIs('admin.lenh-san-xuat.*') || request()->routeIs('admin.quy-trinh-san-xuat.*') || request()->routeIs('admin.dinh-muc-nvl.*') || request()->routeIs('admin.production-dashboard.*') || request()->routeIs('admin.production-reports.*') ? 'true' : 'false' }}">
                    Sản xuất <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="sidebarProduction"
                    class="collapse {{ request()->routeIs('admin.lenh-san-xuat.*') || request()->routeIs('admin.quy-trinh-san-xuat.*') || request()->routeIs('admin.dinh-muc-nvl.*') || request()->routeIs('admin.production-dashboard.*') || request()->routeIs('admin.production-reports.*') ? 'show' : '' }}">
                    @can('production.view')
                        <a href="{{ route('admin.production-dashboard.index') }}"
                            class="nav-item-sb {{ request()->routeIs('admin.production-dashboard.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i><span class="nav-label">Dashboard SX</span>
                        </a>
                    @endcan
                    @can('lenh_sx.view')
                        <a href="{{ route('admin.lenh-san-xuat.index') }}"
                            class="nav-item-sb {{ request()->routeIs('admin.lenh-san-xuat.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-list"></i><span class="nav-label">Lệnh sản xuất</span>
                        </a>
                        <a href="{{ route('admin.quy-trinh-san-xuat.index') }}"
                            class="nav-item-sb {{ request()->routeIs('admin.quy-trinh-san-xuat.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-diagram-project"></i><span class="nav-label">Quy trình SX</span>
                        </a>
                        <a href="{{ route('admin.dinh-muc-nvl.index') }}"
                            class="nav-item-sb {{ request()->routeIs('admin.dinh-muc-nvl.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-list-ol"></i><span class="nav-label">BOM / Định mức</span>
                        </a>
                    @endcan
                    @can('production.view')
                        <a href="{{ route('admin.production-reports.index') }}"
                            class="nav-item-sb {{ request()->routeIs('admin.production-reports.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-industry"></i><span class="nav-label">Báo cáo SX</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endif

        {{-- KHO --}}
        @can('warehouse.view')
            <div class="sidebar-section">
                <button class="sidebar-section-label" type="button" data-bs-toggle="collapse"
                    data-bs-target="#sidebarWarehouse"
                    aria-expanded="{{ request()->routeIs('admin.warehouse-transactions.*') || request()->routeIs('admin.warehouse-documents.*') || request()->routeIs('admin.costing.*') || request()->routeIs('admin.standard-cost-sheets.*') ? 'true' : 'false' }}">
                    Kho <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="sidebarWarehouse"
                    class="collapse {{ request()->routeIs('admin.warehouse-transactions.*') || request()->routeIs('admin.warehouse-documents.*') || request()->routeIs('admin.costing.*') || request()->routeIs('admin.standard-cost-sheets.*') ? 'show' : '' }}">
                    <a href="{{ route('admin.warehouse-transactions.dashboard') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.warehouse-transactions.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-column"></i><span class="nav-label">Dashboard kho</span>
                    </a>
                    <a href="{{ route('admin.warehouse-transactions.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.warehouse-transactions.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-warehouse"></i><span class="nav-label">Giao dịch kho</span>
                    </a>
                    <a href="{{ route('admin.warehouse-transactions.soan-hang') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.warehouse-transactions.soan-hang') ? 'active' : '' }}">
                        <i class="fa-solid fa-dolly-flatbed"></i><span class="nav-label">Soạn hàng</span>
                    </a>
                    <a href="{{ route('admin.warehouse-documents.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.warehouse-documents.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice"></i><span class="nav-label">Phiếu kho</span>
                    </a>
                    <a href="{{ route('admin.warehouse-transactions.ton-kho') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.warehouse-transactions.ton-kho') ? 'active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked"></i><span class="nav-label">Tồn kho</span>
                    </a>
                    <a href="{{ route('admin.costing.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.costing.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-calculator"></i><span class="nav-label">Giá vốn</span>
                    </a>
                    <a href="{{ route('admin.standard-cost-sheets.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.standard-cost-sheets.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice-dollar"></i><span class="nav-label">Định mức giá vốn</span>
                    </a>
                </div>
            </div>
        @endcan

        {{-- DANH MỤC --}}
        @can('catalog.view')
            <div class="sidebar-section">
                <button class="sidebar-section-label" type="button" data-bs-toggle="collapse"
                    data-bs-target="#sidebarCatalog"
                    aria-expanded="{{ request()->routeIs('admin.hang-hoa.*') || request()->routeIs('admin.khach-hang.*') || request()->routeIs('admin.nha-cung-cap.*') || request()->routeIs('admin.purchase-orders.*') ? 'true' : 'false' }}">
                    Danh mục <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="sidebarCatalog"
                    class="collapse {{ request()->routeIs('admin.hang-hoa.*') || request()->routeIs('admin.khach-hang.*') || request()->routeIs('admin.nha-cung-cap.*') || request()->routeIs('admin.purchase-orders.*') ? 'show' : '' }}">
                    <a href="{{ route('admin.hang-hoa.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.hang-hoa.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box-open"></i><span class="nav-label">Hàng hóa</span>
                    </a>
                    <a href="{{ route('admin.khach-hang.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.khach-hang.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-building"></i><span class="nav-label">Khách hàng</span>
                    </a>
                    <a href="{{ route('admin.nha-cung-cap.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.nha-cung-cap.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck"></i><span class="nav-label">Nhà cung cấp</span>
                    </a>
                    <a href="{{ route('admin.purchase-orders.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-cart-shopping"></i><span class="nav-label">Đặt hàng NVL</span>
                    </a>
                </div>
            </div>
        @endcan

        {{-- HỆ THỐNG --}}
        @role('admin')
            <div class="sidebar-section">
                <button class="sidebar-section-label" type="button" data-bs-toggle="collapse"
                    data-bs-target="#sidebarSystem"
                    aria-expanded="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.notifications.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.activity-logs.*') ? 'true' : 'false' }}">
                    Hệ thống <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="sidebarSystem"
                    class="collapse {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.notifications.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.activity-logs.*') ? 'show' : '' }}">
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i><span class="nav-label">Người dùng</span>
                    </a>
                    <a href="{{ route('admin.notifications.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell"></i><span class="nav-label">Thông báo</span>
                        @php $unread = \App\Models\ErpNotification::unread()->count(); @endphp
                        @if ($unread > 0)
                            <span class="badge bg-danger ms-auto" style="font-size:.6rem">{{ $unread }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gears"></i><span class="nav-label">Cài đặt</span>
                    </a>
                    <a href="{{ route('admin.activity-logs.index') }}"
                        class="nav-item-sb {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i><span class="nav-label">Nhật ký</span>
                    </a>
                </div>
            </div>
        @endrole

        {{-- Footer: Logout --}}
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item-sb sidebar-logout w-100 text-start border-0"
                    style="background:none;cursor:pointer">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="nav-label">Đăng xuất</span>
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
                position:absolute;top:0;right:0;
                min-width:16px;height:16px;padding:0 4px;border-radius:999px;
                background:#ef4444;color:#fff;display:none;
                font-size:.62rem;font-weight:800;line-height:16px;text-align:center;
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
        const AppLoader = (() => {
            let timer = null;
            const fileResponsePattern = /(^|\/)(export|template|download)(\/|$|-)/i;

            const isFileResponseUrl = url => fileResponsePattern.test(url.pathname);

            const show = (delay = 120) => {
                clearTimeout(timer);
                timer = setTimeout(() => document.body.classList.add('app-is-loading'), delay);
            };

            const hide = () => {
                clearTimeout(timer);
                document.body.classList.remove('app-is-loading');
            };

            window.addEventListener('load', hide);
            window.addEventListener('pageshow', hide);

            document.addEventListener('click', event => {
                const link = event.target.closest?.('a[href]');
                if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const href = link.getAttribute('href') || '';
                const url = new URL(link.href, window.location.href);
                const samePageHash = url.pathname === window.location.pathname && url.search === window.location.search && url.hash;

                if (
                    link.target === '_blank' ||
                    link.hasAttribute('download') ||
                    link.dataset.noLoader !== undefined ||
                    link.dataset.bsToggle ||
                    isFileResponseUrl(url) ||
                    href.startsWith('#') ||
                    href.startsWith('javascript:') ||
                    href.startsWith('mailto:') ||
                    href.startsWith('tel:') ||
                    samePageHash
                ) {
                    return;
                }

                show();
            }, true);

            document.addEventListener('submit', event => {
                const form = event.target;
                const actionUrl = new URL(form.action || window.location.href, window.location.href);
                if (
                    event.defaultPrevented ||
                    form.dataset.noLoader !== undefined ||
                    form.target === '_blank' ||
                    isFileResponseUrl(actionUrl) ||
                    (typeof form.checkValidity === 'function' && !form.checkValidity())
                ) {
                    return;
                }

                show(80);
            }, true);

            return { show, hide };
        })();

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
                    if (b) {
                        b.textContent = d.count > 99 ? '99+' : d.count;
                        b.style.display = d.count > 0 ? 'block' : 'none';
                    }
                }).catch(() => {});
        @endauth
        }
        @auth updateBell();
        setInterval(updateBell, 60000);
        @endauth

        // Table sorting
        document.addEventListener('DOMContentLoaded', function() {
            const isoToDisplayDate = value => {
                const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
                return match ? `${match[3]}/${match[2]}/${match[1]}` : value;
            };
            const displayToIsoDate = value => {
                const trimmed = (value || '').trim();
                const dmy = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec(trimmed);
                if (dmy) {
                    return `${dmy[3]}-${dmy[2].padStart(2, '0')}-${dmy[1].padStart(2, '0')}`;
                }

                const compactDmy = /^(\d{2})(\d{2})(\d{4})$/.exec(trimmed);
                if (compactDmy) {
                    return `${compactDmy[3]}-${compactDmy[2]}-${compactDmy[1]}`;
                }

                return trimmed;
            };

            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.type = 'text';
                input.inputMode = 'numeric';
                input.placeholder = 'dd/mm/yyyy';
                input.autocomplete = 'off';
                input.dataset.dateDisplay = 'dmy';
                input.value = isoToDisplayDate(input.value);
                input.addEventListener('blur', () => {
                    input.value = isoToDisplayDate(displayToIsoDate(input.value));
                });
            });

            document.addEventListener('submit', event => {
                event.target.querySelectorAll?.('input[data-date-display="dmy"]').forEach(input => {
                    input.value = displayToIsoDate(input.value);
                });
            }, true);

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
