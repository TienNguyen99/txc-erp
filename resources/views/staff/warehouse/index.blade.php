@extends('layouts.staff')

@section('content')
    <h5 class="fw-bold mb-3"><i class="fa-solid fa-dolly me-2 text-success"></i>Nhap kho theo Lenh SX</h5>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="staff-card mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col">
                <label class="form-label fw-semibold mb-1">Ma Lenh SX</label>
                <input type="text" name="lenh_sx" class="form-control" list="dsLenh"
                    placeholder="Nhap hoac chon lenh..." value="{{ $lenhSx }}" autofocus>
                <datalist id="dsLenh">
                    @foreach ($danhSachLenh as $lenh)
                        <option value="{{ $lenh }}">
                    @endforeach
                </datalist>
            </div>
            <div class="col-auto">
                <button class="btn btn-staff"><i class="fa-solid fa-search me-1"></i>Tra cuu</button>
            </div>
        </form>
    </div>

    @if ($lenhSx && $items->count())
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="staff-card text-center">
                    <div class="text-muted small">Tong can nhap</div>
                    <div class="fw-bold fs-5 text-success">{{ number_format($summary->total_remaining, 0) }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="staff-card text-center">
                    <div class="text-muted small">Ma con thieu</div>
                    <div class="fw-bold fs-5 {{ $summary->shortage_items > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $summary->shortage_items }}
                    </div>
                </div>
            </div>
        </div>

        <div class="staff-card">
            <form method="POST" action="{{ route('staff.warehouse.store') }}">
                @csrf
                <input type="hidden" name="lenh_sx" value="{{ $lenhSx }}">

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold mb-1">Ngay nhap</label>
                        <input type="date" name="ngay" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold mb-1">Lenh</label>
                        <input type="text" class="form-control" value="{{ $lenhSx }}" disabled>
                    </div>
                </div>

                <div class="alert alert-success small py-2 mb-3">
                    <i class="fa-solid fa-clipboard-list me-1"></i>
                    <strong>{{ $items->count() }}</strong> ma hang. He thong da goi y so luong con lai, nhan vien co the sua lai theo thuc te.
                </div>

                @foreach ($items as $i => $item)
                    <div class="border rounded-3 p-3 mb-2 {{ $item->con_lai <= 0 ? 'border-success' : 'border-danger' }}"
                        style="background:#fafffe">
                        <input type="hidden" name="rows[{{ $i }}][ma_hh]" value="{{ $item->ma_hang }}">
                        <input type="hidden" name="rows[{{ $i }}][mau]" value="{{ $item->mau }}">
                        <input type="hidden" name="rows[{{ $i }}][size]" value="{{ $item->size }}">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold" style="font-size:.95rem">{{ $item->ma_hang }}</div>
                                <div class="text-muted" style="font-size:.8rem">
                                    {{ $item->mau ?: '-' }} &bull; {{ $item->size ?: '-' }} &bull; {{ $item->job_no }}
                                </div>
                            </div>
                            <div class="text-end" style="font-size:.8rem">
                                <div>Don: <strong>{{ number_format($item->sl_don) }}</strong></div>
                                <div>Da nhap: <strong>{{ number_format($item->da_nhap_lenh) }}</strong></div>
                                <div class="{{ $item->con_lai > 0 ? 'text-danger' : 'text-success' }}">
                                    Con lai: <strong>{{ number_format($item->con_lai) }}</strong>
                                </div>
                                <div class="{{ $item->ton_kho < $item->sl_don ? 'text-danger' : 'text-success' }}">
                                    Ton: <strong>{{ number_format($item->ton_kho) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white" style="font-size:.8rem">SL nhap</span>
                            <input type="number" step="0.01" min="0" name="rows[{{ $i }}][so_luong]"
                                class="form-control text-end fw-bold" placeholder="0" inputmode="decimal"
                                value="{{ $item->con_lai > 0 ? rtrim(rtrim(number_format($item->con_lai, 2, '.', ''), '0'), '.') : '' }}">
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-staff btn-lg w-100 mt-3">
                    <i class="fa-solid fa-save me-1"></i>Nhap kho va tao phieu
                </button>
            </form>
        </div>
    @elseif ($lenhSx)
        <div class="alert alert-warning">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Khong tim thay don hang nao voi lenh "<strong>{{ $lenhSx }}</strong>".
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('staff.warehouse.history') }}" class="text-muted" style="font-size:.85rem">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>Xem lich su nhap kho
        </a>
    </div>
@endsection
