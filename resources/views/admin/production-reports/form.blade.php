@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="card-page">
            <h5 class="fw-bold mb-3" style="color:#1e3a5f">
                <i class="fa-solid fa-industry me-2"></i>{{ isset($productionReport) ? 'Sửa Báo cáo' : 'Thêm Báo cáo SX' }}
            </h5>
            <form method="POST"
                action="{{ isset($productionReport) ? route('admin.production-reports.update', $productionReport) : route('admin.production-reports.store') }}">
                @csrf
                @if (isset($productionReport))
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Công đoạn</label>
                        <input type="text" name="cong_doan" class="form-control"
                            value="{{ old('cong_doan', $productionReport->cong_doan ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Ngày SX <span class="text-danger">*</span></label>
                        <input type="date" name="ngay_sx" class="form-control @error('ngay_sx') is-invalid @enderror"
                            value="{{ old('ngay_sx', isset($productionReport) ? $productionReport->ngay_sx->format('Y-m-d') : '') }}"
                            required>
                        @error('ngay_sx')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ca</label>
                        <select name="ca" class="form-select">
                            <option value="">--</option>
                            @foreach (['1', '2', '3'] as $ca)
                                <option value="{{ $ca }}"
                                    {{ old('ca', $productionReport->ca ?? '') == $ca ? 'selected' : '' }}>Ca {{ $ca }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Mã NV</label>
                        <input type="text" name="ma_nv" class="form-control"
                            value="{{ old('ma_nv', $productionReport->ma_nv ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Lệnh SX</label>
                        <input type="text" name="lenh_sx" class="form-control"
                            value="{{ old('lenh_sx', $productionReport->lenh_sx ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Màu</label>
                        <input type="text" name="mau" class="form-control"
                            value="{{ old('mau', $productionReport->mau ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Size</label>
                        <input type="text" name="size" class="form-control"
                            value="{{ old('size', $productionReport->size ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Định mức</label>
                        <input type="number" step="0.0001" name="dinh_muc" class="form-control"
                            value="{{ old('dinh_muc', $productionReport->dinh_muc ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Số band</label>
                        <input type="number" name="so_band" class="form-control"
                            value="{{ old('so_band', $productionReport->so_band ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">NS 8h/1 máy</label>
                        <input type="number" step="0.01" name="ns_8h_1may" class="form-control"
                            value="{{ old('ns_8h_1may', $productionReport->ns_8h_1may ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">NS giờ/máy</label>
                        <input type="number" step="0.01" name="ns_gio_may" class="form-control"
                            value="{{ old('ns_gio_may', $productionReport->ns_gio_may ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">SL Đạt</label>
                        <input type="number" step="0.01" name="sl_dat" class="form-control"
                            value="{{ old('sl_dat', $productionReport->sl_dat ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">SL Hư</label>
                        <input type="number" step="0.01" name="sl_hu" class="form-control"
                            value="{{ old('sl_hu', $productionReport->sl_hu ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Số máy</label>
                        <input type="number" name="so_may" class="form-control"
                            value="{{ old('so_may', $productionReport->so_may ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Giờ SX</label>
                        <input type="number" step="0.01" name="gio_sx" class="form-control"
                            value="{{ old('gio_sx', $productionReport->gio_sx ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">SL Yard/Met</label>
                        <input type="number" step="0.01" name="sl_yard_met" class="form-control"
                            value="{{ old('sl_yard_met', $productionReport->sl_yard_met ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Vấn đề</label>
                        <textarea name="van_de" class="form-control" rows="2">{{ old('van_de', $productionReport->van_de ?? '') }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    <a href="{{ route('admin.production-reports') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection
