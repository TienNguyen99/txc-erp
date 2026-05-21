@extends('layouts.app')

@section('css')
    <style>
        .stat-card {
            border-radius: var(--radius);
            padding: 1.5rem;
            color: #fff;
            transition: all .25s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .12);
        }

        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .stat-card .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -.5px;
        }

        .stat-card .stat-label {
            font-size: .8rem;
            opacity: .85;
            font-weight: 500;
        }

        .bg-grad-1 {
            background: linear-gradient(135deg, #f7941d, #fbb04c);
        }

        .bg-grad-2 {
            background: linear-gradient(135deg, #ec4899, #f43f5e);
        }

        .bg-grad-3 {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
        }

        .bg-grad-4 {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .bg-grad-5 {
            background: linear-gradient(135deg, #f59e0b, #f97316);
        }

        .bg-grad-6 {
            background: linear-gradient(135deg, #14b8a6, #0d9488);
        }

        .bg-grad-7 {
            background: linear-gradient(135deg, #f7941d, #e07b08);
        }

        .section-title {
            font-size: .85rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -.2px;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .section-title i {
            color: var(--primary);
            font-size: .9rem;
        }

        .table-modern tbody td {
            font-size: .85rem;
        }

        .table-modern thead th {
            font-size: .72rem;
        }

        /* Production Order Filter Section */
        .filter-card {
            background: linear-gradient(135deg, rgba(247, 148, 29, .05) 0%, rgba(247, 148, 29, .09) 100%);
            border: 1px solid rgba(247, 148, 29, .18);
            border-radius: var(--radius);
            padding: 1.25rem;
        }

        .filter-card .filter-select {
            border-radius: 12px;
            border: 2px solid rgba(247, 148, 29, .2);
            padding: .6rem 1rem;
            font-size: .85rem;
            font-weight: 500;
            transition: all .2s ease;
            background: #fff;
        }

        .filter-card .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(247, 148, 29, .15);
        }

        .lenh-sx-table th {
            background: linear-gradient(135deg, #f7941d, #e07b08);
            color: #fff;
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
            border: none;
        }

        .lenh-sx-table td {
            font-size: .82rem;
            vertical-align: middle;
        }

        .lenh-sx-table tbody tr {
            transition: all .15s ease;
        }

        .lenh-sx-table tbody tr:hover {
            background: rgba(247, 148, 29, .04);
        }

        .summary-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .8rem;
            border-radius: 10px;
            font-size: .8rem;
            font-weight: 600;
        }

        .summary-pill.det {
            background: rgba(59, 130, 246, .1);
            color: #3b82f6;
        }

        .summary-pill.dh {
            background: rgba(247, 148, 29, .1);
            color: #c55f00;
        }

        .summary-pill.nk {
            background: rgba(16, 185, 129, .1);
            color: #10b981;
        }

        .qty-cell {
            font-weight: 600;
            font-size: .85rem;
        }

        .qty-cell.text-det {
            color: #3b82f6;
        }

        .qty-cell.text-dh {
            color: #c55f00;
        }

        .qty-cell.text-nk {
            color: #10b981;
        }

        /* ── Performance animations (GPU-composited only) ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0);    }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(.94); }
            to   { opacity: 1; transform: scale(1);   }
        }
        @keyframes countUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0);   }
        }

        /* Stat cards staggered entrance */
        .stat-card { animation: fadeUp .45s cubic-bezier(.22,.68,0,1.2) both; }
        .stat-card:nth-child(1) { animation-delay: .05s; }
        .stat-card:nth-child(2) { animation-delay: .12s; }
        .stat-card:nth-child(3) { animation-delay: .19s; }
        .stat-card:nth-child(4) { animation-delay: .26s; }
        .stat-card:nth-child(5) { animation-delay: .33s; }
        .stat-card:nth-child(6) { animation-delay: .40s; }
        .stat-card .stat-number { animation: countUp .5s .5s ease both; }

        /* Chart cards entrance */
        .card { animation: scaleIn .4s cubic-bezier(.22,.68,0,1.15) both; will-change: transform, opacity; }

        /* Smooth card hover — transform only (GPU) */
        .card {
            transition: transform .22s cubic-bezier(.22,.68,0,1.2),
                        box-shadow .22s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,.09) !important;
        }

        /* Table row hover — background only, no reflow */
        .lenh-sx-table tbody tr {
            transition: background-color .15s ease;
        }

        /* Stat card icon pulse on hover */
        .stat-card:hover .stat-icon {
            animation: pulse 1s ease infinite;
        }
        @keyframes pulse {
            0%,100% { transform: scale(1); }
            50%      { transform: scale(1.12); }
        }

        /* Skeleton shimmer (utility class) */
        @keyframes shimmer {
            from { background-position: -400px 0; }
            to   { background-position: 400px 0; }
        }
        .shimmer {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 800px 100%;
            animation: shimmer 1.4s infinite linear;
            will-change: background-position;
        }

        .focus-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .08) !important;
            overflow: hidden;
        }

        .focus-card .card-body {
            padding: 1rem;
        }

        .focus-card.revenue {
            border-top: 3px solid #10b981 !important;
        }

        .focus-card.risk {
            border-top: 3px solid #f59e0b !important;
        }

        .focus-card.product {
            border-top: 3px solid #3b82f6 !important;
        }

        .focus-card .focus-title {
            font-size: .95rem;
            font-weight: 700;
            color: #0f172a;
        }

        .focus-kpi {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem;
        }

        .focus-kpi > div {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: .65rem .75rem;
            background: linear-gradient(180deg, #fff, #f8fafc);
        }

        .focus-kpi .label {
            font-size: .72rem;
            color: #64748b;
            font-weight: 600;
        }

        .focus-kpi .value {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: .15rem;
        }

        .focus-table th {
            background: #f1f5f9;
            color: #475569;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .02em;
            white-space: nowrap;
            padding: .65rem .75rem;
        }

        .focus-table td {
            font-size: .83rem;
            vertical-align: middle;
            padding: .65rem .75rem;
        }

        .focus-table tbody tr:hover {
            background: #f8fafc;
        }

        .focus-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .risk-late {
            color: #dc2626;
            font-weight: 800;
        }

        .risk-due {
            color: #d97706;
            font-weight: 800;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="page-title mb-1">
                    <i class="fa-solid fa-gauge-high me-2"></i>Dashboard
                </h4>
                <p class="text-muted mb-0" style="font-size:.85rem">
                    Vận hành từ {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }}
                    đến {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }}
                </p>
            </div>
            <span class="badge" style="background:#f1f5f9;color:var(--text);font-size:.8rem;padding:.5em 1em;">
                <i class="fa-regular fa-calendar me-1"></i>{{ now()->format('d/m/Y H:i') }}
            </span>
        </div>

        <div class="card-page mb-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                        value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-lg-3 col-md-4">
                    <label class="form-label">Khách hàng</label>
                    <select name="khach_hang_id" id="dashboardKhachHang" class="form-select form-select-sm">
                        <option value="">Tất cả khách hàng</option>
                        @foreach ($khachHangOptions as $id => $ten)
                            <option value="{{ $id }}" {{ (string) $filters['khach_hang_id'] === (string) $id ? 'selected' : '' }}>
                                {{ $ten }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4">
                    <label class="form-label">Nhóm hàng</label>
                    <select name="nhom_hang" id="dashboardNhomHang" class="form-select form-select-sm"
                        data-selected="{{ $filters['nhom_hang'] }}">
                        <option value="">Tất cả nhóm hàng</option>
                        @foreach ($nhomHangOptions as $nhom)
                            <option value="{{ $nhom['ma_nhom'] }}" {{ $filters['nhom_hang'] === $nhom['ma_nhom'] ? 'selected' : '' }}>
                                {{ $nhom['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-filter me-1"></i>Lọc
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-rotate-left me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Executive focus: doanh thu khách hàng + lot cần xử lý --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-4">
                <div class="card focus-card revenue h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="focus-title">Doanh thu theo khách hàng</div>
                                <div class="text-muted small">Giá trị đơn, đã hóa đơn/đã giao và phần còn lại</div>
                            </div>
                            <span class="badge bg-success-subtle text-success">
                                {{ $opsDashboard['finance']['summary']['invoice_rate'] ?? 0 }}%
                            </span>
                        </div>
                        <div class="focus-kpi mb-3">
                            <div>
                                <div class="label">Giá trị đơn</div>
                                <div class="value">{{ number_format($opsDashboard['finance']['summary']['order_revenue'], 0) }}</div>
                            </div>
                            <div>
                                <div class="label">Đã HĐ/giao</div>
                                <div class="value text-success">{{ number_format($opsDashboard['finance']['summary']['invoiced_revenue'], 0) }}</div>
                            </div>
                            <div>
                                <div class="label">Còn lại</div>
                                <div class="value text-warning">{{ number_format($opsDashboard['finance']['summary']['uninvoiced_revenue'], 0) }}</div>
                            </div>
                        </div>
                        <div class="table-responsive focus-table-wrap">
                            <table class="table table-sm focus-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th class="text-end">Giá trị đơn</th>
                                        <th class="text-end">Đã HĐ/giao</th>
                                        <th class="text-end">Còn lại</th>
                                        <th class="text-end">Tỷ lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($opsDashboard['finance']['by_customer']->take(6) as $r)
                                        <tr>
                                            <td class="fw-semibold">{{ $r['customer'] }}</td>
                                            <td class="text-end">{{ number_format($r['revenue'], 0) }}</td>
                                            <td class="text-end text-success">{{ number_format($r['invoiced_revenue'], 0) }}</td>
                                            <td class="text-end text-warning">{{ number_format($r['uninvoiced_revenue'], 0) }}</td>
                                            <td class="text-end fw-bold">{{ $r['invoice_rate'] ?? 'N/A' }}{{ $r['invoice_rate'] !== null ? '%' : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-muted">Chưa có dữ liệu doanh thu trong phạm vi lọc.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card focus-card product h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="focus-title">Mặt hàng doanh thu cao</div>
                                <div class="text-muted small">Theo filter khách hàng, ngày và nhóm hàng hiện tại</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">
                                Top {{ $opsDashboard['finance']['by_product']->count() }}
                            </span>
                        </div>
                        <div class="table-responsive focus-table-wrap">
                            <table class="table table-sm focus-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã hàng</th>
                                        <th class="text-end">SL</th>
                                        <th class="text-end">Doanh thu</th>
                                        <th class="text-end">Còn lại</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($opsDashboard['finance']['by_product']->take(6) as $r)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $r['ma_hh'] }}</div>
                                                <div class="text-muted" style="font-size:.72rem">{{ \Illuminate\Support\Str::limit($r['ten_hh'], 34) }}</div>
                                            </td>
                                            <td class="text-end">{{ number_format($r['qty'], 0) }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($r['revenue'], 0) }}</td>
                                            <td class="text-end text-warning">{{ number_format($r['uninvoiced_revenue'], 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-muted">Chưa có mặt hàng phát sinh doanh thu trong phạm vi lọc.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card focus-card risk h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="focus-title">Lot trễ / sắp đến hạn</div>
                                <div class="text-muted small">Ưu tiên theo hạn giao gần nhất trong 7 ngày</div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning">
                                {{ $reportDashboard['near_due_production']->count() }} lot
                            </span>
                        </div>
                        <div class="table-responsive focus-table-wrap">
                            <table class="table table-sm focus-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Lot</th>
                                        <th>Khách</th>
                                        <th>Công đoạn</th>
                                        <th class="text-end">Hạn</th>
                                        <th class="text-end">Còn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportDashboard['near_due_production']->take(8) as $r)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.order-tracking.lot', $r['tracking_number']) }}" class="fw-semibold text-decoration-none">
                                                    {{ $r['tracking_number'] }}
                                                </a>
                                            </td>
                                            <td>{{ $r['customer'] }}</td>
                                            <td><span class="badge bg-secondary">{{ $r['stage'] }}</span></td>
                                            <td class="text-end">{{ $r['due_date'] }}</td>
                                            <td class="text-end {{ $r['days_left'] < 0 ? 'risk-late' : 'risk-due' }}">
                                                {{ $r['days_left'] < 0 ? 'Trễ ' . abs($r['days_left']) : $r['days_left'] . ' ngày' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-muted">
                                                Không có lot trễ hoặc sắp đến hạn trong 7 ngày. Các lot chưa giao nằm ở bảng bên dưới.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($reportDashboard['undelivered_lot_count'] > 0)
                            <div class="mt-3 small text-muted">
                                Tổng lot chưa giao: <span class="fw-bold text-dark">{{ $reportDashboard['undelivered_lot_count'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="production-tracking-top"></div>

        {{-- Báo cáo cần xử lý --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fa-solid fa-triangle-exclamation me-2" style="color:#f7941d"></i>Báo cáo cần xử lý
                </h6>
                <span class="badge" style="background:#fff7ed;color:#c55f00">Gần hạn • Giao hàng • NVL • WIP</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#fff7ed;border:1px solid #fed7aa">
                            <div class="text-muted small">Lệnh gần hạn / trễ chưa xong</div>
                            <div class="fs-4 fw-bold">{{ $reportDashboard['near_due_production']->count() }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#ecfdf5;border:1px solid #a7f3d0">
                            <div class="text-muted small">Đơn đã giao</div>
                            <div class="fs-4 fw-bold">{{ $reportDashboard['order_status_counts']['shipped'] }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe">
                            <div class="text-muted small">Lot chưa giao</div>
                            <div class="fs-4 fw-bold">
                                {{ $reportDashboard['undelivered_lot_count'] }}
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#fef2f2;border:1px solid #fecaca">
                            <div class="text-muted small">Mã thiếu NVL</div>
                            <div class="fs-4 fw-bold">{{ $reportDashboard['material_shortages']->count() }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#fff7ed;border:1px solid #fed7aa !important">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">1. Lệnh gần đến ngày nhưng chưa xong</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Lệnh/Lot</th>
                                                <th>Khách</th>
                                                <th>Công đoạn kẹt</th>
                                                <th class="text-end">Hạn</th>
                                                <th class="text-end">Còn</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($reportDashboard['near_due_production'] as $r)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.order-tracking.lot', $r['tracking_number']) }}" class="fw-semibold text-decoration-none">
                                                            {{ $r['tracking_number'] }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $r['customer'] }}</td>
                                                    <td><span class="badge bg-warning text-dark">{{ $r['stage'] }}</span></td>
                                                    <td class="text-end">{{ $r['due_date'] }}</td>
                                                    <td class="text-end fw-bold {{ $r['days_left'] < 0 ? 'text-danger' : ($r['days_left'] === 0 ? 'text-warning' : '') }}">
                                                        {{ $r['days_left'] }} ngày
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-muted">Không có lệnh gần hạn hoặc trễ trong 7 ngày.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc;border:1px solid #e2e8f0 !important">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">2. Lot chưa giao</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Khách</th>
                                                <th class="text-end">Số mã</th>
                                                <th class="text-end">SL</th>
                                                <th class="text-end">Hạn</th>
                                                <th class="text-end">Công đoạn</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($reportDashboard['undelivered_lots'] as $r)
                                                <tr>
                                                    <td>
                                                        @if($r['tracking_number'] !== '-')
                                                            <a href="{{ route('admin.order-tracking.lot', $r['tracking_number']) }}" class="fw-semibold text-decoration-none">
                                                                {{ $r['tracking_number'] }}
                                                            </a>
                                                        @else
                                                            <span class="fw-semibold">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $r['customer'] }}</td>
                                                    <td class="text-end">{{ number_format($r['total_items']) }}</td>
                                                    <td class="text-end">{{ number_format($r['total_qty'], 2) }}</td>
                                                    <td class="text-end">{{ $r['due_date'] }}</td>
                                                    <td class="text-end"><span class="badge bg-secondary">{{ $r['stage'] }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-muted">Không có lot chưa giao trong phạm vi lọc.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#fef2f2;border:1px solid #fecaca !important">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">3. Thiếu nguyên vật liệu</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>NVL</th>
                                                <th class="text-end">Cần</th>
                                                <th class="text-end">Tồn</th>
                                                <th class="text-end">Thiếu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($reportDashboard['material_shortages'] as $r)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $r['ma_hh'] }}</div>
                                                        <div class="text-muted" style="font-size:.7rem">{{ $r['ten_hh'] }}</div>
                                                    </td>
                                                    <td class="text-end">{{ number_format($r['required'], 0) }}</td>
                                                    <td class="text-end">{{ number_format($r['on_hand'], 0) }}</td>
                                                    <td class="text-end fw-bold text-danger">{{ number_format($r['shortage'], 0) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Không thiếu NVL theo kế hoạch hiện tại.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc;border:1px solid #e2e8f0 !important">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">4. Cảnh báo thêm</h6>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="p-3 rounded-3 bg-white border h-100">
                                            <div class="text-muted small">Công đoạn kẹt &gt; 7 ngày</div>
                                            <div class="fs-5 fw-bold">{{ $reportDashboard['stuck_stages']->sum('over_7_days') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded-3 bg-white border h-100">
                                            <div class="text-muted small">PO/NVL trễ</div>
                                            <div class="fs-5 fw-bold">{{ $reportDashboard['late_po_count'] }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded-3 bg-white border h-100">
                                            <div class="text-muted small">Thiếu BOM/giá vốn</div>
                                            <div class="fs-5 fw-bold">{{ $reportDashboard['missing_cost_data']->count() }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Công đoạn</th>
                                                <th class="text-end">WIP</th>
                                                <th class="text-end">Tuổi TB</th>
                                                <th class="text-end">&gt; 7 ngày</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($reportDashboard['stuck_stages'] as $r)
                                                <tr>
                                                    <td>{{ $r['stage'] }}</td>
                                                    <td class="text-end">{{ $r['count'] }}</td>
                                                    <td class="text-end">{{ $r['avg_days'] }}</td>
                                                    <td class="text-end fw-bold text-danger">{{ $r['over_7_days'] }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Không có công đoạn kẹt quá 7 ngày.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Operations Dashboard (vận hành thật) --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fa-solid fa-industry me-2" style="color:#f7941d"></i>Operations Dashboard
                </h6>
                <span class="badge" style="background:#f1f5f9;color:#475569">OTD • WIP • MRP • PO • Finance</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#fff7ed;border:1px solid #fed7aa">
                            <div class="text-muted small">Lot đến hạn / trễ (≤ 7 ngày)</div>
                            <div class="fs-4 fw-bold">{{ $opsDashboard['otd']['at_risk_lots']->count() }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe">
                            <div class="text-muted small">WIP công đoạn đang mở</div>
                            <div class="fs-4 fw-bold">{{ $opsDashboard['wip']['stages']->sum('total') }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#ecfdf5;border:1px solid #a7f3d0">
                            <div class="text-muted small">PO mở / PO trễ</div>
                            <div class="fs-4 fw-bold">{{ $opsDashboard['procurement']['open_po_count'] }} / {{ $opsDashboard['procurement']['late_po_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded-3" style="background:#f5f3ff;border:1px solid #ddd6fe">
                            <div class="text-muted small">Tỷ lệ lỗi</div>
                            <div class="fs-4 fw-bold">{{ $opsDashboard['quality']['defect_rate_30d'] }}%</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-xl-4">
                        <div class="card border-0 h-100" style="background:#fff7ed;border:1px solid #fed7aa !important">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Đủ hàng chờ xuất</h6>
                                    <span class="badge bg-success">{{ $opsDashboard['action']['alerts']['ready_to_ship'] }}</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Khách</th>
                                                <th class="text-end">Hạn</th>
                                                <th class="text-end">SL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['action']['ready_to_ship_lots'] as $r)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.order-tracking.lot', $r['tracking_number']) }}" class="fw-semibold text-decoration-none">
                                                            {{ $r['tracking_number'] }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $r['customer'] }}</td>
                                                    <td class="text-end">{{ $r['due_date'] }}</td>
                                                    <td class="text-end">{{ number_format($r['total_qty'], 0) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Chưa có lot đủ hàng chờ xuất.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-0 h-100" style="background:#fef2f2;border:1px solid #fecaca !important">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Thiếu NVL theo BOM</h6>
                                    <span class="badge bg-danger">{{ $opsDashboard['action']['alerts']['material_shortages'] }}</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>NVL</th>
                                                <th class="text-end">Cần</th>
                                                <th class="text-end">Tồn</th>
                                                <th class="text-end">Thiếu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['action']['bom_material_shortages'] as $r)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $r['ma_hh'] }}</div>
                                                        <div class="text-muted" style="font-size:.7rem">{{ $r['ten_hh'] }}</div>
                                                    </td>
                                                    <td class="text-end">{{ number_format($r['required'], 0) }}</td>
                                                    <td class="text-end">{{ number_format($r['on_hand'], 0) }}</td>
                                                    <td class="text-end fw-bold text-danger">{{ number_format($r['shortage'], 0) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Không thiếu NVL theo BOM hiện tại.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-0 h-100" style="background:#f8fafc;border:1px solid #e2e8f0 !important">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Thiếu dữ liệu giá vốn</h6>
                                    <span class="badge bg-warning text-dark">{{ $opsDashboard['action']['alerts']['missing_cost_data'] }}</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Mã HH</th>
                                                <th>Tên hàng</th>
                                                <th>Thiếu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['action']['missing_cost_data'] as $r)
                                                <tr>
                                                    <td class="fw-semibold">{{ $r['ma_hh'] }}</td>
                                                    <td>{{ $r['ten_hh'] }}</td>
                                                    <td><span class="badge bg-warning text-dark">{{ $r['issues'] }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-muted">Dữ liệu BOM/giá đã đủ cho mã đang mở.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">OTD theo khách hàng (3 tháng)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Khách hàng</th>
                                                <th class="text-end">On-time</th>
                                                <th class="text-end">Tổng</th>
                                                <th class="text-end">OTD%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['otd']['by_customer'] as $r)
                                                <tr>
                                                    <td>{{ $r['customer'] }}</td>
                                                    <td class="text-end">{{ $r['on_time'] }}</td>
                                                    <td class="text-end">{{ $r['total'] }}</td>
                                                    <td class="text-end fw-bold">{{ $r['rate'] }}%</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Chưa có dữ liệu giao hàng.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">Lot đến hạn / trễ (≤ 7 ngày)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Khách hàng</th>
                                                <th>Công đoạn</th>
                                                <th class="text-end">Items</th>
                                                <th class="text-end">Hạn</th>
                                                <th class="text-end">Còn</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['otd']['at_risk_lots'] as $r)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.order-tracking.lot', $r['tracking_number']) }}" class="fw-semibold text-decoration-none">
                                                            {{ $r['tracking_number'] }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $r['customer'] }}</td>
                                                    <td>{{ $r['stage'] }}</td>
                                                    <td class="text-end">{{ $r['total_items'] }}</td>
                                                    <td class="text-end">{{ $r['due_date'] }}</td>
                                                    <td class="text-end fw-bold">{{ $r['days_left'] }} ngày</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-muted">Không có lot đến hạn hoặc trễ trong cửa sổ 7 ngày.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#fff7ed;border:1px solid #fed7aa !important">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">Lot gần ngày xe lấy nhưng chưa xuất kho</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Khách hàng</th>
                                                <th>Công đoạn</th>
                                                <th class="text-end">Items</th>
                                                <th class="text-end">Ngày xe lấy</th>
                                                <th class="text-end">Còn</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['otd']['pickup_due_lots'] as $r)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.order-tracking.lot', $r['tracking_number']) }}" class="fw-semibold text-decoration-none">
                                                            {{ $r['tracking_number'] }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $r['customer'] }}</td>
                                                    <td>{{ $r['stage'] }}</td>
                                                    <td class="text-end">{{ $r['total_items'] }}</td>
                                                    <td class="text-end">{{ $r['pickup_date'] }}</td>
                                                    <td class="text-end fw-bold {{ $r['days_left'] < 0 ? 'text-danger' : ($r['days_left'] === 0 ? 'text-warning' : '') }}">
                                                        {{ $r['days_left'] }} ngày
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-muted">Không có lot gần ngày xe lấy chưa xuất kho trong 7 ngày.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">WIP aging theo công đoạn</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Công đoạn</th>
                                                <th class="text-end">WIP</th>
                                                <th class="text-end">Tuổi TB (ngày)</th>
                                                <th class="text-end">&gt; 7 ngày</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['wip']['aging'] as $r)
                                                <tr>
                                                    <td>{{ $r['stage'] }}</td>
                                                    <td class="text-end">{{ $r['count'] }}</td>
                                                    <td class="text-end">{{ $r['avg_days'] }}</td>
                                                    <td class="text-end fw-bold">{{ $r['over_7_days'] }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Chưa có dữ liệu WIP.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">Kho & MRP cảnh báo (thiếu cho kế hoạch)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Mã HH</th>
                                                <th class="text-end">Cần SX</th>
                                                <th class="text-end">Tồn hiện tại</th>
                                                <th class="text-end">Thiếu hụt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['inventory']['top_shortages'] as $r)
                                                <tr>
                                                    <td>{{ $r['ma_hh'] }}</td>
                                                    <td class="text-end">{{ number_format($r['required'], 2) }}</td>
                                                    <td class="text-end">{{ number_format($r['on_hand'], 2) }}</td>
                                                    <td class="text-end fw-bold text-danger">{{ number_format($r['shortage'], 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Không có thiếu hụt cho kế hoạch hiện tại.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">NCC - OTD giao NVL</h6>
                                <div class="text-muted small mb-2">Lead time TB: {{ $opsDashboard['procurement']['avg_lead_time_days'] ?? 'N/A' }} ngày</div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>NCC</th>
                                                <th class="text-end">On-time</th>
                                                <th class="text-end">Tổng</th>
                                                <th class="text-end">OTD%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['procurement']['supplier_otd'] as $r)
                                                <tr>
                                                    <td>{{ $r['supplier'] }}</td>
                                                    <td class="text-end">{{ $r['on_time'] }}</td>
                                                    <td class="text-end">{{ $r['total'] }}</td>
                                                    <td class="text-end fw-bold">{{ $r['rate'] }}%</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Chưa có dữ liệu PO đã nhận.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card border-0 h-100" style="background:#f8fafc">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">Doanh thu & hóa đơn theo khách hàng</h6>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <div class="p-2 rounded bg-white border">
                                            <div class="text-muted small">Giá trị đơn hàng</div>
                                            <div class="fw-bold">{{ number_format($opsDashboard['finance']['summary']['order_revenue'], 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 rounded bg-white border">
                                            <div class="text-muted small">Đã xuất hóa đơn</div>
                                            <div class="fw-bold text-success">{{ number_format($opsDashboard['finance']['summary']['invoiced_revenue'], 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 rounded bg-white border">
                                            <div class="text-muted small">Chưa hóa đơn</div>
                                            <div class="fw-bold text-warning">{{ number_format($opsDashboard['finance']['summary']['uninvoiced_revenue'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Khách hàng</th>
                                                <th class="text-end">Giá trị đơn</th>
                                                <th class="text-end">Đã hóa đơn</th>
                                                <th class="text-end">Chưa hóa đơn</th>
                                                <th class="text-end">Tỷ lệ</th>
                                                <th class="text-end">Giá vốn</th>
                                                <th class="text-end">Margin%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($opsDashboard['finance']['by_customer'] as $r)
                                                <tr>
                                                    <td>{{ $r['customer'] }}</td>
                                                    <td class="text-end">{{ number_format($r['revenue'], 2) }}</td>
                                                    <td class="text-end text-success">{{ number_format($r['invoiced_revenue'], 2) }}</td>
                                                    <td class="text-end text-warning">{{ number_format($r['uninvoiced_revenue'], 2) }}</td>
                                                    <td class="text-end">{{ $r['invoice_rate'] ?? 'N/A' }}{{ $r['invoice_rate'] !== null ? '%' : '' }}</td>
                                                    <td class="text-end">{{ number_format($r['cost'], 2) }}</td>
                                                    <td class="text-end fw-bold {{ ($r['margin_rate'] ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ $r['margin_rate'] ?? 'N/A' }}{{ $r['margin_rate'] !== null ? '%' : '' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-muted">Chưa có dữ liệu tài chính để tính.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat Cards MISA AMIS Style --}}
        <div class="row g-3 mb-4">
            {{-- Đơn chưa hoàn thành --}}
            <div class="col-xl-3 col-lg-6">
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-dark fw-bold" style="font-size: .95rem">Đơn chưa hoàn thành</div>
                                <div class="stat-icon"
                                    style="background-color: #ffe4e6; color: #f43f5e; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-box"></i>
                                </div>
                            </div>
                            <div class="mb-2">
                                <span class="fs-2 fw-bold text-dark">{{ number_format($stats['pending_orders']) }}</span>
                            </div>
                            <div class="d-flex align-items-center" style="font-size: .8rem">
                                <span class="text-success fw-bold me-1">{{ $stats['pct_pending_orders'] }}%</span>
                                <span class="text-muted">Tổng số đơn hàng:
                                    {{ number_format($stats['total_orders']) }}</span>
                            </div>
                            <div class="text-muted mt-2" style="font-size: .7rem">
                                Số liệu tính đến: {{ now()->format('H:i') }} <i class="fa-solid fa-rotate ms-1"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Lệnh chưa hoàn thành --}}
            <div class="col-xl-3 col-lg-6">
                <a href="{{ route('admin.order-tracking.index') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-dark fw-bold" style="font-size: .95rem">Lệnh chưa hoàn thành</div>
                                <div class="stat-icon"
                                    style="background-color: #fef3c7; color: #d97706; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-chart-simple"></i>
                                </div>
                            </div>
                            <div class="mb-2">
                                <span class="fs-2 fw-bold text-dark">{{ number_format($stats['pending_trackings']) }}</span>
                            </div>
                            <div class="d-flex align-items-center" style="font-size: .8rem">
                                <span class="text-success fw-bold me-1">{{ $stats['pct_pending_trackings'] }}%</span>
                                <span class="text-muted">Lệnh đang sản xuất:
                                    {{ number_format($stats['total_trackings']) }}</span>
                            </div>
                            <div class="text-muted mt-2" style="font-size: .7rem">
                                Số liệu tính đến: {{ now()->format('H:i') }} <i class="fa-solid fa-rotate ms-1"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Sản lượng chưa SX --}}
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-dark fw-bold" style="font-size: .95rem">Sản lượng chưa SX</div>
                            <div class="stat-icon"
                                style="background-color: #fff0db; color: #c55f00; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="fs-2 fw-bold text-dark">{{ number_format($stats['unproduced_qty'], 2) }}</span>
                        </div>
                        <div class="d-flex align-items-center" style="font-size: .8rem">
                            <span class="text-success fw-bold me-1">{{ $stats['pct_unproduced'] }}%</span>
                            <span class="text-muted">Cần sản xuất:
                                {{ number_format($stats['total_qty_required'], 2) }}</span>
                        </div>
                        <div class="text-muted mt-2" style="font-size: .7rem">
                            Số liệu tính đến: {{ now()->format('H:i') }} <i class="fa-solid fa-rotate ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tỷ lệ hao hụt NVL --}}
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-dark fw-bold" style="font-size: .95rem">Tỷ lệ hao hụt NVL</div>
                            <div class="stat-icon"
                                style="background-color: #dcfce7; color: #16a34a; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-list-check"></i>
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="fs-2 fw-bold text-dark">{{ $stats['loss_rate'] }} %</span>
                        </div>
                        <div class="d-flex align-items-center" style="font-size: .8rem">
                            <span class="text-danger fw-bold me-1"><i class="fa-solid fa-arrow-trend-down"></i> Tỷ lệ trung
                                bình toàn nhà máy</span>
                        </div>
                        <div class="text-muted mt-2" style="font-size: .7rem">
                            Số liệu tính đến: {{ now()->format('H:i') }} <i class="fa-solid fa-rotate ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dashboard Charts MISA Style --}}
        <div class="row g-3 mb-4">
            {{-- Chart 0: Trạng thái Đơn hàng (Doughnut) --}}
            <div class="col-xl-6 col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #f7941d !important">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-pie me-2" style="color:#f7941d"></i>Trạng thái Đơn hàng</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="position:relative;height:240px;width:100%">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 1: Sản lượng SX theo thời gian (Line) --}}
            <div class="col-xl-6 col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #10b981 !important">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-line me-2" style="color:#10b981"></i>Sản lượng SX theo thời gian</h6>
                        <span class="badge" style="background:rgba(16,185,129,.1);color:#059669;font-size:.72rem">7 ngày qua</span>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:240px;width:100%">
                            <canvas id="productionTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 2: Trạng thái lệnh SX (Doughnut) --}}
            <div class="col-xl-6 col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #3b82f6 !important">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-circle-half-stroke me-2" style="color:#3b82f6"></i>Trạng thái lệnh sản xuất</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="position:relative;height:240px;width:100%">
                            <canvas id="trackingStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 3: Sản lượng theo ca --}}
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #f7941d !important">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-bar me-2" style="color:#f7941d"></i>Theo ca</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:200px;width:100%">
                            <canvas id="productionCaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 4: Sản lượng theo công đoạn --}}
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;border-top:3px solid #10b981 !important">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-industry me-2" style="color:#10b981"></i>Theo công đoạn</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:200px;width:100%">
                            <canvas id="productionStageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- THEO DÕI LỆNH SẢN XUẤT                    --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div id="production-tracking-card" class="card border-0 shadow-sm mb-4" style="border-radius: 12px">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-clipboard-list text-primary me-2"></i>Theo dõi Lệnh Sản Xuất
                    </h6>
                    <p class="text-muted mb-0" style="font-size:.78rem">Tổng quan tiến độ 20 lệnh gần nhất</p>
                </div>
                <a href="{{ route('admin.lenh-san-xuat.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-arrow-right me-1"></i>Xem tất cả
                </a>
            </div>
            <div class="card-body pt-0">
                {{-- Status Mini Cards --}}
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <span class="summary-pill" style="background:rgba(247,148,29,.12);color:#c55f00">
                        <i class="fa-solid fa-layer-group"></i> Tổng: {{ $lenhSxStats->total }}
                    </span>
                    <span class="summary-pill" style="background:rgba(107,114,128,.1);color:#6b7280">
                        <i class="fa-solid fa-file-circle-plus"></i> Mới: {{ $lenhSxStats->new }}
                    </span>
                    <span class="summary-pill" style="background:rgba(245,158,11,.1);color:#f59e0b">
                        <i class="fa-solid fa-clock"></i> Chờ SX: {{ $lenhSxStats->waiting }}
                    </span>
                    <span class="summary-pill" style="background:rgba(59,130,246,.1);color:#3b82f6">
                        <i class="fa-solid fa-industry"></i> Đang SX: {{ $lenhSxStats->producing }}
                    </span>
                    <span class="summary-pill" style="background:rgba(16,185,129,.1);color:#10b981">
                        <i class="fa-solid fa-check-circle"></i> Hoàn thành: {{ $lenhSxStats->done }}
                    </span>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 table-modern">
                        <thead>
                            <tr style="background:linear-gradient(135deg,#f7941d,#e07b08);color:#fff;">
                                <th
                                    style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none">
                                    Mã lệnh</th>
                                <th
                                    style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none">
                                    Chart</th>
                                <th
                                    style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none">
                                    Nhóm</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none"
                                    class="text-center">Mã HH</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none"
                                    class="text-end">Tổng YRD</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none"
                                    class="text-end">Đã SX</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none"
                                    class="text-end">Tồn kho</th>
                                <th
                                    style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none;min-width:160px">
                                    Tiến độ</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none"
                                    class="text-center">Trạng thái</th>
                                <th
                                    style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .5rem;border:none">
                                    Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lenhSxTracking as $lenh)
                                @php
                                    $statusMap = [
                                        'new' => [
                                            'label' => 'Mới tạo',
                                            'color' => '#6b7280',
                                            'bg' => 'rgba(107,114,128,.1)',
                                        ],
                                        'waiting' => [
                                            'label' => 'Chờ SX',
                                            'color' => '#f59e0b',
                                            'bg' => 'rgba(245,158,11,.1)',
                                        ],
                                        'producing' => [
                                            'label' => 'Đang SX',
                                            'color' => '#3b82f6',
                                            'bg' => 'rgba(59,130,246,.1)',
                                        ],
                                        'done' => [
                                            'label' => 'Hoàn thành',
                                            'color' => '#10b981',
                                            'bg' => 'rgba(16,185,129,.1)',
                                        ],
                                    ];
                                    $s = $statusMap[$lenh->trang_thai] ?? $statusMap['new'];
                                    $pctKho =
                                        $lenh->tong_yrd > 0
                                            ? min(100, round(($lenh->tong_ton_kho / $lenh->tong_yrd) * 100))
                                            : 0;
                                    $pctSx =
                                        $lenh->tong_yrd > 0
                                            ? min(100 - $pctKho, round(($lenh->tong_da_sx / $lenh->tong_yrd) * 100))
                                            : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-light border-0 shadow-sm"
                                                data-bs-toggle="collapse" data-bs-target="#dash-child-{{ $lenh->id }}" 
                                                style="width:24px; height:24px; padding:0; display:flex; align-items:center; justify-content:center; color:#c55f00; transition: transform 0.2s">
                                                <i class="fa-solid fa-chevron-down" style="font-size: .65rem;"></i>
                                            </button>
                                            <a href="{{ route('admin.lenh-san-xuat.show', $lenh->id) }}"
                                                class="fw-bold text-decoration-none" style="color:#c55f00">
                                                <i class="fa-solid fa-clipboard-list me-1" style="font-size:.75rem"></i>{{ $lenh->lenh_so }}
                                            </a>
                                        </div>
                                    </td>
                                    <td><span class="badge" style="background:#f1f5f9;color:#64748b;font-weight:500">{{ $lenh->chart }}</span></td>
                                    <td><span class="badge" style="background:rgba(59,130,246,.1);color:#3b82f6;font-weight:500">{{ $lenh->nhom_hh }}</span></td>
                                    <td class="text-center">
                                        <span class="fw-semibold" style="color:#c55f00">{{ $lenh->active_items }}</span>
                                        <span class="text-muted" style="font-size:.7rem">/{{ $lenh->total_items }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($lenh->tong_yrd, 0) }}</td>
                                    <td class="text-end" style="color:#f59e0b;font-weight:600">{{ number_format($lenh->tong_da_sx, 0) }}</td>
                                    <td class="text-end" style="color:#10b981;font-weight:600">{{ number_format($lenh->tong_ton_kho, 0) }}</td>
                                    <td>
                                        <div class="progress" style="height:18px;border-radius:6px;background:#f1f5f9">
                                            @if ($pctKho > 0)
                                                <div class="progress-bar" style="width:{{ $pctKho }}%;background:#10b981;border-radius:6px 0 0 6px" title="Tồn kho {{ $pctKho }}%">
                                                    @if ($pctKho >= 12) <span style="font-size:.65rem">{{ $pctKho }}%</span> @endif
                                                </div>
                                            @endif
                                            @if ($pctSx > 0)
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:{{ $pctSx }}%;background:#3b82f6" title="Đang SX {{ $pctSx }}%">
                                                    @if ($pctSx >= 12) <span style="font-size:.65rem">{{ $pctSx }}%</span> @endif
                                                </div>
                                            @endif
                                            @if ($pctKho == 0 && $pctSx == 0)
                                                <div class="progress-bar" style="width:100%;background:#f1f5f9;color:#94a3b8;font-size:.65rem">0%</div>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between mt-1" style="font-size:.6rem;color:#94a3b8">
                                            <span><i class="fa-solid fa-warehouse me-1" style="color:#10b981"></i>Kho</span>
                                            <span><i class="fa-solid fa-industry me-1" style="color:#3b82f6"></i>SX</span>
                                            <span class="fw-bold" style="color:#334155">{{ $lenh->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" style="background:{{ $s['bg'] }};color:{{ $s['color'] }};font-weight:600;font-size:.72rem;padding:.35em .7em;border-radius:6px">
                                            {{ $s['label'] }}
                                        </span>
                                    </td>
                                    <td style="font-size:.78rem;color:#64748b">{{ $lenh->created_at->format('d/m/Y') }}</td>
                                </tr>
                                <tr class="collapse" id="dash-child-{{ $lenh->id }}" style="background: #f8fafc;">
                                    <td colspan="10" class="p-3 border-bottom">
                                        <div class="card shadow-none border-0" style="background: transparent;">
                                            <div class="card-body p-0">
                                                <h6 class="fw-bold mb-2 d-flex align-items-center" style="font-size:.85rem; color:#64748b">
                                                    <i class="fa-solid fa-diagram-project text-primary me-2"></i>Chi tiết các lệnh con thuộc <span class="text-dark ms-1">{{ $lenh->lenh_so }}</span>
                                                </h6>
                                                @php
                                                    $activeItemsList = isset($lenh->items) ? $lenh->items->where('da_len_lenh', true) : collect();
                                                @endphp
                                                @if($activeItemsList->count())
                                                    <div class="table-responsive bg-white rounded shadow-sm border" style="border-color:#e2e8f0;">
                                                        <table class="table table-sm align-middle mb-0">
                                                            <thead style="background:#f1f5f9;">
                                                                <tr>
                                                                    <th style="font-size:.75rem; color:#475569; font-weight:600; border-bottom:1px solid #e2e8f0" class="ps-3">Lệnh con</th>
                                                                    <th style="font-size:.75rem; color:#475569; font-weight:600; border-bottom:1px solid #e2e8f0">Mã HH</th>
                                                                    <th style="font-size:.75rem; color:#475569; font-weight:600; border-bottom:1px solid #e2e8f0" class="text-end">Cần SX</th>
                                                                    <th style="font-size:.75rem; color:#475569; font-weight:600; border-bottom:1px solid #e2e8f0" class="text-center">Trạng thái</th>
                                                                    <th style="font-size:.75rem; color:#475569; font-weight:600; border-bottom:1px solid #e2e8f0" class="pe-3">Công đoạn hiện tại</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($activeItemsList as $child)
                                                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                                                        <td class="ps-3 fw-bold text-primary" style="font-size:.8rem;">{{ $child->lenh_child }}</td>
                                                                        <td class="fw-semibold text-dark" style="font-size:.8rem;">{{ $child->ma_hh }}</td>
                                                                        <td class="text-end fw-semibold" style="font-size:.8rem;">{{ number_format($child->sl_can_sx, 2) }}</td>
                                                                        <td class="text-center">
                                                                            <span class="badge" style="background:rgba(16,185,129,.1);color:#10b981"><i class="fa-solid fa-check me-1"></i>Đã lên</span>
                                                                        </td>
                                                                        <td class="pe-3">
                                                                            @php
                                                                                $stageInfo = $stages[$child->cong_doan] ?? ['icon' => 'fa-question', 'color' => 'secondary', 'order' => -1];
                                                                                $currentOrder = $stageInfo['order'];
                                                                            @endphp
                                                                            <div class="d-flex align-items-center gap-1">
                                                                                @foreach ($stages as $stageName => $info)
                                                                                    @php
                                                                                        $isDone = $info['order'] < $currentOrder;
                                                                                        $isCurrent = $stageName === $child->cong_doan;
                                                                                    @endphp
                                                                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm
                                                                                        {{ $isCurrent ? 'bg-' . $info['color'] . ' text-white' : ($isDone ? 'bg-' . $info['color'] . ' text-white opacity-50' : 'bg-light text-muted border border-light') }}"
                                                                                        style="width:24px;height:24px;font-size:.6rem; transition: transform .2s" title="{{ $stageName }}">
                                                                                        <i class="fa-solid {{ $info['icon'] }}"></i>
                                                                                    </span>
                                                                                    @if (!$loop->last)
                                                                                        <i class="fa-solid fa-chevron-right" style="font-size:.45rem;color:#cbd5e1"></i>
                                                                                    @endif
                                                                                @endforeach
                                                                                <span class="badge bg-{{ $stageInfo['color'] }} ms-2 shadow-sm" style="font-size:.68rem">{{ $child->cong_doan }}</span>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="alert alert-light text-muted mb-0 border shadow-sm" style="font-size:.85rem">
                                                        <i class="fa-solid fa-circle-info me-1"></i>Chưa có lệnh con nào được tạo.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-muted text-center py-4">
                                        <i class="fa-solid fa-inbox me-2" style="font-size:1.2rem"></i>Chưa có lệnh sản
                                        xuất nào
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nhomHangByKhachHang = @json($nhomHangByKhachHang);
            const allNhomHangOptions = @json($nhomHangOptions);
            const khachHangSelect = document.getElementById('dashboardKhachHang');
            const nhomHangSelect = document.getElementById('dashboardNhomHang');

            function renderNhomHangOptions() {
                if (!khachHangSelect || !nhomHangSelect) return;

                const selectedCustomer = khachHangSelect.value;
                const selectedNhom = nhomHangSelect.dataset.selected || nhomHangSelect.value;
                const items = selectedCustomer ? (nhomHangByKhachHang[selectedCustomer] || []) : allNhomHangOptions;

                nhomHangSelect.innerHTML = '<option value="">Tất cả nhóm hàng</option>';
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.ma_nhom;
                    option.textContent = item.label;
                    option.selected = item.ma_nhom === selectedNhom;
                    nhomHangSelect.appendChild(option);
                });

                if (selectedNhom && !items.some(item => item.ma_nhom === selectedNhom)) {
                    nhomHangSelect.value = '';
                    nhomHangSelect.dataset.selected = '';
                }
            }

            khachHangSelect?.addEventListener('change', function () {
                nhomHangSelect.dataset.selected = '';
                renderNhomHangOptions();
            });
            renderNhomHangOptions();

            // ── Shared config ──────────────────────────────────────────
            const ORANGE      = '#f7941d';
            const ORANGE_DARK = '#e07b08';
            const PALETTE     = ['#f7941d','#3b82f6','#10b981','#ef4444','#f59e0b','#8b5cf6'];

            const sharedTooltip = {
                backgroundColor: '#1e293b',
                titleColor: '#f8fafc',
                bodyColor: '#cbd5e1',
                padding: 12,
                cornerRadius: 10,
                borderColor: 'rgba(255,255,255,.08)',
                borderWidth: 1,
                displayColors: true,
                boxWidth: 8, boxHeight: 8, boxPadding: 4,
                usePointStyle: true,
            };

            const sharedScales = {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    border: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 10 } },
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 10 } },
                },
            };

            // Plugin: vẽ center-text cho doughnut
            const doughnutCenterPlugin = {
                id: 'centerText',
                afterDraw(chart) {
                    if (chart.config.type !== 'doughnut') return;
                    const { ctx, chartArea: { top, bottom, left, right } } = chart;
                    const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    const cx = (left + right) / 2, cy = (top + bottom) / 2;
                    ctx.save();
                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.font = 'bold 22px Inter, sans-serif';
                    ctx.fillStyle = '#1e293b';
                    ctx.fillText(total, cx, cy - 8);
                    ctx.font = '500 10px Inter, sans-serif';
                    ctx.fillStyle = '#94a3b8';
                    ctx.fillText('Tổng', cx, cy + 12);
                    ctx.restore();
                }
            };
            Chart.register(doughnutCenterPlugin);

            // ── Chart 0: Trạng thái Đơn hàng (Doughnut) ────────────────
            const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
            const dataOrderStatus = @json($chartDataOrderStatus);
            new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: dataOrderStatus.labels,
                    datasets: [{
                        data: dataOrderStatus.data,
                        backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#1e293b'],
                        borderWidth: 0,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'right', labels: { padding: 16, font: { size: 11 }, usePointStyle: true, pointStyleWidth: 8 } },
                        tooltip: { ...sharedTooltip, callbacks: { label: ctx => `  ${ctx.label}: ${ctx.parsed} đơn` } },
                    },
                }
            });

            // ── Chart 1: Sản lượng SX theo thời gian (Line + area) ─────
            const timeCtx = document.getElementById('productionTimeChart').getContext('2d');
            const dataTime = @json($chartDataProductionTime);
            const areaGrad = timeCtx.createLinearGradient(0, 0, 0, 250);
            areaGrad.addColorStop(0, 'rgba(247,148,29,.28)');
            areaGrad.addColorStop(1, 'rgba(247,148,29,.0)');
            new Chart(timeCtx, {
                type: 'line',
                data: {
                    labels: dataTime.labels,
                    datasets: [{
                        label: 'Sản lượng đạt',
                        data: dataTime.data,
                        borderColor: ORANGE,
                        borderWidth: 2.5,
                        backgroundColor: areaGrad,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: ORANGE,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: sharedTooltip },
                    scales: sharedScales,
                }
            });

            // ── Chart 2: Trạng thái lệnh SX (Doughnut) ─────────────────
            const statusCtx = document.getElementById('trackingStatusChart').getContext('2d');
            const dataStatus = @json($chartDataTrackingStatus);
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: dataStatus.labels.map(l => l || 'N/A'),
                    datasets: [{
                        data: dataStatus.data,
                        backgroundColor: PALETTE,
                        borderWidth: 0,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'right', labels: { padding: 16, font: { size: 11 }, usePointStyle: true, pointStyleWidth: 8 } },
                        tooltip: sharedTooltip,
                    },
                }
            });

            // ── Chart 3: Sản lượng theo ca (Bar gradient) ──────────────
            const caCtx = document.getElementById('productionCaChart').getContext('2d');
            const dataCa = @json($chartDataProductionCa);
            const caGrad = caCtx.createLinearGradient(0, 0, 0, 250);
            caGrad.addColorStop(0, ORANGE);
            caGrad.addColorStop(1, 'rgba(247,148,29,.35)');
            new Chart(caCtx, {
                type: 'bar',
                data: {
                    labels: dataCa.labels,
                    datasets: [{
                        label: 'Sản lượng đạt',
                        data: dataCa.data,
                        backgroundColor: caGrad,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.55,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: sharedTooltip },
                    scales: sharedScales,
                }
            });

            // ── Chart 4: Sản lượng theo công đoạn (Bar gradient) ───────
            const stageCtx = document.getElementById('productionStageChart').getContext('2d');
            const dataStage = @json($chartDataProductionStage);
            const stageGrad = stageCtx.createLinearGradient(0, 0, 0, 250);
            stageGrad.addColorStop(0, '#10b981');
            stageGrad.addColorStop(1, 'rgba(16,185,129,.3)');
            new Chart(stageCtx, {
                type: 'bar',
                data: {
                    labels: dataStage.labels,
                    datasets: [{
                        label: 'Sản lượng đạt',
                        data: dataStage.data,
                        backgroundColor: stageGrad,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.55,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: sharedTooltip },
                    scales: sharedScales,
                }
            });
        });
    </script>
@endsection
