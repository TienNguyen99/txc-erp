<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TXC ERP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #f0f2f5;
            font-family: 'Figtree', sans-serif;
        }

        .navbar-erp {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
        }

        .navbar-erp .navbar-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .navbar-erp .nav-link {
            color: rgba(255, 255, 255, .85);
            font-size: .9rem;
        }

        .navbar-erp .nav-link:hover,
        .navbar-erp .nav-link.active {
            color: #fff;
        }

        .card-page {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .08);
            padding: 1.5rem;
        }

        .table thead.table-dark th {
            background: #1e3a5f;
            border-color: #163050;
            font-size: .85rem;
            letter-spacing: .3px;
        }

        .btn-xs {
            padding: .2rem .5rem;
            font-size: .75rem;
        }

        .badge {
            font-size: .78rem;
        }
    </style>
    @yield('css')
</head>

<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-erp mb-0">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('warehouse.index') }}"><i class="fa-solid fa-warehouse me-2"></i>TXC
                ERP</a>
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse"
                data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('warehouse.index') ? 'active fw-bold' : '' }}"
                            href="{{ route('warehouse.index') }}"><i class="fa-solid fa-exchange-alt me-1"></i>Giao Dịch
                            Kho</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('warehouse.ton-kho') ? 'active fw-bold' : '' }}"
                            href="{{ route('warehouse.ton-kho') }}"><i class="fa-solid fa-boxes-stacked me-1"></i>Tồn
                            Kho</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('warehouse.create') ? 'active fw-bold' : '' }}"
                            href="{{ route('warehouse.create') }}"><i class="fa-solid fa-plus me-1"></i>Nhập / Xuất</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.*') ? 'active fw-bold' : '' }}"
                            href="#" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-gauge-high me-1"></i>Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i
                                        class="fa-solid fa-gauge-high me-1"></i>Dashboard</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('admin.users') }}"><i
                                        class="fa-solid fa-users me-1"></i>Users</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.orders') }}"><i
                                        class="fa-solid fa-file-invoice me-1"></i>Đơn hàng</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.order-tracking') }}"><i
                                        class="fa-solid fa-truck-fast me-1"></i>Order Tracking</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.production-reports') }}"><i
                                        class="fa-solid fa-industry me-1"></i>Báo cáo SX</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.warehouse-transactions') }}"><i
                                        class="fa-solid fa-warehouse me-1"></i>Giao dịch Kho</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-circle me-1"></i>{{ Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit"><i
                                            class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="py-3">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
