@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.khach-hang.index') }}" class="text-decoration-none"
                style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-building me-2"></i>{{ isset($khachHang) ? 'Sửa Khách hàng' : 'Thêm Khách hàng' }}
            </h4>
        </div>
        <div class="card-page">
            <form method="POST"
                action="{{ isset($khachHang) ? route('admin.khach-hang.update', $khachHang) : route('admin.khach-hang.store') }}">
                @csrf
                @if (isset($khachHang))
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã KH <span class="text-danger">*</span></label>
                        <input type="text" name="ma_kh" class="form-control @error('ma_kh') is-invalid @enderror"
                            value="{{ old('ma_kh', $khachHang->ma_kh ?? '') }}" required>
                        @error('ma_kh')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" name="ten_kh" class="form-control @error('ten_kh') is-invalid @enderror"
                            value="{{ old('ten_kh', $khachHang->ten_kh ?? '') }}" required>
                        @error('ten_kh')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Người liên hệ</label>
                        <input type="text" name="nguoi_lien_he" class="form-control"
                            value="{{ old('nguoi_lien_he', $khachHang->nguoi_lien_he ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Số điện thoại</label>
                        <input type="text" name="sdt" class="form-control"
                            value="{{ old('sdt', $khachHang->sdt ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $khachHang->email ?? '') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã số thuế</label>
                        <input type="text" name="ma_so_thue" class="form-control"
                            value="{{ old('ma_so_thue', $khachHang->ma_so_thue ?? '') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                                {{ old('active', $khachHang->active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="active">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Địa chỉ</label>
                        <input type="text" name="dia_chi" class="form-control"
                            value="{{ old('dia_chi', $khachHang->dia_chi ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Ghi chú</label>
                        <textarea name="ghi_chu" class="form-control" rows="2">{{ old('ghi_chu', $khachHang->ghi_chu ?? '') }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    <a href="{{ route('admin.khach-hang.index') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection
