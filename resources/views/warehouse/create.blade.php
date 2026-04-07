@extends('layouts.app')
@section('content')
    <div class="container py-4" style="max-width:680px">
        <div class="card-page">
            <h5 class="mb-4 fw-bold" style="color:#1e3a5f"><i class="fa-solid fa-plus-circle me-2"></i>Nhập / Xuất Kho</h5>

            <form method="POST" action="{{ route('admin.warehouse-transactions.store') }}">
                @csrf
                <div class="row g-3">

                    {{-- Loại giao dịch --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Loại giao dịch *</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cong_doan" value="NHAPKHO"
                                    id="nhap" checked>
                                <label class="form-check-label text-success fw-bold" for="nhap">
                                    <i class="fa-solid fa-arrow-down me-1"></i>NHẬP KHO
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cong_doan" value="XUATKHO"
                                    id="xuat">
                                <label class="form-check-label text-danger fw-bold" for="xuat">
                                    <i class="fa-solid fa-arrow-up me-1"></i>XUẤT KHO
                                </label>
                            </div>
                        </div>
                        @error('cong_doan')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ngày --}}
                    <div class="col-md-4">
                        <label class="form-label">Ngày *</label>
                        <input type="date" name="ngay" class="form-control @error('ngay') is-invalid @enderror"
                            value="{{ old('ngay', now()->format('Y-m-d')) }}">
                        @error('ngay')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Màu --}}
                    <div class="col-md-4">
                        <label class="form-label">Màu *</label>
                        <input type="text" name="mau" list="list-mau"
                            class="form-control @error('mau') is-invalid @enderror" value="{{ old('mau') }}"
                            placeholder="WHITE, BLACK...">
                        <datalist id="list-mau">
                            @foreach ($danhSachMau as $m)
                                <option value="{{ $m }}">
                            @endforeach
                        </datalist>
                        @error('mau')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Size --}}
                    <div class="col-md-4">
                        <label class="form-label">Size *</label>
                        <input type="text" name="size" list="list-size"
                            class="form-control @error('size') is-invalid @enderror" value="{{ old('size') }}"
                            placeholder="9MM, 12MM...">
                        <datalist id="list-size">
                            @foreach ($danhSachSize as $s)
                                <option value="{{ $s }}">
                            @endforeach
                        </datalist>
                        @error('size')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mã HH --}}
                    <div class="col-md-4">
                        <label class="form-label">Mã hàng hóa</label>
                        <input type="text" name="ma_hh" class="form-control" value="{{ old('ma_hh') }}"
                            placeholder="Mã hàng hóa...">
                    </div>

                    {{-- Số lượng --}}
                    <div class="col-md-4">
                        <label class="form-label">Số lượng *</label>
                        <input type="number" name="so_luong" step="0.01" min="0"
                            class="form-control @error('so_luong') is-invalid @enderror" value="{{ old('so_luong') }}"
                            placeholder="0.00">
                        @error('so_luong')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mã NV --}}
                    <div class="col-md-4">
                        <label class="form-label">Mã NV</label>
                        <input type="text" name="ma_nv" class="form-control" value="{{ old('ma_nv') }}"
                            placeholder="NV001">
                    </div>

                    {{-- Lệnh SX --}}
                    <div class="col-md-4">
                        <label class="form-label">Lệnh SX</label>
                        <input type="text" name="lenh_sx" class="form-control" value="{{ old('lenh_sx') }}"
                            placeholder="LSX-001">
                    </div>

                    {{-- Ghi chú --}}
                    <div class="col-12">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú thêm nếu có...">{{ old('note') }}</textarea>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4"><i
                                class="fa-solid fa-floppy-disk me-1"></i>Lưu</button>
                        <a href="{{ route('admin.warehouse-transactions.index') }}" class="btn btn-outline-secondary">Hủy</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
