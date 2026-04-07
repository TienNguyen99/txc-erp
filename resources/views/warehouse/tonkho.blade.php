@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="card-page">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f"><i class="fa-solid fa-chart-bar me-2"></i>Tồn Kho — Tháng {{ $thang }}/{{ $nam }}</h5>
                <a href="{{ route('warehouse.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i>Danh sách giao dịch
                </a>
            </div>

            {{-- Lọc tháng --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-auto">
                    <select name="thang" class="form-select form-select-sm">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $thang == $i ? 'selected' : '' }}>Tháng
                                {{ $i }}
                            </option>
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

            {{-- Bảng tồn kho --}}
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Màu</th>
                            <th>Size</th>
                            <th class="text-end">Tồn đầu tháng</th>
                            <th class="text-end text-success">+ Nhập tháng</th>
                            <th class="text-end text-danger">- Xuất tháng</th>
                            <th class="text-end">Tồn cuối</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $tongTonCuoi = 0; @endphp
                        @forelse($tonKho as $row)
                            @php $tongTonCuoi += $row['ton_cuoi']; @endphp
                            <tr class="{{ $row['ton_cuoi'] < 0 ? 'table-danger' : '' }}">
                                <td>{{ $row['mau'] }}</td>
                                <td>{{ $row['size'] }}</td>
                                <td class="text-end">{{ number_format($row['ton_dau'], 2) }}</td>
                                <td class="text-end text-success">
                                    +{{ number_format($row['nhap'], 2) }}
                                </td>
                                <td class="text-end text-danger">
                                    -{{ number_format($row['xuat'], 2) }}
                                </td>
                                <td
                                    class="text-end fw-bold
            {{ $row['ton_cuoi'] < 0 ? 'text-danger' : 'text-dark' }}">
                                    {{ number_format($row['ton_cuoi'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($tonKho->count() > 0)
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="5" class="text-end">TỔNG TỒN CUỐI:</td>
                                <td class="text-end">{{ number_format($tongTonCuoi, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

        </div>
    </div>
@endsection
