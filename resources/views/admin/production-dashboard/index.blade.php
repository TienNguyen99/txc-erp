@extends('layouts.app')

@section('css')
    <style>
        .production-dashboard { --sx-blue: #2563eb; --sx-green: #059669; --sx-red: #dc2626; --sx-amber: #d97706; }
        .sx-header { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; }
        .sx-kpi { background:#fff; border:1px solid var(--border); border-radius:14px; padding:1rem; height:100%; }
        .sx-kpi-label { color:var(--text-muted); font-size:.75rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
        .sx-kpi-value { color:var(--text); font-size:1.65rem; font-weight:800; letter-spacing:-.04em; line-height:1.15; margin-top:.4rem; }
        .sx-kpi-note { color:var(--text-muted); font-size:.75rem; margin-top:.3rem; }
        .sx-icon { align-items:center; border-radius:10px; display:flex; height:36px; justify-content:center; width:36px; }
        .sx-section-title { color:var(--text); font-size:.92rem; font-weight:800; margin:0; }
        .sx-section-note { color:var(--text-muted); font-size:.75rem; }
        .sx-table th { color:var(--text-muted); font-size:.68rem; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
        .sx-table td { color:var(--text); font-size:.8rem; vertical-align:middle; }
        .sx-empty { color:var(--text-muted); font-size:.8rem; padding:1.3rem !important; text-align:center; }
        .sx-risk { border-left:3px solid var(--sx-amber); }
        .sx-risk.is-late { border-left-color:var(--sx-red); }
        .sx-badge { border-radius:999px; display:inline-flex; font-size:.7rem; font-weight:700; padding:.25rem .5rem; white-space:nowrap; }
        .sx-badge.pending { background:#fff7ed; color:#c2410c; }
        .sx-badge.approved { background:#ecfdf5; color:#047857; }
        .sx-badge.late { background:#fef2f2; color:#b91c1c; }
        .sx-badge.soon { background:#fffbeb; color:#b45309; }
        .sx-chart { display:flex; gap:.52rem; height:172px; align-items:flex-end; padding-top:1rem; }
        .sx-bar-col { align-items:center; display:flex; flex:1 1 0; flex-direction:column; height:100%; justify-content:flex-end; min-width:0; }
        .sx-bar-value { color:var(--text-muted); font-size:.62rem; font-weight:700; margin-bottom:.25rem; }
        .sx-bar { background:linear-gradient(180deg, #60a5fa, var(--sx-blue)); border-radius:6px 6px 2px 2px; min-height:3px; width:min(28px, 82%); }
        .sx-bar.defect { background:#fca5a5; margin-top:2px; }
        .sx-bar-label { color:var(--text-muted); font-size:.65rem; margin-top:.4rem; overflow:hidden; text-align:center; text-overflow:ellipsis; white-space:nowrap; width:100%; }
        .sx-horizontal { display:grid; gap:.65rem; }
        .sx-horizontal-row { display:grid; gap:.55rem; grid-template-columns:minmax(74px, 110px) 1fr auto; align-items:center; }
        .sx-horizontal-label { color:var(--text); font-size:.75rem; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .sx-horizontal-track { background:#eff4f8; border-radius:999px; height:8px; overflow:hidden; }
        .sx-horizontal-fill { background:linear-gradient(90deg, #fb923c, var(--primary)); border-radius:999px; height:100%; min-width:3px; }
        .sx-horizontal-value { color:var(--text-muted); font-size:.72rem; font-weight:700; }
        .sx-progress { background:#e9eef5; border-radius:999px; height:6px; min-width:80px; overflow:hidden; }
        .sx-progress > span { background:var(--sx-blue); border-radius:999px; display:block; height:100%; }
        .sx-filter { align-items:end; display:flex; flex-wrap:wrap; gap:.65rem; }
        .sx-filter .form-control { min-width:145px; }
        @media (max-width: 767px) {
            .sx-header { flex-direction:column; }
            .sx-filter { width:100%; }
            .sx-filter > div { flex:1 1 135px; }
            .sx-filter .form-control { min-width:0; }
        }
    </style>
@endsection

@section('content')
    @php
        $trendMax = max(1, (float) $outputTrend->max('output'));
        $stageMax = max(1, count($stageOutput['data']) ? max($stageOutput['data']) : 0);
        $shiftMax = max(1, count($shiftOutput['data']) ? max($shiftOutput['data']) : 0);
        $stageBacklogTotal = $stageBacklog['stages']->sum('total');
    @endphp
    <div class="container-fluid px-4 production-dashboard">
        <div class="sx-header mb-4">
            <div>
                <h4 class="page-title mb-1"><i class="fa-solid fa-chart-line me-2"></i>Dashboard Sản xuất</h4>
                <div class="text-muted small">Theo dõi sản lượng, chất lượng, tiến độ lệnh và các việc cần xử lý.</div>
            </div>
            <form method="GET" class="sx-filter card-page py-2 px-3">
                <div>
                    <label class="form-label mb-1 small">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                </div>
                <div>
                    <label class="form-label mb-1 small">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                </div>
                <div>
                    <label class="form-label mb-1 small">Nhóm hàng</label>
                    <input type="text" name="nhom_hang" class="form-control form-control-sm" value="{{ $filters['nhom_hang'] }}" placeholder="VD: F27">
                </div>
                <button class="btn btn-primary btn-sm"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                <a href="{{ route('admin.production-dashboard.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl col-md-4 col-6">
                <div class="sx-kpi">
                    <div class="d-flex justify-content-between">
                        <div class="sx-kpi-label">Sản lượng đạt</div>
                        <div class="sx-icon bg-primary-subtle text-primary"><i class="fa-solid fa-chart-column"></i></div>
                    </div>
                    <div class="sx-kpi-value">{{ number_format($summary['output']) }}</div>
                    <div class="sx-kpi-note">Trong kỳ đã chọn</div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="sx-kpi">
                    <div class="d-flex justify-content-between">
                        <div class="sx-kpi-label">Tỷ lệ lỗi</div>
                        <div class="sx-icon bg-danger-subtle text-danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                    <div class="sx-kpi-value text-danger">{{ number_format($summary['defect_rate'], 2) }}%</div>
                    <div class="sx-kpi-note">{{ number_format($summary['defect']) }} sản phẩm lỗi</div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="sx-kpi">
                    <div class="d-flex justify-content-between">
                        <div class="sx-kpi-label">Tổng lệnh sản xuất</div>
                        <div class="sx-icon bg-info-subtle text-info"><i class="fa-solid fa-gears"></i></div>
                    </div>
                    <div class="sx-kpi-value">{{ number_format($summary['total_production_orders']) }}</div>
                    <div class="sx-kpi-note">{{ number_format($summary['active_items']) }} item đã lên lệnh</div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="sx-kpi">
                    <div class="d-flex justify-content-between">
                        <div class="sx-kpi-label">Đang xử lý</div>
                        <div class="sx-icon bg-warning-subtle text-warning"><i class="fa-solid fa-layer-group"></i></div>
                    </div>
                    <div class="sx-kpi-value">{{ number_format($stageBacklogTotal) }}</div>
                    <div class="sx-kpi-note">Item chưa hoàn tất giao hàng</div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="sx-kpi">
                    <div class="d-flex justify-content-between">
                        <div class="sx-kpi-label">Chờ xử lý</div>
                        <div class="sx-icon bg-success-subtle text-success"><i class="fa-solid fa-clipboard-check"></i></div>
                    </div>
                    <div class="sx-kpi-value">{{ number_format($summary['pending_reports'] + $summary['approved_reports']) }}</div>
                    <div class="sx-kpi-note">{{ $summary['pending_reports'] }} chờ duyệt, {{ $summary['approved_reports'] }} chờ nhập kho</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-7">
                <div class="card-page h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="sx-section-title">Sản lượng 14 ngày gần nhất</h5>
                            <div class="sx-section-note">Giới hạn 14 cột để đọc nhanh trên mọi màn hình</div>
                        </div>
                        <span class="sx-badge approved">Đạt / lỗi</span>
                    </div>
                    <div class="sx-chart">
                        @foreach ($outputTrend as $row)
                            <div class="sx-bar-col" title="{{ $row['date'] }}: đạt {{ number_format($row['output']) }}, lỗi {{ number_format($row['defect']) }}">
                                <div class="sx-bar-value">{{ number_format($row['output']) }}</div>
                                <div class="sx-bar" style="height:{{ max(2, round(($row['output'] / $trendMax) * 118)) }}px"></div>
                                @if ($row['defect'] > 0)
                                    <div class="sx-bar defect" style="height:{{ max(2, round(($row['defect'] / $trendMax) * 118)) }}px"></div>
                                @endif
                                <div class="sx-bar-label">{{ $row['date'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card-page h-100">
                    <h5 class="sx-section-title mb-1">Tồn tại công đoạn</h5>
                    <div class="sx-section-note mb-3">Số item chưa hoàn tất tại từng điểm vận hành</div>
                    <div class="sx-horizontal">
                        @forelse ($stageBacklog['stages']->take(7) as $row)
                            <div class="sx-horizontal-row">
                                <div class="sx-horizontal-label" title="{{ $row['stage'] }}">{{ $row['stage'] }}</div>
                                <div class="sx-horizontal-track"><div class="sx-horizontal-fill" style="width:{{ $stageBacklogTotal ? max(2, round(($row['total'] / $stageBacklogTotal) * 100)) : 0 }}%"></div></div>
                                <div class="sx-horizontal-value">{{ number_format($row['total']) }}</div>
                            </div>
                        @empty
                            <div class="sx-empty">Không có item tồn tại công đoạn.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-6">
                <div class="card-page h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h5 class="sx-section-title">Lệnh sản xuất cần theo dõi</h5>
                            <div class="sx-section-note">Các lệnh chưa hoàn tất, ưu tiên xử lý theo tiến độ</div>
                        </div>
                        <span class="sx-badge {{ $productionOrdersNeedAttention->isNotEmpty() ? 'soon' : 'approved' }}">{{ $productionOrdersNeedAttention->count() }} lệnh</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm sx-table mb-0">
                            <thead><tr><th>Lệnh SX</th><th>Chart</th><th>Item</th><th>Tiến độ</th><th>Tình trạng</th></tr></thead>
                            <tbody>
                                @forelse ($productionOrdersNeedAttention as $lenh)
                                    @php
                                        $statusLabels = ['new' => 'Mới tạo', 'waiting' => 'Chờ sản xuất', 'producing' => 'Đang sản xuất'];
                                    @endphp
                                    <tr class="sx-risk">
                                        <td class="fw-bold">
                                            @can('lenh_sx.view')
                                                <a href="{{ route('admin.lenh-san-xuat.show', $lenh->id) }}">{{ $lenh->lenh_so }}</a>
                                            @else
                                                {{ $lenh->lenh_so }}
                                            @endcan
                                        </td>
                                        <td>{{ $lenh->chart }}</td>
                                        <td>{{ $lenh->active_items }}/{{ $lenh->total_items }}</td>
                                        <td class="fw-bold">{{ $lenh->progress }}%</td>
                                        <td><span class="sx-badge soon">{{ $statusLabels[$lenh->trang_thai] ?? 'Cần theo dõi' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="sx-empty">Không có lệnh sản xuất cần theo dõi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card-page h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h5 class="sx-section-title">Báo cáo cần xử lý</h5>
                            <div class="sx-section-note">Ưu tiên duyệt và tạo phiếu nhập kho</div>
                        </div>
                        <a href="{{ route('admin.production-reports.index') }}" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm sx-table mb-0">
                            <thead><tr><th>Ngày</th><th>Lệnh SX</th><th>Công đoạn</th><th>SL đạt</th><th>Trạng thái</th></tr></thead>
                            <tbody>
                                @forelse ($pendingReports as $report)
                                    <tr>
                                        <td>{{ $report->ngay_sx?->format('d/m') }}</td>
                                        <td class="fw-bold">{{ $report->lenh_sx ?: '-' }}</td>
                                        <td>{{ $report->cong_doan ?: '-' }}</td>
                                        <td>{{ number_format((float) $report->sl_dat) }}</td>
                                        <td><span class="sx-badge {{ $report->status }}">{{ $report->status === 'approved' ? 'Chờ nhập kho' : 'Chờ duyệt' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="sx-empty">Không có báo cáo chờ xử lý.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-6">
                <div class="card-page h-100">
                    <h5 class="sx-section-title mb-1">Sản lượng theo công đoạn</h5>
                    <div class="sx-section-note mb-3">Khối lượng đạt trong phạm vi ngày đã chọn</div>
                    <div class="sx-horizontal">
                        @forelse ($stageOutput['labels'] as $index => $label)
                            <div class="sx-horizontal-row">
                                <div class="sx-horizontal-label" title="{{ $label ?: 'Chưa gán' }}">{{ $label ?: 'Chưa gán' }}</div>
                                <div class="sx-horizontal-track"><div class="sx-horizontal-fill" style="width:{{ max(2, round(($stageOutput['data'][$index] / $stageMax) * 100)) }}%"></div></div>
                                <div class="sx-horizontal-value">{{ number_format($stageOutput['data'][$index]) }}</div>
                            </div>
                        @empty
                            <div class="sx-empty">Chưa có dữ liệu sản lượng.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card-page h-100">
                    <h5 class="sx-section-title mb-1">Sản lượng theo ca</h5>
                    <div class="sx-section-note mb-3">So sánh nhanh hiệu suất giữa các ca</div>
                    <div class="sx-horizontal">
                        @forelse ($shiftOutput['labels'] as $index => $label)
                            <div class="sx-horizontal-row">
                                <div class="sx-horizontal-label">{{ $label }}</div>
                                <div class="sx-horizontal-track"><div class="sx-horizontal-fill" style="width:{{ max(2, round(($shiftOutput['data'][$index] / $shiftMax) * 100)) }}%"></div></div>
                                <div class="sx-horizontal-value">{{ number_format($shiftOutput['data'][$index]) }}</div>
                            </div>
                        @empty
                            <div class="sx-empty">Chưa có dữ liệu theo ca.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-5">
                <div class="card-page h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h5 class="sx-section-title">Thiếu nguyên vật liệu</h5>
                            <div class="sx-section-note">Theo BOM của các item đã lên lệnh</div>
                        </div>
                        <span class="sx-badge {{ $materialShortages->isNotEmpty() ? 'late' : 'approved' }}">{{ $materialShortages->count() }} mã</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm sx-table mb-0">
                            <thead><tr><th>Mã NVL</th><th class="text-end">Cần</th><th class="text-end">Tồn</th><th class="text-end">Thiếu</th></tr></thead>
                            <tbody>
                                @forelse ($materialShortages->take(8) as $row)
                                    <tr><td class="fw-bold">{{ $row['ma_hh'] }}</td><td class="text-end">{{ number_format($row['required']) }}</td><td class="text-end">{{ number_format($row['on_hand']) }}</td><td class="text-end text-danger fw-bold">{{ number_format($row['shortage']) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="sx-empty">Không phát hiện thiếu NVL theo BOM.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card-page h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h5 class="sx-section-title">Tiến độ lệnh sản xuất gần nhất</h5>
                            <div class="sx-section-note">Hiển thị tối đa 20 lệnh để giữ tốc độ tải ổn định</div>
                        </div>
                        @can('lenh_sx.view')
                            <a href="{{ route('admin.lenh-san-xuat.index') }}" class="btn btn-outline-primary btn-sm">Danh sách lệnh</a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm sx-table mb-0">
                            <thead><tr><th>Lệnh</th><th>Chart</th><th>Item</th><th style="min-width:130px">Tiến độ</th><th class="text-end">Đã SX</th></tr></thead>
                            <tbody>
                                @forelse ($lenhSxTracking->take(8) as $lenh)
                                    <tr>
                                        <td class="fw-bold">
                                            @can('lenh_sx.view')
                                                <a href="{{ route('admin.lenh-san-xuat.show', $lenh->id) }}">{{ $lenh->lenh_so }}</a>
                                            @else
                                                {{ $lenh->lenh_so }}
                                            @endcan
                                        </td>
                                        <td>{{ $lenh->chart }}</td>
                                        <td>{{ $lenh->active_items }}/{{ $lenh->total_items }}</td>
                                        <td><div class="d-flex gap-2 align-items-center"><div class="sx-progress flex-grow-1"><span style="width:{{ $lenh->progress }}%"></span></div><small class="fw-bold">{{ $lenh->progress }}%</small></div></td>
                                        <td class="text-end">{{ number_format($lenh->tong_da_sx) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="sx-empty">Chưa có lệnh sản xuất.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
