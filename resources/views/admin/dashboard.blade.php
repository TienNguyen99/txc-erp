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

        {{-- Analytics cockpit --}}
        @php
            $financeSummary = $opsDashboard['finance']['summary'];
            $trend = $financeSummary['trend'] ?? [];
            $trendClass = fn($value) => $value === null ? 'text-muted' : ($value >= 0 ? 'text-success' : 'text-danger');
            $trendIcon = fn($value) => $value === null ? 'fa-minus' : ($value >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
            $trendText = fn($value) => $value === null ? 'N/A' : (($value > 0 ? '+' : '') . $value . '%');
            $topProduct = $opsDashboard['finance']['by_product']->first();
        @endphp
        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="card focus-card revenue h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Giá trị đơn hàng</div>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                            <div class="fs-4 fw-bold">{{ number_format($financeSummary['order_revenue'], 0) }}</div>
                            <div class="{{ $trendClass($trend['order_revenue_pct'] ?? null) }} fw-bold small">
                                <i class="fa-solid {{ $trendIcon($trend['order_revenue_pct'] ?? null) }} me-1"></i>{{ $trendText($trend['order_revenue_pct'] ?? null) }}
                            </div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.72rem">So với kỳ trước cùng độ dài</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card focus-card revenue h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Đã HĐ / đã giao</div>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                            <div class="fs-4 fw-bold text-success">{{ number_format($financeSummary['invoiced_revenue'], 0) }}</div>
                            <div class="{{ $trendClass($trend['invoiced_revenue_pct'] ?? null) }} fw-bold small">
                                <i class="fa-solid {{ $trendIcon($trend['invoiced_revenue_pct'] ?? null) }} me-1"></i>{{ $trendText($trend['invoiced_revenue_pct'] ?? null) }}
                            </div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.72rem">Tỷ lệ {{ $financeSummary['invoice_rate'] ?? 0 }}%</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card focus-card risk h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Lot rủi ro giao hàng</div>
                        <div class="fs-4 fw-bold mt-2">{{ $reportDashboard['lot_risk_summary']['total'] }}</div>
                        <div class="d-flex gap-2 mt-1 small">
                            <span class="risk-late">Trễ {{ $reportDashboard['lot_risk_summary']['late'] }}</span>
                            <span class="risk-due">Hôm nay {{ $reportDashboard['lot_risk_summary']['due_today'] }}</span>
                            <span class="text-muted">7 ngày {{ $reportDashboard['lot_risk_summary']['next_7_days'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card focus-card product h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Mặt hàng dẫn đầu</div>
                        <div class="fw-bold mt-2" style="font-size:1.05rem">{{ $topProduct['ma_hh'] ?? 'N/A' }}</div>
                        <div class="text-primary fw-bold">{{ number_format($topProduct['revenue'] ?? 0, 0) }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $topProduct ? \Illuminate\Support\Str::limit($topProduct['ten_hh'], 38) : 'Không có dữ liệu' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-6">
                <div class="card focus-card revenue h-100">
                    <div class="card-body">
                        <div class="focus-title">Xu hướng doanh thu</div>
                        <div class="text-muted small mb-3">Giá trị đơn hàng so với đã HĐ/đã giao theo ngày</div>
                        <div style="position:relative;height:260px;width:100%">
                            <canvas id="financeRevenueTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card focus-card revenue h-100">
                    <div class="card-body">
                        <div class="focus-title">Top khách hàng</div>
                        <div class="text-muted small mb-3">Đã HĐ/giao và còn lại trên giá trị đơn</div>
                        <div style="position:relative;height:260px;width:100%">
                            <canvas id="customerRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card focus-card product h-100">
                    <div class="card-body">
                        <div class="focus-title">Top mặt hàng theo doanh thu</div>
                        <div class="text-muted small mb-3">Tự đổi theo khách hàng/nhóm hàng đang lọc</div>
                        <div style="position:relative;height:260px;width:100%">
                            <canvas id="productRevenueChart"></canvas>
                        </div>
                    </div>
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

            const financeCharts = @json($opsDashboard['finance']['charts']);
            const financeTrendEl = document.getElementById('financeRevenueTrendChart');
            if (financeTrendEl) {
                const financeTrendCtx = financeTrendEl.getContext('2d');
                const financeGrad = financeTrendCtx.createLinearGradient(0, 0, 0, 260);
                financeGrad.addColorStop(0, 'rgba(16,185,129,.22)');
                financeGrad.addColorStop(1, 'rgba(16,185,129,0)');
                new Chart(financeTrendCtx, {
                    type: 'line',
                    data: {
                        labels: financeCharts.revenue_trend.labels,
                        datasets: [
                            {
                                label: 'Giá trị đơn',
                                data: financeCharts.revenue_trend.order_revenue,
                                borderColor: '#f7941d',
                                backgroundColor: 'rgba(247,148,29,.08)',
                                borderWidth: 2.5,
                                tension: .35,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                            },
                            {
                                label: 'Đã HĐ/giao',
                                data: financeCharts.revenue_trend.invoiced_revenue,
                                borderColor: '#10b981',
                                backgroundColor: financeGrad,
                                borderWidth: 2.5,
                                fill: true,
                                tension: .35,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', align: 'end', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } } },
                            tooltip: sharedTooltip,
                        },
                        scales: sharedScales,
                    },
                });
            }

            const productRevenueEl = document.getElementById('productRevenueChart');
            if (productRevenueEl) {
                const productCtx = productRevenueEl.getContext('2d');
                new Chart(productCtx, {
                    type: 'bar',
                    data: {
                        labels: financeCharts.product_revenue.labels,
                        datasets: [{
                            label: 'Doanh thu',
                            data: financeCharts.product_revenue.data,
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'],
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: .62,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: sharedTooltip },
                        scales: {
                            x: sharedScales.y,
                            y: { ...sharedScales.x, ticks: { color: '#475569', font: { size: 10 } } },
                        },
                    },
                });
            }

            const customerRevenueEl = document.getElementById('customerRevenueChart');
            if (customerRevenueEl) {
                const customerCtx = customerRevenueEl.getContext('2d');
                new Chart(customerCtx, {
                    type: 'bar',
                    data: {
                        labels: financeCharts.customer_revenue.labels,
                        datasets: [
                            {
                                label: 'Đã HĐ/giao',
                                data: financeCharts.customer_revenue.invoiced_revenue,
                                backgroundColor: '#10b981',
                                borderRadius: 8,
                                borderSkipped: false,
                                barPercentage: .62,
                            },
                            {
                                label: 'Còn lại',
                                data: financeCharts.customer_revenue.uninvoiced_revenue,
                                backgroundColor: '#f59e0b',
                                borderRadius: 8,
                                borderSkipped: false,
                                barPercentage: .62,
                            },
                        ],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', align: 'end', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } } },
                            tooltip: {
                                ...sharedTooltip,
                                callbacks: {
                                    afterBody(items) {
                                        const index = items[0].dataIndex;
                                        const total = financeCharts.customer_revenue.order_revenue[index] || 0;
                                        return `Tổng đơn: ${Number(total).toLocaleString('en-US')}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: { ...sharedScales.y, stacked: true },
                            y: { ...sharedScales.x, stacked: true, ticks: { color: '#475569', font: { size: 10 } } },
                        },
                    },
                });
            }


        });
    </script>
@endsection
