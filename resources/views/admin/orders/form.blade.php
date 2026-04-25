@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.orders.index') }}" class="text-decoration-none"
                style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-file-invoice me-2"></i>{{ isset($order) ? 'Sửa Đơn hàng' : 'Thêm Đơn hàng' }}
            </h4>
        </div>
        <div class="card-page">
            <form method="POST"
                action="{{ isset($order) ? route('admin.orders.update', $order) : route('admin.orders.store') }}">
                @csrf
                @if (isset($order))
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Khách hàng</label>
                        <select name="khach_hang_id" class="form-select">
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach ($khachHangs ?? [] as $id => $ten)
                                <option value="{{ $id }}"
                                    {{ old('khach_hang_id', $order->khach_hang_id ?? '') == $id ? 'selected' : '' }}>
                                    {{ $ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Job No <span class="text-danger">*</span></label>
                        <input type="text" name="job_no" class="form-control @error('job_no') is-invalid @enderror"
                            value="{{ old('job_no', $order->job_no ?? '') }}" required>
                        @error('job_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fty PO</label>
                        <input type="text" name="fty_po" class="form-control"
                            value="{{ old('fty_po', $order->fty_po ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">IM Number</label>
                        <input type="text" name="im_number" class="form-control"
                            value="{{ old('im_number', $order->im_number ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Color</label>
                        <input type="text" name="color" class="form-control"
                            value="{{ old('color', $order->color ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">YRD</label>
                        <input type="number" step="0.01" name="yrd" class="form-control"
                            value="{{ old('yrd', $order->yrd ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Unit</label>
                        <input type="text" name="unit" class="form-control"
                            value="{{ old('unit', $order->unit ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Mã HH</label>
                        <input type="text" name="ma_hh" class="form-control"
                            value="{{ old('ma_hh', $order->ma_hh ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tên HH</label>
                        <input type="text" name="ten_hh" class="form-control"
                            value="{{ old('ten_hh', $order->ten_hh ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Cần giao 1</label>
                        <input type="number" step="0.01" name="can_giao_1" class="form-control"
                            value="{{ old('can_giao_1', $order->can_giao_1 ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Cần giao 2</label>
                        <input type="number" step="0.01" name="can_giao_2" class="form-control"
                            value="{{ old('can_giao_2', $order->can_giao_2 ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">PL Number</label>
                        <input type="text" name="pl_number" class="form-control"
                            value="{{ old('pl_number', $order->pl_number ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tagtime ETC</label>
                        <input type="date" name="tagtime_etc" class="form-control"
                            value="{{ old('tagtime_etc', isset($order) ? $order->tagtime_etc?->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sig Need Date</label>
                        <input type="date" name="sig_need_date" class="form-control"
                            value="{{ old('sig_need_date', isset($order) ? $order->sig_need_date?->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Chart</label>
                        <input type="text" name="chart" class="form-control"
                            value="{{ old('chart', $order->chart ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Price USD (Auto)</label>
                        <input type="number" step="0.0001" name="price_usd_auto" class="form-control"
                            value="{{ old('price_usd_auto', $order->price_usd_auto ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Price USD</label>
                        <input type="number" step="0.0001" name="price_usd" class="form-control"
                            value="{{ old('price_usd', $order->price_usd ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tờ khai</label>
                        <input type="text" name="to_khai" class="form-control"
                            value="{{ old('to_khai', $order->to_khai ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Lệnh sản xuất</label>
                        <input type="text" name="lenh_sanxuat" class="form-control"
                            value="{{ old('lenh_sanxuat', $order->lenh_sanxuat ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach (['pending', 'in_production', 'done', 'shipped'] as $s)
                                <option value="{{ $s }}"
                                    {{ old('status', $order->status ?? 'pending') == $s ? 'selected' : '' }}>
                                    {{ $s }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection
