@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-truck-fast me-2"></i>Order Tracking — Dashboard</h4>
            <a href="{{ route('admin.order-tracking.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm Tracking
            </a>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')

            {{-- ═══ BỘ LỌC ═══ --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label mb-0" style="font-size:.8rem">PL Number <small class="text-muted">(chọn
                            nhiều)</small></label>
                    <select id="plNumberSelect" name="pl_number[]" multiple placeholder="Tìm & chọn PL...">
                        @foreach ($plNumbers as $pl)
                            <option value="{{ $pl }}"
                                {{ in_array($pl, (array) request('pl_number', [])) ? 'selected' : '' }}>{{ $pl }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0" style="font-size:.8rem">Chart <small class="text-muted">(chọn
                            nhiều)</small></label>
                    <select id="chartSelect" name="chart[]" multiple placeholder="Tìm & chọn Chart...">
                        @foreach ($charts as $c)
                            <option value="{{ $c }}"
                                {{ in_array($c, (array) request('chart', [])) ? 'selected' : '' }}>
                                {{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0" style="font-size:.8rem">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm PL / Mã HH / Màu..." value="{{ request('search') }}">
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Lọc</button>
                    <a href="{{ route('admin.order-tracking.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                        <i class="fa-solid fa-rotate-left me-1"></i>Reset
                    </a>
                </div>
            </form>

            {{-- ═══ DASHBOARD CARDS ═══ --}}
            @if ($hasFilter)
                {{-- Nút tạo Order Tracking Number --}}
                <div class="card border-primary mb-3">
                    <div class="card-body py-2 d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fa-solid fa-hashtag text-primary me-1"></i>
                            <strong>PL đã chọn:</strong>
                            @foreach ((array) request('pl_number', []) as $pl)
                                <span class="badge bg-primary ms-1">{{ $pl }}</span>
                            @endforeach
                            @foreach ((array) request('chart', []) as $c)
                                <span class="badge bg-secondary ms-1">Chart: {{ $c }}</span>
                            @endforeach
                        </div>
                        <form method="POST" action="{{ route('admin.order-tracking.create-batch') }}" class="d-inline">
                            @csrf
                            @foreach ((array) request('pl_number', []) as $pl)
                                <input type="hidden" name="pl_numbers[]" value="{{ $pl }}">
                            @endforeach
                            <button type="submit" class="btn btn-primary btn-sm"
                                onclick="return confirm('Tạo Order Tracking Number mới cho các PL đã chọn?')">
                                <i class="fa-solid fa-layer-group me-1"></i>Tạo Order Tracking Number
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- ═══ DANH SÁCH ORDER TRACKING NUMBERS ═══ --}}
            @if ($trackingNumbers->count())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center"
                        data-bs-toggle="collapse" data-bs-target="#collapseOTList" role="button">
                        <span>
                            <i class="fa-solid fa-layer-group me-1"></i>
                            <strong>Danh sách Order Tracking</strong>
                            <span class="badge bg-light text-dark ms-2">{{ $trackingNumbers->count() }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="collapse show" id="collapseOTList">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Order Tracking Number</th>
                                            <th class="text-center">Số tracking</th>
                                            <th>Ngày tạo</th>
                                            <th class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($trackingNumbers as $tn)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('admin.order-tracking.lot', $tn->tracking_number) }}"
                                                        class="fw-bold text-decoration-none">
                                                        <i
                                                            class="fa-solid fa-hashtag text-primary me-1"></i>{{ $tn->tracking_number }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $tn->total_items }} items</span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($tn->created_at)->format('d/m/Y H:i') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.order-tracking.lot', $tn->tracking_number) }}"
                                                        class="btn btn-outline-primary btn-xs">
                                                        <i class="fa-solid fa-eye me-1"></i>Xem
                                                    </a>
                                                    @can('tracking.export')
                                                    <a href="{{ route('admin.order-tracking.export-invoice', $tn->tracking_number) }}"
                                                        class="btn btn-sm btn-outline-success btn-xs" title="Hóa Đơn GTGT">
                                                        <i class="fa-solid fa-file-invoice-dollar me-1"></i>Xuất VAT
                                                    </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($summary->count())
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-muted" style="font-size:.75rem">Tổng Mã HH</div>
                                <h3 class="fw-bold text-primary mb-0">{{ $stats->total_mahh }}</h3>
                                <small class="text-muted">mã hàng hóa</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-muted" style="font-size:.75rem">Cần giao / Tồn kho</div>
                                <h3 class="fw-bold mb-0">
                                    <span class="text-dark">{{ number_format($stats->tong_can_giao, 0) }}</span>
                                    <span class="text-muted mx-1">/</span>
                                    <span class="text-success">{{ number_format($stats->tong_ton_kho, 0) }}</span>
                                </h3>
                                <small class="text-muted">YRD</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                            <div class="card-body text-center">
                                <div class="text-muted" style="font-size:.75rem">Đang sản xuất</div>
                                <h3 class="fw-bold text-info mb-0">{{ number_format($stats->tong_dang_sx, 0) }}</h3>
                                <small class="text-muted">{{ $stats->dang_sx }} mã đang SX</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div
                            class="card border-0 shadow-sm h-100 {{ $stats->tong_thieu > 0 ? 'border-start border-danger border-3' : 'border-start border-success border-3' }}">
                            <div class="card-body text-center">
                                @if ($stats->tong_thieu > 0)
                                    <div class="text-muted" style="font-size:.75rem">Còn thiếu</div>
                                    <h3 class="fw-bold text-danger mb-0">{{ number_format($stats->tong_thieu, 0) }}</h3>
                                    <small class="text-danger">{{ $stats->thieu_hang }} mã thiếu hàng</small>
                                @else
                                    <div class="text-muted" style="font-size:.75rem">Trạng thái</div>
                                    <h3 class="fw-bold text-success mb-0"><i class="fa-solid fa-check-circle"></i></h3>
                                    <small class="text-success">Đủ hàng tất cả</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ BẢNG TIẾN ĐỘ TỔNG HỢP THEO MÃ HH ═══ --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="fa-solid fa-chart-bar me-1"></i>Tiến độ sản xuất theo Mã HH
                        @if (request('pl_number'))
                            — PL: <span class="text-dark">{{ implode(', ', (array) request('pl_number')) }}</span>
                        @endif
                        @if (request('chart'))
                            — Chart: <span class="text-dark">{{ implode(', ', (array) request('chart')) }}</span>
                        @endif
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã HH</th>
                                    <th class="text-center">Số PO</th>
                                    <th class="text-end">Cần giao</th>
                                    <th class="text-end">Đang SX</th>
                                    <th class="text-end">Tồn kho</th>
                                    <th class="text-end">Thiếu / Dư</th>
                                    <th style="min-width:200px">Tiến độ</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary->sortByDesc('thieu') as $row)
                                    @php
                                        $diff = $row->ton_kho - $row->tong_qty;
                                        $pctKho =
                                            $row->tong_qty > 0
                                                ? min(100, round(($row->ton_kho / $row->tong_qty) * 100))
                                                : 0;
                                        $pctSx =
                                            $row->tong_qty > 0
                                                ? min(
                                                    100 - $pctKho,
                                                    round(($row->sl_production / $row->tong_qty) * 100),
                                                )
                                                : 0;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $row->ma_hh ?: '—' }}</td>
                                        <td class="text-center">{{ $row->so_don }}</td>
                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                        <td
                                            class="text-end {{ $row->sl_production > 0 ? 'text-info fw-semibold' : 'text-muted' }}">
                                            {{ number_format($row->sl_production, 2) }}
                                        </td>
                                        <td
                                            class="text-end {{ $row->ton_kho > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                            {{ number_format($row->ton_kho, 2) }}
                                        </td>
                                        <td class="text-end fw-bold {{ $diff >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                        </td>
                                        <td>
                                            <div class="progress" style="height:20px">
                                                @if ($pctKho > 0)
                                                    <div class="progress-bar bg-success"
                                                        style="width:{{ $pctKho }}%"
                                                        title="Tồn kho: {{ number_format($row->ton_kho, 0) }}">
                                                        @if ($pctKho >= 15)
                                                            {{ $pctKho }}%
                                                        @endif
                                                    </div>
                                                @endif
                                                @if ($pctSx > 0)
                                                    <div class="progress-bar bg-info progress-bar-striped progress-bar-animated"
                                                        style="width:{{ $pctSx }}%"
                                                        title="Đang SX: {{ number_format($row->sl_production, 0) }}">
                                                        @if ($pctSx >= 15)
                                                            {{ $pctSx }}%
                                                        @endif
                                                    </div>
                                                @endif
                                                @if ($pctKho == 0 && $pctSx == 0)
                                                    <div class="progress-bar bg-light text-dark" style="width:100%">Chưa
                                                        SX</div>
                                                @endif
                                            </div>
                                            <div class="d-flex justify-content-between"
                                                style="font-size:.65rem;margin-top:2px">
                                                <span class="text-success"><i
                                                        class="fa-solid fa-warehouse me-1"></i>Kho</span>
                                                <span class="text-info"><i class="fa-solid fa-industry me-1"></i>SX</span>
                                                <span>{{ $row->total_progress }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($row->du_hang)
                                                <span class="badge bg-success"><i
                                                        class="fa-solid fa-check-circle me-1"></i>Đủ hàng</span>
                                            @elseif ($row->sl_production > 0)
                                                <span class="badge bg-info"><i class="fa-solid fa-industry me-1"></i>Đang
                                                    SX</span>
                                            @elseif ($row->stage_breakdown->sum() > 0)
                                                <span class="badge bg-warning text-dark"><i
                                                        class="fa-solid fa-clock me-1"></i>Chờ SX</span>
                                            @else
                                                <span class="badge bg-danger"><i
                                                        class="fa-solid fa-triangle-exclamation me-1"></i>Thiếu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark fw-bold">
                                <tr>
                                    <td>Tổng ({{ $summary->count() }} mã)</td>
                                    <td class="text-center">{{ $summary->sum('so_don') }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('tong_qty'), 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('sl_production'), 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('ton_kho'), 2) }}</td>
                                    <td class="text-end">
                                        {{ number_format($summary->sum('ton_kho') - $summary->sum('tong_qty'), 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- ═══ HÀNH ĐỘNG: ĐỦ HÀNG → XUẤT KHO ═══ --}}
                @php
                    $duHang = $summary->where('du_hang', true);
                    $thieuHang = $summary->where('du_hang', false);
                @endphp

                @if ($duHang->count() && $hasFilter)
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#collapseShip" role="button">
                            <span>
                                <i class="fa-solid fa-check-circle me-1"></i>
                                <strong>Đủ hàng — Sẵn sàng xuất kho</strong>
                                <span class="badge bg-light text-success ms-2">{{ $duHang->count() }} mã</span>
                            </span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="collapse" id="collapseShip">
                            <div class="card-body p-2">
                                <form method="POST" action="{{ route('admin.order-tracking.ship-from-stock') }}"
                                    id="shipForm">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-2">
                                            <thead class="table-success">
                                                <tr>
                                                    <th><input type="checkbox" id="checkAllShip" checked></th>
                                                    <th>Mã HH</th>
                                                    <th class="text-center">Số PO</th>
                                                    <th class="text-end">Cần giao</th>
                                                    <th class="text-end">Tồn kho</th>
                                                    <th class="text-end">Dư</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($duHang as $row)
                                                    <tr>
                                                        <td>
                                                            @foreach ($row->order_ids as $oid)
                                                                <input type="checkbox" name="order_ids[]"
                                                                    value="{{ $oid }}" class="ship-check"
                                                                    checked style="display:none">
                                                            @endforeach
                                                            <input type="checkbox" class="ship-group-check" checked>
                                                        </td>
                                                        <td class="fw-semibold">{{ $row->ma_hh ?: '—' }}</td>
                                                        <td class="text-center">{{ $row->so_don }}</td>
                                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                                        <td class="text-end fw-bold text-success">
                                                            {{ number_format($row->ton_kho, 2) }}</td>
                                                        <td class="text-end text-success">
                                                            +{{ number_format($row->ton_kho - $row->tong_qty, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm"
                                        onclick="return confirm('Xuất kho giao hàng cho các đơn đã chọn?')">
                                        <i class="fa-solid fa-truck-fast me-1"></i>Xuất kho giao hàng
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ═══ HÀNH ĐỘNG: THIẾU HÀNG → TẠO TRACKING & SX ═══ --}}
                @if ($thieuHang->count() && $hasFilter)
                    <div class="card border-danger mb-3">
                        <div class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#collapseGenerate" role="button">
                            <span>
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                <strong>Thiếu hàng — Cần sản xuất</strong>
                                <span class="badge bg-light text-danger ms-2">{{ $thieuHang->count() }} mã</span>
                            </span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="collapse show" id="collapseGenerate">
                            <div class="card-body p-2">
                                <form method="POST" action="{{ route('admin.order-tracking.generate') }}"
                                    id="generateForm">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-2">
                                            <thead class="table-danger">
                                                <tr>
                                                    <th><input type="checkbox" id="checkAllGenerate" checked></th>
                                                    <th>Mã HH</th>
                                                    <th class="text-center">Số PO</th>
                                                    <th class="text-end">Cần giao</th>
                                                    <th class="text-end">Đang SX</th>
                                                    <th class="text-end">Tồn kho</th>
                                                    <th class="text-end">Thiếu</th>
                                                    <th style="min-width:140px">Tiến độ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($thieuHang as $row)
                                                    <tr>
                                                        <td>
                                                            @foreach ($row->order_ids as $oid)
                                                                <input type="checkbox" name="order_ids[]"
                                                                    value="{{ $oid }}" class="generate-check"
                                                                    checked style="display:none">
                                                            @endforeach
                                                            <input type="checkbox" class="generate-group-check" checked>
                                                        </td>
                                                        <td class="fw-semibold">{{ $row->ma_hh ?: '—' }}</td>
                                                        <td class="text-center">{{ $row->so_don }}</td>
                                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                                        <td class="text-end text-info">
                                                            {{ number_format($row->sl_production, 2) }}</td>
                                                        <td
                                                            class="text-end {{ $row->ton_kho > 0 ? 'text-success' : 'text-muted' }}">
                                                            {{ number_format($row->ton_kho, 2) }}</td>
                                                        <td class="text-end fw-bold text-danger">
                                                            {{ number_format($row->thieu, 2) }}</td>
                                                        <td>
                                                            <div class="progress" style="height:18px">
                                                                @php $pct = $row->total_progress; @endphp
                                                                <div class="progress-bar {{ $pct >= 50 ? 'bg-info' : 'bg-warning' }}"
                                                                    style="width:{{ $pct }}%">
                                                                    {{ $pct }}%</div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Tạo Tracking & Lên lệnh SX
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @elseif ($hasFilter && $summary->isEmpty())
                <div class="alert alert-info">Không tìm thấy đơn hàng nào với bộ lọc đã chọn.</div>
            @else
                <div class="alert alert-secondary mb-4">
                    <i class="fa-solid fa-filter me-1"></i>Chọn PL Number hoặc Chart ở trên để xem dashboard tiến độ sản
                    xuất.
                </div>
            @endif

            {{-- ═══ DANH SÁCH TRACKING CHI TIẾT ═══ --}}
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-list me-1"></i>Chi tiết Tracking theo PO</h6>
            <form id="trackingActionForm" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th><input type="checkbox" id="checkAllTracking"></th>
                                <th>#</th>
                                <th>Order (Job No)</th>
                                <th>OT Number</th>
                                <th>PL Number</th>
                                <th>Mã HH</th>
                                <th>Màu</th>
                                <th>Kích</th>
                                <th>Công đoạn</th>
                                <th class="text-end">SL Đơn hàng</th>
                                <th class="text-end">SL Sản xuất</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    <td><input type="checkbox" name="tracking_ids[]" value="{{ $item->id }}"
                                            class="tracking-check"></td>
                                    <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                    <td class="fw-semibold">{{ $item->order->job_no ?? '—' }}</td>
                                    <td>
                                        @if ($item->tracking_number)
                                            <a href="{{ route('admin.order-tracking.lot', $item->tracking_number) }}"
                                                class="text-decoration-none fw-semibold text-primary"
                                                title="{{ $item->tracking_number }}">
                                                {{ $item->tracking_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                        @if ($item->da_tao_lenh_sx)
                                            <span class="badge bg-success ms-1" style="font-size:.75rem"><i
                                                    class="fa-solid fa-check"></i> Đã lên lệnh</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->pl_number)
                                            <span class="fw-semibold">{{ $item->pl_number }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->mau }}</td>
                                    <td>{{ $item->kich }}</td>
                                    <td>
                                        @php
                                            $stageInfo = $stages[$item->cong_doan] ?? [
                                                'icon' => 'fa-question',
                                                'color' => 'secondary',
                                                'order' => -1,
                                            ];
                                            $currentOrder = $stageInfo['order'];
                                        @endphp
                                        <div class="d-flex align-items-center gap-1">
                                            @foreach ($stages as $stageName => $info)
                                                @php
                                                    $isDone = $info['order'] < $currentOrder;
                                                    $isCurrent = $stageName === $item->cong_doan;
                                                @endphp
                                                <span
                                                    class="rounded-circle d-inline-flex align-items-center justify-content-center
                                                    {{ $isCurrent ? 'bg-' . $info['color'] . ' text-white' : ($isDone ? 'bg-' . $info['color'] . ' text-white opacity-50' : 'bg-light text-muted border') }}"
                                                    style="width:24px;height:24px;font-size:.6rem"
                                                    title="{{ $stageName }}">
                                                    <i class="fa-solid {{ $info['icon'] }}"></i>
                                                </span>
                                                @if (!$loop->last)
                                                    <i class="fa-solid fa-chevron-right"
                                                        style="font-size:.45rem;color:#ccc"></i>
                                                @endif
                                            @endforeach
                                            <span class="badge bg-{{ $stageInfo['color'] }} ms-1"
                                                style="font-size:.7rem">{{ $item->cong_doan }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($item->sl_don_hang, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->sl_san_xuat, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.order-tracking.edit', $item) }}"
                                            class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                        <button type="button" class="btn btn-danger btn-xs"
                                            data-url="{{ route('admin.order-tracking.destroy', $item) }}"
                                            onclick="if(confirm('Xóa tracking này?')){document.getElementById('deleteForm').action=this.dataset.url;document.getElementById('deleteForm').submit();}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-muted text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @if ($data->count())
                <div class="d-flex gap-2 mt-2 mb-3">
                    <button type="button" class="btn btn-info btn-sm text-white" id="btnPushProduction">
                        <i class="fa-solid fa-industry me-1"></i>Chuyển sang Sản xuất (gộp theo Mã HH)
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnBulkDelete">
                        <i class="fa-solid fa-trash me-1"></i>Xóa hàng loạt
                    </button>
                </div>
            @endif

            {{ $data->links() }}
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none">@csrf @method('DELETE')</form>

    <script>
        document.getElementById('checkAllShip')?.addEventListener('change', function() {
            document.querySelectorAll('.ship-group-check').forEach(cb => cb.checked = this.checked);
            document.querySelectorAll('.ship-check').forEach(cb => cb.checked = this.checked);
        });
        document.querySelectorAll('.ship-group-check').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('tr').querySelectorAll('.ship-check').forEach(h => h.checked = this.checked);
            });
        });
        document.getElementById('checkAllGenerate')?.addEventListener('change', function() {
            document.querySelectorAll('.generate-group-check').forEach(cb => cb.checked = this.checked);
            document.querySelectorAll('.generate-check').forEach(cb => cb.checked = this.checked);
        });
        document.querySelectorAll('.generate-group-check').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('tr').querySelectorAll('.generate-check').forEach(h => h.checked = this
                    .checked);
            });
        });
        document.getElementById('checkAllTracking')?.addEventListener('change', function() {
            document.querySelectorAll('.tracking-check').forEach(cb => cb.checked = this.checked);
        });
        document.getElementById('btnPushProduction')?.addEventListener('click', function() {
            const form = document.getElementById('trackingActionForm');
            const checked = form.querySelectorAll('.tracking-check:checked');
            if (checked.length === 0) return alert('Chọn ít nhất 1 tracking.');
            if (!confirm(`Chuyển ${checked.length} mục sang sản xuất (gộp theo Mã HH)?`)) return;
            form.action = '{{ route('admin.order-tracking.push-production') }}';
            form.submit();
        });
        document.getElementById('btnBulkDelete')?.addEventListener('click', function() {
            const form = document.getElementById('trackingActionForm');
            const checked = form.querySelectorAll('.tracking-check:checked');
            if (checked.length === 0) return alert('Chọn ít nhất 1 tracking.');
            if (!confirm(`Xóa ${checked.length} tracking đã chọn?`)) return;
            form.action = '{{ route('admin.order-tracking.bulk-delete') }}';
            form.submit();
        });
    </script>
@endsection

@section('scripts')
    <script>
        new TomSelect('#plNumberSelect', {
            plugins: ['remove_button'],
            maxOptions: null,
            allowEmptyOption: false,
        });
        new TomSelect('#chartSelect', {
            plugins: ['remove_button'],
            maxOptions: null,
            allowEmptyOption: false,
        });
    </script>
@endsection
