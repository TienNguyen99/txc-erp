@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-box-open me-2"></i>Danh mục Hàng hóa</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.hang-hoa.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-file-lines me-1"></i>Tải Template
                </a>
                <a href="{{ route('admin.hang-hoa.export') }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                </a>
                <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal"
                    data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i>Import Excel
                </button>
                <a href="{{ route('admin.hang-hoa.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Thêm Hàng hóa
                </a>
            </div>
        </div>

        <div class="card-page">
            @include('admin.partials.alert')

            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Mã HH / Tên HH..." value="{{ request('search') }}">
                </div>
                @if(request('missing'))
                    <input type="hidden" name="missing" value="{{ request('missing') }}">
                @endif
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Tìm</button>
                </div>
            </form>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('admin.hang-hoa.index') }}"
                    class="btn btn-sm {{ request('missing') ? 'btn-outline-secondary' : 'btn-primary' }}">
                    Tất cả
                </a>
                <a href="{{ route('admin.hang-hoa.index', ['missing' => 'price']) }}"
                    class="btn btn-sm {{ request('missing') === 'price' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fa-solid fa-tags me-1"></i>Thiếu đơn giá
                </a>
                <a href="{{ route('admin.hang-hoa.index', ['missing' => 'carton_norm']) }}"
                    class="btn btn-sm {{ request('missing') === 'carton_norm' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fa-solid fa-boxes-packing me-1"></i>Thiếu định mức thùng
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Mã HH</th>
                            <th>Tên hàng hóa</th>
                            <th>Màu</th>
                            <th>Kích cỡ</th>
                            <th>Nhóm</th>
                            <th>ĐVT</th>
                            <th>Đơn giá</th>
                            <th>Định mức thùng</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td class="fw-semibold">{{ $item->ma_hh }}</td>
                                <td>{{ $item->ten_hh }}</td>
                                <td>{{ $item->mau }}</td>
                                <td>{{ $item->kich_co }}</td>
                                <td>{{ $item->nhom_hh }}</td>
                                <td>{{ $item->don_vi }}</td>
                                <td>
                                    @if((float) ($item->don_gia ?? 0) > 0)
                                        {{ number_format($item->don_gia, 2) }}
                                    @else
                                        <span class="badge bg-warning text-dark">Thiếu</span>
                                    @endif
                                </td>
                                <td>
                                    @if((int) ($item->dinh_muc_thung ?? 0) > 0)
                                        {{ number_format($item->dinh_muc_thung) }}
                                    @else
                                        <span class="badge bg-warning text-dark">Thiếu</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->active ? 'success' : 'secondary' }}">
                                        {{ $item->active ? 'Active' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.hang-hoa.edit', $item) }}" class="btn btn-warning btn-xs"><i
                                            class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.hang-hoa.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa hàng hóa này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:var(--radius)">
                <form method="POST" action="{{ route('admin.hang-hoa.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-file-import me-2"></i>Import Hàng hóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            File Excel cần có các cột header: <strong>ma_hh, ten_hh</strong>.
                            Tùy chọn: <strong>mau, kich_co, nhom_hh, don_vi, don_gia, dinh_muc_thung, mo_ta</strong>.
                        </p>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i>Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
