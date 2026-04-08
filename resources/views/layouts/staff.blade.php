<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TEXENCO — Nhân viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: #10b981; --primary-dark: #059669; --bg: #f0fdf4; --surface: #ffffff; }
        body { background: var(--bg); font-family: 'Inter', sans-serif; }
        .staff-nav { background: var(--surface); border-bottom: 2px solid var(--primary); }
        .staff-nav .brand { font-weight: 700; color: var(--primary-dark); font-size: 1.1rem; }
        .staff-card { background: var(--surface); border-radius: 16px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .btn-staff { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-staff:hover { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }
    </style>
    @yield('css')
</head>
<body>
    <nav class="navbar staff-nav mb-3">
        <div class="container">
            <span class="brand"><i class="fa-solid fa-warehouse me-2"></i>TEXENCO Kho</span>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted" style="font-size:.85rem">
                    <i class="fa-solid fa-user me-1"></i>{{ Auth::user()->name ?? 'NV' }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
    </nav>
    <div class="container pb-4">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
