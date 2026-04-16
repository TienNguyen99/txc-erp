@extends('layouts.app')
@section('css')
    <style>
        .ton-kho-table th,
        .ton-kho-table td {
            white-space: nowrap;
            font-size: .8rem;
            padding: .25rem .4rem;
        }

        .ton-kho-table .nhap-header {
            background: #d4edda !important;
            color: #155724;
        }

        .ton-kho-table .xuat-header {
            background: #f8d7da !important;
            color: #721c24;
        }

        .ton-kho-table .sep {
            border-left: 3px solid #333 !important;
        }

        .ton-kho-table .nhap-cell {
            background: #f0fff0;
        }

        .ton-kho-table .xuat-cell {
            background: #fff5f5;
        }

        .ton-kho-table .total-col {
            font-weight: 700;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-warehouse me-2"></i>Quản lý Kho</h4>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.warehouse-transactions.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-file-excel me-1"></i>Template
                </a>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i>Import
                </button>
                <a href="{{ route('admin.warehouse-transactions.export') }}" class="btn btn-info btn-sm text-white">
                    <i class="fa-solid fa-file-export me-1"></i>Export
                </a>
                <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#packingListModal">
                    <i class="fa-solid fa-file-invoice me-1"></i>Packing List
                </button>
                <a href="{{ route('admin.warehouse-transactions.nhap-theo-lenh') }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-dolly me-1"></i>Nhập kho theo Lệnh SX
                </a>
                <a href="{{ route('admin.warehouse-transactions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Thêm Giao dịch
                </a>
            </div>
        </div>

        {{-- Import Modal --}}
        <div class="modal fade" id="importModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.warehouse-transactions.import') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa-solid fa-file-import me-2"></i>Import Nhập/Xuất Kho</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">
                                Tải <a href="{{ route('admin.warehouse-transactions.template') }}">file mẫu</a>,
                                điền dữ liệu rồi upload.<br>
                                Cột <code>cong_doan</code>: <strong>NHAPKHO</strong> hoặc <strong>XUATKHO</strong>.
                            </p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Chọn file Excel</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-upload me-1"></i>Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Packing List Export Modal --}}
        <div class="modal fade" id="packingListModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="GET" action="{{ route('admin.warehouse-transactions.export-packing-list') }}">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa-solid fa-file-invoice me-2"></i>Export Packing List</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">
                                Chọn Order Tracking Number để xuất phiếu Packing List (Phiếu xuất kho) theo đúng format
                                chuẩn.
                            </p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Order Tracking Number</label>
                                <select name="tracking_number" id="packingPlSelect" class="form-select" required>
                                    <option value="">-- Chọn OT Number --</option>
                                    @php
                                        $otNumbers = \App\Models\OrderTracking::whereNotNull('tracking_number')
                                            ->select('tracking_number')
                                            ->selectRaw('COUNT(*) as total_items')
                                            ->groupBy('tracking_number')
                                            ->orderByDesc('tracking_number')
                                            ->get();
                                    @endphp
                                    @foreach ($otNumbers as $ot)
                                        <option value="{{ $ot->tracking_number }}">{{ $ot->tracking_number }}
                                            ({{ $ot->total_items }} items)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-dark btn-sm">
                                <i class="fa-solid fa-download me-1"></i>Export Packing List
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ═══ SOẠN HÀNG — Phân theo lô giao (tracking_number) ═══ --}}
        <div class="card-page mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f">
                    <i class="fa-solid fa-dolly-flatbed me-2"></i>Soạn Hàng (Phiếu xuất kho)
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-success fs-6">Đủ hàng: {{ $soanStats->du_hang }}</span>
                    <span class="badge fs-6" style="background:#fd7e14;color:#fff">Thiếu 1 phần: {{ $soanStats->thieu_1_phan }}</span>
                    <span class="badge bg-warning text-dark fs-6">Đang SX: {{ $soanStats->dang_sx }}</span>
                    <span class="badge bg-danger fs-6">Thiếu: {{ $soanStats->thieu_hang }}</span>
                    <span class="badge bg-secondary fs-6">Tổng: {{ $soanStats->tong_phieu }} phiếu</span>
                </div>
            </div>

            {{-- Filter theo tracking_number --}}
            <form method="GET" class="row g-2 mb-3 align-items-end">
                {{-- Giữ lại params khác --}}
                @if (request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                @if (request('cong_doan')) <input type="hidden" name="cong_doan" value="{{ request('cong_doan') }}"> @endif
                @if (request('thang')) <input type="hidden" name="thang" value="{{ request('thang') }}"> @endif
                @if (request('nam')) <input type="hidden" name="nam" value="{{ request('nam') }}"> @endif

                <div class="col-md-3">
                    <label class="form-label mb-0" style="font-size:.8rem">
                        <i class="fa-solid fa-truck-fast me-1"></i>Lô giao (Tracking Number)
                    </label>
                    <select name="tracking_filter" id="trackingFilterSelect" class="form-select form-select-sm">
                        <option value="">-- Tất cả lô --</option>
                        @foreach ($availableTrackings as $tn)
                            <option value="{{ $tn }}" {{ $selectedTracking === $tn ? 'selected' : '' }}>
                                {{ $tn }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-filter me-1"></i>Lọc lô
                    </button>
                    @if ($selectedTracking)
                        <a href="{{ route('admin.warehouse-transactions.index', request()->except('tracking_filter')) }}"
                            class="btn btn-outline-secondary btn-sm ms-1">
                            <i class="fa-solid fa-rotate-left me-1"></i>Xem tất cả
                        </a>
                    @endif
                </div>
                @if ($selectedTracking)
                    <div class="col-auto ms-auto">
                        <a href="{{ route('admin.warehouse-transactions.export-packing-list', ['tracking_number' => $selectedTracking]) }}"
                            class="btn btn-dark btn-sm">
                            <i class="fa-solid fa-file-invoice me-1"></i>Export Packing List: {{ $selectedTracking }}
                        </a>
                    </div>
                @endif
            </form>

            @if ($selectedTracking)
                <div class="alert alert-info py-2 mb-3" style="font-size:.85rem">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    Đang hiển thị lô: <strong>{{ $selectedTracking }}</strong>
                    — Bảng sắp xếp theo <strong>Mã HH → FTY PO</strong> giống file Packing List.
                </div>
            @endif

            @if ($soanHang->count())
                <form id="xuatKhoForm" method="POST" action="{{ route('admin.warehouse-transactions.xuat-hang-loat') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0" style="font-size:.85rem">
                            <thead class="table-dark">
                                <tr class="text-center">
                                    <th style="width:35px"><input type="checkbox" id="checkAllSoan"></th>
                                    <th>Mã HH</th>
                                    <th>Tên SP / Description</th>
                                    <th>Job No</th>
                                    <th>FTY PO</th>
                                    <th>PL Number</th>
                                    <th>Màu</th>
                                    <th>Công đoạn</th>
                                    <th>Cần xuất</th>
                                    <th style="background:#2d6a4f!important">Tồn còn lại</th>
                                    <th style="background:#2d6a4f!important">Cấp được</th>
                                    <th style="background:#9d0208!important">Thiếu</th>
                                    <th>Đang SX</th>
                                    <th>SL Xuất</th>
                                    <th>Ngày cần giao</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $prevMaHh = null; @endphp
                                @foreach ($soanHang as $idx => $row)
                                    {{-- Separator giữa các nhóm ma_hh --}}
                                    @if ($prevMaHh !== null && $prevMaHh !== $row->ma_hh)
                                        <tr><td colspan="16" style="height:4px;background:#1e3a5f;padding:0;border:none"></td></tr>
                                    @endif
                                    @php $prevMaHh = $row->ma_hh; @endphp
                                    <tr class="@if($row->trang_thai === 'du') table-success @elseif($row->trang_thai === 'thieu_1_phan') @elseif($row->trang_thai === 'thieu') table-danger @endif"
                                        @if($row->trang_thai === 'thieu_1_phan') style="background:#fff3cd" @endif>
                                        <td class="text-center">
                                            @if (in_array($row->trang_thai, ['du', 'thieu_1_phan']))
                                                <input type="checkbox" name="items[{{ $idx }}][selected]"
                                                    value="1" class="soan-check">
                                                <input type="hidden" name="items[{{ $idx }}][tracking_id]"
                                                    value="{{ $row->tracking_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][ma_hh]"
                                                    value="{{ $row->ma_hh }}">
                                                <input type="hidden" name="items[{{ $idx }}][mau]"
                                                    value="{{ $row->mau }}">
                                                <input type="hidden" name="items[{{ $idx }}][size]"
                                                    value="{{ $row->size }}">
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $row->ma_hh }}</td>
                                        <td><small>{{ $row->ten_hh ?: $row->im_number }}</small></td>
                                        <td><small>{{ $row->job_no }}</small></td>
                                        <td><small>{{ $row->fty_po ?? '' }}</small></td>
                                        <td><small>{{ $row->pl_number }}</small></td>
                                        <td>{{ $row->mau }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $row->cong_doan === 'Đã nhập kho' ? 'success' : 'info' }}">
                                                {{ $row->cong_doan }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($row->can_xuat, 2) }}</td>
                                        {{-- Tồn còn lại (sau khi trừ PO phía trên) --}}
                                        <td class="text-end fw-bold {{ $row->ton_con_lai <= 0 ? 'text-danger' : 'text-success' }}"
                                            style="background:#e8f5e9">
                                            {{ number_format($row->ton_con_lai, 2) }}
                                        </td>
                                        {{-- Cấp được --}}
                                        <td class="text-end fw-bold {{ $row->cap_duoc >= $row->can_xuat ? 'text-success' : ($row->cap_duoc > 0 ? 'text-warning' : 'text-danger') }}"
                                            style="background:#e8f5e9">
                                            {{ number_format($row->cap_duoc, 2) }}
                                        </td>
                                        {{-- Thiếu --}}
                                        <td class="text-end fw-bold {{ $row->thieu > 0 ? 'text-danger' : 'text-muted' }}"
                                            style="background:#ffebee">
                                            {{ $row->thieu > 0 ? number_format($row->thieu, 2) : '—' }}
                                        </td>
                                        <td
                                            class="text-end {{ $row->dang_sx > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                                            {{ $row->dang_sx > 0 ? number_format($row->dang_sx, 2) : '—' }}
                                        </td>
                                        <td style="width:100px">
                                            @if (in_array($row->trang_thai, ['du', 'thieu_1_phan']))
                                                <input type="number" name="items[{{ $idx }}][so_luong]"
                                                    class="form-control form-control-sm text-end sl-xuat"
                                                    value="{{ $row->cap_duoc }}" min="0"
                                                    max="{{ $row->cap_duoc }}" step="0.01">
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row->sig_need_date)
                                                <small
                                                    class="{{ $row->sig_need_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                                    {{ $row->sig_need_date->format('d/m/Y') }}
                                                </small>
                                                @if ($row->sig_need_date->isPast())
                                                    <i class="fa-solid fa-exclamation-triangle text-danger"
                                                        title="Quá hạn!"></i>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row->trang_thai === 'du')
                                                <span class="badge bg-success">Đủ hàng</span>
                                            @elseif ($row->trang_thai === 'thieu_1_phan')
                                                <span class="badge" style="background:#fd7e14">Thiếu {{ number_format($row->thieu, 2) }}</span>
                                            @elseif ($row->trang_thai === 'dang_sx')
                                                <span class="badge bg-warning text-dark">Đang SX</span>
                                            @else
                                                <span class="badge bg-danger">Thiếu hàng</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="8" class="text-end">TỔNG:</td>
                                    <td class="text-end">{{ number_format($soanHang->sum('can_xuat'), 2) }}</td>
                                    <td></td>
                                    <td class="text-end text-success">{{ number_format($soanHang->sum('cap_duoc'), 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($soanHang->sum('thieu'), 2) }}</td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <input type="date" name="ngay" class="form-control form-control-sm" style="width:160px"
                            value="{{ now()->format('Y-m-d') }}" required>
                        <input type="text" name="ma_nv" class="form-control form-control-sm" style="width:140px"
                            placeholder="Mã NV thủ kho">
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Xác nhận xuất kho các phiếu đã chọn?')">
                            <i class="fa-solid fa-truck-loading me-1"></i>Xuất Kho
                        </button>
                    </div>
                </form>
            @else
                <p class="text-muted text-center mb-0">
                    @if ($selectedTracking)
                        Không có phiếu nào cần soạn hàng cho lô <strong>{{ $selectedTracking }}</strong>.
                    @else
                        Không có phiếu nào cần soạn hàng.
                    @endif
                </p>
            @endif
        </div>


        {{-- ═══ BẢNG TỒN KHO ═══ --}}
        <div class="card-page mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f">
                    <i class="fa-solid fa-chart-bar me-2"></i>Tồn Kho — Tháng {{ $thang }}/{{ $nam }}
                </h5>
                <form method="GET" class="d-flex gap-2 align-items-center">
                    {{-- giữ lại các filter giao dịch nếu có --}}
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if (request('cong_doan'))
                        <input type="hidden" name="cong_doan" value="{{ request('cong_doan') }}">
                    @endif
                    <select name="thang" class="form-select form-select-sm" style="width:auto">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $thang == $i ? 'selected' : '' }}>Tháng
                                {{ $i }}</option>
                        @endfor
                    </select>
                    <input type="number" name="nam" class="form-control form-control-sm"
                        value="{{ $nam }}" style="width:80px">
                    <button class="btn btn-primary btn-sm">Xem</button>
                </form>
            </div>

            @php
                $nhapCount = $nhapDates->count();
                $xuatCount = $xuatDates->count();
            @endphp

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle ton-kho-table mb-0">
                    <thead>
                        <tr class="table-dark text-center">
                            <th rowspan="2" style="min-width:90px">Mã HH</th>
                            <th rowspan="2" style="min-width:60px">Kích</th>
                            <th rowspan="2" style="min-width:60px">Màu</th>
                            <th rowspan="2" class="text-end"
                                style="min-width:70px;background:#ffeeba!important;color:#856404">TỒN ĐẦU</th>
                            @if ($nhapCount)
                                <th colspan="{{ $nhapCount + 1 }}" class="nhap-header sep">NHẬP KHO</th>
                            @else
                                <th class="nhap-header sep">NHẬP KHO</th>
                            @endif
                            @if ($xuatCount)
                                <th colspan="{{ $xuatCount + 1 }}" class="xuat-header sep">XUẤT KHO</th>
                            @else
                                <th class="xuat-header sep">XUẤT KHO</th>
                            @endif
                            <th rowspan="2" class="text-end sep"
                                style="min-width:70px;background:#ffeeba!important;color:#856404">TỒN CUỐI</th>
                            <th rowspan="2" class="text-end"
                                style="min-width:80px;background:#cce5ff!important;color:#004085">CẦN ĐI</th>
                        </tr>
                        <tr class="text-center" style="font-size:.75rem">
                            @foreach ($nhapDates as $d)
                                <th class="nhap-header {{ $loop->first ? 'sep' : '' }}">
                                    {{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
                            @endforeach
                            <th class="nhap-header total-col">TỔNG</th>
                            @foreach ($xuatDates as $d)
                                <th class="xuat-header {{ $loop->first ? 'sep' : '' }}">
                                    {{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
                            @endforeach
                            <th class="xuat-header total-col">TỔNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grouped = $tonKho->groupBy('ma_hh'); @endphp
                        @forelse($grouped as $maHh => $rows)
                            @foreach ($rows as $i => $row)
                                <tr>
                                    @if ($i === 0)
                                        <td rowspan="{{ $rows->count() }}" class="fw-bold align-middle">
                                            {{ $maHh ?: '—' }}</td>
                                    @endif
                                    <td>{{ $row['size'] ?: '—' }}</td>
                                    <td>{{ $row['mau'] ?: '—' }}</td>
                                    <td class="text-end" style="background:#fff8e1">
                                        {{ $row['ton_dau'] ? number_format($row['ton_dau']) : '' }}</td>
                                    @foreach ($nhapDates as $d)
                                        <td class="text-end nhap-cell {{ $loop->first ? 'sep' : '' }}">
                                            {{ $row['nhap_days'][$d] ?? 0 ? number_format($row['nhap_days'][$d]) : '' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end nhap-cell total-col">
                                        {{ $row['tong_nhap'] ? number_format($row['tong_nhap']) : '' }}</td>
                                    @foreach ($xuatDates as $d)
                                        <td class="text-end xuat-cell {{ $loop->first ? 'sep' : '' }}">
                                            {{ $row['xuat_days'][$d] ?? 0 ? number_format($row['xuat_days'][$d]) : '' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end xuat-cell total-col">
                                        {{ $row['tong_xuat'] ? number_format($row['tong_xuat']) : '' }}</td>
                                    <td class="text-end fw-bold sep {{ $row['ton_cuoi'] < 0 ? 'text-danger' : '' }}"
                                        style="background:#fff8e1">{{ number_format($row['ton_cuoi']) }}</td>
                                    @if ($i === 0)
                                        <td rowspan="{{ $rows->count() }}"
                                            class="text-end align-middle fw-bold {{ ($row['can_di'] ?? 0) > 0 ? 'text-primary' : 'text-muted' }}">
                                            {{ $row['can_di'] ?? 0 ? number_format($row['can_di']) : '' }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="20" class="text-center text-muted py-3">Chưa có dữ liệu kho trong tháng này
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══ DANH SÁCH GIAO DỊCH ═══ --}}
        <div class="card-page">
            <h5 class="fw-bold mb-3" style="color:#1e3a5f"><i class="fa-solid fa-exchange-alt me-2"></i>Giao dịch</h5>
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <input type="hidden" name="thang" value="{{ $thang }}">
                <input type="hidden" name="nam" value="{{ $nam }}">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Lệnh SX / Mã NV..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="cong_doan" class="form-select form-select-sm">
                        <option value="">-- Loại --</option>
                        <option value="NHAPKHO" {{ request('cong_doan') == 'NHAPKHO' ? 'selected' : '' }}>NHẬP KHO
                        </option>
                        <option value="XUATKHO" {{ request('cong_doan') == 'XUATKHO' ? 'selected' : '' }}>XUẤT KHO
                        </option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Tìm</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th>Mã HH</th>
                            <th>Size</th>
                            <th>Màu</th>
                            <th>Số lượng</th>
                            <th>Mã NV</th>
                            <th>Lệnh SX</th>
                            <th>Note</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->ngay->format('d/m/Y') }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $item->cong_doan == 'NHAPKHO' ? 'success' : 'danger' }}">{{ $item->cong_doan }}</span>
                                </td>
                                <td>{{ $item->ma_hh }}</td>
                                <td>{{ $item->size }}</td>
                                <td>{{ $item->mau }}</td>
                                <td>{{ number_format($item->so_luong, 2) }}</td>
                                <td>{{ $item->ma_nv }}</td>
                                <td>{{ $item->lenh_sx }}</td>
                                <td>{{ Str::limit($item->note, 30) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.warehouse-transactions.edit', $item) }}"
                                        class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                    <form method="POST"
                                        action="{{ route('admin.warehouse-transactions.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa giao dịch này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>

    <script>
        document.getElementById('checkAllSoan')?.addEventListener('change', function() {
            document.querySelectorAll('.soan-check').forEach(cb => cb.checked = this.checked);
        });

        document.getElementById('xuatKhoForm')?.addEventListener('submit', function(e) {
            const checked = this.querySelectorAll('.soan-check:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Chọn ít nhất 1 mã hàng để xuất kho.');
            }
        });
    </script>
@endsection

@section('scripts')
    <script>
        document.getElementById('packingListModal')?.addEventListener('shown.bs.modal', function() {
            if (!document.getElementById('packingPlSelect').tomselect) {
                new TomSelect('#packingPlSelect', {
                    allowEmptyOption: false,
                    maxOptions: null,
                });
            }
        });
    </script>
@endsection
