@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.dinh-muc-nvl.index') }}" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i> Trở về</a>
        <h2 class="page-title mb-0">
            Cấu hình BOM: <span class="text-primary">{{ $sanPham->ma_hh }}</span> - {{ $sanPham->ten_hh }}
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Bảng danh sách NVL đã cấu hình -->
        <div class="col-md-8">
            <div class="card-page p-0 mb-4">
                <div class="p-3 border-bottom bg-light fw-bold">
                    Danh sách Nguyên vật liệu cấu thành (1 {{ $sanPham->don_vi }} {{ $sanPham->ma_hh }})
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Mã NVL</th>
                                <th>Tên NVL</th>
                                <th class="text-end">Định mức</th>
                                <th>Đơn vị</th>
                                <th class="text-end">Hao hụt (%)</th>
                                <th>Công đoạn</th>
                                <th>Ghi chú</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($boms as $bom)
                                <tr>
                                    <td><strong>{{ $bom->nguyenLieu->ma_hh }}</strong></td>
                                    <td>{{ $bom->nguyenLieu->ten_hh }}</td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($bom->so_luong, 4) }}</td>
                                    <td>{{ $bom->nguyenLieu->don_vi }}</td>
                                    <td class="text-end">{{ number_format($bom->ti_le_hao_hut, 2) }}%</td>
                                    <td>
                                        @if($bom->cong_doan)
                                            <span class="badge bg-info text-dark">{{ $bom->cong_doan }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $bom->ghi_chu }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.dinh-muc-nvl.destroy', [$sanPham->id, $bom->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa nguyên liệu này khỏi BOM?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Sản phẩm này chưa được cấu hình BOM.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form thêm mới NVL vào BOM -->
        <div class="col-md-4">
            <div class="card-page">
                <h5 class="mb-4"><i class="fa-solid fa-plus text-primary me-2"></i>Thêm Nguyên liệu</h5>
                <form action="{{ route('admin.dinh-muc-nvl.store', $sanPham->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Chọn Nguyên vật liệu <span class="text-danger">*</span></label>
                        <select name="nguyen_lieu_id" id="nguyen_lieu_id" class="form-select" required>
                            <option value="">-- Tìm mã hoặc tên NVL --</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->ma_hh }} - {{ $mat->ten_hh }} ({{ $mat->don_vi }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Định mức (Số lượng) <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" name="so_luong" class="form-control" required placeholder="VD: 1.5">
                        <small class="text-muted">Cần bao nhiêu để làm ra 1 {{ $sanPham->don_vi }} Thành phẩm?</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tỷ lệ hao hụt (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="ti_le_hao_hut" class="form-control" value="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gắn với Công đoạn (Tùy chọn)</label>
                        <input type="text" name="cong_doan" class="form-control" placeholder="VD: Dệt, Nhuộm, Đóng gói...">
                        <small class="text-muted">Để biết NVL này được xuất kho ở bước nào.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" class="form-control" placeholder="Ghi chú thêm...">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Lưu Định Mức</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#nguyen_lieu_id', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });
</script>
@endsection
