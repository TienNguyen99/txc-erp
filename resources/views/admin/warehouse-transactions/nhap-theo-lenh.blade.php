@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-dolly me-2"></i>Nhập kho theo Lệnh SX</h4>
            <a href="{{ route('admin.warehouse-transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại Kho
            </a>
        </div>

        <div class="card-page">
            @include('admin.partials.alert')

            {{-- Chọn Lệnh SX --}}
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Lệnh SX</label>
                    <input type="text" name="lenh_sx" class="form-control" list="danhSachLenh"
                        placeholder="Nhập hoặc chọn mã lệnh SX..." value="{{ $lenhSx }}">
                    <datalist id="danhSachLenh">
                        @foreach ($danhSachLenh as $lenh)
                            <option value="{{ $lenh }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button class="btn btn-primary"><i class="fa-solid fa-search me-1"></i>Tra cứu</button>
                </div>
            </form>

            @if ($lenhSx && $items->count())
                {{-- Form nhập kho --}}
                <form method="POST" action="{{ route('admin.warehouse-transactions.store-nhap-theo-lenh') }}">
                    @csrf
                    <input type="hidden" name="lenh_sx" value="{{ $lenhSx }}">

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Ngày nhập <span class="text-danger">*</span></label>
                            <input type="date" name="ngay" class="form-control" value="{{ now()->format('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Mã NV</label>
                            <input type="text" name="ma_nv" class="form-control" placeholder="Mã nhân viên nhập kho">
                        </div>
                    </div>

                    <div class="alert alert-info small py-2 mb-3">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        Lệnh: <strong>{{ $lenhSx }}</strong> — {{ $items->count() }} mã hàng.
                        Chỉ cần điền <strong>SL nhập</strong>, các dòng SL = 0 sẽ bỏ qua.
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Nhập tổng SL cho tất cả mã hàng</label>
                        <div class="input-group" style="max-width:220px">
                            <input type="number" step="0.01" min="0" id="input-total-qty"
                                class="form-control form-control-sm text-end" placeholder="Nhập tổng SL...">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyTotalQty()">Áp
                                dụng</button>
                        </div>
                        <small class="text-muted">Nhập số lượng vào ô này để tự động điền cho tất cả các dòng bên
                            dưới.</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Mã Hàng</th>
                                    <th>Màu</th>
                                    <th>Size</th>
                                    <th class="text-end">SL Đơn hàng</th>
                                    <th class="text-end">SL Tồn kho</th>
                                    <th class="text-end" style="background:#d4edda;color:#155724;min-width:120px">SL Nhập
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="fw-semibold">
                                            {{ $item->ma_hang }}
                                            <input type="hidden" name="rows[{{ $i }}][ma_hh]"
                                                value="{{ $item->ma_hang }}">
                                            <input type="hidden" name="rows[{{ $i }}][mau]"
                                                value="{{ $item->mau }}">
                                            <input type="hidden" name="rows[{{ $i }}][size]"
                                                value="{{ $item->size }}">
                                        </td>
                                        <td>{{ $item->mau ?: '—' }}</td>
                                        <td>{{ $item->size ?: '—' }}</td>
                                        <td class="text-end">{{ number_format($item->sl_don) }}</td>
                                        <td
                                            class="text-end {{ $item->ton_kho < $item->sl_don ? 'text-danger fw-bold' : 'text-success' }}">
                                            {{ number_format($item->ton_kho) }}
                                        </td>
                                        <td style="background:#f0fff0">
                                            <input type="number" step="0.01" min="0"
                                                name="rows[{{ $i }}][so_luong]"
                                                class="form-control form-control-sm text-end input-row-qty" placeholder="0"
                                                value="">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save me-1"></i>Nhập kho
                        </button>
                    </div>

                    <script>
                        function applyTotalQty() {
                            var total = document.getElementById('input-total-qty').value;
                            document.querySelectorAll('.input-row-qty').forEach(function(input) {
                                input.value = total;
                            });
                        }
                    </script>
                </form>
            @elseif ($lenhSx)
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Không tìm thấy đơn hàng nào với lệnh SX "<strong>{{ $lenhSx }}</strong>".
                </div>
            @endif
        </div>
    </div>
@endsection
