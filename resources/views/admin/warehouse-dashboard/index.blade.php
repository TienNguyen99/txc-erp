@extends('layouts.app')

@section('css')
    <style>
        .warehouse-dashboard-table th,
        .warehouse-dashboard-table td {
            white-space: nowrap;
            font-size: .8rem;
            padding: .25rem .4rem;
        }

        .warehouse-dashboard-table .nhap-header {
            background: #d4edda !important;
            color: #155724;
        }

        .warehouse-dashboard-table .xuat-header {
            background: #f8d7da !important;
            color: #721c24;
        }

        .warehouse-dashboard-table .sep {
            border-left: 3px solid #333 !important;
        }

        .warehouse-dashboard-table .nhap-cell {
            background: #f0fff0;
        }

        .warehouse-dashboard-table .xuat-cell {
            background: #fff5f5;
        }

        .warehouse-dashboard-table .total-col {
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="page-title mb-1"><i class="fa-solid fa-chart-column me-2"></i>Dashboard Kho</h4>
                <div class="text-muted small">Tổng hợp tồn đầu, nhập, xuất và tồn cuối theo tháng.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.warehouse-transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i>Giao dịch kho
                </a>
                <a href="{{ route('admin.warehouse-transactions.export-dashboard', ['thang' => $thang, 'nam' => $nam]) }}"
                    class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel me-1"></i>Export Dashboard
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card-page py-3">
                    <div class="text-muted small">Mã hàng theo dõi</div>
                    <div class="fs-4 fw-bold">{{ number_format($stats->tong_ma) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-page py-3">
                    <div class="text-muted small">Tổng nhập tháng</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($stats->tong_nhap) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-page py-3">
                    <div class="text-muted small">Tổng xuất tháng</div>
                    <div class="fs-4 fw-bold text-danger">{{ number_format($stats->tong_xuat) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-page py-3">
                    <div class="text-muted small">Tổng tồn cuối</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($stats->tong_ton) }}</div>
                </div>
            </div>
        </div>

        <div class="card-page">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f">
                    <i class="fa-solid fa-table me-2"></i>Tồn Kho - Tháng {{ $thang }}/{{ $nam }}
                </h5>
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <select name="thang" class="form-select form-select-sm" style="width:auto">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $thang == $i ? 'selected' : '' }}>Tháng {{ $i }}</option>
                        @endfor
                    </select>
                    <input type="number" name="nam" class="form-control form-control-sm" value="{{ $nam }}" style="width:90px">
                    <button class="btn btn-primary btn-sm">Xem</button>
                </form>
            </div>

            @php
                $nhapCount = $nhapDates->count();
                $xuatCount = $xuatDates->count();
            @endphp

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle warehouse-dashboard-table mb-0">
                    <thead>
                        <tr class="table-dark text-center">
                            <th rowspan="2" style="min-width:90px">Mã HH</th>
                            <th rowspan="2" style="min-width:60px">Kích</th>
                            <th rowspan="2" style="min-width:60px">Màu</th>
                            <th rowspan="2" class="text-end" style="min-width:70px;background:#ffeeba!important;color:#856404">Tồn đầu</th>
                            <th colspan="{{ max(1, $nhapCount + 1) }}" class="nhap-header sep">Nhập kho</th>
                            <th colspan="{{ max(1, $xuatCount + 1) }}" class="xuat-header sep">Xuất kho</th>
                            <th rowspan="2" class="text-end sep" style="min-width:70px;background:#ffeeba!important;color:#856404">Tồn cuối</th>
                            <th rowspan="2" class="text-end" style="min-width:80px;background:#cce5ff!important;color:#004085">Cần đi</th>
                        </tr>
                        <tr class="text-center" style="font-size:.75rem">
                            @foreach ($nhapDates as $d)
                                <th class="nhap-header {{ $loop->first ? 'sep' : '' }}">{{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
                            @endforeach
                            <th class="nhap-header total-col {{ $nhapCount ? '' : 'sep' }}">Tổng nhập</th>
                            @foreach ($xuatDates as $d)
                                <th class="xuat-header {{ $loop->first ? 'sep' : '' }}">{{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
                            @endforeach
                            <th class="xuat-header total-col {{ $xuatCount ? '' : 'sep' }}">Tổng xuất</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grouped = $tonKho->groupBy('ma_hh'); @endphp
                        @forelse($grouped as $maHh => $rows)
                            @foreach ($rows as $i => $row)
                                <tr>
                                    @if ($i === 0)
                                        <td rowspan="{{ $rows->count() }}" class="fw-bold align-middle">{{ $maHh ?: '-' }}</td>
                                    @endif
                                    <td>{{ $row['size'] ?: '-' }}</td>
                                    <td>{{ $row['mau'] ?: '-' }}</td>
                                    <td class="text-end" style="background:#fff8e1">{{ $row['ton_dau'] ? number_format($row['ton_dau']) : '' }}</td>
                                    @foreach ($nhapDates as $d)
                                        <td class="text-end nhap-cell {{ $loop->first ? 'sep' : '' }}">
                                            {{ $row['nhap_days'][$d] ?? 0 ? number_format($row['nhap_days'][$d]) : '' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end nhap-cell total-col {{ $nhapCount ? '' : 'sep' }}">
                                        {{ $row['tong_nhap'] ? number_format($row['tong_nhap']) : '' }}
                                    </td>
                                    @foreach ($xuatDates as $d)
                                        <td class="text-end xuat-cell {{ $loop->first ? 'sep' : '' }}">
                                            {{ $row['xuat_days'][$d] ?? 0 ? number_format($row['xuat_days'][$d]) : '' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end xuat-cell total-col {{ $xuatCount ? '' : 'sep' }}">
                                        {{ $row['tong_xuat'] ? number_format($row['tong_xuat']) : '' }}
                                    </td>
                                    <td class="text-end fw-bold sep {{ $row['ton_cuoi'] < 0 ? 'text-danger' : '' }}" style="background:#fff8e1">
                                        {{ number_format($row['ton_cuoi']) }}
                                    </td>
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
                                <td colspan="20" class="text-center text-muted py-3">Chưa có dữ liệu kho trong tháng này</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
