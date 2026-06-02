@extends('layouts.app')

@section('css')
    <style>
        .cost-sheet { --cost-green:#16a34a; --cost-yellow:#fef08a; }
        .cost-summary { background:#f8fafc; border:1px solid var(--border); border-radius:12px; padding:1rem; }
        .cost-summary strong { color:var(--text); font-size:1.2rem; }
        .cost-table th { color:var(--text-muted); font-size:.68rem; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
        .cost-table td { font-size:.8rem; vertical-align:middle; }
        .cost-group td { background:#f0fdf4; border-top:2px solid #bbf7d0; font-weight:800; }
        .cost-total td { background:var(--cost-yellow); font-weight:800; }
        .cost-sheet .form-label { font-size:.78rem; font-weight:700; }
        .cost-muted { color:var(--text-muted); font-size:.72rem; }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4 cost-sheet">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <a href="{{ route('admin.standard-cost-sheets.index') }}" class="text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i>Danh sách định mức</a>
                <h4 class="page-title my-1">Bảng tính giá vốn: {{ $standardCostSheet->product->ma_hh }}</h4>
                <div class="text-muted small">{{ $standardCostSheet->product->ten_hh }} · Phiên bản {{ $standardCostSheet->version }} · Hiệu lực {{ $standardCostSheet->effective_date->format('d/m/Y') }}</div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge align-self-center {{ $standardCostSheet->status === 'active' ? 'bg-success' : ($standardCostSheet->status === 'draft' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                    {{ \App\Models\StandardCostSheet::STATUSES[$standardCostSheet->status] ?? $standardCostSheet->status }}
                </span>
                @can('warehouse.edit')
                    @if ($standardCostSheet->status !== 'active')
                        <form method="POST" action="{{ route('admin.standard-cost-sheets.activate', $standardCostSheet) }}">@csrf
                            <button class="btn btn-success btn-sm"><i class="fa-solid fa-check me-1"></i>Áp dụng phiên bản</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>

        @include('admin.partials.alert')

        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="cost-summary"><div class="cost-muted">Giá thành sản xuất / SP</div><strong>{{ number_format($calculation['production_cost_vnd'], 2) }} đ</strong></div></div>
            <div class="col-md-3"><div class="cost-summary"><div class="cost-muted">Tổng giá vốn / SP</div><strong class="text-danger">{{ number_format($calculation['total_cost_vnd'], 2) }} đ</strong></div></div>
            <div class="col-md-3"><div class="cost-summary"><div class="cost-muted">Giá bán dự kiến</div><strong class="text-primary">{{ number_format($calculation['sale_price_vnd'], 2) }} đ</strong></div></div>
            <div class="col-md-3"><div class="cost-summary"><div class="cost-muted">Lợi nhuận / SP</div><strong class="{{ $calculation['profit_vnd'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($calculation['profit_vnd'], 2) }} đ</strong><div class="cost-muted">Biên {{ number_format($calculation['margin_pct'], 2) }}% · Markup {{ number_format($calculation['markup_pct'], 2) }}%</div></div></div>
        </div>

        <div class="card-page mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div><h5 class="fw-bold mb-1"><i class="fa-solid fa-tags text-primary me-2"></i>Đề xuất giá bán</h5><div class="cost-muted">Đã tính ngược các khoản theo giá bán và làm tròn lên theo cấu hình.</div></div>
                <span class="badge bg-light text-dark border">Biên mục tiêu {{ number_format((float) $standardCostSheet->target_margin_pct, 2) }}%</span>
            </div>
            <div class="row g-3">
                <div class="col-md-3"><div class="cost-summary"><div class="cost-muted">Giá hòa vốn chưa VAT</div><strong>{{ number_format($calculation['break_even_price_vnd'], 2) }} đ</strong></div></div>
                <div class="col-md-3"><div class="cost-summary border-primary"><div class="cost-muted">Giá đề xuất chưa VAT</div><strong class="text-primary">{{ number_format($calculation['suggested_price_vnd'], 2) }} đ</strong><div class="cost-muted">Biên thực tế sau làm tròn {{ number_format($calculation['suggested_margin_pct'], 2) }}%</div></div></div>
                <div class="col-md-3"><div class="cost-summary"><div class="cost-muted">Giá báo khách gồm VAT {{ number_format((float) $standardCostSheet->vat_pct, 2) }}%</div><strong class="text-success">{{ number_format($calculation['quote_price_vnd'], 2) }} đ</strong></div></div>
                <div class="col-md-3"><div class="cost-summary"><div class="cost-muted">Lợi nhuận dự kiến / SP</div><strong class="text-success">{{ number_format($calculation['suggested_profit_vnd'], 2) }} đ</strong></div></div>
            </div>
        </div>

        <div class="card-page p-0 mb-3">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <div><h5 class="fw-bold mb-0">Chi tiết cấu thành giá vốn</h5><div class="cost-muted">Mọi dòng đều quy đổi về chi phí cho 1 đơn vị thành phẩm.</div></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm cost-table mb-0">
                    <thead class="table-light"><tr><th>Nhóm / hạng mục</th><th>Công đoạn</th><th>ĐVT</th><th class="text-end">Định mức / hệ số</th><th class="text-end">Hao hụt</th><th class="text-end">Đơn giá</th><th class="text-end">SL phân bổ</th><th class="text-end">Giá vốn / SP</th><th>Ghi chú</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($calculation['groups'] as $group)
                            <tr class="cost-group"><td colspan="7">{{ $loop->iteration }}. {{ $group['label'] }}</td><td class="text-end">{{ number_format($group['total_vnd'], 2) }} đ</td><td colspan="2"></td></tr>
                            @forelse ($group['lines'] as $line)
                                <tr>
                                    <td><div class="fw-semibold">{{ $line->code ?: $line->item?->ma_hh }} {{ $line->name }}</div><div class="cost-muted">{{ $line->formula_text }}</div></td>
                                    <td>{{ $line->stage ?: '-' }}</td><td>{{ $line->unit ?: '-' }}</td>
                                    <td class="text-end">{{ number_format((float) $line->quantity, 6) }}</td>
                                    <td class="text-end">{{ number_format((float) $line->waste_pct, 2) }}%</td>
                                    <td class="text-end">{{ number_format((float) $line->unit_price_vnd, 2) }}</td>
                                    <td class="text-end">{{ $line->allocation_qty ? number_format((float) $line->allocation_qty, 2) : '-' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($line->calculated_cost_vnd, 2) }} đ</td>
                                    <td>{{ $line->note }}</td>
                                    <td class="text-end">
                                        @can('warehouse.edit')
                                            <form method="POST" action="{{ route('admin.standard-cost-sheets.lines.destroy', [$standardCostSheet, $line]) }}" onsubmit="return confirm('Xóa dòng chi phí này?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash"></i></button></form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-2">Chưa có dòng chi phí.</td></tr>
                            @endforelse
                        @endforeach
                        <tr><td colspan="7" class="fw-bold">A. Giá thành sản xuất</td><td class="text-end fw-bold">{{ number_format($calculation['production_cost_vnd'], 2) }} đ</td><td colspan="2"></td></tr>
                        <tr><td colspan="7">B. Lãi ngân hàng ({{ number_format((float) $standardCostSheet->bank_interest_pct, 2) }}%)</td><td class="text-end">{{ number_format($calculation['bank_interest_vnd'], 2) }} đ</td><td colspan="2"></td></tr>
                        <tr><td colspan="7">C. Hoa hồng ({{ number_format((float) $standardCostSheet->commission_pct, 2) }}%)</td><td class="text-end">{{ number_format($calculation['commission_vnd'], 2) }} đ</td><td colspan="2"></td></tr>
                        <tr><td colspan="7">D. Chi phí quản lý ({{ number_format((float) $standardCostSheet->management_pct, 2) }}%)</td><td class="text-end">{{ number_format($calculation['management_vnd'], 2) }} đ</td><td colspan="2"></td></tr>
                        <tr><td colspan="7">E. Vận chuyển / SP</td><td class="text-end">{{ number_format($calculation['transport_vnd'], 2) }} đ</td><td colspan="2"></td></tr>
                        <tr class="cost-total"><td colspan="7">TỔNG GIÁ VỐN / SP</td><td class="text-end">{{ number_format($calculation['total_cost_vnd'], 2) }} đ</td><td colspan="2"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        @can('warehouse.edit')
            <div class="row g-3">
                <div class="col-xl-7">
                    <div class="card-page">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus text-primary me-2"></i>Thêm dòng chi phí</h5>
                        <form method="POST" action="{{ route('admin.standard-cost-sheets.lines.store', $standardCostSheet) }}">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-4"><label class="form-label">Nhóm chi phí</label><select name="category" id="cost-category" class="form-select" required>@foreach (\App\Models\StandardCostLine::CATEGORIES as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Chọn NVL có sẵn <span class="cost-muted">(tùy chọn)</span></label>
                                        <button type="button" class="btn btn-outline-primary btn-xs mb-1" data-bs-toggle="modal" data-bs-target="#quickItemModal">
                                            <i class="fa-solid fa-plus me-1"></i>Thêm NVL mới
                                        </button>
                                    </div>
                                    <select name="item_id" id="cost-item" class="form-select"><option value="">Nhập thủ công...</option>@foreach ($materials as $material)<option value="{{ $material->id }}" data-code="{{ $material->ma_hh }}" data-name="{{ $material->ten_hh }}" data-unit="{{ $material->baseUom?->code ?: $material->don_vi }}" data-price="{{ $material->gia_nvl ? $material->base_unit_cost_vnd : (float) $material->don_gia }}">{{ $material->ma_hh }} - {{ $material->ten_hh }}</option>@endforeach</select>
                                </div>
                                <div class="col-md-4"><label class="form-label">Mã / ký hiệu</label><input name="code" id="cost-code" class="form-control"></div>
                                <div class="col-md-8"><label class="form-label">Tên hạng mục</label><input name="name" id="cost-name" class="form-control" required></div>
                                <div class="col-md-4"><label class="form-label">Công đoạn</label><input name="stage" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">ĐVT</label><input name="unit" id="cost-unit" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">Định mức / hệ số</label><input type="number" step="0.000001" min="0" name="quantity" value="1" class="form-control" required></div>
                                <div class="col-md-3"><label class="form-label">Hao hụt %</label><input type="number" step="0.0001" min="0" max="100" name="waste_pct" value="0" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Đơn giá / chi phí</label><input type="number" step="0.0001" min="0" name="unit_price_vnd" id="cost-price" value="0" class="form-control" required></div>
                                <div class="col-md-4"><label class="form-label">SL phân bổ <span class="cost-muted">(nếu có)</span></label><input type="number" step="0.0001" min="0.0001" name="allocation_qty" class="form-control" placeholder="VD: năng suất ca"></div>
                                <div class="col-md-4"><label class="form-label">Ghi chú</label><input name="note" class="form-control"></div>
                            </div>
                            <button class="btn btn-primary mt-3"><i class="fa-solid fa-floppy-disk me-1"></i>Thêm dòng</button>
                        </form>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="card-page">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-sliders text-primary me-2"></i>Thông số tổng hợp</h5>
                        <form method="POST" action="{{ route('admin.standard-cost-sheets.update', $standardCostSheet) }}">
                            @csrf @method('PUT')
                            <div class="row g-2">
                                <div class="col-md-6"><label class="form-label">Phiên bản</label><input name="version" value="{{ $standardCostSheet->version }}" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Ngày hiệu lực</label><input type="date" name="effective_date" value="{{ $standardCostSheet->effective_date->toDateString() }}" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Sản lượng chuẩn</label><input type="number" step="0.0001" min="0.0001" name="standard_output_qty" value="{{ $standardCostSheet->standard_output_qty }}" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Giá bán dự kiến</label><input type="number" step="0.01" min="0" name="sale_price_vnd" value="{{ $standardCostSheet->sale_price_vnd }}" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Biên lợi nhuận mục tiêu %</label><input type="number" step="0.01" min="0" max="95" name="target_margin_pct" value="{{ $standardCostSheet->target_margin_pct }}" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">VAT %</label><input type="number" step="0.01" min="0" max="100" name="vat_pct" value="{{ $standardCostSheet->vat_pct }}" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Làm tròn giá lên</label><input type="number" step="1" min="1" name="price_rounding_vnd" value="{{ $standardCostSheet->price_rounding_vnd }}" class="form-control"></div>
                                @foreach (['bank_interest' => 'Lãi ngân hàng', 'commission' => 'Hoa hồng', 'management' => 'Chi phí quản lý'] as $field => $label)
                                    <div class="col-md-6"><label class="form-label">{{ $label }} %</label><input type="number" step="0.0001" min="0" max="100" name="{{ $field }}_pct" value="{{ $standardCostSheet->{$field.'_pct'} }}" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Cơ sở tính</label><select name="{{ $field }}_basis" class="form-select">@foreach (\App\Models\StandardCostSheet::BASES as $key => $basis)<option value="{{ $key }}" @selected($standardCostSheet->{$field.'_basis'} === $key)>{{ $basis }}</option>@endforeach</select></div>
                                @endforeach
                                <div class="col-md-6"><label class="form-label">Vận chuyển / SP</label><input type="number" step="0.0001" min="0" name="transport_cost_vnd" value="{{ $standardCostSheet->transport_cost_vnd }}" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Ghi chú</label><input name="note" value="{{ $standardCostSheet->note }}" class="form-control"></div>
                            </div>
                            <button class="btn btn-primary mt-3"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu cấu hình</button>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    @can('warehouse.edit')
        <div class="modal fade" id="quickItemModal" tabindex="-1" aria-labelledby="quickItemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="quick-item-form" data-no-loader>
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold" id="quickItemModalLabel">Thêm nhanh nguyên vật liệu</h5>
                                <div class="cost-muted">Chỉ nhập thông tin cần để tính giá vốn. Có thể bổ sung chi tiết trong danh mục sau.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>
                        <div class="modal-body">
                            <div id="quick-item-errors" class="alert alert-danger d-none mb-3"></div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label">Mã NVL <span class="text-danger">*</span></label>
                                    <input name="ma_hh" class="form-control" pattern="[-A-Za-z0-9_]+" required placeholder="VD: SOI-POLY-75">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Tên NVL <span class="text-danger">*</span></label>
                                    <input name="ten_hh" class="form-control" required placeholder="VD: Sợi poly 75">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nhóm hàng</label>
                                    <input name="nhom_hh" class="form-control" value="Nguyên vật liệu">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ĐVT tồn kho <span class="text-danger">*</span></label>
                                    <select name="base_uom_id" id="quick-base-uom" class="form-select" required>
                                        @foreach ($units as $unit)<option value="{{ $unit->id }}" data-dimension="{{ $unit->dimension }}" data-factor="{{ $unit->factor_to_base }}">{{ $unit->code }} - {{ $unit->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ĐVT mua <span class="text-danger">*</span></label>
                                    <select name="purchase_uom_id" id="quick-purchase-uom" class="form-select" required>
                                        @foreach ($units as $unit)<option value="{{ $unit->id }}" data-dimension="{{ $unit->dimension }}" data-factor="{{ $unit->factor_to_base }}">{{ $unit->code }} - {{ $unit->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">1 ĐVT mua = bao nhiêu ĐVT tồn</label>
                                    <input type="number" step="0.000001" min="0.000001" name="purchase_to_base_factor" id="quick-conversion-factor" class="form-control" value="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Giá NVL / ĐVT mua</label>
                                    <input type="number" step="0.0001" min="0" name="gia_nvl" class="form-control" value="0">
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <div class="alert alert-info py-2 px-3 mb-0 w-100 small" id="quick-conversion-preview"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button class="btn btn-primary" id="quick-item-submit"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu và chọn NVL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const item = document.getElementById('cost-item');
            if (!item) return;
            const itemSelect = new TomSelect(item, { create: false });
            item.addEventListener('change', () => {
                const option = item.options[item.selectedIndex];
                if (!option?.value) return;
                document.getElementById('cost-code').value = option.dataset.code || '';
                document.getElementById('cost-name').value = option.dataset.name || '';
                document.getElementById('cost-unit').value = option.dataset.unit || '';
                document.getElementById('cost-price').value = option.dataset.price || 0;
            });

            const quickForm = document.getElementById('quick-item-form');
            quickForm?.addEventListener('submit', async event => {
                event.preventDefault();
                const submit = document.getElementById('quick-item-submit');
                const errors = document.getElementById('quick-item-errors');
                submit.disabled = true;
                errors.classList.add('d-none');
                errors.textContent = '';

                try {
                    const response = await fetch('{{ route('admin.standard-cost-sheets.quick-create-item') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(Object.fromEntries(new FormData(quickForm))),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw payload;

                    const created = payload.item;
                    itemSelect.addOption({
                        value: String(created.id),
                        text: `${created.ma_hh} - ${created.ten_hh}`,
                        code: created.ma_hh,
                        name: created.ten_hh,
                        unit: created.don_vi || '',
                        price: created.base_unit_cost_vnd || 0,
                    });
                    itemSelect.setValue(String(created.id));
                    document.getElementById('cost-code').value = created.ma_hh;
                    document.getElementById('cost-name').value = created.ten_hh;
                    document.getElementById('cost-unit').value = created.don_vi || '';
                    document.getElementById('cost-price').value = created.base_unit_cost_vnd || 0;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('quickItemModal')).hide();
                    quickForm.reset();
                } catch (payload) {
                    const messages = Object.values(payload.errors || {}).flat();
                    errors.textContent = messages.join(' ') || payload.message || 'Không thể thêm hàng hóa.';
                    errors.classList.remove('d-none');
                } finally {
                    submit.disabled = false;
                    window.AppLoader?.hide();
                }
            });

            const baseUom = document.getElementById('quick-base-uom');
            const purchaseUom = document.getElementById('quick-purchase-uom');
            const conversionFactor = document.getElementById('quick-conversion-factor');
            const preview = document.getElementById('quick-conversion-preview');
            const suggestConversionFactor = () => {
                const base = baseUom.options[baseUom.selectedIndex];
                const purchase = purchaseUom.options[purchaseUom.selectedIndex];
                if (base?.dataset.dimension && base.dataset.dimension === purchase?.dataset.dimension) {
                    conversionFactor.value = Number(purchase.dataset.factor || 1) / Number(base.dataset.factor || 1);
                }
                updateConversionPreview();
            };
            const updateConversionPreview = () => {
                const base = baseUom.options[baseUom.selectedIndex]?.text.split(' - ')[0] || '';
                const purchase = purchaseUom.options[purchaseUom.selectedIndex]?.text.split(' - ')[0] || '';
                const factor = Number(conversionFactor.value || 1);
                preview.textContent = `Quy đổi: 1 ${purchase} = ${factor.toLocaleString('vi-VN')} ${base}. Giá vốn vật tư sẽ tính theo ${base}.`;
            };
            [baseUom, purchaseUom].forEach(input => input?.addEventListener('change', suggestConversionFactor));
            conversionFactor?.addEventListener('input', updateConversionPreview);
            updateConversionPreview();
        });
    </script>
@endsection
