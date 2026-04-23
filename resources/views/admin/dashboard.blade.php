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
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
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
            background: linear-gradient(135deg, #8b5cf6, #d946ef);
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
            background: linear-gradient(135deg, rgba(99, 102, 241, .05) 0%, rgba(139, 92, 246, .08) 100%);
            border: 1px solid rgba(99, 102, 241, .15);
            border-radius: var(--radius);
            padding: 1.25rem;
        }

        .filter-card .filter-select {
            border-radius: 12px;
            border: 2px solid rgba(99, 102, 241, .2);
            padding: .6rem 1rem;
            font-size: .85rem;
            font-weight: 500;
            transition: all .2s ease;
            background: #fff;
        }

        .filter-card .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .12);
        }

        .lenh-sx-table th {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
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
            background: rgba(99, 102, 241, .04);
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
            background: rgba(139, 92, 246, .1);
            color: #8b5cf6;
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
            color: #8b5cf6;
        }

        .qty-cell.text-nk {
            color: #10b981;
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
                <p class="text-muted mb-0" style="font-size:.85rem">Tổng quan hệ thống quản lý</p>
            </div>
            <span class="badge" style="background:#f1f5f9;color:var(--text);font-size:.8rem;padding:.5em 1em;">
                <i class="fa-regular fa-calendar me-1"></i>{{ now()->format('d/m/Y H:i') }}
            </span>
        </div>

        {{-- Stat Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-1">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                        </div>
                        <div class="stat-number">{{ number_format($stats['users']) }}</div>
                        <div class="stat-label">Users</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-2">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon"><i class="fa-solid fa-file-invoice"></i></div>
                        </div>
                        <div class="stat-number">{{ number_format($stats['orders']) }}</div>
                        <div class="stat-label">Tổng Đơn hàng</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="stat-card bg-grad-6">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                    </div>
                    <div class="stat-number">{{ number_format($stats['total_revenue'], 0, ',', '.') }} ₫</div>
                    <div class="stat-label">Tổng Doanh thu (VND dự kiến)</div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="stat-card bg-grad-7">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                    </div>
                    <div class="stat-number">{{ number_format($stats['shipped_revenue'], 0, ',', '.') }} ₫</div>
                    <div class="stat-label">Doanh thu đã Giao (VND thực tế)</div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-lg-4 col-sm-6">
                <a href="{{ route('admin.order-tracking.index') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon"><i class="fa-solid fa-truck-fast"></i></div>
                        </div>
                        <div class="stat-number">{{ number_format($stats['order_tracking']) }}</div>
                        <div class="stat-label">Order Tracking</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-lg-4 col-sm-6">
                <a href="{{ route('admin.production-reports.index') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon"><i class="fa-solid fa-industry"></i></div>
                        </div>
                        <div class="stat-number">{{ number_format($stats['production_reports']) }}</div>
                        <div class="stat-label">Báo cáo SX</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-lg-4 col-sm-6">
                <a href="{{ route('admin.warehouse-transactions.index') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-5">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon"><i class="fa-solid fa-warehouse"></i></div>
                        </div>
                        <div class="stat-number">{{ number_format($stats['warehouse_transactions']) }}</div>
                        <div class="stat-label">Giao dịch kho</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Dashbaord Charts --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card-page h-100">
                    <h6 class="section-title mb-3">
                        <i class="fa-solid fa-chart-pie"></i>Trạng thái Đơn hàng
                    </h6>
                    <div style="position: relative; height:250px; width:100%">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-page h-100">
                    <h6 class="section-title mb-3">
                        <i class="fa-solid fa-chart-pie"></i>Tỷ lệ Giao hàng (SP)
                    </h6>
                    <div style="position: relative; height:250px; width:100%">
                        <canvas id="qtyStatusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-page h-100">
                    <h6 class="section-title mb-3">
                        <i class="fa-solid fa-chart-column"></i>Sản lượng May
                    </h6>
                    <div style="position: relative; height:250px; width:100%">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lệnh Sản Xuất Filter --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card-page">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="section-title mb-0">
                            <i class="fa-solid fa-clipboard-list"></i>Theo dõi Lệnh Sản Xuất
                        </h6>
                        <a href="{{ route('admin.lenh-san-xuat.index') }}" class="text-decoration-none"
                            style="font-size:.8rem;font-weight:500;color:var(--primary)">
                            Quản lý LSX <i class="fa-solid fa-arrow-right ms-1" style="font-size:.7rem"></i>
                        </a>
                    </div>

                    {{-- Filter --}}
                    <div class="filter-card mb-3">
                        <form method="GET" action="{{ route('admin.dashboard') }}" id="lenhSxFilterForm">
                            <div class="row align-items-end g-2">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold" style="font-size:.8rem;color:var(--text)">
                                        <i class="fa-solid fa-filter me-1 text-primary"></i>Chọn Lệnh Sản Xuất
                                    </label>
                                    <select name="lenh_sx_id" class="form-select filter-select" id="lenhSxSelect">
                                        <option value="">-- Chọn lệnh SX --</option>
                                        @foreach($lenhSxList as $lenh)
                                            <option value="{{ $lenh->id }}" {{ $selectedLenhId == $lenh->id ? 'selected' : '' }}>
                                                {{ $lenh->lenh_so }} — Chart: {{ $lenh->chart }} ({{ $lenh->nhom_hh }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary"
                                        style="border-radius:12px;padding:.6rem 1.2rem;font-size:.85rem;font-weight:600">
                                        <i class="fa-solid fa-search me-1"></i>Xem
                                    </button>
                                </div>
                                @if($selectedLenhId)
                                    <div class="col-auto">
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary"
                                            style="border-radius:12px;padding:.6rem 1.2rem;font-size:.85rem;font-weight:600">
                                            <i class="fa-solid fa-times me-1"></i>Bỏ lọc
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Kết quả --}}
                    @if($selectedLenh)
                        {{-- Summary pills --}}
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="summary-pill" style="background:rgba(99,102,241,.1);color:#6366f1">
                                <i class="fa-solid fa-barcode"></i>
                                {{ $lenhSxItems->count() }} mã hàng
                            </span>
                            <span class="summary-pill det">
                                <i class="fa-solid fa-industry"></i>
                                Dệt: {{ number_format($lenhSxItems->sum('sl_det'), 2) }}
                            </span>
                            <span class="summary-pill dh">
                                <i class="fa-solid fa-shapes"></i>
                                Định hình: {{ number_format($lenhSxItems->sum('sl_dinh_hinh'), 2) }}
                            </span>
                            <span class="summary-pill nk">
                                <i class="fa-solid fa-warehouse"></i>
                                Nhập kho: {{ number_format($lenhSxItems->sum('sl_nhap_kho'), 2) }}
                            </span>
                        </div>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover lenh-sx-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:40px">#</th>
                                        <th>Mã lệnh con</th>
                                        <th>Mã HH</th>
                                        <th>Tên HH</th>
                                        <th>Màu</th>
                                        <th class="text-end">Tổng YRD</th>
                                        <th class="text-end">SL cần SX</th>
                                        <th class="text-end">
                                            <i class="fa-solid fa-industry me-1"></i>Dệt
                                        </th>
                                        <th class="text-end">
                                            <i class="fa-solid fa-shapes me-1"></i>Định hình
                                        </th>
                                        <th class="text-end">
                                            <i class="fa-solid fa-warehouse me-1"></i>Nhập kho
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lenhSxItems as $idx => $item)
                                        <tr>
                                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <span class="badge"
                                                    style="background:#eef2ff;color:var(--primary);font-size:.78rem">
                                                    {{ $item->lenh_child }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold">{{ $item->ma_hh }}</td>
                                            <td>{{ $item->ten_hh ?: '—' }}</td>
                                            <td>
                                                @if($item->mau)
                                                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.75rem">
                                                        {{ $item->mau }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold">{{ number_format($item->tong_yrd, 2) }}</td>
                                            <td class="text-end">{{ number_format($item->sl_can_sx, 2) }}</td>
                                            <td class="text-end qty-cell text-det">
                                                {{ number_format($item->sl_det, 2) }}
                                            </td>
                                            <td class="text-end qty-cell text-dh">
                                                {{ number_format($item->sl_dinh_hinh, 2) }}
                                            </td>
                                            <td class="text-end qty-cell text-nk">
                                                {{ number_format($item->sl_nhap_kho, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-muted text-center py-4">
                                                <i class="fa-regular fa-folder-open me-1"></i>
                                                Chưa có mã hàng nào đã lên lệnh SX trong lệnh này
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($lenhSxItems->count() > 0)
                                    <tfoot>
                                        <tr style="background:#f8fafc;font-weight:700;font-size:.82rem">
                                            <td colspan="5" class="text-end">Tổng cộng</td>
                                            <td class="text-end">{{ number_format($lenhSxItems->sum('tong_yrd'), 2) }}</td>
                                            <td class="text-end">{{ number_format($lenhSxItems->sum('sl_can_sx'), 2) }}</td>
                                            <td class="text-end qty-cell text-det">
                                                {{ number_format($lenhSxItems->sum('sl_det'), 2) }}</td>
                                            <td class="text-end qty-cell text-dh">
                                                {{ number_format($lenhSxItems->sum('sl_dinh_hinh'), 2) }}</td>
                                            <td class="text-end qty-cell text-nk">
                                                {{ number_format($lenhSxItems->sum('sl_nhap_kho'), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa-solid fa-hand-pointer text-muted mb-2" style="font-size:2rem;opacity:.4"></i>
                            <p class="text-muted mb-0" style="font-size:.85rem">
                                Chọn một lệnh sản xuất ở trên để xem chi tiết mã hàng và tiến độ sản xuất
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent data tables --}}
        <div class="row g-3">

            {{-- Recent Orders --}}
            <div class="col-lg-6">
                <div class="card-page">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="section-title mb-0">
                            <i class="fa-solid fa-file-invoice"></i>Đơn hàng gần đây
                        </h6>
                        <a href="{{ route('admin.orders.index') }}" class="text-decoration-none"
                            style="font-size:.8rem;font-weight:500;color:var(--primary)">
                            Xem tất cả <i class="fa-solid fa-arrow-right ms-1" style="font-size:.7rem"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Job No</th>
                                    <th>Fty PO</th>
                                    <th>Màu</th>
                                    <th>SL</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $o)
                                    <tr>
                                        <td class="fw-semibold">{{ $o->job_no }}</td>
                                        <td>{{ $o->fty_po }}</td>
                                        <td>{{ $o->color }}</td>
                                        <td>{{ number_format($o->qty, 2) }}</td>
                                        <td>
                                            @php $colors = ['pending' => 'warning', 'in_production' => 'info', 'done' => 'success', 'shipped' => 'primary']; @endphp
                                            <span
                                                class="badge bg-{{ $colors[$o->status] ?? 'secondary' }}">{{ $o->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center py-4">
                                            <i class="fa-regular fa-folder-open me-1"></i>Chưa có dữ liệu
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Production Reports --}}
            <div class="col-lg-6">
                <div class="card-page">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="section-title mb-0">
                            <i class="fa-solid fa-industry"></i>Báo cáo SX gần đây
                        </h6>
                        <a href="{{ route('admin.production-reports.index') }}" class="text-decoration-none"
                            style="font-size:.8rem;font-weight:500;color:var(--primary)">
                            Xem tất cả <i class="fa-solid fa-arrow-right ms-1" style="font-size:.7rem"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Ngày SX</th>
                                    <th>Ca</th>
                                    <th>Mã NV</th>
                                    <th>Lệnh SX</th>
                                    <th>SL Đạt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProduction as $p)
                                    <tr>
                                        <td>{{ $p->ngay_sx->format('d/m/Y') }}</td>
                                        <td><span class="badge" style="background:#eef2ff;color:var(--primary)">Ca
                                                {{ $p->ca }}</span></td>
                                        <td>{{ $p->ma_nv }}</td>
                                        <td class="fw-semibold">{{ $p->lenh_sx }}</td>
                                        <td>{{ number_format($p->sl_dat, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center py-4">
                                            <i class="fa-regular fa-folder-open me-1"></i>Chưa có dữ liệu
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Warehouse --}}
            <div class="col-12">
                <div class="card-page">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="section-title mb-0">
                            <i class="fa-solid fa-warehouse"></i>Giao dịch kho gần đây
                        </h6>
                        <a href="{{ route('admin.warehouse-transactions.index') }}" class="text-decoration-none"
                            style="font-size:.8rem;font-weight:500;color:var(--primary)">
                            Xem tất cả <i class="fa-solid fa-arrow-right ms-1" style="font-size:.7rem"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Loại</th>
                                    <th>Size</th>
                                    <th>Màu</th>
                                    <th>Số lượng</th>
                                    <th>Mã NV</th>
                                    <th>Lệnh SX</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentWarehouse as $w)
                                    <tr>
                                        <td>{{ $w->ngay->format('d/m/Y') }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $w->cong_doan == 'NHAPKHO' ? 'success' : 'danger' }}">{{ $w->cong_doan }}</span>
                                        </td>
                                        <td>{{ $w->size }}</td>
                                        <td>{{ $w->mau }}</td>
                                        <td class="fw-semibold">{{ number_format($w->so_luong, 2) }}</td>
                                        <td>{{ $w->ma_nv }}</td>
                                        <td>{{ $w->lenh_sx }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted text-center py-4">
                                            <i class="fa-regular fa-folder-open me-1"></i>Chưa có dữ liệu
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Doughnut Chart for Order Status
            const orderCtx = document.getElementById('orderStatusChart').getContext('2d');
            const chartDataOrder = @json($chartDataOrder);

            new Chart(orderCtx, {
                type: 'doughnut',
                data: {
                    labels: chartDataOrder.labels.map(l => l.toUpperCase()),
                    datasets: [{
                        data: chartDataOrder.data,
                        backgroundColor: [
                            '#6366f1', // primary
                            '#10b981', // success
                            '#f59e0b', // warning
                            '#ef4444', // danger
                            '#8b5cf6', // purple
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 20, font: { size: 11, family: "'Inter', sans-serif" } }
                        }
                    },
                    cutout: '70%'
                }
            });
            // Doughnut Chart for QTY Status (Shipped vs Remaining)
            const qtyCtx = document.getElementById('qtyStatusChart').getContext('2d');
            const chartDataQty = @json($chartDataQty);

            new Chart(qtyCtx, {
                type: 'doughnut',
                data: {
                    labels: chartDataQty.labels,
                    datasets: [{
                        data: chartDataQty.data,
                        backgroundColor: [
                            '#10b981', // success (Shipped)
                            '#cbd5e1', // slate-300 (Remaining)
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 20, font: { size: 11, family: "'Inter', sans-serif" } }
                        }
                    },
                    cutout: '70%'
                }
            });

            // Bar Chart for Production
            const prodCtx = document.getElementById('productionChart').getContext('2d');
            const chartDataProd = @json($chartDataProduction);

            new Chart(prodCtx, {
                type: 'bar',
                data: {
                    labels: chartDataProd.labels,
                    datasets: [{
                        label: 'Sản lượng đạt',
                        data: chartDataProd.data,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });
        });
    </script>
@endsection