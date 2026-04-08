@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.order-tracking.index') }}" class="text-decoration-none"
                style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-truck-fast me-2"></i>{{ isset($orderTracking) ? 'Sửa Tracking' : 'Thêm Tracking' }}
            </h4>
        </div>
        <div class="card-page">
            <form method="POST"
                action="{{ isset($orderTracking) ? route('admin.order-tracking.update', $orderTracking) : route('admin.order-tracking.store') }}">
                @csrf
                @if (isset($orderTracking))
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Order (Job No) <span class="text-danger">*</span></label>
                        <select name="order_id" class="form-select @error('order_id') is-invalid @enderror" required>
                            <option value="">-- Chọn Order --</option>
                            @foreach ($orders as $id => $jobNo)
                                <option value="{{ $id }}"
                                    {{ old('order_id', $orderTracking->order_id ?? '') == $id ? 'selected' : '' }}>
                                    {{ $jobNo }}</option>
                            @endforeach
                        </select>
                        @error('order_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">PL Number</label>
                        <input type="text" name="pl_number" class="form-control"
                            value="{{ old('pl_number', $orderTracking->pl_number ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Size</label>
                        <input type="text" name="size" class="form-control"
                            value="{{ old('size', $orderTracking->size ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Màu</label>
                        <input type="text" name="mau" class="form-control"
                            value="{{ old('mau', $orderTracking->mau ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Kích</label>
                        <input type="text" name="kich" class="form-control"
                            value="{{ old('kich', $orderTracking->kich ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Công đoạn</label>
                        <select name="cong_doan" class="form-select">
                            <option value="">-- Chọn công đoạn --</option>
                            @foreach (\App\Models\OrderTracking::STAGES as $stage => $info)
                                <option value="{{ $stage }}"
                                    {{ old('cong_doan', $orderTracking->cong_doan ?? '') == $stage ? 'selected' : '' }}>
                                    {{ $stage }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">SL Đơn hàng</label>
                        <input type="number" step="0.01" name="sl_don_hang" class="form-control"
                            value="{{ old('sl_don_hang', $orderTracking->sl_don_hang ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">SL Sản xuất</label>
                        <input type="number" step="0.01" name="sl_san_xuat" class="form-control"
                            value="{{ old('sl_san_xuat', $orderTracking->sl_san_xuat ?? 0) }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    <a href="{{ route('admin.order-tracking.index') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection
