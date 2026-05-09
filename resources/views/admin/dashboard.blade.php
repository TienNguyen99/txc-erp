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
            {{-- Chart 0: Trạng thái Đơn hàng (Order.status) --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0">Trạng thái Đơn hàng</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="position: relative; height:250px; width:100%">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 1: Sản lượng SX theo thời gian --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0">Sản lượng sản xuất theo thời gian</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border-0 text-muted" type="button">
                                7 ngày qua <i class="fa-solid fa-chevron-down ms-1"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height:250px; width:100%">
                            <canvas id="productionTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 2: Trạng thái lệnh SX --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0">Trạng thái lệnh sản xuất</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border-0 text-muted" type="button">
                                Theo công đoạn <i class="fa-solid fa-chevron-down ms-1"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="position: relative; height:250px; width:100%">
                            <canvas id="trackingStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 3: Sản lượng theo ca (đơn vị) --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0">Sản lượng sản xuất theo ca</h6>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height:250px; width:100%">
                            <canvas id="productionCaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 4: Sản lượng theo công đoạn --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0">Sản lượng sản xuất theo công đoạn</h6>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height:250px; width:100%">
                            <canvas id="productionStageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- THEO DÕI LỆNH SẢN XUẤT                    --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px">
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
                                        <a href="{{ route('admin.lenh-san-xuat.show', $lenh->id) }}"
                                            class="fw-bold text-decoration-none" style="color:#c55f00">
                                            <i class="fa-solid fa-clipboard-list me-1"
                                                style="font-size:.75rem"></i>{{ $lenh->lenh_so }}
                                        </a>
                                    </td>
                                    <td><span class="badge"
                                            style="background:#f1f5f9;color:#64748b;font-weight:500">{{ $lenh->chart }}</span>
                                    </td>
                                    <td><span class="badge"
                                            style="background:rgba(59,130,246,.1);color:#3b82f6;font-weight:500">{{ $lenh->nhom_hh }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold" style="color:#c55f00">{{ $lenh->active_items }}</span>
                                        <span class="text-muted" style="font-size:.7rem">/{{ $lenh->total_items }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($lenh->tong_yrd, 0) }}</td>
                                    <td class="text-end" style="color:#f59e0b;font-weight:600">
                                        {{ number_format($lenh->tong_da_sx, 0) }}</td>
                                    <td class="text-end" style="color:#10b981;font-weight:600">
                                        {{ number_format($lenh->tong_ton_kho, 0) }}</td>
                                    <td>
                                        <div class="progress" style="height:18px;border-radius:6px;background:#f1f5f9">
                                            @if ($pctKho > 0)
                                                <div class="progress-bar"
                                                    style="width:{{ $pctKho }}%;background:#10b981;border-radius:6px 0 0 6px"
                                                    title="Tồn kho {{ $pctKho }}%">
                                                    @if ($pctKho >= 12)
                                                        <span style="font-size:.65rem">{{ $pctKho }}%</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($pctSx > 0)
                                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                    style="width:{{ $pctSx }}%;background:#3b82f6"
                                                    title="Đang SX {{ $pctSx }}%">
                                                    @if ($pctSx >= 12)
                                                        <span style="font-size:.65rem">{{ $pctSx }}%</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($pctKho == 0 && $pctSx == 0)
                                                <div class="progress-bar"
                                                    style="width:100%;background:#f1f5f9;color:#94a3b8;font-size:.65rem">0%
                                                </div>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between mt-1"
                                            style="font-size:.6rem;color:#94a3b8">
                                            <span><i class="fa-solid fa-warehouse me-1"
                                                    style="color:#10b981"></i>Kho</span>
                                            <span><i class="fa-solid fa-industry me-1" style="color:#3b82f6"></i>SX</span>
                                            <span class="fw-bold" style="color:#334155">{{ $lenh->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge"
                                            style="background:{{ $s['bg'] }};color:{{ $s['color'] }};font-weight:600;font-size:.72rem;padding:.35em .7em;border-radius:6px">
                                            {{ $s['label'] }}
                                        </span>
                                    </td>
                                    <td style="font-size:.78rem;color:#64748b">{{ $lenh->created_at->format('d/m/Y') }}
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
        document.addEventListener('DOMContentLoaded', function() {
            // Chart 0: Trạng thái Đơn hàng (Doughnut)
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
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 16,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} đơn`
                            }
                        }
                    },
                    cutout: '65%'
                }
            });

            // Chart 1: Sản lượng sản xuất theo thời gian (Bar)
            const timeCtx = document.getElementById('productionTimeChart').getContext('2d');
            const dataTime = @json($chartDataProductionTime);
            new Chart(timeCtx, {
                type: 'bar',
                data: {
                    labels: dataTime.labels,
                    datasets: [{
                        label: 'Sản lượng đạt',
                        data: dataTime.data,
                        backgroundColor: '#10b981', // Green color similar to image
                        borderRadius: 2,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Chart 2: Trạng thái lệnh sản xuất (Doughnut)
            const statusCtx = document.getElementById('trackingStatusChart').getContext('2d');
            const dataStatus = @json($chartDataTrackingStatus);
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: dataStatus.labels.map(l => (l || 'N/A')),
                    datasets: [{
                        data: dataStatus.data,
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#f7941d'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });

            // Chart 3: Sản lượng sản xuất theo ca (Bar)
            const caCtx = document.getElementById('productionCaChart').getContext('2d');
            const dataCa = @json($chartDataProductionCa);
            new Chart(caCtx, {
                type: 'bar',
                data: {
                    labels: dataCa.labels,
                    datasets: [{
                        label: 'Sản lượng đạt',
                        data: dataCa.data,
                        backgroundColor: '#3b82f6',
                        borderRadius: 2,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Chart 4: Sản lượng sản xuất theo công đoạn (Bar)
            const stageCtx = document.getElementById('productionStageChart').getContext('2d');
            const dataStage = @json($chartDataProductionStage);
            new Chart(stageCtx, {
                type: 'bar',
                data: {
                    labels: dataStage.labels,
                    datasets: [{
                        label: 'Sản lượng đạt',
                        data: dataStage.data,
                        backgroundColor: '#f59e0b',
                        borderRadius: 2,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
