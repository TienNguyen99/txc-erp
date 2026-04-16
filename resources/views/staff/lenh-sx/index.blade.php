<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lệnh SX — {{ $trackingNumber }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --bg: #f0f4ff;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .scan-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            padding: 1.5rem 1rem;
            border-radius: 0 0 24px 24px;
        }

        .child-card {
            background: #fff;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
            margin-bottom: .75rem;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform .15s ease, box-shadow .15s ease;
            border-left: 4px solid transparent;
        }

        .child-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(99, 102, 241, .15);
            border-left-color: var(--primary);
        }

        .child-card .lenh-code {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .child-card .ma-hh {
            font-size: .85rem;
            color: #475569;
            font-weight: 600;
        }

        .child-card .ten-hh {
            font-size: .78rem;
            color: #94a3b8;
        }

        .child-card .stats {
            display: flex;
            gap: .75rem;
            margin-top: .5rem;
            flex-wrap: wrap;
        }

        .child-card .stat-item {
            font-size: .75rem;
            background: #f8fafc;
            padding: .25rem .6rem;
            border-radius: 8px;
        }

        .child-card .stat-item .stat-label {
            color: #94a3b8;
            font-weight: 500;
        }

        .child-card .stat-item .stat-value {
            font-weight: 700;
            color: #1e293b;
        }

        .progress-bar-custom {
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            margin-top: .5rem;
            overflow: hidden;
        }

        .progress-bar-custom .fill {
            height: 100%;
            border-radius: 3px;
            background: linear-gradient(90deg, #6366f1, #10b981);
            transition: width .3s ease;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: .5;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="scan-header text-center">
        <div style="font-size:.75rem;opacity:.8">LỆNH SẢN XUẤT</div>
        <h4 class="fw-bold mb-1">{{ $trackingNumber }}</h4>
        <div style="font-size:.8rem;opacity:.8">
            <i class="fa-solid fa-list-ol me-1"></i>{{ $children->count() }} lệnh con
        </div>
    </div>

    <div class="container px-3 py-3" style="max-width:600px">

        @if ($children->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-clipboard-list"></i>
                <p>Chưa có lệnh con nào được lên lệnh.<br>
                    Vui lòng liên hệ quản lý.</p>
            </div>
        @else
            <div class="mb-2" style="font-size:.8rem;color:#64748b">
                <i class="fa-solid fa-hand-pointer me-1"></i>Chọn lệnh để báo cáo sản xuất
            </div>

            @foreach ($children as $child)
                <a href="{{ route('lenh-sx.scan', [$trackingNumber, $child->stt]) }}" class="child-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="lenh-code">
                                <i class="fa-solid fa-clipboard-list me-1"></i>{{ $child->lenh_child }}
                            </div>
                            <div class="ma-hh mt-1">{{ $child->ma_hh }}</div>
                            @if ($child->ten_hh)
                                <div class="ten-hh">{{ $child->ten_hh }}</div>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary" style="font-size:.7rem">{{ $child->mau ?: '—' }}</span>
                        </div>
                    </div>

                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-label">Đơn hàng</span>
                            <span class="stat-value ms-1">{{ number_format($child->tong_sl, 0) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Cần SX</span>
                            <span class="stat-value ms-1 text-info">{{ number_format($child->sl_can_sx, 0) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Đã SX</span>
                            <span class="stat-value ms-1 text-warning">{{ number_format($child->sl_da_sx, 0) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Tồn kho</span>
                            <span class="stat-value ms-1 {{ $child->ton_kho > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($child->ton_kho, 0) }}</span>
                        </div>
                    </div>

                    <div class="progress-bar-custom">
                        <div class="fill" style="width:{{ $child->progress }}%"></div>
                    </div>
                    <div class="text-end" style="font-size:.7rem;color:#94a3b8;margin-top:2px">
                        {{ $child->progress }}% hoàn thành
                    </div>
                </a>
            @endforeach
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
