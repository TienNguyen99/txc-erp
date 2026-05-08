@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0">
            <i class="fa-solid fa-cart-shopping me-2"></i>
            {{ isset($nvlThieu) ? 'Tạo PO từ BOM (NVL thiếu)' : 'Tạo Purchase Order' }}
        </h4>
        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    @isset($nvlThieu)
    @if($nvlThieu->count())
    <div class="alert alert-warning mb-3" style="border-radius:var(--radius-sm)">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <strong>{{ $nvlThieu->count() }} mặt hàng</strong> tồn kho dưới mức tối thiểu. Đã điền sẵn vào bảng bên dưới.
        <button class="btn btn-warning btn-sm ms-2" id="btnLoadBom" type="button">
            <i class="fa-solid fa-fill-drip me-1"></i>Nạp vào bảng
        </button>
    </div>
    @else
    <div class="alert alert-success mb-3" style="border-radius:var(--radius-sm)">
        <i class="fa-solid fa-circle-check me-2"></i>Tất cả NVL đều đủ tồn kho tối thiểu!
    </div>
    @endif
    @endisset

    <div class="card-page">
        @include('admin.partials.alert')
        <form method="POST" action="{{ route('admin.purchase-orders.store') }}" id="formPo">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Số PO <span class="text-danger">*</span></label>
                    <input type="text" name="so_po" class="form-control form-control-sm"
                        value="{{ old('so_po', $soPo) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nhà cung cấp</label>
                    <select name="nha_cung_cap_id" class="form-select form-select-sm">
                        <option value="">-- Chọn NCC --</option>
                        @foreach($nccList as $ncc)
                        <option value="{{ $ncc->id }}">{{ $ncc->ma_ncc }} – {{ $ncc->ten_ncc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ngày đặt <span class="text-danger">*</span></label>
                    <input type="date" name="ngay_dat" class="form-control form-control-sm"
                        value="{{ old('ngay_dat', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ngày giao dự kiến</label>
                    <input type="date" name="ngay_giao_du_kien" class="form-control form-control-sm"
                        value="{{ old('ngay_giao_du_kien') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="ghi_chu" class="form-control form-control-sm" rows="2">{{ old('ghi_chu') }}</textarea>
                </div>
            </div>

            <h6 class="fw-bold mb-3"><i class="fa-solid fa-list me-2 text-primary"></i>Danh sách NVL đặt mua</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm no-sort-table align-middle" id="itemTable">
                    <thead>
                        <tr>
                            <th style="width:200px">Mã NVL</th>
                            <th>Tên NVL</th>
                            <th style="width:80px">ĐVT</th>
                            <th style="width:120px">Số lượng</th>
                            <th style="width:140px">Đơn giá (VNĐ)</th>
                            <th style="width:140px">Thành tiền</th>
                            <th class="no-sort" style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemBody">
                        <tr class="item-row">
                            <td>
                                <select name="items[0][ma_hh]" class="form-select form-select-sm ma-hh-select" required>
                                    <option value="">-- Chọn --</option>
                                    @foreach($nvlList as $hh)
                                    <option value="{{ $hh->ma_hh }}" data-don-vi="{{ $hh->don_vi }}" data-don-gia="{{ $hh->gia_nvl ?? 0 }}">
                                        {{ $hh->ma_hh }} - {{ $hh->ten_hh }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="items[0][ten_hh]" class="form-control form-control-sm ten-hh" placeholder="Tự động"></td>
                            <td><input type="text" name="items[0][don_vi]" class="form-control form-control-sm don-vi" value="Yard"></td>
                            <td><input type="number" name="items[0][so_luong]" class="form-control form-control-sm sl" step="0.01" min="0.01" required></td>
                            <td><input type="number" name="items[0][don_gia]" class="form-control form-control-sm don-gia" step="1" min="0" value="0"></td>
                            <td><span class="thanh-tien text-primary fw-semibold">0</span></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-xs btn-remove-row">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Tổng cộng:</td>
                            <td><span id="grandTotal" class="text-primary fw-bold">0</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btnAddRow">
                <i class="fa-solid fa-plus me-1"></i>Thêm dòng
            </button>
            <hr>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-floppy-disk me-1"></i>Tạo Purchase Order
            </button>
        </form>
    </div>
</div>

@php $nvlThieuJson = isset($nvlThieu) ? $nvlThieu->toJson() : '[]'; @endphp
@section('scripts')
<script>
    const nvlThieu = @json(isset($nvlThieu) ? $nvlThieu : []);
    const nvlList  = @json($nvlList);
    let rowIdx = 1;

    function buildOptions(selected = '') {
        return nvlList.map(hh =>
            `<option value="${hh.ma_hh}" data-don-vi="${hh.don_vi ?? 'Yard'}" data-don-gia="${hh.gia_nvl ?? 0}" ${hh.ma_hh === selected ? 'selected' : ''}>
                ${hh.ma_hh} - ${hh.ten_hh}
            </option>`
        ).join('');
    }

    function addRow(maHh = '', tenHh = '', soLuong = '', donGia = '') {
        const idx = rowIdx++;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <select name="items[${idx}][ma_hh]" class="form-select form-select-sm ma-hh-select" required>
                    <option value="">-- Chọn --</option>
                    ${buildOptions(maHh)}
                </select>
            </td>
            <td><input type="text" name="items[${idx}][ten_hh]" class="form-control form-control-sm ten-hh" value="${tenHh}" placeholder="Tự động"></td>
            <td><input type="text" name="items[${idx}][don_vi]" class="form-control form-control-sm don-vi" value="Yard"></td>
            <td><input type="number" name="items[${idx}][so_luong]" class="form-control form-control-sm sl" step="0.01" min="0.01" value="${soLuong}" required></td>
            <td><input type="number" name="items[${idx}][don_gia]" class="form-control form-control-sm don-gia" step="1" min="0" value="${donGia}"></td>
            <td><span class="thanh-tien text-primary fw-semibold">0</span></td>
            <td><button type="button" class="btn btn-danger btn-xs btn-remove-row"><i class="fa-solid fa-times"></i></button></td>
        `;
        document.getElementById('itemBody').appendChild(tr);
        bindRow(tr);
        updateRow(tr);
    }

    function fmtNum(n) { return new Intl.NumberFormat('vi-VN').format(Math.round(n)); }

    function updateRow(tr) {
        const sl = parseFloat(tr.querySelector('.sl')?.value) || 0;
        const dg = parseFloat(tr.querySelector('.don-gia')?.value) || 0;
        const tt = sl * dg;
        tr.querySelector('.thanh-tien').textContent = fmtNum(tt);
        calcTotal();
    }

    function calcTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(tr => {
            const sl = parseFloat(tr.querySelector('.sl')?.value) || 0;
            const dg = parseFloat(tr.querySelector('.don-gia')?.value) || 0;
            total += sl * dg;
        });
        document.getElementById('grandTotal').textContent = fmtNum(total);
    }

    function bindRow(tr) {
        tr.querySelector('.ma-hh-select').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            tr.querySelector('.don-vi').value = opt.dataset.donVi || 'Yard';
            tr.querySelector('.don-gia').value = opt.dataset.donGia || 0;
            // tên tự động
            const found = nvlList.find(h => h.ma_hh === this.value);
            if (found) tr.querySelector('.ten-hh').value = found.ten_hh || '';
            updateRow(tr);
        });
        tr.querySelector('.sl').addEventListener('input', () => updateRow(tr));
        tr.querySelector('.don-gia').addEventListener('input', () => updateRow(tr));
        tr.querySelector('.btn-remove-row').addEventListener('click', function () {
            if (document.querySelectorAll('.item-row').length > 1) {
                tr.remove(); calcTotal();
            }
        });
    }

    // Bind first row
    document.querySelectorAll('.item-row').forEach(tr => bindRow(tr));

    document.getElementById('btnAddRow').addEventListener('click', () => addRow());

    // Load BOM suggestion
    const btnLoadBom = document.getElementById('btnLoadBom');
    if (btnLoadBom) {
        btnLoadBom.addEventListener('click', () => {
            // Xóa hết row cũ
            document.getElementById('itemBody').innerHTML = '';
            rowIdx = 0;
            nvlThieu.forEach(item => addRow(item.ma_hh, item.ten_hh, item.can_mua, item.don_gia));
        });
    }
</script>
@endsection
@endsection
