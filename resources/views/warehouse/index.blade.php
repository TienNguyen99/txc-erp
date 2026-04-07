@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="card-page">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f"><i class="fa-solid fa-exchange-alt me-2"></i>Giao Dịch Kho</h5>
                <a href="{{ route('warehouse.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Nhập / Xuất Kho
                </a>
            </div>

            {{-- Alert --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Bộ lọc --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <select name="cong_doan" class="form-select form-select-sm">
                        <option value="">-- Tất cả --</option>
                        <option value="NHAPKHO" {{ request('cong_doan') == 'NHAPKHO' ? 'selected' : '' }}>NHẬP KHO</option>
                        <option value="XUATKHO" {{ request('cong_doan') == 'XUATKHO' ? 'selected' : '' }}>XUẤT KHO</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="mau" class="form-select form-select-sm">
                        <option value="">-- Màu --</option>
                        @foreach ($danhSachMau as $m)
                            <option value="{{ $m }}" {{ request('mau') == $m ? 'selected' : '' }}>
                                {{ $m }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="size" class="form-select form-select-sm">
                        <option value="">-- Size --</option>
                        @foreach ($danhSachSize as $s)
                            <option value="{{ $s }}" {{ request('size') == $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="thang" class="form-select form-select-sm">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}"
                                {{ request('thang', now()->month) == $i ? 'selected' : '' }}>
                                Tháng {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="nam" class="form-control form-control-sm"
                        value="{{ request('nam', now()->year) }}" placeholder="Năm">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-primary btn-sm flex-fill">Lọc</button>
                    <a href="{{ route('warehouse.index') }}" class="btn btn-outline-secondary btn-sm"><i
                            class="fa-solid fa-xmark"></i></a>
                </div>
            </form>

            {{-- Bảng danh sách --}}
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Ngày</th>
                            <th>Công đoạn</th>
                            <th>Mã HH</th>
                            <th>Màu</th>
                            <th>Size</th>
                            <th class="text-end">Số lượng</th>
                            <th>Mã NV</th>
                            <th>Lệnh SX</th>
                            <th>Ghi chú</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td>{{ $t->ngay->format('d/m/Y') }}</td>
                                <td>
                                    @if ($t->cong_doan == 'NHAPKHO')
                                        <span class="badge bg-success">NHẬP KHO</span>
                                    @else
                                        <span class="badge bg-danger">XUẤT KHO</span>
                                    @endif
                                </td>
                                <td>{{ $t->ma_hh }}</td>
                                <td>{{ $t->mau }}</td>
                                <td>{{ $t->size }}</td>
                                <td class="text-end fw-bold">{{ number_format($t->so_luong, 2) }}</td>
                                <td>{{ $t->ma_nv }}</td>
                                <td>{{ $t->lenh_sx }}</td>
                                <td>{{ $t->note }}</td>
                                <td class="text-center">
                                    <a href="{{ route('warehouse.edit', $t) }}" class="btn btn-xs btn-outline-warning"><i
                                            class="fa-solid fa-pen me-1"></i>Sửa</a>
                                    <form method="POST" action="{{ route('warehouse.destroy', $t) }}" class="d-inline"
                                        onsubmit="return confirm('Xác nhận xóa?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i
                                                class="fa-solid fa-trash me-1"></i>Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Chưa có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $transactions->withQueryString()->links() }}
        </div>
    </div>
@endsection
