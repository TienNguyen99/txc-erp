@extends('layouts.staff')
@section('content')
    <h5 class="fw-bold mb-3"><i class="fa-solid fa-dolly me-2 text-success"></i>Nhập kho theo Lệnh SX</h5>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Chọn lệnh --}}
    <div class="staff-card mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col">
                <label class="form-label fw-semibold mb-1">Mã Lệnh SX</label>
                <input type="text" name="lenh_sx" class="form-control" list="dsLenh"
                    placeholder="Nhập hoặc chọn lệnh..." value="{{ $lenhSx }}" autofocus>
                <datalist id="dsLenh">
                    @foreach ($danhSachLenh as $lenh)
                        <option value="{{ $lenh }}">
                    @endforeach
                </datalist>
            </div>
            <div class="col-auto">
                <button class="btn btn-staff"><i class="fa-solid fa-search me-1"></i>Tra cứu</button>
            </div>
        </form>
    </div>

    @if ($lenhSx && $items->count())
        <div class="staff-card">
            <form method="POST" action="{{ route('staff.warehouse.store') }}">
                @csrf
                <input type="hidden" name="lenh_sx" value="{{ $lenhSx }}">

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold mb-1">Ngày nhập</label>
                        <input type="date" name="ngay" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold mb-1">Lệnh</label>
                        <input type="text" class="form-control" value="{{ $lenhSx }}" disabled>
                    </div>
                </div>

                <div class="alert alert-success small py-2 mb-3">
                    <i class="fa-solid fa-clipboard-list me-1"></i>
                    <strong>{{ $items->count() }}</strong> mã hàng — Chỉ cần điền <strong>SL nhập</strong>.
                </div>

                {{-- Danh sách hàng --}}
                @foreach ($items as $i => $item)
                    <div class="border rounded-3 p-3 mb-2 {{ $item->ton_kho >= $item->sl_don ? 'border-success' : 'border-danger' }}" style="background:#fafffe">
                        <input type="hidden" name="rows[{{ $i }}][ma_hh]" value="{{ $item->ma_hang }}">
                        <input type="hidden" name="rows[{{ $i }}][mau]" value="{{ $item->mau }}">
                        <input type="hidden" name="rows[{{ $i }}][size]" value="{{ $item->size }}">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold" style="font-size:.95rem">{{ $item->ma_hang }}</div>
                                <div class="text-muted" style="font-size:.8rem">
                                    {{ $item->mau ?: '—' }} &bull; {{ $item->size ?: '—' }} &bull; {{ $item->job_no }}
                                </div>
                            </div>
                            <div class="text-end" style="font-size:.8rem">
                                <div>Đơn: <strong>{{ number_format($item->sl_don) }}</strong></div>
                                <div class="{{ $item->ton_kho < $item->sl_don ? 'text-danger' : 'text-success' }}">
                                    Tồn: <strong>{{ number_format($item->ton_kho) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white" style="font-size:.8rem">SL nhập</span>
                            <input type="number" step="0.01" min="0"
                                name="rows[{{ $i }}][so_luong]"
                                class="form-control text-end fw-bold"
                                placeholder="0" inputmode="decimal">
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-staff btn-lg w-100 mt-3">
                    <i class="fa-solid fa-save me-1"></i>Nhập kho
                </button>
            </form>
        </div>

    @elseif ($lenhSx)
        <div class="alert alert-warning">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Không tìm thấy đơn hàng nào với lệnh "<strong>{{ $lenhSx }}</strong>".
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('staff.warehouse.history') }}" class="text-muted" style="font-size:.85rem">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>Xem lịch sử nhập kho
        </a>
    </div>
@endsection
