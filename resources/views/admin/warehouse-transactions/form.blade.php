@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="card-page">
            <h5 class="fw-bold mb-3" style="color:#1e3a5f">
                <i
                    class="fa-solid fa-warehouse me-2"></i>{{ isset($warehouseTransaction) ? 'Sửa Giao dịch Kho' : 'Thêm Giao dịch Kho' }}
            </h5>
            <form method="POST"
                action="{{ isset($warehouseTransaction) ? route('admin.warehouse-transactions.update', $warehouseTransaction) : route('admin.warehouse-transactions.store') }}">
                @csrf
                @if (isset($warehouseTransaction))
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Loại <span class="text-danger">*</span></label>
                        <select name="cong_doan" class="form-select @error('cong_doan') is-invalid @enderror" required>
                            <option value="NHAPKHO"
                                {{ old('cong_doan', $warehouseTransaction->cong_doan ?? '') == 'NHAPKHO' ? 'selected' : '' }}>NHẬP
                                KHO</option>
                            <option value="XUATKHO"
                                {{ old('cong_doan', $warehouseTransaction->cong_doan ?? '') == 'XUATKHO' ? 'selected' : '' }}>XUẤT
                                KHO</option>
                        </select>
                        @error('cong_doan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Ngày <span class="text-danger">*</span></label>
                        <input type="date" name="ngay" class="form-control @error('ngay') is-invalid @enderror"
                            value="{{ old('ngay', isset($warehouseTransaction) ? $warehouseTransaction->ngay->format('Y-m-d') : now()->format('Y-m-d')) }}"
                            required>
                        @error('ngay')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Size</label>
                        <input type="text" name="size" class="form-control"
                            value="{{ old('size', $warehouseTransaction->size ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Màu</label>
                        <input type="text" name="mau" class="form-control"
                            value="{{ old('mau', $warehouseTransaction->mau ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="so_luong"
                            class="form-control @error('so_luong') is-invalid @enderror"
                            value="{{ old('so_luong', $warehouseTransaction->so_luong ?? '') }}" required>
                        @error('so_luong')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã NV</label>
                        <input type="text" name="ma_nv" class="form-control"
                            value="{{ old('ma_nv', $warehouseTransaction->ma_nv ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Lệnh SX</label>
                        <input type="text" name="lenh_sx" class="form-control"
                            value="{{ old('lenh_sx', $warehouseTransaction->lenh_sx ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2">{{ old('note', $warehouseTransaction->note ?? '') }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    <a href="{{ route('admin.warehouse-transactions') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection
