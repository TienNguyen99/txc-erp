@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-dolly-flatbed me-2"></i>Soạn hàng</h4>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.warehouse-transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i>Giao dịch kho
                </a>
                <a href="{{ route('admin.warehouse-transactions.ton-kho') }}" class="btn btn-info btn-sm text-white">
                    <i class="fa-solid fa-boxes-stacked me-1"></i>Tồn kho
                </a>
                <a href="{{ route('admin.warehouse-documents.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-file-invoice me-1"></i>Phiếu kho
                </a>
            </div>
        </div>

        <div class="card-page mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i>Phiếu xuất kho theo lô giao
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-dark fs-6">Lot shipped: {{ $lotStats->shipped_lots ?? 0 }}</span>
                    <span class="badge bg-danger fs-6">Thiếu XUATKHO: {{ $lotStats->missing_xuat_kho ?? 0 }}</span>
                    <span class="badge bg-success fs-6">Đủ hàng: {{ $soanStats->du_hang }}</span>
                    <span class="badge fs-6" style="background:#fd7e14;color:#fff">Thiếu 1 phần: {{ $soanStats->thieu_1_phan }}</span>
                    <span class="badge bg-warning text-dark fs-6">Đang SX: {{ $soanStats->dang_sx }}</span>
                    <span class="badge bg-danger fs-6">Thiếu: {{ $soanStats->thieu_hang }}</span>
                    <span class="badge bg-secondary fs-6">Tổng: {{ $soanStats->tong_phieu }} phiếu</span>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.warehouse-transactions.soan-hang') }}" class="row g-2 mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-0" style="font-size:.8rem">
                        <i class="fa-solid fa-truck-fast me-1"></i>Lô giao
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
                        <a href="{{ route('admin.warehouse-transactions.soan-hang') }}" class="btn btn-outline-secondary btn-sm ms-1">
                            <i class="fa-solid fa-rotate-left me-1"></i>Xem tất cả
                        </a>
                    @endif
                </div>
                @if ($selectedTracking)
                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('admin.warehouse-transactions.export-packing-list', ['tracking_number' => $selectedTracking]) }}"
                            class="btn btn-dark btn-sm">
                            <i class="fa-solid fa-file-invoice me-1"></i>Export Packing List
                        </a>
                        <a href="{{ route('admin.warehouse-transactions.print-labels', ['tracking_number' => $selectedTracking]) }}"
                            class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-print me-1"></i>In tem thùng
                        </a>
                    </div>
                @endif
            </form>

            @php
                $missingXuatKhoCount = $selectedTracking
                    ? ($selectedLotMissingXuatKho ?? 0)
                    : ($lotStats->missing_xuat_kho ?? 0);
            @endphp

            @if ($missingXuatKhoCount > 0)
                <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
                    Có {{ $missingXuatKhoCount }} dòng order đã shipped nhưng chưa có XUATKHO, tồn kho chưa được trừ.
                    <form method="POST" action="{{ route('admin.warehouse-transactions.sync-shipped-xuat-kho') }}" class="d-inline ms-2">
                        @csrf
                        @if ($selectedTracking)
                            <input type="hidden" name="tracking_number" value="{{ $selectedTracking }}">
                        @endif
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Tạo XUATKHO cho các order đã shipped nhưng chưa trừ tồn?')">
                            Đồng bộ XUATKHO
                        </button>
                    </form>
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
                                    <th>SL xuất</th>
                                    <th>Ngày cần giao</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $prevMaHh = null; @endphp
                                @foreach ($soanHang as $idx => $row)
                                    @if ($prevMaHh !== null && $prevMaHh !== $row->ma_hh)
                                        <tr><td colspan="16" style="height:4px;background:#1e3a5f;padding:0;border:none"></td></tr>
                                    @endif
                                    @php $prevMaHh = $row->ma_hh; @endphp
                                    <tr class="@if($row->trang_thai === 'du') table-success @elseif($row->trang_thai === 'thieu') table-danger @endif"
                                        @if($row->trang_thai === 'thieu_1_phan') style="background:#fff3cd" @endif>
                                        <td class="text-center">
                                            @if (in_array($row->trang_thai, ['du', 'thieu_1_phan']))
                                                <input type="checkbox" name="items[{{ $idx }}][selected]" value="1" class="soan-check">
                                                <input type="hidden" name="items[{{ $idx }}][tracking_id]" value="{{ $row->tracking_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][ma_hh]" value="{{ $row->ma_hh }}">
                                                <input type="hidden" name="items[{{ $idx }}][mau]" value="{{ $row->mau }}">
                                                <input type="hidden" name="items[{{ $idx }}][size]" value="{{ $row->size }}">
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $row->ma_hh }}</td>
                                        <td><small>{{ $row->ten_hh ?: $row->im_number }}</small></td>
                                        <td><small>{{ $row->job_no }}</small></td>
                                        <td><small>{{ $row->fty_po ?? '' }}</small></td>
                                        <td><small>{{ $row->pl_number }}</small></td>
                                        <td>{{ $row->mau }}</td>
                                        <td><span class="badge bg-info">{{ $row->cong_doan }}</span></td>
                                        <td class="text-end fw-bold">{{ number_format($row->can_xuat, 2) }}</td>
                                        <td class="text-end fw-bold {{ $row->ton_con_lai <= 0 ? 'text-danger' : 'text-success' }}" style="background:#e8f5e9">
                                            {{ number_format($row->ton_con_lai, 2) }}
                                        </td>
                                        <td class="text-end fw-bold {{ $row->cap_duoc >= $row->can_xuat ? 'text-success' : ($row->cap_duoc > 0 ? 'text-warning' : 'text-danger') }}" style="background:#e8f5e9">
                                            {{ number_format($row->cap_duoc, 2) }}
                                        </td>
                                        <td class="text-end fw-bold {{ $row->thieu > 0 ? 'text-danger' : 'text-muted' }}" style="background:#ffebee">
                                            {{ $row->thieu > 0 ? number_format($row->thieu, 2) : '-' }}
                                        </td>
                                        <td class="text-end {{ $row->dang_sx > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                                            {{ $row->dang_sx > 0 ? number_format($row->dang_sx, 2) : '-' }}
                                        </td>
                                        <td style="width:100px">
                                            @if (in_array($row->trang_thai, ['du', 'thieu_1_phan']))
                                                <input type="number" name="items[{{ $idx }}][so_luong]"
                                                    class="form-control form-control-sm text-end sl-xuat"
                                                    value="{{ $row->cap_duoc }}" min="0" max="{{ $row->cap_duoc }}" step="0.01">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row->sig_need_date)
                                                <small class="{{ $row->sig_need_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                                    {{ $row->sig_need_date->format('d/m/Y') }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
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
                                    <td colspan="8" class="text-end">Tổng:</td>
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
                        <input type="date" name="ngay" class="form-control form-control-sm" style="width:160px" value="{{ now()->format('Y-m-d') }}" required>
                        <input type="text" name="ma_nv" class="form-control form-control-sm" style="width:140px" placeholder="Mã NV thủ kho">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xuất kho các phiếu đã chọn?')">
                            <i class="fa-solid fa-truck-loading me-1"></i>Xuất kho
                        </button>
                    </div>
                </form>
            @else
                @if (($lotStats->shipped_lots ?? 0) > 0)
                    <div class="alert alert-secondary py-2 mb-3" style="font-size:.85rem">
                        Có {{ $lotStats->shipped_lots }} lot đã shipped nên không hiển thị trong danh sách soạn hàng.
                    </div>
                @endif
                <p class="text-muted text-center mb-0">
                    @if ($selectedTracking)
                        Không có phiếu nào cần soạn hàng cho lô <strong>{{ $selectedTracking }}</strong>.
                    @else
                        Không có phiếu nào cần soạn hàng.
                    @endif
                </p>
            @endif
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
