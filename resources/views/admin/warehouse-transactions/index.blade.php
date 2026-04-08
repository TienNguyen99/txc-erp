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
            <div class="d-flex gap-2">
                <a href="{{ route('admin.warehouse-transactions.nhap-theo-lenh') }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-dolly me-1"></i>Nhập kho theo Lệnh SX
                </a>
                <a href="{{ route('admin.warehouse-transactions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Thêm Giao dịch
                </a>
            </div>
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
                    <input type="number" name="nam" class="form-control form-control-sm" value="{{ $nam }}"
                        style="width:80px">
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
@endsection
