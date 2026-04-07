@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.hang-hoa') }}" class="text-decoration-none" style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-box-open me-2"></i>{{ isset($hangHoa) ? 'Sửa Hàng hóa' : 'Thêm Hàng hóa' }}
            </h4>
        </div>
        <div class="card-page">
            <form method="POST" enctype="multipart/form-data"
                action="{{ isset($hangHoa) ? route('admin.hang-hoa.update', $hangHoa) : route('admin.hang-hoa.store') }}">
                @csrf
                @if (isset($hangHoa))
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã HH <span class="text-danger">*</span></label>
                        <input type="text" name="ma_hh" class="form-control @error('ma_hh') is-invalid @enderror"
                            value="{{ old('ma_hh', $hangHoa->ma_hh ?? '') }}" required>
                        @error('ma_hh')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tên hàng hóa <span class="text-danger">*</span></label>
                        <input type="text" name="ten_hh" class="form-control @error('ten_hh') is-invalid @enderror"
                            value="{{ old('ten_hh', $hangHoa->ten_hh ?? '') }}" required>
                        @error('ten_hh')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Màu</label>
                        <input type="text" name="mau" class="form-control"
                            value="{{ old('mau', $hangHoa->mau ?? '') }}" placeholder="WHITE, BLACK...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Kích cỡ</label>
                        <input type="text" name="kich_co" class="form-control"
                            value="{{ old('kich_co', $hangHoa->kich_co ?? '') }}" placeholder="9MM, 12MM...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nhóm HH</label>
                        <input type="text" name="nhom_hh" class="form-control"
                            value="{{ old('nhom_hh', $hangHoa->nhom_hh ?? '') }}" placeholder="Vải, Phụ kiện...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Đơn vị tính</label>
                        <input type="text" name="don_vi" class="form-control"
                            value="{{ old('don_vi', $hangHoa->don_vi ?? '') }}" placeholder="yard, mét, cái...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Đơn giá</label>
                        <input type="number" step="0.0001" name="don_gia" class="form-control"
                            value="{{ old('don_gia', $hangHoa->don_gia ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Hình ảnh</label>
                        <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                        @if (isset($hangHoa) && $hangHoa->hinh_anh)
                            <img src="{{ asset('storage/' . $hangHoa->hinh_anh) }}" class="mt-2"
                                style="width:60px;height:60px;object-fit:cover;border-radius:10px;">
                        @endif
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                                {{ old('active', $hangHoa->active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="active">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="mo_ta" class="form-control" rows="2">{{ old('mo_ta', $hangHoa->mo_ta ?? '') }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    <a href="{{ route('admin.hang-hoa') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection
