@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0">
            <i class="fa-solid fa-truck me-2"></i>
            {{ isset($nhaCungCap) ? 'Sửa Nhà Cung Cấp' : 'Thêm Nhà Cung Cấp' }}
        </h4>
        <a href="{{ route('admin.nha-cung-cap.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
    <div class="card-page" style="max-width:680px">
        @include('admin.partials.alert')
        <form method="POST"
            action="{{ isset($nhaCungCap) ? route('admin.nha-cung-cap.update', $nhaCungCap) : route('admin.nha-cung-cap.store') }}">
            @csrf
            @if(isset($nhaCungCap)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Mã NCC <span class="text-danger">*</span></label>
                    <input type="text" name="ma_ncc" class="form-control form-control-sm @error('ma_ncc') is-invalid @enderror"
                        value="{{ old('ma_ncc', $nhaCungCap->ma_ncc ?? '') }}" required>
                    @error('ma_ncc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                    <input type="text" name="ten_ncc" class="form-control form-control-sm @error('ten_ncc') is-invalid @enderror"
                        value="{{ old('ten_ncc', $nhaCungCap->ten_ncc ?? '') }}" required>
                    @error('ten_ncc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Người liên hệ</label>
                    <input type="text" name="nguoi_lien_he" class="form-control form-control-sm"
                        value="{{ old('nguoi_lien_he', $nhaCungCap->nguoi_lien_he ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">SĐT</label>
                    <input type="text" name="sdt" class="form-control form-control-sm"
                        value="{{ old('sdt', $nhaCungCap->sdt ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm"
                        value="{{ old('email', $nhaCungCap->email ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mã số thuế</label>
                    <input type="text" name="ma_so_thue" class="form-control form-control-sm"
                        value="{{ old('ma_so_thue', $nhaCungCap->ma_so_thue ?? '') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="dia_chi" class="form-control form-control-sm" rows="2">{{ old('dia_chi', $nhaCungCap->dia_chi ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="ghi_chu" class="form-control form-control-sm" rows="2">{{ old('ghi_chu', $nhaCungCap->ghi_chu ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                            {{ old('active', $nhaCungCap->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Đang hoạt động</label>
                    </div>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-floppy-disk me-1"></i>Lưu
            </button>
        </form>
    </div>
</div>
@endsection
