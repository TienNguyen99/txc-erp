@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.lenh-san-xuat.show', $lenhSanXuat->id) }}" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i> Trở về lệnh</a>
        <h2 class="page-title mb-0">
            Yêu cầu Xuất vật tư cho Lệnh: <span class="text-primary">{{ $lenhSanXuat->lenh_so }}</span>
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-page">
        <form action="{{ route('admin.warehouse-transactions.store-lenh-xuat-vat-tu') }}" method="POST">
            @csrf
            <input type="hidden" name="lenh_san_xuat_id" value="{{ $lenhSanXuat->id }}">
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Ngày xuất <span class="text-danger">*</span></label>
                    <input type="date" name="ngay" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Người xuất (Mã NV)</label>
                    <input type="text" name="ma_nv" class="form-control" value="{{ auth()->user()->username ?? '' }}">
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Mã NVL</th>
                            <th>Tên Nguyên vật liệu</th>
                            <th>ĐVT</th>
                            <th class="text-end text-info">Tổng cần (Theo định mức)</th>
                            <th class="text-end text-success">Đã xuất cho lệnh</th>
                            <th class="text-end text-danger">Tồn kho hiện tại</th>
                            <th class="text-end" style="width: 150px;">Số lượng xuất lần này</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $mat)
                            @php
                                $conLai = max(0, $mat['tong_can'] - $mat['da_xuat']);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $mat['ma_hh'] }}</strong><input type="hidden" name="rows[{{ $loop->index }}][ma_hh]" value="{{ $mat['ma_hh'] }}"></td>
                                <td>{{ $mat['ten_hh'] }}</td>
                                <td>{{ $mat['dvt'] }}</td>
                                <td class="text-end fw-bold text-info">{{ number_format($mat['tong_can'], 2) }}</td>
                                <td class="text-end text-success">{{ number_format($mat['da_xuat'], 2) }}</td>
                                <td class="text-end text-danger fw-bold">{{ number_format($mat['ton_kho'], 2) }}</td>
                                <td class="text-end">
                                    <input type="number" step="0.01" min="0" class="form-control text-end" 
                                           name="rows[{{ $loop->index }}][so_luong_xuat]" 
                                           value="{{ round($conLai, 2) }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Không tìm thấy vật tư yêu cầu nào (Các mã hàng trong lệnh chưa được cấu hình định mức BOM).</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg" {{ empty($materials) ? 'disabled' : '' }}>
                    <i class="fa-solid fa-check-circle me-2"></i>Duyệt Xuất Kho
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
