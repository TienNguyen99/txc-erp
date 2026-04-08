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

        .ton-kho-table .ma-hh-group td {
            background: #eef3f8;
            font-weight: 700;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid px-4">
        <div class="card-page">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f"><i class="fa-solid fa-chart-bar me-2"></i>Tồn Kho — Tháng
                    {{ $thang }}/{{ $nam }}</h5>
                <a href="{{ route('admin.warehouse-transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i>Danh sách giao dịch
                </a>
            </div>

            {{-- Lọc tháng --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-auto">
                    <select name="thang" class="form-select form-select-sm">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $thang == $i ? 'selected' : '' }}>Tháng
                                {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <input type="number" name="nam" class="form-control form-control-sm" value="{{ $nam }}"
                        style="width:90px">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm">Xem</button>
                </div>
            </form>

            @php
                $nhapCount = $nhapDates->count();
                $xuatCount = $xuatDates->count();
                $totalCols = 4 + $nhapCount + 1 + $xuatCount + 1 + 1 + 1; // ma_hh,size,mau,ton_dau + nhap_days + tong_nhap + xuat_days + tong_xuat + ton_cuoi + can_di
            @endphp

            {{-- Bảng tồn kho giống Excel --}}
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle ton-kho-table mb-0">
                    <thead>
                        {{-- Row 1: group headers --}}
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
                        {{-- Row 2: date sub-headers --}}
                        <tr class="text-center" style="font-size:.75rem">
                            @forelse($nhapDates as $d)
                                <th class="nhap-header {{ $loop->first ? 'sep' : '' }}">
                                    {{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
                            @empty
                                {{-- placeholder handled by rowspan --}}
                            @endforelse
                            <th class="nhap-header total-col">TỔNG</th>
                            @forelse($xuatDates as $d)
                                <th class="xuat-header {{ $loop->first ? 'sep' : '' }}">
                                    {{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
                            @empty
                                {{-- placeholder --}}
                            @endforelse
                            <th class="xuat-header total-col">TỔNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grouped = $tonKho->groupBy('ma_hh');
                        @endphp
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
                                    {{-- Nhập theo ngày --}}
                                    @foreach ($nhapDates as $d)
                                        <td class="text-end nhap-cell {{ $loop->first ? 'sep' : '' }}">
                                            {{ $row['nhap_days'][$d] ?? 0 ? number_format($row['nhap_days'][$d]) : '' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end nhap-cell total-col">
                                        {{ $row['tong_nhap'] ? number_format($row['tong_nhap']) : '' }}</td>
                                    {{-- Xuất theo ngày --}}
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
                                <td colspan="{{ $totalCols }}" class="text-center text-muted py-4">Chưa có dữ liệu kho
                                    trong tháng này</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
