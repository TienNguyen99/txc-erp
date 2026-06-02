@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.hang-hoa.index') }}" class="text-decoration-none"
                style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-box-open me-2"></i>{{ isset($hangHoa) ? 'Sửa Hàng hóa' : 'Thêm Hàng hóa' }}
            </h4>
            @if (isset($hangHoa))
                <div class="mt-2 d-flex gap-2">
                    @if (!empty($prevHangHoaId))
                        <a href="{{ route('admin.hang-hoa.edit', $prevHangHoaId) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i>Hàng trước
                        </a>
                    @endif
                    @if (!empty($nextHangHoaId))
                        <a href="{{ route('admin.hang-hoa.edit', $nextHangHoaId) }}" class="btn btn-outline-primary btn-sm">
                            Hàng tiếp<i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
            @endif
        </div>
        <div class="card-page">
            <form method="POST" enctype="multipart/form-data"
                action="{{ isset($hangHoa) ? route('admin.hang-hoa.update', $hangHoa) : route('admin.hang-hoa.store') }}">
                @csrf
                @if (isset($hangHoa))
                    @method('PUT')
                @endif
                <input type="hidden" name="after_save" id="afterSaveAction" value="">
                <input type="hidden" name="next_id" value="{{ $nextHangHoaId ?? '' }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã HH <span class="text-danger">*</span></label>
                        <input type="text" name="ma_hh" class="form-control @error('ma_hh') is-invalid @enderror"
                            value="{{ old('ma_hh', $hangHoa->ma_hh ?? '') }}" pattern="[-A-Za-z0-9_]+"
                            title="Chi cho phep chu, so, dau gach ngang (-) va gach duoi (_)." required>
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
                        <label class="form-label fw-semibold">ĐVT tồn kho chuẩn</label>
                        <select name="base_uom_id" id="base-uom" class="form-select">
                            <option value="">-- Chọn --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" data-dimension="{{ $unit->dimension }}" data-factor="{{ $unit->factor_to_base }}" @selected(old('base_uom_id', $hangHoa->base_uom_id ?? '') == $unit->id)>{{ $unit->code }} - {{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Đơn giá</label>
                        <input type="number" step="0.0001" name="don_gia" class="form-control"
                            value="{{ old('don_gia', $hangHoa->don_gia ?? 0) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Quy cách đóng gói</label>
                        <select name="quy_cach" class="form-select">
                            <option value="">-- Chọn --</option>
                            <option value="Quấn cuộn" {{ old('quy_cach', $hangHoa->quy_cach ?? '') == 'Quấn cuộn' ? 'selected' : '' }}>Quấn cuộn</option>
                            <option value="Xả thùng" {{ old('quy_cach', $hangHoa->quy_cach ?? '') == 'Xả thùng' ? 'selected' : '' }}>Xả thùng</option>
                            <option value="Khác" {{ old('quy_cach', $hangHoa->quy_cach ?? '') == 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Số Yard/Cuộn</label>
                        <input type="number" step="0.01" name="yards_per_roll" class="form-control"
                            value="{{ old('yards_per_roll', $hangHoa->yards_per_roll ?? '') }}" placeholder="36">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Số Cuộn/Thùng</label>
                        <input type="number" name="rolls_per_carton" class="form-control"
                            value="{{ old('rolls_per_carton', $hangHoa->rolls_per_carton ?? '') }}" placeholder="2, 27...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Định mức thùng <small
                                class="text-muted">(yard)</small></label>
                        <input type="number" name="dinh_muc_thung" class="form-control"
                            value="{{ old('dinh_muc_thung', $hangHoa->dinh_muc_thung ?? '') }}" placeholder="540, 504...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Net Weight <small
                                class="text-muted">(KGS/thùng)</small></label>
                        <input type="number" step="0.01" name="net_weight" class="form-control"
                            value="{{ old('net_weight', $hangHoa->net_weight ?? '') }}" placeholder="11.5">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Gross Weight <small
                                class="text-muted">(KGS/thùng)</small></label>
                        <input type="number" step="0.01" name="gross_weight" class="form-control"
                            value="{{ old('gross_weight', $hangHoa->gross_weight ?? '') }}" placeholder="11.73">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Hình ảnh</label>
                        <div id="dropZoneImage" class="border rounded-3 p-3 text-center"
                            style="border-style:dashed!important;cursor:pointer;background:#fafafa">
                            <input id="hinhAnhInput" type="file" name="hinh_anh" class="d-none" accept="image/*">
                            <div class="text-muted" style="font-size:.85rem">
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i>Kéo thả ảnh vào đây hoặc bấm để chọn
                            </div>
                            <div class="mt-2">
                                <img id="hinhAnhPreview"
                                    src="{{ isset($hangHoa) && $hangHoa->hinh_anh ? asset('storage/' . $hangHoa->hinh_anh) : '' }}"
                                    style="width:80px;height:80px;object-fit:cover;border-radius:10px;{{ isset($hangHoa) && $hangHoa->hinh_anh ? '' : 'display:none;' }}">
                            </div>
                        </div>
                        @error('hinh_anh')
                            <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="active" id="active"
                                value="1" {{ old('active', $hangHoa->active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="active">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="mo_ta" class="form-control" rows="2">{{ old('mo_ta', $hangHoa->mo_ta ?? '') }}</textarea>
                    </div>

                    {{-- ── Thông tin NVL (Module 1, 3, 9) ── --}}
                    <div class="col-12 mt-2">
                        <div class="p-3" style="background:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0">
                            <h6 class="fw-bold text-success mb-3" style="font-size:.85rem">
                                <i class="fa-solid fa-truck me-1"></i>Thông tin Nguyên Vật Liệu (NVL)
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nhà cung cấp</label>
                                    <select name="nha_cung_cap_id" class="form-select form-select-sm">
                                        <option value="">-- Chọn NCC --</option>
                                        @foreach(\App\Models\NhaCungCap::where('active', true)->orderBy('ten_ncc')->get() as $ncc)
                                        <option value="{{ $ncc->id }}"
                                            {{ old('nha_cung_cap_id', $hangHoa->nha_cung_cap_id ?? '') == $ncc->id ? 'selected' : '' }}>
                                            {{ $ncc->ma_ncc }} – {{ $ncc->ten_ncc }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">ĐVT mua mặc định</label>
                                    <select name="purchase_uom_id" id="purchase-uom" class="form-select form-select-sm">
                                        <option value="">-- Chọn --</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}" data-dimension="{{ $unit->dimension }}" data-factor="{{ $unit->factor_to_base }}" @selected(old('purchase_uom_id', $hangHoa->purchase_uom_id ?? '') == $unit->id)>{{ $unit->code }} - {{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hệ số quy đổi</label>
                                    <input type="number" step="0.000001" min="0.000001" name="purchase_to_base_factor" id="purchase-factor" class="form-control form-control-sm"
                                        value="{{ old('purchase_to_base_factor', $hangHoa->purchase_to_base_factor ?? 1) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Giá NVL (VNĐ/ĐVT mua)
                                        <small class="text-muted">— dùng tính chi phí SX</small>
                                    </label>
                                    <input type="number" step="1" min="0" name="gia_nvl" class="form-control form-control-sm"
                                        value="{{ old('gia_nvl', $hangHoa->gia_nvl ?? 0) }}" placeholder="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tồn kho tối thiểu
                                        <small class="text-muted">— cảnh báo khi dưới mức này</small>
                                    </label>
                                    <input type="number" min="0" name="ton_toi_thieu" class="form-control form-control-sm"
                                        value="{{ old('ton_toi_thieu', $hangHoa->ton_toi_thieu ?? 0) }}" placeholder="0">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info py-2 px-3 mb-0 small" id="uom-preview">Chọn đơn vị để xem quy đổi.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary" onclick="document.getElementById('afterSaveAction').value=''"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    @if (isset($hangHoa) && !empty($nextHangHoaId))
                        <button type="submit" class="btn btn-outline-primary ms-2" onclick="document.getElementById('afterSaveAction').value='next'">
                            <i class="fa-solid fa-forward-step me-1"></i>Lưu & sang hàng tiếp
                        </button>
                    @endif
                    <a href="{{ route('admin.hang-hoa.index') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const dropZone = document.getElementById('dropZoneImage');
            const input = document.getElementById('hinhAnhInput');
            const preview = document.getElementById('hinhAnhPreview');
            if (!dropZone || !input || !preview) return;

            const setInputFile = (file) => {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            };

            const centerCropToSquare = (file) => {
                return new Promise((resolve) => {
                    if (!file || !file.type.startsWith('image/')) return resolve(null);
                    const img = new Image();
                    const url = URL.createObjectURL(file);
                    img.onload = () => {
                        const side = Math.min(img.width, img.height);
                        const sx = Math.floor((img.width - side) / 2);
                        const sy = Math.floor((img.height - side) / 2);
                        const canvas = document.createElement('canvas');
                        canvas.width = side;
                        canvas.height = side;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, sx, sy, side, side, 0, 0, side, side);
                        canvas.toBlob((blob) => {
                            URL.revokeObjectURL(url);
                            if (!blob) return resolve(null);
                            resolve(new File([blob], file.name || 'pasted-image.jpg', {
                                type: 'image/jpeg'
                            }));
                        }, 'image/jpeg', 0.92);
                    };
                    img.onerror = () => {
                        URL.revokeObjectURL(url);
                        resolve(null);
                    };
                    img.src = url;
                });
            };

            const handleImageFile = async (file) => {
                const cropped = await centerCropToSquare(file);
                const finalFile = cropped || file;
                if (!finalFile) return;
                setInputFile(finalFile);
                const previewUrl = URL.createObjectURL(finalFile);
                preview.src = previewUrl;
                preview.style.display = 'inline-block';
            };

            dropZone.addEventListener('click', () => input.click());
            input.addEventListener('change', async (e) => {
                await handleImageFile(e.target.files?.[0]);
            });

            ['dragenter', 'dragover'].forEach(evt => {
                dropZone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    dropZone.style.background = '#eef6ff';
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropZone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    dropZone.style.background = '#fafafa';
                });
            });

            dropZone.addEventListener('drop', (e) => {
                const file = e.dataTransfer?.files?.[0];
                if (!file) return;
                handleImageFile(file);
            });

            document.addEventListener('paste', async (e) => {
                const items = e.clipboardData?.items || [];
                for (const item of items) {
                    if (item.type && item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        if (file) {
                            e.preventDefault();
                            await handleImageFile(file);
                            break;
                        }
                    }
                }
            });

            const baseUom = document.getElementById('base-uom');
            const purchaseUom = document.getElementById('purchase-uom');
            const factor = document.getElementById('purchase-factor');
            const uomPreview = document.getElementById('uom-preview');
            const suggestUomFactor = () => {
                const base = baseUom.options[baseUom.selectedIndex];
                const purchase = purchaseUom.options[purchaseUom.selectedIndex];
                if (base?.dataset.dimension && base.dataset.dimension === purchase?.dataset.dimension) {
                    factor.value = Number(purchase.dataset.factor || 1) / Number(base.dataset.factor || 1);
                }
                updateUomPreview();
            };
            const updateUomPreview = () => {
                const base = baseUom.options[baseUom.selectedIndex]?.text.split(' - ')[0] || '';
                const purchase = purchaseUom.options[purchaseUom.selectedIndex]?.text.split(' - ')[0] || '';
                const ratio = Number(factor.value || 1);
                uomPreview.textContent = base && purchase
                    ? `Quy đổi nhập kho: 1 ${purchase} = ${ratio.toLocaleString('vi-VN')} ${base}. Bảng giá vốn dùng đơn giá theo ${base}.`
                    : 'Chọn đơn vị để xem quy đổi.';
            };
            [baseUom, purchaseUom].forEach(input => input?.addEventListener('change', suggestUomFactor));
            factor?.addEventListener('input', updateUomPreview);
            updateUomPreview();
        })();
    </script>
@endsection
